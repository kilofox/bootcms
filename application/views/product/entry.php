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
		<h2><?php echo $node->product_name; ?></h2>
		<?php if ($node->introduce): ?>
			<h2><?php echo $node->introduce; ?></h2>
		<?php endif; ?>
		<table style="width:100%; border-spacing:0;">
			<?php if (is_array($node->item1) && count($node->item1) == 2): ?>
				<tr>
					<td><?php echo $node->item1[0]; ?></td>
					<td><?php echo $node->item1[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if (is_array($node->item2) && count($node->item2) == 2): ?>
				<tr>
					<td><?php echo $node->item2[0]; ?></td>
					<td><?php echo $node->item2[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if (is_array($node->item3) && count($node->item3) == 2): ?>
				<tr>
					<td><?php echo $node->item3[0]; ?></td>
					<td><?php echo $node->item3[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if (is_array($node->item4) && count($node->item4) == 2): ?>
				<tr>
					<td><?php echo $node->item4[0]; ?></td>
					<td><?php echo $node->item4[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if (is_array($node->item5) && count($node->item5) == 2): ?>
				<tr>
					<td><?php echo $node->item5[0]; ?></td>
					<td><?php echo $node->item5[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if (is_array($node->item6) && count($node->item6) == 2): ?>
				<tr>
					<td><?php echo $node->item6[0]; ?></td>
					<td><?php echo $node->item6[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if (is_array($node->item7) && count($node->item7) == 2): ?>
				<tr>
					<td><?php echo $node->item7[0]; ?></td>
					<td><?php echo $node->item7[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if (is_array($node->item8) && count($node->item8) == 2): ?>
				<tr>
					<td><?php echo $node->item8[0]; ?></td>
					<td><?php echo $node->item8[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if (is_array($node->item9) && count($node->item9) == 2): ?>
				<tr>
					<td><?php echo $node->item9[0]; ?></td>
					<td><?php echo $node->item9[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if (is_array($node->item10) && count($node->item10) == 2): ?>
				<tr>
					<td><?php echo $node->item10[0]; ?></td>
					<td><?php echo $node->item10[1]; ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($node->commodity_price): ?>
				<?php if ($node->promote == '1' && $node->promotion_price) $node->commodity_price = '<del>' . $node->commodity_price . '</del>'; ?>
				<tr>
					<td>产品价格</td>
					<td><?php echo $node->commodity_price; ?> 元</td>
				</tr>
			<?php endif; ?>
			<?php if ($node->promote == '1' && $node->promotion_price): ?>
				<tr>
					<td>促销价格</td>
					<td><?php echo $node->promotion_price; ?> 元</td>
				</tr>
			<?php endif; ?>
		</table>
		<div id="showcase" class="showcase">
			<?php foreach ($node->pictures as $pic): ?>
				<div class="showcase-slide">
					<div class="showcase-content">
						<img src="<?php echo $homeUrl; ?>assets/uploads/product/<?php echo $pic[0]; ?>" data-bind="<?php echo $homeUrl; ?>assets/uploads/product/<?php echo $pic[0]; ?>" alt="<?php echo $node->product_name; ?>" />
					</div>
					<div class="showcase-thumbnail">
						<img src="<?php echo $homeUrl; ?>assets/uploads/product/<?php echo $pic[1]; ?>" data-bind="<?php echo $homeUrl; ?>assets/uploads/product/<?php echo $pic[0]; ?>" alt="<?php echo $node->product_name; ?>" />
						<div class="showcase-thumbnail-caption"></div>
						<div class="showcase-thumbnail-cover"></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<form id="buy_form">
			<div class="form_settings">
				<p style="padding-top: 15px">
					<span><input type="hidden" name="pid" value="<?php echo $node->id; ?>" />&nbsp;</span>
					<input class="submit" type="submit" value="放入购物车" />
				</p>
			</div>
		</form>
	</div>
</div>
<link href="<?php echo $homeUrl; ?>assets/css/showcase.css" rel="stylesheet" />
<script src="<?php echo $homeUrl; ?>assets/js/showcase.min.js"></script>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			refresh = false;
		$('#buy_form').submit(function() {
			$.post(homeUrl + 'cart/addto', $('#buy_form').serialize(), function(r) {
				if (r.status == 1)
					window.location.href = homeUrl + 'cart';
				else if (r.status == 2)
					window.location.href = homeUrl + 'member/login';
				else
					showMsg(r.title, r.content);
			}, 'json');
			return false;
		});
		$('#thumb a').click(function(event) {
			var imgSrc = $(this).find('img').attr('data-bind');
			$('#big img').attr('src', imgSrc);
			event.preventDefault();
		});
		$('#showcase').awShowcase({
			content_width: 595,
			content_height: 397,
			fit_to_parent: false,
			auto: false,
			interval: 3000,
			continuous: false,
			loading: true,
			tooltip_width: 200,
			tooltip_icon_width: 32,
			tooltip_icon_height: 32,
			tooltip_offsetx: 18,
			tooltip_offsety: 0,
			arrows: false,
			buttons: false,
			btn_numbers: false,
			keybord_keys: true,
			mousetrace: false,
			pauseonover: true,
			stoponclick: true,
			transition: 'hslide',
			transition_delay: 300,
			transition_speed: 500,
			show_caption: 'onhover',
			thumbnails: true,
			thumbnails_position: 'outside-last',
			thumbnails_direction: 'horizontal',
			thumbnails_slidex: 0,
			dynamic_height: false,
			speed_change: false,
			viewline: false
		});
	});
</script>