<?php

if ( !defined('CMS') )
{
	die("Нарушение прав доступа");
	exit;
}

if ( $_GET['cmd'] === 'add_pool' )
{
	$body = "<a href='admin.php?action=pool'>Список голосований</a><br /><br />
	<form action='admin.php' method='post' onsubmit='return this.question.value.length>0'>
	<input type='hidden' name='pool' value='ok' />
	Вопрос: <input type='text' maxlength='255' size='44' name='question' /><br />
	Варианты ответов (по одному на каждой строке):<br />
	<textarea name='answer' rows='8' cols='45'></textarea><br />
	Продолжительность опроса (дней): <input type='text' maxlength='6' size='2' name='duration' value='0' /> (0 - бессрочно)<br />
	<input type='submit' value='Добавить' />
	</form>
	";
} else
{
	if ( $_GET['cmd'] === 'view' )
	{
		$r = db_query ("select * from $INFO[db_prefix]pools where k_pl='$_GET[k_pl]'");
		if ( $f = db_fetch_assoc ($r) )
		{
			$co = ( $f['active'] == 1 ) ? 'активно' : 'закрыто';
			$bdt = date ('d.m.Y H:i', $f['begdate']);
			$edt = ( $f['enddate'] == 0 ) ? "бессрочное" : date ('d.m.Y H:i', $f['enddate']);
			$body = "<b><big>$f[question]</big></b><br />
			<table border=1>
			<tr><th>вариант</th><th>голосов</th><th>процент</th></tr>
			";
			$ra = db_query ("select * from $INFO[db_prefix]answers where k_pl='$_GET[k_pl]' order by pos");
			while ($fa = db_fetch_assoc ($ra))
			{
				$pc = ( $f['cnt'] == 0 ) ? 0 : intval (0.5 + ($fa['vote'] * 100) / $f['cnt']);
				$body .= "<tr><td>$fa[answer]</td><td align='center'>$fa[vote]</td><td align='center'>$pc %</td></tr>";
			}
			$body .= "</table><br />
			Всего голосов: <b>$f[cnt]</b><br />
			Голосование <b>$co</b><br />
			Дата начала: <b>$bdt</b><br />
			Дата конца: <b>$edt</b><br />
			";
		}

		$body = "<a href='admin.php?action=pool'>Список голосований</a><br /><br />
		$body
		";
	} else
	{
		$order = 'active desc,begdate desc';
		if ( isset ($_GET['sort']) || isset ($_COOKIE['P_SORT']) )
		{
			$st = isset ($_GET['sort']) ? $_GET['sort'] : $_COOKIE['P_SORT'];
			SetCookie ("P_SORT", $st, time() + 60 * 60 * 24 * 30 * 12);
			if ( $st === 'qus' ) $order = 'question';
			if ( $st === 'cnt' ) $order = 'cnt desc,active desc,begdate desc';
			if ( $st === 'bdt' ) $order = 'begdate desc';
			if ( $st === 'edt' ) $order = 'enddate desc';
		} else $st = 'act';

		$list = '';
		$cnt = 0;
		$cta = 0;
		$r = db_query ("select * from $INFO[db_prefix]pools order by $order");
		while ($f = db_fetch_assoc ($r))
		{
			$cnt++;
			$bdt = date ('d.m.Y H:i', $f['begdate']);
			$edt = ( $f['enddate'] == 0 ) ? "бессрочное" : date ('d.m.Y H:i', $f['enddate']);
			if ( $f['active'] != 0 )
			{
				$act = "активно";
				$nac = "закрыть";
				$o_c = "pool_close";
				$mes = "Закрыть \\\"$f[question]\\\"?";
				$cta++;
			} else
			{
				$act = "закрыто";
				$nac = "активировать";
				$o_c = "pool_open";
				$mes = "Активировать \\\"$f[question]\\\"?";
			}
			$list .= "<tr><td><a href='admin.php?action=pool&cmd=view&k_pl=$f[k_pl]'>$f[question]</a></td>\n";
			$list .= "<td style='text-align:center;'>$bdt</td>\n";
			$list .= "<td style='text-align:center;'>$edt</td>\n";
			$list .= "<td style='text-align:center;color:#800000;'><b>$f[cnt]</b></td>\n";
			$list .= "<td style='text-align:center;color:#800000;'><b>$act</b></td>\n";
			$list .= "<td style='text-align:center;'>
			<a href='admin.php?action=$o_c&k_pl=$f[k_pl]' style='color:blue;' onclick='return confirm(\"$mes\");'>$nac</a></td>\n";
			$mes = "Удалить \\\"$f[question]\\\"?";
			$list .= "<td><a href='admin.php?action=pool_del&amp;k_pl=$f[k_pl]' style='color:blue;' onclick='return confirm(\"$mes\");'>удалить</a></td></tr>\n";		
		}
	
		$sort = "";
		if ( $st === "qus" ) $sort .= "<th>вопрос</th>\n";
		else $sort .= "<th><a href='admin.php?action=pool&amp;sort=qus'>вопрос</a></th>\n";
		if ( $st === "bdt" ) $sort .= "<th>дата начала</th>\n";
		else $sort .= "<th><a href='admin.php?action=pool&amp;sort=bdt'>дата начала</a></th>\n";
		if ( $st === "edt" ) $sort .= "<th>дата конца</th>\n";
		else $sort .= "<th><a href='admin.php?action=pool&amp;sort=edt'>дата конца</a></th>\n";
		if ( $st === "cnt" ) $sort .= "<th>голосов</th>\n";
		else $sort .= "<th><a href='admin.php?action=pool&amp;sort=cnt'>голосов</a></th>\n";
		if ( $st === "act" ) $sort .= "<th>активность</th>\n";
		else $sort .= "<th><a href='admin.php?action=pool&amp;sort=act'>активность</a></th>\n";
		$sort = "<tr>$sort<th>&nbsp;</th><th>&nbsp;</th></tr>";
	
		$body = "<br /><a href='admin.php?action=pool&amp;cmd=add_pool'>Добавить голосование</a><br /><br />
		<table border='1'>
		$sort
		$list</table>
		<br />
		Всего в списке: <b>$cnt</b><br />
		Активных: <b>$cta</b>
		";
	}
}

$body = "<h3>Администрирование - блок голосований</h3>
<a href='admin.php'>Меню</a><br />
$body
";

?>