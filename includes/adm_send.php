<?php

if ( ! defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

$body = "
<script type='text/javascript'><!--
function check(frm)
{	err='';
	if (frm.subj.value.length==0) err+='������ ������� ����.\\n';
	if (frm.body.value.length==0) err+='������ ������ ���������.\\n';
	if (err!='')
	{	alert(err);
		return false;
	} else return true;
}
//--></script>
<h3>����������������� - �������� ��������</h3>
<a href='admin.php'>����</a><br /><br />
<form method='post' action='admin.php' name='send' onsubmit='return check(this);'>
<input type='hidden' name='send' value='ok' />
����: <input type='text' name='subj' maxlength='255' size='40' value='$caption' /><br />
����� ������ (������� �����):<br />
<textarea name='body' rows='20' cols='80'></textarea><br />
<input type='submit' value='���������' />
</form>
";

?>