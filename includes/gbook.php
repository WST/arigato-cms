<?php
if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$page = isset ($_GET['c']) ? $_GET['c'] : 1;
if ( $page < 1 ) $page = 1;

$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]book where k_art='$art'");
$fg = db_fetch_assoc ($rg);
$cnt = $fg['CNT'];
db_free_result ($rg);
$rg = db_query ("select * from $INFO[db_prefix]book where k_art='$art' order by mesdate desc 
			limit " . ($page - 1) * $f_config['mpp'] . ", $f_config[mpp]");
$text = join ('', file ("$skin/gbmes.html"));
$mes = "";
$censor = array();
$rcn = db_query ("select * from $INFO[db_prefix]censor");
while ($fcn = db_fetch_assoc ($rcn)) $censor[$fcn['bad']] = $fcn['good'];
db_free_result ($rcn);
while ( $fg = db_fetch_assoc ($rg) )
{
	$pst = $fg['post'];
	$pst = str_replace (array_keys ($censor), array_values ($censor), $pst);
	$pst = format_text ($pst);
	$tag = $global_tag;
	$tag['AUTHORNAME'] = protection ($fg['user']);
	$tag['MESSAGE'] = $pst;
	$tag['QUOTE'] = nl2br (protection ($fg['post']));
	$tag['AUTHOREMAIL'] = str_replace (array ("@", "."), array ("&#64;", "&#46;"), protection ($fg['email']));
	$tag['URL'] = protection ($fg['url']);
	$tag['ICQ'] = protection ($fg['icq']);
	$tag['IP'] = $fg['ip'];
	$tag['ID'] = $fg['k_post'];
	$tag['DATE'] = date ($f_config['datefrm'], $fg['mesdate']);

	$mes .= tpl ($text, $tag);
}

if ( ! ban(0) )
{
	$text = join ('', file ("$skin/gbform.html"));
	$tag = $global_tag;
	$tag['INDEX'] = $art;
	if ( isset ($_COOKIE['AUTHORNAME']) ) $tag['AUTHORNAME'] = protection ($_COOKIE['AUTHORNAME']);
	if ( isset ($_COOKIE['AUTHOREMAIL']) ) $tag['AUTHOREMAIL'] = protection ($_COOKIE['AUTHOREMAIL']);
	if ( isset ($_COOKIE['URL']) ) $tag['URL'] = protection ($_COOKIE['URL']);
	if ( isset ($_COOKIE['ICQ']) ) $tag['ICQ'] = protection ($_COOKIE['ICQ']);
	$sid = $num = "";
	get_num ($sid, $num);
	$tag['NUM'] = $num;
	$tag['SID'] = $sid;
	$form = tpl ($text, $tag);
} else $form = '';

$text = join ('', file ("$skin/pagebar.html"));
$pgs = "";
for ($p = 0; $p < $cnt / $f_config['mpp']; $p++)
{
	$tag = $global_tag;
	$tag['PAGE'] = $p + 1;
	if ( $p != $page - 1 ) $tag['URL'] = "index.php?$art_href&amp;c=" . ($p + 1);
	$pgs .= tpl ($text, $tag);
}
if ( $page > 1 ) 
{
	$tag = $global_tag;
	$tag['PREV'] = $page - 1;
	$tag['URL'] = "index.php?$art_href&amp;c=" . ($page - 1);
	$pgs = tpl ($text, $tag) . $pgs;	
}
if ( $page < $cnt / $f_config['mpp'] ) 
{
	$tag = $global_tag;
	$tag['NEXT'] = $page + 1;
	$tag['URL'] = "index.php?$art_href&amp;c=" . ($page + 1);
	$pgs .= tpl ($text, $tag);	
}

$text = join ('', file ("$skin/gbmain.html"));
$tag = $global_tag;
if ($cnt / $f_config['mpp'] > 1) $tag['PAGEBAR'] = $pgs;
$tag['MESSAGES'] = $mes;
$tag['FORM'] = $form;
$comment = tpl ($text, $tag);

?>