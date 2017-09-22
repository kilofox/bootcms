<?php
include 'header.php';
$failed = false;
?>
<h2>环境测试</h2>
<p>
	下面已经运行的测试是用来确定 BootCMS 在您的环境下是否工作。<br />
	如果某项测试失败，请参考<a href="http://bootcms.kilofox.net/guide/about/install">文档</a>，查找如何纠正问题的信息。
</p>
<table cellspacing="0">
	<tr>
		<th>PHP 版本</th>
		<?php if (version_compare(PHP_VERSION, '5.3', '>=')): ?>
			<td class="pass"><?php echo PHP_VERSION; ?></td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">BootCMS 需要 PHP 5.3.0 或更高版本，这个版本是 <?php echo PHP_VERSION; ?>。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>系统目录</th>
		<?php if (is_dir(SYSPATH) && is_file(SYSPATH . 'classes/bootphp.php')): ?>
			<td class="pass"><?php echo SYSPATH; ?></td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">配置的 <code>system</code> 目录不存在，或者不含所需文件。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>应用目录</th>
		<?php if (is_dir(APPPATH)): ?>
			<td class="pass"><?php echo APPPATH; ?></td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">配置的 <code>application</code> 目录不存在，或者不含所需文件。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>缓存目录</th>
		<?php if (is_dir(APPPATH) && is_dir(APPPATH . 'cache') && is_writable(APPPATH . 'cache')): ?>
			<td class="pass"><?php echo APPPATH . 'cache/'; ?></td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail"><code><?php echo APPPATH . 'cache/'; ?></code> 目录不可写。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>日志目录</th>
		<?php if (is_dir(APPPATH) && is_dir(APPPATH . 'logs') && is_writable(APPPATH . 'logs')): ?>
			<td class="pass"><?php echo APPPATH . 'logs/' ?></td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail"><code><?php echo APPPATH . 'logs/'; ?></code> 目录不可写。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>PCRE UTF-8</th>
		<?php if (!@preg_match('/^.$/u', 'ñ')): ?>
			<?php $failed = true; ?>
			<td class="fail"><a href="http://php.net/pcre" target="_blank">PCRE</a> 没有用 UTF-8 编译。</td>
		<?php elseif (!@preg_match('/^\pL$/u', 'ñ')): ?>
			<?php $failed = true; ?>
			<td class="fail"><a href="http://php.net/pcre" target="_blank">PCRE</a> 没有用 Unicode 编译。</td>
		<?php else: ?>
			<td class="pass">通过</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>开启 SPL</th>
		<?php if (function_exists('spl_autoload_register')): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">PHP <a href="http://www.php.net/spl" target="_blank">SPL</a> 要么未加载，要么未编译进来。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>开启 Reflection</th>
		<?php if (class_exists('ReflectionClass')): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">PHP <a href="http://www.php.net/reflection" target="_blank">reflection</a> 要么未加载，要么未编译进来。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>开启 Filters</th>
		<?php if (function_exists('filter_list')): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">PHP <a href="http://www.php.net/filter" target="_blank">filter</a> 扩展要么未加载，要么未编译进来。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>加载 Iconv 扩展</th>
		<?php if (extension_loaded('iconv')): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail"><a href="http://php.net/iconv" target="_blank">iconv</a> 扩展未加载。</td>
		<?php endif; ?>
	</tr>
	<?php if (extension_loaded('mbstring')): ?>
		<tr>
			<th>Mbstring 未重载</th>
			<?php if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING): ?>
				<?php $failed = true; ?>
				<td class="fail"><a href="http://php.net/mbstring" target="_blank">mbstring</a> 扩展重载了 PHP 的本地 string 函数。</td>
			<?php else: ?>
				<td class="pass">通过</td>
			<?php endif; ?>
		</tr>
	<?php endif; ?>
	<tr>
		<th>字符类型（CTYPE）扩展</th>
		<?php if (function_exists('ctype_digit')): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail"><a href="http://php.net/ctype" target="_blank">ctype</a> 扩展未开启。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>URI 检测</th>
		<?php if (isset($_SERVER['REQUEST_URI']) || isset($_SERVER['PHP_SELF']) || isset($_SERVER['PATH_INFO'])): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail"><code>$_SERVER['REQUEST_URI']</code>、<code>$_SERVER['PHP_SELF']</code> 或者 <code>$_SERVER['PATH_INFO']</code> 均不可用。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>开启 cURL</th>
		<?php if (extension_loaded('curl')): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">BootCMS 可以使用 <a href="http://php.net/curl" target="_blank">cURL</a> 扩展，用于 Request_Client_External 类。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>开启 mcrypt</th>
		<?php if (extension_loaded('mcrypt')): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">BootCMS 需要 <a href="http://php.net/mcrypt" target="_blank">mcrypt</a>，用于 Encrypt 类。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>开启 GD</th>
		<?php if (function_exists('gd_info')): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">BootCMS 需要 <a href="http://php.net/gd" target="_blank">GD</a> v2，用于 Image 类。</td>
		<?php endif; ?>
	</tr>
	<tr>
		<th>开启 PDO_MYSQL</th>
		<?php if (extension_loaded('pdo_mysql')): ?>
			<td class="pass">通过</td>
		<?php else: ?>
			<?php $failed = true; ?>
			<td class="fail">BootCMS 需要使用 <a href="http://php.net/pdo" target="_blank">PDO_MYSQL</a> 来支持数据库。</td>
		<?php endif; ?>
	</tr>
</table>
<?php if ($failed === true): ?>
	<p id="results" class="fail">BootCMS 在您的环境下可能不会正常工作。</p>
<?php else: ?>
	<p id="results" class="pass">您的环境通过了所有需求。</p>
	<p id="buttons">
		<input type="button" id="prev" value="上一步" />
		<input type="submit" value="下一步" />
	</p>
	<script>
		$(function() {
			$('input[type="submit"]').focus();
			$('#prev').click(function() {
				window.location.href = '?action=<?php echo $this->prevStep; ?>';
			});
			$('input[type="submit"]').click(function() {
				window.location.href = '?action=<?php echo $this->nextStep; ?>';
			});
		});
	</script>
<?php endif; ?>
<?php include 'footer.php'; ?>