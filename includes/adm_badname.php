<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

$list = '';
$r = db_query ("select * from $INFO[db_prefix]badname order by bname");
while ($f = db_fetch_assoc ($r))
{
	$name = protection ($f['bname']);
	$list .= "<li />$name (<a href='admin.php?action=bname_del&amp;k_bn=$f[k_bn]' onclick='return confirm(\"������� $f[bname]?\");'>�������</a>)<br />\n";
}

$body = "<h3>����������������� - ������� ���� � ������������</h3>
<a href='admin.php'>����</a><br /><br />
$list
<form action='admin.php' method='post' onsubmit='return (this.bname.value.length>1);'>
<input type='hidden' name='badname' value='ok'>
����������� ��� (����� ������������ ������ �������������):<br />
<input type='text' maxlength='16' size='30' name='bname'>
<input type='submit' value='��������'>
</form>
";

?>