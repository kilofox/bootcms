<?php
$tags = Setup::tags();
$title = isset($title) && $title ? $title : $tags['site_description'];
$keywords = isset($keywords) && $keywords ? $keywords : $tags['meta_description'];
$description = isset($description) && $description ? $description : $tags['meta_description'];
$website = Setup::siteInfo();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title><?php echo $title, ' - ', $tags['site_title']; ?></title>
		<meta name="description" content="<?php echo $description; ?>" />
		<meta name="keywords" content="<?php echo $keywords; ?>" />
		<link rel="icon" href="<?php echo $homeUrl; ?>assets/images/favicon.ico" type="image/x-icon" />
		<?php
		// 使用 config.php 来添加 CSS 文件
		echo Setup::css($css);
		// 使用 config.php 来添加 javascript 文件
		echo Setup::js($js);
		// 另外，如果要为特定网页添加额外的 javascripts ，可以在 controller/action 中创建模板变量。
		if (isset($extraScripts))
			echo $extraScripts, "\n";
		?>
	</head>
	<body<?php if (isset($bodyTag)) echo $bodyTag; ?>>
		<?php
		if (isset($head))
		{
			isset($user) and $head->user = $user;
			$head->homeUrl = $homeUrl;
			$head->controller = isset($slug) ? $slug : Request::current()->controller();
			echo $head;
		}
		if (isset($body))
		{
			isset($user) and $body->user = $user;
			$body->homeUrl = $homeUrl;
			echo isset($message) ? Setup::message($message) : $body;
		}
		if (isset($foot))
		{
			$foot->homeUrl = $homeUrl;
			echo $foot;
		}
		?>
	</body>
</html>
