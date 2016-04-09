<?php

define ('CMS', true);

require_once "includes/utils.php";

$num = 0;
$sid = anti_inj ($_GET['sid']);
$r = db_query ("select num from $INFO[db_prefix]nums where sid='$sid'");
$num = ( $f = db_fetch_assoc ($r) ) ? $f['num'] : false;

db_query ("delete from $INFO[db_prefix]nums where stime<" . (time() - 60 * 60));

if ( $num !== false )
{
	$numimg = ImageCreate (60, 16);
	$white = ImageColorAllocate ($numimg, 255, 255, 255);
	$black = ImageColorAllocate ($numimg, 0, 0, 0);

	ImageString ($numimg, 5, 3, 0, $num, $black);

	// Шум:
	mt_srand (intval ($num));
	$sx = ImageSX ($numimg) - 1;
	$sy = ImageSY ($numimg) - 1;
	for ($i = 0; $i < 32; $i++)
	{ // Белые точки:
		$x = mt_rand (0, $sx);
		$y = mt_rand (0, $sy);
		ImageSetPixel ($numimg, $x, $y, $white);
	}
	for ($i = 0; $i < 48; $i++)
	{ // Черные точки:
		$x = mt_rand (0, $sx);
		$y = mt_rand (0, $sy);
		ImageSetPixel ($numimg, $x, $y, $black);
	}

	header ("Content-type: image/png");
	ImagePng ($numimg);
	ImageDestroy ($numimg);
}

?>