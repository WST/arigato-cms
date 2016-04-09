<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

$order = 'lastdate desc';
if ( isset ($_GET['sort']) || isset ($_COOKIE['DC_SORT']) )
{
	$st = isset ($_GET['sort']) ? $_GET['sort'] : $_COOKIE['DC_SORT'];
	SetCookie ("DC_SORT", $st, time() + 60 * 60 * 24 * 30 * 12);
	if ( $st === 'abc' ) $order = 'file';
	if ( $st === 'dat' ) $order = 'lastdate desc';
	if ( $st === 'cnt' ) $order = 'cnt desc,lastdate desc';	
	if ( $st === 'ip' ) $order = 'ip,lastdate desc';	
} else $st = 'dat';
$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]dlcnt");
$fg = db_fetch_assoc ($rg);
$cnt = $fg['CNT'];
$page = isset ($_GET['c']) ? $_GET['c'] : 1;

$list = '';
$r = db_query ("select * from $INFO[db_prefix]dlcnt order by $order limit " . ($page - 1) * 100 . ", 100");
while ($f = db_fetch_assoc ($r))
{
	$act = "<font color='#800000' title='$f[cd]'>$act</font>";
	$dt = date ('d.m.Y H:i', $f['lastdate']);
	$fn = protection ($f['file']);
	$list .= "<tr><td>$fn</td><td><center>$f[cnt]</center></td><td>$dt</td><td>$f[ip]</td>
	<td>(<a href='admin.php?action=dlcnt_del&amp;k_dl=$f[k_dl]' style='color:blue;' onclick='return confirm(\"�������� ������� \\\"$fn\\\"?\");'>��������</a>)</td>
	</tr>\n";
}

$pagebar = '';
if ( $cnt > 100 )
{
	for ($p = 0; $p < $cnt / 100; $p++)
	{
		if ( $p != $page - 1 ) $pagebar .= "<a href='admin.php?action=dlcnt&amp;sort=$st&amp;c=" . ($p + 1) . "'>" . ($p + 1) . "</a> ";
		else $pagebar .= "<b>" . ($p + 1) . "</b> ";
	}
	$pagebar = "��������: $pagebar<br />";
}

$sort = "";
if ( $st === "abc" ) $sort .= "<th>��� �����</th>\n";
else $sort .= "<th><a href='admin.php?action=dlcnt&amp;sort=abc'>��� �����</a></th>\n";
if ( $st === "cnt" ) $sort .= "<th>�������</th>\n";
else $sort .= "<th><a href='admin.php?action=dlcnt&amp;sort=cnt'>�������</a></th>\n";
if ( $st === "dat" ) $sort .= "<th>���� �������</th>\n";
else $sort .= "<th><a href='admin.php?action=dlcnt&amp;sort=dat'>���� �������</a></th>\n";
if ( $st === "ip" ) $sort .= "<th>IP-�����</th>\n";
else $sort .= "<th><a href='admin.php?action=dlcnt&amp;sort=ip'>IP-�����</a></th>\n";
$sort .= "<th>&nbsp;</th>\n";

$body = "<h3>����������������� - �������� ���������� ������</h3>
<a href='admin.php'>����</a><br /><br />
<table border='1'>
$sort
$list</table>
$pagebar
<br />
����� � ������: <b>$cnt</b><br />
";

?>