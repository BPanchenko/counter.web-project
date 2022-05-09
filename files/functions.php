<?
//IP-адрес пользователя в числовом формате учитывая прокси-сервер
function protostats_getipint() {
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown")) $ip = getenv("HTTP_CLIENT_IP");
	elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR");
	elseif (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) $ip = getenv("REMOTE_ADDR");
	elseif (!empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) $ip = $_SERVER['REMOTE_ADDR'];
	else $ip = "unknown";
	$a=explode(".",$ip);
	return $a[0]*256*256*256+$a[1]*256*256+$a[2]*256+$a[3];
};
// преобразование IP из числового формата в символьный
function protostats_int2ip($i) {
   $d[0]=(int)($i/256/256/256);
   $d[1]=(int)(($i-$d[0]*256*256*256)/256/256);
   $d[2]=(int)(($i-$d[0]*256*256*256-$d[1]*256*256)/256);
   $d[3]=$i-$d[0]*256*256*256-$d[1]*256*256-$d[2]*256;
   return "$d[0].$d[1].$d[2].$d[3]";
}
// Функция переводит utf8-символы в win-1251
// Используется при учете поисковых фраз
function protostats_utf8_win($str) {
	$win = array("а","б","в","г","д","е","ё","ж","з","и",
                 "й","к","л","м","н","о","п","р","с","т",
                 "у","ф","х","ц","ч","ш","щ","ъ","ы","ь",
                 "э","ю","я","А","Б","В","Г","Д","Е","Ё",
                 "Ж","З","И","Й","К","Л","М","Н","О","П",
                 "Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ",
                 "Ъ","Ы","Ь","Э","Ю","Я"," ");
	$utf8 = array("\xD0\xB0","\xD0\xB1","\xD0\xB2","\xD0\xB3","\xD0\xB4",
                  "\xD0\xB5","\xD1\x91","\xD0\xB6","\xD0\xB7","\xD0\xB8",
                  "\xD0\xB9","\xD0\xBA","\xD0\xBB","\xD0\xBC","\xD0\xBD",
                  "\xD0\xBE","\xD0\xBF","\xD1\x80","\xD1\x81","\xD1\x82",
                  "\xD1\x83","\xD1\x84","\xD1\x85","\xD1\x86","\xD1\x87",
                  "\xD1\x88","\xD1\x89","\xD1\x8A","\xD1\x8B","\xD1\x8C",
                  "\xD1\x8D","\xD1\x8E","\xD1\x8F","\xD0\x90","\xD0\x91",
                  "\xD0\x92","\xD0\x93","\xD0\x94","\xD0\x95","\xD0\x81",
                  "\xD0\x96","\xD0\x97","\xD0\x98","\xD0\x99","\xD0\x9A",
                  "\xD0\x9B","\xD0\x9C","\xD0\x9D","\xD0\x9E","\xD0\x9F",
                  "\xD0\xA0","\xD0\xA1","\xD0\xA2","\xD0\xA3","\xD0\xA4",
                  "\xD0\xA5","\xD0\xA6","\xD0\xA7","\xD0\xA8","\xD0\xA9",
                  "\xD0\xAA","\xD0\xAB","\xD0\xAC","\xD0\xAD","\xD0\xAE",
                  "\xD0\xAF","+");
	return str_replace($utf8, $win, $str);
};
// 
function protostats_convert_date($date,$type=0) {
	$__date	= explode(' ',$date);
	$_date	= explode('-',$__date[0]);
	$_time	= explode(':',$__date[1]);
	unset($date);
	switch ($type) :
	case 0:
		if ($__date[0]==date("Y-m-d")) $date = "<strong>сегодня</strong>";
		elseif ($__date[0]==date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")))) $date = "<strong>вчера</strong>";
		//else $date = $_date[2];
		else $date = strftime("%d/%b",strtotime($__date[0]));
		//else $date = strftime("%e %b %a",strtotime($__date[0]));
	break;
	case 1:
		if ($__date[0]==date("Y-m-d")) $date = "<strong>сегодня</strong>";
		elseif ($__date[0]==date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")))) $date = "<strong>вчера</strong>";
		else $date = strftime("%d/%b/%Y",strtotime($__date[0]));
	break;
	endswitch;
	
	return $date;
}
//
function protostats_sortPopularity($a, $b) {
	if ($a['popularity'] == $b['popularity']) return 0;
	return ($a['popularity'] > $b['popularity']) ? -1 : 1;
}
function protostats_sortCoverage($a, $b) {
	if ($a['coverage'] == $b['coverage']) return 0;
	return ($a['coverage'] > $b['coverage']) ? -1 : 1;
}
//
function protostats_sortHostSum($a, $b) {
	if ($a['host_sum'] == $b['host_sum']) return 0;
	return ($a['host_sum'] > $b['host_sum']) ? -1 : 1;
}
// 
function protostats_page_title($url) {
	if (mysql_num_rows(mysql_query("SHOW TABLES LIKE 'proto_site_map'"))) :
		$sql = mysql_query("SELECT `name` FROM `proto_site_map` WHERE `url`='".$url."' LIMIT 1");
		if (mysql_num_rows($sql)) {
			$row = mysql_fetch_array($sql, MYSQL_ASSOC);
			mysql_free_result($sql_total_sum);
			return $row['name'];
		} else return $url;
	else :
		return $url;
	endif;
}
?>