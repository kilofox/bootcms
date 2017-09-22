<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">选择媒体分组</span>
</header>
<article class="module width_3_quarter">
	<header><h3 class="tabs_involved">媒体分组列表</h3></header>
	<div class="module_content">
		<?php foreach ($groups as $group): ?>
			<article class="stats_overview">
				<p><a href="<?php echo $homeUrl; ?>manage/list_media/<?php echo $group->id; ?>/"><?php echo $group->group_name; ?></a></p>
				<p style="padding-top:50px;"><?php echo Functions::makeDate($group->created, 'Y-m-d H:i'); ?></p>
				<p><a data-edit-group="<?php echo $group->id; ?>">编辑</a> <a data-delete-group="<?php echo $group->id; ?>">删除</a></p>
			</article>
		<?php endforeach; ?>
	</div>
</article>
<article class="module width_quarter" data-area="mg_create">
	<header><h3>创建媒体分组</h3></header>
	<div class="message_list">
		<div class="module_content">
			<label>别名</label>
			<input type="text" id="create_slug" />
			<label>媒体分组名称</label>
			<input type="text" id="create_name" />
			<label>媒体宽度</label>
			<input type="text" id="create_width" /> px
			<label>媒体高度</label>
			<input type="text" id="create_height" /> px
			<label>缩略图宽度</label>
			<input type="text" id="create_tn_width" /> px
			<label>缩略图高度</label>
			<input type="text" id="create_tn_height" /> px
		</div>
	</div>
	<footer>
		<div class="submit_link">
			<input type="submit" id="create_btn" value="创建" class="alt_btn" />
			<input type="reset" value="重置" class="btn" />
		</div>
	</footer>
</article>
<article class="module width_quarter" data-area="mg_edit">
	<header><h3>编辑媒体分组</h3></header>
	<div class="message_list">
		<div class="module_content">
			<label>别名</label>
			<input type="text" id="edit_slug" disabled="disabled" />
			<label>媒体分组名称</label>
			<input type="text" id="edit_name" />
			<label>媒体宽度</label>
			<input type="text" id="edit_rs_width" /> px
			<label>媒体高度</label>
			<input type="text" id="edit_rs_height" /> px
			<label>缩略图宽度</label>
			<input type="text" id="edit_tn_width" /> px
			<label>缩略图高度</label>
			<input type="text" id="edit_tn_height" /> px
		</div>
	</div>
	<footer>
		<div class="submit_link">
			<input type="hidden" id="edit_id" />
			<input type="submit" id="edit_btn" value="更新" class="alt_btn" />
			<input type="reset" value="重置" class="btn" />
		</div>
	</footer>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			from = '<?php echo Request::current()->action(); ?>',
			refresh = false;
		$('article[data-area="mg_edit"]').hide();
		$('#create_btn').click(function() {
			$.post(homeUrl + 'manage/create_media_group', {
				"slug": $('#create_slug').val(),
				"group_name": $('#create_name').val(),
				"rs_width": $('#create_width').val(),
				"rs_height": $('#create_height').val(),
				"tn_width": $('#create_tn_width').val(),
				"tn_height": $('#create_tn_height').val(),
				"from": from
			}, function(r) {
				if (r.status == 1)
				{
					$('article[data-area="group_create"]').hide();
					refresh = true;
				}
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('a[data-edit-group]').click(function() {
			$('article[data-area="mg_create"]').hide();
			$('article[data-area="mg_edit"]').show();
			$.post(homeUrl + 'manage/edit_media_group', {
				"group_id": $(this).attr('data-edit-group'),
				"action": "get_info",
				"from": from
			}, function(r) {
				if (r.data) {
					$('#edit_id').val(r.data.id);
					$('#edit_slug').val(r.data.slug);
					$('#edit_name').val(r.data.group_name);
					$('#edit_rs_width').val(r.data.rs_width);
					$('#edit_rs_height').val(r.data.rs_height);
					$('#edit_tn_width').val(r.data.tn_width);
					$('#edit_tn_height').val(r.data.tn_height);
				} else {
					showMsg(r.title, r.content);
				}
			}, 'json');
		});
		$('#edit_btn').click(function() {
			$.post(homeUrl + 'manage/edit_media_group', {
				"group_id": $('#edit_id').val(),
				"group_name": $('#edit_name').val(),
				"rs_width": $('#edit_rs_width').val(),
				"rs_height": $('#edit_rs_height').val(),
				"tn_width": $('#edit_tn_width').val(),
				"tn_height": $('#edit_tn_height').val(),
				"from": from
			}, function(r) {
				if (r.status == 1) {
					$('article[data-area="mg_edit"]').hide();
					$('article[data-area="mg_create"]').show();
					refresh = true;
				}
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('a[data-delete-group]').click(function() {
			if (confirm('您确定要删除该媒体分组吗？')) {
				$.post(homeUrl + 'manage/delete_media_group', {
					"group_id": $(this).attr('data-delete-group'),
					"from": from
				}, function(r) {
					if (r.status == 1) {
						refresh = true;
					}
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage/' + from;
		});
		$('#create_slug').blur(function() {
			var re = /^[a-z0-9-]+$/;
			if (!re.test($(this).val())) {
				showMsg('别名错误', '别名仅支持小写字母、数字、中划线。');
			}
		});
	});
</script>
