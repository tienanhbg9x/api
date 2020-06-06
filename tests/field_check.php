<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 22/11/2018
 * Time: 16:02
 */

$cla_field = [
    'cla_title' => 0,
    'cla_cat_id' => 0,
    'cla_cit_id' => 0,
    'cla_dis_id' => 0,
    'cla_ward_id' => 0,
    'cla_street_id' => 0,
    'cla_proj_id' => 0,
    'cla_mobile' => 0,
    'cla_description'  => 0,
    'cla_teaser' => 0,
    'cla_price' => 0,
    'cla_picture' => 0,
    'cla_list_acreage' => 0,
    'cla_list_price' => 0,
    'cla_list_badroom' => 0,
    'cla_list_toilet' => 0,
    'cla_feature' => 0,
    'cla_address' => 0,
    'cla_phone' => 0,
    'cla_email' => 0,
    'cla_contact_name' => 0
];

$i = 0;
foreach ($cla_field as $key=>$value){

    $cla_field[$key] = pow(2,$i);
    $i++;
}
print_r($cla_field);
$sum = 0;
foreach ($cla_field as $value){
    $sum +=$value;
}
print_r($sum);