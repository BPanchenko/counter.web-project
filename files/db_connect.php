<?
@mysql_close();

define("_DB_CONNECT_DATA_HOSTNAME", "******");
define("_DB_CONNECT_DATA_USERNAME", "******");
define("_DB_CONNECT_DATA_PSW", "******");
define("_DB_CONNECT_DATA_DBNAME", "******");

mysql_connect(_DB_CONNECT_DATA_HOSTNAME,_DB_CONNECT_DATA_USERNAME,_DB_CONNECT_DATA_PSW)
			or die("Не могу создать соединение. Ответ сервера: ".mysql_error());
mysql_select_db(_DB_CONNECT_DATA_DBNAME)
			or die("Не удаётся подключиться к базе данных. Ответ сервера: ".mysql_error());
mysql_query("SET CHARACTER SET 'utf8'");
mysql_query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
?>