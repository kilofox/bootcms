<div id="site_content">
	<div class="sidebar">
		<h3>搜索</h3>
		<p>
		<form id="search_form" action="<?php echo $homeUrl; ?>homepage/search/" method="get">
			<input type="text" name="q" value="<?php echo $query; ?>" placeholder="关键字" class="search" />
			<input type="image" id="search" style="border: 0; margin: 0 0 -9px 5px;" src="<?php echo $homeUrl; ?>assets/images/search.png" alt="搜索" title="搜索" />
		</form>
		</p>
	</div>
	<div id="content">
		<h1>搜索结果</h1>
		<?php if ($nodes): ?>
			<ul>
				<?php foreach ($nodes as $node): ?>
					<li><a href="<?php echo $homeUrl . $node->slug; ?>" target="_blank"><?php echo $node->node_title; ?></a></li>
				<?php endforeach; ?>
			</ul>
			<div class="page"><?php echo $pagination; ?></div>
		<?php else: ?>
			<p>抱歉，没有找到与“<?php echo $query; ?>”相关的内容。</p>
		<?php endif; ?>
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