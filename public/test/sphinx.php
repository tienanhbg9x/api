<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 7/31/2018
 * Time: 4:43 PM
 */

use Foolz\SphinxQL\SphinxQL;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/../../bootstrap/app.php';
$results = DB::select("SELECT * FROM queue_classifieds
                       STRAIGHT_JOIN classifieds ON (id = cla_id)
                       LIMIT 1");
//$results = collect($results);

//dd($results);
foreach($results as $row){
    //_debug($row);
    $keyword = $row->cla_address;
    //$keyword = 'Dự án Hà Nội Homeland, Phường Thượng Thanh, Long Biên, Hà Nội';
    //_debug($row);exit();
    echo $keyword . '<hr>';
    // create a SphinxQL Connection object to use with SphinxQL
    $arrLoc = ["catid" => $row->cla_cat_id];
    //*
    $myDetect = new \Toanld\Location\DetectLocation($keyword);
    $arrLoc["citid"] = $myDetect->getCity();
    $arrLoc["disid"] = $myDetect->getDistrict();
    $arrLoc["wardid"] = $myDetect->getWard();
    $arrLoc["streetid"] = $myDetect->getStreet();
    $arrLoc["projid"] = $myDetect->getProject($row->cla_title);

    _debug($arrLoc);
    $update = [];

    $update["cla_citid"] = $arrLoc["citid"];
    $update["cla_disid"] = $arrLoc["disid"];
    $update["cla_wardid"] = $arrLoc["wardid"];
    $update["cla_streetid"] = $arrLoc["streetid"];
    $update["cla_projid"] = $arrLoc["projid"];
    //*/
    $links = new \Toanld\MakeLink\MakeLink($arrLoc);

    $update["cla_linid"] = $links->createLink();
    _debug($update);

    DB::table('classifieds')
    ->where('cla_id',$row->cla_id)
    ->update($update);
    DB::table('queue_classifieds')
    ->where('id',$row->cla_id)
    ->delete();
    //echo '<meta http-equiv="refresh" content="0.05">';
}
DB::disconnect('foo');
app()->make("SphinxConnect")->close();