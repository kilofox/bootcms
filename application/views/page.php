<div id="site_content">
	<div class="sidebar">
		<?php if ($node->sidebar) echo $sidebar; ?>
		<h3>搜索</h3>
		<p>
		<form id="search_form" action="<?php echo $homeUrl; ?>homepage/search/" method="get">
			<input type="text" name="q" placeholder="关键字" class="search" />
			<input type="image" id="search" style="border: 0; margin: 0 0 -9px 5px;" src="<?php echo $homeUrl; ?>assets/images/search.png" alt="搜索" title="搜索" />
		</form>
		</p>
	</div>
	<div id="content">
		<h1><?php echo $node->node_title; ?></h1>
		<?php if ($node->node_intro): ?>
			<h3><?php echo $node->node_intro; ?></h3>
		<?php endif; ?>
		<p><?php echo $node->node_content; ?></p>
		<?php if ($node->commenting && is_array($comments)): ?>
			<?php foreach ($comments as $c): ?>
				<blockquote>
					<h5><?php echo $c->nickname; ?> 发表于 <span><?php echo Functions::makeDate($c->created, 'Y-m-d H:i'); ?></span></h5>
					<?php echo $c->comment; ?>
				</blockquote>
			<?php endforeach; ?>
			<p class="page"><?php echo $pagination; ?></p>
			<p><a id="post_comment" class="pointer">发表评论</a></p>
			<div id="comment_area">
				<form id="comment_form">
					<div class="form_settings">
						<p>
							<span>评论内容</span>
							<textarea name="content" rows="8" cols="50"></textarea>
						</p>
						<p style="padding-top: 15px">
							<span><input type="hidden" name="node_id" value="<?php echo $node->id; ?>" />&nbsp;</span>
							<input type="submit" value="发表评论" class="submit" />
						</p>
					</div>
				</form>
			</div>
		<?php endif; ?>
	</div>
</div>
<script>
	$(function() {
		$('#search_form').submit(function() {
			if (!$('input[name="q"]').val())
				return false;
		});
<?php if ($node->commenting): ?>
			var homeUrl = '<?php echo $homeUrl; ?>';
			$('#comment_area').hide();
			$('#post_comment').click(function() {
				$('#comment_area').toggle(500);
			});
			$('#comment_form').submit(function() {
				if (!$('textarea[name="content"]').val()) {
					$('textarea[name="content"]').focus();
					return false;
				}
				$('input[type="submit"]').prop('disabled', true);
				$.post(homeUrl + '<?php echo $node->slug; ?>/comment', $(this).serialize(), function(r) {
					if (r.status == 1) {
						window.location.href = homeUrl + '<?php echo $node->slug; ?>';
					} else {
						showMsg(r.title, r.content);
						$('input[type="submit"]').prop('disabled', false);
					}
				}, 'json');
				return false;
			});
<?php endif; ?>
	});
</script>