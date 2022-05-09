<?
///////////////////////////////////////////////////////////////
// Система статистики посещений сайта proto.Statistics
// 
// Copyright (C) 2011 Boris Panchenko, http://www.bp-studio.ru.
///////////////////////////////////////////////////////////////
header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
putenv ("LC_ALL=ru_RU");
setlocale (LC_ALL, "Russian");
require_once('files/db_connect.php');
require_once('files/functions.php');

require_once('files/idna_convert.class.php');
$idna_convert = new idna_convert();

// определяем момент начала сбора статистики
$sql = mysql_query("SELECT `date` FROM `proto_stats_day` ORDER BY `date` ASC LIMIT 1");
$row = mysql_fetch_array($sql, MYSQL_ASSOC);
$date_start_sll_stats = $row['date'];
$_date = explode(" ",$date_start_sll_stats);
$__date = explode("-",$_date[0]);
$__time = explode(":",$_date[1]);
$second_all_stats = time()-mktime($__time[0],$__time[1],$__time[2],$__date[1],$__date[2],$__date[0]);
$day_all_stats = ceil($second_all_stats/86400);
mysql_free_result($sql);
// ===================

$day_previous = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));

if ($_GET['ps'] && $_GET['pe']) {
	$DAY_START = $_GET['ps']." 00:00:00";
	$DAY_END = $_GET['pe']." 23:59:59";
} else {
	$DAY_START = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")-1, date("d"), date("Y")));
	$DAY_END = date("Y-m-d"." 23:59:59");
}
if ($DAY_START > $DAY_END) {
	$_day_end = $DAY_END;
	$DAY_END = str_replace("00:00:00","23:59:59",$DAY_START);
	$DAY_START = str_replace("23:59:59","00:00:00",$_day_end);
}
// Для некоторых параметров роста определяем конечную дату периода равную тремя днями ранее заданой
$_date = explode(" ",$DAY_END);
$__date = explode("-",$_date[0]);
$DAY_END_PREV = date("Y-m-d H:i:s", mktime(23, 59, 59, $__date[1], $__date[2]-3, $__date[0]));
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Статистика посещений сайта <?=$_SERVER['SERVER_NAME'];?></title>
	<meta name="Author" content="Boris Panchenko, http://www.bp-studio.ru, boris.panchenko@bp-studio.ru">
	<link href="files/favicon.ico" rel="icon" type="image/x-icon">
	<link href="files/favicon.ico" rel="shortcut icon" type="image/x-icon">
	<link href="files/style.css" rel="stylesheet" type="text/css">
	<link href="http://tools.protosite.ru/css/datepicker.css" rel="stylesheet" type="text/css">
	<link href="files/top_line_<?=rand(1,5);?>.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="http://tools.protosite.ru/js/jquery.min.js"></script>
	<script type="text/javascript" src="http://tools.protosite.ru/js/tooltip.js"></script>
	<script type="text/javascript" src="http://tools.protosite.ru/js/swfobject/swfobject.js"></script>
    <script type="text/javascript" src="http://tools.protosite.ru/js/ui/jquery.ui.datepicker.js"></script>
    <script type="text/javascript" src="http://tools.protosite.ru/js/ui/jquery.ui.datepicker-ru.js"></script>
    <script type="text/javascript">
		var cop = 10;
		var activePeriod = "ps";
		var ps = "<?=str_replace(" 00:00:00","",$DAY_START);?>";
		var pe = "<?=str_replace(" 23:59:59","",$DAY_END);?>";
		function periodNewSet(dateText, inst) {
			if (activePeriod=="ps") ps = dateText;
			if (activePeriod=="pe") pe = dateText;
			location.href = "/proto.statistics/?ps="+ps+"&pe="+pe;
		}
    	function LoadNextPage(el,type) {
			var tr = null;
			for(var i=0;i<cop;i++) {
				tr = $(el).parent().parent().parent().children('tr[visible="hidden"]').get(0);
				$(tr).removeAttr('visible');
				$(tr).removeAttr('style');
			}
			WriteLinkNext(type);
		}
		function WriteLinkNext(type) {
			var size = $('#linkNext_'+type).parent().parent().parent().children('tr[visible="hidden"]').size();
			if (!size) {
				$('#linkNext_'+type).hide();
				return false;
			}
			if (size>cop) size=cop;
			var html = '';
			switch(type) {
				case 'stats_day':
					if (size==1) html = '&nbsp;предыдущий день&nbsp;';
					if (size>1 && size<5) html = '&nbsp;предыдущие '+size+' дня&nbsp;';
					if (size>=5) html = '&nbsp;предыдущие '+size+' дней&nbsp;';
				break;
				case 'url_in':
					if (size==1) html = '&nbsp;следующая страница&nbsp;';
					if (size>1 && size<5) html = '&nbsp;следующие '+size+' страницы&nbsp;';
					if (size>=5) html = '&nbsp;следующие '+size+' страниц&nbsp;';
				break;
				case 'country':
					if (size==1) html = '&nbsp;следующая страна&nbsp;';
					if (size>1 && size<5) html = '&nbsp;следующие '+size+' страны&nbsp;';
					if (size>=5) html = '&nbsp;следующие '+size+' стран&nbsp;';
				break;
				case 'city':
					if (size==1) html = '&nbsp;следующий город&nbsp;';
					if (size>1 && size<5) html = '&nbsp;следующие '+size+' города&nbsp;';
					if (size>=5) html = '&nbsp;следующие '+size+' городов&nbsp;';
				break;
				case 'os':
					if (size==1) html = '&nbsp;следующая платформа&nbsp;';
					if (size>1 && size<5) html = '&nbsp;следующие '+size+' платформы&nbsp;';
					if (size>=5) html = '&nbsp;следующие '+size+' платформ&nbsp;';
				break;
				case 'browsers':
					if (size==1) html = '&nbsp;следующий браузер&nbsp;';
					if (size>1 && size<5) html = '&nbsp;следующие '+size+' браузера&nbsp;';
					if (size>=5) html = '&nbsp;следующие '+size+' браузеров&nbsp;';
				break;
				case 'phrase':
					if (size==1) html = '&nbsp;следующий запрос&nbsp;';
					if (size>1 && size<5) html = '&nbsp;следующие '+size+' запроса&nbsp;';
					if (size>=5) html = '&nbsp;следующие '+size+' запросов&nbsp;';
				break;
				case 'url_from':
					if (size==1) html = '&nbsp;следующий источник&nbsp;';
					if (size>1 && size<5) html = '&nbsp;следующие '+size+' источника&nbsp;';
					if (size>=5) html = '&nbsp;следующие '+size+' источников&nbsp;';
				break;
				default: alert('Я таких значений не знаю');
			}

			$('#linkNext_'+type).children('small').html(html);
			return true;
		}
		function showDatePicker(event,el,periodVarName) {
			var сe = $(el);
			var left_offset_position = Math.round(сe.offset().left);
			var top_offset_position = Math.round(сe.offset().top);
			if (periodVarName=='ps') activePeriod = "ps";
			if (periodVarName=='pe') activePeriod = "pe";
			$('#datePicker').css("left", left_offset_position);
			$('#datePicker').show();
			if(event.preventDefault) {
				event.preventDefault();
				event.stopPropagation();
			} else {
				event.returnValue=false;
				event.cancelBubble=true;
			}
		}
		$(document).ready(function(){
			WriteLinkNext('stats_day');
			WriteLinkNext('url_in');
			WriteLinkNext('country');
			WriteLinkNext('city');
			WriteLinkNext('os');
			WriteLinkNext('browsers');
			WriteLinkNext('phrase');
			WriteLinkNext('url_from');
			$.datepicker.setDefaults( $.extend($.datepicker.regional["ru"]) );
			$("#datePicker").datepicker({
				dateFormat: "yy-mm-dd",
				minDate: "-<?=$day_all_stats;?>d",
				maxDate: "0",
				showButtonPanel: true,
				firstDay: 1,
				onSelect: function(dateText, inst) {
					periodNewSet(dateText, inst);
				}
			});
			$("#datePicker").click(function (event) {
				if(event.preventDefault) {
					event.preventDefault();
					event.stopPropagation();
				} else {
					event.returnValue=false;
					event.cancelBubble=true;
				}
			})
			$("body").click(function () { $('#datePicker').hide(); })
		});
    </script>
</head>
<body>
<div id="system_message_box"></div>
<table id="top_line">
<tr>
<td class="square1"></td>
<td class="square2"></td>
<td class="square3"></td>
<td class="square4"></td>
<td class="square5"></td>
</tr>
<tr>
<td colspan="5" class="cell_with_logo"><a href="http://www.protosite.ru/statistics.phtml"><strong>proto.Statistics</strong></a></td>
</tr>
</table>
<div id="container">

<div id="menu_top">
Статистика сайта <a href="http://<?=$_SERVER['SERVER_NAME'];?>" target="_blank" class="color_inherit"><?=$_SERVER['SERVER_NAME'];?></a> за период с <a href="javascript:void(0)" onClick="showDatePicker(event,this,'ps')"><?=protostats_convert_date($DAY_START,1);?></a> до <a href="javascript:void(0)" onClick="showDatePicker(event,this,'pe')"><?=protostats_convert_date($DAY_END,1);?></a>
<div id="datePicker"></div>
</div>
<br clear="all">

<!-- GRAPH -->
<div class="rounded_box_gray"><div class="corner_rt"></div>
<div class="inside"><div style="background-color: #F9F9F9;">
<script type="text/javascript"> 
var flashvars    = { 
    xmlPath: 'http://<?=$_SERVER['SERVER_NAME'];?>/proto.statistics/xml/graph_host_data.php',
	dateStart: '<?=urlencode($DAY_START);?>',
	dateEnd: '<?=urlencode($DAY_END);?>'
}; 
var params    = { wmode:'transparent', allowFullScreen:'true' }; 
var attributes    = { }; 
swfobject.embedSWF("files/graph_proto_statistics.swf", 
                    "statsGraph", "100%", "300", "9,0,115,0", 
                    "http://tools.protosite.ru/js/swfobject/expressInstall.swf", 
                    flashvars, params, attributes);
</script> 
<div id="statsGraph"> 
<a href="http://www.adobe.com/go/getflashplayer"><img src=http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif alt="Get Adobe Flash player"></a> 
</div></div>
</div>
<div class="corner_lb"><div class="corner_rb"></div></div></div>
<!-- // graph -->

<br>

<!-- STATISTICS -->
<div class="rounded_box_gray"><div class="corner_rt"></div>
<div class="inside">


<div class="statistics_box">

<h5>Посещаемость сайта</h5>
<table class="list_items">
<tr><th><small>Посетителей</small></th><th><small>Посещений</small></th><th></th></tr>
<?
$sql = mysql_query("SELECT * FROM `proto_stats_day` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' ORDER BY `id` DESC");
$a=0;
$num_rows = mysql_num_rows($sql);
while ($row = mysql_fetch_array($sql, MYSQL_ASSOC)) {
	$a%2 ? print "<tr row_key=\"".$a."\" class=\"row_odd\"" : print "<tr row_key=\"".$a."\" class=\"row_even\"";
	$a<10 ? print ">" : print " visible=\"hidden\" style=\"display: none;\">";
	print "<td class=\"first\" align=\"right\"><strong>".$row['host']."</strong></td>
		<td align=\"right\">".$row['hit']."</td>
		<td class=\"last dark\">".protostats_convert_date($row['date'])."</td>";
	if ($row['date']!=date("Y-m-d") && $a<4) {
		$count_last_vals++;
		$last_val_host += $row['host'];
		$last_val_hit += $row['hit'];
	}
	if ($row['date']!=date("Y-m-d")) {
		$count_day_sum++;
		$host_day_sum += $row['host'];
		$hit_day_sum += $row['hit'];
	}
	$a++;
}
mysql_free_result($sql);

$DAY_COUNT = $a;
$host_day_mean = round($host_day_sum/$count_day_sum,2);
$hit_day_mean = round($hit_day_sum/$count_day_sum,2);
$host_previous_mean = round($last_val_host/$count_last_vals,2);
$hit_previous_mean = round($last_val_hit/$count_last_vals,2);

if ($host_previous_mean > $host_day_mean) { $class_name_host = "sign_up"; $str_sign_host = "роста"; }
if ($host_previous_mean < $host_day_mean) { $class_name_host = "sign_down"; $str_sign_host = "спада"; }
if ($hit_previous_mean > $hit_day_mean) { $class_name_hit = "sign_up"; $str_sign_hit = "роста"; }
if ($hit_previous_mean < $hit_day_mean) { $class_name_hit = "sign_down"; $str_sign_hit = "спада"; }
?>
</tr>
<? $a%2 ? print "<tr class=\"row_even\" colspan=\"3\">" : print "<tr class=\"row_odd\" colspan=\"3\">"; ?>
<td colspan="6" class="first last dark">
<a href="javascript:void(0)" onClick="LoadNextPage(this,'stats_day')" class="invert_face" id="linkNext_stats_day"><small></small></a>
</td>
</tr>
</table>
<table class="list_items">
<tr><td>
<small>
В среднем посетителей <u tool="Уникальные пользователи за день фиксируются отдельно от посетителей за месяц и за весь период работы сайта.<br>Суммарное количество уникальных посетителей за день обычно больше чем за месяц.">за день</u>: <strong class="<?=$class_name_host;?>" tool="тенденция <?=$str_sign_host;?> за последние <?=$count_last_vals;?> дня"><?=$host_day_mean;?></strong><br>
Посещений на одного пользователя за день: <strong class="<?=$class_name_hit;?>" tool="тенденция <?=$str_sign_hit;?> за последние <?=$count_last_vals;?> дня"><?=$hit_day_mean;?></strong><br>
</small>
</td></tr>
<tr><td>
<small>
<?
$sql = mysql_query("SELECT `id` FROM `proto_stats_ip_all` WHERE (`date_record`>='".$DAY_START."' AND `date_record`<='".$DAY_END."') OR (`date_last_hit`!='0000-00-00 00:00:00' AND `date_last_hit`>='".$DAY_START."' AND `date_last_hit`<='".$DAY_END."')");
$host_count_period = mysql_num_rows($sql);
mysql_free_result($sql);
$sql = mysql_query("SELECT `id` FROM `proto_stats_ip_all` WHERE `date_record`>='".$DAY_START."' AND `date_record`<='".$DAY_END."'");
$host_new_count = mysql_num_rows($sql);
mysql_free_result($sql);
?>
Уникальных посетителей за выбранный период: <strong><?=$host_count_period;?></strong><br>
О сайте узнали впервые: <strong><?=$host_new_count;?></strong>
</small>
</td></tr>
</table>
<? /*////////////////////////////////////////////////////////*/ ?>

</div>


<div class="statistics_box">

<h5>Посещаемость страниц</h5>
<?
$arUrlIN = array();
$sql = mysql_query("SELECT * FROM `proto_stats_url_in`");
/*
SELECT SUM(proto_stats_url_in_day.hit) as hit,SUM(proto_stats_url_in_day.host) as host,proto_stats_url_in.url FROM proto_stats_url_in RIGHT JOIN proto_stats_url_in_day ON proto_stats_url_in.id = proto_stats_url_in_day.url_id
*/
while ($row = mysql_fetch_array($sql, MYSQL_ASSOC)) {
	$sql_sum = mysql_query("SELECT SUM(`hit`),SUM(`host`) FROM `proto_stats_url_in_day` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' AND `url_id`=".$row['id']);
	$row_sum = mysql_fetch_array($sql_sum, MYSQL_ASSOC);
	mysql_free_result($sql_sum);
	$sql_previous = mysql_query("SELECT SUM(`hit`),SUM(`host`) FROM `proto_stats_url_in_day` WHERE `date`>='".$DAY_END_PREV."' AND `date`<='".$DAY_END."' AND `url_id`=".$row['id']);
	$row_previous = mysql_fetch_array($sql_previous, MYSQL_ASSOC);
	mysql_free_result($sql_previous);
	if ($row_sum['SUM(`hit`)']) array_push($arUrlIN,array(
		"url" => $row['url'],
		"hit" => $row_sum['SUM(`hit`)'],
		"host" => $row_sum['SUM(`host`)'],
		"popularity" => round((sin(deg2rad(90*$host_day_mean/($row_sum['SUM(`hit`)']/$row_sum['SUM(`host`)'])))+1)*100/2,1),
		"popularity_prev" => round((sin(deg2rad(90*$host_day_mean/($row_previous['SUM(`hit`)']/$row_previous['SUM(`host`)'])))+1)*100/2,1),
		"point_in"	=> 0,
		"point_out"	=> 0
	));
	unset($row_sum);
}
usort($arUrlIN, "protostats_sortPopularity");
mysql_free_result($sql);
?>
<table class="list_items">
<tr><th align="left"><small>Адрес страницы</small></th>
<th><small>Популярность</small></th>
<th><small><u tool="Фиксируются уникальные посетители за день">Посетителей</u></small></th>
<th><small>Посещений</small></th>
<? /*
<th><small>Точка входа</small></th>
<th><small>Точка выхода</small></th>
*/ ?></tr>
<?
foreach ($arUrlIN as $key=>$value) {
	$key%2 ? print "<tr column_key=\"".$key."\" class=\"row_odd\"" : print "<tr column_key=\"".$key."\" class=\"row_even\"";
	$key<10 ? print ">" : print " visible=\"hidden\" style=\"display: none;\">";
	$popularity_previous = ($value['hit_previous'] + 2*$value['host_previous'])*100/($hit_day_previous + 2*$host_day_previous);
	if ($value['popularity_prev'] > $value['popularity']) { $class_name_popularity = "sign_up"; $str_sign = "роста"; }
	if ($value['popularity_prev'] < $value['popularity']) { $class_name_popularity = "sign_down"; $str_sign = "спада"; }
?>
<? if (strlen($value['url'])>40) { ?>
<td class="first"><a href="<?=$value['url'];?>" target="_blank"><u tool="<?=$value['url'];?>"><?=substr($value['url'], 0, 40);?></u></a></td>
<? } else { ?>
<td class="first"><a href="<?=$value['url'];?>" target="_blank"><?=protostats_page_title($value['url']);?></a></td>
<? } ?>
<td align="center" class="dark"><span class="<?=$class_name_popularity;?>" tool="тенденция <?=$str_sign;?>"><?=$value['popularity'];?>%</span></td>
<td align="right"><?=$value['host'];?></td>
<td align="right" class="last"><?=$value['hit'];?></td>
<? /*
<td align="right" class="first"><?=$value['point_in'];?>%</td>
<td align="right" class="last"><?=$value['point_out'];?>%</td>
*/ ?>
</tr>
<? } ?>
<? $key%2 ? print "<tr class=\"row_even\" colspan=\"3\">" : print "<tr class=\"row_odd\" colspan=\"3\">"; ?>
<td colspan="6" class="first last dark">
<a href="javascript:void(0)" onClick="LoadNextPage(this,'url_in')" class="invert_face" id="linkNext_url_in"><small></small></a>
</td>
</tr>
</table>

</div>

<? /*////////////////////////////////////////////////////////*/ ?>
<br clear="all"><br>
<?
$sql_total = mysql_query("SELECT SUM(`host`),SUM(`hit`) FROM `proto_stats_geo_moth` WHERE `date`>='".$DAY_START."' AND `date`<='".$DAY_END."'");
$row_total = mysql_fetch_array($sql_total, MYSQL_ASSOC);
mysql_free_result($sql_total);
$host_total = $row_total['SUM(`host`)'];
$hit_total = $row_total['SUM(`hit`)'];
?>
<div class="statistics_box">
<h5>Страны</h5>
<?
$coverage_undefined = 100;
$arCountry = array();
$sql = mysql_query("SELECT DISTINCT `geo_ip_id` FROM `proto_stats_geo_moth` WHERE `date`>='".$DAY_START."' AND `date`<='".$DAY_END."'");
while($row = mysql_fetch_array($sql, MYSQL_ASSOC)) {
	$geo_ip_id = $row['geo_ip_id'];
	$sql_country_id = mysql_query("SELECT `country_id` FROM `proto_dir_geo_ip` WHERE `id`=".$geo_ip_id." LIMIT 1");
	$row_country_id = mysql_fetch_array($sql_country_id, MYSQL_ASSOC);
	mysql_free_result($sql_country_id);
	$country_id = $row_country_id['country_id'];
	if ($country_id) {
		$sql_country = mysql_query("SELECT `country` FROM `proto_dir_country` WHERE `country_id`=".$country_id." LIMIT 1");
		$row_country = mysql_fetch_array($sql_country, MYSQL_ASSOC);
		mysql_free_result($sql_country);
		array_push($arCountry, array(
			"country_id" => $country_id,
			"country" => $row_country['country'],
			"host_sum" => 0,
			"hit_sum" => 0
		));
		$sql_sum = mysql_query("SELECT SUM(`host`),SUM(`hit`) FROM `proto_stats_geo_moth` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' AND `geo_ip_id` = ".$geo_ip_id);
		$row_sum = mysql_fetch_array($sql_sum, MYSQL_ASSOC);
		mysql_free_result($sql_sum);
		$a=0;
		$f=true;
		while ($a<count($arCountry) && $f) {
			if ($arCountry[$a]['country_id']==$country_id) {
				$arCountry[$a]['host_sum'] += $row_sum['SUM(`host`)'];
				$arCountry[$a]['hit_sum'] += $row_sum['SUM(`hit`)'];
				$arCountry[$a]['coverage'] = round(100*$arCountry[$a]['host_sum']/$host_total,1);
				if ($a != count($arCountry)-1) array_pop($arCountry);
				$f=false;
			}
			$a++;
		}
	}
}
mysql_free_result($sql);
usort($arCountry, "protostats_sortCoverage");
?>
<table class="list_items">
<tr><th align="left"><small>Название</small></th>
<th><small>Охват</small></th>
<th><small><u tool="Фиксируются уникальные посетители за месяц">Посетителей</u></small></th>
<th><small>Посещений</small></th></tr>
<?
foreach ($arCountry as $key=>$value) {
	$key%2 ? print "<tr column_key=\"".$key."\" class=\"row_odd\"" : print "<tr column_key=\"".$key."\" class=\"row_even\"";
	$key<10 ? print ">" : print " visible=\"hidden\" style=\"display: none;\">";
	$coverage_undefined = $coverage_undefined- $value['coverage'];
?>
<td class="first"><?=$value['country'];?></td>
<td align="center" class="dark"><span><?=$value['coverage'];?>%</span></td>
<td align="right"><?=$value['host_sum'];?></td>
<td align="right" class="last"><?=$value['hit_sum'];?></td>
</tr>
<? } ?>
<? $key%2 ? print "<tr class=\"row_even\" colspan=\"3\">" : print "<tr class=\"row_odd\" colspan=\"3\">"; ?>
<td colspan="6" class="first last dark">
<a href="javascript:void(0)" onClick="LoadNextPage(this,'country')" class="invert_face" id="linkNext_country"><small></small></a>
</td>
</tr>
</table>
<? if ($coverage_undefined>1) { ?>
<table class="list_items">
<tr><td>
<small>
У <?=$coverage_undefined;?>% посетителей не удалось определить страну.
</small>
</td></tr>
</td></tr>
</table>
<? } ?>
</div>
<? /*////////////////////////////////////////////////////////*/ ?>

<div class="statistics_box">
<h5>Города</h5>

<?
$arCity = array();
$coverage_undefined = 100;
$sql = mysql_query("SELECT DISTINCT `geo_ip_id` FROM `proto_stats_geo_moth` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%'");
while($row = mysql_fetch_array($sql, MYSQL_ASSOC)) {
	$geo_ip_id = $row['geo_ip_id'];
	$sql_city_id = mysql_query("SELECT `city_id` FROM `proto_dir_geo_ip` WHERE `id`=".$geo_ip_id." LIMIT 1");
	$row_city_id = mysql_fetch_array($sql_city_id, MYSQL_ASSOC);
	mysql_free_result($sql_city_id);
	$city_id = $row_city_id['city_id'];
	if ($city_id) {
		$sql_city = mysql_query("SELECT `name` FROM `proto_dir_city` WHERE `city_id`=".$city_id." LIMIT 1");
		$row_city = mysql_fetch_array($sql_city, MYSQL_ASSOC);
		mysql_free_result($sql_city);
		array_push($arCity, array(
			"city_id" => $city_id,
			"city" => $row_city['name'],
			"host_sum" => 0,
			"hit_sum" => 0
		));
		$sql_sum = mysql_query("SELECT SUM(`host`),SUM(`hit`) FROM `proto_stats_geo_moth` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' AND `geo_ip_id` = ".$geo_ip_id);
		$row_sum = mysql_fetch_array($sql_sum, MYSQL_ASSOC);
		mysql_free_result($sql_sum);
		$a=0;
		$f=true;
		while ($a<count($arCity) && $f) {
			if ($arCity[$a]['city_id']==$city_id) {
				$arCity[$a]['host_sum'] += $row_sum['SUM(`host`)'];
				$arCity[$a]['hit_sum'] += $row_sum['SUM(`hit`)'];
				$arCity[$a]['coverage'] = round(100*$arCity[$a]['host_sum']/$host_total,1);
				if ($a != count($arCity)-1) array_pop($arCity);
				$f=false;
			}
			$a++;
		}
	}
}
mysql_free_result($sql);
usort($arCity, "protostats_sortCoverage");
?>
<table class="list_items">
<tr><th align="left"><small>Название</small></th>
<th><small>Охват</small></th>
<th><small><u tool="Фиксируются уникальные посетители за месяц">Посетителей</u></small></th>
<th><small>Посещений</small></th></tr>
<?
foreach ($arCity as $key=>$value) {
	$key%2 ? print "<tr column_key=\"".$key."\" class=\"row_odd\"" : print "<tr column_key=\"".$key."\" class=\"row_even\"";
	$key<10 ? print ">" : print " visible=\"hidden\" style=\"display: none;\">";
	$coverage_undefined = $coverage_undefined- $value['coverage'];
?>
<td class="first"><?=$value['city'];?></td>
<td align="center" class="dark"><span><?=$value['coverage'];?>%</span></td>
<td align="right"><?=$value['host_sum'];?></td>
<td align="right" class="last"><?=$value['hit_sum'];?></td>
</tr>
<? } ?>
<? $key%2 ? print "<tr class=\"row_even\" colspan=\"3\">" : print "<tr class=\"row_odd\" colspan=\"3\">"; ?>
<td colspan="6" class="first last dark">
<a href="javascript:void(0)" onClick="LoadNextPage(this,'city')" class="invert_face" id="linkNext_city"><small></small></a>
</td>
</tr>
</table>
<? if ($coverage_undefined>1) { ?>
<table class="list_items">
<tr><td>
<small>
У <?=$coverage_undefined;?>% посетителей не удалось определить город.
</small>
</td></tr>
</td></tr>
</table>
<? } ?>
</div>
<? /*////////////////////////////////////////////////////////*/ ?>

<br clear="all"><br>

<?
$sql_total_sum = mysql_query("SELECT SUM(`host`) FROM `proto_stats_agent_moth` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%'");
$row_total_sum = mysql_fetch_array($sql_total_sum, MYSQL_ASSOC);
$total_sum = $row_total_sum['SUM(`host`)'];
mysql_free_result($sql_total_sum);
?>
<div class="statistics_box">
<h5>Платформы</h5>
<?
// Формируем данные
$oses = array(
	'Windows' => '(Windows)|(Win16)|(Win95)|(Win98)|(WinNT)|^(Windows Mobile)',
	'Windows Mobile' => 'Windows Mobile',
	'Macintosh'=>'(Mac_PowerPC)|(Macintosh)',
	'iPhone'=>'iPhone',
	'iPad'=>'iPad',
	'BlackBerry'=>'BlackBerry',
	'Linux/Unix'=>'(Linux)|(X11)|(Unix)|(Lynx)',
	'Android'=>'Android'
);
foreach ($oses as $os=>$pattern) {
	$strWhere = "`agent` LIKE '%".str_replace(array(')|(',')|^(','(',')'),array("%' OR `agent` LIKE '%","%' AND `agent` NOT LIKE '%"),$pattern)."%'";
	$sql = mysql_query("SELECT `id` FROM `proto_stats_agent` WHERE ".$strWhere);
	unset($arStrWhere,$strWhere);
	while ($row = mysql_fetch_array($sql, MYSQL_ASSOC)) $arStrWhere[] = "`agent_id`=".$row['id'];
	mysql_free_result($sql);
	$strWhere = implode(" OR ",$arStrWhere);
	$sql_sum = mysql_query("SELECT SUM(`host`) FROM `proto_stats_agent_moth` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' AND (".$strWhere.")");
	$row_sum = mysql_fetch_array($sql_sum, MYSQL_ASSOC);
	if (!$row_sum['SUM(`host`)']) $row_sum['SUM(`host`)'] = 0;
	$arOS[]=array(
		"name" => $os,
		"host_sum" => $row_sum['SUM(`host`)'],
		"percent" => round($row_sum['SUM(`host`)']*100/$total_sum)
	);
	mysql_free_result($sql_sum);
}
usort($arOS, "protostats_sortHostSum");
$_otherOS = array("name"=>"Другие", "host_sum"=>$total_sum, "percent"=>100);
foreach ($arOS as $value) if ($value['host_sum']) {
	$arOS_out[] = $value;
	$_otherOS['host_sum'] -= $value['host_sum'];
}
$_otherOS['percent'] = round($_otherOS['host_sum']*100/$total_sum);
array_push($arOS_out,$_otherOS);
?>

<table class="list_items">
<tr><th align="left"><small>Название</small></th>
<th><small>Популярность</small></th>
<?
foreach ($arOS_out as $key=>$value) {
	$key%2 ? print "<tr column_key=\"".$key."\" class=\"row_odd\"" : print "<tr column_key=\"".$key."\" class=\"row_even\"";
	$key<10 ? print ">" : print " visible=\"hidden\" style=\"display: none;\">";
?>
<td class="first"><?=$value['name'];?></td>
<td align="center" class="dark last"><span class="-><?=$class_name_popularity;?>"><?=$value['percent'];?>%</span></td>
</tr>
<? } ?>
<? $key%2 ? print "<tr class=\"row_even\" colspan=\"3\">" : print "<tr class=\"row_odd\" colspan=\"3\">"; ?>
<td colspan="6" class="first last dark">
<a href="javascript:void(0)" onClick="LoadNextPage(this,'os')" class="invert_face" id="linkNext_os"><small></small></a>
</td>
</tr>
</table>
</div>
<? /*////////////////////////////////////////////////////////*/ ?>


<div class="statistics_box">
<h5>Браузеры</h5>
<?
// Формируем данные о браузерах
$arBrowsers = array(
	array("name"=>"Chrome", "host_sum"=>0),
	array("name"=>"Opera", "host_sum"=>0),
	array("name"=>"Opera Mini", "host_sum"=>0),
	array("name"=>"Opera Mobile", "host_sum"=>0),
	array("name"=>"Firefox", "host_sum"=>0),
	array("name"=>"Internet Explorer версии 7 и ниже", "host_sum"=>0),
	array("name"=>"Internet Explorer версии 8 и выше", "host_sum"=>0),
	array("name"=>"Safari", "host_sum"=>0)
);
foreach ($arBrowsers as $key=>$value) {
	// собираем строку запроса по идентификаторам браузеров
	if ($value['name']=="Safari") $sql = mysql_query("SELECT `id` FROM `proto_stats_agent` WHERE `agent` LIKE '%Version%' AND `agent` LIKE '%Safari%'");
	elseif ($value['name']=="Opera") $sql = mysql_query("SELECT `id` FROM `proto_stats_agent` WHERE `agent` LIKE '%".$value['name']."%' AND `agent` NOT LIKE '%Opera Mini%' AND `agent` NOT LIKE '%Opera Mobi%'");
	elseif ($value['name']=="Opera Mobile") $sql = mysql_query("SELECT `id` FROM `proto_stats_agent` WHERE `agent` LIKE '%Opera Mobi%'");
	elseif ($value['name']=="Internet Explorer версии 7 и ниже") $sql = mysql_query("SELECT `id` FROM `proto_stats_agent` WHERE `agent` LIKE '%MSIE 7.0%' OR `agent` LIKE '%MSIE 6.0%' OR `agent` LIKE '%MSIE 5.5%' OR `agent` LIKE '%MSIE 5.0%' OR `agent` LIKE '%MSIE 4.0%'");
	elseif ($value['name']=="Internet Explorer версии 8 и выше") $sql = mysql_query("SELECT `id` FROM `proto_stats_agent` WHERE `agent` LIKE '%MSIE 8.0%' OR `agent` LIKE '%MSIE 9.0%' OR `agent` LIKE '%MSIE 10.0%'");
	else $sql = mysql_query("SELECT `id` FROM `proto_stats_agent` WHERE `agent` LIKE '%".$value['name']."%'");
	unset($arStrWhere,$strWhere);
	while ($row = mysql_fetch_array($sql, MYSQL_ASSOC)) $arStrWhere[] = "`agent_id`=".$row['id'];
	mysql_free_result($sql);
	$strWhere = implode(" OR ",$arStrWhere);
	$sql_sum = mysql_query("SELECT SUM(`host`) FROM `proto_stats_agent_moth` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' AND (".$strWhere.")");
	$row_sum = mysql_fetch_array($sql_sum, MYSQL_ASSOC);
	$arBrowsers[$key]['host_sum'] = $row_sum['SUM(`host`)'];
	$arBrowsers[$key]['percent'] = round($row_sum['SUM(`host`)']*100/$total_sum);
	mysql_free_result($sql_sum);
}
usort($arBrowsers, "protostats_sortHostSum");
$_otherBrowsers = array("name"=>"Другие", "host_sum"=>$total_sum, "percent"=>100);
foreach ($arBrowsers as $value) if ($value['host_sum']) {
	$arBrowsers_out[] = $value;
	$_otherBrowsers['host_sum'] -= $value['host_sum'];
}
$_otherBrowsers['percent'] = round($_otherBrowsers['host_sum']*100/$total_sum);
if ($_otherBrowsers['percent']>=1) array_push($arBrowsers_out,$_otherBrowsers);
/* //////////////////////////////////////////////////////////// */
?>
<!--img src="files/diagrammCircle_browsers.php?h=170"-->

<table class="list_items">
<tr><th align="left"><small>Название</small></th>
<th><small>Популярность</small></th>
<?
foreach ($arBrowsers_out as $key=>$value) {
	$key%2 ? print "<tr column_key=\"".$key."\" class=\"row_odd\"" : print "<tr column_key=\"".$key."\" class=\"row_even\"";
	$key<10 ? print ">" : print " visible=\"hidden\" style=\"display: none;\">";
?>
<td class="first"><?=$value['name'];?></td>
<td align="center" class="dark last"><span class="-><?=$class_name_popularity;?>"><?=$value['percent'];?>%</span></td>
</tr>
<? } ?>
<? $key%2 ? print "<tr class=\"row_even\" colspan=\"3\">" : print "<tr class=\"row_odd\" colspan=\"3\">"; ?>
<td colspan="6" class="first last dark">
<a href="javascript:void(0)" onClick="LoadNextPage(this,'browsers')" class="invert_face" id="linkNext_browsers"><small></small></a>
</td>
</tr>
</table>
</div>
<? /*////////////////////////////////////////////////////////*/ ?>

<br clear="all"><br>

<? /*////////////////////////////////////////////////////////*/ ?>

<?
$sql = mysql_query("SELECT DISTINCT(`phrase_id`) FROM `proto_stats_searcher` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' ORDER BY `hit` DESC, `host` DESC;");
while ($row = mysql_fetch_array($sql, MYSQL_ASSOC)) {
	// get phrase
	$sql_phrase = mysql_query("SELECT `text` FROM `proto_stats_searcher_phrase` WHERE `id`=".$row['phrase_id']." LIMIT 1;");
	$row_phrase = mysql_fetch_array($sql_phrase, MYSQL_ASSOC);
	mysql_free_result($sql_phrase);
	
	$sql_data_url = mysql_query("SELECT DISTINCT(`url_id`) FROM `proto_stats_searcher` WHERE `phrase_id`=".$row['phrase_id']." AND `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' ORDER BY `hit` DESC, `host` DESC;");
	unset($data_url);
	while ($row_data_url = mysql_fetch_array($sql_data_url, MYSQL_ASSOC)) {
		// get url
		$sql_url = mysql_query("SELECT `url` FROM `proto_stats_url_in` WHERE `id`=".$row_data_url['url_id']." LIMIT 1;");
		$row_url = mysql_fetch_array($sql_url, MYSQL_ASSOC);
		mysql_free_result($sql_url);
		$data_url[] = $row_url['url'];
	}
	mysql_free_result($sql_data_url);
	
	$sql_data_searcher = mysql_query("SELECT DISTINCT(`searcher_id`) FROM `proto_stats_searcher` WHERE `phrase_id`=".$row['phrase_id']." AND `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' ORDER BY `hit` DESC, `host` DESC;");
	unset($data_searcher);
	while ($row_data_searcher = mysql_fetch_array($sql_data_searcher, MYSQL_ASSOC)) {
		// get searcher url
		$sql_site = mysql_query("SELECT * FROM `proto_stats_searcher_site` WHERE `id`=".$row_data_searcher['searcher_id']." LIMIT 1;");
		$row_site = mysql_fetch_array($sql_site, MYSQL_ASSOC);
		mysql_free_result($sql_site);
		if ($row_site['url']=="yandex.ru") $link = $row_site['url']."/yandsearch?".str_replace(array("([^&]+)","/i","/","[^a]"),array($row_phrase['text'],"","",""),$row_site['pattern']);
		elseif ($row_site['url']=="go.mail.ru") $link = $row_site['url']."/search?".str_replace(array("([^&]+)","/i","/","[^a]"),array($row_phrase['text'],"","",""),$row_site['pattern']);
		else $link = $row_site['url']."/?".str_replace(array("([^&]+)","/i","/","[^a]"),array($row_phrase['text'],"","",""),$row_site['pattern']);
		$data_searcher[] = array(
			"id"		=> $row_data_searcher['searcher_id'],
			"url"		=> $row_site['url'],
			"link"		=> $link,
			"host"		=> 0,
			"hit"		=> 0
		);
	}
	mysql_free_result($sql_data);
	
	$sql_sum = mysql_query("SELECT SUM(`host`),SUM(`hit`) FROM `proto_stats_searcher` WHERE `phrase_id`=".$row['phrase_id']." AND `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%';");
	$row_sum = mysql_fetch_array($sql_sum, MYSQL_ASSOC);
	mysql_free_result($sql_sum);
	$sql_total_sum = mysql_query("SELECT SUM(`host`),SUM(`hit`) FROM `proto_stats_searcher` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%';");
	$row_total_sum = mysql_fetch_array($sql_total_sum, MYSQL_ASSOC);
	mysql_free_result($sql_total_sum);
	if ($row_phrase['text']) $arPhraseSearch[] = array(
		"phrase_id"		=> $row['phrase_id'],
		"phrase"		=> $row_phrase['text'],
		//"popularity" 	=> floor(($row_sum['SUM(`hit`)'] + 2*$row_sum['SUM(`host`)'])*1000/($row_total_sum['SUM(`hit`)'] + 2*$row_total_sum['SUM(`host`)']))/10,
		"popularity" => round((sin(deg2rad(90*$host_day_mean/($row_sum['SUM(`hit`)']/$row_sum['SUM(`host`)'])))+1)*100/2,1),
		"host_sum"		=> $row_sum['SUM(`host`)'],
		"hit_sum"		=> $row_sum['SUM(`hit`)'],
		"data_searcher" => $data_searcher,
		"data_url" 		=> $data_url
	);
	//unset($data);
}
mysql_free_result($sql);
usort($arPhraseSearch,"protostats_sortPopularity");
if (is_array($arPhraseSearch)) :
?>
<div class="statistics_box">
<h5>Поисковые запросы</h5>
<table class="list_items">
<tr>
<th align="left"><small>Запрос</small></th>
<th><small>Эффективность</small></th>
<th><small>Переходов</small></th>
<th><small><u tool="Фиксируются уникальные посетители за месяц">Уникальных</u></small></th>
<th><small>Источники</small></th>
<th><small>Точки входа</small></th>
</tr>
<?
foreach ($arPhraseSearch as $key=>$value) {
	$key%2 ? print "<tr column_key=\"".$key."\" class=\"row_odd\"" : print "<tr column_key=\"".$key."\" class=\"row_even\"";
	$key<10 ? print ">" : print " visible=\"hidden\" style=\"display: none;\">";
?>
<? if (mb_strlen($value['phrase'])>40) { ?>
<td class="first"><u><strong tool="<?=$value['phrase'];?>"><?=mb_substr($value['phrase'], 0, 40);?></strong></u></td>
<? } else { ?>
<td class="first"><strong><?=$idna_convert->decode($value['phrase']);?></strong></td>
<? } ?>
<td align="center" class="dark"><span><?=$value['popularity'];?>%</span></td>
<td align="right"><strong><?=$value['hit_sum'];?></strong></td>
<td align="right"><?=$value['host_sum'];?></td>
<td align="right"><? foreach ($value['data_searcher'] as $searcher) {
	print "<a href=\"http://".$searcher['link']."\" target=\"_blank\">".$searcher['url']."</a><br>";
}
?></td>
<td class="last"><small><? foreach ($value['data_url'] as $url) print "<a href=\"".$url."\" target=\"_blank\">".$url."</a><br>";?></small></td>
</tr>
<? } ?>
<? $key%2 ? print "<tr class=\"row_even\" colspan=\"3\">" : print "<tr class=\"row_odd\" colspan=\"3\">"; ?>
<td colspan="6" class="first last dark">
<a href="javascript:void(0)" onClick="LoadNextPage(this,'phrase')" class="invert_face" id="linkNext_phrase"><small></small></a>
</td>
</tr>
</table>
</div>
<? endif; ?>
<? /*////////////////////////////////////////////////////////*/ ?>

<div class="statistics_box">
<h5>Показатели сайта</h5>
<?
require_once('files/class.XmlToArray.php');
require_once('files/pr.php');
$xml = file_get_contents("http://bar-navig.yandex.ru/u?ver=2&url=http://".str_replace("wwww.","",$_SERVER['SERVER_NAME'])."&show=1");
$XmlToArray = new XmlToArray($xml);
$arYandexRank = $XmlToArray->createArray();
?>
Яндекс тИЦ: <strong><?=$arYandexRank['urlinfo']['tcy']['value'];?></strong><br>
Яндекс PR: <strong><?=$arYandexRank['urlinfo']['tcy']['rang'];?></strong>/6<br>
<? /* Google PageRank: <?=getPR('http://barbook.ru');?>/10<br> */ ?>
</div>
<? /*////////////////////////////////////////////////////////*/ ?>

<br clear="all"><br>

<?
$sql_total_sum = mysql_query("SELECT SUM(`hit`), SUM(`host`) FROM `proto_stats_url_from_day` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%'");
$row_total_sum = mysql_fetch_array($sql_total_sum, MYSQL_ASSOC);
$hit_sum_total = $row_total_sum['SUM(`hit`)'];
$host_sum_total = $row_total_sum['SUM(`host`)'];
mysql_free_result($sql_total_sum);
$sql = mysql_query("SELECT DISTINCT(`url_from_id`) FROM `proto_stats_url_from_day` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' ORDER BY `date` DESC;");
while ($row = mysql_fetch_array($sql, MYSQL_ASSOC)) {
	// get url
	$sql_url = mysql_query("SELECT `url` FROM `proto_stats_url_from` WHERE `id`=".$row['url_from_id']." LIMIT 1;");
	$row_url = mysql_fetch_array($sql_url, MYSQL_ASSOC);
	mysql_free_result($sql_url);
	// get hit_sum
	$sql_hit_sum = mysql_query("SELECT SUM(`hit`), SUM(`host`) FROM `proto_stats_url_from_day` WHERE `url_from_id`=".$row['url_from_id']." AND `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' LIMIT 1;");
	$row_sum = mysql_fetch_array($sql_hit_sum, MYSQL_ASSOC);
	mysql_free_result($sql_hit_sum);
	
	$sql_data_url_in = mysql_query("SELECT DISTINCT(`url_in_id`) FROM `proto_stats_url_from_day` WHERE `url_from_id`=".$row['url_from_id']." AND `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' ORDER BY `hit` DESC, `host` DESC;");
	unset($data_url_in);
	while ($row_data_url_in = mysql_fetch_array($sql_data_url_in, MYSQL_ASSOC)) if($row_data_url_in['url_in_id']) {
		// get url
		$sql_url_in = mysql_query("SELECT `url` FROM `proto_stats_url_in` WHERE `id`=".$row_data_url_in['url_in_id']." LIMIT 1;");
		$row_url_in = mysql_fetch_array($sql_url_in, MYSQL_ASSOC);
		mysql_free_result($sql_url_in);
		$data_url_in[] = $row_url_in['url'];
	}
	mysql_free_result($sql_data_url_in);
	
	$arUrlFrom[] = array(
		"url"			=> $row_url['url'],
		"hit_sum"		=> $row_sum['SUM(`hit`)'],
		"host_sum"		=> $row_sum['SUM(`host`)'],
		"popularity" 	=> round((sin(deg2rad(90*$host_day_mean/($row_sum['SUM(`hit`)']/$row_sum['SUM(`host`)'])))+1)*100/2,1),
		"data_url_in"	=> $data_url_in
	);
}
/*
print "<pre>";
print_r($arUrlFrom);
print "</pre>";
*/
mysql_free_result($sql);
usort($arUrlFrom,"protostats_sortPopularity");
if (is_array($arUrlFrom)) :
?>
<div class="statistics_box">
<h5>Переходы с других источников</h5>
<table class="list_items">
<tr>
<th align="left"><small>Источник</small></th>
<th><small>Эффективность</small></th>
<th><small>Переходов</small></th>
<th><small><u tool="Фиксируются уникальные посетители за день">Уникальных</u></small></th>
<th><small>Точки входа</small></th>
</tr>
<?
foreach ($arUrlFrom as $key=>$value) {
	$key%2 ? print "<tr column_key=\"".$key."\" class=\"row_odd\"" : print "<tr column_key=\"".$key."\" class=\"row_even\"";
	$key<10 ? print ">" : print " visible=\"hidden\" style=\"display: none;\">";
?>
<? if (strlen($value['url'])>40) { ?>
<td class="first"><u tool="<?=$value['url'];?>"><strong><?=substr($value['url'], 0, 40);?></strong></u></td>
<? } else { ?>
<td class="first"><a href="http://www.<?=$value['url'];?>" target="_blank"><strong><?=$idna_convert->decode($value['url']);?></strong></a></td>
<? } ?>
<td align="right" class="dark"><?=$value['popularity'];?>%</td>
<td align="right"><?=$value['hit_sum'];?></td>
<td align="right"><?=$value['host_sum'];?></td>
<td class="last"><small><? foreach ($value['data_url_in'] as $url) print "<a href=\"".$url."\" target=\"_blank\">".$url."</a><br>";?></small></td>
</tr>
<? } ?>
<? $key%2 ? print "<tr class=\"row_even\" colspan=\"3\">" : print "<tr class=\"row_odd\" colspan=\"3\">"; ?>
<td colspan="6" class="first last dark">
<a href="javascript:void(0)" onClick="LoadNextPage(this,'url_from')" class="invert_face" id="linkNext_url_from"><small></small></a>
</td>
</tr>
</table>
</div>
<? endif; ?>
<? /*////////////////////////////////////////////////////////*/ ?>

<br clear="all"><br>

</div>
<div class="corner_lb"><div class="corner_rb"></div></div></div>
<!-- // content -->
</div>
</body>
</html>
