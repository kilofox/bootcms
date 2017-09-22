<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage2/list_products">产品管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">编辑产品</span>
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
							<option value="<?php echo $cate->id; ?>"<?php if ($cate->id == $node->category): ?> selected="selected"<?php endif; ?>><?php echo $cate->name; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>产品名称</label>
					<input type="text" name="product_name" value="<?php echo $node->product_name; ?>" class="input" />
				</li>
				<li>
					<label>产品介绍</label>
					<textarea name="introduce" class="textarea"><?php echo $node->introduce; ?></textarea>
				</li>
				<li>
					<label>自定义字段一</label>
					<input type="text" name="item1_name" value="<?php echo stripslashes($node->item1[0]); ?>" />：
					<input type="text" name="item1_value" value="<?php echo stripslashes($node->item1[1]); ?>" />
				</li>
				<li>
					<label>自定义字段二</label>
					<input type="text" name="item2_name" value="<?php echo stripslashes($node->item2[0]); ?>" />：
					<input type="text" name="item2_value" value="<?php echo stripslashes($node->item2[1]); ?>" />
				</li>
				<li>
					<label>自定义字段三</label>
					<input type="text" name="item3_name" value="<?php echo stripslashes($node->item3[0]); ?>" />：
					<input type="text" name="item3_value" value="<?php echo stripslashes($node->item3[1]); ?>" />
				</li>
				<li>
					<label>自定义字段四</label>
					<input type="text" name="item4_name" value="<?php echo stripslashes($node->item4[0]); ?>" />：
					<input type="text" name="item4_value" value="<?php echo stripslashes($node->item4[1]); ?>" />
				</li>
				<li>
					<label>自定义字段五</label>
					<input type="text" name="item5_name" value="<?php echo stripslashes($node->item5[0]); ?>" />：
					<input type="text" name="item5_value" value="<?php echo stripslashes($node->item5[1]); ?>" />
				</li>
				<li>
					<label>自定义字段六</label>
					<input type="text" name="item6_name" value="<?php echo stripslashes($node->item6[0]); ?>" />：
					<input type="text" name="item6_value" value="<?php echo stripslashes($node->item6[1]); ?>" />
				</li>
				<li>
					<label>自定义字段七</label>
					<input type="text" name="item7_name" value="<?php echo stripslashes($node->item7[0]); ?>" />：
					<input type="text" name="item7_value" value="<?php echo stripslashes($node->item7[1]); ?>" />
				</li>
				<li>
					<label>自定义字段八</label>
					<input type="text" name="item8_name" value="<?php echo stripslashes($node->item8[0]); ?>" />：
					<input type="text" name="item8_value" value="<?php echo stripslashes($node->item8[1]); ?>" />
				</li>
				<li>
					<label>自定义字段九</label>
					<input type="text" name="item9_name" value="<?php echo stripslashes($node->item9[0]); ?>" />：
					<input type="text" name="item9_value" value="<?php echo stripslashes($node->item9[1]); ?>" />
				</li>
				<li>
					<label>自定义字段十</label>
					<input type="text" name="item10_name" value="<?php echo stripslashes($node->item10[0]); ?>" />：
					<input type="text" name="item10_value" value="<?php echo stripslashes($node->item10[1]); ?>" />
				</li>
				<li>
					<label>商品价格</label>
					<input type="text" name="commodity_price" value="<?php echo $node->commodity_price; ?>" /> 元
				</li>
				<li>
					<label>促销价格</label>
					<input type="text" name="promotion_price" value="<?php echo $node->promotion_price; ?>" /> 元
				</li>
				<li>
					<label>是否促销</label>
					<input type="radio" name="promote" value="1"<?php if ($node->promote == 1): ?> checked="checked"<?php endif; ?>/> 是
					<input type="radio" name="promote" value="0"<?php if ($node->promote == 0): ?> checked="checked"<?php endif; ?>/> 否
				</li>
				<li>
					<label>插入图片</label>
					<?php foreach ($media as $m): ?>
						<img class="picture selected" src="<?php echo $homeUrl; ?>assets/uploads/<?php echo $mediaGroups[$m->group]->slug; ?>/<?php echo $m->thumb_name; ?>" data-id="<?php echo $m->id; ?>" />
					<?php endforeach; ?>
					<ul id="mediaGroups">
						<?php foreach ($mediaGroups as $group): ?>
							<li>
								<a data-gid="<?php echo $group->id; ?>" class="message active"><?php echo $group->group_name; ?>&raquo;</a>
								<p></p>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="hidden" name="pid" value="<?php echo $node->id; ?>" />
				<input type="submit" value="更新" class="alt_btn" />
			</div>
		</footer>
	</form>
</article>
<style>
	.picture{margin:0 5px 5px 0;}
</style>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			toList = false;
		$('form').submit(function() {
			var media = '';
			$('img.picture.selected').each(function() {
				media += $(this).attr('data-id') + ',';
			});
			$(this).append('<input type="hidden" name="pictures" value="' + media + '" />');
			$.post(homeUrl + 'manage2/edit_product', $(this).serialize(), function(r) {
				showMsg(r.title, r.content);
				if (r.status === 1)
					toList = true;
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (toList)
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
