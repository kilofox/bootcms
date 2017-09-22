<?php defined('SYSPATH') || exit('Access Denied.'); ?>
<style>
<?php include BootPHP::find_file('views', 'profiler/style', 'css'); ?>
</style>
<?php
$groupStats = Profiler::groupStats();
$groupCols = array('最小' => 'min', '最大' => 'max', '平均' => 'average', '总计' => 'total');
$applicationCols = array('min', 'max', 'average', 'current');
?>
<div class="bootphp">
	<?php foreach (Profiler::groups() as $group => $benchmarks): ?>
		<table class="profiler">
			<tr class="group">
				<th class="name" rowspan="2"><?php echo ucfirst($group); ?></th>
				<td class="time" colspan="4"><?php echo number_format($groupStats[$group]['total']['time'], 4); ?> <abbr>秒</abbr></td>
			</tr>
			<tr class="group">
				<td class="memory" colspan="4"><?php echo number_format($groupStats[$group]['total']['memory'] / 1024, 2); ?> <abbr>KB</abbr></td>
			</tr>
			<tr class="headers">
				<th class="name">基准</th>
				<?php foreach ($groupCols as $alias => $key): ?>
					<th class="<?php echo $key; ?>"><?php echo ucfirst($alias); ?></th>
				<?php endforeach ?>
			</tr>
			<?php foreach ($benchmarks as $name => $tokens): ?>
				<tr class="mark time">
					<?php $stats = Profiler::stats($tokens) ?>
					<th class="name" rowspan="2" scope="rowgroup"><?php echo HTML::chars($name), '（', count($tokens), '）'; ?></th>
					<?php foreach ($groupCols as $key): ?>
						<td class="<?php echo $key ?>">
							<div>
								<div class="value"><?php echo number_format($stats[$key]['time'], 4); ?> <abbr>秒</abbr></div>
								<?php if ($key === 'total'): ?>
									<div class="graph" style="left: <?php echo max(0, 100 - $stats[$key]['time'] / $groupStats[$group]['max']['time'] * 100); ?>%"></div>
								<?php endif ?>
							</div>
						</td>
					<?php endforeach ?>
				</tr>
				<tr class="mark memory">
					<?php foreach ($groupCols as $key): ?>
						<td class="<?php echo $key ?>">
							<div>
								<div class="value"><?php echo number_format($stats[$key]['memory'] / 1024, 2) ?> <abbr>KB</abbr></div>
								<?php if ($key === 'total'): ?>
									<div class="graph" style="left: <?php echo max(0, 100 - $stats[$key]['memory'] / $groupStats[$group]['max']['memory'] * 100); ?>%"></div>
								<?php endif ?>
							</div>
						</td>
					<?php endforeach ?>
				</tr>
			<?php endforeach ?>
		</table>
	<?php endforeach ?>
	<table class="profiler">
		<?php $stats = Profiler::application(); ?>
		<tr class="final mark time">
			<th class="name" rowspan="2" scope="rowgroup">应用执行（<?php echo $stats['count']; ?>）</th>
			<?php foreach ($applicationCols as $key): ?>
				<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['time'], 4); ?> <abbr>秒</abbr></td>
			<?php endforeach ?>
		</tr>
		<tr class="final mark memory">
			<?php foreach ($applicationCols as $key): ?>
				<td class="<?php echo $key ?>"><?php echo number_format($stats[$key]['memory'] / 1024, 2); ?> <abbr>KB</abbr></td>
			<?php endforeach ?>
		</tr>
	</table>
</div>