<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span><a href="<?php echo $homeUrl; ?>manage/list_media_groups">媒体库</a></span>
	<div class="breadcrumb_divider"></div>
	<span><a href="<?php echo $homeUrl; ?>manage/list_media/<?php echo $group->id; ?>"><?php echo $group->group_name; ?></a></span>
	<div class="breadcrumb_divider"></div>
	<span class="current">上传新媒体</span>
</header>
<h4 class="alert_info">文件大小：我们强烈建议您在上传图像之前调整图像的大小。允许上传的最大文件为 2MB（建议小于 150KB）。</h4>
<article class="module width_full">
	<header><h3>上传新媒体</h3></header>
	<div class="module_content field">
		<div id="uploader"></div>
	</div>
	<footer>
		<div class="submit_link">
			<input type="button" id="to_group" value="返回<?php echo $group->group_name; ?>" class="alt-btn" />
		</div>
	</footer>
</article>
<link href="<?php echo $homeUrl; ?>assets_manage/css/fineuploader.css" rel="stylesheet" />
<script src="<?php echo $homeUrl; ?>assets_manage/js/fineuploader.js"></script>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		refresh = false,
		groupId = '<?php echo $group->id; ?>';
	var uploader = new qq.FineUploader({
		element: $('#uploader')[0],
		request: {
			endpoint: homeUrl + 'manage/create_media',
			params: {
				gid: groupId
			}
		},
		multiple: true,
		validation: {
			allowedExtensions: ['jpeg', 'jpg', 'gif', 'png'],
			sizeLimit: 1048576	// 1MB = 1024 * 1024 bytes
		},
		text: {
			uploadButton: '上传文件'
		},
		showMessage: function(message) {
			showMsg('上传失败', message);
		},
		callbacks: {
			onComplete: function(id, fileName, r) {
				if (r.status == 2)
					refresh = true;
				if (r.status == 1)
					$('#uploader').append('<img src="' + homeUrl + 'assets/uploads/<?php echo $group->slug; ?>/' + r.thumbName + '" />');
			}
		}
	});
	$(function() {
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage/create_media';
		});
		$('#to_group').click(function() {
			window.location.href = homeUrl + 'manage/list_media/' + groupId;
		});
	});
</script>
