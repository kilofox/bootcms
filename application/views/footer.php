<div id="content_footer"></div>
<div id="footer">
	<?php echo Setup::copyright(); ?>
	<?php if (BootPHP::$profiling === true): ?>
		<?php $stats = Profiler::application(); ?>
		用时 <?php echo number_format($stats['current']['time'], 4); ?> 秒，内存 <?php echo number_format($stats['current']['memory'] / 1024, 2); ?> KB
	<?php endif; ?>
</div>
</div>