<?php

define ('CMS', true);

require_once "includes/utils.php";

function spacer()
// Выдача пустого изображения:
{
	$img = ImageCreate (1, 1);
	$white = ImageColorAllocate ($img, 255, 255, 255);
	ImageFill ($img, 0, 0, $white);
	header ("Content-type: image/png");
	ImagePng ($img);
	ImageDestroy ($img);
	exit;
}

$k_photo = anti_inj ($_GET['photo']);
$r = db_query ("select pictur from $INFO[db_prefix]photos where k_photo='$k_photo'");
if ( $f = db_fetch_assoc ($r) )
{
	$imgname = "images/$f[pictur]";
	if ( ($size = @getimagesize ($imgname)) === false ) spacer();
	$format = strtolower (substr ($size['mime'], strpos ($size['mime'], '/') + 1));
	$icfunc = "imagecreatefrom" . $format;
	$ifunc = "image" . $format;
	if ( ! function_exists ($icfunc) || ! function_exists ($ifunc) ) spacer();
	$img = $icfunc ($imgname);
	$res = ImageCreateTrueColor ($f_config['sketchwidth'], $f_config['sketchheight']);
	ImageCopyResized ($res, $img, 0, 0, 0, 0, $f_config['sketchwidth'], $f_config['sketchheight'], $size[0], $size[1]);
	@header ("Content-type: image/$format");
	$ifunc ($res);
	flush();
	ImageDestroy ($img);
	ImageDestroy ($res);
} else spacer();

?>