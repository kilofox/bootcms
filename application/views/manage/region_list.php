<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">管理菜单与碎片</span>
</header>
<article class="module width_3_quarter" data-area="list">
	<header>
		<h3 class="tabs_involved">区域管理</h3>
		<ul class="tabs">
			<li><a data-show="region_create">创建区域</a></li>
			<li><a data-show="block_create">创建块</a></li>
		</ul>
	</header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>块名称</th>
					<th>排序</th>
					<th>类型</th>
					<th>所属区域</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
			<tr>
				<td colspan="5" class="tar"><span id="page"></span></td>
			</tr>
		</table>
	</div>
</article>
<article class="module width_quarter" data-area="region_create">
	<header><h3>创建区域</h3></header>
	<div class="message_list">
		<div class="module_content">
			<label>类型</label>
			<select id="region_create_type">
				<option value="">请选择</option>
				<option value="0">菜单</option>
				<option value="1">碎片</option>
			</select>
			<label>名称</label>
			<input type="text" id="region_create_title" value="" />
		</div>
	</div>
	<footer>
		<div class="submit_link">
			<input type="submit" id="region_create_btn" value="创建" class="alt_btn" />
		</div>
	</footer>
</article>
<article class="module width_3_quarter" data-area="block_create">
	<header><h3>创建块</h3></header>
	<div class="module_content field">
		<ul>
			<li>
				<label>类型</label>
				<select id="block_create_region">
					<option value="">请选择</option>
					<?php foreach ($regions as $region): ?>
						<option value="<?php echo $region->id; ?>"><?php echo $region->region_title; ?></option>
					<?php endforeach; ?>
				</select>
			</li>
			<li>
				<label>块的名称</label>
				<input type="text" id="block_create_title" value="" />
			</li>
			<li>
				<label>块的内容</label>
				<textarea id="block_create_content" class="textarea"></textarea>
			</li>
			<li>
				<label>排序</label>
				<input type="text" id="block_create_order" value="" />
			</li>
			<li>
				<label>状态</label>
				<input type="radio" name="block_create_status" value="0" /> 显示
				<input type="radio" name="block_create_status" value="1" /> 隐藏
			</li>
		</ul>
	</div>
	<footer>
		<div class="submit_link">
			<input type="submit" id="block_create_btn" value="创建" class="alt_btn" />
			<input type="button" id="block_create_cancel_btn" value="取消" />
		</div>
	</footer>
</article>
<article class="module width_quarter" data-area="region_edit">
	<header><h3>编辑区域</h3></header>
	<div class="message_list">
		<div class="module_content">
			<label>类型</label>
			<select id="region_edit_type" disabled="disabled">
				<option value="0">菜单</option>
				<option value="1">碎片</option>
			</select>
			<label>区域名称</label>
			<input type="text" id="region_edit_title" value="" />
		</div>
	</div>
	<footer>
		<div class="submit_link">
			<input type="hidden" id="region_edit_id" value="" />
			<input type="submit" id="region_edit_btn" value="更新" class="alt_btn" />
			<input type="button" id="region_delete_btn" value="删除" class="btn" />
		</div>
	</footer>
</article>
<article class="module width_3_quarter" data-area="block_edit">
	<header><h3>编辑块</h3></header>
	<div class="module_content field">
		<ul>
			<li>
				<label>所属区域</label>
				<select id="block_edit_region">
					<?php foreach ($regions as $region): ?>
						<option value="<?php echo $region->id; ?>"><?php echo $region->region_title; ?></option>
					<?php endforeach; ?>
				</select>
			</li>
			<li>
				<label>块的名称</label>
				<input type="text" id="block_edit_title" value="" />
			</li>
			<li>
				<label>块的内容</label>
				<textarea id="block_edit_content" class="textarea"></textarea>
			</li>
			<li>
				<label>排序</label>
				<input type="text" id="block_edit_order" value="" />
			</li>
			<li>
				<label>状态</label>
				<input type="radio" name="block_edit_status" value="0" /> 显示
				<input type="radio" name="block_edit_status" value="1" /> 隐藏
			</li>
		</ul>
	</div>
	<footer>
		<div class="submit_link">
			<input type="hidden" id="block_edit_id" value="" />
			<input type="submit" id="block_edit_btn" value="更新" class="alt_btn" />
			<input type="button" id="block_edit_cancel_btn" value="取消" />
		</div>
	</footer>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		from = '<?php echo Request::current()->action(); ?>',
		refresh = false;
	var page = 1;
	var requestData = function() {
		$.post(homeUrl + 'manage/' + from, {
			"page": page
		}, function(r) {
			$('#dataList').html(r.data);
			$('#page').html(r.pagination);
		}, 'json');
	};
	$(function() {
		$('article[data-area]:not([data-area="list"])').hide();
		requestData();
		$('a[data-show]').click(function() {
			$(this).parent().addClass('active').siblings().removeClass('active');
			$('article[data-area]:not([data-area="list"])').hide();
			$('article[data-area="' + $(this).attr('data-show') + '"]').show();
			if ($(this).attr('data-show') == 'block_create')
				$('article[data-area="list"]').hide();
		});
		$('#block_create_cancel_btn').click(function() {
			$('article[data-area="block_create"]').hide();
			$('article[data-area="list"]').show();
		});
		$('#block_edit_cancel_btn').click(function() {
			$('article[data-area="block_edit"]').hide();
			$('article[data-area="list"]').show();
		});
		$('#dataList').on('click', 'a[data-edit-region]', function() {
			$('.tabs li').removeClass('active');
			$.post(homeUrl + 'manage/edit_region', {
				"region_id": $(this).attr('data-edit-region'),
				"action": "get_info",
				"from": from
			}, function(r) {
				if (r.data) {
					$('#region_edit_id').val(r.data.id);
					$('#region_edit_type').val(r.data.type);
					$('#region_edit_title').val(r.data.region_title);
					$('#region_edit_type option[value=' + r.data.type + ']').prop('selected', true);
					$('article[data-area]:not([data-area="list"])').hide();
					$('article[data-area="region_edit"]').show();
				} else {
					showMsg(r.title, r.content);
				}
			}, 'json');
		});
		$('#dataList').on('click', 'input[data-edit-block]', function() {
			$('.tabs li').removeClass('active');
			$.post(homeUrl + 'manage/edit_block', {
				"block_id": $(this).attr('data-edit-block'),
				"action": "get_info",
				"from": from
			}, function(r) {
				if (r.data) {
					$('#block_edit_id').val(r.data.id);
					$('#block_edit_region').val(r.data.region_id);
					$('#block_edit_title').val(r.data.block_title);
					$('#block_edit_content').val(r.data.block_content);
					$('#block_edit_region option[value=' + r.data.region_id + ']').prop('selected', true);
					$('#block_edit_order').val(r.data.list_order);
					$('input[name="block_edit_status"][value="' + r.data.status + '"]').prop('checked', true);
					$('article[data-area]').hide();
					$('article[data-area="block_edit"]').show();
				} else {
					showMsg(r.title, r.content);
				}
			}, 'json');
		});
		$('#region_create_btn').click(function() {
			$.post(homeUrl + 'manage/create_region', {
				"region_type": $('#region_create_type').val(),
				"region_title": $('#region_create_title').val(),
				"from": from
			}, function(r) {
				if (r.status == 1) {
					$('article[data-area="region_create"]').hide();
					refresh = true;
				}
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('#block_create_btn').click(function() {
			$.post(homeUrl + 'manage/create_block', {
				"block_region": $('#block_create_region').val(),
				"block_title": $('#block_create_title').val(),
				"block_content": $('#block_create_content').val(),
				"block_order": $('#block_create_order').val(),
				"block_status": $('input[name="block_create_status"]:checked').val(),
				"from": from
			}, function(r) {
				if (r.status == 1) {
					$('#block_create_region').val('');
					$('#block_create_title').val('');
					$('#block_create_content').val('');
					$('#block_create_order').val('');
					$('input[name="block_create_status"]').prop('checked', false),
						$('article[data-area="block_create"]').hide();
					requestData();
					$('article[data-area="list"]').show();
				}
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('#region_delete_btn').click(function() {
			if (confirm('您确定要删除该区域吗？')) {
				$.post(homeUrl + 'manage/delete_region', {
					"region_id": $('#region_edit_id').val(),
					"from": from
				}, function(r) {
					if (r.status == 1)
						refresh = true;
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#dataList').on('click', 'input[data-del-block]', function() {
			if (confirm('您确定要删除该块吗？')) {
				$.post(homeUrl + 'manage/delete_block', {
					"block_id": $(this).attr('data-del-block'),
					"from": from
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#region_edit_btn').click(function() {
			$.post(homeUrl + 'manage/edit_region', {
				"region_id": $('#region_edit_id').val(),
				"region_type": $('#region_edit_type').val(),
				"region_title": $('#region_edit_title').val(),
				"from": from
			}, function(r) {
				if (r.status == 1) {
					requestData();
					$('article[data-area="region_edit"]').hide();
				}
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('#block_edit_btn').click(function() {
			$.post(homeUrl + 'manage/edit_block', {
				"block_id": $('#block_edit_id').val(),
				"block_region": $('#block_edit_region').val(),
				"block_title": $('#block_edit_title').val(),
				"block_content": $('#block_edit_content').val(),
				"block_order": $('#block_edit_order').val(),
				"block_status": $('input[name="block_edit_status"]:checked').val(),
				"from": from
			}, function(r) {
				if (r.status == 1) {
					$('article[data-area="block_edit"]').hide();
					requestData();
					$('article[data-area="list"]').show();
				}
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage/' + from;
		});
	});
</script>