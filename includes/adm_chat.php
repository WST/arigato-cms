<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$r = db_query ("select * from $INFO[db_prefix]chatconf");
$f = db_fetch_assoc ($r);

$chatblock = ($f['chatblock'] == 1) ? " checked" : "";

$body = "<h3>Администрирование - конфигурация чата</h3>
<a href='admin.php'>Меню</a><br />
<form action='admin.php' method='post'>
<input type='hidden' name='chat' value='ok' />
<input type='checkbox' class='radio' name='chatblock' value='1'$chatblock /> Блок чата на сайте.<br />
<table><tr>
<td>Максимум сообщений:</td>
<td><input type='text' maxlength='6' size='16' name='maxmes' value='$f[maxmes]' /></td>
</tr><tr>
<td>Время хранения истории (мин):</td>
<td><input type='text' maxlength='6' size='16' name='maxtime' value='$f[maxtime]' /></td>
</tr><tr>
<td>Максимальная длинна сообщения:</td>
<td><input type='text' maxlength='6' size='16' name='maxlen' value='$f[maxlen]' /></td>
</tr><tr>
<td>Частота обновлений (сек):</td>
<td><input type='text' maxlength='6' size='16' name='refresh' value='$f[refresh]' /></td>
</tr>
</table>
<b>Примечание:</b> система бана по IP-адресу, автоцензура и запрещенные имена действуют из системы комментариев.<br /><br />
<input type='submit' value='Отправить' />
</form>
";

?>