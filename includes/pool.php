<?php
if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

foreach ($_COOKIE as $cook_name => $cook_val)
{
	if ( strpos ($cook_name, 'POOL') === 0 )
	{
		$k_pl = anti_inj (substr ($cook_name, 4));
		if ( $k_pk !== '' )
		{
			$rp = db_query ("select k_pl from $INFO[db_prefix]pools where k_pl='$k_pl' limit 1");
			if ( ! ($fp = db_fetch_assoc ($rp)) ) SetCookie ("POOL$k_pl", "", 0);
			db_free_result ($rp);
		}
	}
}

$pool = "";

$text = join ('', file ("$skin/pool.html"));
$answ = join ('', file ("$skin/answ.html"));
$rp = db_query ("select * from $INFO[db_prefix]pools where active=1 order by begdate desc");
if ( db_num_rows ($rp) > 0 ) db_query ("update $INFO[db_prefix]pools set active=0 where enddate<" . time() . " and enddate<>0");
while ($fp = db_fetch_assoc ($rp))
{
	$answer = "";
	$voted = ( isset ($_COOKIE["POOL$fp[k_pl]"]) ) ? $_COOKIE["POOL$fp[k_pl]"] : "";
	$ra = db_query ("select * from $INFO[db_prefix]answers where k_pl=$fp[k_pl] order by pos");
	while ($fa = db_fetch_assoc ($ra))
	{
		$tag = $global_tag;
		$tag['VOTE'] = $fa['vote'];
		$tag['PERCENT'] = ( $fp['cnt'] == 0 ) ? 0 : intval (0.5 + ($fa['vote'] * 100) / $fp['cnt']);
		$tag['ANSWER'] = $fa['answer'];
		$tag['K_ANS'] = $fa['k_ans'];
		$tag['VOTED'] = $voted;
		$answer .= tpl ($answ, $tag);
	}
	$tag = $global_tag;
	$tag['QUEST'] = $fp['question'];
	$tag['ANSWERS'] = $answer;
	$tag['POOL_ID'] = $fp['k_pl'];
	$tag['BDATE'] = date ($f_config['datefrm'], $fp['begdate']);
	if ( $fp['enddate'] != 0 ) $tag['EDATE'] = date ($f_config['datefrm'], $fp['enddate']);
	$tag['CNT'] = $fp['cnt'];
	$tag['VOTED'] = $voted;
	$pool .= tpl ($text, $tag);
}

?>