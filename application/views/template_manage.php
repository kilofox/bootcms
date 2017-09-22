<?php
$tags = Setup::tags();
$title = isset($title) && $title ? $title : $tags['site_description'];
$website = Setup::siteInfo();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title><?php echo $title, ' - ', $tags['site_title']; ?></title>
		<!--<link rel="icon" type="image/x-icon" href="<?php echo $homeUrl; ?>assets_manage/images/favicon.ico" />-->
		<?php
		// 使用 config.php 来添加 CSS 文件
		echo Setup::css($css);
		// 使用 config.php 来添加 javascript 文件
		echo Setup::js($js);
		// 另外，如果要为特定网页添加额外的 css 或 javascript，可以在 controller/action 中创建模板变量。
		if (isset($extra))
			echo $extra;
		?>
	</head>
	<body<?php if (isset($bodyTag)) echo $bodyTag; ?>>
		<?php
		if (isset($message))
			echo Setup::message($message);
		if (isset($head))
		{
			$head->user = $user;
			$head->homeUrl = $homeUrl;
			$head->action = Request::current()->action();
			echo $head;
		}
		if (isset($body))
		{
			$body->homeUrl = $homeUrl;
			echo $body;
		}
		if (isset($foot))
		{
			$foot->homeUrl = $homeUrl;
			echo $foot;
		}
		?>
	</body>
</html>
