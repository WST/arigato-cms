<?php

if ( ! defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

if ( ! file_exists ("backup") ) mkdir ("backup", 0755);

if ( isset ($_GET['restore']) )
{
	$rest = protection ($_GET['restore']);
	$body = "�������������� ������ <b>$rest</b>.<br />
	� ������ �������������� ��� ����������� � $rest ������� �������� ������� ������� �� �����.<br />
	�� �������, ��� ����� ����������?<br />
	<form action='admin.php' method='post'>
	<input type='hidden' name='restore' value='$rest' />
	<input type='submit' name='yes' value='������������ �����' /> <input type='submit' name='no' value='������' />
	</form>
	";
} elseif ( isset ($_GET['backup']) )
{
	$tables = array (
				"art" => "������ �����",
				"news" => "�������",
				"photos" => "����������",
				"book" => "����������� �������������",
				"pools" => "�����������",
				"answers" => "���������� �����������",
				"config" => "������������ �����",
				"links" => "���� ������",
				"blocks" => "��������� ������ �����",
				"chatconf" => "������������ ����",
				"messages" => "�������������� ���������",
				"email" => "������ ��������",
				"dlcnt" => "�������� ���������� ������",
				"censor" => "������� � ������������",
				"ban" => "������� �����",
				"badname" => "����������� ����� � ������������",
				"refer" => "�������� ������",
				"find" => "��������� �������",
				"counter" => "���������� �����");
	$list = "";
	foreach ($tables as $tname => $discr)
	{
		$list .= "<input type='checkbox' class='radio' name='table[]' value='$tname' checked /> <b>$tname</b> - $discr<br />\n";
	}
	$body = "�������� �������, ������� ����� ���������:<br />
	<form action='admin.php?action=backup' method='post' onsubmit='return confirm(\"���������� ����� ��������� ������?\");'>
	$list
	<br />
	<input type='submit' name='backup' value='������' /><br />
	</form>";
	
} else
{
	$list = array();
	$dir = opendir ("backup");
	while ( $d = readdir ($dir) )
		if ( ! is_dir ("backup/$d") )
		{
			$fileinfo = stat ("backup/$d");
			$creat = ( $fileinfo["mtime"] < $fileinfo["ctime"] ) ? $fileinfo["mtime"] : $fileinfo["ctime"];
			$list[$d] = $creat;
		}
	closedir ($dir);
	arsort ($list);

	$files = "";
	foreach ($list as $name => $date)
	{
		$size = get_size ("backup/$name");
		$date = date ('d.m.Y H:i', $date);
		$files .= "<tr>
		<td><input type='checkbox' class='radio' name='file[]' value='$name' /></td>
		<td><a href='admin.php?action=backup&amp;restore=$name' onclick='return confirm(mes+\"$date?\");'>$name</a></td>
		<td>$date</td>
		<td>$size</td>
		</tr>	
		";
	}

	if ( ! empty ($files) )
		$files = "<script type='text/javascript'><!--
		mes='������������ ����� ���� ������ �� ';
		--></script>
		<form action='admin.php?action=backup' method='post' onsubmit='return confirm(\"������� ��������� �����?\");'>
		<table border='1' cellspacing='1' cellpadding='1'>
		$files
		</table><br />
		<input type='submit' name='del' value='������� ��������� �����' /><br />
		</form>";
	$body = "(<a href='admin.php?action=backup&amp;backup=1');'>������� �����</a>)
	$files";
}

$body = "
<h3>����������������� - ����� ���� ������</h3>
<a href='admin.php'>����</a><br /><br />
$body
";

?>