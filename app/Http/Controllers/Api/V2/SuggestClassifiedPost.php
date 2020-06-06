<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SuggestClassifiedPost extends Controller
{
    /**
     * GET v2/suggest-classified
     *
     *  Lấy dữ liệu trong bộ nhớ cache theo khóa
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@key` | Key cache
     * `@access_token ` | khóa truy cập
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'access_token' => 'required|string',
        ], [
            'key.required' => 'Bạn chưa cung cấp key',
            'key.string' => 'Kiểu dữ liệu của key sai',
            'access_token.required' => 'Không tìm thấy khóa truy cập',
            'access_token.string' => 'Kiểu dữ liệu sai(access_token)',
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return $this->setResponse(500, '', collect($validator->messages())->collapse());
        }

        $data_auth = getDataAuth($request->input('access_token'));
        $user_id = $data_auth->data->id;
        $key = "suggest-classified_" . $request->input('key') . "_" . $user_id;
        $data = Cache::get($key);
        if (!$data) {
            return response($this->setResponse(404, null, 'Not found data autosave!'),200);
        }
        $data = json_decode($data);
        return $this->setResponse(200, $data);
    }


    /**
     * POST v2/suggest-classified
     *
     *  Thêm dữ liệu vào trong bộ nhớ cache theo khóa
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@key` | Key cache
     * `@access_token ` | khóa truy cập
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'access_token' => 'required|string',
        ], [
            'key.required' => 'Bạn chưa cung cấp key',
            'key.string' => 'Kiểu dữ liệu của key sai',
            'access_token.required' => 'Không tìm thấy khóa truy cập',
            'access_token.string' => 'Kiểu dữ liệu sai(access_token)',
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return $this->setResponse(500, '', collect($validator->messages())->collapse());
        }

        if (getDataAuth($request->input('access_token')) !== false) {
            $data_auth = getDataAuth($request->input('access_token'));
            $user_id = $data_auth->data->id;
            $data = $request->all();
            /*
            $filename       = getValue("filename","arr","POST",[]);
            $teaser         = getValue("teaser","arr","POST",[]);
            $type           = getValue("type","arr","POST",[]);
            $w              = getValue("w","arr","POST",[]);
            $h              = getValue("h","arr","POST",[]);

            $listImage      = [];
            //Xử lý phần ảnh
            $arrPictures = [];
            //neu url khong phai cua web thi download anh ve
            if(!empty($filename)){
                $arrayPostImg = array();
                foreach($filename as $key => $val){
                     $arrayDomainImage = getListDomainMedia();
                     $check = false;
                     foreach($arrayDomainImage as $dm){
                         if(strpos($val,$dm) !== false || substr($val,0,4) != "http"){
                             $check = true;
                         };
                     }
                     if(!$check){
                         $arrayPostImg[$key]["url"] = $val;
                         $arrayPostImg[$key]["name"] = $cla_title;
                     }
                }
                if(!empty($arrayPostImg)){
                    //$listImage = downloadPicure($arrayPostImg);
                    $listImage = arrayVal(json_decode($listImage,true));
                }
            }

            $has_picture = 0;
            if(count($type)>0){
                foreach($type as $key=>$item_type){
                    //kiem tra truong hop download anh tu noi khac ve
                    if(isset($listImage[$key])){
                        if($has_picture == 0) $has_picture =1;
                        $arrPictures[$key]['type'] = 'photo';
                        $arrPictures[$key]['filename'] = isset($listImage[$key]["filename"])?$listImage[$key]["filename"]:$listImage[$key]["name"];
                        $arrPictures[$key]['teaser'] = isset($teaser[$key])?trim(removeHTML($teaser[$key])):'';
                        $arrPictures[$key]['width'] = isset($listImage[$key]["width"])?$listImage[$key]["width"]:0;
                        $arrPictures[$key]['height'] = isset($listImage[$key]["height"])?$listImage[$key]["height"]:0;
                    }else{
                        if($has_picture == 0 && $item_type == 'photo' &&isset($filename[$key])&& $filename[$key] !='') $has_picture =1;
                        $arrPictures[$key]['type'] = $item_type;
                        $arrPictures[$key]['filename'] = isset($filename[$key])?$filename[$key]:'';
                        $arrPictures[$key]['teaser'] = isset($teaser[$key])?trim(removeHTML($teaser[$key])):'';
                        $arrPictures[$key]['width'] = isset($w[$key])?$w[$key]:0;
                        $arrPictures[$key]['height'] = isset($h[$key])?$h[$key]:0;
                    }
                }
            }
            $cla_picture = '';
            if($has_picture == 1){
                $data["cla_picture"] = bdsEncode($arrPictures);
            }
            //*/
            $key = "suggest-classified_" . $request->input('key') . "_" . $user_id;
            Cache::forget($key);
            unset($data['access_token']);
            unset($data['key']);
            $dataReponse = [];
            $title          = arrGetVal("cla_title",$data,"");
            $description    = arrGetVal("cla_description",$data,"");
            $arr            = getPrice($title . " " . $description);
            $arr            = explode(",",$arr);
            $cla_price      = $arr[0];
            $arrPhone       = splitPhoneNumber($title . " " . $description);
            $dataReponse["suggestion"]["cla_phone"] = implode(",",$arrPhone);
            $cla_list_acreage   = getAcreage($title . " " . $description);
            Cache::remember($key, 14400, function () use ($data) {
                return json_encode($data);
            });
            $dataReponse["suggestion"]["cla_price"] = $cla_price;
            $dataReponse["suggestion"]["cla_list_acreage"] = $cla_list_acreage;
            $address = arrGetVal("cla_address",$data,"");
            if(!empty($address)){
                $myAddress = new AddressController();
                $arr = $myAddress->SearchDetect($address);
                $dataReponse["suggestion"]["address"] = $arr;
                $myAddress = new LocationController();
                $arr = $myAddress->SearchDetect($address);
                $dataReponse["suggestion"]["location"] = $arr;
            }
            if(!empty($title)){
                $myCategory = new CategoryController();
                $arr = $myCategory->SearchDetect($title);
                $cla_cat_id = isset($arr["id"]) ? intval($arr["id"]) : 0;
                if($cla_cat_id > 0) $dataReponse["suggestion"]["cla_cat_id"] = $cla_cat_id;
            }
            return $this->setResponse(200, $dataReponse);
        } else {
            return $this->setResponse(500, [], 'Auto save fail! Not found user_id');
        }
    }


    /**
     * PUT v2/suggest-classified/{id}
     *
     *  Xóa dữ liệu trong bộ nhớ cache theo khóa
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@key` | Key cache
     * `@access_token ` | Khóa truy cập
     * @return \Illuminate\Http\Response
     */
    function destroy(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'access_token' => 'required|string',
        ], [
            'key.required' => 'Bạn chưa cung cấp key',
            'key.string' => 'Kiểu dữ liệu của key sai',
            'access_token.required' => 'Không tìm thấy khóa truy cập',
            'access_token.string' => 'Kiểu dữ liệu sai(access_token)',
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return $this->setResponse(500, '', collect($validator->messages())->collapse());
        }
        if (checkAuthUser($request->access_token, $id)) {
            $key = "suggest-classified_" . $request->input('key') . "_" . $id;
            $status = Cache::forget($key);
            if ($status) {
                return $this->setResponse(200, 'Delete');
            } else {
                return $this->setResponse(500, null, 'Delete fail');
            }

        }
    }
}
