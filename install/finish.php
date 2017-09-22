<?php
include 'header.php';
$failed = false;
?>
<h2>安装完成！</h2>
<p></p>
<p id="results" class="pass">恭喜您，由千狐工作室研发的内容管理系统 BootCMS 现已安装完毕并且可以使用了。</p>
<h2>有用的链接</h2>
<p>下面是一些对于您来说可能会有用的链接。</p>
<p>
	<a href="<?php echo substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1); ?>">您的网站</a>
</p>
<p>
	<a href="<?php echo substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1); ?>manage">后台管理</a>
</p>
<p>
	<a href="http://www.kilofox.net" target="_blank">千狐工作室</a>
</p>
<?php include 'footer.php'; ?>
