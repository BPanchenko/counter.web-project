<?
header("Content-Type: text/xml; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
require_once('../files/db_connect.php');
require_once('../files/functions.php');

if ($_GET['ps'] && $_GET['pe']) {
	$DAY_START = urldecode($_GET['ps']);
	$DAY_END = urldecode($_GET['pe']);
} else {
	$DAY_START = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d")-31, date("Y")));
	$DAY_END = date("Y-m-d"." 23:59:59");
}

$sql = mysql_query("SELECT * FROM `proto_stats_day` WHERE `date`>='".$DAY_START."%' AND `date`<='".$DAY_END."%' ORDER BY `id` ASC");
print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"."\n"."<main>";
$a=1;
while ($row = mysql_fetch_array($sql, MYSQL_ASSOC)) {
	if ($row['host']==1) $str_host = $row['host'].' посетитель';
	if ($row['host']>1 && $row['host']<5) $str_host = $row['host'].' посетителя';
	if ($row['host']>=5) $str_host = $row['host'].' посетителей';
?>
<point><x value="<?=$a;?>"><?=strip_tags(protostats_convert_date($row['date']));?></x><y value="<?=$row['host'];?>"><?=$str_host;?></y></point>
<?
	$a++;
}
mysql_free_result($sql);
?>
</main>