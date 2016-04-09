<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$list = '';
$r = db_query ("select * from $INFO[db_prefix]badname order by bname");
while ($f = db_fetch_assoc ($r))
{
	$name = protection ($f['bname']);
	$list .= "<li />$name (<a href='admin.php?action=bname_del&amp;k_bn=$f[k_bn]' onclick='return confirm(\"Удалить $f[bname]?\");'>удалить</a>)<br />\n";
}

$body = "<h3>Администрирование - запреты имен в комментариях</h3>
<a href='admin.php'>Меню</a><br /><br />
$list
<form action='admin.php' method='post' onsubmit='return (this.bname.value.length>1);'>
<input type='hidden' name='badname' value='ok'>
Запрещенное имя (может использовать только администратор):<br />
<input type='text' maxlength='16' size='30' name='bname'>
<input type='submit' value='Добавить'>
</form>
";

?>