<?php

if ( ! file_exists ("install.lock") )
{
	header ("location:install.php");
	exit;
}

define ('CMS', true);

require_once "includes/utils.php";

if ( ban (2) )
{
	error (tpl ($f_config['error9'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

$sendblock = ( isset ($_GET['action']) && $_GET['action'] === "send" && $f_config['mail'] == 1 );

$findblock = ( isset ($_GET['find']) && share ($_GET['find']) <> '' && $f_config['find'] == 1 );

$capt = $descript = $keywords = "";

$k_sup = $k_supsup = $cnt_onl = 0;
$art_href = ""; /* параметр ссылки на текущий раздел (art либо cap) */
if ( ! $findblock && ! $sendblock )
{
	$index = true;
	if ( isset ($_GET['cap']) )
	{
		$mnem = anti_inj ($_GET['cap']);
		$r = db_query ("select k_art from $INFO[db_prefix]art where mnemonic='$mnem'");
		if ( $f = db_fetch_assoc ($r) )
		{
			$art = $f['k_art'];
			$art_href = "cap=$mnem";
			$index = false;
		}
		db_free_result ($r);
	}
	if ( $index === true && isset ($_GET['art']) )
	{
		$art = anti_inj ($_GET['art']);
		$index = false;
	}
	if ( $index === true )
	{
		$r = db_query ("select k_art from $INFO[db_prefix]art where inmenu=1 and sup=0 order by pos limit 1");
		if ( $f = db_fetch_assoc ($r) ) $art = $f['k_art'];
		db_free_result ($r);
	}
	$r = db_query ("select * from $INFO[db_prefix]art where k_art='$art'");
	if ( ! ($f = db_fetch_assoc ($r)) )
	{
		error (tpl ($f_config['error8'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
		exit;
	}
	if ( $f['p_n'] == 2 )
	{ // Переход на подраздел:
		db_free_result ($r);
		$r = db_query ("select * from $INFO[db_prefix]art where sup='$art' order by pos limit 1");
		if ( ! ($f = db_fetch_assoc ($r)) )
		{
			error (tpl ($f_config['error8'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
			exit;
		} else 
		{
			$art = $f['k_art'];
			if ( ! empty ($f['mnemonic']) ) $art_href = "cap=$f[mnemonic]";
		}
	}
	$k_sup = $f['sup'];
	if ( $k_sup != 0 )
	{
		$rp = db_query ("select sup from $INFO[db_prefix]art where k_art='$k_sup'");
		if ( $fp = db_fetch_assoc ($rp) ) $k_supsup = $fp['sup'];
		db_free_result ($rp);
	}
	if ( empty ($art_href) ) $art_href = "art=$art";
} else $art = -1;

$rm = db_query ("select k_art,mnemonic,title,icon from $INFO[db_prefix]art where sup=0 and inmenu=1 order by pos");
if ( db_num_rows ($rm) == 0 )
{
	header ("location:admin.php");
	exit;
}

$rs = db_query ("select COUNT(DISTINCT ip) as CNT from $INFO[db_prefix]counter where reftime>" . (time() - $f_config['stat'] * 60));
$fs = db_fetch_assoc ($rs);
$cnt_onl = $fs['CNT'];
if ( $cnt_onl == 0 ) $cnt_onl = 1;
db_free_result ($rs);
$global_tag['STAT_ONL'] = $cnt_onl;

$skin = $f_config['skin'] . '/patterns';
$text = join ('', file ("$skin/menu.html"));
$text_sub = join ('', file ("$skin/submenu.html"));
$menu = "";
while ( $fm = db_fetch_assoc ($rm) )
{
	$tag = $global_tag;
	$submenu = "";
	$show_sub = 0;
	if ( $fm['k_art'] !== $art ) $tag['LINK'] = $fm['title'];
	else 
	{
		$tag['VLINK'] = $fm['title'];
		$show_sub = 1;
	}

	// Подменю:
	if ( $k_sup == 0 && $show_sub == 1 || $k_sup == $fm['k_art'] || $k_supsup == $fm['k_art'] )
	{
		$show_sub = 1;
		$r_sub = db_query ("select k_art,mnemonic,title,icon from $INFO[db_prefix]art where sup=$fm[k_art] and inmenu=1 order by pos");
		while ( $f_sub = db_fetch_assoc ($r_sub) )
		{
			$tag_sub = $global_tag;
			if ( $f_sub['k_art'] !== $art ) $tag_sub['LINK'] = $f_sub['title'];
			else $tag_sub['VLINK'] = $f_sub['title'];
			$tag_sub['HREF'] = get_href ($f_sub['k_art'], $f_sub['mnemonic']);
			$tag['K_ART'] = $f_sub['k_art'];
			$tagp['MNEM'] = $f_sub['mnemonic'];
			$tag_sub['ICON'] = $f_sub['icon'];
			$submenu .= tpl ($text_sub, $tag_sub);
		}
		db_free_result ($r_sub);
	}
	
	if ( $show_sub == 1 ) $tag['SUBMENU'] = $submenu;
	$tag['HREF'] = get_href ($fm['k_art'], $fm['mnemonic']);
	$tag['K_ART'] = $fm['k_art'];
	$tagp['MNEM'] = $fm['mnemonic'];
	$tag['ICON'] = $fm['icon'];
	$menu .= tpl ($text, $tag);
}
db_free_result ($rm);

include ('includes/pool.php');

$main = join ('', file ("$skin/main.html"));
if ( ! $findblock && ! $sendblock )
{
	$final_query = "update $INFO[db_prefix]art set cnt=cnt+1 where k_art='$art'";

	$comment = '';
	$history = '';

	$sup = $f['sup'];
	$cont = $f['cont'];
	$dynamic = $f['dynamic'];

	switch ($dynamic)
	{
		case 1: include ('includes/history.php'); break;
		case 2: include ('includes/faq.php'); break;
		case 3: include ('includes/photos.php'); break;
	}

	switch ($cont)
	{
		case 1: include ('includes/gbook.php'); break;
		case 2: if ( $dynamic != 1 ) include ('includes/history.php'); break;
		case 3: include ('includes/news.php'); break;
	}
	
	$tag = $global_tag;
	if ( $f['path'] == 1 )
	{ // Вывод пути до раздела:
		$art_path = '';
		$s = $sup;
		$fpath = join ('', file ("$skin/path.html"));
		while ( $s != 0 )
		{
			$rt = db_query ("select caption,title,mnemonic,sup from $INFO[db_prefix]art where k_art='$s'");
			if ( $ft = db_fetch_assoc ($rt) )
			{
				$ptag = $global_tag;
				$ptag['HREF'] = get_href ($s, $ft['mnemonic']);
				$ptag['K_ART'] = $s;
				$ptag['MNEM'] = $ft['mnemonic'];
				$ptag['CAPTION'] = $ft['caption'];
				$ptag['TITLE'] = $ft['title'];
				$s = $ft['sup'];
				$art_path = tpl ($fpath, $ptag) . $art_path;
			} else $s = 0;
			db_free_result ($rt);
		}
		$tag['ART_PATH'] = ( empty ($art_path) ) ? " " : $art_path;
	}
	if ( $f['p_n'] == 1 )
	{ // Ссылки на соседние разделы:
		$rpn = db_query ("select k_art,mnemonic,title,caption from $INFO[db_prefix]art where sup='$art' order by pos limit 1");
		if ( $fpn = db_fetch_assoc ($rpn) )
		{
			$tag['NEXT'] = $fpn['k_art'];
			$tag['NEXT_MNEM'] = $fpn['mnemonic'];
			$tag['NEXT_HREF'] = get_href ($fpn['k_art'], $fpn['mnemonic']);
			$tag['NEXT_TITLE'] = $fpn['title'];
			$tag['NEXT_CAPTION'] = $fpn['caption'];
		} else
		{
			db_free_result ($rpn);
			$rpn = db_query ("select k_art,mnemonic,title,caption from $INFO[db_prefix]art
						where sup='$f[sup]' and pos>$f[pos] order by pos limit 1");
			if ( $fpn = db_fetch_assoc ($rpn) )
			{
				$tag['NEXT'] = $fpn['k_art'];
				$tag['NEXT_MNEM'] = $fpn['mnemonic'];
				$tag['NEXT_HREF'] = get_href ($fpn['k_art'], $fpn['mnemonic']);
				$tag['NEXT_TITLE'] = $fpn['title'];
				$tag['NEXT_CAPTION'] = $fpn['caption'];
			}
			db_free_result ($rpn);
			$rpn = db_query ("select k_art,mnemonic,title,caption from $INFO[db_prefix]art
						where sup='$f[sup]' and pos<$f[pos] order by pos desc limit 1");
			if ( $fpn = db_fetch_assoc ($rpn) )
			{
				$tag['PREV'] = $fpn['k_art'];
				$tag['PREV_MNEM'] = $fpn['mnemonic'];
				$tag['PREV_HREF'] = get_href ($fpn['k_art'], $fpn['mnemonic']);
				$tag['PREV_TITLE'] = $fpn['title'];
				$tag['PREV_CAPTION'] = $fpn['caption'];
			} else if ( $f['sup'] != 0 ) 
			{
				db_free_result ($rpn);
				$rpn = db_query ("select p_n,mnemonic,title,caption from $INFO[db_prefix]art where k_art='$f[sup]'");
				$fpn = db_fetch_assoc ($rpn);
				if ( $fpn['p_n'] == 1 )
				{
					$tag['PREV'] = $f['sup'];
					$tag['PREV_MNEM'] = $fpn['mnemonic'];
					$tag['PREV_HREF'] = get_href ($f['sup'], $fpn['mnemonic']);
					$tag['PREV_TITLE'] = $fpn['title'];
					$tag['PREV_CAPTION'] = $fpn['caption'];
				}
			}
		}
		db_free_result ($rpn);
	}
	
	$tag['DATE'] = date ($f_config['datefrm'], $f['mesdate']);
	switch ($f['format'])
	{
		case 0: $tag['ART'] = $f['post']; break; // в HTML-коде
		case 1: $tag['ART'] = format_text ($f['post']); break;// простой текст
		case 2: // в PHP
			if ( ! file_exists ("temp") ) mkdir ("temp", 0755);
			$fname = "temp/" . gen_rand_string() . ".php";
			$ftmp = @fopen ($fname, "w");
			flock ($ftmp, LOCK_EX);
			fputs ($ftmp, $f['post']);
			flock ($ftmp, LOCK_UN);
			fclose ($ftmp);
			ob_start();
			local_begin();
			include ($fname);
			local_end();
			$tag['ART'] = ob_get_contents();
			ob_clean();
			unlink ($fname);
			break;
	}

	$p = 0;
	$cnthtml = join ('', file ("$skin/count.html"));
	while ( ($p = strpos ($tag['ART'], '<a href="files/', $p)) !== false )
	{ // Счетчик скачиваний:
		if ( ($q = strpos ($tag['ART'], '"', $p + 15)) !== false )
		{
			$filename = substr ($tag['ART'], $p + 15, $q - $p - 15);
			if ( ($q = strpos ($tag['ART'], '>', $q)) !== false && ($ca = strpos ($tag['ART'], '</a>', $q)) !== false )
			{
				$filetext = substr ($tag['ART'], $q + 1, $ca - $q - 1);
				$cnttag = $global_tag;
				$rcnt = db_query ("select cnt,lastdate,ip from $INFO[db_prefix]dlcnt where file='$filename'");
				if ( $fcnt = db_fetch_assoc ($rcnt) )
				{
					$cnttag['COUNT'] = $fcnt['cnt'];
					$cnttag['IP'] = $fcnt['ip'];
					$cnttag['DATE'] = date ($f_config['datefrm'], $fcnt['lastdate']);
				} else $cnttag['COUNT'] = 0;
				db_free_result ($rcnt);
				$cnttag['FILE'] = $filename;
				$cnttag['TEXT'] = $filetext;
				if ( file_exists ("files/$filename") )
				{
					$cnttag['EXISTS'] = 1;
					$cnttag['SIZE'] = get_size ("files/$filename");
					$cnttag['EXT'] = get_ext ($filename);
					$fileinfo = stat ("files/$filename");
					$creat = ( $fileinfo["mtime"] > $fileinfo["ctime"] ) ? $fileinfo["mtime"] : $fileinfo["ctime"];
					$cnttag['CREATING'] = date ($f_config['datefrm'], $creat);
				} else $cnttag['EXISTS'] = 0;
				$cnthref = tpl ($cnthtml, $cnttag);
				$tag['ART'] = substr ($tag['ART'], 0, $p) . $cnthref . substr ($tag['ART'], $ca + 4);
				$p += strlen ($cnthref);
			}
		} else break;
	}

	$tag['K_ART'] = $f['k_art'];
	$tag['MNEM'] = $f['mnemonic'];
	$tag['CAPTION'] = $f['caption'];
	$tag['TITLE'] = $f['title'];
	$tag['AUTHOR'] = $f['author'];
	$tag['ICON'] = $f['icon'];
	if ( isset ($comment) ) $tag['ANSWER'] = $comment;
	if ( isset ($news) ) $tag['NEWS'] = $news;
	if ( isset ($history) ) $tag['HISTORY'] = $history;
	if ( isset ($photos) ) $tag['PHOTOS'] = $photos;
	if ( isset ($faq) ) $tag['FAQ'] = $faq;
	$capt = $f['caption'];
	$descript = trim (strip_tags ($f['shot']));
	$keywords = $f['keywords'];
} else 
{
	$descript = '';
	$capt = '';
	if ( $findblock )
	{
		include ('includes/find.php');
		$tag = $global_tag;
		$tag['HISTORY'] = $result;	
	} else // $sendblock:
	{
		$tag = $global_tag;
		if ( isset ($_COOKIE['AUTHOREMAIL']) ) $tag['AUTHOREMAIL'] = protection ($_COOKIE['AUTHOREMAIL']);
		if ( isset ($_GET['subj']) ) $tag['SUBJ'] = protection ($_GET['subj']);
		$sid = $num = "";
		get_num ($sid, $num);
		$tag['NUM'] = $num;
		$tag['SID'] = $sid;
		$sendbody = tpl (join ('', file ("$skin/send.html")), $tag);
		$tag = $global_tag;
		$tag['ART'] = $sendbody;
	}
}

$tag['COMMENT'] = $f['comment'];
$tag['MENU'] = $menu;
$tag['POOL'] = $pool;
$tag['WIDTH'] = $f_config['menuwidth'];

// Блоки:

if ( $f_config['b_stat'] == 1 || ($f_config['b_stat'] == 2 && $admin) ) $tag['STATISTIC'] = tpl (join ('', file ("$skin/stat.html")), $global_tag);

if ( $f_config['lastnew'] != 0 )
{
	$new_tag = $global_tag;
	$where = "where $INFO[db_prefix]news.k_art=$INFO[db_prefix]art.k_art and faq=0";
	if ( $f_config['lastnew'] == 2 ) $where .= " and $INFO[db_prefix]news.mesdate>" . (time () - 60 * 60 * 24 * $f_config['new']);
	$r = db_query ("select $INFO[db_prefix]news.*,$INFO[db_prefix]art.mnemonic from $INFO[db_prefix]news,$INFO[db_prefix]art
				$where order by mesdate desc limit 1");
	if ( $f = db_fetch_assoc ($r) )
	{
		$new_tag['NEWMES_BB'] = format_text ($f['post']);
		$new_tag['NEWTITLE_BB'] = format_text ($f['title']);
		$new_tag['NEWDATE'] = date ($f_config['datefrm'], $f['mesdate']);
		$new_tag['NEWART'] = $f['k_art'];
		$new_tag['NEWMNEM'] = $f['mnemonic'];
		$new_tag['NEWHREF'] = get_href ($f['k_art'], $f['mnemonic']);
		$mes = plain_text ($f['post']);
		$new_tag['NEWMES'] = $mes;
		$new_tag['NEWABS'] = abstr ($mes);
		$new_tag['NEWTITLE'] = plain_text ($f['title']);
		$ra = db_query ("select title from $INFO[db_prefix]art where k_art='$f[k_art]'");
		if ( $fa = db_fetch_assoc ($ra) ) $new_tag['NEWCAP'] = $fa['title'];
	}
	db_free_result ($r);
	$tag['LASTNEW'] = tpl (join ('', file ("$skin/lastnew.html")), $new_tag);
}

if ( $f_config['find'] == 1 )
{
	$find_tag = $global_tag;
	if ( $findblock ) $find_tag['FIND'] = share ($_GET['find']);
	$tag['SEARCH'] = tpl (join ('', file ("$skin/search.html")), $find_tag);
}

if ( $f_config['mail'] == 1 )
{
	$mail_tag = $global_tag;
	$mail_tag['TITLE'] = $tag['TITLE'];
	$tag['MAIL'] = tpl (join ('', file ("$skin/mail.html")), $mail_tag);
}

if ( $f_config['spam'] == 1 )
{
	$disp_tag = $global_tag;
	if ( isset ($_COOKIE['AUTHOREMAIL']) ) $disp_tag['AUTHOREMAIL'] = protection ($_COOKIE['AUTHOREMAIL']);
	$sid = $num = "";
	get_num ($sid, $num);
	$disp_tag['NUM'] = $num;
	$disp_tag['SID'] = $sid;
	$tag['DISPATCH'] = tpl (join ('', file ("$skin/dispatch.html")), $disp_tag);
}

if ( $f_config['chatblock'] == 1 )
{
	$chat_tag = $global_tag;
	if ( isset ($_COOKIE['AUTHORNAME']) ) $chat_tag['AUTHORNAME'] = protection ($_COOKIE['AUTHORNAME']);
	if ( ban(0) ) $chat_tag['BAN'] = "banned";
	$chat_tag['REFRESH'] = $f_config['refresh'];
	$tag['CHAT'] = tpl (join ('', file ("$skin/chatblock.html")), $chat_tag);
}

$links = "";
$r = db_query ("select title,url from $INFO[db_prefix]links order by pos");
while ($f = db_fetch_assoc ($r))
{
	$link_tag = $global_tag;
	$link_tag['URL'] = $f['url'];
	$link_tag['TITLE'] = $f['title'];
	$links .= tpl (join ('', file ("$skin/link.html")), $link_tag);
}
db_free_result ($r);
$tag['LINKS'] = $links;

show (tpl ($main, $tag), $capt, $descript, $keywords);

finalization();

?>