<?php

define ('CMS', true);

require_once "includes/utils.php";

if ( ban (2) )
{
	error (tpl ($f_config['error9'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

if ( isset ($_GET['action']) && $f_config['spam'] == 1 )
{
	include ('includes/action.php');
	exit;
}

if ( isset ($_POST['disp']) && $f_config['spam'] == 1 )
{
	include ('includes/disp.php');
	exit;
}

header ("location:index.php");

?>