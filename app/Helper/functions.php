<?php

//use App\helpers\html_cleanup;

function createRewriteNoaccent($string)
{
    $string = convertToUnicode($string);
    $string = mb_strtolower($string, "UTF-8");
    $string = removeAccent($string);
    $string = preg_replace("/[^A-Za-z0-9\-]/", " ", $string);
    $string = replace_double_space($string);
    $string = trim($string);
    $string = str_replace(" ", "-", $string);
    $string = str_replace("--", "-", $string);
    $string = str_replace("--", "-", $string);
    $string = str_replace("--", "-", $string);

    return $string;
}

/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 25/07/2018
 * Time: 09:57
 */
/**
 * Created by Lê Đình Toản
 * User: dinhtoan1905@gmail.com
 * Date: 8/9/2019
 * Time: 11:21 AM
 * @param $coords
 * @return array
 * $coords[]=array('lat' => '53.344104','lng'=>'-6.2674937');
 * $coords[]=array('lat' => '51.5081289','lng'=>'-0.128005');
 */
function getCenter($coords)
{
    $count_coords = count($coords);
    $xcos = 0.0;
    $ycos = 0.0;
    $zsin = 0.0;

    foreach ($coords as $lnglat) {
        $lat = $lnglat['lat'] * pi() / 180;
        $lon = $lnglat['lon'] * pi() / 180;

        $acos = cos($lat) * cos($lon);
        $bcos = cos($lat) * sin($lon);
        $csin = sin($lat);
        $xcos += $acos;
        $ycos += $bcos;
        $zsin += $csin;
    }

    $xcos /= $count_coords;
    $ycos /= $count_coords;
    $zsin /= $count_coords;
    $lon = atan2($ycos, $xcos);
    $sqrt = sqrt($xcos * $xcos + $ycos * $ycos);
    $lat = atan2($zsin, $sqrt);

    return array("lat" => ($lat * 180 / pi()), "lon" => ($lon * 180 / pi()));
}

function similar_word($string1, $string2)
{
    $string2 = str_replace(",", " ", $string2);
    $string1 = str_replace(",", " ", $string1);
    $string1 = cleanText($string1);
    $string2 = cleanText($string2);
    $string1 = explode(" ", $string1);
    $string2 = explode(" ", $string2);
    $result = array_intersect($string1, $string2);
    $total1 = count($string1);
    $total2 = count($result);
    if ($total2 <= 0) return 0;
    return ($total2 / $total1);
}

function NgramsSentence($sentence, $n = 2)
{
    $ngrams = array();
    $sentence = cleanText($sentence);
    $arrWord = explode(" ", $sentence);
    $len = count($arrWord);
    for ($i = 0; $i + $n < $len; $i++) {
        $string = [];
        for ($j = 0; $j < $n; $j++) $string[] = $arrWord[$j + $i];
        $ngrams[$i] = implode("_", $string);
    }
    return implode(" ", $ngrams);
}

function NgramsLocation($sentence)
{
    $ngrams = array();
    $sentence = cleanText($sentence);
    $sentence = explode(",", $sentence);
    foreach ($sentence as $key => $value) $sentence[$key] = str_replace(" ", "_", trim($value));
    $sentence = implode(" ", $sentence);
    //$sentence = str_replace(","," ",$sentence);
    return $sentence;
}

function createTextSearchNgram($string)
{
    $searchText = NgramsSentence($string, 1);
    $searchText .= " " . NgramsSentence($string, 2);
    $searchText .= " " . NgramsSentence($string, 3);
    $searchText .= " " . NgramsLocation($string);
    return $searchText;
}

function cleanText($sentence)
{
    $sentence = convertToUnicode($sentence);
    $search = array('/', '\\', ':', ';', '!', '-', '@', '#', '$', '%', '^', '*', '(', ')', '_', '+', '=', '|', '{', '}', '[', ']', '"', "'", '<', '>', '?', '~', '`', '&', ' ', '.');
    $sentence = str_replace($search, " ", $sentence);
    $sentence = replace_double_space($sentence);
    $sentence = mb_strtolower($sentence, "UTF-8");
    $sentence = str_replace("  ", " ", $sentence);
    $sentence = str_replace("  ", " ", $sentence);
    return $sentence;
}

function bdsEncode($string)
{
    if (empty($string)) return '';
    $string = base64_encode(json_encode($string));
    $string = str_replace("m", "| |", $string);
    $string = str_replace("M", ": :", $string);
    $string = str_replace("O", "{ }", $string);
    $string = str_replace("J", " ", $string);
    return $string;
}

function bdsDecode($string)
{
    if (empty($string)) return '';
    $string = str_replace("| |", "m", $string);
    $string = str_replace(": :", "M", $string);
    $string = str_replace("{ }", "O", $string);
    $string = str_replace(" ", "J", $string);
    $string = json_decode(base64_decode($string), true);
    return $string;
}


function replace_double_space($string, $char = " ")
{
    $i = 0;
    $max = 10;
    if ($char == "") return $string;
    while (mb_strpos($string, $char . $char, 0, "UTF-8") !== false) {
        $i++;
        $string = str_replace($char . $char, $char, $string);
        if ($i >= $max) break;
    }
    return trim($string);
}

function convertListIntToArray($string)
{
    $string = trim($string);
    $string = explode(",", $string);
    $arrayValue = [];
    foreach ($string as $val) {
        $val = intval($val);
        if ($val <= 0) continue;
        if (!in_array($val, $arrayValue)) $arrayValue[] = $val;
    }
    return $arrayValue;
}

function convertListFloatToArray($string)
{
    $string = trim($string);
    $string = explode(",", $string);
    $arrayValue = [];
    foreach ($string as $val) {
        $val = doubleval($val);
        if ($val <= 0) continue;
        if (!in_array($val, $arrayValue)) $arrayValue[] = $val;
    }
    return $arrayValue;
}


function getUrlPictures($filename, $width = 0, $source = false, $type = null)
{
    $image = bdsDecode($filename);
    if (isset($image[key($image)]['filename'])) {
        $images = [];
        foreach ($image as $data) {
            $filename = $data['filename'];
            if (trim($filename) == '') {
                return 'https://sosanhnha.com/assets/images/noimage.png';
            }
            $timefile = intval($filename);
            if ($width > 0) {
                $images['thumb'][] = "https://media.sosanhnha.com/thumb/" . $width . date("/Y/m/", $timefile) . $filename;
            }
            $images['full'][] = "https://media.sosanhnha.com/" . (($source) ? "batdongsan" : "full") . "/" . date("Y/m/d/", $timefile) . $filename;
        }
        $images['count_image'] = count($images['full']);
        return $images;
    } elseif ($image[key($image)]['name']) {
        $images = [];
        foreach ($image as $data) {
            $filename = $data['name'];
            if (trim($filename) == '') {
                return 'https://sosanhnha.com/assets/images/noimage.png';
            }
            $timefile = intval($filename);
            if ($width > 0) {
                $images['thumb'][] = "https://media.sosanhnha.com/thumb/" . $width . date("/Y/m/", $timefile) . $filename;
            }
            $images['full'][] = "https://media.sosanhnha.com/" . (($source) ? "batdongsan" : "full") . "/" . date("Y/m/d/", $timefile) . $filename;

        }
        $images['count_image'] = count($images['full']);
        return $images;
    } else {
        return ['full' => 'https://sosanhnha.com/assets/images/noimage.png', 'count_image' => 1];
    }
}

function getUrlPictureInvestor($filename, $width = 0, $source = false)
{
    $image = bdsDecode($filename);
    if (isset($image['filename'])) {
        $filename = $image['filename'];
    } else {
        $filename = '';
    }

    if (trim($filename) == '') {
        return 'https://sosanhnha.com/assets/images/noimage.png';
    }
    $timefile = intval($filename);
    if ($width > 0) {
        return "https://media.sosanhnha.com/thumb/" . $width . date("/Y/m/", $timefile) . $filename;
    }
    return "https://media.sosanhnha.com/" . (($source) ? "batdongsan" : "full") . "/" . date("Y/m/d/", $timefile) . $filename;
}

// print_r(getUrlPicture($image[0]['filename']));


if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

function cleanKeywordSearch($string)
{
    $string = removeHTML($string);
    $string = convertToUnicode($string);
    $string = strval($string);
    $string = str_replace(array(chr(9), chr(10), chr(13)), "", $string);
    $string = mb_strtolower($string, "UTF-8");
    $array_bad_word = array("?", "^", ",", ";", "*", "(", ")", "|", "!", "\\", "@");
    $string = str_replace($array_bad_word, " ", $string);
    $string = str_replace("  ", " ", $string);
    $string = str_replace("  ", " ", $string);
    $string = str_replace("  ", " ", $string);
    $string = str_replace("  ", " ", $string);
    $string = str_replace("  ", " ", $string);
    $string = str_replace("  ", " ", $string);
    $string = str_replace("  ", " ", $string);
    $string = str_replace("  ", " ", $string);
    $string = replaceSphinxMQ($string);
    return trim($string);
}

/**
 * removeAccent()
 *
 * @param mixed $mystring
 * @return
 */
function removeAccent($mystring)
{
    $marTViet = array(
        // Chữ thường
        "à", "á", "ạ", "ả", "ã", "â", "ầ", "ấ", "ậ", "ẩ", "ẫ", "ă", "ằ", "ắ", "ặ", "ẳ", "ẵ",
        "è", "é", "ẹ", "ẻ", "ẽ", "ê", "ề", "ế", "ệ", "ể", "ễ",
        "ì", "í", "ị", "ỉ", "ĩ",
        "ò", "ó", "ọ", "ỏ", "õ", "ô", "ồ", "ố", "ộ", "ổ", "ỗ", "ơ", "ờ", "ớ", "ợ", "ở", "ỡ",
        "ù", "ú", "ụ", "ủ", "ũ", "ư", "ừ", "ứ", "ự", "ử", "ữ",
        "ỳ", "ý", "ỵ", "ỷ", "ỹ",
        "đ", "Đ", "'",
        // Chữ hoa
        "À", "Á", "Ạ", "Ả", "Ã", "Â", "Ầ", "Ấ", "Ậ", "Ẩ", "Ẫ", "Ă", "Ằ", "Ắ", "Ặ", "Ẳ", "Ẵ",
        "È", "É", "Ẹ", "Ẻ", "Ẽ", "Ê", "Ề", "Ế", "Ệ", "Ể", "Ễ",
        "Ì", "Í", "Ị", "Ỉ", "Ĩ",
        "Ò", "Ó", "Ọ", "Ỏ", "Õ", "Ô", "Ồ", "Ố", "Ộ", "Ổ", "Ỗ", "Ơ", "Ờ", "Ớ", "Ợ", "Ở", "Ỡ",
        "Ù", "Ú", "Ụ", "Ủ", "Ũ", "Ư", "Ừ", "Ứ", "Ự", "Ử", "Ữ",
        "Ỳ", "Ý", "Ỵ", "Ỷ", "Ỹ",
        "Đ", "Đ", "'",
    );
    $marKoDau = array(
        /// Chữ thường
        "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a",
        "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e",
        "i", "i", "i", "i", "i",
        "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o",
        "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u",
        "y", "y", "y", "y", "y",
        "d", "D", "",
        //Chữ hoa
        "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A",
        "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E",
        "I", "I", "I", "I", "I",
        "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O",
        "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U",
        "Y", "Y", "Y", "Y", "Y",
        "D", "D", "",
    );

    return str_replace($marTViet, $marKoDau, $mystring);
}

/**
 * removeHTML()
 *
 * @param mixed $string
 * @return
 */
function removeHTML($string)
{
    $string = preg_replace('/<script.*?\>.*?<\/script>/si', ' ', $string);
    $string = preg_replace('/<style.*?\>.*?<\/style>/si', ' ', $string);
    $string = preg_replace('/<.*?\>/si', ' ', $string);
    $string = str_replace('&nbsp;', ' ', $string);

    return $string;
}

/**
 * removeLink()
 *
 * @param mixed $string
 * @return
 */
function removeLink($string)
{
    $string = preg_replace('/<a.*?\>/si', '', $string);
    $string = preg_replace('/<\/a>/si', '', $string);

    return $string;
}

function replaceSphinxMQ($str)
{
    $array_bad_word = array("?", "^", ",", ";", "*", "(", ")", "\\", "/");
    $str = str_replace($array_bad_word, " ", $str);
    $str = str_replace(array("\\", "'", '"'), array("", "\\'", ""), $str);
    return $str;
}

function convertToUnicode($string)
{
    $trans = array("á" => "á", "à" => "à", "ả" => "ả", "ã" => "ã", "ạ" => "ạ", "ă" => "ă", "ắ" => "ắ",
        "ằ" => "ằ", "ẳ" => "ẳ", "ẵ" => "ẵ", "ặ" => "ặ", "â" => "â", "ấ" => "ấ", "ầ" => "ầ", "ẩ" => "ẩ",
        "ậ" => "ậ", "ẫ" => "ẫ", "ó" => "ó", "ò" => "ò", "ỏ" => "ỏ", "õ" => "õ", "ọ" => "ọ", "ô" => "ô",
        "ố" => "ố", "ồ" => "ồ", "ổ" => "ổ", "ỗ" => "ỗ", "ộ" => "ộ", "ơ" => "ơ", "ớ" => "ớ", "ờ" => "ờ",
        "ở" => "ở", "ỡ" => "ỡ", "ợ" => "ợ", "ú" => "ú", "ù" => "ù", "ủ" => "ủ", "ũ" => "ũ", "ụ" => "ụ",
        "ư" => "ư", "ứ" => "ứ", "ừ" => "ừ", "ử" => "ử", "ự" => "ự", "ữ" => "ữ", "é" => "é", "è" => "è",
        "ẻ" => "ẻ", "ẽ" => "ẽ", "ẹ" => "ẹ", "ê" => "ê", "ế" => "ế", "ề" => "ề", "ể" => "ể", "ễ" => "ễ",
        "ệ" => "ệ", "í" => "í", "ì" => "ì", "ỉ" => "ỉ", "ĩ" => "ĩ", "ị" => "ị", "ý" => "ý", "ỳ" => "ỳ",
        "ỷ" => "ỷ", "ỹ" => "ỹ", "ỵ" => "ỵ", "đ" => "đ", "Á" => "Á", "À" => "À", "Ả" => "Ả", "Ã" => "Ã",
        "Ạ" => "Ạ", "Ă" => "Ă", "Ắ" => "Ắ", "Ằ" => "Ằ", "Ẳ" => "Ẳ", "Ẵ" => "Ẵ", "Ặ" => "Ặ", "Â" => "Â",
        "Ấ" => "Ấ", "Ầ" => "Ầ", "Ẩ" => "Ẩ", "Ậ" => "Ậ", "Ẫ" => "Ẫ", "Ó" => "Ó", "Ò" => "Ò", "Ỏ" => "Ỏ",
        "Õ" => "Õ", "Ọ" => "Ọ", "Ô" => "Ô", "Ố" => "Ố", "Ồ" => "Ồ", "Ổ" => "Ổ", "Ỗ" => "Ỗ", "Ộ" => "Ộ",
        "Ơ" => "Ơ", "Ớ" => "Ớ", "Ờ" => "Ờ", "Ở" => "Ở", "Ỡ" => "Ỡ", "Ợ" => "Ợ", "Ú" => "Ú", "Ù" => "Ù",
        "Ủ" => "Ủ", "Ũ" => "Ũ", "Ụ" => "Ụ", "Ư" => "Ư", "Ứ" => "Ứ", "Ừ" => "Ừ", "Ử" => "Ử", "Ữ" => "Ữ",
        "Ự" => "Ự", "É" => "É", "È" => "È", "Ẻ" => "Ẻ", "Ẽ" => "Ẽ", "Ẹ" => "Ẹ", "Ê" => "Ê", "Ế" => "Ế",
        "Ề" => "Ề", "Ể" => "Ể", "Ễ" => "Ễ", "Ệ" => "Ệ", "Í" => "Í", "Ì" => "Ì", "Ỉ" => "Ỉ", "Ĩ" => "Ĩ",
        "Ị" => "Ị", "Ý" => "Ý", "Ỳ" => "Ỳ", "Ỷ" => "Ỷ", "Ỹ" => "Ỹ", "Ỵ" => "Ỵ", "Đ" => "Đ",
        "&#225;" => "á", "&#224;" => "à", "&#7843;" => "ả", "&#227;" => "ã", "&#7841;" => "ạ", "&#259;" => "ă",
        "&#7855;" => "ắ", "&#7857;" => "ằ", "&#7859;" => "ẳ", "&#7861;" => "ẵ", "&#7863;" => "ặ", "&#226;" => "â",
        "&#7845;" => "ấ", "&#7847;" => "ầ", "&#7849;" => "ẩ", "&#7853;" => "ậ", "&#7851;" => "ẫ", "&#243;" => "ó",
        "&#242;" => "ò", "&#7887;" => "ỏ", "&#245;" => "õ", "&#7885;" => "ọ", "&#244;" => "ô", "&#7889;" => "ố",
        "&#7891;" => "ồ", "&#7893;" => "ổ", "&#7895;" => "ỗ", "&#7897;" => "ộ", "&#417;" => "ơ", "&#7899;" => "ớ",
        "&#7901;" => "ờ", "&#7903;" => "ở", "&#7905;" => "ỡ", "&#7907;" => "ợ", "&#250;" => "ú", "&#249;" => "ù",
        "&#7911;" => "ủ", "&#361;" => "ũ", "&#7909;" => "ụ", "&#432;" => "ư", "&#7913;" => "ứ", "&#7915;" => "ừ",
        "&#7917;" => "ử", "&#7921;" => "ự", "&#7919;" => "ữ", "&#233;" => "é", "&#232;" => "è", "&#7867;" => "ẻ",
        "&#7869;" => "ẽ", "&#7865;" => "ẹ", "&#234;" => "ê", "&#7871;" => "ế", "&#7873;" => "ề", "&#7875;" => "ể",
        "&#7877;" => "ễ", "&#7879;" => "ệ", "&#237;" => "í", "&#236;" => "ì", "&#7881;" => "ỉ", "&#297;" => "ĩ",
        "&#7883;" => "ị", "&#253;" => "ý", "&#7923;" => "ỳ", "&#7927;" => "ỷ", "&#7929;" => "ỹ", "&#7925;" => "ỵ",
        "&#273;" => "đ", "&#193;" => "Á", "&#192;" => "À", "&#7842;" => "Ả", "&#195;" => "Ã", "&#7840;" => "Ạ",
        "&#258;" => "Ă", "&#7854;" => "Ắ", "&#7856;" => "Ằ", "&#7858;" => "Ẳ", "&#7860;" => "Ẵ", "&#7862;" => "Ặ",
        "&#194;" => "Â", "&#7844;" => "Ấ", "&#7846;" => "Ầ", "&#7848;" => "Ẩ", "&#7852;" => "Ậ", "&#7850;" => "Ẫ",
        "&#211;" => "Ó", "&#210;" => "Ò", "&#7886;" => "Ỏ", "&#213;" => "Õ", "&#7884;" => "Ọ", "&#212;" => "Ô",
        "&#7888;" => "Ố", "&#7890;" => "Ồ", "&#7892;" => "Ổ", "&#7894;" => "Ỗ", "&#7896;" => "Ộ", "&#416;" => "Ơ",
        "&#7898;" => "Ớ", "&#7900;" => "Ờ", "&#7902;" => "Ở", "&#7904;" => "Ỡ", "&#7906;" => "Ợ", "&#218;" => "Ú",
        "&#217;" => "Ù", "&#7910;" => "Ủ", "&#360;" => "Ũ", "&#7908;" => "Ụ", "&#431;" => "Ư", "&#7912;" => "Ứ",
        "&#7914;" => "Ừ", "&#7916;" => "Ử", "&#7918;" => "Ữ", "&#7920;" => "Ự", "&#201;" => "É", "&#200;" => "È",
        "&#7866;" => "Ẻ", "&#7868;" => "Ẽ", "&#7864;" => "Ẹ", "&#202;" => "Ê", "&#7870;" => "Ế", "&#7872;" => "Ề",
        "&#7874;" => "Ể", "&#7876;" => "Ễ", "&#7878;" => "Ệ", "&#205;" => "Í", "&#204;" => "Ì", "&#7880;" => "Ỉ",
        "&#296;" => "Ĩ", "&#7882;" => "Ị", "&#221;" => "Ý", "&#7922;" => "Ỳ", "&#7926;" => "Ỷ", "&#7928;" => "Ỹ",
        "&#7924;" => "Ỵ", "&#272;" => "Đ"
    );
    $string = strtr($string, $trans);
    $string = mb_convert_encoding($string, "UTF-8", "UTF-8");
    return $string;
}

/**
 * Debug function
 */
function _debug($data)
{

    echo '<pre style="background: #000; color: #fff; width: 100%; overflow: auto">';
    echo '<div>Your IP: ' . @$_SERVER['REMOTE_ADDR'] . '</div>';

    $debug_backtrace = debug_backtrace();
    // $debug = array_shift($debug_backtrace);

    foreach ($debug_backtrace as $info) {
        if (isset($info['line'])) {
            echo '<div>Line: ' . $info['line'] . '->' . $info['file'] . '</div>';
        }
    }

    if (is_array($data) || is_object($data)) {
        print_r($data);
    } else {
        var_dump($data);
    }
    echo '</pre>';
}

function signUrlGoogleApi($input_url, $secret)
{
    if (!$input_url || !$secret) {
        throw new ErrorException('Both input_url and secret are required');
    }
    $url = parse_url($input_url);
    $url_to_sign = $url['path'] . "?" . $url['query'];
    $decoded_key = base64url_decode($secret);
    $signature = hash_hmac('sha1', $url_to_sign, $decoded_key, true);
    $encoded_signature = base64url_encode($signature);
    $original_url = $url['scheme'] . "://" . $url['host'] . $url['path'] . "?" . $url['query'];
    return $original_url . "&signature=" . $encoded_signature;
}

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data)
{
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function getUrlHashId($link)
{
    if (preg_match('/cla([0-9a-zA-Z]+)|new([0-9a-zA-Z]+)/', $link, $match)) {
        if (!isset($match[1])) return 0;
        $hashids = new  \Hashids\Hashids(config('app.key_hashid'));
        $numbers = $hashids->decode($match[1]);
        return isset($numbers[0]) ? $numbers[0] : 0;
    }
    return 0;
}

function encodeHashId($id)
{
    $hashids = new  \Hashids\Hashids(config('app.key_hashid'));
    $data = $hashids->encode($id);
    return $data;
}

function decodeHashId($id)
{
    $hashids = new  \Hashids\Hashids(config('app.key_hashid'));
    $id = $hashids->decode($id);
    return $id;
}


function createMeta($data_arr = [])
{
    $meta_arr = [];
    foreach ($data_arr as $key => $content) {
        array_push($meta_arr, ['name' => $key, 'content' => $content]);
    }
    return $meta_arr;
}

function format_number($number, $num_decimal = 2, $edit = 0)
{
    $sep = ($edit == 0 ? array(",", ".") : array(".", ""));
    $stt = -1;
    $return = number_format($number, $num_decimal, $sep[0], $sep[1]);

    for ($i = $num_decimal; $i > 0; $i--) {
        $stt++;
        if (intval(substr($return, -$i, $i)) == 0) {
            $return = number_format($number, $stt, $sep[0], $sep[1]);
            break;
        }
    }

    return $return;
}


function whereRawQueryBuilder($request_where_condition, $eloquent, $connection, $table)
{
    $options = explode(",", $request_where_condition);
    $alias_column = config("alias_database.$connection.$table");
    foreach ($options as $key => $option) {
        $option = explode(" ", $option);
        $field = array_search($option[0], $alias_column);
        if (!empty($field)) {
            if (strpos($option[1], '(float)') !== false) {
                $option[1] = (float)str_replace('(float)', '', $option[1]);
            } else {
                $option[1] = (int)$option[1];
            }
            $eloquent = $eloquent->where($field, $option[1]);
        }
    }
    return $eloquent;
}

function convertDate($date)
{
    $dt = DateTime::createFromFormat('U', $date);
    return $dt->format('d-m-Y');
}

function showAddresFromList($address)
{
    $address = explode(",", $address);
    $count = count($address);
    $arrayReturn = array();
    foreach ($address as $key => $value) {
        if ($key >= ($count - 3)) {
            $arrayReturn[] = '' . $value . '';
        }
    }
    $address = implode(",", $arrayReturn);
    return $address;
}

function showAddresFromListv2($address)
{
    $address = explode(",", $address);
    return $address[0] . ((count($address) > 1) ? ', ' . end($address) : '');
}

function showPriceFromList($list_price, $module = "bannhadat")
{
    if (trim($list_price) == "") return '';
    $list_price = explode(",", $list_price);
    arsort($list_price);
    $endfix = '/m2';
    if ($module == "chothue") $endfix = '';
    foreach ($list_price as $key => $value) {
        $value = doubleval(trim($value));
        if ($value < 10000) {
            $list_price[$key] = 'Liên hệ';
        } elseif ($value < 200000000) {
            $list_price[$key] = formatPriceText($value) . $endfix;
        } else {
            $list_price[$key] = formatPriceText($value);
        }
    }
    return implode(", ", $list_price);
}

function formatPriceText($pirce)
{
    $pirce = doubleval($pirce);
    if ($pirce < 1000000) {
        return format_number(intval($pirce / 1000)) . " ngàn";
    } elseif ($pirce < 1000000000) {
        return format_number(intval($pirce / 1000000)) . " triệu";
    } elseif ($pirce >= 1000000000) {
        return format_number(doubleval($pirce / 1000000000)) . " tỷ";
    }
}

function filterIdSphinx($arr_id)
{
    $arr_id = collect($arr_id)->map(function ($item) {
        return $item['id'];
    });
    return $arr_id;
}

function transformer_collection(&$items, \League\Fractal\TransformerAbstract $transformer, $includes = [], $meta = [])
{
    $manager = new \League\Fractal\Manager();
    $manager->setSerializer(new App\Helper\Transformer\DataArraySerializer());
    $resource = new \League\Fractal\Resource\Collection($items, $transformer);

    if ($includes) {
        $manager->parseIncludes($includes);
    }

    if ($meta) {
        $resource->setMeta($meta);
    }

    $vars = $manager->createData($resource)->toArray();

    return $vars;
}

function transformer_collection_paginator(&$items, \League\Fractal\TransformerAbstract $transformer, \League\Fractal\Pagination\PaginatorInterface $paginator, $includes = [], $meta = [])
{
    $manager = new \League\Fractal\Manager();
    $resource = new \League\Fractal\Resource\Collection($items, $transformer);

    $resource->setPaginator($paginator);

    if ($includes) {
        $manager->parseIncludes($includes);
    }

    if ($meta) {
        $resource->setMeta($meta);
    }

    $vars = $manager->createData($resource)->toArray();

    return $vars;
}


function BuildPhraseTrigrams($keyword)
{
    $keyword = str_replace(' ', '_', $keyword);
    $t = $keyword;
    $trigrams = "";
    for ($i = 0; $i < mb_strlen($t, "UTF-8") - 2; $i++)
        $trigrams .= mb_substr($t, $i, 3, "UTF-8") . " ";

    return $trigrams;
}

function BuildTrigrams($keyword)
{
    $t = "__" . $keyword . "__";
    $trigrams = "";
    for ($i = 0; $i < mb_strlen($t, "UTF-8") - 2; $i++)
        $trigrams .= mb_substr($t, $i, 3, "UTF-8") . " ";
    return $trigrams;
}

function EncodePassword($pass_input)
{
    $key_security = str_random(20);
    $password = md5($pass_input . $key_security);
    return ['password' => $password, 'key_security' => $key_security];
}

function DecodePassword($key_number, $key_security)
{
    $password = md5($key_number, $key_security);
    return $password;

}

function checkTokenFacebook($access_token)
{
    $user_face_info = file_get_contents("https://graph.facebook.com/me?access_token=$access_token&fields=email,name,picture,first_name,last_name,id,location");
    $user_face_info = json_decode($user_face_info);
    if (property_exists($user_face_info, 'id')) {
        $user_face_info->status_login = true;
    } else {
        $user_face_info->status_login = false;
    }
    return $user_face_info;
}

function checkLoginGoogle($access_token)
{
    $user_google_info = file_get_contents("https://www.googleapis.com/oauth2/v1/userinfo?access_token=$access_token");
    $user_google_info = json_decode($user_google_info);
    if (property_exists($user_google_info, 'id')) {
        $user_google_info->status_login = true;
    } else {
        $user_google_info->status_login = false;
    }
    return $user_google_info;

}

function createAccessToken($user)
{
    $time_now = (int)explode(' ', microtime())[1];
    $config_exp_token = (int)config('app.exp_token');
    $exp = $time_now + $config_exp_token;
    $data = [
        "data" => [
            "id" => $user->use_id,
            "user_name" => $user->use_name,
            "user_email" => $user->use_email,
            "rol" => getUserRol($user->use_rol)
        ],
        "iat" => $time_now,
        "exp" => $exp
    ];
    $token = Firebase\JWT\JWT::encode($data, config("app.secret_access_token"));
    return $token;
}

function getUserRol($rol)
{
    $rol_detail = [2 => 'admin', 1 => 'developer', 0 => 'user'];
    if (!isset($rol_detail[$rol])) {
        return 'user';
    }
    return $rol_detail[$rol];
}

function getUserId($access_token)
{
    try {
        $token_info = Firebase\JWT\JWT::decode($access_token, config("app.secret_access_token"), ['HS256']);
        return $token_info->data->id;
    } catch (\Exception $error) {
        return 0;
    }
}

function checkAuthUser($access_token, $user_id)
{
    try {
        $token_info = Firebase\JWT\JWT::decode($access_token, config("app.secret_access_token"), ['HS256']);
        if ($token_info->data->id == $user_id) {
            return true;
        }
        return false;
    } catch (\Exception $error) {
        return false;
    }

}

function getInfoToken($access_token)
{
    try {
        $token_info = Firebase\JWT\JWT::decode($access_token, config("app.secret_access_token"), ['HS256']);
        return $token_info->data;
    } catch (\Exception $error) {
        return false;
    }
}

function getInfoFormSecret($access_token)
{
    try {
        $token_info = Firebase\JWT\JWT::decode($access_token, config("app.secret_form_template"), ['HS256']);
        return $token_info->data;
    } catch (\Exception $error) {
        return false;
    }
}

function checkAuth($access_token)
{
    try {
        Firebase\JWT\JWT::decode($access_token, config("app.secret_access_token"), ['HS256']);
        return true;
    } catch (\Exception $error) {
        return false;
    }

}

function getDataAuth($access_token)
{
    try {
        $token_info = Firebase\JWT\JWT::decode($access_token, config("app.secret_access_token"), ['HS256']);
        return $token_info;
    } catch (\Exception $error) {
        return false;
    }
}

function convertPictureClassified($images)
{
    $arr_picture = [];
    $arr_picture['full'] = $images;
    $arr_picture['count_image'] = count($images);
    return $arr_picture;
}

function convertColumnDataBase($alias, $data)
{
    $data_new = (object)[];
    foreach ($alias as $key => $name_alias) {
        $data_new->{$name_alias} = $data->{$key};
    }
    return $data_new;
}

function filterWhereElasticssearch($val_where)
{
    $options = explode(",", $val_where);
    $data = [];
    foreach ($options as $key => $option) {
        $option = explode(" ", $option);
        if (!isset($option[1])) break;
        $data[$option[0]] = $option[1];
    }
    if (count($data) == 0) {
        return null;
    }
    return $data;

}

function createScoreFieldCheckClassified($classified = null, $classified_vip = null)
{
    $categories = Illuminate\Support\Facades\Cache::remember('categories_fields_check', 120, function () {
        return App\Models\Category::select('cat_id', 'cat_cla_field_check')->where('cat_active', 1)->get();
    });
    $sum_score = 0;
    if ($classified != null) {
        $classified_vip = App\Models\ClassifiedVip::where('clv_cla_id', $classified->cla_id)->first();
    } else if ($classified_vip != null) {
        $classified = App\Models\Classified::find($classified_vip->clv_cla_id);
    }
    $category = $categories->where('cat_id', $classified->cla_cat_id)->first();
    if (isset($category)) {
        $cla_fields = (array)$classified->fillable;
        if ($classified_vip != null) {
            $cla_fields = array_merge($cla_fields, (array)$classified_vip->fillable);
        }
        $cat_fields = (array)json_decode($category->cat_cla_field_check);
        foreach ($cat_fields as $field => $score) {
            if ($field == 'clv_media') {
                continue;
            }
            if (isset($cla_fields[$field]) && ($classified->{$field} != null && $classified->{$field} !== 0 && $classified->{$field} != '' && $classified->{$field} != '[]')) {
                $sum_score += $score;
            }
        }

    }
    return $sum_score;
}


function _combineRawUrlencodeMessage($request_method, $request_uri, $get_args, $post_args)
{
    $message = '';
    $message .= $request_method;
    $message .= "&" . rawurlencode($request_uri);
    $message .= "&" . rawurlencode(normalizeArrayToString($get_args));
    $message .= "&" . rawurlencode(normalizeArrayToString($post_args));
    return $message;
}


function normalizeArrayToString($data)
{
    $items = array();
    _makeQueryStrings($data, $items);
    ksort($items);

    $tmp = array();
    foreach ($items as $k => $v) $tmp[] = $k . '=' . $v;

    return implode('&', $tmp);
}

function _makeQueryStrings($data, & $queryStrings, $prefix = null)
{
    foreach ($data as $k => $v) {
        if (!empty($prefix)) $k = $prefix . '[' . $k . ']';
        if (is_array($v)) _makeQueryStrings($v, $queryStrings, $k);
        else $queryStrings[rawurlencode($k)] = rawurlencode($v);
    }
}

function makeBaoKimAPISignature($method, $url, $getArgs = array(), $postArgs = array(), $priKeyFile)
{
    ksort($getArgs);
    ksort($postArgs);
    $method = strtoupper($method);

    //$data		= $method . '&' . rawurlencode($url) . '&' . rawurlencode(http_build_query($getArgs)) . '&' . rawurlencode(http_build_query($postArgs));
    $data = _combineRawUrlencodeMessage($method, $url, $getArgs, $postArgs);

//    dump_log("my_combined_message: " . $data, "BAOKIM_PAYMENT_LOG");

    $priKey = openssl_get_privatekey($priKeyFile);
    assert('$priKey !== false');

    $x = openssl_sign($data, $signature, $priKey, OPENSSL_ALGO_SHA1);
    assert('$x !== false');

//    dump_log("my_signature: " . strval( rawurlencode(base64_encode($signature)) ), "BAOKIM_PAYMENT_LOG");

    return strval(rawurlencode(base64_encode($signature)));
}

function makeSignatureBaoKim($method, $url, $getArgs = array(), $postArgs = array(), $priKeyFile)
{
    ksort($getArgs);
    ksort($postArgs);
    $method = strtoupper($method);
    //$data		= $method . '&' . rawurlencode($url) . '&' . rawurlencode(http_build_query($getArgs)) . '&' . rawurlencode(http_build_query($postArgs));
    $data = _combineRawUrlencodeMessage($method, $url, $getArgs, $postArgs);
    $priKey = openssl_get_privatekey($priKeyFile);
    assert('$priKey !== false');
    $x = openssl_sign($data, $signature, $priKey, OPENSSL_ALGO_SHA1);
    assert('$x !== false');
    return strval(rawurlencode(base64_encode($signature)));
}

function createUserSpendHistory($user_id, $ip, $user_agent, $count, $order_id, $message, $status,$type=0)
{
    $ush = new \App\Models\UserSpendHistory();
    $ush->ush_order_id = $order_id;
    $ush->ush_user_id = $user_id;
    $ush->ush_count = $count;
    $ush->ush_message = $message;
    $ush->ush_ip = $ip;
    $ush->ush_user_agent = $user_agent;
    $ush->ush_status = $status;
    $ush->ush_type = $type;
    $ush->save();
    return $ush;
}

function updateStatusUserSpendHistory($id = null, $order_id = null, $status)
{
    if ($id != null) {
        $ush = \App\Models\UserSpendHistory::find($id);
    } else if ($order_id != null) {
        $ush = \App\Models\UserSpendHistory::where('ush_order_id', $order_id)->first();
    }
    if ($ush) {
        $ush->ush_status = $status;
        $ush->save();
        return $ush;
    }
    return false;

}

function updateMoneyBuyUserId($user_id, $count)
{
    $money = \App\Models\Money::where('mon_user_id', $user_id)->first();
    if ($money) {
        if (($money->mon_count + $count) < 0) {
            return false;
        }
        $money->mon_count = $money->mon_count + $count;
        $money->save();
        return $money;
    }
    return false;

}


/**
 * $index (string): Tên index
 * $fields (array): Tên trường tham gia tìm kiếm chuỗi: VD: "cla_title"
 * $query (string): Từ khóa tìm kiếm
 * $terms (array): Điều kiện tìm kiếm: VD : [field_name => value, field_name_2 => value_2 ]
 * $range_query (array): Điều kiện tìm kiếm trong một khoản giá trị: VD: [field_name=>[ {$type} => value_int]] (type: '>'. '<' , 'between')
 * $source (array): Trường muốn lấy: VD: [field_name, field_name_2]
 * $limit (int): Lấy giới hạn bản ghi
 * $_doc (string): Kiểu doccument : Mặc đinh là "_doc"
 * $form (int) : Lấy từ bản ghi thứ bao nhiêu, tương đương với offset trong sql
 * $convert_data (boolean) : Lọc lấy dữ liệu lấy được
 * $sort(array) : Sắp xếp dữ liệu theo một trường nhất định ['field'=> field_name,'type'=>{$type_sort}] ($type_sort: desc , asc)
 */

function searchDataElasticV3($index, $fields = null, $query = null, $terms = null, $range_query = null, $source, $limit = 30, $_doc = '_doc', $from = 0, $convert_data = true, $sort = null)
{
    $range = [];
    $filter = [];
    if ($range_query != null && is_array($range_query)) {
        foreach ($range_query as $name_column => $data_rage) {
            if ($name_column == 'geo_distance') {
                $filter[$name_column] = $data_rage;
                continue;
            }
            $rage_temp[$name_column] = [];
            foreach ($data_rage as $type_range => $value) {
                if ($type_range == ">") {
                    $rage_temp[$name_column]['gte'] = $value;
                } else if ($type_range == "<") {
                    $rage_temp[$name_column]['lte'] = $value;
                } else if ($type_range == "between") {
                    $rage_temp[$name_column] = ['gte' => $value[0], 'lte' => (int)$value[1]];
                }
            }
            $range = $rage_temp;
        }

    } elseif ($range_query != null) {
        throw  new \Exception('param $range_query not is type array');
    }
    $arr_terms = [];
    if ($terms != null) {
        foreach ($terms as $key => $term) {
            if (is_array($term)) {
                $arr_terms[] = [
                    "terms" => [
                        $key => $term
                    ]
                ];
            } else {
                $arr_terms[] = [
                    "terms" => [
                        $key => [$term]
                    ]
                ];
            }
        }
    }

    if ($query != null) {
        $must = [
            [
                "match" => [
                    $fields => $query
                ]
            ]
        ];

    } else {
        $must = [];
    }

    $params = [
        'index' => $index,
        'type' => $_doc,
        'body' => [
            "query" => [
                "bool" => [
                    "must" => $must
                ]

            ],
            "_source" => $source,
            "from" => $from,
            "size" => (int)$limit
        ]
    ];
    if (count($arr_terms) != 0 && count($range) != 0) {
        $params['body']['query']['bool']['must'][] = $arr_terms;
        $params['body']['query']['bool']['must'][] = ['range' => $range];
    } else if (count($arr_terms) != 0) {
        $params['body']['query']['bool']['must'][] = $arr_terms;
    } else if (count($range) != 0) {
        $params['body']['query']['bool']['must'][] = ['range' => $range];
    }
    if (count($filter) != 0) {
        $params['body']['query']['bool']['filter'] = $filter;
        if (isset($filter['geo_distance'])) {
            $params['body']['sort'] = [
                "_geo_distance" => [
                    "location" => [
                        'lat' => arrGetVal('geo_distance.location.lat', $filter, 0),
                        'lon' => arrGetVal('geo_distance.location.lon', $filter, 0),
                    ],
                    'order' => 'asc',
                    'unit' => 'mi'
                ]
            ];
        }
    }
    if ($sort != null) {
        $params['body']['sort'] = [$sort['field'] => ['order' => $sort['type']]];
    }
    $data_search = app('esearch')->search($params);
    if ($convert_data) {
        $value = null;
        if (isset($data_search['hits']['hits'])) {
            $value = [];
            foreach ($data_search['hits']['hits'] as $data) {
                $value[] = $data['_source'];
            }
            if (isset($value[0])) {
                if ($limit == 1) {
                    $value = $value[0];
                }
            }
        }

        return $value;
    }
    return $data_search;
}

function searchDataElastic($index, $fields, $query, $terms = null, $source, $limit = 30, $_doc = '_doc')
{
    $params = null;
    if ($terms != null) {
        $arr_terms = [];
        foreach ($terms as $key => $term) {
            if (is_array($term)) {
                $arr_terms[] = [
                    "terms" => [
                        $key => $term
                    ]
                ];
            } else {
                $arr_terms[] = [
                    "terms" => [
                        $key => [$term]
                    ]
                ];
            }
        }
        $params = [
            'index' => $index,
            'type' => $_doc,
            'body' => [
                "query" => [
                    "function_score" => [
                        "query" => [
                            "bool" => [
                                "must" => [
                                    [
                                        "simple_query_string" => [
                                            "fields" => $fields,
                                            "query" => $query
                                        ]
                                    ],
                                    $arr_terms
                                ]
                            ]
                        ]
                    ]
                ],
                "_source" => $source,
                "size" => (int)$limit
            ]
        ];
    } else {
        $params = [
            'index' => $index,
            'type' => $_doc,
            'body' => [
                "query" => [
                    "function_score" => [
                        "query" => [
                            "bool" => [
                                "must" => [
                                    [
                                        "simple_query_string" => [
                                            "fields" => $fields,
                                            "query" => $query
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "_source" => $source,
                "size" => (int)$limit
            ]
        ];
    }
    //print_r($params);
    $data_search = app('elastic')->search($params);
    $value = null;
    if (isset($data_search['hits']['hits'])) {
        $value = [];
        foreach ($data_search['hits']['hits'] as $data) {
            $value[] = $data['_source'];
        }
        if (isset($value[0])) {
            if ($limit == 1) {
                $value = $value[0];
            }
        }
    }

    return $value;
}


function getSourceElastic(array $result)
{
    $arrReturn = [];
    if (isset($result["hits"]["hits"])) {
        foreach ($result["hits"]["hits"] as $val) {
            $arrReturn[$val["_id"]] = $val["_source"];
        }
    }
    return $arrReturn;
}


function getHtmlContent($path)
{
    if (Illuminate\Support\Facades\Storage::has($path)) {
        return Illuminate\Support\Facades\Storage::get($path);
    }
    return "";

}


function saveFileLocal($name, $file, $folder_save)
{
    $exp_name = explode('.', $name);
    $type = isset($exp_name[count($exp_name) - 1]) ? $exp_name[count($exp_name) - 1] : null;
    Storage::disk('local')->putFileAs(
        $folder_save,
        $file,
        $name
    );
    $data['type'] = $type;
    $data['name'] = $name;
    $data['url'] = config('app.source_path') . '/' . $folder_save . '/' . $name;
    $data['path'] = $folder_save . '/' . $name;
    return $data;
}

function minimizeCSS($css)
{
    $css = preg_replace('/\\n/', '', $css);
    return $css;
}


function cut_string($str, $length, $char = " ...")
{
    //Nếu chuỗi cần cắt nhỏ hơn $length thì return luôn
    $strlen = mb_strlen($str, "UTF-8");
    if ($strlen <= $length) return $str;

    //Cắt chiều dài chuỗi $str tới đoạn cần lấy
    $substr = mb_substr($str, 0, $length, "UTF-8");
    if (mb_substr($str, $length, 1, "UTF-8") == " ") return $substr . $char;

    //Xác định dấu " " cuối cùng trong chuỗi $substr vừa cắt
    $strPoint = mb_strrpos($substr, " ", "UTF-8");

    //Return string
    if ($strPoint < $length - 20) return $substr . $char;
    else return mb_substr($substr, 0, $strPoint, "UTF-8") . $char;
}

function getSourceTheme()
{
    return Illuminate\Support\Facades\Cache::rememberForever('theme_source', function () {
        $source = new  App\Models\ThemeSource();
        $source = $source->select($source->alias('*'))->get()->toArray();
        return collect($source);
    });

}


function minimizeHtml($html)
{
    $html = preg_replace('/\\n/', '', $html);
    return $html;
}

function resizeImage($path, $width, $height)
{
    try {
        if (!file_exists($path)) {
            throw new \Exception('Not found file(resize image)');
        }
        $imageThumb = new App\Helper\Image($path);
        $imageThumb->createThumb($path, $width, $height, 'fit');
    } catch (\Exception $error) {
        throw new \Exception($error->getMessage());
    }
}

function filterFileName($path)
{
    $vars = strrchr($path, "?"); // ?asd=qwe&stuff#hash
    $name = preg_replace('/' . preg_quote($vars, '/') . '$/', '', basename($path));
    $name = explode('#', explode('?', $name)[0])[0];
    if ($name != '' && strpos($name, '.') !== false) {
        $name_exp = explode('.', $name);
        $type_file = isset($name_exp[count($name_exp) - 1]) ? $name_exp[count($name_exp) - 1] : '';
//            $type_file = explode('?',explode('#',$type_file)[0]);
        return ['name' => $name, 'type' => $type_file];
    }
    return false;
}

function getClassifiedConfig($id = null, $time = null, $type = null)
{
    $cla_vip_config = \Illuminate\Support\Facades\Cache::rememberForever('cla_vip_config', function () {
        return collect(\App\Models\ClassifiedVipConfig::where('cvc_active', 1)->get()->toArray());
    });
    $value = $cla_vip_config;
    if ($id != null) {
        $value = $value->where('cvc_id', (int)$id)->first();
        return $value;
    }

    if ($time != null) {
        $value = $value->where('cvc_time_start', '<', $time)->where('cvc_time_end', '>', $time);
    }

    if ($type != null) {
        $value = $value->where('cvc_type', $type);
    }
    return $value;

}

function cleanRewriteNoAccent($string)
{
    $string = mb_strtolower($string, "UTF-8");
    $string = convertToUnicode($string);
    $string = removeAccent($string);
    $string = mb_convert_encoding($string, "UTF-8", "UTF-8");
    $string = str_replace("?", "", trim($string));
    $string = str_replace("tr/m2", "triệu một m2", trim($string));
    $string = trim(preg_replace("/[^A-Za-z0-9]/i", " ", $string));
    $string = mb_convert_encoding($string, "UTF-8", "UTF-8");
    $string = trim(preg_replace("/[^A-Za-z0-9]/i", " ", $string));
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

function removeEmoji($text)
{

    $clean_text = "";

    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $text);

    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);

    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);

    // Match Miscellaneous Symbols
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '', $clean_text);

    // Match Dingbats
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    //$clean_text = str_replace(array(chr(9)),"",$clean_text);
    $arrOtherChar = array('�');
    $clean_text = str_replace($arrOtherChar, "", $clean_text);
    $clean_text = preg_replace('%(?:
          \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
    )%xs', '', $clean_text);
    return $clean_text;
}

function replaceMQ($text)
{
    $text = str_replace("\'", "'", $text);
    $text = str_replace("'", "''", $text);

    return $text;
}

function getCountWordRewrite($rewrite)
{
    $rewrite = explode("-", $rewrite);
    return count($rewrite);
}

function getSphinxIndexClassified($cla_id = 0)
{
    $table = intval($cla_id) % 8;
    return 'bds_rt_classifieds_' . $table;
}

function arrayVal($array)
{
    if (!is_array($array)) return array();
    if (isset($array[0]) && empty($array[0]) && count($array) == 1) return array();
    return $array;
}

function getUrlImageMain($array, $maxWidth = 150, $catID = 0, $cit_id = 0, $dis_id = 0, $ward_id = 0, $street_id = 0, $sourcePicture = 0)
{
    $array = arrayVal($array);
    foreach ($array as $row) {
        if (isset($row['type'])) {
            if ($row['type'] == 'photo') {
                if (isset($row["filename"])) return getUrlPicture($row["filename"], $maxWidth, $sourcePicture);
                if (isset($row["name"])) return getUrlPicture($row["name"], $maxWidth, $sourcePicture);
            }
        } else {
            if (isset($row["filename"])) return getUrlPicture($row["filename"], $maxWidth, $sourcePicture);
            if (isset($row["name"])) return getUrlPicture($row["name"], $maxWidth, $sourcePicture);
        }
    }
    $map_size = 'crop150x100';
    if ($maxWidth <= 0) {
        $map_size = 'full';
    }

    if ($ward_id > 0) {
        return "https://sosanhnha.com/" . config("con_static_version") . "/assets/maps/$map_size/ward$ward_id.png";
    }
    if ($dis_id > 0) {
        return "https://sosanhnha.com/" . config("con_static_version") . "/assets/maps/$map_size/dis$dis_id.png";
    }
    if ($cit_id > 0) {
        return "https://sosanhnha.com/" . config("con_static_version") . "/assets/maps/$map_size/$cit_id.png";
    }
    if ($catID > 0) {
        return "https://sosanhnha.com/" . config("con_static_version") . "/assets/images/noimage_$catID.png";
    }
    return "https://sosanhnha.com/" . config("con_static_version") . "/assets/images/noimage.png";
}

function cutRewriteName($name)
{
    $stringReturn = '';
    $name = explode(",", $name);
    foreach ($name as $val) {
        $stringReturn .= $val . ",";
        if (strpos($stringReturn, "tại") !== false) {
            return substr($stringReturn, 0, -1);
        }
    }
    return $stringReturn;

}

function arrGetVal($key, $arr, $defaultValue = '')
{
    $key = explode(".", $key);
    $arrTemp = $arr;
    foreach ($key as $k) {
        if (isset($arrTemp[$k])) {
            $arrTemp = $arrTemp[$k];
        } else {
            $arrTemp = $defaultValue;
        }
    }
    if (!$arrTemp) $arrTemp = $defaultValue;
    return $arrTemp;
}

function cleanUrl($url)
{
    $url = urldecode($url);
    $url = str_slug($url);
    return $url;
}

function optimizeImage($path)
{
    if (file_exists($path)) {
        $optimizerChain = \Spatie\ImageOptimizer\OptimizerChainFactory::create();
        $optimizerChain->optimize($path);
        return true;
    }
    return false;
}

function getUrlImageByRow($row, $maxWidth = 0, $source = false)
{
    $type = arrGetVal('type', $row, 'photo');
    $filename = arrGetVal('filename', $row, '');
    if ($filename == '') {
        $filename = arrGetVal('name', $row, '');
    }
    if ($type == 'photo') {
        return getUrlPicture($filename, $maxWidth, $source);
    }
    return config("configuration.con_static_url") . "/" . config("configuration.con_static_version") . "/assets/images/noimage.png";
}

function checkDiffClassified($title, $teaser, $minPercent = 60, $check_all = 1)
{
    //check date cách đây  3 tháng đổ lại nếu trùng thì bỏ qua
    $time = strtotime("today") - (86400 * 90);
    $textSearch = cut_string($title, 200, "");
    $sphinx = app()->make('sphinx');
    $sql_where = '';
    if ($check_all == 2) {
        $sql_where = 'AND cla_type = 2 ';
    }
//    $db_sphinx = new db_sphinx("SELECT id FROM bds_rt_classifieds_all WHERE MATCH('@(cla_title) \"" . cleanKeywordSearch($textSearch) . "\"/4') AND cla_date > " . $time . " LIMIT 10 OPTION ranker = matchany");
    $db_sphinx = $sphinx->query("SELECT id FROM bds_rt_classifieds_all WHERE MATCH('@(cla_title) \"" . cleanKeywordSearch($textSearch) . "\"/4')  $sql_where  AND cla_date > " . $time . " LIMIT 10 ")->execute()->fetchAllAssoc();
    foreach ($db_sphinx as $sphinx) {
        $db_select = App\Models\Classified::select('cla_teaser')->where('cla_id', (int)$sphinx["id"])->first();
//        $db_select = new db_query("SELECT cla_teaser FROM classifieds WHERE cla_id = " . $sphinx["id"] . " LIMIT 1");
        if ($db_select) {
            $result = getDiffText(cleanKeywordSearch($db_select->cla_teaser), cleanKeywordSearch($teaser));
            if ($result["copy"] > $minPercent) return $result;
        }
        unset($db_select);
    }
    return false;
}

function checkDuplicatePost($description, $maxPercent = 75, $user_id)
{
    $new_description = cleanKeywordSearch($description);
    if (mb_strlen($description, "UTF-8") < 20) return false;

    $db_select = \App\Models\Classified::select("cla_id", "cla_title", "cla_rewrite", "cla_description")->where("cla_use_id", $user_id)->where("cla_type", 2)->orderBy('cla_id', 'desc')->limit(5)->get();

//    $db_sphinx =  $sphinx->query("SELECT id FROM bds_rt_classifieds_all WHERE MATCH('@(cla_title) \"" . cleanKeywordSearch($textSearch) . "\"/4')  $sql_where  AND cla_date > " . $time . " LIMIT 10 ")->execute()->fetchAllAssoc();
    $content_copy = null;
    foreach ($db_select as $row) {
        $row = $row->toArray();
        $description = cleanKeywordSearch($row['cla_description']);
        $result = getDiffText($new_description, $description);
        if ($result["copy"] > $maxPercent) {
            $row["copy"] = $result["copy"];
            $content_copy = $row;
            break;
        }
    }
    if (!empty($content_copy)) return $content_copy;
    unset($db_select);
    return [];
}

function getDiffText($from, $to, $type = 2)
{
    $time = microtime(true);
    $memory = memory_get_usage();
    $from_len = mb_strlen($from, 'UTF-8');
    $to_len = mb_strlen($to, 'UTF-8');
    if ($from_len <= 0) {
        return [];
    }
    require_once(__DIR__ . "/finediff/finediff.php");
    $granularityStacks = array(
        FineDiff::$paragraphGranularity,
        FineDiff::$sentenceGranularity,
        FineDiff::$wordGranularity,
        FineDiff::$characterGranularity
    );

    $diff = new FineDiff($from, $to, $granularityStacks[$type]);
    $edits = $diff->getOps();

    // var_dump($edits);die;//
    // $exec_time      = sprintf('%.3f sec', gettimeofday(true) - $start_time);
    // $rendered_diff  = $diff->renderDiffToHTML();
    // $rendering_time = sprintf('%.3f sec', gettimeofday(true) - $start_time);

    // $arrText = array();
    $opcodes = array();
    $copy_len = 0;
    $delete_len = 0;
    $insert_len = 0;
    $replace_len = 0;
    $copy = 0;
    $delete = 0;
    $insert = 0;
    $replace = 0;

    if ($edits !== false) {

        $offset = 0;
        foreach ($edits as $edit) {
            $n = $edit->getFromLen();
            // $text   = mb_substr($from, $offset, $n, 'UTF-8');
            // $length = mb_strlen($text, 'UTF-8');

            if ($edit instanceof FineDiffCopyOp) {
                $state = 'copy';
                $text = mb_substr($from, $offset, $n, 'UTF-8');
                $copy_len += mb_strlen($text, 'UTF-8');

            } else if ($edit instanceof FineDiffDeleteOp) {
                $state = 'delete';
                $text = mb_substr($from, $offset, $n, 'UTF-8');
                if (strcspn($text, " \n\r") === 0) {
                    $text = str_replace(array("\n", "\r"), array('\n', '\r'), $text);
                }
                $delete_len += mb_strlen($text, 'UTF-8');

            } else if ($edit instanceof FineDiffInsertOp) {
                $state = 'insert';
                $text = mb_substr($edit->getText(), 0, $edit->getToLen(), 'UTF-8');
                $insert_len += mb_strlen($text, 'UTF-8');

            } else /* if ( $edit instanceof FineDiffReplaceOp ) */ {
                $state = 'replace';

                // delete
                $text = mb_substr($from, $offset, $n, 'UTF-8');
                if (strcspn($text, " \n\r") === 0) {
                    $text = str_replace(array("\n", "\r"), array('\n', '\r'), $text);
                }
                $delete_len += mb_strlen($text, 'UTF-8');
                $replace_len += $delete_len;

                // insert
                $text = mb_substr($from, $offset, $n, 'UTF-8');
                $insert_len += mb_strlen($text, 'UTF-8');
            }

            $opcodes[] = array('state' => $state, 'value' => $text);
            $offset += $n;
        }

        // $opcodes = implode("", $opcodes);
        // $opcodes_len = sprintf('%d bytes (%.1f %% of &quot;To&quot;)', $opcodes_len, $to_len ? $opcodes_len * 100 / $to_len : 0);
        $copy = $copy_len * 100 / $from_len;
        $delete = $delete_len * 100 / $from_len;
        $insert = $insert_len * 100 / $from_len;
        $replace = $replace_len * 100 / $from_len;
    }

    return array(
        'meta' => array(
            'time' => microtime(true) - $time,
            'memory' => memory_get_usage() - $memory,
        ),
        'opcode' => $opcodes,
        'copy' => $copy,
        'delete' => $delete,
        'insert' => $insert,
        'replace' => $replace
    );
}

function remove_utf8_bom($text)
{
    $bom = pack('H*', 'EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}

function MomoExecPostRequest($data)
{
    $ch = curl_init(config('momo.endpoint'));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);

    return $result;
}

function getInfoTokenUserContact($access_token)
{
    try {
        $token_info = Firebase\JWT\JWT::decode($access_token, config("app.secret_form_template"), ['HS256']);
        return $token_info->data;
    } catch (\Exception $error) {
        return false;
    }
}

function getListDomainMedia()
{
    $arrayListDomainMediaVatgia = array("media.sosanhnha.com", "bds.vatgia.vn", "youtube.com");
    return $arrayListDomainMediaVatgia;
}

function splitPhoneNumber($string, $return_old = false)
{

    /*
        Số điện thoại là các số bắt đầu == 01 -> 09 và có 10 hoặc 11 ký tự
        09: sẽ có 10 ký tự
        01 -> 08 là số máy bàn hoặc đầu số mới cũng sẽ có 10 hoặc 11 ký tự
     */
    $arrDauSo = array(
        1 => '01',
        2 => '02',
        3 => '03',
        4 => '04',
        5 => '05',
        6 => '06',
        7 => '07',
        8 => '08',
        9 => '09'
    );

    $str_tmp = str_replace(array(" - ", " . "), " / ", $string);
    $str_tmp = preg_replace('/\s/', '', $str_tmp);

    $pattern = '/(\d{6,}(?!\d)|(?<!\d)\d{6,}|(\(|\d|\.|-|,|\)){6,})/';

    preg_match_all($pattern, $str_tmp, $match);
    //print_r($match[0]);

    $result = array();// Mang luu lai ket qua tra ve
    foreach ($match[0] as $key => $value) {
        // số chuẩn khi đã replace hết các ký tự string
        $phoneNumber = preg_replace('/\D/', '', $value);
        foreach ($arrDauSo as $k => $dauso) {
            if (strpos($phoneNumber, $dauso) === 0) {
                if ($dauso == '09') {
                    if (strlen($phoneNumber) == 10) {
                        $result[$key]["socu"] = removeCharPhoneNumber($value);
                        $result[$key]["somoi"] = $phoneNumber;
                    }
                } else {
                    if (strlen($phoneNumber) >= 9 && strlen($phoneNumber) <= 11) {
                        $result[$key]["socu"] = removeCharPhoneNumber($value);
                        $result[$key]["somoi"] = $phoneNumber;
                    }
                }
            }
        }
    }
    $arrayReturn = array();
    foreach ($result as $val) {
        $val["somoi"] = format_login_phone($val["somoi"]);
        if ($val["somoi"] < 10000000) continue;
        if ($return_old) {
            $arrayReturn[$val["socu"]] = $val["somoi"];

        } else {
            $arrayReturn[$val["somoi"]] = $val["somoi"];
        }
    }
    //$arrayReturn = array_flip($arrayReturn);
    //reset($arrayReturn);
    return $arrayReturn;

}

function removeCharPhoneNumber($string)
{

    $length = mb_strlen($string, "UTF-8");

    $start_char = 0;
    //Remove các ký tự ko phải số ở đầu
    for ($i = 0; $i < $length; $i++) {
        $char = mb_substr($string, $i, 1, "UTF-8");
        if (($char == "(") || (is_numeric($char))) break;
        $start_char = $i + 1;
    }

    $end_char = $length;
    //Remove các ký tự ko phải số ở cuối
    for ($i = $length; $i >= 0; $i--) {
        $char = mb_substr($string, $i - 1, 1, "UTF-8");
        if (is_numeric($char)) break;
        $end_char = $i - 1;
    }
    //Cắt chuỗi
    $string = mb_substr($string, $start_char, ($end_char - $start_char), "UTF-8");

    return $string;
}

function format_login_phone($phone)
{

    $phone = str_replace('+84', '0', $phone);

    $phone = preg_replace("/[^A-Za-z0-9 ]/", '', $phone);


    //Check xem có bắt đầu bằng số 0?
    if (substr($phone, 0, 1) == '0') {
        //09 thì là 10 số --- 01 thì là 11 số
        if (
            (substr($phone, 0, 2) == '09' && strlen($phone) == 10)
            || (substr($phone, 0, 2) == '01' && strlen($phone) == 11)
            || (substr($phone, 0, 2) == '08' && strlen($phone) == 10)
        ) {
            return $phone;
        }
    }
    return false;
}


/**
 * $index (string): Tên index
 * $fields (array): Tên trường tham gia tìm kiếm chuỗi: VD: ["cla_title^6","cla_address^5","cat_name^4"]
 * $query (string): Từ khóa tìm kiếm
 * $terms (array): Điều kiện tìm kiếm: VD : [field_name => value, field_name_2 => value_2 ]
 * $range_query (array): Điều kiện tìm kiếm trong một khoản giá trị: VD: [field_name=>[ {$type} => value_int]] (type: '>'. '<' , 'between')
 * $source (array): Trường muốn lấy: VD: [field_name, field_name_2]
 * $limit (int): Lấy giới hạn bản ghi
 * $_doc (string): Kiểu doccument : Mặc đinh là "_doc"
 * $form (int) : Lấy từ bản ghi thứ bao nhiêu, tương đương với offset trong sql
 * $convert_data (boolean) : Lọc lấy dữ liệu lấy được
 * $sort(array) : Sắp xếp dữ liệu theo một trường nhất định ['field'=> field_name,'type'=>{$type_sort}] ($type_sort: desc , asc)
 */

function searchDataElasticV2($index, $fields = null, $query = null, $terms = null, $range_query = null, $source, $limit = 30, $_doc = '_doc', $from = 0, $convert_data = true, $sort = null)
{
    $range = [];
    if ($range_query != null && is_array($range_query)) {
        foreach ($range_query as $name_column => $data_rage) {
            $rage_temp[$name_column] = [];
            foreach ($data_rage as $type_range => $value) {
                if ($type_range == ">") {
                    $rage_temp[$name_column]['gte'] = $value;
                } else if ($type_range == "<") {
                    $rage_temp[$name_column]['lte'] = $value;
                } else if ($type_range == "between") {
                    $rage_temp[$name_column] = ['gte' => $value[0], 'lte' => (int)$value[1]];
                }
            }
            $range = $rage_temp;
        }

    } elseif ($range_query != null) {
        throw  new \Exception('param $range_query not is type array');
    }
    $arr_terms = [];
    if ($terms != null) {
        foreach ($terms as $key => $term) {
            if (is_array($term)) {
                $arr_terms[] = [
                    "terms" => [
                        $key => $term
                    ]
                ];
            } else {
                $arr_terms[] = [
                    "terms" => [
                        $key => [$term]
                    ]
                ];
            }
        }
    }

    if ($query != null) {
        $must = [
            [
                "simple_query_string" => [
                    "fields" => $fields,
                    "query" => $query
                ]
            ]
        ];
    } else {
        $must = [];
    }

    $params = [
        'index' => $index,
        'type' => $_doc,
        'body' => [
            "query" => [
                "bool" => [
                    "must" => $must
                ]

            ],
            "_source" => $source,
            "from" => $from,
            "size" => (int)$limit
        ]
    ];
    if (count($arr_terms) != 0 && count($range) != 0) {
        $params['body']['query']['bool']['must'][] = $arr_terms;
        $params['body']['query']['bool']['must'][] = ['range' => $range];
    } else if (count($arr_terms) != 0) {
        $params['body']['query']['bool']['must'][] = $arr_terms;
    } else if (count($range) != 0) {
        $params['body']['query']['bool']['must'][] = ['range' => $range];
    }

    if ($sort != null) {
        $params['body']['sort'] = [$sort['field'] => ['order' => $sort['type']]];
    }
    $data_search = app('elastic')->search($params);
    if ($convert_data) {
        $value = null;
        if (isset($data_search['hits']['hits'])) {
            $value = [];
            foreach ($data_search['hits']['hits'] as $data) {
                $value[] = $data['_source'];
            }
            if (isset($value[0])) {
                if ($limit == 1) {
                    $value = $value[0];
                }
            }
        }

        return $value;
    }
    return $data_search;
}

/**
 * $index (string): Tên index
 * $fields (array): Tên trường tham gia tìm kiếm chuỗi: VD: "cla_title"
 * $query (string): Từ khóa tìm kiếm
 * $terms (array): Điều kiện tìm kiếm: VD : [field_name => value, field_name_2 => value_2 ]
 * $range_query (array): Điều kiện tìm kiếm trong một khoản giá trị: VD: [field_name=>[ {$type} => value_int]] (type: '>'. '<' , 'between')
 * $source (array): Trường muốn lấy: VD: [field_name, field_name_2]
 * $limit (int): Lấy giới hạn bản ghi
 * $_doc (string): Kiểu doccument : Mặc đinh là "_doc"
 * $form (int) : Lấy từ bản ghi thứ bao nhiêu, tương đương với offset trong sql
 * $convert_data (boolean) : Lọc lấy dữ liệu lấy được
 * $sort(array) : Sắp xếp dữ liệu theo một trường nhất định ['field'=> field_name,'type'=>{$type_sort}] ($type_sort: desc , asc)
 */

function searchDataElasticV4($index, $fields = null, $query = null, $terms = null, $range_query = null, $source, $limit = 30, $_doc = '_doc', $from = 0, $convert_data = true, $sort = null)
{

    $range = [];
    $filter = [];
    if ($range_query != null && is_array($range_query)) {
        foreach ($range_query as $name_column => $data_rage) {
            if ($name_column == 'geo_distance') {
                $filter[$name_column] = $data_rage;
                continue;
            }
            $rage_temp[$name_column] = [];
            foreach ($data_rage as $type_range => $value) {
                if ($type_range == ">") {
                    $rage_temp[$name_column]['gte'] = $value;
                } else if ($type_range == "<") {
                    $rage_temp[$name_column]['lte'] = $value;
                } else if ($type_range == "between") {
                    $rage_temp[$name_column] = ['gte' => $value[0], 'lte' => (int)$value[1]];
                }
            }
            $range = $rage_temp;
        }

    } elseif ($range_query != null) {
        throw  new \Exception('param $range_query not is type array');
    }

    $arr_terms = [];
    if ($terms != null) {
        foreach ($terms as $key => $term) {
            if (is_array($term)) {
                $arr_terms[] = [
                    "terms" => [
                        $key => $term
                    ]
                ];
            } else {
                $arr_terms[] = [
                    "terms" => [
                        $key => [$term]
                    ]
                ];
            }
        }
    }

    if ($query != null) {
        $must = [
            [
                "match" => [
                    $fields[0] => $query
                ]
            ]
        ];

    } else {
        $must = [];
    }

    $params = [
        'index' => $index,
        'type' => $_doc,
        'body' => [
            "query" => [
                "bool" => [
                    "must" => $must
                ]

            ],
            "_source" => $source,
            "from" => $from,
            "size" => (int)$limit
        ]
    ];


    if (count($arr_terms) != 0 && count($range) != 0) {
        $params['body']['query']['bool']['must'][] = $arr_terms;
        $params['body']['query']['bool']['must'][] = ['range' => $range];
    } else if (count($arr_terms) != 0) {
        $params['body']['query']['bool']['must'][] = $arr_terms;
    } else if (count($range) != 0) {
        $params['body']['query']['bool']['must'][] = ['range' => $range];
    }
    if (count($filter) != 0) {
        $params['body']['query']['bool']['filter'] = $filter;
        if (isset($filter['geo_distance'])) {
            $params['body']['sort'] = [
                "_geo_distance" => [
                    "location" => [
                        'lat' => arrGetVal('geo_distance.location.lat', $filter, 0),
                        'lon' => arrGetVal('geo_distance.location.lon', $filter, 0),
                    ],
                    'order' => 'asc',
                    'unit' => 'mi'
                ]
            ];
        }
    }

    if ($sort != null) {
        $params['body']['sort'] = [$sort['field'] => ['order' => $sort['type']]];
    }

    $data_search = app('elastic')->search($params);
    if ($convert_data) {
        $value = null;
        if (isset($data_search['hits']['hits'])) {
            $value = [];
            foreach ($data_search['hits']['hits'] as $data) {
                $value[] = $data['_source'];
            }
            if (isset($value[0])) {
                if ($limit == 1) {
                    $value = $value[0];
                }
            }
        }

        return $value;
    }
    return $data_search;
}


function isDenyKeyword($string)
{
    $string = removeHTML($string);
    $string = mb_strtolower($string, "UTF-8");
    $string = replace_double_space($string);
    $arrayKeyword = preg_split("/([\,\|\n])/", config("configuration.con_keyword_spam"));
    foreach ($arrayKeyword as $key => $value) {
        $value = replace_double_space($value);
        $value = trim($value);
        $value = mb_strtolower($value, "UTF-8");
        if (empty($value)) continue;
        if (strpos($string, $value) !== false) return $value;
    }
    return false;


}

function getBreadCrumb($rew_id, $proj_id = 0)
{
    if (intval($rew_id) <= 0) return [];
    $arrayReturn = [];
    $proj_id = intval($proj_id);
    $key_cache = $rew_id . "_" . $proj_id;
    $dataCache = Illuminate\Support\Facades\Cache::get($key_cache);
    if (!empty($dataCache)) {
        $arrayRewId = [];
        foreach ($dataCache as $rew) {
            $arrayRewId[] = $rew["rew_id"];
        }
        return $dataCache;
    }

//    $sqlWhere = "rew_id = " . intval($rew_id);
//    $result = arrayVal($this->fields("rew_id,rew_title,rew_rewrite,rew_cat_id,rew_cit_id,rew_dis_id,rew_ward_id,rew_street_id,rew_proj_id")->where($sqlWhere)->limit(1)->select_all());
    $result = \App\Models\Rewrite::select("rew_id", "rew_title", "rew_rewrite", "rew_cat_id", "rew_cit_id", "rew_dis_id", "rew_ward_id", "rew_street_id", "rew_proj_id")->where('rew_id', $rew_id)->first();
    //*

    if ($result) {
        if (intval($result->rew_cat_id) > 0) {
            $sqlWhereCity = "rew_cat_id = " . intval($result->rew_cat_id) . " AND rew_cit_id = 0 AND rew_dis_id = 0 AND rew_ward_id = 0 AND rew_street_id = 0 AND rew_proj_id = 0";
            $rew = \App\Models\Rewrite::select("rew_id", "rew_title", "rew_rewrite", "rew_cat_id", "rew_cit_id", "rew_dis_id", "rew_ward_id", "rew_street_id", "rew_proj_id")->whereRaw(\DB::raw($sqlWhereCity))->first();
//            $rew =  $modal_rewrite->where()
//            dd($rew);
            if (!empty($rew)) {
                $rew->rew_title = cutRewriteName($rew->rew_title);
                $arrayReturn[] = $rew->toArray();
            }
        }
        if (intval($result->rew_cit_id) > 0) {
            $sqlWhereCity = "rew_cat_id = " . intval($result->rew_cat_id) . " AND rew_cit_id = " . $result->rew_cit_id . " AND rew_dis_id = 0 AND rew_ward_id = 0 AND rew_street_id = 0 AND rew_proj_id = $proj_id";
            $rew = \App\Models\Rewrite::select("rew_id", "rew_title", "rew_rewrite", "rew_cat_id", "rew_cit_id", "rew_dis_id", "rew_ward_id", "rew_street_id", "rew_proj_id")->whereRaw(\DB::raw($sqlWhereCity))->first();
            if (!empty($rew)) {
                $rew->rew_title = cutRewriteName($rew->rew_title);
                $arrayReturn[] = $rew->toArray();
            }
        }
        if (intval($result->rew_dis_id) > 0) {
            $sqlWhereCity = "rew_cat_id = " . intval($result->rew_cat_id) . " AND rew_dis_id = " . $result->rew_dis_id . " AND rew_ward_id = 0 AND rew_street_id = 0";
            $rew = \App\Models\Rewrite::selectRaw(\DB::raw("rew_id,rew_title,rew_rewrite,rew_cat_id,rew_cit_id,rew_dis_id,rew_ward_id,rew_street_id,rew_proj_id"))->whereRaw(\DB::raw($sqlWhereCity))->first();
            if (!empty($rew)) {
                $rew->rew_title = cutRewriteName($rew->rew_title);
                $arrayReturn[] = $rew->toArray();
            }
        }
        if (intval($result->rew_ward_id) > 0) {
            $sqlWhereCity = "rew_cat_id = " . intval($result->rew_cat_id) . " AND rew_ward_id = " . $result->rew_ward_id . " AND rew_street_id = 0 ";
            $rew = \App\Models\Rewrite::select("rew_id", "rew_title", "rew_rewrite", "rew_cat_id", "rew_cit_id", "rew_dis_id", "rew_ward_id", "rew_street_id", "rew_proj_id")->whereRaw(\DB::raw($sqlWhereCity))->first();
            if (!empty($rew)) {
                $rew->rew_title = cutRewriteName($rew->rew_title);
                $arrayReturn[] = $rew->toArray();
            }
        }
        if (intval($result->rew_proj_id) > 0) {
            $rew = $result;
            if (!empty($rew)) {
                $rew->rew_title = cutRewriteName($rew->rew_title);
                $arrayReturn[] = $rew->toArray();
            }
        }
    }
    //cache vao redis
    Illuminate\Support\Facades\Cache::remember($key_cache, 86400 * 365, function () use ($arrayReturn) {
        return $arrayReturn;
    });
    $arrayRewId = [];
    foreach ($arrayReturn as $rew) {
        $arrayRewId[] = $rew['rew_id'];
    }
    return $arrayReturn;
    //*/
}

// Đổi ngày ra Hôm nay, Hôm qua....
function today_yesterday_v2($compare_time, $type = 0)
{
    $today = getdate();
    $yesterday = getdate(strtotime("yesterday"));
    $ct = getdate($compare_time);

    if ($type == 0) {
        // Nếu thời gian nhỏ hơn 10h thì show kiểu "10 giờ 30 phút" trước
        $intTime = time() - $compare_time;
        if ($intTime / 3600 <= 10) return generateDuration($intTime, 3, "1 phút") . " trước";
    }

    // Kiểm tra so với hôm nay
    if ($today["mday"] == $ct["mday"] && $today["month"] == $ct["month"] && $today["year"] == $ct["year"]) return "Hôm nay, lúc " . date("H:i", $compare_time);
    if ($yesterday["mday"] == $ct["mday"] && $yesterday["month"] == $ct["month"] && $yesterday["year"] == $ct["year"]) return "Hôm qua, lúc " . date("H:i", $compare_time);
    // Nếu không trùng thì return lại
    return date("d/m/Y - H:i", $compare_time);
}

function generateDuration($int_time, $type = 4, $default = "", $limit_param = 0, $time = array())
{
    $strReturn = "";
    $arrTime = array(86400 => "ngày",
        3600 => "giờ",
        60 => "phút",
        1 => "giây",
    );
    if (is_array($time) && count($time) > 0) $arrTime = $time;
    $i = 0;
    $j = 0;
    foreach ($arrTime as $key => $value) {
        $i++;
        if ($int_time >= $key) {
            $j++;
            $strReturn .= " " . format_number(intval($int_time / $key)) . " " . $value;
            $int_time = $int_time % $key;
            if ($limit_param > 0 && $j >= $limit_param) break;
        }
        if ($i >= $type) break;
    }
    if ($strReturn == "") $strReturn = $default;
    return trim($strReturn);
}

function createClassifiedLog($classified, $money_count, $type_vip)
{
    $fields = ['cat_id', 'cit_id', 'dis_id', 'ward_id', 'street_id', 'proj_id', 'lat', 'lng', 'citid', 'disid', 'wardid', 'streetid', 'use_id'];
    $cla_log = new \App\Models\ClassifiedLog();
    $cla_log->cla_id = $classified->cla_id;
    $cla_log->created_at = time();
    $cla_log->money_count = $money_count;
    $cla_log->type_vip = $type_vip;
    foreach ($fields as $field) {
        $cla_log->{$field} = $classified->{'cla_' . $field};
    }
    return $cla_log->save();
}

function verifyGoogleCaptcha($token)
{
    $captcha = new \Anhskohbo\NoCaptcha\NoCaptcha(env('GOOGLE_CAPTCHA_SECRET'), env('GOOGLE_CAPTCHA_SITE_KEY'));
    if ($captcha->verifyResponse($token)) {
        return true;
    }
    return false;
}

function isMobilePhone($str)
{
    if (preg_match('/((0)|(84)|(\+84))([35789]{1})([0-9]{8})$/', $str)) {
        return true;
    }
    return false;
}

function createSecretKeyApi($cla_id, $user_id = null)
{
    $exp = time() + 10800;
    $data = ['cla_id' => $cla_id];
    if ($user_id != null) $data['user_id'] = $user_id;
    $token = [
        "data" => $data,
        "iat" => time(),
        "exp" => $exp
    ];
    $token = \Firebase\JWT\JWT::encode($token, env("SECRET_FORM_TEMPLATE"));
    return $token;
}

function createJwtToken($key, $data, $expires)
{
    $time_now = time();
    $payload = [
        "data" => $data,
    ];
    $token = \Firebase\JWT\JWT::encode($payload, $key);
    return $token;
}

function whereQueryBuilderV2($where, $model, $connection, $table)
{

    $databases_config = config('alias_database_v2');
    if (isset($databases_config[$connection][$table])) {
        $databases_config = $databases_config[$connection][$table];
        $options = explode(',', $where);
        foreach ($options as $value) {
            $value = explode(" ", $value);
            if (isset($value[1]) && key_exists($value[0], $databases_config)) {
                $data_column = $databases_config[$value[0]];
                $type = $data_column['type'];
                switch ($type) {
                    case 'int':
                        $value[1] = (int)$value[1];
                        $model = $model->where($data_column['column'], $value[1]);
                        break;
                    case 'string':
                        $value[1] = trim($value[1]);
                        if (in_array($value[1], $data_column['values'])) {
                            $model = $model->where($data_column['column'], $value[1]);
                        }
                        break;
                }
            }

        }
    }
    return $model;
}

function isHtml($string)
{
    if (preg_match("#<([a-zA-Z])#ui", $string)) {
        return true;
    } else {
        return false;
    }
}

/**
 * replaceFCK()
 *
 * @param mixed $string
 * @param integer $type
 * @return
 */
function replaceFCK($string, $type = 0)
{
    $array_fck = array("&Agrave;", "&Aacute;", "&Acirc;", "&Atilde;", "&Egrave;", "&Eacute;", "&Ecirc;", "&Igrave;", "&Iacute;", "&Icirc;",
        "&Iuml;", "&ETH;", "&Ograve;", "&Oacute;", "&Ocirc;", "&Otilde;", "&Ugrave;", "&Uacute;", "&Yacute;", "&agrave;",
        "&aacute;", "&acirc;", "&atilde;", "&egrave;", "&eacute;", "&ecirc;", "&igrave;", "&iacute;", "&ograve;", "&oacute;",
        "&ocirc;", "&otilde;", "&ugrave;", "&uacute;", "&ucirc;", "&yacute;",
    );
    $array_text = array("À", "Á", "Â", "Ã", "È", "É", "Ê", "Ì", "Í", "Î",
        "Ï", "Ð", "Ò", "Ó", "Ô", "Õ", "Ù", "Ú", "Ý", "à",
        "á", "â", "ã", "è", "é", "ê", "ì", "í", "ò", "ó",
        "ô", "õ", "ù", "ú", "û", "ý",
    );
    if ($type == 1) $string = str_replace($array_fck, $array_text, $string);
    else $string = str_replace($array_text, $array_fck, $string);

    return $string;
}

/**
 * @param $cla_description
 * @param $cla_title
 * @return string
 */
function validateDescription($cla_description, $cla_title)
{
    $cla_description = removeEmoji($cla_description);
    $cla_description = convertToUnicode($cla_description);
    if (isHtml($cla_description)) {
        $html = new App\helpers\html_cleanup($cla_description);
        $html->removeAttribute(array("table.style","tr.style","td.style","div.style","font.style","span.style","b.style","p.style","strong.style","h1.style","h2.style","h3.style"));
        $html->setIgnoreCheckProtocol();
        //gọi hàm yêu cầu xử lý và download ảnh
        $html->DOMDocument_clean_image(getListDomainMedia(),$cla_title);
        $html->clean();
   //     $arrayImage = arrayVal($html->uploadImage(1100,1800,[]));
        $cla_description = $html->output_html;
        $cla_description = replaceFCK($cla_description,1);
        $cla_description = removeLink($cla_description);
        $cla_description = convertToUnicode($cla_description);
        unset($html);
    } else {
        $cla_description = nl2br($cla_description);
    }
    return $cla_description;
}


function sendNotifySlack($data,$channel){
    $data['date']=date('m-d-Y H:i:s');
    $data['page']=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $data['user_agent']= !empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'null';
    $data['IP']=!empty($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
    $data = collect($data)->map(function($item,$key){
        return "$key : $item \n";
    })->toArray();
    $data = implode(' ',$data);
    $client = new Maknz\Slack\Client($channel);
    $client->to('#payment')->send($data);
}

function str_slug($str){
    return Illuminate\Support\Str::slug($str);
}

function array_get($array,$key,$default=null){
    return arrGetVal($key,$array,$default);
}

function str_random($str){
    \Illuminate\Support\Str::random($str);
}