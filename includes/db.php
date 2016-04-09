<?php

if ( ! defined ('CMS') )
{
	die ("Нарушение прав доступа");
	exit;
}

require_once "conf.php";

$INFO['db_time'] = 0;
$INFO['db_querys'] = 0;

function db_error ($query = '')
// Обработка ошибок запросов к базе данных:
{
	die (mysql_error() . "<br />$query");
}

function db_connect()
// Подключение к базе данных:
{
	global $INFO;
	$time = gettickcount();
	@mysql_pconnect ($INFO['db_server'], $INFO['db_login'], $INFO['db_password']) or db_error();
	@mysql_select_db ($INFO['db_database']) or db_error();
	$INFO['db_time'] += gettickcount() - $time;
}

function db_query ($query)
// Запрос к базе данных:
{
	global $INFO;
	$time = gettickcount();
	$res = @mysql_query ($query) or db_error ($query);
	$INFO['db_time'] += gettickcount() - $time;
	$INFO['db_querys']++;
	return $res;
}

function db_free_result ($result)
// Очистка результата запроса:
{
	global $INFO;
	$time = gettickcount();
	@mysql_free_result ($result) or db_error();
	$INFO['db_time'] += gettickcount() - $time;
}

function db_fetch_assoc ($result)
// Получение результата:
{
	return mysql_fetch_assoc ($result);
}

function db_fetch_row ($result)
// Получение результата:
{
	return mysql_fetch_row ($result);
}

function db_num_rows ($result)
// Получение количества записей:
{
	return mysql_num_rows ($result);
}

function db_insert_id()
// Получение первичного ключа последней записи:
{
	return mysql_insert_id();
}

function db_affected_rows()
// Получение количества обновленных записей:
{
	return mysql_affected_rows();
}

?>