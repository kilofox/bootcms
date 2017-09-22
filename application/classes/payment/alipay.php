<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 支付宝支付。
 *
 * @package	BootCMS
 * @category	辅助类
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Payment_Alipay extends Payment {

	public function __construct($config = array())
	{
		// 设置父类
		parent::__construct($config);
		$this->config = $config;
		if ($this->config['service_type'] == 1)
			$this->config['service'] = 'trade_create_by_buyer';
		else if ($this->config['service_type'] == 2)
			$this->config['service'] = 'create_direct_pay_by_user';
		else
			$this->config['service'] = 'create_partner_trade_by_buyer';
		$this->config['gateway_url'] = 'https://mapi.alipay.com/gateway.do?';
		$this->config['gateway_method'] = 'POST';
	}

	/**
	 * 建立请求，以表单HTML形式构造（默认）
	 * @param $para_temp 请求参数数组
	 * @param $method 提交方式。两个值可选：post、get
	 * @param $button_name 确认按钮显示文字
	 * @return 提交表单HTML文本
	 */
	public function buildRequestForm($para_temp, $method, $button_name)
	{
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		$sHtml = '<form id="alipaysubmit" name="alipaysubmit" action="' . $this->config['gateway_url'] . '_input_charset=' . $this->config['input_charset'] . '" method="' . $method . '" target="_blank">' . "\n";
		while (list($key, $val) = each($para))
		{
			$sHtml.= '<input type="hidden" name="' . $key . '" value="' . $val . '"/>' . "\n";
		}
		// submit 按钮不要有 name 属性
		$sHtml .= '<input type="submit" value="' . $button_name . '" class="submit"/>' . "\n" . '</form>' . "\n";
		return $sHtml;
	}

	/**
	 * 生成要请求给支付宝的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组
	 */
	public function buildRequestPara($para_temp)
	{
		$para_temp['service'] = $this->config['service'];
		$para_temp['seller_email'] = $this->config['account'];
		$para_temp['partner'] = $this->config['partner'];
		$para_temp['_input_charset'] = $this->config['input_charset'];
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);
		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);
		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->config['sign_type']));
		return $para_sort;
	}

	/**
	 * 生成签名结果。
	 *
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	public function buildRequestMysign($para_sort)
	{
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkString($para_sort);
		$mysign = '';
		switch (strtoupper(trim($this->config['sign_type'])))
		{
			case 'MD5':
				$mysign = $this->md5Sign($prestr, $this->config['key']);
				break;
			default:
				$mysign = '';
				break;
		}
		return $mysign;
	}

	/**
	 * 针对notify_url验证消息是否是支付宝发出的合法消息。
	 *
	 * @return 验证结果
	 */
	public function verifyNotify()
	{
		if (empty($_POST))
			return false;
		// 生成签名结果
		$isSign = $this->getSignVerify($_POST, $_POST['sign']);
		// 获取支付宝远程服务器ATN结果，验证是否是支付宝发来的消息
		$responseTxt = 'true';
		if ($_POST['notify_id'])
			$responseTxt = $this->getResponse($_POST['notify_id']);
		// 验证
		// $responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
		// isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
		if (preg_match('/true$/i', $responseTxt) && $isSign)
			return true;
		else
			return false;
	}

	/**
	 * 针对return_url验证消息是否是支付宝发出的合法消息。
	 *
	 * @return 验证结果
	 */
	public function verifyReturn()
	{
		if (empty($_GET))
			return false;
		// 生成签名结果
		$isSign = $this->getSignVerify($_GET, $_GET['sign']);
		// 获取支付宝远程服务器ATN结果，验证是否是支付宝发来的消息
		$responseTxt = 'true';
		if ($_GET['notify_id'])
			$responseTxt = $this->getResponse($_GET['notify_id']);
		// 验证
		// $responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
		// isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
		if (preg_match('/true$/i', $responseTxt) && $isSign)
			return true;
		else
			return false;
	}

	/**
	 * 获取返回时的签名验证结果。
	 *
	 * @param	string	通知返回来的参数数组
	 * @param	string	返回的签名结果
	 * @return	boolean	签名验证结果
	 */
	private function getSignVerify($para_temp, $sign)
	{
		//除去待签名参数数组中的空值和签名参数
		$para_filter = $this->paraFilter($para_temp);
		//对待签名参数数组排序
		$para_sort = $this->argSort($para_filter);

		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkString($para_sort);
		$isSgin = false;
		switch (strtoupper(trim($this->config['sign_type'])))
		{
			case 'MD5':
				$isSgin = $this->md5Verify($prestr, $sign, $this->config['key']);
				break;
			default:
				$isSgin = false;
				break;
		}
		return $isSgin;
	}

	/**
	 * 获取远程服务器ATN结果,验证返回URL。
	 *
	 * 验证结果集：
	 * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
	 * true 返回正确信息
	 * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
	 * @param $notifyId 通知校验ID
	 * @return 服务器ATN结果
	 */
	private function getResponse($notifyId)
	{
		$transport = strtolower($this->config['transport']);
		if ($transport == 'https')
			$verifyUrl = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
		else
			$verifyUrl = 'http://notify.alipay.com/trade/notify_query.do?';
		$verifyUrl = $verifyUrl . 'partner=' . $this->config['partner'] . '&notify_id=' . $notifyId;
		$responseTxt = $this->getHttpResponse($verifyUrl, $this->config['cacert']);
		return $responseTxt;
	}

	/**
	 * 远程获取数据，GET模式。
	 *
	 * 注意：文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 *
	 * @param	string	指定URL完整路径地址
	 * @param	string	指定当前工作目录绝对路径
	 * return	远程输出的数据
	 */
	private function getHttpResponse($url, $cacertUrl)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);   // 过滤HTTP头
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // SSL证书认证
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 严格认证
		curl_setopt($ch, CURLOPT_CAINFO, $cacertUrl); // 证书地址
		$output = curl_exec($ch);
		//var_dump(curl_error($ch));
		curl_close($ch);
		return $output;
	}

	/**
	 * 签名字符串。
	 *
	 * @param $prestr 需要签名的字符串
	 * @param $key 私钥
	 * return 签名结果
	 */
	private function md5Sign($prestr, $key)
	{
		$prestr = $prestr . $key;
		return md5($prestr);
	}

	/**
	 * 除去数组中的空值和签名参数。
	 *
	 * @param $para 签名参数组
	 * return 去掉空值与签名参数后的新签名参数组
	 */
	private function paraFilter($para)
	{
		$para_filter = array();
		while (list($key, $val) = each($para))
		{
			if ($key == 'sign' || $key == 'sign_type' || $val == '')
				continue;
			else
				$para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}

	/**
	 * 对数组排序。
	 *
	 * @param $para 排序前的数组
	 * return 排序后的数组
	 */
	private function argSort($para)
	{
		ksort($para);
		reset($para);
		return $para;
	}

	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串。
	 *
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	private function createLinkString($para)
	{
		$arg = '';
		while (list($key, $val) = each($para))
		{
			$arg.= $key . '=' . $val . '&';
		}
		//去掉最后一个&字符
		$arg = substr($arg, 0, count($arg) - 2);
		//如果存在转义字符，那么去掉转义
		if (get_magic_quotes_gpc())
			$arg = stripslashes($arg);
		return $arg;
	}

	/**
	 * 验证签名。
	 *
	 * @param $prestr 需要签名的字符串
	 * @param $sign 签名结果
	 * @param $key 私钥
	 * return 签名结果
	 */
	private function md5Verify($prestr, $sign, $key)
	{
		$prestr = $prestr . $key;
		$mysgin = md5($prestr);
		return $mysgin == $sign;
	}

}
