<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

$r = db_query ("select * from $INFO[db_prefix]chatconf");
$f = db_fetch_assoc ($r);

$chatblock = ($f['chatblock'] == 1) ? " checked" : "";

$body = "<h3>����������������� - ������������ ����</h3>
<a href='admin.php'>����</a><br />
<form action='admin.php' method='post'>
<input type='hidden' name='chat' value='ok' />
<input type='checkbox' class='radio' name='chatblock' value='1'$chatblock /> ���� ���� �� �����.<br />
<table><tr>
<td>�������� ���������:</td>
<td><input type='text' maxlength='6' size='16' name='maxmes' value='$f[maxmes]' /></td>
</tr><tr>
<td>����� �������� ������� (���):</td>
<td><input type='text' maxlength='6' size='16' name='maxtime' value='$f[maxtime]' /></td>
</tr><tr>
<td>������������ ������ ���������:</td>
<td><input type='text' maxlength='6' size='16' name='maxlen' value='$f[maxlen]' /></td>
</tr><tr>
<td>������� ���������� (���):</td>
<td><input type='text' maxlength='6' size='16' name='refresh' value='$f[refresh]' /></td>
</tr>
</table>
<b>����������:</b> ������� ���� �� IP-������, ����������� � ����������� ����� ��������� �� ������� ������������.<br /><br />
<input type='submit' value='���������' />
</form>
";

?>