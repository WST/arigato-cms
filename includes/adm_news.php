<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

if ( isset ($_GET['id']) )
{ // ������ �������:
	$id = anti_inj ($_GET['id']);
	$r = db_query ("select * from $INFO[db_prefix]news where k_new='$id'");
	if ( $f = db_fetch_assoc ($r) )
	{
		$title = protection ($f['title']);
		$message = protection ($f['post']);
		$mesdate = date ('d.m.Y H:i', $f['mesdate']);
		$refer = protection ($_SERVER['HTTP_REFERER']);
	} else $title = $message = "";
	$body = "<h3>����������������� - �������</h3>
	<a href='admin.php'>����</a><br /><br />
	<form action='admin.php' method='post'>
	<input type='hidden' name='newmesid' value='$id' />
	<input type='hidden' name='refer' value='$refer' />
	���� ����������: <b>$mesdate</b><br />
	���������: <input type='text' size='67' maxlength='255' name='title' value='$title' /><br />
	���������:<br />
	<textarea name='newmessage' rows='12' cols='80'>$message</textarea>
	<br /><br />
	<input type='submit' value='��������' />
	</form>
	";
}

?>