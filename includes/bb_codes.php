<?php

			/************************************************************/
			/*                                                          */
			/*             М О Д У Л Ь    О Б Р А Б О Т К И             */
			/*                 B B - К О Д О В    Д Л Я                 */
			/*                      Ф О Р У М О В                       */
			/*                            И                             */
			/*                Г О С Т Е В Ы Х    К Н И Г                */
			/*                                                          */
			/*    Исходное имя файла: bb_codes.php                      */
			/*    Разработчик: Акатов Алексей aka Arigato, (c) 2005.    */
			/*    E-Mail: akatov@list.ru                                */
			/*                                                          */
			/************************************************************/

/*

function bb_codes ($text, $smiles) - Обработка bb-кодов
function no_bb ($text, $smiles) - Удаление bb-кодов

*/

function bb_codes ($text, $smiles, $bb = 0, $light = 0)
// Обработка bb-кодов:
{
	if ( ! is_array ($bb) )
		$bb = array ("b" => "_bb_tag", "i" => "_bb_tag", "u" => "_bb_tag",
				"s" => "_bb_tag", "o" => "_bb_o", "html" => "_bb_html",
				"sub" => "_bb_tag", "sup" => "_bb_tag", "center" => "_bb_div",
				"right" => "_bb_div", "no" => "_bb_no", "color" => "_bb_color",
				"size" => "_bb_size", "code" => "_bb_code",
				"quote" => "_bb_quote", "img" => "_bb_img", "url" => "_bb_url");
				
	$codes = array();
	$codes["`1`"] = "&#96;";
	$cnum = 1;
	$text = str_replace ("`", "`1`", $text);
	if ( count ($bb) > 0 )
	{
		$l = 0;
		while ( ($l = strpos ($text, "[", $l)) !== false && ($c = $p = $r = strpos ($text, "]", $l)) !== false )
		{
			$tag = substr ($text, $l + 1, $r - $l - 1);
			if ( ($e = strpos ($tag, "=")) !== false )
			{
				$val = substr ($tag, $e + 1);
				$tag = substr ($tag, 0, $e);
			} else $val = "";
			while ( ($c = strpos ($text, "[/$tag]", $c + 1)) !== false && 
				($p = strpos ($text, "[$tag", $p + 1)) !== false && $p < $c );
			if ( $c !== false )
			{
				$con = substr ($text, $r + 1, $c - $r - 1);
				if ( isset ($bb[$tag]) && ($con = $bb[$tag] ($tag, $val, $con, $smiles, $bb, $light)) !== false )
				{
					$cd = "`" . (++$cnum) . "`";
					$codes[$cd] = $con;
					$text = substr ($text, 0, $l) . $cd . substr ($text, $c + strlen ($tag) + 3);
				} else $l++;
			} else $l++;
		}
	}

	// Подсветка ссылок:
	if ( ! is_array ($light) )
		$light = array ("http://" => "http://", "https://" => "https://", "ftp://" => "ftp://", "www." => "http://www.");
	foreach ($light as $pref => $prot)
	{
		$repl = array();
		$p = 0;
		while ( ($p = strpos ($text, $pref, $p)) !== false )
		{
			$url = "";
			$p += strlen ($pref);
			while ( $p < strlen ($text) && preg_match ("([0-9a-zA-Z_\\-+%&?:@.=/#])", $text[$p]) ) $url .= $text[$p++];
			$repl[($cd="`".(++$cnum)."`")] = $pref . $url;
			$href = bb_protection ($prot . $url);
			$url = bb_protection ($pref . $url);
			$codes[$cd] = "<a href=\"$href\" target=\"_blank\" class=\"bb\">$url</a>";
		}
		$text = str_replace (array_values ($repl), array_keys ($repl), $text);
	}

	// Замена смайликов:
	$repl = array();
	foreach ($smiles as $smile => $img)
	{
		$repl[($cd="`".(++$cnum)."`")] = $smile;
		$codes[$cd] = $img;
	}
	
	$text = str_replace (array_values ($repl), array_keys ($repl), $text);

	return str_replace (array_keys ($codes), array_values ($codes), nl2br (bb_protection ($text)));
}

function no_bb ($text, $smiles)
// Удаление bb-кодов:
{
	// Вырезаем блоки quote и code:
	$remove = array ("quote", "code");
	for ($i = 0; $i < count ($remove); $i++)
	{
		$l = 0;
		do
		{
			$l = $r = $p = strpos ($text, "[{$remove[$i]}", $l);
			if ( $l !== false )
			{
				while ( ($r = strpos ($text, "[/{$remove[$i]}]", $r + 1)) !== false && 
					($p = strpos ($text, "[{$remove[$i]}", $p + 1)) !== false && $p < $r );
				if ( $r !== false ) $text = substr ($text, 0, $l) . substr ($text, $r + strlen ($remove[$i]) + 3);
			}
		} while ( $l !== false && $r !== false );
	}

	// Вырезаем смайлики:
	$text = str_replace (array_keys ($smiles), "", $text);
	
	// Вырезаем все псевдотеги:
	$l = 0;
	do
	{
		$l = strpos ($text, "[", $l);
		if ( $l !== false )
		{
			$r = strpos ($text, "]", $l);
			if ( $r !== false ) $text = substr ($text, 0, $l) . substr ($text, $r + 1);
		}
	} while ( $l !== false && $r !== false );
	
	return nl2br (bb_protection ($text));
}

function bb_protection ($text)
// Замена опасных символов:
{
	return str_replace (array ("&", '"', "'", "<", ">", "\\"), array ("&amp;", "&quot;", "&#39;", "&lt;", "&gt;", "&#92;"), $text);
}

function bb_check_url ($url)
// Корректирование URL-адреса:
{
	return preg_match ("#^(http://|https://|ftp://|mailto:)#", $url) ? $url : "http://$url";
}

// Функции, обрабатывающие поведение bb-кодов:

function _bb_tag ($tag, $val, $con, $smiles, $bb, $light)
{
	return "<$tag class=\"bb\">" . bb_codes ($con, $smiles, $bb, $light) . "</$tag>";
}

function _bb_div ($tag, $val, $con, $smiles, $bb, $light)
{
	return "<div align=\"$tag\" class=\"bb\">" . bb_codes ($con, $smiles, $bb, $light) . "</div>";
}

function _bb_no ($tag, $val, $con, $smiles, $bb, $light)
{
	return nl2br (bb_protection ($con));
}

function _bb_html ($tag, $val, $con, $smiles, $bb, $light)
{
	return $con;
}

function _bb_color ($tag, $val, $con, $smiles, $bb, $light)
{
	static $colors = array (
				"darkred" => 1,
				"red" => 1,
				"orange" => 1,
				"brown" => 1,
				"yellow" => 1,
				"green" => 1,
				"olive" => 1,
				"cyan" => 1,
				"cyan" => 1,
				"blue" => 1,
				"darkblue" => 1,
				"indigo" => 1,
				"violet" => 1,
				"white" => 1,
				"silver" => 1,
				"gray" => 1,
				"black" => 1);
	$val = strtolower ($val);
	if ( isset ($colors[$val]) || preg_match ("/^#[0-9a-f]{6}$/", $val) )
		return "<font color=\"$val\" class=\"bb\">" . bb_codes ($con, $smiles, $bb, $light) . "</font>";
	else return false;
}

function _bb_size ($tag, $val, $con, $smiles, $bb, $light)
{
	if ( preg_match ("#^[1-7]$#", $val) )
		return "<font size=\"$val\" class=\"bb\">" . bb_codes ($con, $smiles, $bb, $light) . "</font>";
	else return false;
}

function _bb_code ($tag, $val, $con, $smiles, $bb, $light)
{
	if ( ! empty ($con) )
	{
		$comment = bb_protection (( empty ($val) ) ? "Code:" : "Code ($val):");
		$con = bb_protection ($con);
		return "<div class=\"code\"><table align=\"center\" class=\"code\"><tr><th>$comment</th></tr><tr><td><pre class=\"bb\">$con</pre></td></tr></table></div>";
	} else return "";
}

function _bb_quote ($tag, $val, $con, $smiles, $bb, $light)
{
	if ( ! empty ($con) )
	{
		$comment = bb_protection (( empty ($val) ) ? "Quote:" : "$val wrote:");
		$con = bb_codes ($con, $smiles, $bb, $light);
		return "<div class=\"quote\"><table align=\"center\" class=\"quote\"><tr><th>$comment</th></tr><tr><td><blockquote class=\"bb\">$con</blockquote></td></tr></table></div>";
	} else return "";
}

function _bb_img ($tag, $val, $con, $smiles, $bb, $light)
{
	if ( preg_match ("#.(gif|png|jpg|jpeg|tiff)$#", $con) && strpos ("?", $con) === false )
	{
		$title = empty ($val) ? "" : " title=\"" . protection ($val) . "\"";
		$con = bb_protection (bb_check_url ($con));
		$href = str_replace (array ("(", ")"), array ("%28", "%29"), $con);
		return "<img src=\"$href\" alt=\"$con\"$title class=\"bb\" />";
	} else return false;
}

function _bb_url ($tag, $val, $con, $smiles, $bb, $light)
{
	$href = bb_protection (bb_check_url (empty ($val) ? $con : $val));
      $href = str_replace (array ("(", ")"), array ("%28", "%29"), $href);
	if ( ! empty ($val) ) $con = bb_codes ($con, $smiles, $bb, $light);
	return "<a href=\"$href\" target=\"_blank\" class=\"bb\">$con</a>";
}

function _bb_o ($tag, $val, $con, $smiles, $bb, $light)
{
	return "<span class=\"bb\" style=\"text-decoration:overline;\">" . bb_codes ($con, $smiles, $bb, $light) . "</span>";
}

?>