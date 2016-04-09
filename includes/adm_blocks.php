<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$banner = protection ($f_config['banner']);
$c_title = protection ($f_config['c_title']);
$find = ($f_config['find'] == 1) ? " checked" : "";
$spam = ($f_config['spam'] == 1) ? " checked" : "";
$mail = ($f_config['mail'] == 1) ? " checked" : "";
$lastnew0 = $lastnew1 = $lastnew2 = "";
switch ( $f_config['lastnew'] )
{
	case 0: $lastnew0 = " checked"; break;
	case 1: $lastnew1 = " checked"; break;
	case 2: $lastnew2 = " checked"; break;
}
$durat0 = $durat1 = $durat2 = "";
switch ( $f_config['durat'] )
{
	case 0: $durat0 = " checked"; break;
	case 1: $durat1 = " checked"; break;
	case 2: $durat2 = " checked"; break;
}
$stat0 = $stat1 = $stat2 = "";
switch ( $f_config['b_stat'] )
{
	case 0: $stat0 = " checked"; break;
	case 1: $stat1 = " checked"; break;
	case 2: $stat2 = " checked"; break;
}

$body = "<h3>Администрирование - настройка блоков сайта</h3>
<a href='admin.php'>Меню</a><br />
<form action='admin.php' method='post'>
<input type='hidden' name='blocks' value='ok' />
<input type='checkbox' class='radio' name='find' value='1'$find /> Поиск по сайту.<br />
<input type='checkbox' class='radio' name='spam' value='1'$spam /> Подписка на рассылку.<br />
<input type='checkbox' class='radio' name='mail' value='1'$mail /> Письмо администратору сайта.<br />
<table><tr>
<td>Последняя новость сайта:</td>
<td><input type='radio' class='radio' name='lastnew' value='0'$lastnew0 /> нет</td>
<td><input type='radio' class='radio' name='lastnew' value='1'$lastnew1 /> есть всегда</td>
<td><input type='radio' class='radio' name='lastnew' value='2'$lastnew2 /> есть в течении срока давности</td>
</tr><tr>
<td>Вывод времени работы скриптов:</td>
<td><input type='radio' class='radio' name='durat' value='0'$durat0 /> нет</td>
<td><input type='radio' class='radio' name='durat' value='1'$durat1 /> есть</td>
<td><input type='radio' class='radio' name='durat' value='2'$durat2 /> только для администратора</td>
</tr><tr>
<td>Статистика сайта:</td>
<td><input type='radio' class='radio' name='b_stat' value='0'$stat0 /> нет</td>
<td><input type='radio' class='radio' name='b_stat' value='1'$stat1 /> есть</td>
<td><input type='radio' class='radio' name='b_stat' value='2'$stat2 /> только для администратора</td>
</tr></table>
<table><tr>
<td>Колонтитул (текст или html):</td>
<td><textarea name='c_title' rows='4' cols='48'>$c_title</textarea></td>
</tr><tr>
<td>html-код баннера (счетчика):</td>
<td><textarea name='banner' rows='4' cols='48'>$banner</textarea></td>
</tr></table>
<input type='submit' value='Отправить' />
</form>
";

?>