<?php

if ( !defined('CMS') )
{
	die ("Нарушение прав доступа");
	exit;
}

if ( $cont == 2 )
{
	$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]art");
	$page = isset ($_GET['c']) ? $_GET['c'] : 1;
	if ( $page < 1 ) $page = 1;
	$fg = db_fetch_assoc ($rg);
	$cnt = $fg['CNT'];
	db_free_result ($rg);
	if ( isset ($_GET['sort']) )
	{
		$sort = ( $_GET['sort'] === "date" ) ? "date" : "popular";
		SetCookie ("SORT", $sort, time() + 60 * 60 * 24 * 30 * 12);
	} else if ( isset ($_COOKIE['SORT']) ) $sort = ( $_COOKIE['SORT'] === "date" ) ? "date" : "popular";
		else $sort = "date";
	if ( $sort === "date" )
		$rg = db_query ("select $INFO[db_prefix]art.caption,$INFO[db_prefix]art.title,$INFO[db_prefix]art.mesdate,
					$INFO[db_prefix]art.cnt,$INFO[db_prefix]art.shot,$INFO[db_prefix]art.k_art,
					$INFO[db_prefix]art.mnemonic,$INFO[db_prefix]art.sup,
					COUNT($INFO[db_prefix]book.k_post) as comment
					from $INFO[db_prefix]art LEFT JOIN $INFO[db_prefix]book ON $INFO[db_prefix]art.k_art=$INFO[db_prefix]book.k_art
					group by $INFO[db_prefix]art.k_art
					order by mesdate desc limit " . ($page - 1) * $f_config['hst'] . ", $f_config[hst]");
	else $rg = db_query ("select $INFO[db_prefix]art.caption,$INFO[db_prefix]art.title,$INFO[db_prefix]art.mesdate,
					$INFO[db_prefix]art.cnt,$INFO[db_prefix]art.shot,$INFO[db_prefix]art.k_art,
					$INFO[db_prefix]art.mnemonic,$INFO[db_prefix]art.sup,
					($INFO[db_prefix]art.cnt/(1+(" . time() . " - $INFO[db_prefix]art.mesdate)/86400)) as popul,
					COUNT($INFO[db_prefix]book.k_post) as comment
					from $INFO[db_prefix]art LEFT JOIN $INFO[db_prefix]book ON $INFO[db_prefix]art.k_art=$INFO[db_prefix]book.k_art
					group by $INFO[db_prefix]art.k_art
					order by popul desc limit " . ($page - 1) * $f_config['hst'] . ", $f_config[hst]");
} else $rg = db_query ("select caption,title,mesdate,cnt,shot,k_art,mnemonic,sup from $INFO[db_prefix]art where sup='$art' order by pos");
$text = join ('', file ("$skin/hrec.html"));
$rec = "";
while ( $fg = db_fetch_assoc ($rg) )
{
	$tag = $global_tag;
	$tag['CAPTION'] = $fg['caption'];
	$tag['TITLE'] = $fg['title'];
	$tag['DATE'] = date ($f_config['datefrm'], $fg['mesdate']);
	$tag['COUNT'] = $fg['cnt'];
	$tag['CONTENT'] = $fg['shot'];
	$tag['K_ART'] = $fg['k_art'];
	$tag['MNEM'] = $fg['mnemonic'];
	$tag['HREF'] = get_href ($fg['k_art'], $fg['mnemonic']);
	$tag['SUP'] = $sup;
	if ( $cont == 2 )
	{
		$tag['NEWS'] = 'NEWS';
		if ( $fg['mesdate'] > time () - 60 * 60 * 24 * $f_config['new'] ) $tag['NEW'] = 'NEW';
		$tag['COMMENT'] = $fg['comment'];
		if ( $fg['sup'] > 0 )
		{
			$rs = db_query ("select k_art,mnemonic,title from $INFO[db_prefix]art where k_art=$fg[sup]");
			if ( $fs = db_fetch_assoc ($rs) ) 
			{
				$tag['FATHER'] = $fs['title'];
				$tag['F_ART'] = $fs['k_art'];
				$tag['F_MNEM'] = $fs['mnemonic'];
				$tag['F_HREF'] = get_href ($fs['k_art'], $fs['mnemonic']);
			}
			db_free_result ($rs);
		}
	}
	$rec .= tpl ($text, $tag);
}

if ( $cont == 2 )
{ // Разбиение на страницы:
	$text = join ('', file ("$skin/pagebar.html"));
	$pgs = "";
	for ($p = 0; $p < $cnt / $f_config['hst']; $p++)
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
	if ( $page < $cnt / $f_config['hst'] ) 
	{
		$tag = $global_tag;
		$tag['NEXT'] = $page + 1;	
		$tag['URL'] = "index.php?$art_href&amp;c=" . ($page + 1);
		$pgs .= tpl ($text, $tag);	
	}
}

$text = join ('', file ("$skin/history.html"));
$tag = $global_tag;
if ( $cont == 2 && $cnt / $f_config['hst'] > 1 ) $tag['PAGEBAR'] = $pgs;
$tag['RECORDS'] = $rec;
if ( $cont == 2 )
{
	$tag['NEWS'] = 'NEWS';
	$tag['SORT'] = $sort;
}

$history = tpl ($text, $tag);

?>