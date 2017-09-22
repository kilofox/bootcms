<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage3/list_forums">管理版块</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">编辑版块</span>
</header>
<article class="module width_full">
	<header><h3>编辑版块 <?php echo $node->name; ?></h3></header>
	<form id="edit_form">
		<div class="module_content field">
			<ul>
				<li>
					<label>版块名称</label>
					<input type="text" name="name" value="<?php echo $node->name; ?>" />
				</li>
				<li>
					<label>上级分类</label>
					<select type="text" name="cat_id">
						<?php foreach ($categories as $cat): ?>
							<option value="<?php echo $cat->id; ?>"<?php if ($cat->id === $node->cat_id): ?> selected="selected"<?php endif; ?>><?php echo $cat->name; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>描述</label>
					<textarea name="descr" class="textarea"><?php echo $node->descr; ?></textarea>
				</li>
				<li>
					<label>状态</label>
					<input type="checkbox" name="status" value="1"<?php if ($node->status == '1'): ?> checked="checked"<?php endif; ?>/> 打开
				</li>
				<li>
					<label>版主</label>
					<input type="text" name="moderators" value="<?php echo $node->moderators; ?>" /> 用户名（不是昵称），用英文逗号分隔。
				</li>
				<li>
					<label>隐藏版主列表</label>
					<input type="checkbox" name="hide_mods_list" value="<?php echo $node->hide_mods_list; ?>" /> 是
				</li>
				<li>
					<label>查看版块</label>
					<select type="text" name="auth0">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth0): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>阅读主题</label>
					<select type="text" name="auth1">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth1): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>发表新主题</label>
					<select type="text" name="auth2">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth2): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>回复</label>
					<select type="text" name="auth3">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth3): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>编辑别人的帖子</label>
					<select type="text" name="auth4">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth4): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>移动主题</label>
					<select type="text" name="auth5">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth5): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>删除主题和回复</label>
					<select type="text" name="auth6">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth6): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>锁定主题</label>
					<select type="text" name="auth7">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth7): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>置顶主题</label>
					<select type="text" name="auth8">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth8): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>以 HTML 格式发表帖子（危险）</label>
					<select type="text" name="auth9">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k == $node->auth9): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="hidden" name="cid" value="<?php echo $node->id; ?>" />
				<input type="submit" name="publish" value="编辑" class="alt_btn" />
				<input type="reset" value="重置" />
			</div>
		</footer>
	</form>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			refresh = false;
		$('#edit_form').submit(function() {
			$.post(homeUrl + 'manage3/edit_forum', $(this).serialize(), function(r) {
				showMsg(r.title, r.content);
				if (r.status === 1)
					refresh = true;
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage3/list_forums';
		});
	});
</script>