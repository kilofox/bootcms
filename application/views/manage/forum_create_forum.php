<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">创建新版块</span>
</header>
<article class="module width_full">
	<header><h3>创建新版块</h3></header>
	<form id="new_form">
		<div class="module_content field">
			<ul>
				<li>
					<label>版块名称</label>
					<input type="text" name="name" value="" />
				</li>
				<li>
					<label>上级分类</label>
					<select type="text" name="cat_id">
						<?php foreach ($categories as $cat): ?>
							<option value="<?php echo $cat->id; ?>"><?php echo $cat->name; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>描述</label>
					<textarea name="descr" class="textarea"></textarea>
				</li>
				<li>
					<label>状态</label>
					<input type="checkbox" name="status" value="1" checked="checked" /> 打开
				</li>
				<li>
					<label>版主</label>
					<input type="text" name="moderators" value="" /> 用户名（不是昵称），用英文逗号分隔。
				</li>
				<li>
					<label>隐藏版主列表</label>
					<input type="checkbox" name="hide_mods_list" value="1" /> 是
				</li>
				<li>
					<label>查看版块</label>
					<select type="text" name="auth0">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 0): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>阅读主题</label>
					<select type="text" name="auth1">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 0): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>发表新主题</label>
					<select type="text" name="auth2">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 1): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>回复</label>
					<select type="text" name="auth3">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 1): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>编辑别人的帖子</label>
					<select type="text" name="auth4">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 2): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>移动主题</label>
					<select type="text" name="auth5">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 2): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>删除主题和回复</label>
					<select type="text" name="auth6">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 2): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>锁定主题</label>
					<select type="text" name="auth7">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 2): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>置顶主题</label>
					<select type="text" name="auth8">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 2): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>以 HTML 格式发表帖子（危险）</label>
					<select type="text" name="auth9">
						<?php foreach ($userLevels as $k => $lv): ?>
							<option value="<?php echo $k; ?>"<?php if ($k === 3): ?> selected="selected"<?php endif; ?>><?php echo $lv; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="submit" value="创建版块" class="alt_btn" />
			</div>
		</footer>
	</form>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			refresh = false;
		$('#new_form').submit(function() {
			var forumName = $('#forum_name').val();
			$.post(homeUrl + 'manage3/create_forum', $(this).serialize(),
				function(r) {
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
