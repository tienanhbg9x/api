<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ClassIFiedSource;
use App\Models\Link;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use App\Models\Location;
use DateTime;
use \Firebase\JWT\JWT;


class Controller extends BaseController
{
    public $api_token = null;
    public $sphinx = null;

    function __construct()
    {
        if (!isset($_REQUEST['api_token'])) {
            $this->api_token = $this->createToken();
        }

    }

    function filterIdSphinx($arr_id){
        $arr_id = collect($arr_id)->map(function($item){
            return $item['id'];
        });
        return $arr_id;
    }

    function filterClassifieds($classifieds){
        $link_arr = $this->filterClassifiedsLink($classifieds);
        $location_arr = $this->filterClassifiedsLocation($classifieds);
        $classifieds_v2 = [];
        $classifieds->map(function ($item) use (&$classifieds_v2,&$link_arr,&$location_arr) {

                array_push($classifieds_v2, [
                    'id' => $item->cla_id,
                    'title' => $item->cla_title,
                    'rewrite' => $item->cla_rewrite,
                    'address' => isset($location_arr[$item->cla_disid])?$location_arr[$item->cla_disid]:'',
                    'date' => $this->convertDate($item->cla_date),
                    'description' => mb_substr($item->cla_description,0,300),
                    'price' => $item->cla_price,
                    'link' => isset($link_arr[$item->cla_id])?$link_arr[$item->cla_id]:'#',
                    'list_acreage' => $item->cla_list_acreage != "" || $item->cla_list_acreage != null ? explode(',', $item->cla_list_acreage) : null,
                    'picture' => $item->cla_has_picture == 1 ? getUrlPicture($item->cla_picture) : 'https://sosanhnha.com/assets/images/noimage.png',
                    'has_picture' => $item->cla_has_picture
                ]);


        });
        return $classifieds_v2;
    }

    function filterClassifiedsLocation($classifieds){
        $id_dis_arr =[];
        $classifieds->each(function($item,$key) use(&$id_dis_arr){
            if(!isset($id_dis_arr[$item->cla_disid])){
                $id_dis_arr[$item->cla_disid] = $item->cla_disid;
            }
        });
        $list_location = Location::select('loc_id','loc_short_address')->whereIn('loc_id',$id_dis_arr)->get();
        $list_location_arr =[];
        $list_location->each(function($item,$key) use (&$list_location_arr){
            $list_location_arr[$item->loc_id]= $item->loc_short_address;
        });
        return $list_location_arr;
    }

    function filterClassifiedsLink($classifieds){
        $id_arr =[];
        $classifieds->each(function($item,$key) use(&$id_arr){
            if(!isset($id_arr[$item->cla_id])){
                $id_arr[$item->cla_id] = $item->cla_id;
            }
        });
        $list_link = ClassIFiedSource::select('cla_id','cla_link')->whereIn('cla_id',$id_arr)->get();
        $list_link_arr =[];
        $list_link->each(function($item,$key) use (&$list_link_arr){
            $list_link_arr[$item->cla_id]= $this->filterDomain($item->cla_link);
        });
        return $list_link_arr;
    }

    function convertDate($date)
    {
        $dt = DateTime::createFromFormat('U', $date);
        return $dt->format('d/m/Y');
    }

    function getCategories()
    {
        $categories = Link::select('lin_id as id', 'lin_rewrite as rewrite', 'lin_title as title')->where('lin_citid', 0)->limit(30)->get();
        return $this->setResponse(200,$categories);
    }

    function setResponse($status,$data=null,$message_error=''){
        if($status!=200){
            return [
              'status'=>$status,
              'message'=>$message_error
            ];
        }else{
            $data_return = ['status'=>200,'data'=>$data];
            if($this->api_token!=null) $data_return['api_token']=$this->api_token;
            return $data_return;
        }
    }

    function filterDomain($url)
    {
        if($url!=''){
            $url = explode('//', $url);
            $url = explode('/', $url[1]);
            return $url[0];
        }else{
            return '';
        }

    }

    function getCity()
    {
        if (!Cache::has('list-city')) {
            Cache::remember('list-city', 120, function () {
                $cities = Location::select('loc_name as name', 'loc_id as id')->where('loc_type', 'city')->get();
                return $cities;
            });
        }
        $cities = Cache::get('list-city');
        return $this->setResponse(200,$cities);
    }

    function createToken()
    {
        $data = str_random(30);
        $key = "example_key";
        $token = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "browser_id" => $data,
        );
        $jwt = JWT::encode($token, $key);
        return $jwt;
    }

    function decodeToken($data)
    {
        $jwt = JWT::decode($data, 'example_key', array('HS256'));
    }


    //
}
