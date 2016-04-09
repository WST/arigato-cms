<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

$order = 'active desc,qtime desc';
if ( isset ($_GET['sort']) || isset ($_COOKIE['L_SORT']) )
{
	$st = isset ($_GET['sort']) ? $_GET['sort'] : $_COOKIE['L_SORT'];
	SetCookie ("L_SORT", $st, time() + 60 * 60 * 24 * 30 * 12);
	if ( $st === 'abc' ) $order = 'email';
	if ( $st === 'dat' ) $order = 'qtime desc';
	if ( $st === 'ip' ) $order = 'ip,active desc,qtime desc';	
} else $st = 'act';
$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]email where active=1");
$fg = db_fetch_assoc ($rg);
$cta = $fg['CNT'];
$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]email");
$fg = db_fetch_assoc ($rg);
$cnt = $fg['CNT'];
$page = isset ($_GET['c']) ? $_GET['c'] : 1;

$list = '';
$r = db_query ("select * from $INFO[db_prefix]email order by $order limit " . ($page - 1) * 100 . ", 100");
while ($f = db_fetch_assoc ($r))
{
	$to_act = '<td>&nbsp;</td>';
	if ( $f['active'] == 0 ) 
	{
		$act = '���������';
		$to_act = "<td>(<a href='admin.php?action=sp_act&amp;k_mail=$f[k_mail]' style='color:blue;'>������������</a>)</td>";
	} else $act = '�������';
	$act = "<font color='#800000' title='$f[cd]'>$act</font>";
	$dt = date ('d.m.Y H:i', $f['qtime']);
	$snd = date ('d.m.Y H:i', $f['send_time']);
	$list .= "<tr><td><span title='$snd'>$f[email]</span></td><td>$dt</td><td>$f[ip]</td><td>$act</td>
	<td>(<a href='admin.php?action=sp_del&amp;k_mail=$f[k_mail]' onclick='return confirm(\"������� �� ������ ���� $f[email]?\");'>�������</a>)</td>
	$to_act</tr>\n";
}

$pagebar = '';
if ( $cnt > 100 )
{
	for ($p = 0; $p < $cnt / 100; $p++)
	{
		if ( $p != $page - 1 ) $pagebar .= "<a href='admin.php?action=spamlist&amp;sort=$st&amp;c=" . ($p + 1) . "'>" . ($p + 1) . "</a> ";
		else $pagebar .= "<b>" . ($p + 1) . "</b> ";
	}
	$pagebar = "��������: $pagebar<br />";
}

$sort = "";
if ( $st === "abc" ) $sort .= "<th>����� e-mail</th>\n";
else $sort .= "<th><a href='admin.php?action=spamlist&amp;sort=abc'>����� e-mail</a></th>\n";
if ( $st === "dat" ) $sort .= "<th>���� �������</th>\n";
else $sort .= "<th><a href='admin.php?action=spamlist&amp;sort=dat'>���� �������</a></th>\n";
if ( $st === "ip" ) $sort .= "<th>ip-�����</th>\n";
else $sort .= "<th><a href='admin.php?action=spamlist&amp;sort=ip'>ip-�����</a></th>\n";
if ( $st === "act" ) $sort .= "<th>���������</th>\n";
else $sort .= "<th><a href='admin.php?action=spamlist&amp;sort=act'>���������</a></th>\n";
$sort = "<tr>$sort<th>&nbsp;</th><th>&nbsp;</th></tr>";

$body = "<h3>����������������� - ������ ������� ��������</h3>
<a href='admin.php'>����</a><br /><br />
<table border='1'>
$sort
$list</table>
$pagebar
<br />
����� � ������: <b>$cnt</b><br />
��������: <b>$cta</b>
";

?>