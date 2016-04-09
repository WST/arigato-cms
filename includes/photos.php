<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$rg = db_query ("select k_photo,pictur,title,caption,pos from $INFO[db_prefix]photos where k_art='$art' order by pos");

$text = join ('', file ("$skin/sketch.html"));
$photos = "";
$cnt = 0;
$tag = $global_tag;
$tag['SWIDTH'] = $f_config['sketchwidth'];
$tag['SHEIGHT'] = $f_config['sketchheight'];
while ( $fg = db_fetch_assoc ($rg) )
{
	$tag['IMAGE'] = $fg['pictur'];
	$tag['SIZE'] = get_size ("images/$fg[pictur]");
	$size = @getimagesize ("images/$fg[pictur]");
	$tag['WIDTH'] = $size[0];
	$tag['HEIGHT'] = $size[1];
	$tag['TITLE'] = $fg['title'];
	$tag['CAPTION'] = $fg['caption'];
	$tag['ID'] = $fg['k_photo'];
	if ( ++$cnt == $f_config['photocols'] )
	{
		$cnt = 0;
		$tag['NEWLINE'] = "<br />";
	} else $tag['NEWLINE'] = "";
	$photos .= tpl ($text, $tag);
}
$tag = $global_tag;
for ( $i = $cnt; $i < $f_config['photocols']; $i++ ) $photos .= tpl ($text, $tag);

$text = join ('', file ("$skin/photos.html"));
$tag['INDEX'] = $art;
$tag['PHOTOS'] = $photos;

$photos = tpl ($text, $tag);

?>