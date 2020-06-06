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
    foreach($dbh->query('SELECT cla_title,cla_teaser,cla_cat_id FROM classifieds') as $row) {
        $line = "title__" . $row["cla_cat_id"] . "__" . $row["cla_title"] . "\n";
        file_put_contents($pathSave . "title_" . $row["cla_cat_id"] . ".txt",$line, FILE_APPEND);
        $line = "description__" . $row["cla_cat_id"] . "__" . str_replace([chr(9),chr(10),chr(13)],"",$row["cla_teaser"]) . "\n";
        file_put_contents($pathSave . "description_" . $row["cla_cat_id"] . ".txt",$line, FILE_APPEND);
    }
    $dbh = null;
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
