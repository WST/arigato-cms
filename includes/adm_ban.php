<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$list = '';
$r = db_query ("select * from $INFO[db_prefix]ban where mode='$mode' order by ip");
while ($f = db_fetch_assoc ($r))
{
	$list .= "<li />$f[ip] (<a href='admin.php?action=ip_del&amp;k_ban=$f[k_ban]' onclick='return confirm(\"Удалить IP $f[ip]?\");'>удалить</a>)<br />\n";
}

$title = $info = "";
switch ($mode)
{
	case 0: $title = 'запреты IP в комментариях';
			$info = 'Пользователи с указанными ip-адресами не смогут оставлять сообщения в комментариях к статьям.';
			break;
	case 1: $title = 'запреты IP в админке';
			$info = 'Пользователи с указанными ip-адресами не смогут войти в панель администрирования.<br />
			ВНИМАНИЕ! Не указывайте тут свой ip-адрес.';
			break;
	case 2: $title = 'запреты IP на доступ';
			$info = 'Пользователи с указанными ip-адресами не смогут попасть на сайт и просматривать его.';
			break;
	case 3: $title = 'запреты IP на отправку письма';
			$info = 'Пользователи с указанными ip-адресами не смогут отправлять письма администратору сайта.';
			break;
}

$body = "<h3>Администрирование - $title</h3>
<a href='admin.php'>Меню</a><br /><br />
$info<br /><br />
$list
<form action='admin.php' method='post' onsubmit='return (this.ip.value.length>6);'>
<input type='hidden' name='ban' value='ok'>
<input type='hidden' name='mode' value='$mode'>
IP (например, 127.0.0.1 или по маске 127.0.0.*, 127.0.*.*):<br />
<input type='text' maxlength='15' size='30' name='ip'>
<input type='submit' value='Добавить'>
</form>
";

?>