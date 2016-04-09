<?php

if ( ! defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$body = "
<script type='text/javascript'><!--
function check(frm)
{	err='';
	if (frm.subj.value.length==0) err+='Забыли указать тему.\\n';
	if (frm.body.value.length==0) err+='Забыли ввести сообщение.\\n';
	if (err!='')
	{	alert(err);
		return false;
	} else return true;
}
//--></script>
<h3>Администрирование - отправка рассылки</h3>
<a href='admin.php'>Меню</a><br /><br />
<form method='post' action='admin.php' name='send' onsubmit='return check(this);'>
<input type='hidden' name='send' value='ok' />
Тема: <input type='text' name='subj' maxlength='255' size='40' value='$caption' /><br />
Текст письма (простой текст):<br />
<textarea name='body' rows='20' cols='80'></textarea><br />
<input type='submit' value='Отправить' />
</form>
";

?>