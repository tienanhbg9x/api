<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 10/22/2018
 * Time: 2:22 PM
 */
set_time_limit(0);
ini_set('memory_limit', '1024M');

include (dirname(__FILE__) . "/../../bootstrap/app.php");
$pathSave = $file_path = storage_path('train/');
try {
    $dbh = new PDO('mysql:host=' . env('DB_HOST') . ';dbname=' . env("DB_DATABASE") . '', env('DB_USERNAME'), env('DB_PASSWORD'),array(
        PDO::ATTR_TIMEOUT => 200000,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
    ));
    $dbh->exec("set names utf8");
    $arrCat = [];
    foreach($dbh->query('SELECT cat_id,cat_name FROM categories_multi') as $row) {
        $arrCat[] = $row;
    }
    file_put_contents($pathSave . "categories.json",json_encode($arrCat,JSON_UNESCAPED_UNICODE));
    $dbh = null;
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}