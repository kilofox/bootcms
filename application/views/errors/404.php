<?php
$homeUrl = Url::base();
?>
<div id="site_content">
	<div id="content">
		<h1>404 网页未找到</h1>
		<span class="right"><img src="<?php echo $homeUrl; ?>assets/images/404.png" /></span>
		<h2>找不到您要找的页面</h2>
		<p>这可能是页面已删除、文件被改名或者页面暂时不可用的结果。</p>
		<h2>故障排除</h2>
		<ul>
			<li>如果您是手动拼写的URL，请仔细检查拼写。</li>
			<li>转到我们网站的<a href="<?php echo $homeUrl; ?>">首页</a>，浏览有问题的内容。</li>
			<li>或者，您可以在下面搜索我们的网站。</li>
		</ul>
		<form id="search_form" action="<?php echo $homeUrl; ?>homepage/search/" method="get">
			<input type="text" class="search" name="q" placeholder="关键字" />
			<input type="image" id="search" style="border: 0; margin: 0 0 -10px 5px;" src="<?php echo $homeUrl; ?>assets/images/search.png" alt="搜索" title="搜索" />
		</form>
	</div>
</div>
