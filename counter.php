<?
if (!count($_POST)) : // если на страницу отправленны данные методом POST, то посещение не фиксируем

require_once('files/db_connect.php');
require_once('files/functions.php');

// определяем текущие дату и месяц
$DAY = date("Y-m-d");
$MOTH = date("Y-m");

$IP = protostats_getipint();

// сразу проверяем не бот ли к нам пришел
$sql = mysql_query("SELECT * FROM `proto_stats_bot` ORDER BY `id` ASC");
while ($row = mysql_fetch_array($sql, MYSQL_ASSOC)) if ($row['id']) {
	if (strpos($_SERVER['HTTP_USER_AGENT'],$row["name"])!==false) {
		mysql_free_result($sql);
		$BOT_ID = $row['id'];
		if ($row['ip1']!=$IP && $row['ip2']!=$IP && $row['ip3']!=$IP) {
			if (!$row['ip1'])		mysql_query("UPDATE `proto_stats_bot` SET `ip1`=".$IP." WHERE `id`=".$BOT_ID." LIMIT 1;");
			elseif (!$row['ip2'])	mysql_query("UPDATE `proto_stats_bot` SET `ip2`=".$IP." WHERE `id`=".$BOT_ID." LIMIT 1;");
			elseif (!$row['ip3'])	mysql_query("UPDATE `proto_stats_bot` SET `ip3`=".$IP." WHERE `id`=".$BOT_ID." LIMIT 1;");
			elseif (!$row['ip4'])	mysql_query("UPDATE `proto_stats_bot` SET `ip4`=".$IP." WHERE `id`=".$BOT_ID." LIMIT 1;");
			else					mysql_query("UPDATE `proto_stats_bot` SET `ip5`=".$IP." WHERE `id`=".$BOT_ID." LIMIT 1;");
		}
		
	}
}
if ($BOT_ID) :
	if (mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_bot_day` WHERE `bot_id`=".$BOT_ID." AND `date` LIKE '".$DAY."%' LIMIT 1"))) {
		mysql_query("UPDATE `proto_stats_bot_day` SET `hit`=`hit`+1 WHERE `bot_id`=".$BOT_ID." AND `date` LIKE '".$DAY."%' LIMIT 1;");
	} else {
		mysql_query("INSERT INTO `proto_stats_bot_day` (`bot_id`, `hit`) VALUES (".$BOT_ID.", 1);");
	}
else :

// работаем с ip пользователя и фиксируем статистику по дням и месяцам
$is_host_day = false;
$is_host_moth = false;
$sql = mysql_query("SELECT `id` FROM `proto_stats_ip_day` WHERE `ip`=".$IP." AND `date` LIKE '".$DAY."%' LIMIT 1");
$is_ip_day = mysql_num_rows($sql);
$is_rows_day = mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_day` WHERE `date` LIKE '".$DAY."%' LIMIT 1"));
if (!$is_ip_day) {
	if (!$is_rows_day) mysql_query("INSERT INTO `proto_stats_day` (`hit`, `host`) VALUES (1, 1);");
	else mysql_query("UPDATE `proto_stats_day` SET `hit`=`hit`+1, `host`=`host`+1 WHERE `date` LIKE '".$DAY."%' LIMIT 1;");
	mysql_query("INSERT INTO `proto_stats_ip_day` (`ip`) VALUES (".$IP.");");
	$IP_DAY_ID = mysql_insert_id();
	$is_host_day = true; // уникальный для текущего дня пользователь
} else {
	mysql_query("UPDATE `proto_stats_day` SET `hit`=`hit`+1 WHERE `date` LIKE '".$DAY."%' LIMIT 1;");
	$row = mysql_fetch_array($sql, MYSQL_ASSOC);
	$IP_DAY_ID = $row['id'];
}
$is_ip_moth = mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_ip_moth` WHERE `ip`=".$IP." AND `date` LIKE '".$MOTH."%' LIMIT 1"));
$is_rows_moth = mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_moth` WHERE `date` LIKE '".$MOTH."%' LIMIT 1"));
if (!$is_ip_moth) {
	if (!$is_rows_moth) mysql_query("INSERT INTO `proto_stats_moth` (`hit`, `host`) VALUES (1, 1);");
	else mysql_query("UPDATE `proto_stats_moth` SET `hit`=`hit`+1, `host`=`host`+1 WHERE `date` LIKE '".$MOTH."%' LIMIT 1;");
	mysql_query("INSERT INTO `proto_stats_ip_moth` (`ip`) VALUES (".$IP.");");
	$is_host_moth = true; // уникальный для текущего месяца пользователь
} else mysql_query("UPDATE `proto_stats_moth` SET `hit`=`hit`+1 WHERE `date` LIKE '".$MOTH."%' LIMIT 1;");
$is_ip_all = mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_ip_all` WHERE `ip`=".$IP." LIMIT 1"));
if ($is_ip_all) {
	mysql_query("UPDATE `proto_stats_ip_all` SET `hit`=`hit`+1, `date_last_hit`='".date("Y-m-d H:i:s")."' WHERE `ip`=".$IP." LIMIT 1");
	$is_host_all = true; // уникальный пользователь за весь период существования сайта
} else mysql_query("INSERT INTO `proto_stats_ip_all` (`ip`,`hit`) VALUES (".$IP.",1);");

// работаем с адресом посещенной страницы
$URL = $_SERVER['REQUEST_URI'];
$sql = mysql_query("SELECT `id` FROM `proto_stats_url_in` WHERE `url`='".$URL."' LIMIT 1");
if (mysql_num_rows($sql)) {
	$row = mysql_fetch_array($sql, MYSQL_ASSOC);
	$URL_ID = $row['id'];
} else {
	mysql_query("INSERT INTO `proto_stats_url_in` (`url`) VALUES ('".$URL."');");
	$URL_ID = mysql_insert_id();
}
mysql_free_result($sql);
if ($URL_ID && mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_url_in_day` WHERE `url_id`=".$URL_ID." AND `date` LIKE '".$DAY."%' LIMIT 1"))) {
	if ($is_host_day) mysql_query("UPDATE `proto_stats_url_in_day` SET `hit`=`hit`+1, `host`=`host`+1 WHERE `url_id`=".$URL_ID." AND `date` LIKE '".$DAY."%' LIMIT 1;");
	else mysql_query("UPDATE `proto_stats_url_in_day` SET `hit`=`hit`+1 WHERE `url_id`=".$URL_ID." AND `date` LIKE '".$DAY."%' LIMIT 1;");
} elseif ($URL_ID) {
	if ($is_host_day) mysql_query("INSERT INTO `proto_stats_url_in_day` (`url_id`, `host`, `hit`) VALUES (".$URL_ID.",1, 1);");
	else mysql_query("INSERT INTO `proto_stats_url_in_day` (`url_id`, `host`, `hit`) VALUES (".$URL_ID.",0, 1);");
}
unset($URL);

// ВАЖНО!!! Статистика ведется только для уникальных посетителей за месяц!
// география посещений
$sql = mysql_query("SELECT `id` FROM `proto_dir_geo_ip` WHERE `ip1`<=".$IP." AND `ip2`>=".$IP." LIMIT 1");
$row = mysql_fetch_array($sql, MYSQL_ASSOC);
mysql_num_rows($sql) ? $GEO_ID = $row['id'] : $GEO_ID = 0;
if (mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_geo_moth` WHERE `geo_ip_id`=".$GEO_ID." AND `date` LIKE '".$MOTH."%' LIMIT 1"))) {
	if ($is_host_moth) mysql_query("UPDATE `proto_stats_geo_moth` SET `hit`=`hit`+1, `host`=`host`+1 WHERE `geo_ip_id`=".$GEO_ID." AND `date` LIKE '".$MOTH."%' LIMIT 1;");
	else mysql_query("UPDATE `proto_stats_geo_moth` SET `hit`=`hit`+1 WHERE `geo_ip_id`=".$GEO_ID." AND `date` LIKE '".$MOTH."%' LIMIT 1;");
} else {
	if ($is_host_moth) mysql_query("INSERT INTO `proto_stats_geo_moth` (`geo_ip_id`, `host`, `hit`) VALUES (".$GEO_ID.",1, 1);");
	else mysql_query("INSERT INTO `proto_stats_geo_moth` (`geo_ip_id`, `host`, `hit`) VALUES (".$GEO_ID.",0, 1);");
}
mysql_free_result($sql);

// статистика по браузерам
if ($is_host_moth) :
$AGENT = $_SERVER['HTTP_USER_AGENT'];
if ($AGENT) $sql = mysql_query("SELECT `id` FROM `proto_stats_agent` WHERE `agent`='".$AGENT."' LIMIT 1");
if (mysql_num_rows($sql)) {
	$row = mysql_fetch_array($sql, MYSQL_ASSOC);
	$AGENT_ID = $row['id'];
} elseif ($AGENT) {
	mysql_query("INSERT INTO `proto_stats_agent` (`agent`) VALUES ('".mysql_real_escape_string($AGENT)."');");
	$AGENT_ID = mysql_insert_id();
}
mysql_free_result($sql);
if ($AGENT_ID && mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_agent_moth` WHERE `agent_id`=".$AGENT_ID." AND `date` LIKE '".$MOTH."%' LIMIT 1"))) {
	mysql_query("UPDATE `proto_stats_agent_moth` SET `host`=`host`+1 WHERE `agent_id`=".$AGENT_ID." AND `date` LIKE '".$MOTH."%' LIMIT 1;");
} else {
	mysql_query("INSERT INTO `proto_stats_agent_moth` (`agent_id`, `host`) VALUES (".$AGENT_ID.",1);");
}
endif; // конец проверки на уникального прользователя в месяц

// фиксируем адрес, откуда пришел пользователь
$URL = parse_url($_SERVER['HTTP_REFERER']);
$URL['host'] = str_replace("www.","",$URL['host']);

if ($URL['host'] && strpos($_SERVER['SERVER_NAME'],$URL['host'])===false) :

$sql = mysql_query("SELECT `id`,`pattern` FROM `proto_stats_searcher_site` WHERE `url`='".$URL['host']."' LIMIT 1");
if (mysql_num_rows($sql)) :
// посещение со страницы поисковика, фиксируем поисковой запрос
$row = mysql_fetch_array($sql, MYSQL_ASSOC);
$SEARCHER_ID = $row['id'];
$PATTERN = $row['pattern'];
preg_match($PATTERN, $URL['query'], $out);
$PHRASE = trim(urldecode($out[1]));
if ($PHRASE) {
	$sql = mysql_query("SELECT `id` FROM `proto_stats_searcher_phrase` WHERE `text`='".$PHRASE."' LIMIT 1");
	if (mysql_num_rows($sql)) {
		$row = mysql_fetch_array($sql, MYSQL_ASSOC);
		$PHRASE_ID = $row['id'];
	} else {
		mysql_query("INSERT INTO `proto_stats_searcher_phrase` (`text`) VALUES ('".$PHRASE."');");
		$PHRASE_ID = mysql_insert_id();
	}
	mysql_free_result($sql);
	if (mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_searcher` WHERE `searcher_id`=".$SEARCHER_ID." AND `phrase_id`=".$PHRASE_ID." AND `date` LIKE '".$DAY."%' LIMIT 1"))) {
		if ($is_host_moth) mysql_query("UPDATE `proto_stats_searcher` SET `hit`=`hit`+1, `host`=`host`+1 WHERE `searcher_id`=".$SEARCHER_ID." AND `phrase_id`=".$PHRASE_ID." AND `date` LIKE '".$DAY."%' LIMIT 1;");
		else mysql_query("UPDATE `proto_stats_searcher` SET `hit`=`hit`+1 WHERE `searcher_id`=".$SEARCHER_ID." AND `phrase_id`=".$PHRASE_ID." AND `date` LIKE '".$DAY."%' LIMIT 1;");
	} else {
		if ($is_host_moth) mysql_query("INSERT INTO `proto_stats_searcher` (`searcher_id`, `phrase_id`, `url_id`, `host`, `hit`) VALUES (".$SEARCHER_ID.",".$PHRASE_ID.",".$URL_ID.",1, 1);");
		else mysql_query("INSERT INTO `proto_stats_searcher` (`searcher_id`, `phrase_id`, `url_id`, `host`, `hit`) VALUES (".$SEARCHER_ID.",".$PHRASE_ID.",".$URL_ID.",0, 1);");
	}
} else {  };


elseif(strpos($_SERVER['SERVER_NAME'],$URL['host'])===false):

// посещение со страницы других сайтов
$sql = mysql_query("SELECT `id` FROM `proto_stats_url_from` WHERE `url`='".$URL['host']."' LIMIT 1");
if (mysql_num_rows($sql)) {
	$row = mysql_fetch_array($sql, MYSQL_ASSOC);
	$URL_FROM_ID = $row['id'];
} else {
	mysql_query("INSERT INTO `proto_stats_url_from` (`url`) VALUES ('".$URL['host']."');");
	$URL_FROM_ID = mysql_insert_id();
}
mysql_free_result($sql);
if (mysql_num_rows(mysql_query("SELECT `id` FROM `proto_stats_url_from_day` WHERE `url_from_id`=".$URL_FROM_ID." AND `date` LIKE '".$DAY."%' LIMIT 1"))) {
	if ($is_host_day) mysql_query("UPDATE `proto_stats_url_from_day` SET `hit`=`hit`+1, `host`=`host`+1 WHERE `url_from_id`=".$URL_FROM_ID." AND `date` LIKE '".$DAY."%' LIMIT 1;");
	else mysql_query("UPDATE `proto_stats_url_from_day` SET `hit`=`hit`+1 WHERE `url_from_id`=".$URL_FROM_ID." AND `date` LIKE '".$DAY."%' LIMIT 1;");
} else {
	if ($is_host_day) mysql_query("INSERT INTO `proto_stats_url_from_day` (`url_from_id`, `url_in_id`, `hit`, `host`) VALUES (".$URL_FROM_ID.",".$URL_ID.",1,1);");
	else mysql_query("INSERT INTO `proto_stats_url_from_day` (`url_from_id`, `url_in_id`, `hit`) VALUES (".$URL_FROM_ID.",".$URL_ID.",1);");
}
endif;

endif;
// ========
endif; // конец проверки посещения бота
endif; // конец условия на наличие данных в массиве $_POST


mysql_close();
?>