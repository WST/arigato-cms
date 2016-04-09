<?php

define ('CMS', true);

require_once "includes/utils.php";

$k_ans = anti_inj ($_POST['answer']);
$err = false;

$ra = db_query ("select k_pl,vote from $INFO[db_prefix]answers where k_ans='$k_ans'");
if ( $fa = db_fetch_assoc ($ra) )
{
	$k_pl = $fa['k_pl'];
	if ( ! isset ($_COOKIE["POOL$k_pl"]) )
	{
		$rp = db_query ("select active,cnt from $INFO[db_prefix]pools where k_pl='$k_pl'");
		if ( ($fp = db_fetch_assoc ($rp)) && ($fp['active'] == 1) )
		{
			db_query ("update $INFO[db_prefix]pools set cnt=" . ($fp['cnt'] + 1) . " where k_pl='$k_pl'");
			db_query ("update $INFO[db_prefix]answers set vote=" . ($fa['vote'] + 1) . " where k_ans='$k_ans'");
		} else $err = true;
	} else $err = true;
} else $err = true;
if ( $err === false )
{
	SetCookie ("POOL$k_pl", $k_ans, time() + 60 * 60 * 24 * 30 * 12);
} else error (tpl ($f_config['error10'], $global_tag), tpl ($f_config['error_cap'], $global_tag));

header ("location:" . referer());

?>