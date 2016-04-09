<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

if ( ! empty ($error_msg) ) $error_msg = "<div align=center style='border:1px solid #000000;background:#FFA0A0;color:#000000;'><b>$error_msg</b></div>";

$body = "<h3>Администрирование - смена пароля</h3>
<a href='admin.php'>Меню</a><br />
<form action='admin.php' method='post'>
<input type='hidden' name='password' value='ok'>
<table><tr>
$error_msg
<td>Старый пароль:</td>
<td><input type='password' maxlength='60' size='30' name='oldpass'></td>
</tr><tr>
<td>Новый пароль:</td>
<td><input type='password' maxlength='60' size='30' name='newpass1'></td>
</tr><tr>
<td>Еще раз:</td>
<td><input type='password' maxlength='60' size='30' name='newpass2'></td>
</tr><tr>
</tr></table>
<input type='submit' value='Отправить'>
</form>
";

?>