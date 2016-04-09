<?php

define ('CMS', true);

require_once "includes/utils.php";

if ( ! $admin )
{
	die("Нарушение прав доступа");
	exit;
}

slash_control ();

if ( isset ($_POST['post']) )
{
	$message = substr (trim ($_POST['message']), 0, 65535);
	$title = substr (trim ($_POST['title']), 0, 65535);
	$date = time ();
	$faq = ( $_POST['faq'] == 1 ) ? 1 : 0;

	$ind = anti_inj ($_POST['index']);
	if ( $ind !== '' )
	{
		db_query ("insert into $INFO[db_prefix]news (k_art,title,post,mesdate,faq) values ('$ind','$title','$message','$date','$faq')");
		artindex ($ind);
	}
}

header ("location:index.php?art=$_POST[index]");

?>