<?php

if ( ! defined ('CMS') )
{
	die ("��������� ���� �������");
	exit;
}

require_once "conf.php";

$INFO['db_time'] = 0;
$INFO['db_querys'] = 0;

function db_error ($query = '')
// ��������� ������ �������� � ���� ������:
{
	die (mysql_error() . "<br />$query");
}

function db_connect()
// ����������� � ���� ������:
{
	global $INFO;
	$time = gettickcount();
	@mysql_pconnect ($INFO['db_server'], $INFO['db_login'], $INFO['db_password']) or db_error();
	@mysql_select_db ($INFO['db_database']) or db_error();
	$INFO['db_time'] += gettickcount() - $time;
}

function db_query ($query)
// ������ � ���� ������:
{
	global $INFO;
	$time = gettickcount();
	$res = @mysql_query ($query) or db_error ($query);
	$INFO['db_time'] += gettickcount() - $time;
	$INFO['db_querys']++;
	return $res;
}

function db_free_result ($result)
// ������� ���������� �������:
{
	global $INFO;
	$time = gettickcount();
	@mysql_free_result ($result) or db_error();
	$INFO['db_time'] += gettickcount() - $time;
}

function db_fetch_assoc ($result)
// ��������� ����������:
{
	return mysql_fetch_assoc ($result);
}

function db_fetch_row ($result)
// ��������� ����������:
{
	return mysql_fetch_row ($result);
}

function db_num_rows ($result)
// ��������� ���������� �������:
{
	return mysql_num_rows ($result);
}

function db_insert_id()
// ��������� ���������� ����� ��������� ������:
{
	return mysql_insert_id();
}

function db_affected_rows()
// ��������� ���������� ����������� �������:
{
	return mysql_affected_rows();
}

?>