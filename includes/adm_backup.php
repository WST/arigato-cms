<?php

if ( ! defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

if ( ! file_exists ("backup") ) mkdir ("backup", 0755);

if ( isset ($_GET['restore']) )
{
	$rest = protection ($_GET['restore']);
	$body = "Восстановление бэкапа <b>$rest</b>.<br />
	В случае восстановления все сохраненные в $rest таблицы заместят текущие таблицы на сайте.<br />
	Вы уверены, что нужно продолжать?<br />
	<form action='admin.php' method='post'>
	<input type='hidden' name='restore' value='$rest' />
	<input type='submit' name='yes' value='Восстановить бэкап' /> <input type='submit' name='no' value='Отмена' />
	</form>
	";
} elseif ( isset ($_GET['backup']) )
{
	$tables = array (
				"art" => "статьи сайта",
				"news" => "новости",
				"photos" => "фотоальбом",
				"book" => "комментарии пользователей",
				"pools" => "голосования",
				"answers" => "результаты голосований",
				"config" => "конфигурации сайта",
				"links" => "блок ссылок",
				"blocks" => "настройки блоков сайта",
				"chatconf" => "конфигурации чата",
				"messages" => "информационные сообщения",
				"email" => "адреса рассылки",
				"dlcnt" => "счетчики скачиваний файлов",
				"censor" => "цензура в комментариях",
				"ban" => "таблица банов",
				"badname" => "запрещенные имена в комментариях",
				"refer" => "обратные ссылки",
				"find" => "поисковые запросы",
				"counter" => "посетители сайта");
	$list = "";
	foreach ($tables as $tname => $discr)
	{
		$list .= "<input type='checkbox' class='radio' name='table[]' value='$tname' checked /> <b>$tname</b> - $discr<br />\n";
	}
	$body = "Выберите таблицы, которые будут сохранены:<br />
	<form action='admin.php?action=backup' method='post' onsubmit='return confirm(\"Произвести бэкап выбранных таблиц?\");'>
	$list
	<br />
	<input type='submit' name='backup' value='Готово' /><br />
	</form>";
	
} else
{
	$list = array();
	$dir = opendir ("backup");
	while ( $d = readdir ($dir) )
		if ( ! is_dir ("backup/$d") )
		{
			$fileinfo = stat ("backup/$d");
			$creat = ( $fileinfo["mtime"] < $fileinfo["ctime"] ) ? $fileinfo["mtime"] : $fileinfo["ctime"];
			$list[$d] = $creat;
		}
	closedir ($dir);
	arsort ($list);

	$files = "";
	foreach ($list as $name => $date)
	{
		$size = get_size ("backup/$name");
		$date = date ('d.m.Y H:i', $date);
		$files .= "<tr>
		<td><input type='checkbox' class='radio' name='file[]' value='$name' /></td>
		<td><a href='admin.php?action=backup&amp;restore=$name' onclick='return confirm(mes+\"$date?\");'>$name</a></td>
		<td>$date</td>
		<td>$size</td>
		</tr>	
		";
	}

	if ( ! empty ($files) )
		$files = "<script type='text/javascript'><!--
		mes='Восстановить бэкап базы данных от ';
		--></script>
		<form action='admin.php?action=backup' method='post' onsubmit='return confirm(\"Удалить выбранные файлы?\");'>
		<table border='1' cellspacing='1' cellpadding='1'>
		$files
		</table><br />
		<input type='submit' name='del' value='Удалить выбранные файлы' /><br />
		</form>";
	$body = "(<a href='admin.php?action=backup&amp;backup=1');'>Сделать бэкап</a>)
	$files";
}

$body = "
<h3>Администрирование - бэкап базы данных</h3>
<a href='admin.php'>Меню</a><br /><br />
$body
";

?>