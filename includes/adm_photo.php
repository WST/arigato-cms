<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

if ( isset ($_GET['id']) )
{ // ������ ����������:
	$id = anti_inj ($_GET['id']);
	$r = db_query ("select * from $INFO[db_prefix]photos where k_photo='$id'");
	if ( $f = db_fetch_assoc ($r) )
	{
		$title = protection ($f['title']);
		$caption = protection ($f['caption']);
		$pictur = protection ($f['pictur']);
		$refer = protection ($_SERVER['HTTP_REFERER']);
	} else $title = $message = "";
	$body = "<h3>����������������� - ������� ����������</h3>
	<a href='admin.php'>����</a><br /><br />
	<form action='admin.php' method='post'>
	<input type='hidden' name='photoid' value='$id' />
	<input type='hidden' name='refer' value='$refer' />
	<table border='0'><tr>
	<td>����������:</td><td><input type='text' size='67' maxlength='255' name='pictur' value='$pictur' /></td>
	</tr><tr>
	<td>��������:</td><td><input type='text' size='67' maxlength='255' name='title' value='$title' /></td>
	</tr><tr>
	<td>��������:</td><td><input type='text' size='67' maxlength='255' name='caption' value='$caption' /></td>
	</tr></table>
	<input type='submit' value='��������' />
	</form>
	";
}

?>