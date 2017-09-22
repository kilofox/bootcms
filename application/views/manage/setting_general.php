<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">常规设置</span>
</header>
<article class="module width_full">
	<form id="setting_form">
		<header><h3>网站信息</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>网站标题</label>
					<input type="text" name="site_title" value="<?php echo $site->site_title; ?>" class="input" />
				</li>
				<li>
					<label>网站描述</label>
					<input type="text" name="site_description" value="<?php echo $site->site_description; ?>" class="input" />
				</li>
				<li>
					<label>默认关键词</label>
					<input type="text" name="meta_keywords" value="<?php echo $site->meta_keywords; ?>" class="input" />
				</li>
				<li>
					<label>默认描述</label>
					<textarea name="meta_description" class="textarea" /><?php echo $site->meta_description; ?></textarea>
				</li>
				<li>
					<label>管理员 E-mail 地址</label>
					<input type="text" name="admin_email" value="<?php echo $site->admin_email; ?>" />
				</li>
			</ul>
		</div>
		<header><h3>公司信息</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>公司名称</label>
					<input type="text" name="company" value="<?php echo $site->company; ?>" />
				</li>
				<li>
					<label>电话号码</label>
					<input type="text" name="phone" value="<?php echo $site->phone; ?>" />
				</li>
				<li>
					<label>地址</label>
					<textarea name="address" class="textarea" /><?php echo $site->address; ?></textarea>
				</li>
			</ul>
		</div>
		<header><h3>日期和时间</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>日期格式</label>
					<input type="text" name="date_format" value="<?php echo $site->date_format; ?>" /> 与PHP函数 date() 的参数相同。
				</li>
				<li>
					<label>时区</label> UTC/GMT
					<select name="timezone">
						<option value="-12"<?php if ($site->timezone == -12): ?> selected="selected"<?php endif; ?>>-12:00</option>
						<option value="-11"<?php if ($site->timezone == -11): ?> selected="selected"<?php endif; ?>>-11:00</option>
						<option value="-10"<?php if ($site->timezone == -10): ?> selected="selected"<?php endif; ?>>-10:00</option>
						<option value="-9"<?php if ($site->timezone == -9): ?> selected="selected"<?php endif; ?>>-9:00</option>
						<option value="-8"<?php if ($site->timezone == -8): ?> selected="selected"<?php endif; ?>>-8:00</option>
						<option value="-7"<?php if ($site->timezone == -7): ?> selected="selected"<?php endif; ?>>-7:00</option>
						<option value="-6"<?php if ($site->timezone == -6): ?> selected="selected"<?php endif; ?>>-6:00</option>
						<option value="-5"<?php if ($site->timezone == -5): ?> selected="selected"<?php endif; ?>>-5:00</option>
						<option value="-4"<?php if ($site->timezone == -4): ?> selected="selected"<?php endif; ?>>-4:00</option>
						<option value="-3"<?php if ($site->timezone == -3): ?> selected="selected"<?php endif; ?>>-3:00</option>
						<option value="-2"<?php if ($site->timezone == -2): ?> selected="selected"<?php endif; ?>>-2:00</option>
						<option value="-1"<?php if ($site->timezone == -1): ?> selected="selected"<?php endif; ?>>-1:00</option>
						<option value="0"<?php if ($site->timezone == 0): ?> selected="selected"<?php endif; ?>>-0:00</option>
						<option value="1"<?php if ($site->timezone == 1): ?> selected="selected"<?php endif; ?>>+1:00</option>
						<option value="2"<?php if ($site->timezone == 2): ?> selected="selected"<?php endif; ?>>+2:00</option>
						<option value="3"<?php if ($site->timezone == 3): ?> selected="selected"<?php endif; ?>>+3:00</option>
						<option value="4"<?php if ($site->timezone == 4): ?> selected="selected"<?php endif; ?>>+4:00</option>
						<option value="5"<?php if ($site->timezone == 5): ?> selected="selected"<?php endif; ?>>+5:00</option>
						<option value="6"<?php if ($site->timezone == 6): ?> selected="selected"<?php endif; ?>>+6:00</option>
						<option value="7"<?php if ($site->timezone == 7): ?> selected="selected"<?php endif; ?>>+7:00</option>
						<option value="8"<?php if ($site->timezone == 8): ?> selected="selected"<?php endif; ?>>+8:00</option>
						<option value="9"<?php if ($site->timezone == 9): ?> selected="selected"<?php endif; ?>>+9:00</option>
						<option value="10"<?php if ($site->timezone == 10): ?> selected="selected"<?php endif; ?>>+10:00</option>
						<option value="11"<?php if ($site->timezone == 11): ?> selected="selected"<?php endif; ?>>+11:00</option>
						<option value="12"<?php if ($site->timezone == 12): ?> selected="selected"<?php endif; ?>>+12:00</option>
					</select>
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="hidden" name="sid" value="<?php echo $site->id; ?>" />
				<input type="submit" value="更新" class="alt_btn" />
				<input type="reset" value="重置" />
			</div>
		</footer>
	</form>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		$('#setting_form').submit(function() {
			$.post(homeUrl + 'manage/general_setting', $('#setting_form').serialize(), function(r) {
				showMsg(r.title, r.content);
			}, 'json');
			return false;
		});
	});
</script>