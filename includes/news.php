<?php
if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$page = isset ($_GET['c']) ? $_GET['c'] : 1;
if ( $page < 1 ) $page = 1;

$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]news where k_art='$art' and faq=0");
$fg = db_fetch_assoc ($rg);
$cnt = $fg['CNT'];
db_free_result ($rg);
$rg = db_query ("select * from $INFO[db_prefix]news where k_art='$art' and faq=0
			order by mesdate desc limit " . ($page - 1) * $f_config['nws'] . ", $f_config[nws]");

$text = join ('', file ("$skin/newmes.html"));
$mes = "";
while ( $fg = db_fetch_assoc ($rg) )
{
	$tag = $global_tag;
	$tag['MESSAGE'] = format_text ($fg['post']);
	$tag['TITLE'] = format_text ($fg['title']);
	$tag['ID'] = $fg['k_new'];
	$tag['DATE'] = date ($f_config['datefrm'], $fg['mesdate']);
	$mes .= tpl ($text, $tag);
}

$text = join ('', file ("$skin/pagebar.html"));
$pgs = "";
for ($p = 0; $p < $cnt / $f_config['nws']; $p++)
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
if ( $page < $cnt / $f_config['nws'] ) 
{
	$tag = $global_tag;
	$tag['NEXT'] = $page + 1;
	$tag['URL'] = "index.php?$art_href&amp;c=" . ($page + 1);
	$pgs .= tpl ($text, $tag);	
}

$text = join ('', file ("$skin/news.html"));
$tag = $global_tag;
if ($cnt / $f_config['nws'] > 1) $tag['PAGEBAR'] = $pgs;
$tag['INDEX'] = $art;
$tag['MESSAGES'] = $mes;
$news = tpl ($text, $tag);

?>