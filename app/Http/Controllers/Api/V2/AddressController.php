<?php

namespace App\Http\Controllers\Api\V2;

use App\Helper\ElasticSearchQuery;
use App\Models\Address;
use Illuminate\Http\Request;

/**
 * @resource v2 Address
 *
 * Api for Address
 */
            

class AddressController extends Controller
{


    function index(Request $request){
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3'
        ]);
        if($request->input('type')=='search'){
            if($request->input('key_search')){
                return $this->searchAddress($request->input('key_search'));
            }else{
                return response($this->setResponse(500,null,'Not found param `key_search`'),500);
            }
        }
        $offset = $this->page * $this->limit - $this->limit;
        $address = new Address();
        $address = $address->select($address->alias($this->fields));
        if ($this->where != null) $address = whereQueryBuilderV2($this->where, $address, 'mysql', 'address');
        $address = $address->orderBy('add_name', 'asc')->offset($offset)->limit($this->limit)->get();
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'address' => $address,

        ];
        return $this->setResponse(200, $data);
    }

    /**
     * GET v2/detect
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@address` | Địa chỉ cần nhận diện
     * @return \Illuminate\Http\Response
     */
    function Detect(Request $request){
        $result = [];
        $arrWeight = [];
        $address = $request->input("address");
        $result["result"] = $this->SearchDetect($address);
        //$result["value"] = $value;
        return $this->setResponse(200, $result);
    }

    function searchAddress($keyword){
        $keyword =  createTextSearchNgram($keyword);
        $address = new ElasticSearchQuery('address','_doc');
        $address = $address->select($this->fields)->queryString('name_search',$keyword);
        $arr_where = explode(',',$this->where);
        foreach ($arr_where as $where){
            $where = explode(" ",$where);
            $address = $address->where($where[0],$where[1]);
        }
        $offset = $this->page * $this->limit - $this->limit;
        $address = $address->offset($offset)->limit($this->limit)->get(true);
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'address' => $address,
        ];
        return $this->setResponse(200,$data);

    }

    function show($id){
        $address =  new Address();
        $address = $address->select($address->alias($this->fields))->where('add_id',$id)->first();
        if($address){
            return $this->setResponse(200,$address);
        }
        return response($this->setResponse(500,'Not found id:'.$id),500);
    }

    public function SearchDetect($address){
        $result = [];
        $arrWeight = [];
        $address_search = createTextSearchNgram($address);
        $source_elastic_data = ["id", "name", "address","citid","disid","projid","wardid","streetid","pre","type","street_name","proj_name","ward_name"];
        $limit = 10;
        $params = [
            'index' => "address",
            'type' => '_doc',
            'body' => [
                "query" => [
                    "function_score" => [
                        "query" => [
                            "bool" => [
                                "should" => [
                                    [
                                        "match" => [
                                           "search" => [
                                               "query" => $address_search,
                                               "boost" => 10
                                           ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "_source" => $source_elastic_data,
                "size" => $limit
            ]
        ];



        $data_search = app('elastic')->search($params);
        $value = null;
        if(isset($data_search['hits']['hits'])){
            $value = [];
            foreach ($data_search['hits']['hits'] as $data){
                $value[] = $data['_source'];
            }
            if(isset($value[0])){
                if($limit==1){
                    $value = $value[0];
                }
            }
        }
        $position = $limit+1;
        foreach ($value as $key => $row){
            if(!isset($arrWeight[$row["citid"]])){
                $arrWeight[$row["citid"]] = $position;
            }else{
                $arrWeight[$row["citid"]]++;
            }
            if(!isset($arrWeight[$row["disid"]])){
                $arrWeight[$row["disid"]] = $position;
            }else{
                $arrWeight[$row["disid"]]++;
            }
            if(!isset($arrWeight[$row["wardid"]])){
                $arrWeight[$row["wardid"]] = $position;
            }else{
                $arrWeight[$row["wardid"]]++;
            }
            if(!isset($arrWeight[$row["streetid"]])){
                $arrWeight[$row["streetid"]] = $position;
            }else{
                $arrWeight[$row["streetid"]]++;
            }
            $position--;
        }
        foreach ($arrWeight as $key => $val){
            $arrWeight[$key] = $val / 20;
        }

        //$result["weight"] = $arrWeight;
        //return $this->setResponse(200, $value);
        $arrReturn = [];
        foreach ($value as $key => $row){
            if(empty($arrReturn)){
                $exactly = similar_word($row["name"],$address);
                if($exactly < 0.7){
                    continue;
                }else{
                    $arrReturn = $row;
                    $arrReturn["exactly"] = $exactly;
                }
            }
            if(intval($row["disid"]) != intval($arrReturn["disid"])){
                $result["result"] = $arrReturn;
                return $arrReturn;
            }
            if(intval($arrReturn["wardid"]) == 0 && intval($row["wardid"]) > 0 && similar_word($row["name"],$address) > 0.5){
                $arrReturn["wardid"] = intval($row["wardid"]);
                $arrReturn["ward_name"] = ($row["ward_name"]);
                $arrReturn["ward_exactly"] = similar_word($row["name"],$address);
            }
            if(intval($arrReturn["streetid"]) == 0 && intval($row["streetid"]) > 0 && similar_word($row["name"],$address) > 0.6){
                $arrReturn["streetid"] = intval($row["streetid"]);
                $arrReturn["street_name"] = ($row["street_name"]);
                $arrReturn["street_exactly"] = similar_word($row["name"],$address);
            }
            if(intval($arrReturn["projid"]) == 0 && intval($row["projid"]) > 0 && similar_word($row["name"],$address) > 0.8){
                $arrReturn["projid"] = intval($row["projid"]);
                $arrReturn["proj_name"] = ($row["proj_name"]);
            }
            //$arrReturn["keyword"] = removeAccent(cleanText($row["name"]));

        }
        return $arrReturn;
    }
        
}
