<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

if ( isset ($_GET['id']) )
{ // Правка сообщения:
	$id = anti_inj ($_GET['id']);
	$r = db_query ("select * from $INFO[db_prefix]book where k_post='$id'");
	if ( $f = db_fetch_assoc ($r) )
	{
		$your_name = protection ($f['user']);
		$your_email = protection ($f['email']);
		$your_url = protection ($f['url']);
		$your_icq = protection ($f['icq']);
		$your_message = protection ($f['post']);
		$refer = protection ($_SERVER['HTTP_REFERER']);
	} else $your_name = $your_email = $your_url = $your_icq = $your_message = "";
	$body = "<h3>Администрирование - комментарии - правка</h3>
	<a href='admin.php'>Меню</a><br /><br />
	<form action='admin.php' method='post'>
	<input type='hidden' name='gbmesid' value='$id' />
	<input type='hidden' name='refer' value='$refer' />
	<table><tr>
	<td>Имя: </td>
	<td>
	<input type='text' size='30' maxlength='16' name='your_name' value='$your_name' />
	</tr><tr>
	<td>e-mail: </td>
	<td>
	<input type='text' size='30' maxlength='40' name='your_email' value='$your_email' />
	</tr><tr>
	<td>URL сайта: </td>
	<td>
	<input type='text' size='30' maxlength='60' name='your_url' value='$your_url' />
	</tr><tr>
	<td>ICQ: </td>
	<td>
	<input type='text' size='30' maxlength='16' name='your_icq' value='$your_icq' /></td>
	</tr></table>
	Сообщение:<br />
	<textarea name='your_message' rows='12' cols='80'>$your_message</textarea>
	<br /><br />
	<input type='submit' value='Изменить' />
	</form>
	";
} else
{ // Список сообщений:
	$order = 'mesdate desc';
	if ( isset ($_GET['sort']) || isset ($_COOKIE['C_SORT']) )
	{
		$st = isset ($_GET['sort']) ? $_GET['sort'] : $_COOKIE['C_SORT'];
		SetCookie ("C_SORT", $st, time() + 60 * 60 * 24 * 30 * 12);
		if ( $st === 'art' ) $order = 'k_art desc,mesdate desc';
		if ( $st === 'abc' ) $order = 'user,k_art desc,mesdate desc';
		if ( $st === 'ip' ) $order = 'ip,k_art desc,mesdate desc';
	} else $st = 'dat';

	$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]book");
	$fg = db_fetch_assoc ($rg);
	$cnt = $fg['CNT'];
	$page = isset ($_GET['c']) ? $_GET['c'] : 1;

	$list = '';
	$r = db_query ("select k_art,mesdate,user,ip from $INFO[db_prefix]book order by $order limit " . ($page - 1) * 100 . ", 100");
	while ( $f = db_fetch_assoc ($r) )
	{
		$ra = db_query ("select caption from $INFO[db_prefix]art where k_art='$f[k_art]'");
		if ( $fa = db_fetch_assoc ($ra) ) $cap = $fa['caption'];
		else $cap = '&nbsp;';
		$dt = date ('d.m.Y H:i', $f['mesdate']);
		$list .= "<tr><td>" . protection ($f[user]) . "</td>\n";
		$list .= "<td>$f[ip]</td>\n";
		$list .= "<td>$dt</td>\n";
		$list .= "<td><a href='index.php?art=$f[k_art]' target='_blank'>$cap</a></td></tr>\n";
	}

	$pagebar = '';
	if ( $cnt > 100 )
	{
		for ($p = 0; $p < $cnt / 100; $p++)
		{
			if ( $p != $page - 1 ) $pagebar .= "<a href='admin.php?action=comment&amp;sort=$st&amp;c=" . ($p + 1) . "'>" . ($p + 1) . "</a> ";
			else $pagebar .= "<b>" . ($p + 1) . "</b> ";
		}
		$pagebar = "Страницы: $pagebar<br />";
	}

	$sort = "";
	if ( $st === "abc" ) $sort .= "<th>имя</th>\n";
	else $sort .= "<th><a href='admin.php?action=comment&amp;sort=abc'>имя</a></th>\n";
	if ( $st === "ip" ) $sort .= "<th>ip-адрес</th>\n";
	else $sort .= "<th><a href='admin.php?action=comment&amp;sort=ip'>ip-адрес</a></th>\n";
	if ( $st === "dat" ) $sort .= "<th>дата</th>\n";
	else $sort .= "<th><a href='admin.php?action=comment&amp;sort=dat'>дата</a></th>\n";
	if ( $st === "art" ) $sort .= "<th>раздел</th>\n";
	else $sort .= "<th><a href='admin.php?action=comment&amp;sort=art'>раздел</a></th>\n";
	$sort = "<tr>$sort</tr>";

	$body = "<h3>Администрирование - комментарии</h3>
	<a href='admin.php'>Меню</a><br /><br />
	<table border='1'>
	$sort
	$list</table>
	$pagebar
	<br />
	Всего в списке: <b>$cnt</b>
	";
}

?>