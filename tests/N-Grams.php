<?php

function pre_print_r($var){
  echo "<pre>";
	print_r($var);
	echo "</pre>";
}

function NgramsSentence($sentence,$n =2){
    $ngrams = array();
    $search = array('/','\\',':',';','!','@','#','$','%','^','*','(',')','_','+','=','|','{','}','[',']','"',"'",'<','>',',','?','~','`','&',' ','.');
    $sentence = str_replace($search,  " ", $sentence);
    for($i = 0; $i < 5; $i++) $sentence = str_replace("  "," ",$sentence);
    $sentence = mb_strtolower($sentence,"UTF-8");
    $arrWord = explode(" ",$sentence);
    $len = count($arrWord);
    for($i=0;$i+$n<$len;$i++){
        $string = [];
        for($j =0; $j < $n; $j++) $string[] = $arrWord[$j+$i];
        $ngrams[$i]= implode("_", $string);
    }
    return implode(" ",$ngrams);
}

function NgramsLocation($sentence){
    $ngrams = array();
    $search = array('/','\\',':',';','!','@','#','$','%','^','*','(',')','_','+','=','|','{','}','[',']','"',"'",'<','>','?','~','`','&',' ','.');
    $sentence = str_replace($search,  " ", $sentence);
    for($i = 0; $i < 5; $i++) $sentence = str_replace("  "," ",$sentence);
    $sentence = mb_strtolower($sentence,"UTF-8");
    $sentence = explode(",",$sentence);
    foreach ($sentence as $key => $value) $sentence[$key] = str_replace(" ","_",trim($value));
    $sentence = implode(" ",$sentence);
    //$sentence = str_replace(","," ",$sentence);
    return $sentence;
}

$string = "Đường Nguyên Hồng, Đống Đa, Hà Nội";
pre_print_r(NgramsSentence($string,1));
pre_print_r(NgramsSentence($string,2));
pre_print_r(NgramsSentence($string,3));
pre_print_r(NgramsLocation($string));