<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

$mes = array();
$r = db_query ("select * from $INFO[db_prefix]messages");
$f_message = db_fetch_assoc ($r);
foreach ($f_message as $key => $val) $mes[$key] =  protection ($val);

$body = "<h3>����������������� - �������������� ���������</h3>
<a href='admin.php'>����</a><br /><br />
<form action='admin.php' method='post'>
<input type='hidden' name='message' value='ok'>
&nbsp;<b>��������� ��������� �� ������:</b><br />
<input type='text' maxlength='255' size='68' name='error_cap' value='$mes[error_cap]' /><br />
&nbsp;<b>��������� ��������������� ���������:</b><br />
<input type='text' maxlength='255' size='68' name='inform_cap' value='$mes[inform_cap]' /><br />
&nbsp;<b>���� � ������� ������������:</b><br />
<input type='text' maxlength='255' size='68' name='error1' value='$mes[error1]' /><br />
&nbsp;<b>������ IP � ������� ������������:</b><br />
<input type='text' maxlength='255' size='68' name='error2' value='$mes[error2]' /><br />
&nbsp;<b>���� � ������� �������� �� ��������:</b><br />
<input type='text' maxlength='255' size='68' name='error3' value='$mes[error3]' /><br />
&nbsp;<b>�� ������� ��� � ������� ������������:</b><br />
<input type='text' maxlength='255' size='68' name='error4' value='$mes[error4]' /><br />
&nbsp;<b>����������� ��� � ������� ������������:</b><br />
<input type='text' maxlength='255' size='68' name='error12' value='$mes[error12]' /><br />
&nbsp;<b>����� �������� ��������� � ������� ������������:</b><br />
<input type='text' maxlength='255' size='68' name='error5' value='$mes[error5]' /><br />
&nbsp;<b>� ������ ������� ������ ��������������:</b><br />
<input type='text' maxlength='255' size='68' name='error13' value='$mes[error13]' /><br />
&nbsp;<b>�������� ������� ����� e-mail:</b><br />
<input type='text' maxlength='255' size='68' name='error6' value='$mes[error6]' /><br />
&nbsp;<b>��������� ��� ������������� �������� �� ��������:</b><br />
<input type='text' maxlength='255' size='68' name='error7' value='$mes[error7]' /><br />
&nbsp;<b>������� �� �������������� ������:</b><br />
<input type='text' maxlength='255' size='68' name='error8' value='$mes[error8]' /><br />
&nbsp;<b>������ IP �� ������ � �����:</b><br />
<input type='text' maxlength='255' size='68' name='error9' value='$mes[error9]' /><br />
&nbsp;<b>��������� �����������:</b><br />
<input type='text' maxlength='255' size='68' name='error10' value='$mes[error10]' /><br />
&nbsp;<b>���� �� ����������:</b><br />
<input type='text' maxlength='255' size='68' name='error11' value='$mes[error11]' /><br />
&nbsp;<b>���� ��� ����������� ������ ��������������:</b><br />
<input type='text' maxlength='255' size='68' name='error14' value='$mes[error14]' /><br />
&nbsp;<b>��������� �� ��� ����������� ����:</b><br />
<input type='text' maxlength='255' size='68' name='error15' value='$mes[error15]' /><br />
&nbsp;<b>�������� ������� ����������� �����:</b><br />
<input type='text' maxlength='255' size='68' name='error16' value='$mes[error16]' /><br />
&nbsp;<b>������ �������� ������:</b><br />
<input type='text' maxlength='255' size='68' name='error17' value='$mes[error17]' /><br />
&nbsp;<b>������� ������������ ������ � �������� �� ��������:</b><br />
<input type='text' maxlength='255' size='68' name='inform1' value='$mes[inform1]' /><br />
&nbsp;<b>��������� ����� � �������� �� ��������:</b><br />
<input type='text' maxlength='255' size='68' name='inform2' value='$mes[inform2]' /><br />
&nbsp;<b>������ ���� ��� ����������� ��� ��������:</b><br />
<input type='text' maxlength='255' size='68' name='inform3' value='$mes[inform3]' /><br />
&nbsp;<b>��������� ���� ��� ���� � ������ ��������:</b><br />
<input type='text' maxlength='255' size='68' name='inform4' value='$mes[inform4]' /><br />
&nbsp;<b>�������� ����� �� ������ ��������:</b><br />
<input type='text' maxlength='255' size='68' name='inform5' value='$mes[inform5]' /><br />
&nbsp;<b>������ �������������� ����� ����������:</b><br />
<input type='text' maxlength='255' size='68' name='inform6' value='$mes[inform6]' /><br />
&nbsp;<b>������ ����� �� �������:</b><br />
<input type='text' maxlength='65535' size='68' name='inform7' value='$mes[inform7]' /><br />
<br /><input type='submit' value='���������' />
</form>
";

?>