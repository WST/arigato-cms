<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$order = 'reftime desc';
if ( isset ($_GET['sort']) || isset ($_COOKIE['S_SORT']) )
{
	$st = isset ($_GET['sort']) ? $_GET['sort'] : $_COOKIE['S_SORT'];
	SetCookie ("R_SORT", $st, time() + 60 * 60 * 24 * 30 * 12);
	if ( $st === 'cnt' ) $order = 'cnt desc,reftime desc';
	if ( $st === 'ip' ) $order = 'ip,cnt desc,reftime desc';
	if ( $st === 'brw' ) $order = 'agent,cnt desc,reftime desc';
} else $st = 'dat';

$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]counter");
$fg = db_fetch_assoc ($rg);
$cnt = $fg['CNT'];
$page = isset ($_GET['c']) ? $_GET['c'] : 1;

$list = '';
$r = db_query ("select * from $INFO[db_prefix]counter order by $order limit " . ($page - 1) * 100 . ", 100");
while ($f = db_fetch_assoc ($r))
{
	$dt = date ('d.m.Y H:i', $f['reftime']);
	$list .= "<tr><td>$f[ip]</td>\n";
	$list .= "<td style='text-align:center;color:#800000;'><b>$f[cnt]</b></td>\n";
	$list .= "<td style='text-align:center;'>$dt</td>\n";
	$list .= "<td>$f[agent]</td></tr>\n";
}

$pagebar = '';
if ( $cnt > 100 )
{
	for ($p = 0; $p < $cnt / 100; $p++)
	{
		if ( $p != $page - 1 ) $pagebar .= "<a href='admin.php?action=visitor&amp;sort=$st&amp;c=" . ($p + 1) . "'>" . ($p + 1) . "</a> ";
		else $pagebar .= "<b>" . ($p + 1) . "</b> ";
	}
	$pagebar = "Страницы: $pagebar<br />";
}

$sort = "";
if ( $st === "ip" ) $sort .= "<th>ip-адрес</th>\n";
else $sort .= "<th><a href='admin.php?action=visitor&amp;sort=ip'>ip-адрес</th>\n";
if ( $st === "cnt" ) $sort .= "<th>визитов</th>\n";
else $sort .= "<th><a href='admin.php?action=visitor&amp;sort=cnt'>визитов</th>\n";
if ( $st === "dat" ) $sort .= "<th><b>дата последнего визита</th>\n";
else $sort .= "<th><a href='admin.php?action=visitor&amp;sort=dat'>дата последнего визита</th>\n";
if ( $st === "brw" ) $sort .= "<th><b>название браузера</th>\n";
else $sort .= "<th><a href='admin.php?action=visitor&amp;sort=brw'>название браузера</th>\n";
$sort = "<tr>$sort</tr>";

$body = "<h3>Администрирование - посетители за месяц</h3>
<a href='admin.php'>Меню</a><br /><br />
<table border='1'>
$sort
$list</table>
$pagebar
<br />
Всего в списке: <b>$cnt</b><br />
";

?>