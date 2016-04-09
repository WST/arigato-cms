<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

$site = protection ($f_config['title']);
$from = protection ($f_config['afrom']);
$sign = protection ($f_config['signature']);

$skins = array();
$dir = opendir ('skins');
while ( ($d = readdir ($dir)) !== false )
{
	if ( is_dir ("skins/$d") && $d !== '.' && $d !== '..') $skins[] = $d;
}
closedir ($dir);
sort ($skins);
$skn = substr ($f_config['skin'], 6, strlen ($f_config['skin']) - 6);
$skin_sel = "<select name='skin'>\n";
for ($i = 0; $i < count ($skins); $i++) 
{
	if ( $skins[$i] === $skn ) $skin_sel .= "<option value='skins/$skins[$i]' selected />$skins[$i]\n";
	else $skin_sel .= "<option value='skins/$skins[$i]' />$skins[$i]\n";
}
$skin_sel .= "</select>\n";
$new_send = ( $f_config['new_send'] == 1 ) ? " checked" : "";
$err = ( isset ($_GET['err']) ) ? "<font color='red'><b>��������� ��������� ����� �����</b></font><br />" : "";

$body = "<h3>����������������� - ��������� ������������</h3>
<a href='admin.php'>����</a><br />
<form action='admin.php' method='post'>
<input type='hidden' name='config' value='ok' />
<table><tr>
<td>�������� �����:</td>
<td><input type='text' maxlength='255' size='30' name='title' value='$site' /></td>
</tr><tr>
<td>${err}URL �����:</td>
<td><input type='text' maxlength='255' size='30' name='url' value='$f_config[url]' /></td>
</tr><tr>
<td>��� �����:</td>
<td>$skin_sel</td>
</tr><tr>
<td>������ ����:</td>
<td><input type='text' maxlength='255' size='20' name='datefrm' value='$f_config[datefrm]' /></td>
</tr><tr>
<td>������ ����:</td>
<td><input type='text' maxlength='16' size='9' name='width' value='$f_config[menuwidth]' /></td>
</tr><tr>
<td>��������� �� �������� � ��������:</td>
<td><input type='text' maxlength='9' size='9' name='mpp' value='$f_config[mpp]' /></td>
</tr><tr>
<td>��������� �� �������� � �������:</td>
<td><input type='text' maxlength='9' size='9' name='hst' value='$f_config[hst]' /></td>
</tr><tr>
<td>��������� �� �������� � ��������:</td>
<td><input type='text' maxlength='9' size='9' name='nws' value='$f_config[nws]' /></td>
</tr><tr>
<td>���������� ���������� (���):</td>
<td><input type='text' maxlength='9' size='9' name='stat' value='$f_config[stat]' /></td>
</tr><tr>
<td>������� ������ ������� (����):</td>
<td><input type='text' maxlength='9' size='9' name='new' value='$f_config[new]' /></td>
</tr><tr>
<td>�������� � �������� �� �������� (���):</td>
<td><input type='text' maxlength='9' size='9' name='sp_mail' value='$f_config[sp_mail]' /></td>
</tr><tr>
<td>�������� � �������� (���):</td>
<td><input type='text' maxlength='9' size='9' name='sp_book' value='$f_config[sp_book]' /></td>
</tr><tr>
<td>�������� � ������ ������ (���):</td>
<td><input type='text' maxlength='9' size='9' name='sp_send' value='$f_config[sp_send]' /></td>
</tr><tr>
<td>������������ ����� �����������:</td>
<td><input type='text' maxlength='9' size='9' name='meslen' value='$f_config[meslen]' /></td>
</tr><tr>
<td>������ ������ ����������:</td>
<td><input type='text' maxlength='9' size='9' name='sketchwidth' value='$f_config[sketchwidth]' /></td>
</tr><tr>
<td>������ ������ ����������:</td>
<td><input type='text' maxlength='9' size='9' name='sketchheight' value='$f_config[sketchheight]' /></td>
</tr><tr>
<td>���������� ���������� � ������:</td>
<td><input type='text' maxlength='9' size='9' name='photocols' value='$f_config[photocols]' /></td>
</tr><tr>
<td>Mail ��������������:</td>
<td><input type='text' maxlength='255' size='30' name='email' value='$f_config[email]' /></td>
</tr><tr>
<td>���� \"��\" � �������:</td>
<td><input type='text' maxlength='255' size='30' name='from' value='$from' /></td>
</tr><tr>
<td>������� ��������������:</td>
<td><textarea name='signature' rows='4' cols='30'>$sign</textarea></td>
</tr><tr>
<td>��������� ������� �����:</td>
<td><input type='checkbox' class='radio' name='new_send' value='1'$new_send /></td>
</tr></table>
<input type='submit' value='���������' />
</form>
";

?>