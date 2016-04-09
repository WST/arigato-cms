<?php

define ('CMS', true);

require_once "includes/utils.php";

if ( isset ($_GET['file']) )
{
	$file = protection ($_GET['file']);
	if ( strpos ($file, "\\") === false && strpos ($file, "..") === false && file_exists ("files/$file") )
	{
		$cnttag = $global_tag;
		$rcnt = db_query ("select cnt,lastdate,ip from $INFO[db_prefix]dlcnt where file='$file'");
		if ( $fcnt = db_fetch_assoc ($rcnt) )
		{
			$cnttag['COUNT'] = $fcnt['cnt'];
			$cnttag['IP'] = $fcnt['ip'];
			$cnttag['DATE'] = date ($f_config['datefrm'], $fcnt['lastdate']);
		} else $cnttag['COUNT'] = 0;
		$cnttag['FILE'] = $file;
		$cnttag['SIZE'] = get_size ("files/$file");
		$fileinfo = stat ("files/$file");
		$creat = ( $fileinfo["mtime"] > $fileinfo["ctime"] ) ? $fileinfo["mtime"] : $fileinfo["ctime"];
		$cnttag['CREATING'] = date ($f_config['datefrm'], $creat);
		error (tpl ($f_config['inform7'], $cnttag), tpl ($f_config['inform_cap'], $global_tag));
	} else error (tpl ($f_config['error11'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
}

if ( isset ($_POST['file']) )
{
	$file = protection ($_POST['file']);
	if ( strpos ($file, "\\") === false && strpos ($file, "..") === false && file_exists ("files/$file") )
	{
		$rcnt = db_query ("select cnt from $INFO[db_prefix]dlcnt where file='$file'");
		if ( $fcnt = db_fetch_assoc ($rcnt) )
		{
			db_query ("update $INFO[db_prefix]dlcnt set cnt=" . ($fcnt['cnt'] + 1) . ",lastdate=" . time() . ",
					ip='$_SERVER[REMOTE_ADDR]' where file='$file'");
		} else db_query ("insert into $INFO[db_prefix]dlcnt (file,cnt,lastdate,ip)
					values ('$file',1," . time() . ",'$_SERVER[REMOTE_ADDR]')");
		header ("location: files/$file");
	} else error (tpl ($f_config['error11'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
} else error (tpl ($f_config['error11'], $global_tag), tpl ($f_config['error_cap'], $global_tag));

?>