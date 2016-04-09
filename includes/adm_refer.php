<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$order = 'url';
if ( isset ($_GET['sort']) || isset ($_COOKIE['R_SORT']) )
{
	$st = isset ($_GET['sort']) ? $_GET['sort'] : $_COOKIE['R_SORT'];
	SetCookie ("R_SORT", $st, time() + 60 * 60 * 24 * 30 * 12);
	if ( $st === 'cnt' ) $order = 'cnt desc,url';
	if ( $st === 'dat' ) $order = 'last_d desc,url';
	if ( $st === 'ip' ) $order = 'ip,last_d desc,url';
} else $st = 'abc';

$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]refer");
$fg = db_fetch_assoc ($rg);
$cnt = $fg['CNT'];
$page = isset ($_GET['c']) ? $_GET['c'] : 1;

$list = '';
$r = db_query ("select * from $INFO[db_prefix]refer order by $order limit " . ($page - 1) * 100 . ", 100");
while ($f = db_fetch_assoc ($r))
{
	$dt = date ('d.m.Y H:i', $f['last_d']);
	if ( $f['url'] !== '' ) $list .= "<tr><td><a href='$f[url]' target='_blank'>" . url_format ($f['url']) . "</a></td>\n";
	else $list .= "<tr><td>about:blank</td>\n";
	$list .= "<td style='text-align:center;color:#800000;'><b>$f[cnt]</b></td>\n";
	$list .= "<td style='text-align:center;'>$dt</td>\n";
	$list .= "<td>$f[ip]</td></tr>\n";
}

$pagebar = '';
if ( $cnt > 100 )
{
	for ($p = 0; $p < $cnt / 100; $p++)
	{
		if ( $p != $page - 1 ) $pagebar .= "<a href='admin.php?action=refer&amp;sort=$st&amp;c=" . ($p + 1) . "'>" . ($p + 1) . "</a> ";
		else $pagebar .= "<b>" . ($p + 1) . "</b> ";
	}
	$pagebar = "Страницы: $pagebar<br />";
}

$sort = "";
if ( $st === "abc" ) $sort .= "<th>адрес</th>\n";
else $sort .= "<th><a href='admin.php?action=refer&amp;sort=abc'>адрес</a></th>\n";
if ( $st === "cnt" ) $sort .= "<th>переходов</th>\n";
else $sort .= "<th><a href='admin.php?action=refer&amp;sort=cnt'>переходов</a></th>\n";
if ( $st === "dat" ) $sort .= "<th>дата последнего перехода</th>\n";
else $sort .= "<th><a href='admin.php?action=refer&amp;sort=dat'>дата последнего перехода</a></th>\n";
if ( $st === "ip" ) $sort .= "<th>IP-адрес</th>\n";
else $sort .= "<th><a href='admin.php?action=refer&amp;sort=ip'>IP-адрес</a></th>\n";
$sort = "<tr>$sort</tr>";

$body = "<h3>Администрирование - обратные ссылки</h3>
<a href='admin.php'>Меню</a><br /><br />
<table border='1'>
$sort
$list</table>
$pagebar
<br />
Всего в списке: <b>$cnt</b><br />
";

?>