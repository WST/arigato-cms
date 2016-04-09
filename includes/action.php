<?php

if ( ! defined ('CMS') )
{
	die ("Нарушение прав доступа");
	exit;
}

$code = isset ($_GET['code']) ? anti_inj ($_GET['code']) : "";

$rg = db_query ("select email,active from $INFO[db_prefix]email where cd='$code'");
if ( $fg = db_fetch_assoc ($rg) )
{
	$tag = $global_tag;
	$tag['MAIL'] = $fg['email'];
	if ( $_GET['action'] === 'active' )
	{
		if ( $fg['active'] == 1 ) error (tpl ($f_config['inform3'], $tag), tpl ($f_config['inform_cap'], $global_tag));
		else 
		{
			db_query ("update $INFO[db_prefix]email set active=1,send_time=" . time() . " where cd='$code'");
			$tag['DEACTIVE'] = $f_config['url'] . "/dispatch.php?action=deactive&code=$code";
			$tag['CODE'] = $code;
			mask_mail ($fg['email'], 'active.txt', $tag);
			error (tpl ($f_config['inform2'], $tag), tpl ($f_config['inform_cap'], $global_tag));
		}
	} else
	{
		db_query ("delete from $INFO[db_prefix]email where cd='$code'");
		error (tpl ($f_config['inform5'], $tag), tpl ($f_config['inform_cap'], $global_tag));
	}
} else error (tpl ($f_config['error7'], $global_tag), tpl ($f_config['error_cap'], $global_tag));

?>