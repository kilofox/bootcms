<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>BootCMS 安装向导</title>
		<style>
			body { width: 42em; margin: 0 auto; font-family: Arial, "Microsoft Yahei"; background: #fff; font-size: 1em; }
			h1 { letter-spacing: -0.04em; }
			h2 + p { margin: 0 0 2em; color: #333; font-size: 90%; font-style: italic; }
			code { font-family: monaco, monospace; }
			hr { border:1px dashed #F79F00; }
			table { border-collapse: collapse; width: 100%; }
			table th,
			table td { padding: 0.4em; text-align: left; vertical-align: middle; }
			table th { width: 12em; font-weight: normal; }
			table tr:nth-child(odd) { background: #eee; }
			table td.pass { color: #191; }
			table td.fail { color: #911; }
			#results { padding: 0.8em; color: #fff; font-size: 1.5em; }
			#results.pass { background: #191; }
			#results.fail { background: #911; }
			#buttons { margin: 20px 0 30px; }
			#footer { font: normal 80% Arial, "Microsoft Yahei"; text-align: center; margin: 0; padding: 18px 0; }
		</style>
		<script src="assets_manage/js/jquery.min.js"></script>
	</head>
	<body>
		<h1>BootCMS <?php echo $this->version; ?> 安装向导</h1>
		<hr />