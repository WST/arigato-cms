<?php

if ( !defined('CMS') )
{
	die ("Нарушение прав доступа");
	exit;
}

$page = isset ($_GET['c']) ? $_GET['c'] : 1;
if ( $page < 1 ) $page = 1;

$frec = protection ($_GET['find']);
$rfn = db_query ("select k_fnd,cnt from $INFO[db_prefix]find where fstring='$frec'");
if ( $ffn = db_fetch_assoc ($rfn) )
{
	db_query ("update $INFO[db_prefix]find set fdate=" . time() . ", ip='$_SERVER[REMOTE_ADDR]', cnt=" . ($ffn['cnt'] + 1) . "
				 where fstring='$frec'");
} else db_query ("insert into $INFO[db_prefix]find (ip, fdate, fstring, cnt) values ('$_SERVER[REMOTE_ADDR]'," . time() . ",'$frec',1)");
db_query ("delete from $INFO[db_prefix]find where fdate<" . (time() - 60 * 60 * 24 * 30));

$s = share ($_GET['find']);

$w = explode (' ', $s);
for ($i = 0; $i < count ($w); $i++)
{
	if ( strlen ($w[$i]) == 6 ) $w[$i] = substr ($w[$i], 0, strlen ($w[$i]) - 1);
	if ( strlen ($w[$i]) > 6 ) $w[$i] = substr ($w[$i], 0, strlen ($w[$i]) - 3);
}
$arts = array();
$rg = db_query ("select k_art,words,caption from $INFO[db_prefix]art");
while ( $fg = db_fetch_assoc ($rg) )
{
	if ( $fg['caption'] !== '' )
	{
		if ( count ($w) > 1 ) $sum = cntpos ($fg['words'], $s) * count ($w) * 2;
		else $sum = 0;
		for ($i = 0; $i < count ($w); $i++) 
		{
			$c = cntpos ($fg['words'], $w[$i]);
			if ( $c > 0 ) $sum += $c;
			else $sum -= 1;
		}
		$arts[$fg['k_art']] = $sum;
	}
}
arsort ($arts);

$text = join ('', file ("$skin/hrec.html"));
$rec = '';
$cnt = 0;
$down = ($page - 1) * $f_config['hst'];
$up = $page * $f_config['hst'];
foreach ($arts as $key => $val)
{
	if ( $val < count ($w) ) break;
	if ( $cnt >= $down && $cnt < $up )
	{
		$rg = db_query ("select k_art,mnemonic,sup,caption,mesdate,cnt,shot from $INFO[db_prefix]art where k_art='$key'");
		$fg = db_fetch_assoc ($rg);
		$tag = $global_tag;
		$tag['CAPTION'] = $fg['caption'];
		$tag['TITLE'] = $fg['title'];
		$tag['DATE'] = date ($f_config['datefrm'], $fg['mesdate']);
		$tag['COUNT'] = $fg['cnt'];
		$tag['CONTENT'] = $fg['shot'];
		$tag['K_ART'] = $fg['k_art'];
		$tag['MNEM'] = $fg['mnemonic'];
		$tag['HREF'] = get_href ($fg['k_art'], $fg['mnemonic']);
		$tag['RATING'] = $val;	
		if ( $fg['sup'] > 0 )
		{
			$rs = db_query ("select k_art,mnemonic,title from $INFO[db_prefix]art where k_art='$fg[sup]'");
			if ( $fs = db_fetch_assoc ($rs) ) 
			{
				$tag['FATHER'] = $fs['title'];
				$tag['F_ART'] = $fs['k_art'];
				$tag['F_MNEM'] = $fs['mnemonic'];
				$tag['F_HREF'] = get_href ($fs['k_art'], $fs['mnemonic']);
			}
		}
		$rec .= tpl ($text, $tag);
	}
	$cnt++;	
}

$text = join ('', file ("$skin/pagebar.html"));
$pgs = "";
for ($p = 0; $p < $cnt / $f_config['hst']; $p++)
{
	$tag = $global_tag;
	$tag['PAGE'] = $p + 1;
	if ( $p != $page - 1 ) $tag['URL'] = "index.php?find=" . UrlEncode ($s) . "&amp;c=" . ($p + 1);
	$pgs .= tpl ($text, $tag);
}
if ( $page > 1 ) 
{
	$tag = $global_tag;
	$tag['PREV'] = $page - 1;
	$tag['URL'] = "index.php?find=" . UrlEncode ($s) . "&amp;c=" . ($page - 1);
	$pgs = tpl ($text, $tag) . $pgs;	
}
if ( $page < $cnt / $f_config['hst'] ) 
{
	$tag = $global_tag;
	$tag['NEXT'] = $page + 1;
	$tag['URL'] = "index.php?find=" . UrlEncode ($s) . "&amp;c=" . ($page + 1);
	$pgs .= tpl ($text, $tag);	
}

$text = join ('', file ("$skin/find.html"));
$tag = $global_tag;
if ($cnt / $f_config['hst'] > 1) $tag['PAGEBAR'] = $pgs;
$tag['RECORDS'] = $rec;
$tag['FOUND'] = $cnt;
$result = tpl ($text, $tag);
?>