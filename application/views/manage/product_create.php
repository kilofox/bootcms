<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage2/list_products">产品管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">添加新产品</span>
</header>
<article class="module width_full">
	<header><h3>产品信息</h3></header>
	<form>
		<div class="module_content field">
			<ul>
				<li>
					<label>产品分类</label>
					<select name="category">
						<?php foreach ($cates as $cate): ?>
							<option value="<?php echo $cate->id; ?>"><?php echo $cate->name; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>产品名称</label>
					<input type="text" name="product_name" value="" class="input" />
				</li>
				<li>
					<label>产品介绍</label>
					<textarea name="introduce" class="textarea"></textarea>
				</li>
				<li>
					<label>自定义字段一</label>
					<input type="text" name="item1_name" />：
					<input type="text" name="item1_value" />
				</li>
				<li>
					<label>自定义字段二</label>
					<input type="text" name="item2_name" />：
					<input type="text" name="item2_value" />
				</li>
				<li>
					<label>自定义字段三</label>
					<input type="text" name="item3_name" />：
					<input type="text" name="item3_value" />
				</li>
				<li>
					<label>自定义字段四</label>
					<input type="text" name="item4_name" />：
					<input type="text" name="item4_value" />
				</li>
				<li>
					<label>自定义字段五</label>
					<input type="text" name="item5_name" />：
					<input type="text" name="item5_value" />
				</li>
				<li>
					<label>自定义字段六</label>
					<input type="text" name="item6_name" />：
					<input type="text" name="item6_value" />
				</li>
				<li>
					<label>自定义字段七</label>
					<input type="text" name="item7_name" />：
					<input type="text" name="item7_value" />
				</li>
				<li>
					<label>自定义字段八</label>
					<input type="text" name="item8_name" />：
					<input type="text" name="item8_value" />
				</li>
				<li>
					<label>自定义字段九</label>
					<input type="text" name="item9_name" />：
					<input type="text" name="item9_value" />
				</li>
				<li>
					<label>自定义字段十</label>
					<input type="text" name="item10_name" />：
					<input type="text" name="item10_value" />
				</li>
				<li>
					<label>商品价格</label>
					<input type="text" name="commodity_price" /> 元
				</li>
				<li>
					<label>促销价格</label>
					<input type="text" name="promotion_price" /> 元
				</li>
				<li>
					<label>是否促销</label>
					<input type="radio" name="promote" value="1" /> 是
					<input type="radio" name="promote" value="0" checked="checked" /> 否
				</li>
				<li>
					<label>插入图片</label>
					<?php if ($mediaGroups): ?>
						<ul id="mediaGroups">
							<?php foreach ($mediaGroups as $group): ?>
								<li>
									<a data-gid="<?php echo $group->id; ?>" class="message active"><?php echo $group->group_name; ?>&raquo;</a>
									<p></p>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else: ?>
						媒体库中尚无图片，请<a href="<?php echo $homeUrl; ?>manage/list_media_groups/">添加新媒体</a>。
					<?php endif; ?>
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="submit" value="创建产品" class="alt_btn" />
			</div>
		</footer>
	</form>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		refresh = false;
	$(function() {
		$('form').submit(function() {
			var pics = '';
			$('img.picture.selected').each(function() {
				pics += $(this).attr('data-id') + ',';
			});
			$('form').append('<input type="hidden" name="pictures" value="' + pics + '" />');
			$('input[type="submit"]').prop('disabled', true);
			$.post(homeUrl + 'manage2/create_product', $('form').serialize(), function(r) {
				showMsg(r.title, r.content);
				if (r.status == 1)
					refresh = true;
				$('input[type="submit"]').prop('disabled', false);
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage2/list_products';
		});
		$('body').on('click', 'img.picture', function() {
			$(this).toggleClass('selected');
		});
		$('#mediaGroups').on('click', 'li > a[data-gid]', function() {
			var mediaArea = $(this).siblings('p');
			if (mediaArea.html()) {
				mediaArea.html('');
			} else {
				$.post(homeUrl + 'manage/load_media_by_group', {
					"group": $(this).attr('data-gid')
				}, function(r) {
					if (r.status === 1)
					{
						mediaArea.append(r.data);
					} else {
						mediaArea.append(r.content);
					}
				}, 'json');
			}
		});
	});
</script>
