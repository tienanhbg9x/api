<?php

namespace App\Http\Controllers\Api\V2;

use App\Helper\Rest_Request_Baokim;
use App\Models\Category;
use App\Transformers\CategoryTransformer;
use App\Transformers\CategoryWithChildsTransformer;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @resource V2 Categories
 *
 * Info route | Description
 * --------- | ---------
 * `version_route` | Version v1
 * `controller` | CategoryController
 * `route_name` | categories
 */
class CategoryController extends Controller
{

    /**
     * GET v2/categories
     *
     * Lấy danh sách Danh mục
     * 
     * Param | Default | Availability Value, Description
     * ------ | ------ | ------ | ------
     * `@module` | product | Kiểu Danh mục: `product`, `raovat`, `hoidap`
     * `@fields` | NULL | Danh sách các trường dữ liệu: `id, name, type, parent_id, has_child, all_child, exclusive, ppc_price, total_product, root, link, picture, icon`
     * `@with` | NULL | Lấy kèm cấp con hay ko: `childs`, Lấy tất cả cách danh mục : `all`
     * `@parent_id` | 0 | Lấy theo ID cấp trên: `{category_id}`
     *
     * ### Ví dụ theo Danh mục Sản phẩm, các danh mục Rao vặt, Hỏi đáp tương tự
     *
     * - Danh mục cấp 1: `/api/v2/categories?module=product`
     *
     * - Toàn bộ danh mục và cấp con: `/api/v2/categories?module=product&with=childs`
     *
     * - Danh mục có ID cấp trên là 1300, chỉ trả về id, name: `/api/v2/categories?module=product&parent_id=3849&fields=id,name`
     *
     * - Danh mục có ID cấp trên là 1300 và các cấp con: `/api/v2/categories?module=product&parent_id=1300&with=childs`
     *
     * @return \Illuminate\Http\Response|mixed
     */

    public function index(Request $request)
    {
        $data = [];
        $array_with = ['childs'];

        $this->validate($request, [
            'module' => 'string|max:255',
            'fields' => 'string|max:255',
            'with' => 'string|max:255',
            'parent_id' => 'integer',
        ]);

        $module = $request->get('module', '');
        $fields = $request->get('fields');
        $with = $request->get('with');
        if($with=='all'){
            $categories = new Category();
            $categories = $categories->select($categories->alias($this->fields))->where('cat_active',1)->get();
            $arr_categories = [];
            foreach ($categories as $category){
                $arr_categories[] = $this->formatField($category);
            }
            return $this->setResponse(200,$categories);
        }
        $with = array_intersect(explode(',', $with), $array_with);
        $parent_id = intval($request->get('parent_id', 0));
        $parent_id = (!$parent_id && $module == 'product' ? [575, 584] : (array)$parent_id);

        $categories = new Category();
        $categories = $categories->fields($fields)->with($with)->where('cat_active', 1);
        if(!empty($module)) $categories = $categories->where('cat_type', $module);
        if($parent_id > 0) $categories = $categories->whereIn('cat_parent_id', $parent_id);
        $categories = $categories->orderBy('cat_order', 'ASC')->orderBy('cat_name', 'ASC')->get();

        if ($categories->count() > 0) {
            if (in_array('childs', $with)) $category_transformer = new CategoryWithChildsTransformer($fields);
            else $category_transformer = new CategoryTransformer($fields);
            $data = transformer_collection($categories, $category_transformer, $with);
        }

        return $this->setResponse(200,$data);

    }

    /**
     * GET v2/categories/{category_id}
     *
     * Lấy thông tin chi tiết một Danh mục
     *
     * Param | Default | Availability Value | Description
     * --------- | --------- | --------- | ---------
     * `@fields` | NULL | id,name,type,parent_id,has_child,all_childs | Danh sách các trường dữ liệu
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $category_id)
    {
        $this->validate($request, [
            'fields' => 'string|max:255'
        ]);
        $category = new Category();
        $category = $category->select($category->alias($this->fields))->where('cat_active',1)->find($category_id);
        $category = $this->formatField($category);
        return $this->setResponse(200,$category);

    }

    function formatField($category){
        if(isset($category->cla_field_check)){
            $category->cla_field_check = json_decode($category->cla_field_check);
        }
        if(isset($category->att_id)){
            $category->att_id = json_decode($category->att_id);
        }
        return $category;
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
        $keyword = $request->input("keyword");
        $value = $this->SearchDetect($keyword);
        $result["result"] = $value;
        return $this->setResponse(200, $result);
    }

    function SearchDetect($keyword){
        $result = [];
        $arrWeight = [];
        $keyword_search = createTextSearchNgram($keyword);
        $source_elastic_data = ["id", "name"];
        $value = searchDataElastic("categories_multi",["search"],$keyword_search,["type" => ["chothue","bannhadat"]],["id","name","type"],1);
        return $value;
    }

    function findCatId($keyword){
        $keyword_search = createTextSearchNgram($keyword);
        $params = ["index" => "categories_multi","type" => "_doc", "size" => 1,"from" => 0];
        $params["_source"] = ["id","name","type"];
        $params['body'] =  [
                        'query' => [
                            'bool' => []
                        ]
                ];
        $params['body']["query"]["bool"]["must"][]["simple_query_string"] =  [
                                            "fields" => ["search"],
                                            "query" => $keyword_search
                                        ];
        $params['body']["query"]["bool"]["must"][]["bool"] = [
                                                              "should" => [
                                                                  ["term" => ["type" => "chothue"]],
                                                                  ["term" => ["type" => "bannhadat"]]
                                                              ]
                                                             ];
        $data_search = app('elastic')->search($params);
        $data_search = getSourceElastic($data_search);
        //print_r($data_search);
        foreach ($data_search as $row){
            return $row["id"];
        }
        return 0;
    }


}
