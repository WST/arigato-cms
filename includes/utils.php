<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

error_reporting  (E_ERROR | E_WARNING | E_PARSE);

$starttime = gettickcount();

require_once "db.php";

db_connect();

$final_query = "";

$version = "1.0.0 beta";

$check_num = false;
$check_sid = false;

$r = db_query ("select * from $INFO[db_prefix]config,$INFO[db_prefix]blocks,$INFO[db_prefix]messages,$INFO[db_prefix]chatconf");
$f_config = db_fetch_assoc ($r);
db_free_result ($r);
$admin = admin();

// формируем список банов:
$rb = db_query ("select mode,ip from $INFO[db_prefix]ban");
$ban_mode = array();
$ip = array();
$ip[] = $_SERVER['REMOTE_ADDR'];
if ( isset ($_SERVER['HTTP_X_FORWARDED_FOR']) )
{
	$xfor = explode (",", $_SERVER['HTTP_X_FORWARDED_FOR']);
	for ($i = 0; $i < count ($xfor); $i++) $ip[] = $xfor[$i];
}
while ( $fb = db_fetch_assoc ($rb) )
{
	$bn = $fb['ip'];
	$tmp = strpos ($bn, '*');
	if ( $tmp !== false ) $bn = substr ($bn, 0, $tmp);
	for ($i = 0; $i < count ($ip); $i++)
		if ( strpos ($ip[$i], $bn) === 0 ) $ban_mode[$fb['mode']] = 1;
}
db_free_result ($rb);

// глобальные шаблонные теги:
$global_tag = array();
$global_tag['IP_ADDR'] = $_SERVER['REMOTE_ADDR'];
$global_tag['TIME'] = date ($f_config['datefrm']);
$global_tag['ADMIN_MAIL'] = $f_config['email'];
$global_tag['PATH'] = $f_config['skin'];
$global_tag['SITE'] = $f_config['title'];
$global_tag['SITE_URL'] = $f_config['url'];
$global_tag['STAT_CNT'] = $f_config['viewer'];
$global_tag['STAT_UNI'] = $f_config['counter'];
$global_tag['STAT_MAX'] = $f_config['maxcnt'];
$global_tag['MAX_TIME'] = date ($f_config['datefrm'], $f_config['maxdate']);
$global_tag['SITE_AGE'] = intval ((time() - $f_config['siteinstall']) / 86400);
$global_tag['C_TITLE'] = $f_config['c_title'];
$global_tag['BANNER'] = $f_config['banner'];
$global_tag['S_HREF'] = $art_href;
$global_tag['SCRIPT'] = anti_inj ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
$global_tag['COPYRIGHT'] = "<a href=\"http://my-cms.jino-net.ru\" target=\"_blank\">Arigato CMS</a> $version, &copy; Акатов Алексей, 2005 - 2006";
if ( $admin ) $global_tag['ADMIN'] = sid ($f_config['sid']);
$global_tag['('] = '{';
$global_tag[')'] = '}';

function finalization()
// Завершение работы скрипта:
{
	global $INFO, $final_query, $f_config, $cnt_onl, $global_tag;
	if ( strpos ($_SERVER['HTTP_REFERER'], $f_config['url']) === false )
	{ // обратные ссылки:
		$ref = get_url (url_control ($_SERVER['HTTP_REFERER']));
		db_query ("update $INFO[db_prefix]refer set last_d=" . time() . ",ip='$_SERVER[REMOTE_ADDR]',cnt=cnt+1 where url='$ref'");
		if ( db_affected_rows() == 0 ) db_query ("insert into $INFO[db_prefix]refer (url,cnt,last_d,ip)
									values ('$ref',1," . time() . ",'$_SERVER[REMOTE_ADDR]')");
	}
	if ( $f_config['new_send'] == 1 )
	{ // отправка обновлений сайта по почте:
		$r = db_query ("select max(mesdate) from $INFO[db_prefix]art");
		$f = db_fetch_row ($r);
		if ( $f[0] > time() - 2 * $f_config['new'] * 60 * 60 * 24 )
		{
			$rs = db_query ("select send_time,email,cd,k_mail from $INFO[db_prefix]email
						where active=1 and send_time<" . (time() - $f_config['new'] * 60 * 60 * 24) . " limit 1");
			if ( $fs = db_fetch_assoc ($rs) )
			{
				// новые разделы:
				$ra = db_query ("select title,caption,shot,mesdate,k_art,mnemonic,sup from $INFO[db_prefix]art
							where mesdate>$fs[send_time] order by mesdate");
				$artmes = join ('', file ("$f_config[skin]/letters/artmes.txt"));
				$body = '';
				while ( $fa = db_fetch_assoc ($ra) )
				{
					$tag = $global_tag;
					if ( $fa['sup'] > 0 )
					{
						$rsb = db_query ("select title,mnemonic from $INFO[db_prefix]art where k_art=$fa[sup]");
						if ( $fsb = db_fetch_assoc ($rsb) )
						{
							$tag['FATHER'] = $fsb['title'];
							$tag['F_ART'] = $fa['sup'];
							$tag['F_MNEM'] = $fsb['mnemonic'];
							$tag['F_HREF'] = get_href ($fa['sup'], $fsb['mnemonic']);
						}
						db_free_result ($rsb);
					}
					$tag['TITLE'] = $fa['title'];
					$tag['CAPTION'] = $fa['caption'];
					$tag['SHOT'] = $fa['shot'];
					$tag['K_ART'] = $fa['k_art'];
					$tag['MNEM'] = $fa['mnemonic'];
					$tag['HREF'] = get_href ($fa['k_art'], $fa['mnemonic']);
					$tag['DATE'] = date ($f_config['datefrm'], $fa['mesdate']);
					$body .= tpl ($artmes, $tag);
				}
				db_free_result ($ra);
				// новые новости:
				$ra = db_query ("select title,post,mesdate,k_art from $INFO[db_prefix]news
							where mesdate>$fs[send_time] and faq=0 order by mesdate,k_art");
				$newmes = join ('', file ("$f_config[skin]/letters/newmes.txt"));
				$newart = join ('', file ("$f_config[skin]/letters/newart.txt"));
				$news = '';
				$nart = 0;
				$tag = $global_tag;
				$ntag = $global_tag;
				while ( $fa = db_fetch_assoc ($ra) )
				{
					if ( $nart != $fa['k_art'] )
					{
						$nart = $fa['k_art'];
						$rsb = db_query ("select title,caption,k_art,mnemonic from $INFO[db_prefix]art where k_art='$nart'");
						if ( $fsb = db_fetch_assoc ($rsb) )
						{
							$ntag['TITLE'] = $fsb['title'];
							$ntag['CAPTION'] = $fsb['caption'];
							$ntag['K_ART'] = $fsb['k_art'];
							$ntag['MNEM'] = $fsb['mnemonic'];
							$ntag['HREF'] = get_href ($fsb['k_art'], $fsb['mnemonic']);
							$news .= tpl ($newart, $ntag);
						}
						db_free_result ($rsb);
					}
					$tag['TITLE'] = $fa['title'];
					$mes = plain_text ($fa['post']);
					$tag['NEWMES'] = $mes;
					$tag['NEWABS'] = abstr ($mes);
					$tag['DATE'] = date ($f_config['datefrm'], $fa['mesdate']);
					$news .= tpl ($newmes, $tag);
				}
				db_free_result ($ra);

				if ( ! empty ($body) || ! empty ($news) )
				{
					$tag = $global_tag;
					$tag['BODY'] = $body;
					$tag['NEWS'] = $news;
					$tag['EMAIL'] = $fs['email'];
					$tag['CD'] = $fs['cd'];
					$tag['DATE'] = date ($f_config['datefrm'], $fs['send_time']);
					if ( mask_mail ($fs['email'], 'news.txt', $tag) )
						db_query ("update $INFO[db_prefix]email set send_time=" . time() . " where k_mail='$fs[k_mail]'");
				}
			}
			db_free_result ($rs);
		}
		db_free_result ($r);
	}
	// статистика:
	$query = "update $INFO[db_prefix]config set viewer=" . ($f_config['viewer'] + 1);
	$url = anti_inj ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
	db_query ("update $INFO[db_prefix]counter set reftime=" . time() . ",url='$url',cnt=cnt+1
			where ip='$_SERVER[REMOTE_ADDR]' and agent='" . protection ($_SERVER['HTTP_USER_AGENT']) . "'");
	if ( db_affected_rows() == 0 )
	{
		$irs = db_query ("select count(*) from $INFO[db_prefix]counter where ip='$_SERVER[REMOTE_ADDR]'");
		$ifs = db_fetch_row ($irs);
		if ( $ifs[0] == 0 ) $query .= ",counter=" . ($f_config['counter'] + 1);
		db_free_result ($irs);
		db_query ("insert into $INFO[db_prefix]counter (ip,reftime,url,agent,cnt) 
					values ('$_SERVER[REMOTE_ADDR]'," . time() . ",'$url','" . protection ($_SERVER['HTTP_USER_AGENT']) . "',1)");
	}
	if ( $cnt_onl > $f_config['maxcnt'] ) $query .= ",maxcnt=$cnt_onl,maxdate=" . time();
	if ( $f_config['viewer'] % 50 == 0 )
	{
		db_query ("delete from $INFO[db_prefix]counter where reftime<" . (time() - 60 * 60 * 24 * 30));
		db_query ("delete from $INFO[db_prefix]nums where stime<" . (time() - 60 * 60));
	}
	db_query ($query);
	
	if ( ! empty ($final_query) ) db_query ($final_query);
}

function abstr ($mes)
// Абстракт текста (только начало):
{
	if ( strlen ($mes) > 512 )
	{
		$p = strpos ($mes, "<br") - 1;
		if ( $p > 512 || $p < 16 ) $p = strpos ($mes, ".", 512);
		if ( $p !== false ) $mes = substr ($mes, 0, $p) . "...";
	}
	return $mes;
}

function get_href ($k_art, $mnemonic)
// Получить ссылку на оаздел:
{
	return ( empty ($mnemonic) ) ? "index.php?art=$k_art" : "index.php?cap=$mnemonic";
}

function artindex ($k_art)
// Индексация раздела для поиска:
{
	global $INFO;
	$r = db_query ("select title,caption,author,shot,mnemonic,keywords,post,icon from $INFO[db_prefix]art where k_art='$k_art'");
	if ( $f = db_fetch_assoc ($r) )
	{
		$words = $f['title'] . ' ' . $f['title'] . ' ' . $f['caption'] . ' ' . $f['caption'] . ' ' . $f['author'] . ' ' . $f['author'] . ' ' .
			$f['shot'] . ' ' . $f['mnemonic'] . ' ' . $f['keywords'] . ' ' . $f['post'] . ' ' . $f['icon'];
		db_free_result ($r);
		$r = db_query ("select title,post from $INFO[db_prefix]news where k_art='$k_art'");
		while ( $f = db_fetch_assoc ($r) ) $words .= ' ' . $f['title'] . ' ' . $f['post'];
		db_free_result ($r);
		$r = db_query ("select title,caption,pictur from $INFO[db_prefix]photos where k_art='$k_art'");
		while ( $f = db_fetch_assoc ($r) ) $words .= ' ' . $f['title'] . ' ' . $f['caption'] . ' ' . $f['pictur'];
		db_free_result ($r);
		$words = share ($words);
		db_query ("update $INFO[db_prefix]art set words='$words' where k_art='$k_art'");
	}
}

function get_ext ($name)
// Получить расширение файла:
{
	$p = strrpos ($name, '.');
	return lowercase (substr ($name, $p + 1));
}

function get_num (&$sid, &$num)
// Генерация контрольного числа:
{
	global $INFO, $check_num, $check_sid;
	if ( $check_num === false )
	{
		$sid = gen_rand_string();
		$num = "";
		$k = mt_rand (0, 255);
		mt_srand (time() + (double) microtime() * $k);
		for ($i = 0; $i < 6; $i++) $num .= mt_rand (0, 9);
		db_query ("insert into $INFO[db_prefix]nums (sid,num,stime) values ('$sid','$num','" . time() . "')");
		$check_num = $num;
		$check_sid = $sid;
	} else
	{
		$num = $check_num;
		$sid = $check_sid;
	}
}

function local_globals ($action)
{ // Локализация глобальных переменных:
	static $save_glob, $ind;
	if ( ! isset ($ind) )
	{
		$ind = 0;
		$save_glob = array();
	}
	switch ( $action )
	{
		case 0:
			$save_glob[++$ind] = array();
			foreach ($GLOBALS as $key => $val)
			{
				if ( $key != "GLOBALS" ) $save_glob[$ind][$key] = $val;
			}
			break;
		case 1: foreach ($save_glob[$ind--] as $key => $val) $GLOBALS[$key] = $val;
	}
}

function local_begin()
{ // Открытие локальной области:
	local_globals (0);
}

function local_end()
{ // Закрытие локальной области:
	local_globals (1);
}

function get_size ($name)
// Получить размер файла:
{
	if ( ($size = @filesize ($name)) === false ) return "none";
	if ( $size < 1024 ) return "$size bytes";
	if ( ($size /= 1024) < 1024 ) return intval ($size + 0.5) . " Kb";
	return intval ($size / 1024 + 0.5) . " Mb";	
}

function gettickcount()
// Получить микротайм:
{
	return array_sum (explode (" ", microtime()));
}

function get_url ($url)
// Убрать слэшь в конце url-адреса:
{
	while ( substr ($url, -1) === '/' || substr ($url, -1) === '\\' ) $url = substr ($url, 0, -1);
	return $url;
}

function lowercase ($s)
// Преобразовать строку к нижнему регистру:
{
	return str_replace (
		array('Q','W','E','R','T','Y','U','I','O','P','A','S','D','F','G','H','J','K','L','Z','X','C','V','B','N','M',
			'Й','Ц','У','К','Е','Н','Г','Ш','Щ','З','Х','Ъ','Ф','Ы','В','А','П','Р','О','Л','Д','Ж','Э','Я','Ч','С','М','И','Т','Ь','Б','Ю','Ё'), 
		array('q','w','e','r','t','y','u','i','o','p','a','s','d','f','g','h','j','k','l','z','x','c','v','b','n','m',
			'й','ц','у','к','е','н','г','ш','щ','з','х','ъ','ф','ы','в','а','п','р','о','л','д','ж','э','я','ч','с','м','и','т','ь','б','ю','ё'), 
		$s);
}

function mask_mail ($to, $mask, $tags)
// Отправка шаблонного письма:
{
	global $f_config, $global_tag;
	$fl = file ("$f_config[skin]/letters/$mask");
	$subj = $fl[0];
	$mes = '';
	for ($i = 1; $i < count ($fl); $i++) $mes .= $fl[$i];
	return send_mail ($to, tpl ($subj, $global_tag), tpl ($mes, $tags), "html");
}

function send_mail ($to, $subj, $message, $type = "plain")
// Отправка письма:
{
	global $f_config, $global_tag;
	$headers  = "From: " . tpl ($f_config['afrom'], $global_tag) . " <$f_config[email]>\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/$type; charset=\"windows-1251\"";
	$message .= "\n" . tpl ($f_config['signature'], $global_tag);
	return @mail ($to, $subj, $message, $headers);
}

function ban ($m)
// Проверка IP на бан:
{
	global $ban_mode;
	return isset ($ban_mode[$m]);
}

function share ($s)
// Выделить слова из строки:
{
	static $regex = array(
		'#&[a-z]+;#i',
		'#[^a-zа-я_0-9]#',
		'#(\s|\A)(\S{1,2}|\S{21,})(?=(\s|\Z))#',
		'#(\s|\A)\d+(?=(\s|\Z))#',
		'#\s+#');
	static $replace = array(' ', ' ', ' ', ' ', ' ');
	$s = trim (preg_replace ($regex, $replace, lowercase (strip_tags ($s))));
	return $s;
}

function cntpos ($s, $sub)
// Количество вхождений подстроки $sub в строку $s:
{
	$res = 0;
	$p = -1;
	while ( ($p = strpos ($s, $sub, $p + 1)) !== false ) $res++;
	return $res;
}

function anti_spam ($mode)
// Проверка антиспамом:
{
	global $f_config;
	global $INFO;
	$t = time () - $f_config['sp_book'];
	db_query ("delete from $INFO[db_prefix]spam where mode=0 and posttime<$t");
	$t = time () - $f_config['sp_mail'];
	db_query ("delete from $INFO[db_prefix]spam where mode=1 and posttime<$t");
	$t = time () - 30;
	db_query ("delete from $INFO[db_prefix]spam where mode=2 and posttime<$t");	
	$t = time () - $f_config['sp_send'];
	db_query ("delete from $INFO[db_prefix]spam where mode=3 and posttime<$t");
	$ip[] = anti_inj ($_SERVER['REMOTE_ADDR']);
	if ( isset ($_SERVER['HTTP_X_FORWARDED_FOR']) )
	{
		$xfor = explode (",", anti_inj ($_SERVER['HTTP_X_FORWARDED_FOR']));
		for ($i = 0; $i < count ($xfor); $i++) $ip[] = $xfor[$i];
	}
	for ($i = 0; $i < count ($ip); $i++)
	{
		$r = db_query ("select k_sp from $INFO[db_prefix]spam where mode='$mode' and ip='$ip[$i]'");
		if ( db_num_rows ($r) != 0 ) return true;
	}
	return false;
}

function sid ($s)
// Получить код сессии:
{
	return md5 (md5 ($_SERVER['REMOTE_ADDR']) . $s . md5 ($_SERVER['HTTP_USER_AGENT']));
}

function admin ()
// Проверить сессию админа:
{
	global $f_config;
	global $INFO;
	if ( isset ($_COOKIE['SID']) )
	{
		$t = time () - 30 * 60;
		db_query ("delete from $INFO[db_prefix]sessions where stime<$t");	
		$r = db_query ("select sid,k_ses from $INFO[db_prefix]sessions");
		while ( $f = db_fetch_assoc ($r) )
		{
			if ( sid ($f['sid']) === $_COOKIE['SID'] )
			{
				db_query ("update $INFO[db_prefix]sessions set stime=" . time() . " where k_ses=$f[k_ses]");
				return true;
			}
		}
		db_free_result ($r);
	}
	return false;
}

function tpl ($text, $tags)
// (c) Акатов Алексей, 2005 - 2006.
// Шаблонный интерпретатор:
{
	$res = "";
	$p = 0;
	while ( $p < strlen ($text) )
	{
		if ( ($i = strpos ($text, "{", $p)) === false )
		{
			$res .= substr ($text, $p);
			break;
		}
		$res .= substr ($text, $p, $i - $p);
		$c = $p = $i;
		$i++;
		while ( ($p = strpos ($text, "}", $p + 1)) !== false && 
			($c = strpos ($text, "{", $c + 1)) !== false && $p > $c );
		if ( $p === false )
		{
			$res .= substr ($text, $i - 1);
			break;
		}
		if ( ($tag = substr ($text, $i, $p - $i)) !== "" )
		{
			if ( $tag[0] === "?" || $tag[0] === "!" )
			{
				if ( ($e = strpos ($tag, "=")) !== false || ($e = strpos ($tag, "<")) !== false || ($e = strpos ($tag, ">")) !== false )
				{ // подстановка сравнения:
					$tmp = substr ($tag, 1, $e - 1);
					$key = tpl ($tmp, $tags);
					$val = tpl (substr ($tag, $e + 1), $tags);
					if ( isset ($tags[$key]) )
					{
						switch ( $tag[$e] )
						{
							case "=": $ok = ($tags[$key] == $val); break;
							case "<": $ok = ($tags[$key] < $val); break;
							case ">": $ok = ($tags[$key] > $val); break;
						}
					} else $ok = false;
				} else
				{ // условная подстановка:
					$tmp = substr ($tag, 1);
					$key = tpl ($tmp, $tags);
					$ok = isset ($tags[$key]) && strlen ($tags[$key]) > 0;
				}
				if ( $tag[0] === "?" && $ok === false || $tag[0] === "!" && $ok === true )
				{
					$c = $p;
					while ( ($p = strpos ($text, "{" . $tmp . $tag[0] . "}", $p + 1)) !== false && 
						($c = strpos ($text, "{" . $tag[0] . $tmp, $c + 1)) !== false && $p > $c );
					if ( $p === false )
					{
						$res .= substr ($text, $i - 1);
						break;
					}
					$p += strlen ($tmp) + 2;
				}
			} elseif ( $tag[0] === "%" )
			{ // проверка файла на существование:
				if ( ! file_exists (tpl ($tmp = substr ($tag, 1), $tags)) )
				{
					if ( ($p = strpos ($text, "{" . $tmp . "%}", $p)) === false ) break;
					$p += strlen ($tmp) + 2;
				}
			} elseif ( $tag[0] === "#" ) // включение файла:
				$text = substr ($text, 0, $p + 1) . getfile (tpl (substr ($tag, 1), $tags)) . substr ($text, $p + 1);
			elseif ( $tag[0] === "$" )
			{ // арифметическая подстановка:
				$val = "";
				@eval ("\$val = " . tpl (substr ($tag, 1), $tags) . ";");
				$res .= $val;
			} elseif // определение шаблонного тега:
				( ($e = strpos ($tag, "=")) !== false ) $tags[tpl (substr ($tag, 0, $e), $tags)] = tpl (substr ($tag, $e + 1), $tags);
			else
			{ // простая подстановка:
				$key = tpl ($tag, $tags);
				if ( isset ($tags[$key]) ) $res .= $tags[$key];
			}
		}
		$p++;
	}
	return $res;
}

function getfile ($filename)
// Чтение файла в строку:
{
	return file_exists ($filename) ? join ("", @file ($filename)) : "";
}

function slash_control ()
// Проверка слэшей в параметрах POST:
{
	error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
	set_magic_quotes_runtime (0); // Disable magic_quotes_runtime

	if ( ! get_magic_quotes_gpc () )
	{
		if ( is_array ($_POST) )
		{
			while ( list ($k, $v) = each ($_POST) )
			{
				if ( is_array ($_POST[$k]) )
				{
					while ( list($k2, $v2) = each ($_POST[$k]) ) $_POST[$k][$k2] = addslashes ($v2);
					@reset ($_POST[$k]);
				}
				else $_POST[$k] = addslashes ($v);
			}
			@reset ($_POST);
		}
	}
}


function anti_inj ($s)
// Блокировка кавычек:
{
	if ( strpos ($s, "'") !== false || strpos ($s, '"') !== false || strpos ($s, "\\") !== false ) return '';
	return $s;
}

function mail_correct ($email)
// Проверка на корректность mail-адреса:
{
	$len = strlen ($email);
	if ( $len == 0 ) return true;
	if ( strpos ($email, "'") !== false || strpos ($email, '"') !== false ||
		strpos ($email, "\n") !== false || strpos ($email, "\r") !== false ) return false;
	$p = strpos ($email, '@');
	$t = ( $p > 0 ) ? strpos ($email, '.', $p) : 0;
	return ( ! ($len < 8 || $p === false || $p >= $len - 1 || strpos ($email, '@', $p + 1) !== false ||
			$t === false || $t >= $len - 1 || $t < $p || $t < 1) );
}

function url_control ($url)
// Обработка полученного url-адреса:
{
	$url = anti_inj (trim ($url));
	if ( strlen ($url) > 0 && substr ($url, 0, 7) !== 'http://' ) $url = "http://$url";
	return $url;
}

function url_format ($url)
// Обработка длинных ссылок:
{
	if ( strlen ($url) > 48 ) return substr ($url, 0, 32) . '...' . substr ($url, -13);
	return $url;
}

function no_cache()
// Запрет кеширования странички:
{ 
	@header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	@header ("Cache-Control: no-cache, must-revalidate");
	@header ("Pramga: no-cache");
	@header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
}

function show ($body, $title, $descript, $keywords = '')
// Вывод странички в браузер:
{
	global $f_config;
	global $starttime;
	global $admin;
	global $INFO;
	global $global_tag;
	$tag = $global_tag;
	if ( $f_config['mail'] == 1 )
	{
		$mail_tag = $global_tag;
		$mail_tag['TITLE'] = $title;
		$tag['MAIL'] = tpl (join ("", file ("$f_config[skin]/patterns/mail.html")), $mail_tag);
	}
	$tag['TITLE'] = $title;
	$tag['DESCRIPT'] = $descript;
	$tag['KEYWORDS'] = $keywords;
	echo tpl (join ("", file ("$f_config[skin]/patterns/header.html")), $tag);
	echo $body;
	if ( $f_config['durat'] == 1 || ($f_config['durat'] == 2 && $admin) )
	{
		$durat = gettickcount() - $starttime;
		$tag['DURATION'] = substr ($durat, 0, 6);
		$tag['DB_QUERYS'] = $INFO['db_querys'];
		$tag['DB_TIME'] = substr ($INFO['db_time'], 0, 6);
		$tag['PHP_TIME'] = substr ($durat - $INFO['db_time'], 0, 6);
		if ( $durat > 0 )
		{
			$percent = intval ($INFO['db_time'] * 100 / $durat);
			$tag['DB_PERCENT'] = $percent;
			$tag['PHP_PERCENT'] = 100 - $percent;
		}
	}
	echo tpl (join ("", file ("$f_config[skin]/patterns/footer.html")), $tag);
}

function referer()
// Получение обратной ссылки:
{
	global $f_config;
	if ( strpos ($_SERVER['HTTP_REFERER'], $f_config['url']) === false ) $url = $f_config['url'];
	else $url = $_SERVER['HTTP_REFERER'];
	return $url;
}

function error ($mes, $cap)
// Вывод сообщения об ошибке:
{
	global $f_config, $global_tag;
	$tag = $global_tag;
	$tag['MESSAGE'] = $mes;
	$tag['CAPTION'] = $cap;
	$tag['BACK'] = referer ();
	show (tpl (join ('', file ("$f_config[skin]/patterns/message.html")), $tag), $cap, '');
	finalization();
	exit;
}

function protection ($text)
// Замена опасных символов:
{
	return str_replace (array('&', "'", '"', '<', '>', '\\'), array('&amp;', '&#39;', '&quot;', '&lt;', '&gt;', '&#92;'), $text);
}

function gen_rand_string ()
// Генерация случайной строки:
{
	$chars = array( 'a', 'A', 'b', 'B', 'c', 'C', 'd', 'D', 'e', 'E', 'f', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'J',  'k', 'K', 'l', 'L', 'm', 'M', 'n', 'N', 'o', 'O', 'p', 'P', 'q', 'Q', 'r', 'R', 's', 'S', 't', 'T',  'u', 'U', 'v', 'V', 'w', 'W', 'x', 'X', 'y', 'Y', 'z', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
	$max_chars = count ($chars) - 1;
	srand ( (double) microtime () * 1000000);
	$rand_str = '';
	for($i = 0; $i < 10; $i++) $rand_str .= $chars[rand (0, $max_chars)];
	return md5 ($rand_str);
}

function format_text ($text)
// Преобразование текста перед выводом:
{
	global $f_config;
	require_once "includes/bb_codes.php";
	include "$f_config[skin]/patterns/smiles.php";
	foreach ($smile as $key => $path) $smile[$key] = "<img src=\"$f_config[skin]/images/$path\" alt=\"" . protection ($key) . "\" />";
	$smile["[hr]"] = "<hr class=\"bb\" />";
	return bb_codes ($text, $smile);
}

function plain_text ($text)
// Удаление bb-кодов из текста:
{
	global $f_config;
	require_once "includes/bb_codes.php";
	include "$f_config[skin]/patterns/smiles.php";
	return no_bb ($text, $smile);
}

?>