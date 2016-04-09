<?php

define ('CMS', true);

require_once "includes/utils.php";

no_cache();
$pst = ( isset ($_POST['your_message']) ) ? substr (trim ($_POST['your_message']), 0, $f_config['meslen']) : "";
if ( get_magic_quotes_gpc () ) $pst = stripslashes ($pst);
if ( ! $admin ) $pst = str_replace ('[html]', '[no][html][/no]', $pst);
$rcn = db_query ("select * from $INFO[db_prefix]censor");
while ($fcn = db_fetch_assoc ($rcn)) $pst = str_replace ($fcn['bad'], $fcn['good'], $pst);
$pst = format_text ($pst);
$tag = $global_tag;
$tag['TEXT'] = $pst;
echo tpl (join ('', file ("$f_config[skin]/patterns/preview.html")), $tag);

?>