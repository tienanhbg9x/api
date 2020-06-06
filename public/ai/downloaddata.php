<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 8/27/2018
 * Time: 3:05 PM
 */
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/../../bootstrap/app.php';
$html = file_get_contents('http://www.finanzen.net/aktien/DAX-Realtimekurse');
$table = explode('<table class="table table-vertical-center">',$html);
$table = explode('</table>',$table[1]);
$table = explode('</thead>',$table[0]);
$rows = explode('<tr>',$table[1]);
unset($rows[0]);
$rows = array_slice($rows,0,31);
foreach($rows AS $row){
	$cols = explode('',$row);

	$name = utf8_encode(strip_tags($cols[1]));
	$lastday = toNumber(strip_tags($cols[3]));
	$bid = toNumber(strip_tags($cols[4]));
	$ask = toNumber(strip_tags($cols[5]));
	$percent = toNumber(strip_tags($cols[6]));
	$sql = "INSERT INTO dax30(name,lastday,bid,ask,percent,timestamp)VALUES(?,?,?,?,?,NOW())";
	//$stmt = $db->prepare($sql);
	//$stmt->bind_param('sdddd',$name,$lastday,$bid,$ask,$percent);
	//$stmt->execute();
	//$stmt->close();

	echo $name.': Last Day: '.$lastday.', Bid: '.$bid.', Ask: '.$ask.', Percent: '.$prozent."<br>";
}
function toNumber($n){
	return trim(str_replace('%','',str_replace(',','.',str_replace('.','',$n))));
}