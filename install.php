<?php

defined('SYSPATH') || exit('安装程序须由 index.php 加载！');

class Installer {

	public $version = '1.1.3';
	public $nextStep = '';

	/**
	 * 构造方法
	 */
	public function __construct()
	{
		error_reporting(E_ALL ^ E_NOTICE);
		date_default_timezone_set('Asia/Shanghai');
		mb_internal_encoding('UTF-8');
		$action = isset($_GET['action']) ? $_GET['action'] : 'index';
		$this->run($action);
	}

	/**
	 * 处理安装步骤
	 *
	 * @param string $action 执行的动作
	 * @return void
	 */
	public function run($action)
	{
		switch ($action)
		{
			case 'index':
			case 'license':
				$this->prevStep = 'index';
				$this->nextStep = 'test_environment';
				$this->license();
				break;
			case 'test_environment':
				$this->prevStep = 'license';
				$this->nextStep = 'config_database';
				$this->testEnvironment();
				break;
			case 'config_database':
				$this->prevStep = 'test_environment';
				$this->nextStep = 'create_database';
				$this->configDatabase();
				break;
			case 'create_database':
				$this->prevStep = 'config_database';
				$this->nextStep = 'create_account';
				$this->createDatabase();
				break;
			case 'create_account':
				$this->prevStep = 'create_database';
				$this->nextStep = 'finish';
				$this->createAccount();
				break;
			case 'finish':
				$this->finish();
				break;
		}
	}

	/**
	 * 通用公共许可证
	 *
	 * @return void
	 */
	private function license()
	{
		include 'install/license.php';
	}

	/**
	 * 测试服务器环境
	 *
	 * @return void
	 */
	private function testEnvironment()
	{
		if (version_compare(PHP_VERSION, '5.3', '<'))
		{
			// 清除缓存以防止错误。这通常发生在 Windows/FastCGI。
			clearstatcache();
		}
		else
		{
			// 清除 realpath() 缓存，只可能发生在 PHP 5.3+
			clearstatcache(true);
		}
		include 'install/test_environment.php';
	}

	/**
	 * 配置数据库
	 *
	 * @return void
	 */
	private function configDatabase()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'config_database')
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$dsn = 'mysql:host=' . $_POST['db_host'] . ';dbname=' . $_POST['db_name'];
			$connection = $this->connectDB(array($dsn, $_POST['db_user'], $_POST['db_pass']));
			if (!is_object($connection))
			{
				$output->status = 2;
				$output->title = '配置失败';
				$output->content = $connection;
				exit(json_encode($output));
			}
			$file = fopen('application/config/database.php', 'wb');
			$content = '<?php defined(\'SYSPATH\') || exit(\'Access Denied.\');
return array(
	\'default\' => array(
		\'type\'			=> \'pdo\',
		\'connection\'	=> array(
			/**
			 * string	dsn			数据源名
			 * string	username	数据库用户名
			 * string	password	数据库密码
			 * boolean	persistent	是否使用持久连接
			 */
			\'dsn\'			=> \'mysql:host=' . $_POST['db_host'] . ';dbname=' . $_POST['db_name'] . '\',
			\'username\'		=> \'' . $_POST['db_user'] . '\',
			\'password\'		=> \'' . $_POST['db_pass'] . '\',
			\'persistent\'	=> false
		),
		\'table_prefix\'	=> \'' . $_POST['db_prefix'] . '\',
		\'charset\'		=> \'utf8\',
		\'caching\'		=> false,
		\'profiling\'		=> true
	)
);';
			fwrite($file, $content);
			$output->status = 1;
			$output->title = '配置成功';
			$output->content = '数据库配置文件已生成。';
			exit(json_encode($output));
		}
		include 'install/config_database.php';
	}

	/**
	 * 创建数据库
	 *
	 * @return void
	 */
	private function createDatabase()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'create_database')
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$config = require 'application/config/database.php';
			$db = $config['default']['connection'];
			$connection = $this->connectDB(array($db['dsn'], $db['username'], $db['password']));
			if (!is_object($connection))
			{
				$output->status = 2;
				$output->title = '数据库错误';
				$output->content = $connection;
				exit(json_encode($output));
			}
			$connection->exec('SET NAMES utf8');
			$file = 'install/bootcms.sql';
			if (file_exists($file))
			{
				$content = file_get_contents($file);
				$arrSQL = $this->parseSQL($content, $config['default']['table_prefix']);
				if ($arrSQL['SQL']['DROP'])
				{
					foreach ($arrSQL['SQL']['DROP'] as $query)
					{
						$connection->query($query);
					}
				}
				if ($arrSQL['SQL']['CREATE'])
				{
					foreach ($arrSQL['SQL']['CREATE'] as $query)
					{
						$connection->query($query);
					}
				}
				if ($arrSQL['SQL']['ALTER'])
				{
					foreach ($arrSQL['SQL']['ALTER'] as $query)
					{
						$connection->query($query);
					}
				}
				if ($arrSQL['SQL']['UPDATE'])
				{
					foreach ($arrSQL['SQL']['UPDATE'] as $query)
					{
						$connection->query($query);
					}
				}
				if ($arrSQL['SQL']['INSERT'])
				{
					foreach ($arrSQL['SQL']['INSERT'] as $query)
					{
						$connection->query($query);
					}
				}
				$data = '';
				foreach ($arrSQL['LOG']['CREATE'] as $log)
				{
					$data.= '表 ' . $log . ' <span style="float:right">创建成功</span><br/>';
				}
				if ($arrSQL['LOG']['INSERT'])
				{
					foreach ($arrSQL['LOG']['INSERT'] as $log)
					{
						$data.= '表 ' . $log . ' 数据 <span style="float:right">插入成功</span><br/>';
					}
				}
				$data.= '已创建 ' . count($arrSQL['SQL']['CREATE']) . ' 个表，数据库创建成功。';
				$output->status = 1;
				$output->title = '数据库已创建';
				$output->content = $data;
			}
			else
			{
				$output->status = 4;
				$output->title = '创建失败';
				$output->content = '没有找到数据库文件。';
			}
			exit(json_encode($output));
		}
		include 'install/create_database.php';
	}

	/**
	 * 创建管理员账户
	 *
	 * @return void
	 */
	private function createAccount()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'create_admin_account')
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$message = '';
			if (strlen($_POST['username']) < 5)
				$message .= '您的用户名长度不能小于 5 个字符。<br/>';
			if ($_POST['password'] != $_POST['password_confirm'])
				$message .= '您两次输入的密码不匹配。<br/>';
			if (strlen($_POST['password']) < 8)
				$message .= '您的密码长度不能小于 8 个字符。<br/>';
			if (!preg_match('/^[a-z0-9]+((_|-|\.)[a-z0-9]+)*@[a-z0-9]+((\.|-)[a-z0-9]+)*\.[a-z]{2,6}$/i', $_POST['email']))
				$message .= '请输入一个有效的电子邮箱。';
			if ($message)
			{
				$output->status = 2;
				$output->title = '账户未创建';
				$output->content = $message;
			}
			else
			{
				$config = require 'application/config/database.php';
				$db = $config['default']['connection'];
				$connection = $this->connectDB(array($db['dsn'], $db['username'], $db['password']));
				if (!is_object($connection))
				{
					$output->status = 2;
					$output->title = '数据库错误';
					$output->content = $connection;
					exit(json_encode($output));
				}
				$connection->exec('SET NAMES utf8');
				$hash = $this->generateCode(38);
				$file = fopen('application/config/auth.php', 'wb');
				$content = '<?php defined(\'SYSPATH\') || exit(\'Access Denied.\');
return array(
	\'driver\'		=> \'db\',
	\'hash_method\'	=> \'sha256\',
	\'hash_key\'		=> \'' . $hash . '\',
	\'lifetime\'		=> 1209600,
	\'session_type\'	=> \'native\',
	\'session_key\'	=> \'auth_user\'
);';
				fwrite($file, $content);
				$timestamp = time();
				$query = "INSERT INTO `bc_users` (`id`, `username`, `password`, `email`, `role_id`, `nickname`, `company`, `first_name`, `secondary_email`, `phone`, `address`, `created`, `resets`, `last_reset`, `logins`, `last_login`) VALUES
(1, '" . $_POST['username'] . "', '" . hash_hmac('sha256', $_POST['password'], $hash) . "', '" . $_POST['email'] . "', 9, '" . $_POST['username'] . "', '', '" . $_POST['first_name'] . "', '', '18900012345', '无', " . $timestamp . ", 0, 0, 0, 0);";
				$connection->query($query);
				$output->status = 1;
				$output->title = '账户已创建';
				$output->content = '您的管理账户创建成功。';
			}
			exit(json_encode($output));
		}
		include 'install/create_account.php';
	}

	/**
	 * 完成安装
	 *
	 * @return void
	 */
	private function finish()
	{
		$file = fopen('application/bootstrap.php', 'wb');
		$content = '<?php defined(\'SYSPATH\') || exit(\'Access Denied.\');
// 加载 BootPHP 核心类
require SYSPATH.\'classes/bootphp.php\';

// 设置默认时区
// 例如 date_default_timezone_set(\'America/Toronto\');
date_default_timezone_set(\'Asia/Shanghai\');

// 设置默认区域
// 例如 setlocale(LC_ALL, \'en_US.utf-8\');
setlocale(LC_ALL, \'zh_CN.utf-8\');

// 开启 BootPHP 自动加载
spl_autoload_register(array(\'BootPHP\', \'auto_load\'));

// 开启 BootPHP 自动加载反序列化
ini_set(\'unserialize_callback_func\', \'spl_autoload_call\');

// 设置默认语言
I18n::lang(\'en-us\');

Cookie::$salt = \'' . $this->generateCode(15) . '\';

// 如果提供了 BOOTPHP_ENV 环境变量，设置 BootPHP::$environment。
// 注意：如果您提供了一个无效的环境变量名，将抛出一个 PHP 警告：Couldn\'t find constant BootPHP::<INVALID_ENV_NAME>
if ( isset($_SERVER[\'BOOTPHP_ENV\']) )
{
	BootPHP::$environment = constant(\'BootPHP::\'.strtoupper($_SERVER[\'BOOTPHP_ENV\']));
}

/**
 * 初始化 BootPHP，设置默认选项。
 * 可用的选项如下：
 * - string		base_url	应用的路径						null
 * - string		index_file	索引文件名，通常是 index.php	index.php
 * - string		charset		设置用于输入与输出的内部字符集	utf-8
 * - string		cache_dir	设置内部缓存目录				APPPATH/cache
 * - boolean	errors		开启或关闭错误处理				true
 * - boolean	profile		开启或关闭内部分析				true
 * - boolean	caching		开启或关闭内部缓存				false
 */
BootPHP::init(array(
	\'base_url\' => \'' . str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) . '\',
	\'errors\' => false,
	\'profile\' => true,
));

// 附加日志文件写入。支持多个写入。
BootPHP::$log->attach(new Log_File(APPPATH.\'logs\'));

// 附加配置文件读取。支持多个读取。
BootPHP::$config->attach(new Config_File);

// 开启模块。相对或绝对路径引用。
BootPHP::modules(array(
	\'auth\' => MODPATH.\'auth\',			// 基本身份验证
	\'cache\' => MODPATH.\'cache\',			// 多后端缓存
	\'database\' => MODPATH.\'database\',	// 数据库访问
	\'image\' => MODPATH.\'image\',			// 图像处理
	\'mail\' => MODPATH.\'mail\',			// 发送邮件
));

// 默认文件上传目录
Upload::$default_directory = \'assets/uploads\';

// 设置异常处理器
set_exception_handler(array(\'Exception_Handler\', \'handle\'));

// 设置路由。每个路由至少要有一个名字，一个 URI，一组 URI 的默认值。
Route::set(\'default\', \'(<controller>(/<action>(/<id>)))\', array(
		\'controller\' => \'(manage|manage2|manage3|homepage|member|findpwd|product|cart|order|pay|forum)\'
	))
	->defaults(array(
		\'controller\' => \'homepage\',
		\'action\' => \'index\',
	));
Route::set(\'page\', \'(<id>(/<action>))\')
	->defaults(array(
		\'controller\' => \'page\',
		\'action\' => \'index\',
	));';
		fwrite($file, $content);
		// Rewrite 文件
		$file = fopen('.htaccess', 'wb');
		$content = '# 打开 URL 重写
RewriteEngine On

# 安装目录
RewriteBase ' . str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) . '

# 保护隐藏文件，防止被浏览
<Files .*>
	Order Deny,Allow
	Deny From All
</Files>

# 保护应用和系统文件，防止被浏览
RewriteRule ^(?:application|modules|system)\b.* index.php/$0 [L]

# 允许任何存在的文件或目录直接显示
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# 将所有其它 URL 重写到 index.php/URL
RewriteRule .* index.php [L,E=PATH_INFO:$0]';
		fwrite($file, $content);
		include 'install/finish.php';
		unlink(getcwd() . '/install/bootcms.sql');
		unlink(getcwd() . '/install/config_database.php');
		unlink(getcwd() . '/install/create_account.php');
		unlink(getcwd() . '/install/create_database.php');
		unlink(getcwd() . '/install/finish.php');
		unlink(getcwd() . '/install/footer.php');
		unlink(getcwd() . '/install/header.php');
		unlink(getcwd() . '/install/test_environment.php');
		unlink(getcwd() . '/install/license.php');
		rmdir(getcwd() . '/install');
		unlink(getcwd() . '/install.php');
	}

	/**
	 * 连接数据库
	 *
	 * @return void
	 */
	private function connectDB($db)
	{
		try
		{
			$attrs[PDO::ATTR_PERSISTENT] = true;
			return new PDO($db[0], $db[1], $db[2], $attrs);
		}
		catch (PDOException $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * 生成随机字符串
	 *
	 * @reutrn void
	 */
	private function generateCode($length = 8)
	{
		$code = '';
		$charPool = '0123456789abcdefghijklmnopqrstuvwxyz';
		for ($i = 0; $i < $length; $i++)
			$code.= $charPool[mt_rand(0, strlen($charPool) - 1)];
		return $code;
	}

	/**
	 * MySQL 格式解析
	 *
	 * @param string SQL语句
	 * @param string 表前缀
	 * @return array(SQL, LOG)
	 */
	private function parseSQL($strSQL, $prefix = '', $engine = 'InnoDB')
	{
		if (!$strSQL)
			return array();
		$query = '';
		$logData = $dataSQL = array();
		$strSQL = str_replace(array("\r", "\n\n", ";\n"), array('', "\n", ";<fox>\n"), trim($strSQL, " \n\t") . "\n");
		$arrSQL = explode("\n", $strSQL);
		foreach ($arrSQL as $value)
		{
			$value = trim($value, " \t");
			if (!$value || substr($value, 0, 2) === '--')
				continue;
			$query .= $value;
			if (substr($query, -6) != ';<fox>')
				continue;
			$query = preg_replace('/([ `]+)bc_/', "\${1}$prefix", $query, 1);
			$sqlKey = strtoupper(substr($query, 0, strpos($query, ' ')));
			switch ($sqlKey)
			{
				case 'CREATE':
					$tableName = trim(strrchr(trim(substr($query, 0, strpos($query, '('))), ' '), '` ');
					$query = str_replace(array('ENGINE=InnoDB', ';<fox>'), array("ENGINE=$engine", ';'), $query);
					$dataSQL['CREATE'][] = $query;
					$logData['CREATE'][] = $tableName;
					break;
				case 'DROP':
					preg_match('/^DROP TABLE IF EXISTS\s+`(\w+)`/', $query, $matches);
					$query = str_replace(';<fox>', '', $query);
					$dataSQL['DROP'][] = $query;
					$logData['DROP'][] = $matches[1];
					break;
				case 'ALTER':
					preg_match('/^ALTER TABLE\s+`(\w+)`\s+/', $query, $matches);
					$query = str_replace(';<fox>', '', $query);
					$dataSQL['ALTER'][] = $query;
					$logData['ALTER'][] = $matches[1];
					break;
				case 'INSERT':
				case 'REPLACE':
					preg_match('/(INSERT|REPLACE) INTO\s+`(\w+)`\s+\(.+\)\s+VALUES/', $query, $matches);
					$query = str_replace(';<fox>', '', $query);
					$sqlKey == 'INSERT' && $query = 'REPLACE' . substr($query, 6);
					$dataSQL['INSERT'][] = $query;
					$logData['INSERT'][] = $matches[2];
					break;
				case 'UPDATE':
					preg_match('/^UPDATE\s+`(\w+)`\s+SET/', $query, $matches);
					$query = str_replace(';<fox>', '', $query);
					$dataSQL['UPDATE'][] = $query;
					$logData['UPDATE'][] = $matches[1];
					break;
			}
			$query = '';
		}
		return array('SQL' => $dataSQL, 'LOG' => $logData);
	}

	/**
	 * 读取文件
	 *
	 * @param string 文件绝对路径
	 * @return string 从文件中读取的数据
	 */
	private static function read($fileName)
	{
		$data = '';
		if (!$handle = fopen($fileName, 'rb'))
			return false;
		while (!feof($handle))
			$data .= fgets($handle, 4096);
		fclose($handle);
		return $data;
	}

}

new Installer();
