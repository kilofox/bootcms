<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 提供简单的基准测试与性能分析。要显示收集到的统计信息，可加载 `profiler/stats` 视图：
 *
 *     echo View::factory('profiler/stats');
 *
 * @package BootPHP
 * @category 辅助类
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Profiler {

	/**
	 * @var integer 保留的应用统计的最大数量
	 */
	public static $rollover = 1000;

	/**
	 * @var array 收集的基准
	 */
	protected static $_marks = array();

	/**
	 * 开启一个新的基准测试，并返回唯一令牌。_必须_用返回的令牌来结束测试。
	 *
	 *     $token = Profiler::start('test', 'profiler');
	 *
	 * @param string $group 分组名
	 * @param string $name 基准名
	 * @return string
	 */
	public static function start($group, $name)
	{
		static $counter = 0;
		// 根据这个记数器创建唯一令牌
		$token = 'kp/' . base_convert($counter++, 10, 32);
		self::$_marks[$token] = array(
			'group' => strtolower($group),
			'name' => (string) $name,
			// 基准测试开始了
			'start_time' => microtime(true),
			'start_memory' => memory_get_usage(),
			// 设置结束的键，没有值
			'stop_time' => false,
			'stop_memory' => false,
		);
		return $token;
	}

	/**
	 * 停止基准测试。
	 *
	 *     Profiler::stop($token);
	 *
	 * @param string $token
	 * @return void
	 */
	public static function stop($token)
	{
		// 停止基准测试
		self::$_marks[$token]['stop_time'] = microtime(true);
		self::$_marks[$token]['stop_memory'] = memory_get_usage();
	}

	/**
	 * 删除基准。如果在测试的过程中出现错误，建议删除这个基准，以防统计受到不利影响。
	 *
	 *     Profiler::delete($token);
	 *
	 * @param string $token
	 * @return void
	 */
	public static function delete($token)
	{
		// 移除基准
		unset(self::$_marks[$token]);
	}

	/**
	 * 返回全部基准令牌的数组。
	 *
	 *     $groups = Profiler::groups();
	 *
	 * @return array
	 */
	public static function groups()
	{
		$groups = array();
		foreach (self::$_marks as $token => $mark)
		{
			// 根据分组和名称对令牌进行排序
			$groups[$mark['group']][$mark['name']][] = $token;
		}
		return $groups;
	}

	/**
	 * 取得一组令牌的最小值、最大值、平均值和总计的数组。
	 *
	 *     $stats = Profiler::stats($tokens);
	 *
	 * @param array $tokens 分析器的令牌
	 * @return array 最小值、最大值、平均值、总计
	 * @uses Profiler::total
	 */
	public static function stats(array $tokens)
	{
		// 最小值和最大值尚不知晓
		$min = $max = array(
			'time' => NULL,
			'memory' => NULL
		);
		// 总计为整型
		$total = array(
			'time' => 0,
			'memory' => 0
		);
		foreach ($tokens as $token)
		{
			// 为基准取得总时间和总内存
			list($time, $memory) = self::total($token);
			if ($min['time'] === NULL || $time < $min['time'])
			{
				// 设置最小时间
				$min['time'] = $time;
			}
			if ($max['time'] === NULL || $time > $max['time'])
			{
				// 设置最大时间
				$max['time'] = $time;
			}
			// 增加总时间
			$total['time'] += $time;
			if ($min['memory'] === NULL || $memory < $min['memory'])
			{
				// 设置最小内存
				$min['memory'] = $memory;
			}
			if ($max['memory'] === NULL || $memory > $max['memory'])
			{
				// 设置最大内存
				$max['memory'] = $memory;
			}
			// 增加总内存
			$total['memory'] += $memory;
		}
		// 确定令牌数
		$count = count($tokens);
		// 确定平均值
		$average = array(
			'time' => $total['time'] / $count,
			'memory' => $total['memory'] / $count
		);
		return array(
			'min' => $min,
			'max' => $max,
			'total' => $total,
			'average' => $average
		);
	}

	/**
	 * 取得分析器最小值、最大值、平均值和总计的全部分组。
	 *
	 *     $stats = Profiler::groupStats('test');
	 *
	 * @param mixed 单个组名字符串，或组名数组；默认为全部分组
	 * @return array 最小值、最大值、平均值、总计
	 * @uses Profiler::groups
	 * @uses Profiler::stats
	 */
	public static function groupStats($groups = NULL)
	{
		// 需要计算哪些分组的统计信息？
		$groups = $groups === NULL ? self::groups() : array_intersect_key(self::groups(), array_flip((array) $groups));
		// 所有统计
		$stats = array();
		foreach ($groups as $group => $names)
		{
			foreach ($names as $name => $tokens)
			{
				// 为每个子分组存储统计。
				// 我们只需要“总计”的值。
				$_stats = self::stats($tokens);
				$stats[$group][$name] = $_stats['total'];
			}
		}
		// 分组统计
		$groups = array();
		foreach ($stats as $group => $names)
		{
			// 最小值和最大值尚不知晓
			$groups[$group]['min'] = $groups[$group]['max'] = array(
				'time' => NULL,
				'memory' => NULL
			);
			// 总计为整型
			$groups[$group]['total'] = array(
				'time' => 0,
				'memory' => 0
			);
			foreach ($names as $total)
			{
				if (!isset($groups[$group]['min']['time']) || $groups[$group]['min']['time'] > $total['time'])
				{
					// 设置最小时间
					$groups[$group]['min']['time'] = $total['time'];
				}
				if (!isset($groups[$group]['max']['time']) || $groups[$group]['max']['time'] < $total['time'])
				{
					// 设置最大时间
					$groups[$group]['max']['time'] = $total['time'];
				}
				if (!isset($groups[$group]['min']['memory']) || $groups[$group]['min']['memory'] > $total['memory'])
				{
					// 设置最小内存
					$groups[$group]['min']['memory'] = $total['memory'];
				}

				if (!isset($groups[$group]['max']['memory']) || $groups[$group]['max']['memory'] < $total['memory'])
				{
					// 设置最大内存
					$groups[$group]['max']['memory'] = $total['memory'];
				}
				// 增加总时间和总内存
				$groups[$group]['total']['time'] += $total['time'];
				$groups[$group]['total']['memory'] += $total['memory'];
			}
			// 确定子分组的基准数
			$count = count($names);
			// 确定平均值
			$groups[$group]['average']['time'] = $groups[$group]['total']['time'] / $count;
			$groups[$group]['average']['memory'] = $groups[$group]['total']['memory'] / $count;
		}
		return $groups;
	}

	/**
	 * 取得一个基准的总执行时间与内存使用的列表。
	 *
	 * @param string $token
	 * @return array 执行时间、内存使用
	 */
	public static function total($token)
	{
		// 导入基准数据
		$mark = self::$_marks[$token];
		// 基准测试尚未停止
		if ($mark['stop_time'] === false)
		{
			$mark['stop_time'] = microtime(true);
			$mark['stop_memory'] = memory_get_usage();
		}
		return array(
			// 总秒数
			$mark['stop_time'] - $mark['start_time'],
			// 内存总字节数
			$mark['stop_memory'] - $mark['start_memory'],
		);
	}

	/**
	 * 取得应用的运行时间与内存使用。缓存这个结果，以便与其它请求进行比较。
	 *
	 * @return array 执行时间、内存使用
	 * @uses BootPHP::cache
	 */
	public static function application()
	{
		// 从缓存加载为期 1 天的统计
		$stats = BootPHP::cache('profiler_application_stats', NULL, 86400);
		if (!is_array($stats) || $stats['count'] > self::$rollover)
		{
			// 初始化统计数组
			$stats = array(
				'min' => array(
					'time' => NULL,
					'memory' => NULL
				),
				'max' => array(
					'time' => NULL,
					'memory' => NULL
				),
				'total' => array(
					'time' => NULL,
					'memory' => NULL
				),
				'count' => 0
			);
		}
		// 取得应用的运行时间
		$time = microtime(true) - START_TIME;
		// 取得总内存使用
		$memory = memory_get_usage() - START_MEMORY;
		// 计算最小时间
		if ($stats['min']['time'] === NULL || $time < $stats['min']['time'])
		{
			$stats['min']['time'] = $time;
		}
		// 计算最大时间
		if ($stats['max']['time'] === NULL || $time > $stats['max']['time'])
		{
			$stats['max']['time'] = $time;
		}
		// 添加到总时间
		$stats['total']['time'] += $time;
		// 计算最小内存
		if ($stats['min']['memory'] === NULL || $memory < $stats['min']['memory'])
		{
			$stats['min']['memory'] = $memory;
		}
		// 计算最大内存
		if ($stats['max']['memory'] === NULL || $memory > $stats['max']['memory'])
		{
			$stats['max']['memory'] = $memory;
		}
		// 添加到总内存使用
		$stats['total']['memory'] += $memory;
		// 另一个标记已加入到统计中
		$stats['count'] ++;
		// 确定平均值
		$stats['average'] = array(
			'time' => $stats['total']['time'] / $stats['count'],
			'memory' => $stats['total']['memory'] / $stats['count']);
		// 缓存新的统计
		BootPHP::cache('profiler_application_stats', $stats);
		// 设置当前应用的执行时间与内存使用
		// 不要缓存这些东西，它们只是针对当前请求
		$stats['current']['time'] = $time;
		$stats['current']['memory'] = $memory;
		// 返回应用的总运行时间与内存使用
		return $stats;
	}

}
