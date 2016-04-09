<?php

define ('CMS', true);

require_once "includes/utils.php";

if ( ! $admin )
{
	die("Нарушение прав доступа");
	exit;
}

slash_control ();

if ( isset ($_POST['pictur']) )
{
	$title = substr (trim ($_POST['title']), 0, 65535);
	if ( $_POST['position'] == 1 )
	{
		$r = db_query ("select max(pos) as mx from $INFO[db_prefix]photos");
		if ( $f = db_fetch_assoc ($r) ) $pos = $f['mx'] + 1;
		else $pos = 1;
	} else
	{
		$r = db_query ("select min(pos) as mn from $INFO[db_prefix]photos");
		if ( $f = db_fetch_assoc ($r) ) $pos = $f['mn'] - 1;
		else $pos = 1;
	}
	db_free_result ($r);
	$ind = anti_inj ($_POST['index']);
	if ( $ind !== '' )
	{
		db_query ("insert into $INFO[db_prefix]photos (k_art,pictur,title,caption,pos) values
				('$ind','$_POST[pictur]','$title','$_POST[caption]','$pos')");
		artindex ($ind);
	}
}

header ("location:index.php?art=$_POST[index]");

?>