<div id="site_content">
	<div class="sidebar">
		<?php echo $sidebar; ?>
		<h3>搜索</h3>
		<p>
		<form id="search_form" action="<?php echo $homeUrl; ?>homepage/search/" method="get">
			<input type="text" name="q" value="" placeholder="关键字" class="search" />
			<input type="image" id="search" style="border: 0; margin: 0 0 -9px 5px;" src="<?php echo $homeUrl; ?>assets/images/search.png" alt="搜索" title="搜索" />
		</form>
		</p>
	</div>
	<div id="content">
		<h1><?php echo $node->node_title; ?></h1>
		<?php if ($node->node_intro): ?>
			<h3><?php echo $node->node_intro; ?></h3>
		<?php endif; ?>
		<?php echo $node->node_content; ?>
	</div>
</div>
<script>
	$(function() {
		$('#search_form').submit(function() {
			if (!$('input[name="q"]').val())
				return false;
		});
	});
</script>