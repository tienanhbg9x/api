<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 3/1/2019
 * Time: 7:57 PM
 */
set_time_limit(0);
ini_set('memory_limit', '1024M');

include (dirname(__FILE__) . "/../../bootstrap/app.php");
$mySphinx = new \Foolz\SphinxQL\SphinxQL()