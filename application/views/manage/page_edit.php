<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage/list_pages">单页管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">编辑内容</span>
</header>
<article class="module width_full">
	<header>
		<h3 class="tabs_involved">编辑单页</h3>
		<ul class="tabs">
			<li><a href="<?php echo $homeUrl . $node->slug; ?>" target="_blank">查看单页</a></li>
		</ul>
	</header>
	<form>
		<div class="module_content field">
			<ul>
				<li>
					<label><?php echo $homeUrl; ?></label>
					<input type="text" name="slug" maxlength="32" value="<?php echo $node->slug; ?>" />
				</li>
				<li>
					<label>标题</label>
					<input type="text" name="node_title" value="<?php echo HTML::chars($node->node_title); ?>" placeholder="该页的标题" class="input" />
				</li>
				<li>
					<label>关键词</label>
					<input type="text" name="keywords" maxlength="255" value="<?php echo HTML::chars($node->keywords); ?>" placeholder="该页的 Meta Keywords" class="input" />
				</li>
				<li>
					<label>描述</label>
					<input type="text" name="descript" maxlength="255" value="<?php echo HTML::chars($node->descript); ?>" placeholder="该页的 Meta Description" class="input" />
				</li>
				<li>
					<label>单页介绍 - 单页上方的内容</label>
					<textarea name="node_intro" placeholder="该页的介绍" class="textarea" /><?php echo $node->node_intro; ?></textarea>
				</li>
				<li>
					<label>单页内容 - 单页的主体</label>
					<textarea id="editor_id" name="node_content" placeholder="该页的主要内容" /><?php echo $node->node_content; ?></textarea>
				</li>
				<?php if ($node->type < 3): ?>
					<li style="width:48%; float:left; margin-right:3%;">
						<label>子菜单</label>
						<select name="submenu">
							<option value="0">无</option>
							<?php foreach ($blocks as $block): ?>
								<?php if ($block->region_id == 1): ?>
									<?php $selected = $node->submenu == $block->id ? ' selected="selected"' : ''; ?>
									<option value="<?php echo $block->id; ?>"<?php echo $selected; ?>><?php echo $block->block_title; ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</li>
				<?php endif; ?>
				<li style="width:48%; float:left;">
					<label>讨论设置</label>
					<input name="commenting" value="1" type="checkbox" class="normal-input"<?php if ($node->commenting == '1'): ?> checked="checked"<?php endif; ?>/>允许评论
				</li>
				<li style="width:48%; float:left; margin-right:3%;">
					<label for="created_m">发布日期</label>
					<?php echo Functions::date_form(NULL, 'time_', 1, 4); ?>
				</li>
				<li style="width:48%; float:left;">
					<label for="status">单页状态</label>
					<?php echo Admin::publishing_options($node->status, $level, 'page', 'edit'); ?>
				</li>
				<li style="width:48%; float:left;">
					<label>侧边栏</label>
					<select name="sidebar">
						<option value="0">无</option>
						<?php foreach ($blocks as $block): ?>
							<?php if ($block->region_id <> 1): ?>
								<?php $selected = $node->sidebar == $block->id ? ' selected="selected"' : ''; ?>
								<option value="<?php echo $block->id; ?>"<?php echo $selected; ?>><?php echo $block->block_title; ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</li>
				<li class="clear"></li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="hidden" id="node_id" name="node_id" value="<?php echo $node->id; ?>" />
				<input type="submit" value="编辑单页" class="alt_btn" />
				<input type="button" id="trash" value="放进垃圾筒" />
				<input type="button" id="showAll" value="显示所有" />
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
			toList = false;
		$('form').submit(function() {
			editor.sync();
			$('input[type="submit"]').prop('disabled', true);
			$.post(homeUrl + 'manage/edit_page', $('form').serialize(), function(r) {
				showMsg(r.title, r.content);
				if (r.status == 1)
					window.location.href = homeUrl + 'manage/edit_page/' + $('#node_id').val();
				else if (r.status == 2)
					toList = true;
				$('input[type="submit"]').prop('disabled', false);
			}, 'json');
			return false;
		});
		$('#showAll').click(function() {
			window.location.href = homeUrl + 'manage/list_pages';
		});
		$('#trash').click(function() {
			if (confirm('您确定要将该单页放入垃圾筒吗？')) {
				$.post(homeUrl + 'manage/trash_page', {
					"node_id": $('#node_id').val()
				}, function(r) {
					showMsg(r.title, r.content);
					toList = true;
				}, 'json');
			}
		});
		$('body').on('click', '.close-me', function() {
			if (toList)
				window.location.href = homeUrl + 'manage/list_pages';
		});
	});
</script>