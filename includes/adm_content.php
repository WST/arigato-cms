<?php

if ( !defined('CMS') )
{
	die("��������� ���� �������");
	exit;
}

function get_art_list ($sp, $pref)
// ��������� ������ ��������:
{
	global $select;
	global $f;
	global $sup;
	global $INFO;
	$rs = db_query ("select * from $INFO[db_prefix]art where sup='$sp' order by pos");
	while ( $fs = db_fetch_assoc ($rs) )
	{
		if ( $_GET['cmd'] === 'add' || $_GET['art'] != $fs['k_art'] )
		{
			$select .= ( $sup == $fs['k_art'] ) ? "<option value='$fs[k_art]' selected />$pref$fs[title]\n"
									: "<option value='$fs[k_art]' />$pref$fs[title]\n";
			get_art_list ($fs['k_art'], $pref . '+ ');
		}
	}
}

function get_art_tree ($sp, $pref)
// ��������� ������ ��������:
{
	global $INFO;
	global $art_num;
	global $art_math_num;
	global $art_no_num;
	$tree = "";
	if ( $sp != 0 )
	{
		$mark = "<img src='includes/images/e2.gif' />";
		$ma = "<img src='includes/images/e1.gif' />";
		$add = "$pref<img src='includes/images/e1.gif' />";
	} else $mark = $add = $ma = "";
	$r = db_query ("select * from $INFO[db_prefix]art where sup='$sp' order by pos");
	while ( $f = db_fetch_assoc ($r) )
	{
		$art_num[] = $f['k_art'];
		$mnem = ( empty ($f['mnemonic']) ) ? "" : " ($f[mnemonic])";
		$tree .= "$pref$mark
		<img src='includes/images/open.png' id='i$f[k_art]' onclick='closeopen($f[k_art]);' /> <a href='admin.php?action=content&amp;cmd=edit&amp;art=$f[k_art]' style='color:black;'>$f[title]$mnem</a> [<font color='#800000'>$f[cnt]</font>]
		&nbsp;(<a href='admin.php?action=content&amp;cmd=down&amp;art=$f[k_art]' title='��������'><b>&#150;</b></a>)
		&nbsp;(<a href='admin.php?action=content&amp;cmd=up&amp;art=$f[k_art]' title='�������'><b>+</b></a>)";
		$tree_s = get_art_tree ($f['k_art'], $add);
		if ( empty ($tree_s) )
		{
			$tree .= "&nbsp; (<a href='admin.php?action=art_del&amp;art=$f[k_art]' onclick='return confirm(\"������� ������ \\\"" .
					str_replace (array ('"', "'"), array (' ', ' '), $f[title]) . "\\\"?\");'>�������</a>)";
			$art_no_num[] = $f['k_art'];
		} else $art_math_num[] = $f['k_art'];
		$tree .= "<br />
		<span style='display:block;' id='$f[k_art]'>$tree_s";
		$tree .= "$pref$ma<img src='includes/images/e3.gif' />
		(<a href='admin.php?action=content&amp;cmd=add&amp;art=$f[k_art]'>��������</a>)<br />
		</span>";
	}
	return $tree;
}

if ( $_GET['cmd'] === 'up' || $_GET['cmd'] === 'down' )
{
	$r = db_query ("select pos,sup from $INFO[db_prefix]art where k_art='$_GET[art]'");
	if ( $f = db_fetch_assoc ($r) )
	{
		$pos = $f['pos'];
		$sup = $f['sup'];
		$r = ($_GET['cmd'] === 'up')
			? db_query ("select k_art,pos from $INFO[db_prefix]art where pos<$pos and sup='$sup' order by pos desc limit 1")
			: db_query ("select k_art,pos from $INFO[db_prefix]art where pos>$pos and sup='$sup' order by pos limit 1");
		if ($f = db_fetch_assoc ($r) )
		{
			db_query ("update $INFO[db_prefix]art set pos='$pos' where k_art='$f[k_art]'");
			db_query ("update $INFO[db_prefix]art set pos='$f[pos]' where k_art='$_GET[art]'");
		}
	}
	header ("location:admin.php?action=content");
	exit;
}

$tr_bl = true;

if ( $_GET['cmd'] === 'edit' || $_GET['cmd'] === 'add' )
{
	$an0 = $an1 = $an2 = $an3 = $dy0 = $dy1 = $dy2 = $dy3 = $fo1 = $fo2 = $fo3 = $pn1 = $pn2 = $pn3 = "";	
	if ( $_GET['cmd'] === 'edit' )
	{
		$r = db_query ("select * from $INFO[db_prefix]art where k_art='$_GET[art]'");
		$f = db_fetch_assoc ($r);
		$sup = $f['sup'];
		switch ( $f['dynamic'] )
		{
			case 0: $dy0 = " selected"; break;
			case 1: $dy1 = " selected"; break;
			case 2: $dy2 = " selected"; break;
			case 3: $dy3 = " selected"; break;
		}
		switch ( $f['cont'] )
		{
			case 0: $an0 = " selected"; break;
			case 1: $an1 = " selected"; break;
			case 2: $an2 = " selected"; break;
			case 3: $an3 = " selected"; break;
		}
		switch ( $f['p_n'] )
		{
			case 0: $pn1 = " checked"; break;
			case 1: $pn2 = " checked"; break;
			case 2: $pn3 = " checked"; break;
		}
		switch ( $f['format'] )
		{
			case 0: $fo1 = " checked"; $disp = "none"; break;
			case 1: $fo2 = " checked"; $disp = "block"; break;
			case 2: $fo3 = " checked"; $disp = "none"; break;
		}
		$path = ($f['path'] == 1) ? " checked" : "";
		$inmenu = ($f['inmenu'] == 1) ? " checked" : "";
		$text = protection ($f['post']);
		$shot = protection ($f['shot']);
		$shotcap = protection ($f['title']);
		$caption = protection ($f['caption']);
		$author = protection ($f['author']);
		$mnemonic = protection ($f['mnemonic']);
		$keywords = protection ($f['keywords']);
		$comment = protection ($f['comment']);
		$icon = protection ($f['icon']);
		$submit = "��������";
	} else
	{
		$disp = "none";
		$path = ( isset ($_COOKIE['PATH']) && $_COOKIE['PATH'] == 1 || ! isset ($_COOKIE['PATH'])) ? " checked" : "";
		$inmenu = ( isset ($_COOKIE['INMENU']) && $_COOKIE['INMENU'] == 1 || ! isset ($_COOKIE['INMENU'])) ? " checked" : "";
		if ( isset ($_COOKIE['DYNAMIC']) )
		{
			switch ( $_COOKIE['DYNAMIC'] )
			{
				case 0: $dy0 = " selected"; break;
				case 1: $dy1 = " selected"; break;
				case 2: $dy2 = " selected"; break;
				case 3: $dy3 = " selected"; break;
			}
		} else $dy0 = " selected";
		if ( isset ($_COOKIE['ANSWER']) )
		{
			switch ( $_COOKIE['ANSWER'] )
			{
				case 0: $an0 = " selected"; break;
				case 1: $an1 = " selected"; break;
				case 2: $an2 = " selected"; break;
				case 3: $an3 = " selected"; break;
			}
		} else $an0 = " selected";
		if ( isset ($_COOKIE['P_N']) )
		{
			switch ( $_COOKIE['P_N'] )
			{
				case 0: $pn1 = " checked"; break;
				case 1: $pn2 = " checked"; break;
				case 2: $pn3 = " checked"; break;						
			}
		} else $pn1 = " checked";
		if ( isset ($_COOKIE['FORMAT']) )
		{
			switch ( $_COOKIE['FORMAT'] )
			{
				case 0: $fo1 = " checked"; $disp = "none"; break;
				case 1: $fo2 = " checked"; $disp = "block"; break;
				case 2: $fo3 = " checked"; $disp = "none"; break;
			}
		} else
		{
			$fo1 = " checked";
			$disp = "none";
		}
		$pos0 = $pos1 = "";
		if ( isset ($_COOKIE['POS']) )
		{
			switch ( $_COOKIE['POS'] )
			{
				case 0: $pos0 = " selected"; break;
				case 1: $pos1 = " selected"; break;
			}
		} else $pos1 = " selected";
		$text = $shot = $shotcap = $caption = $author = $mnemonic = $keywords = $comment = $icon = "";
		$submit = "��������";
	}
	$body = "
	<a href=admin.php>����</a><br />
	<a href=admin.php?action=content>������ ��������</a><br><br />
	<script type='text/javascript'><!--
	function storeCaret(text) 
	{if (text.createTextRange) text.caretPos=document.selection.createRange().duplicate();}
	function addtag(s)
	{	if (document.post.post.createTextRange&&document.post.post.caretPos) 
		{	var cp=document.post.post.caretPos;      
			cp.text=s;
		}else document.post.post.value+=s;
		document.post.post.focus(cp);
	}
	//-->
	</script>
	<form method='post' action='admin.php' name='post'>";
	if ( $_GET['cmd'] === 'edit' )
	{
		$body .= "<b>������������� ������:</b>
		<input type='hidden' name='edit' value='ok' />
		<input type='hidden' name='art' value='$f[k_art]' />";
	} else
	{
		$body .= "<b>�������� ������:</b>
		<select name='position'>
		<option value='0'$pos0 />� ������
		<option value='1'$pos1 />� �����
		</select>
		<input type='hidden' name='add' value='ok' />";
		$sup = $_GET['art'];
	}

	$select = ( $sup == 0 ) ? "<option value='0' selected />-������-\n" : "<option value='0' />-������-\n";
	get_art_list (0, '');

	$body .= "&nbsp;<input type='submit' value='$submit' />
	<table><tr>
	<td>���������:</td>
	<td><input type='text' name='shotcap' maxlength='255' size='40' value='$shotcap' /></td><td>(������, ��� ������ ����)</td>
	</tr><tr>
	<td>��������:</td>
	<td><input type='text' name='caption' maxlength='255' size='40' value='$caption' /></td><td>(������ �������� �������)</td>
	</tr><tr>
	<td>�����:</td>
	<td><input type='text' name='author' maxlength='255' size='40' value='$author' /></td><td>(����� ������)</td>
	</tr><tr>
	<td>���������:</td>
	<td><input type='text' name='mnemonic' maxlength='255' size='40' value='$mnemonic' /></td><td>(������������� �������)</td>
	</tr><tr>
	<td>�������� �����:</td>
	<td><input type='text' name='keywords' maxlength='65535' size='40' value='$keywords' /></td><td>(��� meta-���� keywords)</td>
	</tr><tr>
	<td>������:</td>
	<td><input type='text' name='icon' maxlength='255' size='40' value='$icon' /></td><td>(����, ������������ ����� images)</td>
	</tr><tr>
	<td>������:</td>
	<td><select name='sup'>
	$select
	</select></td><td>(������������ ������)</td>
	</tr><tr>
	<td>���������� 1:</td>
	<td>
	<select name='dynamic'>
	<option value='0'$dy0 />���
	<option value='1'$dy1 />������ �����������
	<option value='2'$dy2 />����
	<option value='3'$dy3 />����������
	</select>
	</td><td>(������������ ���� �������)</td>
	</tr><tr>
	<td>���������� 2:</td>
	<td>
	<select name='answer'>
	<option value='0'$an0 />���
	<option value='1'$an1 />�����������
	<option value='3'$an3 />�������
	<option value='2'$an2 />������� ����������
	</select>
	</td><td>(���� � ���������� �� ��������)</td>
	</tr></table>
	�������� (������� ����� ��� html):<br />
	<textarea name='shot' rows='5' cols='104'>$shot</textarea><br />
	�����:<br />
	<div id='cap' style='display:$disp;'>
	<input type=button value='B' style='font-weight:bold;height:24px;width:32px' title='������' onclick=\"addtag('[b][/b]');\" />
	<input type=button value=' i' style='font-style:italic;height:24px;width:32px' title='������' onclick=\"addtag('[i][/i]');\" />
	<input type=button value=' u ' style='text-decoration:underline;height:24px;width:32px' title='������������' onclick=\"addtag('[u][/u]');\" />
	<input type=button value=' S ' style='text-decoration:line-through;height:24px;width:32px' title='�������������' onclick=\"addtag('[s][/s]');\" />
	<input type=button value=' o ' style='text-decoration:overline;:line-through;height:24px;width:32px' title='������������' onclick=\"addtag('[o][/o]');\" />
	<input type=button value='sup' style='height:24px;width:32px' title='������� ������' onclick=\"addtag('[sup][/sup]');\" />
	<input type=button value='sub' style='height:24px;width:32px' title='������ ������' onclick=\"addtag('[sub][/sub]');\" />
	<input type=button value='center' style='font-size:7pt;height:24px;width:32px' title='�� ������' onclick=\"addtag('[center][/center]');\" />
	<input type=button value='right' style='font-size:7pt;height:24px;width:32px' title='�� ������� ����' onclick=\"addtag('[right][/right]');\" />
	<input type=button value='color' style='font-size:7pt;height:24px;width:32px;color:red' title='����' onclick=\"addtag('[color][/color]');\" />
	<input type=button value='size' style='height:24px;width:32px;' title='������' onclick=\"addtag('[size][/size]');\" />
	<input type=button value='url' style='text-decoration:underline;color:blue;height:24px;width:32px;' title='������' onclick=\"addtag('[url][/url]');\" />
	<input type=button value='img' style='height:24px;width:32px;' title='��������' onclick=\"addtag('[img][/img]');\" />
	<input type=button value='quote' style='font-size:7pt;height:24px;width:32px' title='������' onclick=\"addtag('[quote][/quote]');\" />
	<input type=button value='code' style='font-size:7pt;height:24px;width:32px' title='�������� ���' onclick=\"addtag('[code][/code]');\" />
	<input type=button value='no' style='height:24px;width:32px;' title='��� bb-�����' onclick=\"addtag('[no][/no]');\" />
	<input type=button value='_hr_' style='font-size:7pt;height:24px;width:32px;' title='�������������� �����' onclick=\"addtag('[hr]');\" />
	<input type=button value='html' style='height:24px;width:32px;' title='HTML-��������' onclick=\"addtag('[html][/html]');\" />
	</div>
	<textarea name='post' onclick='storeCaret(this);' onkeyup='storeCaret(this);' onchange='storeCaret(this);' rows='20' cols='104'>$text</textarea><br />
	<table><tr>
	<td><input type='radio' class='radio' name='format' value='1'$fo2 onclick='javascript:document.getElementById(\"cap\").style.display=\"block\";' /> ������� �����</td>
	<td><input type='radio' class='radio' name='format' value='0'$fo1 onclick='javascript:document.getElementById(\"cap\").style.display=\"none\";' /> HTML</td>
	<td><input type='radio' class='radio' name='format' value='2'$fo3 onclick='javascript:document.getElementById(\"cap\").style.display=\"none\";' /> PHP</td>
	</tr><tr>
	<td><input type='radio' class='radio' name='p_n' value='0'$pn1 /> ��� ������</td>
	<td><input type='radio' class='radio' name='p_n' value='1'$pn2 /> ������ �� �������� �������</td>
	<td><input type='radio' class='radio' name='p_n' value='2'$pn3 /> �������� ����������</td>
	</tr><tr>
	<td><input type='checkbox' class='radio' name='path' value='1'$path /> ����� ���� �� �������</td>
	<td><input type='checkbox' class='radio' name='inmenu' value='1'$inmenu /> ���������� ������ � ������� ����</td>
	<td>&nbsp;</td>
	</table>
	<a href='#' onclick='document.getElementById(\"rem\").style.display=(document.getElementById(\"rem\").style.display==\"none\")?\"block\":\"none\";return false;'>
	������� �������������� � �������</a>
	<span style='display:block;' id='rem'> (����� ������ �������������)<br />
	<textarea name='comment' rows='5' cols='100'>$comment</textarea><br />
	</span><br />
	<br /><input type='submit' value='$submit' />
	</form>
	<script type='text/javascript'><!--
	document.getElementById('rem').style.display='none';
	//--></script>
	";
	$tr_bl = false;
}

if ( $tr_bl === true )
{
	$art_num = $art_math_num = $art_no_num = array();
	$tree = get_art_tree (0, "") . "(<a href='admin.php?action=content&amp;cmd=add&amp;pos=down&amp;art=0'>��������</a>)<br />";
	$r = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]art");
	$f = db_fetch_assoc ($r);
	$cnt_art = $f['CNT'];
	$artall = join (",", $art_num);
	$artmathall = join (",", $art_math_num);
	$artnoall = join (",", $art_no_num);
	$body = "
	<style type='text/css'><!--
	img {vertical-align: middle;}
	//--></style>
	<script type='text/javascript'><!--
	art=new Array($artall);
	artm=new Array($artmathall);
	artn=new Array($artnoall);
	function change_img(id,rpath,alt)
	{	var img=document.getElementById(id);
		img.altsrc=img.src;
		img.src=rpath;
		img.alt=alt;
		img.title='';
	}
	function closeopen(id)
	{	if (document.getElementById(id).style.display=='none')
		{	document.getElementById(id).style.display='block';
			change_img('i'+id.toString(),'includes/images/open.png','-');
		} else
		{	document.getElementById(id).style.display='none';
			change_img('i'+id.toString(),'includes/images/close.png','+');
		}
	}
	function openall(a)
	{	for (var i=0;i<a.length;i++)
		{	document.getElementById(a[i]).style.display='block';
			change_img('i'+a[i].toString(),'includes/images/open.png','-');
		}
	}
	function closeall(a)
	{	for (var i=0;i<a.length;i++)
		{	document.getElementById(a[i]).style.display='none';
			change_img('i'+a[i].toString(),'includes/images/close.png','+');
		}
	}
	//--></script>
	<a href='admin.php'>����</a><br /><br />
	<b>������ ��������:</b> (<a href='#' onclick='closeall(artn);openall(artm);'>�������� ��������</a>) (<a href='#' onclick='openall(art);'>�������� ���</a>) (<a href='#' onclick='closeall(artn);'>�������� ������</a>) (<a href='#' onclick='closeall(art);'>�������� ���</a>)
	<br />
	$tree
	<br />����� ��������: <b>$cnt_art</b>
	<script type='text/javascript'><!--
	closeall(artn);
	//--></script>";
}

$body = "<h3>����������������� - ���������� ��������� �����</h3>" . $body;

?>