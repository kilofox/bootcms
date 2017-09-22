<style>
	.smiley{display:inline-block;}
	.smiley img{cursor:pointer;}
</style>
<div id="site_content">
	<h1>移动主题</h1>
	<form id="post_form">
		<div class="form_settings">
			<p><span>主题</span><a href="<?php echo $homeUrl; ?>forum/topic/<?php echo $topic->id; ?>/"><?php echo $topic->topic_title; ?></a></p>
			<p><span>旧版块</span><a href="<?php echo $homeUrl; ?>forum/forum/<?php echo $topic->forum_id; ?>/"><?php echo $topic->forum_name; ?></a></p>
			<p><span>新版块</span>
				<select name="dest_forum">
					<?php foreach ($forums as $node): ?>
						<?php if ($node->id != $topic->forum_id): ?>
							<option value="<?php echo $node->id; ?>"><?php echo $node->name; ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</p>
			<p style="padding-top: 15px">
				<span>&nbsp;</span>
				<input type="hidden" name="tid" value="<?php echo $topic->id; ?>"/>
				<input type="submit" name="submit" value="提交" class="submit"/>
			</p>
		</div>
	</form>
</div>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		$('#post_form').submit(function() {
			$.post(homeUrl + 'forum/move_topic/', $('#post_form').serialize(), function(r) {
				$('input[type="submit"]').prop('disabled', true);
				if (r.status === 1) {
					$('input[type="submit"]').prop('disabled', false);
					window.location.href = homeUrl + 'forum/topic/' + r.content + '/';
				} else {
					$('input[type="submit"]').prop('disabled', false);
					showMsg(r.title, r.content);
				}
			}, 'json');
			return false;
		});
	});
</script>