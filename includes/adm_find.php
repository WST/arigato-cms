<?php

if ( !defined('CMS') )
{
	die("Ќарушение прав доступа");
	exit;
}

$order = 'fdate desc';
if ( isset ($_GET['sort']) || isset ($_COOKIE['F_SORT']) )
{
	$st = isset ($_GET['sort']) ? $_GET['sort'] : $_COOKIE['F_SORT'];
	SetCookie ("F_SORT", $st, time() + 60 * 60 * 24 * 30 * 12);
	if ( $st === 'cnt' ) $order = 'cnt desc,fdate desc';
	if ( $st === 'abc' ) $order = 'fstring';
	if ( $st === 'ip' ) $order = 'ip,cnt desc,fdate desc';
} else $st = 'dat';

$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]find");
$fg = db_fetch_assoc ($rg);
$cnt = $fg['CNT'];
$page = isset ($_GET['c']) ? $_GET['c'] : 1;

$list = '';
$r = db_query ("select * from $INFO[db_prefix]find order by $order limit " . ($page - 1) * 100 . ", 100");
while ($f = db_fetch_assoc ($r))
{
	$dt = date ('d.m.Y H:i', $f['fdate']);
	$list .= "<tr><td style='text-align:center;'>$dt</td>\n";
	$list .= "<td>$f[ip]</td>\n";
	$list .= "<td style='text-align:center;color:#800000;'><b>$f[cnt]</b></td>\n";
	$list .= "<td>$f[fstring]</td></tr>\n";
}

$pagebar = '';
if ( $cnt > 100 )
{
	for ($p = 0; $p < $cnt / 100; $p++)
	{
		if ( $p != $page - 1 ) $pagebar .= "<a href='admin.php?action=find&amp;sort=$st&amp;c=" . ($p + 1) . "'>" . ($p + 1) . "</a> ";
		else $pagebar .= "<b>" . ($p + 1) . "</b> ";
	}
	$pagebar = "—траницы: $pagebar<br />";
}

$sort = "";
if ( $st === "dat" ) $sort .= "<th>дата последнего запроса</th>\n";
else $sort .= "<th><a href='admin.php?action=find&amp;sort=dat'>дата последнего запроса</a></th>\n";
if ( $st === "ip" ) $sort .= "<th>ip-адрес</th>\n";
else $sort .= "<th><a href='admin.php?action=find&amp;sort=ip'>ip-адрес</a></th>\n";
if ( $st === "cnt" ) $sort .= "<th>запросов</th>\n";
else $sort .= "<th><a href='admin.php?action=find&amp;sort=cnt'>запросов</a></th>\n";
if ( $st === "abc" ) $sort .= "<th>содержание запросов</th>\n";
else $sort .= "<th><a href='admin.php?action=find&amp;sort=abc'>содержание запросов</a></th>\n";
$sort = "<tr>$sort</tr>";

$body = "<h3>јдминистрирование - поисковые запросы</h3>
<a href='admin.php'>ћеню</a><br /><br />
ѕоисковые запросы за последний мес€ц:<br /><br />
<table border='1'>
$sort
$list</table>
$pagebar
<br />
¬сего в списке: <b>$cnt</b>
";

?>