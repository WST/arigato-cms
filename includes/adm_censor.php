<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

$list = '';
$r = db_query ("select * from $INFO[db_prefix]censor order by bad");
while ($f = db_fetch_assoc ($r))
{
	$good = protection (str_replace ('&#039;', "'", $f['good']));
	$list .= "<li />$f[bad] = $good (<a href='admin.php?action=cns_del&amp;k_cns=$f[k_cns]' onclick='return confirm(\"������� \\\"$f[bad]\\\"?\");'>�������</a>)<br />\n";
}

$body = "<h3>����������������� - ������� � ������������</h3>
<a href='admin.php'>����</a><br /><br />
$list
<form action='admin.php' method='post' onsubmit='return (this.bad.value.length>0&&this.good.value.length>0);'>
<input type='hidden' name='censor' value='ok'>
<table><tr>
<td>������ �����:</td>
<td><input type='text' maxlength='255' size='30' name='bad'></td>
</tr><tr>
<td>������:</td>
<td><input type='text' maxlength='255' size='30' name='good'></td>
</tr></table>
<input type='submit' value='��������'>
</form>
";

?>