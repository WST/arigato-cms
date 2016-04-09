<?php

if ( ! defined ('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$rg = db_query ("select * from $INFO[db_prefix]news where k_art='$art' and faq=1 order by mesdate desc");

$mestext = join ('', file ("$skin/faqmes.html"));
$lnktext = join ('', file ("$skin/faqlnk.html"));
$mes = "";
$lnk = "";
while ( $fg = db_fetch_assoc ($rg) )
{
	$tag = $global_tag;
	$tag['MESSAGE'] = format_text ($fg['post']);
	$tag['TITLE'] = format_text ($fg['title']);
	$tag['ID'] = $fg['k_new'];
	$tag['DATE'] = date ($f_config['datefrm'], $fg['mesdate']);
	$mes .= tpl ($mestext, $tag);
	$lnk .= tpl ($lnktext, $tag);
}

$text = join ('', file ("$skin/faq.html"));
$tag = $global_tag;
$tag['INDEX'] = $art;
$tag['MESSAGES'] = $mes;
$tag['LINKS'] = $lnk;

$faq = tpl ($text, $tag);

?>