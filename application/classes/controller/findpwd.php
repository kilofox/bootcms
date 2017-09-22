<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 密码找回控制器。
 *
 * @package	BootCMS
 * @category	控制器
 * @Author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Findpwd extends Controller_Template {

	/**
	 * Before 方法
	 */
	public function before()
	{
		parent::before();
		$cache = Cache::instance();
		if (!($views = $cache->get('views', false)))
		{
			$global = BootPHP::$config->load('global');
			$views = $global->get('views');
			$cache->set('views', $views);
		}
		foreach ($views as $key => $view)
		{
			if (!is_array($view))
				$this->template->$key = View::factory($view);
			else
				$this->template->$key = $view;
		}
		$this->model = Model::factory('User');
		$this->homeUrl = Url::base();
		$this->template->homeUrl = $this->homeUrl;
	}

	/**
	 * After 方法
	 */
	public function after()
	{
		parent::after();
	}

	/**
	 * 找回密码第一步：核对用户名
	 */
	public function action_index()
	{
		$this->template->body = View::factory('member/findpwd_step1');
	}

	/**
	 * 找回密码第二步：确认 E-mail
	 */
	public function action_findpwd()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$email = Functions::text($this->request->post('username'));
			$user = $this->model->loadByEmail($email);
			if ($user)
			{
				$output->status = 1;
				$output->title = '找回密码';
				$output->title = '用户已找到，可以发送邮件。';
				Cookie::set('fpwn', $user->username);
				// 检查密码找回次数
				if (Functions::makeDate($user->last_reset, 'd') == date('d') && $user->resets > 4)
				{
					$output->status = 2;
					$output->title = '找回密码';
					$output->content = '您今日已经进行过 5 次找回密码，请明日再试。';
				}
				// 第二日，密码找回次数清零
				if (Functions::makeDate($user->last_reset, 'd') <> date('d'))
				{
					$user->resets = 0;
					foreach ($user as $k => $v)
					{
						if (!in_array($k, array('id', 'resets')))
							unset($user->$k);
					}
					$this->model->update();
				}
			}
			else
			{
				$output->status = 2;
				$output->title = '用户不存在';
				$output->content = '您输入的邮箱不存在，请核对后重新输入。';
			}
			exit(json_encode($output));
		}
		$username = Cookie::get('fpwn');
		$user = $this->model->loadByUsername($username);
		!$user and $this->request->redirect();
		$mask = '';
		$atPos = strpos($user->email, '@');
		for ($i = 0; $i < $atPos - 2; $i++)
			$mask .= '*';
		$email = substr($user->email, 0, 1) . $mask . substr($user->email, $atPos - 1);
		$this->template->title = '找回密码';
		$this->template->body = View::factory('member/findpwd_step2')
			->bind('uid', $user->id)
			->bind('email', $email);
	}

	/**
	 * 找回密码第三步：发送电子邮件
	 */
	public function action_sendemail()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$userId = (int) $this->request->post('uid');
			$user = $this->model->load($userId);
			$username = Cookie::get('fpwn');
			$user->username <> $username and $this->request->redirect();
			if ($user)
			{
				// 设置邮件内容
				$subject = '找回密码';
				$sendTime = time();
				$code = Encrypt::instance()->encode($user->id . '|' . $sendTime);
				$url = 'http://' . $_SERVER['SERVER_NAME'] . $this->homeUrl . 'findpwd/resetpwd/?code=' . rawurlencode($code);
				$message = '尊敬的' . $user->nickname . '：<br />'
					. '您在 Slowood 点击了“忘记密码”按钮，故系统自动为您发送了这封邮件。您可以点击下面的链接重置您的密码：<br />'
					. '<a href="' . $url . '">' . $url . '</a><br />'
					. '此链接有效期为一个小时，请在一小时内点击链接进行修改，每天最多允许找回5次密码。如果您不需要修改密码，或者您从未点击过“忘记密码”按钮，请忽略本邮件。';
				$opts = BootPHP::$config->load('email')->default;
				$mail = Mail::instance($opts);
				$mail->setFrom($opts['smtp_user'], 'Slowood');
				$mail->setTo($user->email);
				$mail->setSubject($subject);
				$mail->setBody($message);
				$mail->send();
				// 记录重置密码的时间
				$user->resets++;
				$user->last_reset = $sendTime;
				foreach ($user as $k => $v)
				{
					if (!in_array($k, array('id', 'resets', 'last_reset')))
						unset($user->$k);
				}
				$this->model->update();
				Cookie::delete('fpwn');
				$output->status = 1;
				$output->title = '邮件已发送';
				$output->content = $sendTime;
			}
			exit(json_encode($output));
		}
		$this->template->title = '找回密码';
		$this->template->body = View::factory('member/findpwd_step3');
	}

	/**
	 * 找回密码第四步：重设密码
	 */
	public function action_resetpwd()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$code = rawurldecode(Functions::text($this->request->post('code')));
			$code = Encrypt::instance()->decode($code);
			$code = explode('|', $code);
			if (count($code) == 2 && $code[0])
			{
				try
				{
					$user = $this->model->load($code[0]);
					if ($this->request->post('password'))
					{
						$user->password = Functions::text($this->request->post('password'));
						$user->password_confirm = Functions::text($this->request->post('password_confirm'));
						$user->last_reset = $user->last_reset - 3600;
					}
					if ($this->model->update())
					{
						$output->status = 1;
						$output->title = '密码已更新';
						$output->content = '您的账户密码已经更新完毕。';
					}
					else
					{
						$output->status = 2;
						$output->title = '密码未更新';
						$output->content = '您的账户密码与原来的一致，没有更新。';
					}
				}
				catch (Validation_Exception $e)
				{
					$errors = $e->errors('models');
					foreach ($errors as $ev)
					{
						$output->status = 4;
						$output->title = '操作失败';
						$output->content = $ev;
						break;
					}
				}
			}
			exit(json_encode($output));
		}
		$codeRaw = rawurldecode(Functions::text($this->request->query('code')));
		$code = Encrypt::instance()->decode($codeRaw);
		$code = explode('|', $code);
		if (count($code) == 2 && $code[0])
		{
			$user = $this->model->load($code[0]);
			if ($user->last_reset == $code[1] && time() - $user->last_reset < 3600)
			{
				$this->template->body = View::factory('member/findpwd_step4')
					->bind('code', $codeRaw);
			}
			else
			{
				$message = '找回密码链接已失效！';
				// 检查密码找回次数
				if (Functions::makeDate($user->last_reset, 'd') == date('d') && $user->resets < 5)
					$message .= '<br /><br />您可以<a href="' . $this->homeUrl . 'findpwd">重新找回密码</a>。';
				$this->template->title = '找回密码';
				$this->template->body = View::factory('member/findpwd_message')
					->bind('message', $message);
			}
		}

		$this->request->redirect();
	}

}
