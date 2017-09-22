<?php $homeUrl = url::base(); ?>
<h1 class="padding-b">注册码</h1>
<h6 class="padding-none space-b">用户注册码在每次使用后将被重置。</h6>
<table border="0" cellspacing="2px" cellpadding="0">
	<tr>
		<th>级别</th>
		<th>角色</th>
		<th>描述</th>
		<th>代码</th>
	</tr>
	<?php $i = 0; ?>
	<?php foreach ($codes as $code): ?>
		<?php $odd_or_even = $i++ % 2 == 0 ? 'odd' : 'even'; ?>
		<tr class="<?php echo $odd_or_even; ?>">
			<td><?php echo $code->id; ?></td>
			<td><strong><?php echo ucwords($code->name); ?></strong></td>
			<td><?php echo $code->description; ?></td>
			<td><strong class="red"><?php echo $code->authorization_code; ?></strong></td>
		</tr>
	<?php endforeach; ?>
	<tr>
		<th colspan="4" class="table_end"></th>
	</tr>
</table>
