<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 验证规则是否有效。
 *
 * @package BootPHP
 * @category 安全
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Valid {

	/**
	 * 检查字段是否为空。
	 *
	 * @param string 值
	 * @return boolean
	 */
	public static function not_empty($value)
	{
		if (is_object($value) && $value instanceof ArrayObject)
		{
			// 从 ArrayObject 中得到数组
			$value = $value->getArrayCopy();
		}
		// 值不能是 NULL、false、'' 或空数组
		return !in_array($value, array(NULL, false, '', array()), true);
	}

	/**
	 * 检查字段是否符合正则表达式。
	 *
	 * @param string 值
	 * @param string 要匹配的正则表达式（包括分隔符）
	 * @return boolean
	 */
	public static function regex($value, $expression)
	{
		return (bool) preg_match($expression, (string) $value);
	}

	/**
	 * 检查字段是否足够长。
	 *
	 * @param string 值
	 * @param integer 要求的最小长度
	 * @return boolean
	 */
	public static function min_length($value, $length)
	{
		return mb_strlen($value) >= $length;
	}

	/**
	 * 检查字段是否足够短。
	 *
	 * @param string 值
	 * @param integer 要求的最大长度
	 * @return boolean
	 */
	public static function max_length($value, $length)
	{
		return mb_strlen($value) <= $length;
	}

	/**
	 * 检查字段的长度是否完全正确。
	 *
	 * @param string 值
	 * @param integer|array 需要检查的长度，或有效长度的数组
	 * @return boolean
	 */
	public static function exact_length($value, $length)
	{
		if (is_array($length))
		{
			foreach ($length as $strlen)
			{
				if (mb_strlen($value) === $strlen)
					return true;
			}
			return false;
		}
		return mb_strlen($value) === $length;
	}

	/**
	 * 检查字段与要求的值是否完全一致。
	 *
	 * @param string 值
	 * @param string 要求的值
	 * @return boolean
	 */
	public static function equals($value, $required)
	{
		return ($value === $required);
	}

	/**
	 * 检查 E-mail 地址格式的正确性。
	 *
	 * @param string E-mail 地址
	 * @param boolean 严格的 RFC 兼容
	 * @return boolean
	 */
	public static function email($email, $strict = false)
	{
		if (mb_strlen($email) > 254)
			return false;
		if ($strict === true)
		{
			$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
			$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
			$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
			$pair = '\\x5c[\\x00-\\x7f]';
			$domain_literal = "\\x5b($dtext|$pair)*\\x5d";
			$quoted_string = "\\x22($qtext|$pair)*\\x22";
			$sub_domain = "($atom|$domain_literal)";
			$word = "($atom|$quoted_string)";
			$domain = "$sub_domain(\\x2e$sub_domain)*";
			$local_part = "$word(\\x2e$word)*";
			$expression = "/^$local_part\\x40$domain$/D";
		}
		else
		{
			$expression = '/^[a-z0-9]+((_|-|\.)[a-z0-9]+)*@[a-z0-9]+((\.|-)[a-z0-9]+)*\.[a-z]{2,6}$/i';
		}
		return (bool) preg_match($expression, (string) $email);
	}

	/**
	 * 验证 E-mail 地址的域，检查域是否为有效的 MX 记录。
	 *
	 * @link http://php.net/manual/zh/function.checkdnsrr.php
	 * @param string E-mail 地址
	 * @return boolean
	 */
	public static function email_domain($email)
	{
		// 空值对于 checkdnsrr() 会产生问题
		if (!Valid::not_empty($email))
			return false;
		// 检查 E-mail 域是否为有效的 MX 记录
		return (bool) checkdnsrr(preg_replace('/^[^@]++@/', '', $email), 'MX');
	}

	/**
	 * 验证URL。
	 *
	 * @param string URL
	 * @return boolean
	 */
	public static function url($url)
	{
		// 基于 http://tools.ietf.org/html/rfc1738#section-5
		if (!preg_match(
				'~^
			# 协议
			[-a-z0-9+.]++://
			# 用户名:密码（可选）
			(?:
				[-a-z0-9$_.+!*\'(),;?&=%]++ # 用户名
				(?::[-a-z0-9$_.+!*\'(),;?&=%]++)? # 密码（可选）
				@
			)?
			(?:
				# IP地址
				\d{1,3}+(?:\.\d{1,3}+){3}+
				| # or
				# 主机名（捕获的）
				(
					(?!-)[-a-z0-9]{1,63}+(?<!-)
					(?:\.(?!-)[-a-z0-9]{1,63}+(?<!-)){0,126}+
				)
			)
			# 端口（可选）
			(?::\d{1,5}+)?
			# 路径（可选）
			(?:/.*)?
			$~iDx', $url, $matches))
			return false;
		// 匹配到了IP地址
		if (!isset($matches[1]))
			return true;
		// 检查整个主机名的最大长度
		// https://zh.wikipedia.org/wiki/%E5%9F%9F%E5%90%8D#.E5.9F.9F.E5.90.8D.E6.9C.8D.E5.8A.A1.E5.99.A8
		if (strlen($matches[1]) > 253)
			return false;
		// 对顶级域名的额外检查
		// 它必须以字母开头
		$tld = ltrim(substr($matches[1], (int) strrpos($matches[1], '.')), '.');
		return ctype_alpha($tld[0]);
	}

	/**
	 * 验证 IP
	 *
	 * @param string IP地址
	 * @param boolean 允许私有IP网络
	 * @return boolean
	 */
	public static function ip($ip, $allow_private = true)
	{
		// 不允许保留的地址
		$flags = FILTER_FLAG_NO_RES_RANGE;
		if ($allow_private === false)
		{
			// 不允许私有的和保留的地址
			$flags = $flags | FILTER_FLAG_NO_PRIV_RANGE;
		}
		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);
	}

	/**
	 * 验证信用卡号码，可带 Luhn 检查。
	 *
	 * @param integer 信用卡号码
	 * @param string|array 卡类型，或卡类型的数组
	 * @return boolean
	 * @uses Valid::luhn
	 */
	public static function credit_card($number, $type = NULL)
	{
		// 从卡号中移除所有非数字字符
		if (($number = preg_replace('/\D+/', '', $number)) === '')
			return false;
		if ($type == NULL)
		{
			// 使用默认类型
			$type = 'default';
		}
		elseif (is_array($type))
		{
			foreach ($type as $t)
			{
				// 测试每种类型的有效性
				if (self::credit_card($number, $t))
					return true;
			}
			return false;
		}
		$cards = BootPHP::$config->load('credit_cards');
		// 检查卡的类型
		$type = strtolower($type);
		if (!isset($cards[$type]))
			return false;
		// 检查卡号长度
		$length = strlen($number);
		// 根据卡的类型验证卡号长度
		if (!in_array($length, preg_split('/\D+/', $cards[$type]['length'])))
			return false;
		// 检查卡号前缀
		if (!preg_match('/^' . $cards[$type]['prefix'] . '/', $number))
			return false;
		// 不需要 Luhn 检查
		if ($cards[$type]['luhn'] == false)
			return true;
		return Valid::luhn($number);
	}

	/**
	 * 验证数字是否满足 [Luhn](http://baike.baidu.com/view/7893503.htm) （模10）公式。
	 *
	 * @param string 要检查的数字
	 * @return boolean
	 */
	public static function luhn($number)
	{
		// 将值强制转换为字符串，因为这个方法使用字符串函数。
		// 转换为整数可能超过 PHP_INT_MAX，导致错误！
		$number = (string) $number;
		if (!ctype_digit($number))
		{
			// Luhn 只能用在数字上！
			return false;
		}
		// 检查数字长度
		$length = strlen($number);
		// 卡号校验
		$checksum = 0;
		for ($i = $length - 1; $i >= 0; $i -= 2)
		{
			// 从右往左，奇数位数字相加
			$checksum += substr($number, $i, 1);
		}
		for ($i = $length - 2; $i >= 0; $i -= 2)
		{
			// 从右往左，偶数位数字加倍后相加
			$double = substr($number, $i, 1) * 2;
			// 加倍后的值大于等于 10 时，减去 9
			$checksum += ($double >= 10) ? ($double - 9) : $double;
		}
		// 如果总和是 10 的倍数，这个数字就是有效的
		return ($checksum % 10 === 0);
	}

	/**
	 * 检查电话号码是否有效。
	 *
	 * @param string 要检查的电话号码
	 * @param array 电话号码长度范围
	 * @return boolean
	 */
	public static function phone($number, $lengths = NULL)
	{
		if (!is_array($lengths))
		{
			$lengths = array(7, 8, 11, 12);
		}
		// 从号码中移除所有非数字字符
		$number = preg_replace('/\D+/', '', $number);
		// 检查号码是否在长度范围内
		return in_array(strlen($number), $lengths);
	}

	/**
	 * 检查字符串是否为有效的日期字符串。
	 *
	 * @param string 要检查的日期
	 * @return boolean
	 */
	public static function date($str)
	{
		return (strtotime($str) !== false);
	}

	/**
	 * 检查字符串是否只包含字母。
	 *
	 * @param string 输入的字符串
	 * @return boolean
	 */
	public static function alpha($str)
	{
		return ctype_alpha((string) $str);
	}

	/**
	 * 检查字符串是否只包含字母和数字。
	 *
	 * @param string 输入的字符串
	 * @return boolean
	 */
	public static function alpha_numeric($str)
	{
		return ctype_alnum($str);
	}

	/**
	 * 检查字符串是否只包含字母、数字、下划线和破折号。
	 *
	 * @param string 输入的字符串
	 * @return boolean
	 */
	public static function alpha_dash($str)
	{
		$regex = '/^[-a-z0-9_]++$/iD';
		return (bool) preg_match($regex, $str);
	}

	/**
	 * 检查字符串是否只包含数字（没有点和破折号）。
	 *
	 * @param string 输入的字符串
	 * @return boolean
	 */
	public static function digit($str)
	{
		return (is_int($str) && $str >= 0) || ctype_digit($str);
	}

	/**
	 * 检查字符串是否是一个有效的数值（允许负数和十进制数）。
	 *
	 * 使用 {@link http://php.net/manual/zh/function.localeconv.php 区域转换} 来指定区域设置的小数点。
	 *
	 * @param string 输入的字符串
	 * @return boolean
	 */
	public static function numeric($str)
	{
		// 获取当前区域设置的小数点
		list($decimal) = array_values(localeconv());
		// 使用向前查找，以确保字符串包含至少一个数字（在小数点之前或者之后）
		return (bool) preg_match('/^-?+(?=.*[0-9])[0-9]*+' . preg_quote($decimal) . '?+[0-9]*+$/D', (string) $str);
	}

	/**
	 * 检查一个数字是否在范围内。
	 *
	 * @param string 要检查的数字
	 * @param integer 最小值
	 * @param integer 最大值
	 * @return boolean
	 */
	public static function range($number, $min, $max)
	{
		return $number >= $min && $number <= $max;
	}

	/**
	 * 检查字符串是否为正确的十进制格式。
	 * 可选，也可以检查指定的数字位数。
	 *
	 * @param string 要检查的数字
	 * @param integer 小数位数
	 * @param integer 数字位数
	 * @return boolean
	 */
	public static function decimal($str, $places = 2, $digits = NULL)
	{
		if ($digits > 0)
		{
			// 指定数字位数
			$digits = '{' . (int) $digits . '}';
		}
		else
		{
			// 任何数字位数
			$digits = '+';
		}
		// 获取当前设置的小数点
		list($decimal) = array_values(localeconv());
		return (bool) preg_match('/^[+-]?[0-9]' . $digits . preg_quote($decimal) . '[0-9]{' . ((int) $places) . '}$/D', $str);
	}

	/**
	 * 检查字符串是否是一个正确的十六进制 HTML 颜色值。
	 * 这个验证是相当灵活的，它不需要以“#”开始，而且可以使用三个十六制字符的简写形式来代替六个十六制进字符。
	 *
	 * @param string 输入的字符串
	 * @return boolean
	 */
	public static function color($str)
	{
		return (bool) preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $str);
	}

	/**
	 * 检查一个字段的值是否匹配另一个字段的值。
	 *
	 * @param array 值的数组
	 * @param string 字段名
	 * @param string 要匹配的字段名
	 * @return boolean
	 */
	public static function matches($array, $field, $match)
	{
		return $array[$field] === $array[$match];
	}

}
