<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

if ( $_GET['cmd'] === 'up' || $_GET['cmd'] === 'down' )
{
	$r = db_query ("select pos from $INFO[db_prefix]links where k_link='$_GET[k_link]'");
	if ( $f = db_fetch_assoc ($r) )
	{
		$pos = $f['pos'];
		$r = ($_GET['cmd'] === 'up')
			? db_query ("select k_link,pos from $INFO[db_prefix]links where pos<$pos order by pos desc limit 1")
			: db_query ("select k_link,pos from $INFO[db_prefix]links where pos>$pos order by pos limit 1");
		if ($f = db_fetch_assoc ($r) )
		{
			db_query ("update $INFO[db_prefix]links set pos='$pos' where k_link='$f[k_link]'");
			db_query ("update $INFO[db_prefix]links set pos='$f[pos]' where k_link='$_GET[k_link]'");
		}
	}
	header ("location:admin.php?action=link");
	exit;
}

$list = '';
$r = db_query ("select * from $INFO[db_prefix]links order by pos");
while ($f = db_fetch_assoc ($r))
{
	$list .= "<li /><a href='$f[url]' target='_blank'>$f[title]</a>
	&nbsp;(<a href='admin.php?action=link&amp;cmd=down&amp;k_link=$f[k_link]' title='Опустить'><b>&#150;</b></a>)
	&nbsp;(<a href='admin.php?action=link&amp;cmd=up&amp;k_link=$f[k_link]' title='Поднять'><b>+</b></a>)
	&nbsp;(<a href='admin.php?action=link_del&amp;k_link=$f[k_link]' onclick='return confirm(\"Удалить \\\"$f[title]\\\"?\");'>удалить</a>)<br />\n";
}

$body = "<h3>Администрирование - блок ссылок</h3>
<a href='admin.php'>Меню</a><br /><br />
$list
<form action='admin.php' method='post' onsubmit='return (this.url.value.length>0&&this.title.value.length>0);'>
<input type='hidden' name='link' value='ok'>
<table><tr>
<td>Название:</td>
<td><input type='text' maxlength='255' size='48' name='title'></td>
</tr><tr>
<td>URL-адрес:</td>
<td><input type='text' maxlength='255' size='48' name='url'></td>
</tr></table>
<input type='submit' value='Добавить'>
</form>
";

?>