<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

$cnt = 0;
$list = '';
$r = db_query ("select * from $INFO[db_prefix]counter where reftime>" . (time() - $f_config['stat'] * 60) . " order by reftime desc");
while ($f = db_fetch_assoc ($r))
{
	$dt = date ('d.m.Y H:i', $f['reftime']);
	$list .= "<tr><td>IP: $f[ip]</td>\n";
	$list .= "<td>$dt</td>\n";
	$list .= "<td><a href='$f[url]' target='_blank'>" . url_format ($f['url']) . "</a></td>\n";
	$list .= "<td>$f[agent]</td></tr>\n";
	$cnt++;
}
$body = "<h3>Администрирование - кто на сайте</h3>
<a href='admin.php'>Меню</a><br /><br />
<table border='1'>
$list</table>
<br />
Всего в списке: <b>$cnt</b>
";

?>