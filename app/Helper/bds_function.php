<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 3/31/2017
 * Time: 1:32 PM
 */

function cleanRewriteAccent($string)
{
    $string = mb_strtolower($string, "UTF-8");
    $string = convertToUnicode($string);
    $string = mb_convert_encoding($string, "UTF-8", "UTF-8");
    $string = str_replace("?", "", trim($string));
    $string = str_replace("tr/m2", "triệu một m2", trim($string));
    $string = trim(preg_replace("/[^A-Za-z0-9àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/i", " ", $string));
    $string = mb_convert_encoding($string, "UTF-8", "UTF-8");
    $string = trim(preg_replace("/[^A-Za-z0-9àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/i", " ", $string));
    $string = removeEmoji($string);
    $string = mb_convert_encoding($string, "UTF-8", "UTF-8");
    $string = str_replace("?", "", trim($string));
    $string = str_replace(" ", "-", trim($string));
    $string = str_replace("--", "-", $string);
    $string = str_replace("--", "-", $string);
    $string = str_replace("--", "-", $string);
    $string = str_replace("--", "-", $string);
    $string = str_replace("--", "-", $string);
    return $string;
}

function showPrice($price)
{
    return $price;
}

/**
 * Ham insert vao sphinx
 */
function updateToSphinxClassified($cla_id = 0)
{
    $cla_id = intval($cla_id);
    $db_select = new db_query("SELECT * FROM classifieds WHERE cla_id = " . $cla_id . " LIMIT 1");
    if ($row = mysql_fetch_assoc($db_select->result)) {
        $cla_title = replaceSphinxMQ(cleanKeywordSearch($row["cla_title"]));
        $cla_address = replaceSphinxMQ(cleanKeywordSearch($row["cla_address"]));
        $cla_mobile = replaceSphinxMQ(cleanKeywordSearch($row["cla_mobile"]));
        $cla_phone = replaceSphinxMQ(cleanKeywordSearch($row["cla_phone"]));
        $cla_email = replaceSphinxMQ(cleanKeywordSearch($row["cla_email"]));
        $cla_contact_name = replaceSphinxMQ(cleanKeywordSearch($row["cla_contact_name"]));
        $cla_search = replaceSphinxMQ(cleanKeywordSearch($row["cla_search"]));
        $cla_cat_id = intval($row["cla_cat_id"]);
        $cla_city_id = intval($row["cla_city_id"]);
        $cla_dis_id = intval($row["cla_dis_id"]);
        $cla_street_id = intval($row["cla_street_id"]);
        $cla_proj_id = intval($row["cla_proj_id"]);
        $cla_date = intval($row["cla_date"]);
        $cla_expire = intval($row["cla_expire"]);
        $cla_use_id = intval($row["cla_use_id"]);
        $cla_vg_id = intval($row["cla_vg_id"]);
        $cla_price = doubleval($row["cla_price"]);
        $cla_lat = doubleval($row["cla_lat"]);
        $cla_lng = doubleval($row["cla_lng"]);
        $cla_list_acreage = "(" . $row["cla_list_acreage"] . ")";
        $cla_list_price = "(" . $row["cla_list_price"] . ")";
        $cla_list_badroom = "(" . $row["cla_list_badroom"] . ")";
        $cla_list_toilet = "(" . $row["cla_list_toilet"] . ")";
        $cla_list_cat = "()";
        $db_ex = new db_execute_sphinx("REPLACE INTO bds_rt_classifieds(id,cla_title,cla_address,cla_mobile,cla_phone,cla_email,cla_contact_name,cla_search,cla_cat_id,cla_city_id,cla_dis_id,cla_street_id,cla_proj_id,cla_date,cla_expire,cla_use_id,cla_vg_id,cla_price,cla_lat,cla_lng,cla_list_acreage,cla_list_price,cla_list_badroom,cla_list_toilet)
                                                               VALUES($cla_id,'$cla_title','$cla_address','$cla_mobile','$cla_phone','$cla_email','$cla_contact_name','$cla_search',$cla_cat_id,$cla_city_id,$cla_dis_id,$cla_street_id,$cla_proj_id,$cla_date,$cla_expire,$cla_use_id,$cla_vg_id,$cla_price,$cla_lat,$cla_lng,$cla_list_acreage,$cla_list_price,$cla_list_badroom,$cla_list_toilet)");
    }
    unset($db_select);
}

/**
 * Kiểm tra những trường đã thành công
 */
function bdsCheckFieldSuccess($arrayVars = array())
{
    $arrayFieldCheck = array(
        "cla_title" => pow(2, 0)
    , "cla_cat_id" => pow(2, 1)
    , "cla_cit_id" => pow(2, 2)
    , "cla_dis_id" => pow(2, 3)
    , "cla_street_id" => pow(2, 4)
    , "cla_proj_id" => pow(2, 5)
    , "cla_price" => pow(2, 6)
    , "cla_address" => pow(2, 7)
    , "cla_date" => pow(2, 8)
    , "cla_phone" => pow(2, 9)
    , "cla_email" => pow(2, 10)
    , "cla_teaser" => pow(2, 11)
    , "cla_picture" => pow(2, 12)
    , "cla_list_acreage" => pow(2, 13)
    , "cla_list_price" => pow(2, 15)
    , "cla_list_badroom" => pow(2, 16)
    , "cla_list_toilet" => pow(2, 17)
    , "cla_lat" => pow(2, 18)
    , "cla_lng" => pow(2, 19)
    , "cla_description" => pow(2, 20)
    );
    $cla_fields_check = 0;
    foreach ($arrayFieldCheck as $key => $val) {
        if (isset($arrayVars[$key]) && $arrayVars[$key] != "" && $arrayVars[$key] != "0") {
            $cla_fields_check += $val;
        }
    }
    return $cla_fields_check;
}

function getTeaser($string)
{
    $string = convertToUnicode($string);
    $string = removeHTML($string);
    $string = replace_double_space($string);
    $string = cut_string($string, 500, "");
    return $string;
}

function searchPhraseInContent($keyword, $content)
{

    $keyword = cleanKeywordSearch($keyword);
    $content = cleanKeywordSearch($content);

    $arrayReturn = array();
    $arrayReturn["total"] = 0;
    $arrayReturn["found"] = 0;
    $arrayReturn["percent"] = 0;
    $arrayReturn["total_percent"] = 0;
    if ($keyword == "" || $content == "") return $arrayReturn;
    $keyword = explode(" ", $keyword);
    $content = explode(" ", $content);
    $content = array_filter($content);
    $keyword = array_filter($keyword);

    foreach ($keyword as $key => $val) {
        if (strlen($val) <= 2) unset($keyword[$key]);
    }
    foreach ($content as $key => $val) {
        if (strlen($val) <= 2) unset($content[$key]);
    }
    $total = count($keyword);
    $arrayReturn["total"] = $total;
    $arrayReturn["found"] = 0;
    foreach ($keyword as $word) {
        if (in_array($word, $content)) {
            $arrayReturn["found"]++;
        }
    }
    //print_r($arrayReturn);exit();
    if (count($content) > $total) {
        if ($arrayReturn["total"] > 0) $arrayReturn["percent"] = $arrayReturn["found"] / $arrayReturn["total"] * 100;
    } else {
        if (count($content) > 0) $arrayReturn["percent"] = $arrayReturn["found"] / count($content) * 100;
    }
    if (count($content) > 0) $arrayReturn["total_percent"] += ($arrayReturn["found"] / count($content) * 100);
    if ($arrayReturn["total"] > 0) $arrayReturn["total_percent"] += ($arrayReturn["found"] / $arrayReturn["total"] * 100);
    return $arrayReturn;
}

// Tính khoảng cách giữa 2 thằng từ lat lon
function distanceGeoPoints($lat1, $lon1, $lat2, $lon2, $return = "auto")
{

    if (intval($lat1) === 0 || intval($lon1) === 0 || intval($lat2) === 0 || intval($lon2) === 0) {
        return NULL;
    }

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $km = ($miles * 1.609344);

    if ($return == "m") return intval($km * 1000);

    if ($km < 1) {
        return '~' . intval($km * 1000) . 'm';
    }

    return '~' . intval($km) . 'km';
}

function Spinner($string)
{
    $arrReplace = [];
    $arrIreplace = ["DT" => "diện tích", "MT" => "mặt tiền", "HXH" => "hẽm xe hơi", "Cho thuê VP" => "Cho thuê văn phòng", "Cho thuê VP" => "Cho thuê văn phòng", "TL" => "có thương lượng", "TTTM" => "trung tâm thương mại", "CĐT" => "chủ đầu tư", "KĐT" => "khu đô thị", "ct" => 'cho thuê', "SĐCC" => "căn hộ chung cư", "ĐTM" => "đô thị mới", "sđcc" => "sổ đỏ chính chủ"];
    $string = replace_double_space($string);
    $string = convertToUnicode($string);
    foreach ($arrReplace as $key => $value) {
        $string = str_replace($key, $value, $string);
    }
    foreach ($arrIreplace as $key => $value) {
        $string = str_ireplace($key, $value, $string);
    }
    $string = preg_replace("/(dt :|dt:|dt)([0-9\.\s\,]{0,5})(m2|mét vuông|m²|m)/si", " Diện tích: $2$3", $string);
    $string = replace_double_space($string);
    $string = preg_replace("/(sđt :|sđt:|sđt|lh:|lh|LH ngay|gọi)([0-9\.\s\,]{10,13})/si", " Liên hệ:$2", $string);
    $string = replace_double_space($string);
    $string = preg_replace("/([0-9\.\,\s]{1,4})(x|\*)([\.\,]{1,4})(m)/si", " Diện tích:$1x$3m", $string);
    $string = replace_double_space($string);
    //phần giá
    $string = preg_replace("/(giá :|giá)([0-9\.\,\s]{1,10})(tỷ|triệu|tr[\s\.\,]|ty)/si", " giá bán$2$3", $string);
    $string = replace_double_space($string);
    $string = preg_replace("/([0-9\.\,\s]{1,10})(tr\/tháng)/si", " $1 triệu/tháng", $string);
    $string = replace_double_space($string);
    $string = preg_replace("/([0-9]{1,10})(tr)/si", " $1 triệu", $string);
    $string = replace_double_space($string);
    $string = preg_replace("/([0-9]{1,2})(T[\s\.\,])/s", " $1 tầng", $string);
    $string = preg_replace("/([0-9]{1,2})(L[\s\.\,])/s", " $1 lầu", $string);
    $string = preg_replace("/([0-9]{1,2})(H[\s\.\,])/s", " $1 hầm", $string);
    $string = preg_replace("/([0-9]{1,2})(pn[\s\.\,])/si", " $1 phòng ngủ", $string);
    $string = preg_replace("/([0-9]{1,2})(wc[\s\.\,])/si", " $1 vệ sinh", $string);
    $string = replace_double_space($string);
    return $string;
}


function getAcreage($string, $return_arr = false)
{
    $string = str_replace("&nbsp", " ", $string);
    $string = removeHTML($string);
    $string = replace_double_space($string);
    $string = mb_strtolower($string, "UTF-8");
    $string = '<span>' . $string . '</span>';
    preg_match_all("/([\s\>])([0-9\.\s\,]{0,5})(m2|mét vuông|m²)([\s\<\.\,])/i", $string, $result);
    $arrrayResult = array();
    foreach ($result[2] as $val) {
        $val = intval($val);
        if ($val > 500 || $val < 15) continue;
        $arrrayResult[$val] = intval($val);
    }
    if(count($arrrayResult) > 5) $arrrayResult = array_slice($arrrayResult,5);
    if (!$return_arr) {
        return implode(",", $arrrayResult);
    }
    return $arrrayResult;
}


function getBadRoom($string, $return_arr = false)
{
    $string = str_replace("&nbsp", " ", $string);
    $string = removeHTML($string);
    $string = replace_double_space($string);
    $string = mb_strtolower($string, "UTF-8");
    preg_match_all("/([\s]|\>)([0-9\s]{1,2})(pn|phòng ngủ)([\s\<\.\,])/i", $string, $result);
    $arrrayResult = array();
    foreach ($result[2] as $val) {
        $val = intval($val);
        if ($val > 10) continue;
        $arrrayResult[$val] = $val;
    }
    if (!$return_arr) {
        return implode(",", $arrrayResult);
    }
    return $arrrayResult;
}

function getToilet($string, $return_arr = false)
{
    $string = removeHTML($string);
    $string = replace_double_space($string);
    $string = mb_strtolower($string, "UTF-8");
    $arrayResult = array();
    preg_match_all("/([\s]|\>)([0-9\s]{1,2})(wc|vệ sinh|nhà vệ sinh)([\s\<\.\,])/i", $string, $result);
    $arrrayResult = array();
    foreach ($result[2] as $val) {
        $val = intval($val);
        if ($val > 10) continue;
        $arrrayResult[$val] = $val;
    }
    if (!$return_arr) {
        return implode(",", $arrrayResult);
    }
    return $arrrayResult;
}

function getPrice($string, $return_arr = false)
{
    $string = removeHTML($string);
    $string = replace_double_space($string);
    $string = mb_strtolower($string, "UTF-8");
    $arrayResult = array();
    $string = '<span>' . $string . '</span>';
    preg_match_all("/([\s]|\>)([0-9\s\.\,]{1,5})(tỷ)([\s\<\.\,\&])/i", $string, $ty);
    preg_match_all("/([\s]|\>)([0-9\s\.\,]{1,6})(triệu|tr)([\s\<\.\,\/\&])/i", $string, $trieu);
    //print_r($ty);
    if (isset($ty[2])) {
        foreach ($ty[2] as $price) {
            $price = str_replace(",", ".", $price);
            $price = doubleval($price);
            if ($price < 100) {
                $price = $price * 1000000000;
                $arrayResult[strval($price)] = $price;
            }
        }
    }
    //print_r($trieu);
    if (isset($trieu[2])) {
        foreach ($trieu[2] as $price) {
            $price = doubleval($price);
            $price = $price * 1000000;
            $arrayResult[strval($price)] = $price;
        }
    }
    if(count($arrayResult) > 5) $arrayResult = array_slice($arrayResult,5);
    if (!$return_arr) {
        return implode(",", $arrayResult);
    }
    return $arrayResult;
}


function insertInvestor($inv_name, $inv_picture, $inv_address, $inv_phone, $inv_email, $inv_website, $inv_teaser, $inv_description)
{


    $inv_name = cleanUpdataSQL($inv_name);
    $inv_picture = cleanUpdataSQL($inv_picture);
    $inv_address = cleanUpdataSQL($inv_address);
    $inv_phone = cleanUpdataSQL($inv_phone);
    $inv_email = cleanUpdataSQL($inv_email);
    $inv_website = cleanUpdataSQL($inv_website);
    $inv_teaser = cleanUpdataSQL($inv_teaser);
    $inv_description = cleanUpdataSQL($inv_description);
    if ($inv_name == "" || $inv_address == "") return 0;
    $db_select = new db_query("SELECT inv_id FROM investors WHERE inv_name = '$inv_name' LIMIT 1");
    if ($inv = mysqli_fetch_assoc($db_select->result)) {
        return $inv["inv_id"];
    } else {
        $db_ex = new db_execute_return();
        $inv_id = $db_ex->db_execute("INSERT INTO investors(inv_name,inv_picture,inv_address,inv_phone,inv_email,inv_website,inv_teaser,inv_description)
                                                VALUES('$inv_name','$inv_picture','$inv_address','$inv_phone','$inv_email','$inv_website','$inv_teaser','$inv_description')");
        return $inv_id;
    }
    //*/
}

function cleanUpdataSQL($string)
{
    $string = strval($string);
    $string = str_replace(array(chr(9), chr(10), chr(13)), "", $string);
    $string = replaceMQ(replaceFCK(trim($string), 1));
    return $string;
}


function updateProject($proj_id, $proj_title, $proj_inv_id, $proj_inv_name, $proj_address, $proj_picture, $proj_video, $proj_teaser, $proj_distribution, $proj_overview, $proj_construction, $proj_cat_name, $proj_price_from)
{
    $proj_title = cleanUpdataSQL($proj_title);
    $proj_inv_name = cleanUpdataSQL($proj_inv_name);
    $proj_address = cleanUpdataSQL($proj_address);
    $proj_picture = cleanUpdataSQL($proj_picture);
    $proj_video = cleanUpdataSQL($proj_video);
    $proj_teaser = cleanUpdataSQL($proj_teaser);
    $proj_distribution = cleanUpdataSQL($proj_distribution);
    $proj_overview = cleanUpdataSQL($proj_overview);
    $proj_construction = cleanUpdataSQL($proj_construction);
    $proj_cat_name = cleanUpdataSQL($proj_cat_name);
    $proj_id = intval($proj_id);
    $proj_inv_id = intval($proj_inv_id);
    $proj_price_from = doubleval($proj_price_from);
    $proj_active = 1;
    $proj_update = time();
    $proj_rewrite = '';

    //lấy thông tin địa chỉ
    $analyzeLocation = new analyzeLocation();
    $analyzeLocation->setAddres($proj_address);
    $arrayLocation = $analyzeLocation->getLocationId();
    $proj_cat_id = $analyzeLocation->getCatId($proj_cat_name);
    $proj_cit_id = $arrayLocation["city"];
    $proj_dis_id = $arrayLocation["district"];
    $proj_ward_id = $arrayLocation["ward"];
    $proj_street_id = $arrayLocation["street"];

    $arrayData = array("iProj" => $proj_id, "iCat" => $proj_cat_id, "module" => "project", "type" => "project");
    $rew = createRewriteNotExists($proj_title, "projects", $proj_id, $arrayData, $proj_cat_id, $proj_cit_id, $proj_dis_id, $proj_ward_id, $proj_street_id);
    $proj_rewrite = '';
    if (isset($rew["rew_rewrite"])) {
        $proj_rewrite = $rew["rew_rewrite"];
    } else {
        $rew = createRewriteNotExists($proj_title . " " . $proj_id, "projects", $proj_id, $arrayData, $proj_cat_id, $proj_cit_id, $proj_dis_id, $proj_ward_id, $proj_street_id);
        if (isset($rew["rew_rewrite"])) {
            $proj_rewrite = $rew["rew_rewrite"];
        }
    }
    $db_ex = new db_execute("UPDATE projects SET proj_street_id=$proj_street_id,proj_ward_id=$proj_ward_id,proj_dis_id=$proj_dis_id,proj_cit_id=$proj_cit_id,proj_cat_id=$proj_cat_id,proj_rewrite='$proj_rewrite',proj_price_from = $proj_price_from,proj_update = $proj_update,proj_title = '$proj_title',proj_inv_name = '$proj_inv_name',proj_inv_id=$proj_inv_id,proj_address='$proj_address',proj_picture = '$proj_picture',proj_video='$proj_video',proj_teaser='$proj_teaser',proj_distribution='$proj_distribution',proj_overview='$proj_overview',proj_construction='$proj_construction',proj_cat_name='$proj_cat_name',proj_active = $proj_active WHERE proj_id = $proj_id");

}

function getUrlPicture($filename, $width = 0, $source = false)
{
    if (trim($filename) == '') {
        return 'https://sosanhnha.com/assets/images/noimage.png';
    }
    $timefile = intval($filename);
    if ($width > 0) {
        return "https://media.sosanhnha.com/thumb/" . $width . date("/Y/m/", $timefile) . $filename;
    }
    return "https://media.sosanhnha.com/" . (($source) ? "batdongsan" : "full") . "/" . date("Y/m/d/", $timefile) . $filename;
}

function updateProjectDescription($proj_id, $proj_intro, $proj_long_keyword, $proj_position, $proj_infrastructure, $proj_template, $proj_template_picture, $proj_progress, $proj_support_finance, $proj_url)
{
    $proj_intro = cleanUpdataSQL($proj_intro);
    $proj_long_keyword = cleanUpdataSQL($proj_long_keyword);
    $proj_position = cleanUpdataSQL($proj_position);
    $proj_infrastructure = cleanUpdataSQL($proj_infrastructure);
    $proj_template = cleanUpdataSQL($proj_template);
    $proj_template_picture = cleanUpdataSQL($proj_template_picture);
    $proj_progress = cleanUpdataSQL($proj_progress);
    $proj_support_finance = cleanUpdataSQL($proj_support_finance);
    $proj_url = cleanUpdataSQL($proj_url);
    $db_ex = new db_execute("UPDATE projects_description SET proj_intro ='$proj_intro',proj_long_keyword='$proj_long_keyword',proj_position='$proj_position',proj_infrastructure='$proj_infrastructure',proj_template='$proj_template',proj_template_picture='$proj_template_picture',proj_progress='$proj_progress',proj_support_finance='$proj_support_finance',proj_url='$proj_url' WHERE proj_proj_id = $proj_id");
}

function genAvatar($fb_id)
{
    if ($fb_id == '') {
        return "https://sosanhnha.com/assets/images/no_avatar.png";
    }
    return '//graph.facebook.com/v2.4/' . $fb_id . '/picture?width=300&height=300';
}

function logoutUrl($url = '/')
{
    return '/pages/logout.php?url=' . base64_url_encode($url);
}

function bdsGetDuration($time)
{
    if ((time() - $time < 864000)&&($time <time())) {
        return getNiceDuration(time() - $time);
    } else {
        return date('d-m-Y, H:i:s', $time);
    }
}

function showDetailByPictures($arrayPictures = [], $title = '', $sourcePicture = 0)
{
    $htmlReturn = '';
    $flip = $sourcePicture == 1 ? '' : ' flip';
    $title = removeHTML(replaceMQ($title));
    $arrayPictures = arrayVal($arrayPictures);
    foreach ($arrayPictures as $key => $pic) {
        $pic_type = arrGetVal('type', $pic, 'photo');
        $filename = arrGetVal('filename', $pic, '');
        if ($filename == '') {
            $filename = arrGetVal('name', $pic, '');
        }
        $pic_teaser = arrGetVal('teaser', $pic, '');
        if ($pic_type == 'photo') {
            $alt = arrGetVal('teaser', $pic, $title . ' - ảnh ' . ($key+1));
            $htmlReturn .= '<span class="detail_photo' . $flip . '">
                            <img src="' . getUrlImageByRow($pic, 0, $sourcePicture) . '" alt="' . $alt . '"/>
                        </span>';

        }//if($pic_type == 'photo')
        if ($pic_type == 'video') {
            $htmlReturn .= '<span class="detail_video">
                            <embed src="https://www.youtube.com/embed/' . $filename . '"> </embed>
                        </span>';

        }//if($pic_type == 'photo')
        if ($pic_teaser != '') {
            $htmlReturn .= ' <p>' . $pic_teaser . '</p>';

        }
    }//foreach($arrayPictures as $pic)
    return $htmlReturn;
}


function textToHTMLCss($string){
    if(app("crawler")->isCrawler()) return $string;
    $arrConvert = [];
    $today = date('z',time());
    for($i = 48; $i < 55; $i++) $arrConvert[chr($i)] = '<span class="'.('s' . substr(md5(chr($i) . "_" . $today),-12)).'"></span>';
    for($i = 97; $i < 105; $i++) $arrConvert[chr($i)] = '<span class="'.('s' . substr(md5(chr($i) . "_" . $today),-12)).'"></span>';

    $arrString  = str_split($string);
    $strReturn = '';
    foreach($arrString as $str){
        if(isset($arrConvert[$str])){
            $strReturn .= $arrConvert[$str];
        }else{
            $strReturn .= $str;
        }
    }
    return $strReturn;
}
function showStyleTextToCSS(){
    $today = date('z',time());
    $strReturn = '';
    $arrConvert = [];
    for($i = 48; $i < 58; $i++) $arrConvert[chr($i)] = chr($i);
    for($i = 97; $i < 105; $i++) $arrConvert[chr($i)] = chr($i);

    foreach($arrConvert as $key=>$value){

        $strReturn .= '.s'.substr(md5(($key) . "_" . $today),-12).'::before{ content:"'.$value.'";} ';
    }
    return $strReturn;
}

function bdsPicture($name,$type=0,$arrInfo=[]){

}