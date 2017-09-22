<div id="site_content">
	<div class="sidebar">
		<h3>产品分类</h3>
		<ul>
			<?php foreach ($categories as $cate): ?>
				<li><a href="<?php echo $homeUrl; ?>product/index/<?php echo $cate->id; ?>/"><?php echo $cate->name; ?></a></li>
			<?php endforeach; ?>
		</ul>
		<h3>搜索</h3>
		<p>
		<form id="search_form" action="<?php echo $homeUrl; ?>homepage/search/" method="get">
			<input type="text" class="search" name="q" placeholder="关键字" />
			<input type="image" id="search" style="border: 0; margin: 0 0 -9px 5px;" src="<?php echo $homeUrl; ?>assets/images/search.png" alt="搜索" title="搜索" />
		</form>
		</p>
	</div>
	<div id="content">
		<h1>产品列表</h1>
		<?php if (count($products)): ?>
			<?php foreach ($products as $node): ?>
				<span class="left"><a href="<?php echo $homeUrl; ?>product/entry/<?php echo $node->id; ?>/"><img src="<?php echo $homeUrl; ?>assets/uploads/product/<?php echo $node->thumb; ?>" /></a></span>
				<h2><a href="<?php echo $homeUrl; ?>product/entry/<?php echo $node->id; ?>/"><?php echo $node->product_name; ?></a></h2>
				<?php if ($node->promote == '1' && $node->promotion_price): ?>
					<p>促销价：<strong>&yen;<?php echo $node->promotion_price; ?></strong></p>
				<?php else: ?>
					<p>商品价：<strong>&yen;<?php echo $node->commodity_price; ?></strong></p>
				<?php endif; ?>
				<?php mb_strlen($node->introduce) > 56 and $node->introduce = mb_substr($node->introduce, 0, 56) . '...'; ?>
				<p><?php echo $node->introduce; ?></p>
			<?php endforeach; ?>
		<?php else: ?>
			<p>该分类下暂无产品</p>
		<?php endif; ?>
	</div>
</div>
