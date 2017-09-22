<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">创建新内容</span>
</header>
<article class="module width_full">
	<header><h3>创建单页</h3></header>
	<form>
		<div class="module_content field">
			<ul>
				<li>
					<label><?php echo $homeUrl; ?></label>
					<input type="text" name="slug" maxlength="32" value="" placeholder="别名" />
				</li>
				<li>
					<label>单页标题</label>
					<input type="text" name="node_title" value="" placeholder="内容标题" class="input" />
				</li>
				<li>
					<label>关键词</label>
					<input type="text" name="keywords" maxlength="255" value="" placeholder="该页的 Meta Keywords" class="input" />
				</li>
				<li>
					<label>描述</label>
					<input type="text" name="descript" maxlength="255" value="" placeholder="该页的 Meta Description" class="input" />
				</li>
				<li>
					<label>单页介绍</label>
					<textarea name="node_intro" placeholder="内容简介" class="textarea" /></textarea>
				</li>
				<li>
					<label>单页内容</label>
					<textarea id="editor_id" name="node_content" placeholder="该页的主要内容" /></textarea>
				</li>
				<li style="width:48%; float:left; margin-right:3%;">
					<label>子菜单</label>
					<select name="submenu">
						<option value="0">无</option>
						<?php foreach ($blocks as $block): ?>
							<?php if ($block->region_id == 1): ?>
								<option value="<?php echo $block->id; ?>"><?php echo $block->block_title; ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</li>
				<li style="width:48%; float:left;">
					<label>讨论设置</label>
					<input name="commenting" value="1" type="checkbox" class="normal-input" />允许评论
				</li>
				<li style="width:48%; float:left; margin-right:3%;">
					<label for="created_m">发布日期</label>
					<?php echo Functions::date_form(NULL, 'time_', 1, 4); ?>
				</li>
				<li style="width:48%; float:left;">
					<label for="status">单页状态</label>
					<?php echo Admin::publishing_options(1, $level, 'page', 'create'); ?>
				</li>
				<li style="width:48%; float:left;">
					<label>侧边栏</label>
					<select name="sidebar">
						<option value="0">无</option>
						<?php foreach ($blocks as $block): ?>
							<?php if ($block->region_id <> 1): ?>
								<option value="<?php echo $block->id; ?>"><?php echo $block->block_title; ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</li>
				<li class="clear"></li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="submit" id="publish" value="发布" class="alt_btn" />
				<input type="button" id="showAll" value="显示所有" class="alt-btn" />
			</div>
		</footer>
	</form>
</article>
<script src="<?php echo $homeUrl; ?>assets_manage/editor/kindeditor.js"></script>
<script src="<?php echo $homeUrl; ?>assets_manage/editor/lang/zh_CN.js"></script>
<script>
	KindEditor.ready(function(K) {
		window.editor = K.create('#editor_id', {
			height: '500px'
		});
	});
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			refresh = false;
		$('form').submit(function() {
			editor.sync();
			$('input[type="submit"]').prop('disabled', true);
			$.post(homeUrl + 'manage/create_page', $('form').serialize(), function(r) {
				showMsg(r.title, r.content);
				if (r.status == 1)
					refresh = true;
				$('input[type="submit"]').prop('disabled', false);
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage/list_pages';
		});
		$('#showAll').click(function() {
			window.location.href = homeUrl + 'manage/list_pages';
		});
	});
</script>
