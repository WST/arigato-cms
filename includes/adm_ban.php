<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

$list = '';
$r = db_query ("select * from $INFO[db_prefix]ban where mode='$mode' order by ip");
while ($f = db_fetch_assoc ($r))
{
	$list .= "<li />$f[ip] (<a href='admin.php?action=ip_del&amp;k_ban=$f[k_ban]' onclick='return confirm(\"������� IP $f[ip]?\");'>�������</a>)<br />\n";
}

$title = $info = "";
switch ($mode)
{
	case 0: $title = '������� IP � ������������';
			$info = '������������ � ���������� ip-�������� �� ������ ��������� ��������� � ������������ � �������.';
			break;
	case 1: $title = '������� IP � �������';
			$info = '������������ � ���������� ip-�������� �� ������ ����� � ������ �����������������.<br />
			��������! �� ���������� ��� ���� ip-�����.';
			break;
	case 2: $title = '������� IP �� ������';
			$info = '������������ � ���������� ip-�������� �� ������ ������� �� ���� � ������������� ���.';
			break;
	case 3: $title = '������� IP �� �������� ������';
			$info = '������������ � ���������� ip-�������� �� ������ ���������� ������ �������������� �����.';
			break;
}

$body = "<h3>����������������� - $title</h3>
<a href='admin.php'>����</a><br /><br />
$info<br /><br />
$list
<form action='admin.php' method='post' onsubmit='return (this.ip.value.length>6);'>
<input type='hidden' name='ban' value='ok'>
<input type='hidden' name='mode' value='$mode'>
IP (��������, 127.0.0.1 ��� �� ����� 127.0.0.*, 127.0.*.*):<br />
<input type='text' maxlength='15' size='30' name='ip'>
<input type='submit' value='��������'>
</form>
";

?>