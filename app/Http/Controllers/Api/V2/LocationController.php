<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Address;
use App\Models\Location;
use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;

/**
 * @resource v2 Locations
 *
 * Api for Locations
 */
class LocationController extends Controller
{
    //


    /**
     * GET v2/locations
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | LocationController
     * `route_name` | locations
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu locations ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@limit` | NULL | Số lượng bản ghi locations cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang locations cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api/categories?where=id+1223,active+1)
     * `@ssr` | boolean | Chế độ gọi api cho web .  Mặc định là `false`
     * `@keyword` | null | Từ khóa tìm kiếm
     * `@location_type` | null | Sử dụng kèm với `type=search`: `location_type` gồm các giá trị : `city`,`district`,`ward`,`street`
     *
     * ### Tùy chọn với tham số `where`:
     *  `where={name_column}+({type_value}){value}`
     *
     *  Giá trị (Value) |  Mô tả chi tiết
     * ----------------  |  -------
     * `name_column` | Tên trường muốn gán điều kiện
     * `type_value` |  Khai báo kiểu giá trị cho value(tham số này có hoặc không),  hỗ trợ kiểu giá trị là `float`. Mặc định là  `int`
     * `value` | Giá trị cần tham chiếu
     *
     * - Có gán kiểu giá trị:    `where=id+(float)234.56,active+0`
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'slug' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
            'ssr' => 'boolean',
            'type' => 'string',
            'key' => 'string'
        ]);
        if ($request->has('type') && $request->type == 'search') {
            if (!$request->has("keyword")) return $this->setResponse(404, null, "Not found key search!!!");
            return $this->searchLocation($request);
        }
        $offset = $this->page * $this->limit - $this->limit;
        $location = new Location();
        $location = $location->select($location->alias($this->fields));
//        if ($this->where != null) $location = whereRawQueryBuilder($this->where, $location, 'mysql', 'locations');
        if ($this->where != null) $location = whereQueryBuilderV2($this->where,$location,'mysql','location');
        $location = $location->offset($offset)->limit($this->limit)->get();
        return $this->setResponse(200, $location);
    }

    /**
     * GET v2/locations/{id}
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID locations
     * `@fields` | List fields locations
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {
        $location = new Location();
        $location = $location->select($location->alias($this->fields))->where('loc_id', $id)->first();
        if ($location) return $this->setResponse(200, $location);
        return $this->setResponse(404, null, 'Not found data');
    }

    function searchLocation($request)
    {
        $terms_elastic_data = [];
        if ($request->input('location_type') == 'city') {
            $terms_elastic_data['type'] = 'city';
        } else if ($request->input('location_type') == 'district') {
            $terms_elastic_data['type'] = 'district';
        } else if ($request->input('location_type') == 'ward') {
            $terms_elastic_data['type'] = 'ward';
        } else if ($request->input('location_type') == 'street') {
            $terms_elastic_data['type'] = 'street';
        }
        if ($request->input('where')) {
            $where = explode(',', $request->input('where'));
            foreach ($where as $item) {
                $item = explode(" ", $item);
                if (isset($item[1])) $terms_elastic_data[$item[0]] = $item[1];
            }
        }
        if ($this->fields != null) {
            $source_elastic_data = explode(',', $this->fields);
        } else {
            $source_elastic_data = ["id", "name", "name_en", "address", "dis_name", "ward_name", "type", "ward_name", "citid", "disid", "ward_id", "street_id", "pre", "cit_id", "dis_id", "ward_id", "lat", "lng", "code"];
        }
        $index_elastic_data = "location";
        $fields_elastic_data = ["name1"];
        $query_elastic_data = BuildTrigrams($request->input('keyword'));
        $data_2 = searchDataElastic($index_elastic_data, $fields_elastic_data, $query_elastic_data, $terms_elastic_data, $source_elastic_data, $this->limit);
        return $this->setResponse(200, $data_2);
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
        $keyword = $request->input("address");
        $arrReturn = $this->SearchDetect($keyword);
        $result["result"] = $arrReturn;
        //$result["value"] = $value;
        return $this->setResponse(200, $result);
    }

    public function SearchDetect($address){
        $result = [];
        $arrWeight = [];
        $address_search = createTextSearchNgram($address);
        $source_elastic_data = ["id", "name", "address","cit_id","dis_id","ward_id","street_id","pre","type","street_name","proj_name","ward_name"];
        $limit = 10;
        $params = [
            'index' => "location",
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
            if(intval($row["dis_id"]) != intval($arrReturn["dis_id"])){
                $result["result"] = $arrReturn;
                return $arrReturn;
            }
            if(intval($arrReturn["ward_id"]) == 0 && intval($row["ward_id"]) > 0 && similar_word($row["name"],$address) > 0.5){
                $arrReturn["ward_id"] = intval($row["ward_id"]);
                $arrReturn["ward_name"] = ($row["ward_name"]);
                $arrReturn["ward_exactly"] = similar_word($row["name"],$address);
            }
            if(intval($arrReturn["street_id"]) == 0 && intval($row["street_id"]) > 0 && similar_word($row["name"],$address) > 0.6){
                $arrReturn["street_id"] = intval($row["street_id"]);
                $arrReturn["street_name"] = ($row["street_name"]);
                $arrReturn["street_exactly"] = similar_word($row["name"],$address);
            }
            //$arrReturn["keyword"] = removeAccent(cleanText($row["name"]));

        }
        return $arrReturn;
    }
}