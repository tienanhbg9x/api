<?php

namespace App\Http\Controllers\Api\V2;

use App\Jobs\JobSendMailUpdateVip;
use App\Jobs\sendMailNotification;
use App\Models\Category;
use App\Models\Classified;
use App\Models\ClassifiedFilter;
use App\Models\ClassifiedVip;
use App\Models\Location;
use App\Models\Rewrite;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use DB;

/**
 * @resource V2 Classifies
 *
 * Api for page Classify
 */
class ClassifiedController extends Controller
{
    protected $request = null;

    public $data_tmp = [];

    protected $field_table = [
        'cla_id' => 'id',
        'cla_title' => 'title',
        'cla_rewrite' => 'rewrite',
        'cla_cat_id' => 'cat_id',
        'cla_cit_id' => 'cit_id',
        'cla_dis_id' => 'dis_id',
        'cla_ward_id' => 'ward_id',
        'cla_street_id' => 'street_id',
        'cla_active' => 'active',
        'cla_proj_id' => 'proj_id',
        'cla_date' => 'date',
        'cla_expire' => 'cla_expire',
        'cla_use_id' => 'use_id',
        'cla_mobile' => 'mobile',
        'cla_description' => 'description',
        'cla_teaser' => "teaser",
        'cla_price' => 'price',
        'cla_picture' => 'picture',
        'cla_list_acreage' => 'list_acreage',
        'cla_list_price' => 'list_price',
        'cla_list_badroom' => 'list_badroom',
        'cla_list_toilet' => 'list_toilet',
        'cla_vg_id' => 'vg_id',
        'cla_lat' => 'lat',
        'cla_lng' => 'lng',
        'cla_fields_check' => 'fields_check',
        'cla_search' => 'search',
        'cla_type' => 'type',
        'cla_feature' => 'feature',
        'cla_type_cat' => 'type_cat',
        'cla_type_vip' => 'type_vip',
        'cla_address' => 'address',
        'cla_phone' => 'phone',
        'cla_email' => 'email',
        'cla_contact_name' => 'contact_name',
    ];

    /**
     * GET v2/classifieds
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@limit` | 30 |  Giới hạn bảng ghi lấy về
     * `@type` | null | Tùy chọn điều kiện lấy dữ liệu
     * `@fiedls` | null | Trường dữ liệu muốn lấy (id,name,...)
     * `@slug` | null | rewrite của category. Để  sử dụng được bắt buộc phải có `type=category` gửi kèm theo.
     * `@page` | 1 | Phân trang
     * `@keyword` | null | Từ khóa tìm kiếm
     * `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api?where=id+1223,title+ads-asd-fasdf)
     * `@permissions`| null | Quyền lấy dữ liệu đặc biệt
     * `@order`| null | Kiểu  sắp xếp (Nhận 2 hai giá trị  date,id)
     * `@ssr` | boolean | Chế độ gọi api cho web .  Mặc định là `false`
     * `@access_token`| null | Khóa truy cập các thao tác đặc biệt với database
     *
     * ### Tùy chọn với tham số `type`:
     *  Giá trị (Value) | Điều kiện | Mô tả chi tiết
     * --------- | ------- | -------
     * `category` |  Phải có tham số `slug` đi kèm | Lấy bản ghi của danh mục với `slug` tương ứng
     * `detail` |  Phải có tham số `slug` đi kèm  | Lấy chi tiết một bản classified  với `slug` lương ứng
     * `search` | Phải có tham số  `keyword` đi kèm |Lọc classifieds theo từ khóa theo `keyword` tương ứng
     * `manage_data` | Phải có tham số `access_token` đi kèm | Lấy dữ liệu, kể cả dữ liệu bị ẩn (yêu cầu quyền người dùng, chỉ dùng cho tính năng quản lí dữ liệu)
     *
     * ### Tùy chọn với tham số `permissions`:
     *  Giá trị (Value) | Điều kiện | Mô tả chi tiết
     * --------- | ------- | -------
     * `manage_data` | null | Lấy dữ liệu, kể cả dữ liệu bị ẩn (yêu cầu quyền người dùng, chỉ dùng cho tính năng quản lí dữ liệu)
     *
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
            'type' => 'string',
            'fiedls' => 'string|min:1|max:255',
            'slug' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'keyword' => 'string|max:255|min:1',
            'where' => 'string|max:255|min:3',
            'ssr' => 'boolean'
        ]);
        $this->request = $request;
        if ($request->input('type')) {
            $type = $request->input('type');
            if ($type == 'category') {
                if ($request->input('slug')) {
                    try {
                        return $this->getClassifiedsCategory();
                    } catch (\Exception $error) {
                        return $this->setResponse(500, null, $error->getMessage());
                    }

                } else {
                    return $this->setResponse(404, null, 'not found param `slug`');
                }
            } else if ($type == 'detail') {
                if ($request->input('slug')) {
                    return $this->getClassified($request->input('slug'));
                } else {
                    return $this->setResponse(404, null, 'not found param `slug`');
                }
            } else if ($type == 'search') {

                $keyword = $request->input('keyword') ? convertToUnicode(trim($request->input('keyword'))) : '';
                return $this->searchClassifieds($keyword);
            }
        }
        $classifieds = new Classified();
        $offset = $this->page * $this->limit - $this->limit;
        $classifieds = $classifieds->select($classifieds->alias($this->fields));
        if ($request->input('permissions') == 'manage_data') {
            if (!checkAuth($request->input('access_token'))) {
                return $this->setResponse(401, null, 'Không có quyền thao tác!');
            }
        } else {
            $classifieds = $classifieds->where('cla_active', 1);
        }
        if ($this->where != null) $classifieds = whereRawQueryBuilder($this->where, $classifieds, 'mysql', 'classifieds');
        if ($request->input('order') == 'date') {
            $classifieds = $classifieds->orderBy('cla_date', 'desc');
        } else {
            $classifieds = $classifieds->orderBy('cla_id', 'desc');
        }
        $classifieds = $classifieds->offset($offset)->limit($this->limit)->get();
        $classifieds = $this->filterClassifieds($classifieds);
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'classifieds' => $classifieds
        ];
        return $this->setResponse(200, $data);
    }

    /**
     * GET v2/classifieds/{id}
     *
     *  Lấy tin tức theo id
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classifieds
     * `@fields` | List fields classifieds
     * `@type=vip`| Lấy cả dữ liệu Vip
     * @return \Illuminate\Http\Response
     */
    function show(Request $request, $id)
    {
        if (($this->fields != null && strpos('user_favorite', $this->fields) != -1) && $request->input('type') == 'detail_cla') {
            if(!is_numeric($id)){
                $id = decodeHashId($id);
                if (empty($id)){
                    return response('Not found id', 500);
                }else{
                    $id = current($id);
                }
            }

            $user_id = getUserId($this->access_token);
            $classified = new Classified();
            $classified = $classified->select($classified->alias($this->fields))->where('cla_id', $id)->where('cla_active', 1)->first();
            $classified = $this->filterClassified($classified, 'detail');
            if ($user_id != 0) {
                $use_favorites = UserFavorite::select('usf_cla_id')->where('usf_use_id', $user_id)->where('usf_cla_id', $id)->first();
                $classified->use_favorite = ($use_favorites != null) ? 1 : 0;
            }
            return $this->setResponse(200, $classified);
        } else {
            $classified = new Classified();
            $classified = $classified->select($classified->alias($this->fields));
            if ($request->input('permissions') == 'manage_data') {
                if (!checkAuth($request->input('access_token'))) {
                    return response($this->setResponse(401, null, 'Không có quyền thao tác!'), 401);
                }
            } else {
                $classified = $classified->where('cla_active', 1);
            }

            if (is_numeric($id)) {
                $classified = $classified->where('cla_id', $id)->first();
            } else {
                $id = decodeHashId($id);
                if (empty($id)) return response('Not found id', 500);
                $classified = $classified->where('cla_id', $id[0])->first();
            }
        }

        if ($classified) {
            if (isset($classified->picture)) {
                $pictures = bdsDecode($classified->picture);
                if ($pictures != "") {
                    if (isset($pictures[0]['type'])) {
                        foreach ($pictures as $key => $picture) {
                            if ($picture['type'] == 'photo') {
                                $picture['url'] = getUrlImageByRow($picture);
                            } else if ($picture['type'] == 'video') {
                                $picture['url'] = "https://img.youtube.com/vi/" . $picture['filename'] . "/sddefault.jpg";
                            }
                            $pictures[$key] = $picture;
                        }
                    } else {
                        foreach ($pictures as $key => $picture) {
                            $picture['teaser'] = "";
                            $picture['filename'] = $picture['name'];
                            unset($picture['name']);
                            $picture['url'] = getUrlImageByRow($picture);
                            $picture['type'] = 'photo';
                            $pictures[$key] = $picture;
                        }
                    }
                    $classified->picture = $pictures;
                } else {
                    $classified->picture = [];
                }
            }
            return $this->setResponse(200, $classified);
        }
        return response($this->setResponse(404, null, 'Not found!'));
    }


    function searchClassifieds($keyword)
    {
        $terms_elastic_data = [];
        if ($this->request->input('where') != null) {
            $where_list = explode(',', $this->request->input('where'));
            $field_where = config('alias_database.mysql.classifieds');
            foreach ($where_list as $data) {
                $data = explode(' ', $data);
                if (isset($data[1])) {
                    $terms_elastic_data[array_search($data[0], $field_where)] = (int)$data[1];
                }
            }
        }
        $source_elastic_data = [];
        if ($this->fields != null) {
            foreach ($this->field_table as $key => $value) {
                if (strpos($this->fields, $value) !== false) {
                    $source_elastic_data[] = $key;
                }
            }
        } else {
            $source_elastic_data = array_keys($this->field_table);
        }
        $index_elastic_data = "bds_classifieds";
        $fields_elastic_data = ["cla_title"];
        $query_elastic_data = $keyword;
        $_doc = 'classifieds';
        $offset = $this->page * $this->limit - $this->limit;
        $classifieds = searchDataElasticV2($index_elastic_data, $fields_elastic_data, $query_elastic_data, $terms_elastic_data, null, $source_elastic_data, 30, $_doc, $offset);
//        $classifieds = searchDataElastic($index_elastic_data, $fields_elastic_data, $query_elastic_data, $terms_elastic_data, $source_elastic_data, 29,$_doc);
        $classifieds = $this->filterClassifiedElasticSearch($classifieds);
        //searchDataElastic();
//        $keyword = urldecode($keyword);
//        $sphinx = app()->make('sphinx');
//        $offset = $this->page * $this->limit - $this->limit;
//        $query_sphinx = $sphinx->select('id')->from('bds_classifieds');
//        if ($this->request->input('permissions') == 'manage_data') {
//            if (!checkAuth($this->request->input('access_token'))) {
//                return $this->setResponse(401, null, 'Không có quyền thao tác!');
//            }
//        } else {
//            $query_sphinx = $query_sphinx->where('cla_active', '=', 1);
//        }
//        if ($this->where != null) $query_sphinx = whereRawQueryBuilder($this->where, $query_sphinx, 'mysql', 'classifieds');
//        $query_sphinx->match('cla_title', $keyword);
//        $query_sphinx->orderBy('id', 'desc');
//        $query_sphinx->limit($offset, $this->limit);
//        $result = $query_sphinx->execute()->fetchAllAssoc();
//        $arr_id = $this->filterIdSphinx($result);
//        $info_sphinx_query = collect($sphinx->query('show meta;')->execute()->fetchAllAssoc());
//        $total_record_found = $info_sphinx_query->where('Variable_name', 'total_found')->first();
//        $total_record_found = isset($total_record_found['Value']) ? $total_record_found['Value'] : 0;
//        $classifieds = new Classified();
//        $classifieds = $classifieds->select($classifieds->alias($this->fields))
//            ->whereIn('cla_id', $arr_id)->orderBy('cla_id', 'desc')->get();
//        $classifieds = $this->filterClassifieds($classifieds);
        //Create meta tag
//        $meta = [];

//        $meta['title'] = "Kết quả tìm kiếm  bất động sản theo từ khóa '$keyword'";
//        $title = $meta['title'];
//        $meta['og:title'] = "Kết quả tìm kiếm  bất động sản theo từ khóa '$keyword'";
//        $meta['description'] = mb_substr("Kết quả tìm kiếm  bất động sản theo từ khóa '$keyword'", 0, 200);
//        $meta['og:description'] = mb_substr("Kết quả tìm kiếm  bất động sản theo từ khóa '$keyword'", 0, 200);
//        $meta['revisit-after'] = "1 days";
//        $meta['og:url'] = env('APP_DOMAIN') . '/search?keyword=' . $keyword;
//        $meta['DC.language'] = "scheme=utf-8 content=vi";
//        $meta['robots'] = "index, follow";
//        $meta['og:image'] = config('app.thumbnail_default');
//        $meta['og:image:width'] = '400px';
//        $meta['og:image:height'] = '400px';
//        $meta['og:image:type'] = 'png';
//        $meta = createMeta($meta);
        $data = [
            'total_record' => 29,
//            'meta' => $meta,
//            'title' => $title,
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'classifieds' => $classifieds,

        ];
        return $this->setResponse(200, $data);
    }


    function getClassified($link)
    {
        //hai_fix
//        if($this->access_token!=null&&strpos('user_favorite',$this->fields)!=-1){
//            $user_id = getUserId($this->access_token);
//            $use_favorites = UserFavorite::select('usf_cla_id')->where('usf_use_id',$user_id)->get()->map(function($item){return $item->usf_cla_id;})->toArray();
//        }
        //end hai_fix

        $link = urldecode($link);
        $id = getUrlHashId($link);
        if ($id == 0) {
            return $this->setResponse(404, null, 'Not found `slug`');
        } else {
            $classified = new Classified();
            $classified = $classified->select($classified->alias())
                ->where('cla_id', $id)->first();

            if ($classified) {
                $classified = $this->filterClassified($classified, 'detail');
                $data_return = [
                    'classified' => $classified,
                    'title' => $classified->title,
                    'type_page' => 'classified'
                ];
                return $this->setResponse(200, $data_return);

            } else {
                return response($this->setResponse(404, null, "Not found url"), 404);
            }

        }
    }


    function getClassifiedsCategory($rewrite = null)
    {
//        $offset = $this->page * $this->limit - $this->limit;
//        $slug = urldecode($_REQUEST['slug']);
//        $slug_md5 = md5($slug);
//        if ($rewrite == null) {
//            $rewrite = Rewrite::select('rew_id', 'rew_title', 'rew_cat_id', 'rew_cit_id', 'rew_dis_id', 'rew_ward_id', 'rew_street_id', 'rew_proj_id', 'rew_keyword', 'rew_picture')->where('rew_length', strlen($slug))->where('rew_md5', $slug_md5)->first();
//        } else {
////            $this->fields = 'id,title,rewrite,date,picture,teaser,price,list_acreage,type_vip,address';
//        }
//        if ($rewrite) {
//            $sort = ['field'=>'cla_date','type'=>'desc'];
//            $arr_terms = ['cla_active'=>1];
//            $index_elastic_data = "bds_classifieds";
//            $query_elastic_data = null;
//            $range_query = null;
//            $source_elastic_data = ["cla_id", "cla_title", "cla_expire","cla_address", "cit_name", "cla_cat_id", "cla_rewrite","cla_street_id","cla_date","cla_ward_id","cla_cit_id","cla_cate_id","cla_dis_id","cla_list_toilet","cla_list_acreage", "cla_list_badroom","cla_picture",'cla_type_vip','cla_type','cla_price','cla_teaser','cla_list_price'];
//            if ($rewrite->rew_cat_id != 0) $arr_terms['cla_cat_id'] = $rewrite->rew_cat_id;
//            if ($rewrite->rew_cit_id != 0) $arr_terms['cla_cit_id'] = $rewrite->rew_cit_id;
//            if ($rewrite->rew_dis_id != 0) $arr_terms['cla_dis_id']  =$rewrite->rew_dis_id;
//            if ($rewrite->rew_ward_id != 0) $arr_terms['cla_ward_id']  =$rewrite->rew_ward_id;
//            if ($rewrite->rew_street_id != 0) $arr_terms['cla_street_id']  =$rewrite->cla_street_id;
//            if ($rewrite->rew_proj_id != 0) $arr_terms['cla_proj_id']  =$rewrite->cla_proj_id;
//
//            $results = searchDataElasticV2($index_elastic_data,null,$query_elastic_data,$arr_terms,$range_query,$source_elastic_data,30,'classifieds',$offset,true,$sort);
//
//            $classifieds = $this->filterClassifiedElasticSearch($results);
//            $data = [
//                'type_page' => 'category',
//                'title' => $rewrite->rew_title ? $rewrite->rew_title : null,
//                'classifieds' => $classifieds,
//                'current_page' => $this->page,
//                'per_page' => $this->limit
//            ];
//
//            return $this->setResponse(200, $data);
//        } else {
//            return response('Not found link',404);
//        }
        //hai_fix
//        dd($rewrite);
        $slug = urldecode($_REQUEST['slug']);
        $slug_md5 = md5($slug);
        if ($rewrite == null) {

            $rewrite = Rewrite::select('rew_id', 'rew_title', 'rew_cat_id', 'rew_cit_id', 'rew_dis_id', 'rew_ward_id', 'rew_street_id', 'rew_proj_id', 'rew_keyword', 'rew_picture')->where('rew_length', strlen($slug))->where('rew_md5', $slug_md5)->first();
        } else {

//            $this->fields = 'id,title,rewrite,date,picture,teaser,price,list_acreage,type_vip,address';
        }

        if ($rewrite) {
//
            $classified_filter = ClassifiedFilter::select('cla_id')->where('cla_cat_id', $rewrite->rew_cat_id);
            if ($rewrite->rew_cit_id != 0) $classified_filter->where('cla_cit_id', $rewrite->rew_cit_id);
            if ($rewrite->rew_dis_id != 0) $classified_filter->where('cla_dis_id', $rewrite->rew_dis_id);
            if ($rewrite->rew_ward_id != 0) $classified_filter->where('cla_ward_id', $rewrite->rew_ward_id);
            if ($rewrite->rew_street_id != 0) $classified_filter->where('cla_street_id', $rewrite->rew_street_id);
//            if ($rewrite->rew_active != 0) $classified_filter->where('cla_active', $rewrite->rew_active);
            if ($rewrite->rew_proj_id != 0) $classified_filter->where('cla_proj_id', $rewrite->rew_proj_id);
            $classified_filter = $classified_filter->where('cla_active', 1);
            $classified_filter = $classified_filter->offset($this->page * $this->limit - $this->limit)->take(30)->get()->map(function ($item) {
                return $item->cla_id;
            })->toArray();
            $classifieds = new Classified();
            $classifieds = $classifieds->select($classifieds->alias('id,title,rewrite,price,address,picture,has_picture,date,list_acreage,active'))->whereIn('cla_id', $classified_filter)->get();
            $classifieds = $this->filterClassifieds($classifieds);
            $data = [
                'type_page' => 'category',
                'title' => $rewrite->rew_title ? $rewrite->rew_title : null,
                'classifieds' => $classifieds,
                'current_page' => $this->page,
                'per_page' => $this->limit
            ];

            return $this->setResponse(200, $data);
        } else {
            return response('Not found link', 404);
        }
        //end hai_fix
    }

    function filterIdSphinx($arr_id)
    {
        $arr_id = collect($arr_id)->map(function ($item) {
            return $item['id'];
        });
        return $arr_id;
    }

    function filterClassifieds($classifieds)
    {

        if (strpos($this->fields, 'value_fields_check') !== false && strpos($this->fields, 'cat_id') !== false && strpos($this->fields, 'fields_check') !== false) {
            $classifieds = $this->checkFieldClassified($classifieds);
        }
        $classifieds = $classifieds->map(function ($item) use (&$location_arr) {
            return $this->filterClassified($item);
        });
        //hai_fix
        if ($this->access_token != null && strpos('user_favorite', $this->fields) != -1) {
            $user_id = getUserId($this->access_token);
            $arr_cla_id = [];
            foreach ($classifieds as $cla) {
                $arr_cla_id[] = $cla['id'];
            }
            if (count($arr_cla_id) == 0) {
                $use_favorites = [];
            } else {
                $use_favorites = UserFavorite::select('usf_cla_id')->where('usf_use_id', $user_id)->whereIn('usf_cla_id', $arr_cla_id)->get()->map(function ($item) {
                    return $item->usf_cla_id;
                })->toArray();
            }
            foreach ($classifieds as $cla) {
                if (in_array($cla['id'], $use_favorites)) $cla['use_favorite'] = 1;
                else $cla['use_favorite'] = 0;
            }
        }

        //end hai_fix
        return $classifieds;
    }

    function filterClassifiedElasticSearch($classifieds)
    {

//        hai_fix
        if ($this->access_token != null && strpos('user_favorite', $this->fields) != -1) {
            $user_id = getUserId($this->access_token);
            $arr_cla_id = [];
            foreach ($classifieds as $cla) {
                $arr_cla_id[] = $cla['cla_id'];
            }
            if (count($arr_cla_id) == 0) {
                $use_favorites = [];
            } else {
                $use_favorites = UserFavorite::select('usf_cla_id')->where('usf_use_id', $user_id)->whereIn('usf_cla_id', $arr_cla_id)->get()->map(function ($item) {
                    return $item->usf_cla_id;
                })->toArray();
            }
        }
        //end hai_fix
        foreach ($classifieds as $key_cla => $item) {
            $item_map = [];
            foreach ($item as $key => $value) {
                //hai_fix
                if ($this->access_token != null && strpos('user_favorite', $this->fields) != -1) {
                    $item_map['use_favorite'] = in_array($item['cla_id'], $use_favorites) ? 1 : 0;
                }
                //end hai_fix
                if ($key == 'cla_address') {
                    $item_map['address'] = showAddresFromList($value);
                    continue;
                }
                if ($key == 'cla_date') {
//                    $item_map['date'] = date('d/m/Y h:i:s',$value);
                    $item_map['date'] = today_yesterday_v2($value);
                    continue;
                }
                if ($key == 'cla_price') {
                    $item_map['price'] = showPriceFromList($value);
                    continue;
                }
                if ($key == 'cla_picture') {
                    $arrayPicture = bdsDecode($value);
                    $item_map['picture'] = ($value != null && $value != "") ? getUrlImageMain($arrayPicture, 150) : config('app.thumbnail_default');
                    continue;
                }
                if ($key == 'cla_list_acreage') {
                    $item_map['list_acreage'] = !empty($value) ? implode(',', $value) : null;
                    continue;
                }
                $item_map[$this->field_table[$key]] = $value;
            }
            $classifieds[$key_cla] = $item_map;
        };
        return $classifieds;
    }

    function filterClassified($classified, $type = null)
    {
        if (isset($classified->address)) {
            $classified->address = showAddresFromList($classified->address);
        }
        if (isset($classified->date)) {
//            $classified->date = date('d/m/Y h:i:s',$classified->date);
            $classified->date = today_yesterday_v2($classified->date);
        }
        if (isset($classified->price)) {
            $classified->price = showPriceFromList($classified->price);
        }
//        if (isset($classified->list_acreage)) {
//            $classified->list_acreage = $classified->list_acreage != "" || $classified->list_acreage != null ? explode(',', $classified->list_acreage) : null;
//        }
//        if (isset($classified->cat_id)) {
//            $category = Category::select('cat_name')->where('cat_id', $classified->cat_id)->first();
//            $classified->type_classified = $category->cat_name;
//        }
        if (isset($classified->picture)) {
            if ($type == 'detail') {
                $pictures = bdsDecode($classified->picture);
                if ($pictures != "") {
                    if (isset($pictures[0]['type'])) {
                        foreach ($pictures as $key => $picture) {
                            if ($picture['type'] == 'photo') {
                                $picture['url'] = getUrlImageByRow($picture);
                            } else if ($picture['type'] == 'video') {
                                $picture['url'] = "https://img.youtube.com/vi/" . $picture['filename'] . "/sddefault.jpg";
                            }
                            $pictures[$key] = $picture;
                        }
                    } else {
                        foreach ($pictures as $key => $picture) {
                            $picture['teaser'] = "";
                            $picture['filename'] = $picture['name'];
                            unset($picture['name']);
                            $picture['url'] = getUrlImageByRow($picture);
                            $picture['type'] = 'photo';
                            $pictures[$key] = $picture;
                        }
                    }
                    $classified->picture = $pictures;
                } else {
                    $classified->picture = [];
                }
            } else {
                $arrayPicture = bdsDecode($classified->picture);
                $classified->picture = $classified->picture != null && $classified->picture != "" ? getUrlImageMain($arrayPicture, 150) : config('app.thumbnail_default');
            }

        }
        return $classified;
    }


    /**
     * POST v2/classifieds/
     *
     *  Thêm dữ liệu mới vào database
     * ### Thông số dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classifieds
     * `@fields` | List fields classifieds
     * @return \Illuminate\Http\Response
     */

    function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:255',
            'cat_id' => 'required|integer',
            'cit_id' => 'required|integer',
            'dis_id' => 'integer',
            'ward_id' => 'integer',
            'price' => 'integer',
            'address' => 'required|string|min:2|max:255',
            'access_token' => 'required|string|min:10',
            'email' => 'email',
            'description' => 'required|string|min:20',
        ], [
            'title.required' => 'Bạn chưa cung cấp tiêu đề bài viết(title)',
            'title.string' => 'Kiểu dữ liệu sai(title)',
            'title.min' => 'Tiêu đề quá ngắn(title)',
            'title.max' => 'Tiêu đề quá dài(title)',
            'cat_id.required' => 'Bạn chưa cung cấp chuyên mục',
            'cat_id.integer' => 'Kiểu dữ liệu không đúng(cat_id)',
            'cit_id.required' => 'Bạn Chưa cung cấp địa chỉ(cit_id)',
            'dis_id.integer' => 'Kiểu dữ liệu sai(dis_id)',
            'ward_id.integer' => 'Kiểu dữ liệu sai(ward_id)',
            'address.required' => 'Bạn chưa cung cấp địa chỉ',
            'address.min' => 'Địa chỉ quá ngắn',
            'address.max' => 'Địa chỉ bạn nhập quá dài',
            'price.integer' => 'Kiểu dữ liệu sai(price)',
            'email.email' => 'Không đúng kiểu định dạng email'
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return $this->setResponse(500, '', collect($validator->messages())->collapse());
        }
        $use_id = getUserId($request->input('access_token'));
        if ($use_id <= 0) {
            return $this->setResponse(500, '', 'Vui lòng đăng nhập lại');
        }
        $cit_id = (int)$request->input('cit_id');
        $dis_id = (int)$request->input('dis_id');
        $ward_id = (int)$request->input('ward_id');
        $street_id = (int)$request->input('street_id');
        $classified = new Classified();
        $cla_title = trim(convertToUnicode($request->input('title')));
        $classified->cla_title = $cla_title;
        $classified->cla_rewrite = str_slug($classified->cla_title);
        $classified->cla_cat_id = (int)$request->input('cat_id');
        $classified->cla_cit_id = $cit_id;
        $classified->cla_dis_id = $dis_id;
        $classified->cla_ward_id = $ward_id;
        $classified->cla_street_id = $street_id;
        $classified->cla_active = 0;
        $classified->cla_proj_id = (int)$request->input('proj_id');
        $classified->cla_price = (int)$request->input('price');
        $classified->cla_address = trim($request->input('address'));
        $classified->cla_date = (int)$request->input('date') ? strtotime($request->input('date')) : time();
        $classified->cla_expire = (int)$request->input('expire') ? strtotime($request->input('expire')) : (time() + 20 * 86400);
        $classified->cla_use_id = (int)$use_id;
        $classified->cla_mobile = $request->input('mobile');
        $classified->cla_phone = $request->input('phone');
        $classified->cla_email = $request->input('email');
        $classified->cla_contact_name = $request->input('contact_name');
        $classified->cla_description = validateDescription($request->input('description'), $cla_title);
        $classified->cla_teaser = getTeaser($classified->cla_description);
        $error = [];
        if ($diff = checkDuplicatePost($classified->cla_description, 90, $use_id)) {
            $copy = arrGetVal("copy", $diff, 0);
            $cla_title = arrGetVal("cla_title", $diff, "/");
            if ($copy > 0) {
                $error['cla_description'] = 'Nội dung tương tự với bài đăng "' . $cla_title . '" của bạn.';
                $error['copy'] = 'Nội dung tương tự với bài đăng "' . $cla_title . '" của bạn.';
            }
        }

        if ($deny_keyword = isDenyKeyword($classified->cla_title . " " . $classified->cla_description)) {
            $error["polixy"] = "Vui lòng bỏ hoặc thay thế từ \"$deny_keyword\" trong nội dung tin của bạn.";
        }

        if (!empty($error)) {
            return $this->setResponse(503, null, $error);
        }
        if ($request->input('picture')) {
            $pictures = $request->input('picture');
            foreach ($pictures as $key => $picture) {
                unset($picture['url']);
                $pictures[$key] = $picture;
            }
            $classified->cla_picture = $request->input('picture') ? bdsEncode($pictures) : null;
        }
        $classified->cla_list_acreage = (int)$request->input('list_acreage');
        $classified->cla_list_price = (int)$request->input('price');
        $classified->cla_list_badroom = (int)$request->input('list_badroom');
        $classified->cla_list_toilet = (int)$request->input('list_toilet');
        $classified->cla_vg_id = (int)$request->input('vg_id');
        $classified->cla_lat = (double)$request->input('lat');
        $classified->cla_lng = (double)$request->input('lng');
        $classified->cla_fields_check = (int)$request->input('fields_check');
        $classified->cla_search = $request->input('search') ? trim($request->input('search')) : null;
        $classified->cla_type_vip = 1;
        $classified->cla_type = 2;
        $classified->cla_has_picture = $request->input('picture') ? 1 : 0;
        $classified->cla_feature = $request->input('feature');
        $classified->cla_type_cat = (int)$request->input('type_cat');
//        $classified->cla_fields_check = createScoreFieldCheckClassified($classified);
        //check location
        $location = Location::select('loc_citid', 'loc_disid', 'loc_wardid', 'loc_streetid')->where('loc_cit_id', $cit_id)->where('loc_dis_id', $dis_id)->where('loc_ward_id', $ward_id)->where('loc_street_id', $street_id)->first();
        if ($location) {
            $classified->cla_citid = $location->loc_citid;
            $classified->cla_disid = $location->loc_disid;
            $classified->cla_wardid = $location->loc_wardid;
            $classified->cla_streetid = $location->loc_streetid;
        }
        $classified->save();
        $classified->cla_rewrite = $classified->cla_rewrite . '-cla' . encodeHashId($classified->cla_id);
        if ($classified->save()) {
            try {
                $this->updateRewrite($classified->cla_id);
                $this->rsyncToFilter($classified->cla_id);
            } catch (\Exception $error) {
                return response('Update Rewrite: massage - ' . $error->getMessage() . ', Line: ' . $error->getLine() . ', File:' . $error->getFile() . ' . Code: ' . $error->getCode(), 500);
            }

            $classified = convertColumnDataBase($classified->fillable, $classified);
            return $this->setResponse(200, $classified);
        } else {
            return $this->setResponse(500, null, 'Add fail!!!');
        }

    }


    /**
     * PUT v2/classifieds/{id}
     *
     * Cập nhật dữ liệu vào database
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classifieds (cla_id)
     * `@type` | Kiểu thao tác với dữ liệu
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {
        $classified = Classified::find($id);
        if ($classified) {
            if (checkAuthUser($request->access_token, $classified->cla_use_id)) {
                if ($request->input('type') == 'update_news') {
                    $cla_id = $id;
                    $price = (int)config('configuration.con_price_cla_update');

                    $money = updateMoneyBuyUserId($classified->cla_use_id, -$price);
                    if ($money == false) {
                        return response($this->setResponse(500, null, 'Không đủ xu để giao dịch'), 500);
                    }
//                    $classified->cla_type_vip = 2;
                    $classified->cla_date = time();
                    $ush = createUserSpendHistory($classified->cla_use_id, $request->ip(), $request->server('HTTP_USER_AGENT'), -$price, time() . '-update_news-' . $classified->cla_use_id . '-' . $cla_id, 'Làm mới tin ' . $cla_id . ' :' . $classified->cla_title, 4,6);
//                    sendMailNotification::dispatch($ush,$money,$classified);
                    $classified->save();
                    $this->data_tmp['data_saved'] = $classified;
                    $this->syncData($classified->cla_id, ['cla_date' => $classified->cla_date]);
//                    $this->updateClassifiedFilter($cla_id,$classified);
                    createClassifiedLog($classified, $price, 1);
                    return $this->setResponse(200, $this->data_tmp);
                } else if ($request->input('type') == 'update_vip') {
//                    if($request->input('day_count')==null){
//                        return response($this->setResponse(500,null,'Not params day_count'),500);
//                    }
//                    $classified_check = ClassifiedVip::where('clv_cla_id',$id)->where('clv_type_vip',2)->first();
//                    if($classified_check) return $this->setResponse(500,null,"Tin này hiện tại đang là tin vip");
//                    $price = config('configuration.con_price_cla_vip');
//                    $date_count =  (int) $request->input('day_count') ;
//                    $total_price =  $date_count * $price;
//                    $money = updateMoneyBuyUserId($classified->cla_use_id, -$total_price);
//                    if($money==false){
//                        return $this->setResponse(500,null,'Không đủ xu để giao dịch');
//                    }
//                    $time_exp = time()+ (86400*$date_count);
//                    $classified->cla_type_vip = 2;
//                    $classified->cla_expire =$time_exp;
//                    $classified->save();
//                    $this->syncData($classified->cla_id,['cla_type_vip'=>2,'cla_date'=>$classified->cla_date]);
//                    $classified_vip = new ClassifiedVip();
//                    $classified_vip->clv_cla_id = $id;
//                    $classified_vip->clv_date = $time_exp;
//                    $classified_vip->clv_type_vip = 2;
//                    $classified_vip->save();
//                    createClassifiedLog($classified,$total_price,2);
//                    $ush = createUserSpendHistory($classified->cla_use_id, $request->ip(), $request->server('HTTP_USER_AGENT'), -$total_price, time() . '-update_vip-' . $classified->cla_use_id . '-' .$id, 'Nâng cấp tin ' . $id. ' :' . $classified->cla_title, 4);
////                    JobSendMailUpdateVip::dispatch($classified->cla_use_id,$id,$ush);
//                    return $this->setResponse(200,'Updated');
                    //haifix
                    if ($request->input('day_count') == null) {
                        return response($this->setResponse(500, null, 'Not params day_count'), 500);
                    }
                    $classified_check = ClassifiedVip::where('clv_cla_id', $id)->where('clv_type_vip', 2)->first();
                    if ($classified_check) return $this->setResponse(500, null, "Tin này hiện tại đang là tin vip");
                    $price = config('configuration.con_price_cla_vip');
                    $sale_vip = config('configuration.con_sale_cla_vip');
                    $date_count = (int)$request->input('day_count');
//                    $total_price =  $date_count * $price;
                    if ($request->input('type_vip') == 'date') {
                        $total_price = $date_count * $price;
                    }
                    if ($request->input('type_vip') == 'date_package') {
                        $total_price = $date_count * $price * (1 - $sale_vip / 100);
//                        switch ($date_count){
//                            case 7:
//                                $total_price =  $date_count * $price - 15000;
//                                break;
//                            case 12:
//                                $total_price =  $date_count * $price - 30000;
//                                break;
//                            case 20:
//                                $total_price =  $date_count * $price - 60000;
//                                break;
//                            case 30:
//                                $total_price =  $date_count * $price - 100000;
//                                break;
//                        }
                    }
                    $money = updateMoneyBuyUserId($classified->cla_use_id, -$total_price);
                    if ($money == false) {
                        return $this->setResponse(500, null, 'Không đủ xu để giao dịch');
                    }
                    $time_exp = time() + (86400 * $date_count);
                    $classified->cla_type_vip = 2;
                    $classified->cla_expire = $time_exp;
                    $classified->save();
                    $this->syncData($classified->cla_id, ['cla_type_vip' => 2, 'cla_date' => $classified->cla_date]);
//                    $this->updateClassifiedFilter($classified->cla_id,$classified);
                    $classified_vip = new ClassifiedVip();
                    $classified_vip->clv_cla_id = $id;
                    $classified_vip->clv_date = $time_exp;
                    $classified_vip->clv_type_vip = 2;
                    $classified_vip->save();
                    createClassifiedLog($classified, $total_price, 2);
                    $ush = createUserSpendHistory($classified->cla_use_id, $request->ip(), $request->server('HTTP_USER_AGENT'), -$total_price, time() . '-update_vip-' . $classified->cla_use_id . '-' . $id, 'Nâng cấp tin ' . $id . ' :' . $classified->cla_title, 4,5);
//                    JobSendMailUpdateVip::dispatch($classified->cla_use_id,$id,$ush);
                    return $this->setResponse(200, 'Updated');
                    //end


                }


                $validator = Validator::make($request->all(), [
                    'title' => 'string|min:5|max:255',
                    'cat_id' => 'integer',
                    'cit_id' => 'integer',
                    'dis_id' => 'integer',
                    'ward_id' => 'integer',
                    'price' => 'integer',
                    'address' => 'string|min:2|max:255',
                    'use_id' => 'integer',
                    'access_token' => 'string|min:10',
                    'email' => 'email'
                ], [
                    'title.string' => 'Kiểu dữ liệu sai(title)',
                    'title.min' => 'Tiêu đề quá ngắn(title)',
                    'title.max' => 'Tiêu đề quá dài(title)',
                    'cat_id.integer' => 'Kiểu dữ liệu không đúng(cat_id)',
                    'dis_id.integer' => 'Kiểu dữ liệu sai(dis_id)',
                    'ward_id.integer' => 'Kiểu dữ liệu sai(ward_id)',
                    'address.min' => 'Địa chỉ quá ngắn',
                    'address.max' => 'Địa chỉ bạn nhập quá dài',
                    'use_id.integer' => 'Kiểu dữ liệu sai(use_id)',
                    'price.integer' => 'Kiểu dữ liệu sai(price)',
                    'email.email' => 'Không đúng kiểu định dạng email'
                ]);
                if (collect($validator->messages())->collapse()->count() !== 0) {
                    return $this->setResponse(500, '', collect($validator->messages())->collapse());
                }

                if ($request->input('title')) {
                    $classified->cla_title = trim(convertToUnicode($request->input('title')));
//                        $classified->cla_rewrite = str_slug($classified->cla_title);
                }
                $location_id = [];
                if ($request->input('cit_id') !== null) $location_id['cit_id'] = (int)$request->input('cit_id');
                if ($request->input('dis_id') !== null) $location_id['dis_id'] = (int)$request->input('dis_id');
                if ($request->input('ward_id') !== null) $location_id['ward_id'] = (int)$request->input('ward_id');
                if ($request->input('street_id') !== null) $location_id['street_id'] = (int)$request->input('street_id');

                if ($request->input('cat_id') !== null) $classified->cla_cat_id = (int)$request->input('cat_id');
                if ($request->input('cit_id') !== null) $classified->cla_cit_id = $location_id['cit_id'];
                if ($request->input('dis_id') !== null) $classified->cla_dis_id = $location_id['dis_id'];
                if ($request->input('ward_id') !== null) $classified->cla_ward_id = $location_id['ward_id'];
                if ($request->input('street_id') !== null) $classified->cla_street_id = $location_id['street_id'];
                if ($request->input('active') !== null) $classified->cla_active = (int)$request->input('active');
                if ($request->input('proj_id') !== null) $classified->cla_proj_id = (int)$request->input('proj_id');
                if ($request->input('price') !== null) $classified->cla_price = (int)$request->input('price');
                if ($request->input('address')) $classified->cla_address = trim($request->input('address'));
                if ($request->input('date') !== null) $classified->cla_date = (int)$request->input('date') ? strtotime($request->input('date')) : time();
                if ($request->input('expire') !== null) $classified->cla_expire = (int)$request->input('expire') ? strtotime($request->input('expire')) : null;
                if ($request->input('use_id') !== null) $classified->cla_use_id = (int)$request->input('use_id');
                if ($request->input('mobile')) $classified->cla_mobile = (int)$request->input('mobile');
                if ($request->input('mobile')) $classified->cla_phone = (int)$request->input('mobile');
                if ($request->input('email')) $classified->cla_email = $request->input('email');
                if ($request->input('contact_name')) $classified->cla_contact_name = $request->input('contact_name');
                if ($request->input('description')) {
                    $classified->cla_description = validateDescription($request->input('description'), $classified->cla_title);
                    $classified->cla_teaser = getTeaser($classified->cla_description);
                }

                if ($request->input('picture')) {
                    $pictures = $request->input('picture');
                    foreach ($pictures as $key => $picture) {
                        unset($picture['url']);
                        $pictures[$key] = $picture;
                    }
                    $classified->cla_picture = $request->input('picture') ? bdsEncode($pictures) : null;
                }
                if ($request->input('list_acreage')) $classified->cla_list_acreage = (int)$request->input('list_acreage');
                if ($request->input('price')) $classified->cla_list_price = (int)$request->input('price');
                if ($request->input('list_badroom')) $classified->cla_list_badroom = (int)$request->input('list_badroom');
                if ($request->input('list_toilet')) $classified->cla_list_toilet = (int)$request->input('list_toilet');
                if ($request->input('vg_id') !== null) $classified->cla_vg_id = (int)$request->input('vg_id');
                if ($request->input('lat')) $classified->cla_lat = (double)$request->input('lat');
                if ($request->input('lng')) $classified->cla_lng = (double)$request->input('lng');
                if ($request->input('fields_check') !== null) $classified->cla_fields_check = (int)$request->input('fields_check');
                if ($request->input('search')) $classified->cla_search = $request->input('search') ? trim($request->input('search')) : null;
                if ($request->input('type_vip')) $classified->cla_type_vip = (int)$request->input('type_vip');
                if ($request->input('type') !== null) $classified->cla_type = (int)$request->input('type');
                if ($request->input('picture')) $classified->cla_has_picture = (int)$request->input('picture') ? 1 : 0;
                if ($request->input('feature')) $classified->cla_feature = $request->input('feature');
                if ($request->input('type_cat')) $classified->cla_type_cat = (int)$request->input('type_cat');
                if (count($location_id) != 0) {
                    $location = Location::select('loc_citid', 'loc_disid', 'loc_wardid', 'loc_streetid');
                    foreach ($location_id as $key => $value) {
                        $location = $location->where("loc_$key", $value);
                    }
                    $location = $location->first();
                    if ($location) {
                        $classified->cla_citid = $location->loc_citid;
                        $classified->cla_disid = $location->loc_disid;
                        $classified->cla_wardid = $location->loc_wardid;
                        $classified->cla_streetid = $location->loc_streetid;
                    }
                }
//                $classified->cla_fields_check = createScoreFieldCheckClassified($classified);
                if ($classified->save()) {
                    if ($request->input('active') !== null) {
                        $sphinx = app()->make('sphinx');
                        $sphinx->query("UPDATE " . getSphinxIndexClassified($classified->cla_id) . "  SET cla_active=$classified->cla_active WHERE id = $classified->cla_id")->execute();
                    }
                    $this->updateRewriteV2($classified->cla_id, $classified);
                    $this->indexClassified($classified->cla_id);
                    $this->rsyncToFilter($classified->cla_id);
                    $this->updateClassifiedFilter($classified->cla_id, $classified);
                    $classified = convertColumnDataBase($classified->fillable, $classified);
                    return $this->setResponse(200, ['classifieds' => $classified, 'log' => $this->data_tmp]);
                } else {
                    return $this->setResponse(500, null, 'Update error!');
                }
//                }
            }
        }
        return response($this->setResponse(401, null, 'Bạn không có quyền thực hiện hành động này!'), 401);
    }


    /**
     * DELETE v2/classifieds/{classified_id}
     *
     * Xóa dữ liệu trong DB
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classifieds-vip
     * `@fields` | List fields classifieds-vip
     * @return \Illuminate\Http\Response
     */

    function destroy(Request $request, $id)
    {
        $classified = Classified::find($id);
        if ($classified) {
            if (checkAuthUser($request->access_token, $classified->cla_use_id)) {
                if ($classified->delete()) {
                    ClassifiedVip::where('clv_cla_id', $id)->delete();
                    ClassifiedFilter::where('cla_id', $id)->delete();
                    UserFavorite::where('usf_cla_id', $id)->delete();
                    $sphinx = app()->make('sphinx');
                    $sphinx->query("DELETE FROM " . getSphinxIndexClassified($id) . " WHERE id = $id")->execute();
                    $params = [
                        'index' => 'bds_classifieds',
                        'type' => 'classifieds',
                        'id' => $id
                    ];
                    app('elastic')->delete($params);
                    return $this->setResponse(200, ['message' => "Xóa thành công"]);
                }
            }
        }
        return response($this->setResponse(404, '', 'Not found id'), 404);
    }

    function deleteClassifiedClaw(Request $request, $id)
    {
        $classified = Classified::where('cla_id', $id)->first();
        if ($classified) {
            if (verifyGoogleCaptcha($request->input('google_token', ''))) {
                if ($classified->delete()) {
                    ClassifiedVip::where('clv_cla_id', $id)->delete();
                    ClassifiedFilter::where('cla_id', $id)->delete();
                    $sphinx = app()->make('sphinx');
                    $sphinx->query("DELETE FROM " . getSphinxIndexClassified($id) . " WHERE id = $id")->execute();
                    $params = [
                        'index' => 'bds_classifieds',
                        'type' => 'classifieds',
                        'id' => $id
                    ];
                    app('elastic')->delete($params);
                    return $this->setResponse(200, ['message' => "Xóa thành công"]);
                }
            } else {
                return response('Verify google captcha token fail!', 500);
            }
        }
        return response($this->setResponse(404, '', 'Not found id'), 404);
    }


    function checkFieldClassified($classifieds)
    {
        $categories = Cache::remember('categories_fields_check', 120, function () {
            return Category::select('cat_id', 'cat_cla_field_check')->where('cat_active', 1)->get();
        });
        foreach ($classifieds as $key => $classified) {
            $category = $categories->where('cat_id', $classified->cat_id)->first();
            if (isset($category) && $category->cat_cla_field_check != null) {
                $cat_fields = json_decode($category->cat_cla_field_check);
                $field_success = count((array)$cat_fields);
                $field_value = 0;
                foreach ($cat_fields as $field) {
                    if (($field & $classified->fields_check) == $field) {
                        $field_value++;
                    }
                }

                $classifieds[$key]->field_check_value = $field_success == 0 ? 0 : round(($field_value / $field_success) * 100);
                continue;
            }
            $classifieds[$key]->field_check_value = 0;
            continue;
        }
        return $classifieds;
    }

    function updateRewrite($cla_id)
    {
        $cla_id = intval($cla_id);
        if ($cla_id <= 0) return false;
        $cla = DB::select("SELECT * FROM classifieds STRAIGHT_JOIN categories_multi ON(cat_id = cla_cat_id) LEFT JOIN projects ON(cla_proj_id = proj_id) WHERE cla_id = " . $cla_id . " LIMIT 1");
        if (isset($cla[0])) {
            $cla = $cla[0];
            if (intval($cla->cla_proj_id) > 0) {
                DB::table('projects')->where('proj_id', intval($cla->cla_proj_id))->update(['proj_update' => time()]);
            }
            $sql = '';
            if (intval($cla->cla_cit_id) > 0) {
                $sql .= " AND loc_cit_id = " . intval($cla->cla_cit_id);
            }
            if (intval($cla->cla_dis_id) > 0) {
                $sql .= " AND loc_dis_id = " . intval($cla->cla_dis_id);
            } else {
                $sql .= " AND loc_dis_id = 0";
            }
            if (intval($cla->cla_ward_id) > 0) {
                $sql .= " AND loc_ward_id = " . intval($cla->cla_ward_id);
            } else {
                $sql .= " AND loc_ward_id = 0";
            }
            $sql .= " AND loc_street_id = 0";
            if ($sql != "") {

                $loc = DB::select("SELECT * FROM location WHERE 1 $sql LIMIT 1");
                if (isset($loc[0])) {
                    $loc = $loc[0];
                    $nameRewrite = cleanRewriteAccent($cla->cat_name . " " . $cla->proj_name . " tại " . $loc->loc_address);
                    $otherTitle = $cla->cat_name . " " . $cla->proj_name . " tại " . $loc->loc_address;
                    $arrayData = array("iCit" => $cla->cla_cit_id, "iDis" => $cla->cla_dis_id, "iWard" => $cla->cla_ward_id, "iStreet" => $cla->cla_street_id, "iCat" => $cla->cat_id, "iProj" => intval($cla->proj_id), "module" => $cla->cat_type, "type" => "classified");
                    //thêm vào bảng rewrite
                    $rew = $this->createRewriteNotExists($nameRewrite, "categories_location", $loc->loc_id, $arrayData, $cla->cat_id, $cla->cla_cit_id, $cla->cla_dis_id, $cla->cla_ward_id, $cla->cla_street_id, $otherTitle, 1, $cla->proj_id);
                    $cla_rew_id = intval($rew["rew_id"]);
                    $rewrite = cleanRewriteAccent(cut_string($cla->cla_title, 120, "")) . "-cla" . encodeHashId($cla->cla_id);
                    $rewrite = replaceMQ(removeAccent($rewrite));
                    $cla_active = 1;
                    DB::select("UPDATE classifieds SET cla_rewrite = '$rewrite',cla_active = $cla_active,cla_rew_id=$cla_rew_id,cla_search =
                                                CONCAT(
                                                    if(cla_cat_id > 0,CONCAT(\" cat\",cla_cat_id),'')
                                                    ,if(cla_cit_id > 0,CONCAT(\" cit\",cla_cit_id),'')
                                                    ,if(cla_dis_id > 0,CONCAT(\" dis\",cla_dis_id),'')
                                                    ,if(cla_ward_id > 0,CONCAT(\" ward\",cla_ward_id),'')
                                                    ,if(cla_street_id > 0,CONCAT(\" street\",cla_street_id),'')
                                                    ,if(cla_proj_id > 0,CONCAT(\" proj\",cla_proj_id),'')

                                                ) WHERE cla_id = " . $cla_id);
                    //bat dau insert vào sphixnx
                    $cla_title = "'" . mb_strtolower(replaceSphinxMQ(cleanKeywordSearch($cla->cla_title)), "UTF-8") . "'";
                    $cla_address = "'" . replaceSphinxMQ(cleanKeywordSearch($cla->cla_address)) . "'";
                    $cla_mobile = "'" . replaceSphinxMQ($cla->cla_mobile) . "'";
                    $cla_phone = "'" . replaceSphinxMQ($cla->cla_phone) . "'";
                    $cla_email = "'" . replaceSphinxMQ($cla->cla_email) . "'";
                    $cla_contact_name = "'" . replaceSphinxMQ($cla->cla_contact_name) . "'";
                    $cla_teaser = "'" . mb_strtolower(replaceSphinxMQ(cleanKeywordSearch($cla->cla_teaser)), "UTF-8") . "'";
                    $cla_cat_id = intval($cla->cla_cat_id);
                    $cla_cit_id = intval($cla->cla_cit_id);
                    $cla_dis_id = intval($cla->cla_dis_id);
                    $cla_ward_id = intval($cla->cla_ward_id);
                    $cla_street_id = intval($cla->cla_street_id);
                    $cla_proj_id = intval($cla->cla_proj_id);
                    $cla_date = intval($cla->cla_date);
                    $cla_expire = intval($cla->cla_expire);
                    $cla_use_id = intval($cla->cla_use_id);
                    $cla_type = intval($cla->cla_type);
                    $cla_type_vip = intval($cla->cla_type_vip);
                    $cla_price = doubleval($cla->cla_price);
                    $cla_lat = doubleval($cla->cla_lat);
                    $cla_lng = doubleval($cla->cla_lng);
                    $cla_fields_check = intval($cla->cla_fields_check);
                    $cla_list_acreage = "(" . $cla->cla_list_acreage . ")";
                    $cla_list_price = "(" . $cla->cla_list_price . ")";
                    $cla_list_badroom = "(" . $cla->cla_list_badroom . ")";
                    $cla_list_toilet = "(" . $cla->cla_list_toilet . ")";
                    $cla_search = "'" . $this->createKeywordSearch(["iCat" => $cla_cat_id, "iCit" => $cla_cit_id, "iDis" => $cla_dis_id, "iWard" => $cla_ward_id, "iStreet" => $cla_street_id, "iProj" => $cla_proj_id]) . "'";
                    $sphinx = app()->make('sphinx');
                    $sphinx->query("INSERT INTO " . getSphinxIndexClassified($cla_id) . "(id,cla_title,cla_address,cla_mobile,cla_phone,cla_email,cla_contact_name,cla_search,cla_teaser,cla_cat_id,cla_cit_id,cla_dis_id,cla_ward_id,cla_street_id,cla_proj_id,cla_date,cla_expire,cla_use_id,cla_price,cla_lat,cla_lng,cla_fields_check,cla_active,cla_list_acreage,cla_list_price,cla_list_badroom,cla_list_toilet,cla_type,cla_type_vip)
                                                      VALUES($cla_id,$cla_title,$cla_address,$cla_mobile,$cla_phone,$cla_email,$cla_contact_name,$cla_search,$cla_teaser,$cla_cat_id,$cla_cit_id,$cla_dis_id,$cla_ward_id,$cla_street_id,$cla_proj_id,$cla_date,$cla_expire,$cla_use_id,$cla_price,$cla_lat,$cla_lng,$cla_fields_check,$cla_active,$cla_list_acreage,$cla_list_price,$cla_list_badroom,$cla_list_toilet,$cla_type,$cla_type_vip)")->execute();
                    DB::table('rewrites_queue')->insert(['que_rew_id' => $cla_rew_id]);


                    $this->indexClassified($cla_id);//??

//                    $myNotification = new Notifications();
                    $arrayID = [
                        "iCat" => $cla->cla_cat_id
                        , "iCit" => $cla->cla_cit_id
                        , "iDis" => $cla->cla_dis_id
                        , "iWard" => $cla->cla_ward_id
                        , "iStreet" => $cla->cla_street_id
                        , "iProj" => $cla->cla_proj_id
                        , "iUse" => $cla->cla_use_id
                        , "iRew" => $cla->cla_rew_id
                    ];
                    $arrayPicture = arrayVal(bdsDecode($cla->cla_picture));
                    $picture = getUrlImageMain($arrayPicture, 150, $cla->cla_cat_id);
                    $arrayData = [
                        "cla_id" => $cla->cla_id
                        , "cla_title" => $cla->cla_title
                        , "cla_rewrite" => $rewrite
                        , "cla_contact_name" => $cla->cla_contact_name
                        , "cla_date" => $cla->cla_date
                        , "rew_title" => cutRewriteName($otherTitle)
                        , "picture" => $picture
                    ];
                    $this->createNotificationFromClassified($arrayID, $arrayData, $cla->cla_type_vip, NOTIFICATION_TYPE_ACTION_POST, NOTIFICATION_TYPE_OBJECT_CLASSIFIED, $cla->cla_id);
                    DB::select("INSERT IGNORE INTO queue_feed(id) VALUES($cla_rew_id)");
                    unset($db_ex);
                    return true;
                } else {
//                    saveLog("error_rewrite.cfn",$db_location->query);
                    throw new \Exception('error');
                }

            }

        }
        return false;
    }

    function createRewriteNotExists($name_rewrite, $table = "", $id_value = 0, $param_get = array(), $iCat = 0, $iCit = 0, $iDis = 0, $iWard = 0, $iStreet = 0, $otherTitle = "", $updateCount = 0, $proj_id = 0)
    {
        $rewrite = cleanRewriteAccent($name_rewrite);
        $rew_md5 = md5($rewrite);
        $iCat = intval($iCat);
        $iCit = intval($iCit);
        $iDis = intval($iDis);
        $iWard = intval($iWard);
        $iProj = (int)$proj_id;//???
        $iStreet = intval($iStreet);
        $row = Rewrite::where('rew_md5', $rew_md5)->first();
        if ($row) {
            if ($updateCount == 1 && $row->rew_total_result <= 0) {
                DB::select("UPDATE rewrites SET rew_total_result = rew_total_result+1 WHERE rew_id = " . $row->rew_id);
            }
            return $row;
        } else {
            $rew_total_result = 1;
            $rew_title = ($otherTitle != "") ? replaceMQ($otherTitle) : replaceMQ($name_rewrite);
            $rew_rewrite = $rewrite;
            $rew_param = bdsEncode($param_get);
            $rew_date = time();
            $rew_table = replaceMQ($table);
            $rew_id_value = intval($id_value);
            $rew_count_word = getCountWordRewrite($rewrite);
            $rew_length = strlen($rewrite);
            $rew_noaccent = replaceMQ(removeAccent($rew_title));
            $rewrite = new Rewrite();
            $rewrite->rew_title = $rew_title;
            $rewrite->rew_noaccent = $rew_noaccent;
            $rewrite->rew_rewrite = $rew_rewrite;
            $rewrite->rew_md5 = $rew_md5;
            $rewrite->rew_param = $rew_param;
            $rewrite->rew_date = $rew_date;
            $rewrite->rew_table = $rew_table;
            $rewrite->rew_id_value = $rew_id_value;
            $rewrite->rew_count_word = $rew_count_word;
            $rewrite->rew_cat_id = $iCat;
            $rewrite->rew_cit_id = $iCit;
            $rewrite->rew_dis_id = $iDis;
            $rewrite->rew_ward_id = $iWard;
            $rewrite->rew_street_id = $iStreet;
            $rewrite->rew_proj_id = $iProj;
            $rewrite->rew_length = $rew_length;
            $rewrite->rew_total_result = $rew_total_result;
            $rewrite->rew_proj_id = $iProj;
            $rewrite->rew_proj_id = $iProj;
            $rewrite->save();
            return ["rew_id" => $rewrite->rew_id, "rew_rewrite" => $rew_rewrite];
        }
    }

    public function createKeywordSearch($filter)
    {
        $arrSearch = [];
        $iCat = (int)array_get($filter, 'iCat', 0);
        $iProj = (int)array_get($filter, 'iProj', 0);
        $iCit = (int)array_get($filter, 'iCit', 0);
        $iDis = (int)array_get($filter, 'iDis', 0);
        $iWard = (int)array_get($filter, 'iWard', 0);
        $iStreet = (int)array_get($filter, 'iStreet', 0);
        if ($iCat > 0) {
            $listCat = Category::select('cat_all_child')->where('cat_id', $iCat)->first();
            $listCat = explode(",", $listCat->cat_all_child);
            if (!empty($listCat)) {
                foreach ($listCat as $cat) {
                    $arrSearch[] = "cat" . $cat;
                }
            }
            $arrSearch[] = "cat" . $iCat;
        }
        if ($iProj > 0) $arrSearch[] = "proj" . $iProj;
        if ($iCit > 0) $arrSearch[] = "cit" . $iCit;
        if ($iDis > 0) $arrSearch[] = "dis" . $iDis;
        if ($iWard > 0) $arrSearch[] = "ward" . $iWard;
        if ($iStreet > 0) $arrSearch[] = "street" . $iStreet;
        $arrSearch = array_flip($arrSearch);
        $arrSearch = array_flip($arrSearch);
        return implode(" ", $arrSearch);
    }

    function indexClassified($cla_id, $update = false)
    {
//        $row = DB::select("SELECT  cla_id,cla_address,cla_mobile,cla_phone,cla_email,cla_contact_name,cla_rewrite,cla_teaser,cla_title,cla_cat_id,cla_cit_id,cla_dis_id,cla_ward_id,cla_street_id,cla_proj_id,cla_date,cla_expire,cla_use_id,cla_vg_id,cla_fields_check,cla_active,cla_type,cla_type_vip,cla_price,cla_lat,cla_lng,cla_list_acreage,cla_list_price,cla_list_badroom,cla_list_toilet,cat_id,cat_type,cat_name,proj_title,cla_picture
//                           FROM classifieds
//                           STRAIGHT_JOIN categories_multi ON(cla_cat_id = cat_id)
//                           LEFT JOIN projects ON(cla_proj_id = proj_id)
//                           WHERE cla_id = " . intval($cla_id));
        $classified = new Classified();
        $classified = $classified
            ->setConnection('master')
            ->select('cla_id', 'cla_address', 'cla_mobile', 'cla_phone', 'cla_email', 'cla_contact_name', 'cla_rewrite', 'cla_teaser', 'cla_title', 'cla_cat_id', 'cla_cit_id', 'cla_dis_id', 'cla_ward_id', 'cla_street_id', 'cla_proj_id', 'cla_date', 'cla_expire', 'cla_use_id', 'cla_vg_id', 'cla_fields_check', 'cla_active', 'cla_type', 'cla_type_vip', 'cla_price', 'cla_lat', 'cla_lng', 'cla_list_acreage', 'cla_list_price', 'cla_list_badroom', 'cla_list_toilet', 'cat_id', 'cat_type', 'cat_name', 'proj_title', 'cla_picture')
            ->join('categories_multi', 'classifieds.cla_cat_id', '=', 'categories_multi.cat_id')
            ->leftJoin('projects', 'classifieds.cla_proj_id', '=', 'projects.proj_id')
            ->where('cla_id', $cla_id)->first();
        $this->data_tmp['cla join'] = $classified;
        $params = [];
        if (!empty($classified)) {
            $row = $classified->toArray();
            foreach ($row as $key => $val) {
                if (preg_match('/^[0-9]*$/', $val) && $val != "") {
                    if (strlen($val) < 10) {
                        $row[$key] = intval($val);
                    } else {
                        $row[$key] = doubleval($val);
                    }
                }
                if (preg_match('/^[0-9,.]*$/', $val) && $val != "") $row[$key] = doubleval($val);
            }
            $params['body'][] = [
                'index' => [
                    '_index' => 'bds_classifieds',
                    '_type' => 'classifieds',
                    '_id' => intval($row["cla_id"])
                ]
            ];
            $row["cla_order"] = $row["cla_date"] + $row["cla_date"] * $row["cla_type_vip"];
            $row["cla_title"] = Spinner($row["cla_title"]);
            $row["cla_list_acreage"] = convertListFloatToArray($row["cla_list_acreage"]);
            $row["cla_list_price"] = convertListFloatToArray($row["cla_list_price"]);
            $row["cla_list_badroom"] = convertListIntToArray($row["cla_list_badroom"]);
            $row["cla_list_toilet"] = convertListIntToArray($row["cla_list_toilet"]);
            $this->data_tmp['data_elas'] = $row;
            $params['body'][] = $row;
            if ($update) {
                $params = [
                    '_index' => 'bds_classifieds',
                    '_type' => 'classifieds',
                    '_id' => intval($row["cla_id"]),
                    'body' => ['params' => $row]
                ];
            }
        }
        if (!empty($params)) {
            if ($update) {
                //_debug($params);
                $responses = app('elastic')->update($params);
            } else {
                $responses = app('elastic')->bulk($params);
            }
            return $responses;
        }
    }

    function createNotificationFromClassified($arrayID, $arrayData, $priority = 0, $type_action = NOTIFICATION_TYPE_ACTION_POST, $type_object = NOTIFICATION_TYPE_OBJECT_CLASSIFIED, $object_id = 0, $timeout = 1296000)
    {
        $not_create_date = time();
        $not_owner = intval(arrGetVal("iUse", $arrayID, 0));
        $not_priority = $priority;
        $not_type_action = $type_action;
        $not_type_object = $type_object;
        $not_to_device = 0;
        $not_object_id = $object_id;
        $not_timeout = $timeout;
        $not_data = bdsEncode($arrayData);
        $not_to = $this->generateKeywordFromId($arrayID);
        $arrayUser = $this->getUserFollowFromKeyword($not_to);
        $arrUserId = [];
        foreach ($arrayUser as $use) {
            $arrUserId[$use["use_id"]] = $use["use_id"];
        }
        $que_list_user = implode(",", $arrUserId);
        $id = substr(number_format(time() * rand(), 0, '', ''), 0, 10);
        $index_name = "bds_rt_notifications_" . ($id % 10);
        $sphinx = app()->make('sphinx');
        $sphinx->query("INSERT INTO $index_name(id,not_to,not_data,not_create_date,not_owner,not_priority,not_type_action,not_type_object,not_to_device,not_object_id,not_timeout)
          VALUES($id,'$not_to','$not_data',$not_create_date,$not_owner,$not_priority,$not_type_action,$not_type_object,$not_to_device,$not_object_id,$not_timeout)")->execute();
        //bat dau insert vao bang queue de ban den email va chrome
        if ($que_list_user != "") {
            DB::select("INSERT INTO queue_notifications(que_obj_type,que_obj_id,que_date,que_keyword,que_list_user) VALUES ($not_type_object,$not_object_id,$not_create_date,'$not_to','$que_list_user')");
        }
    }

    function generateKeywordFromId($arrayID)
    {
        $iCat = intval(arrGetVal("iCat", $arrayID, 0));
        $iCit = intval(arrGetVal("iCit", $arrayID, 0));
        $iDis = intval(arrGetVal("iDis", $arrayID, 0));
        $iWard = intval(arrGetVal("iWard", $arrayID, 0));
        $iStreet = intval(arrGetVal("iStreet", $arrayID, 0));
        $iProj = intval(arrGetVal("iProj", $arrayID, 0));
        $iUse = intval(arrGetVal("iUse", $arrayID, 0));
        $iRew = intval(arrGetVal("iRew", $arrayID, 0));
        $keyword = '';
        if ($iCat > 0) {
            $keyword .= " ca" . $iCat;
            if ($iCit > 0) $keyword .= " ca" . $iCat . "ci" . $iCit;
            if ($iDis > 0) $keyword .= " ca" . $iCat . "di" . $iDis;
            if ($iWard > 0) $keyword .= " ca" . $iCat . "wa" . $iWard;
            if ($iStreet > 0) $keyword .= " ca" . $iCat . "st" . $iStreet;
            if ($iProj > 0) $keyword .= " ca" . $iCat . "pr" . $iProj;
        }
        if ($iCit > 0) $keyword .= " ci" . $iCit;
        if ($iDis > 0) $keyword .= " di" . $iDis;
        if ($iWard > 0) $keyword .= " wa" . $iWard;
        if ($iStreet > 0) $keyword .= " st" . $iStreet;
        if ($iProj > 0) $keyword .= " pr" . $iProj;
        if ($iUse > 0) $keyword .= " us" . $iUse;
        if ($iRew > 0) $keyword .= " re" . $iRew;
        return $keyword;
    }

    function getUserFollowFromKeyword($keyword)
    {
        $sphinx = app()->make('sphinx');
        $row = $sphinx->query("SELECT * FROM bds_follows WHERE MATCH ('\"" . cleanKeywordSearch($keyword) . "\"/1')")->execute()->fetchAllAssoc();
//        $row = DB::select("SELECT * FROM bds_follows WHERE MATCH ('\"" . cleanKeywordSearch($keyword) . "\"/1') OPTION ranker = matchany");
        //echo $db_select->query;
        $result = [];
        if (isset($row[0])) {
            $row = (array)$row[0];
            $result[] = $row;
        }
        return $result;
    }

    function rsyncToFilter($cla_id)
    {
        $field = "cla_id,cla_cat_id,cla_cit_id,cla_dis_id,cla_ward_id,cla_street_id,cla_active,cla_proj_id,cla_price,cla_date,cla_expire,cla_use_id,cla_vg_id,cla_lat,cla_lng,cla_fields_check,cla_type_vip,cla_type,cla_has_picture,cla_rew_id,cla_type_cat,cla_citid,cla_disid,cla_wardid,cla_streetid,cla_cat_prentid,cla_sort,cla_has_video";
        $field = explode(',', $field);
        $cla_id = intval($cla_id);
        $classified = new Classified();
        $data = $classified->setConnection('master')->select($field)->where('cla_id', $cla_id)->first();
//        $data =  $this->fields($field)->findByID($cla_id);
        if (!empty($data)) {
            //nếu trường cat_parent thì select từ bảng categories ra để update
//            $cats = (new Category())->fields("cat_parent_id")->findByID($data["cla_cat_id"]);
            $cats = Category::select('cat_parent_id')->where('cat_id', $data->cla_cat_id)->first();
            if (isset($cats->cat_parent_id)) $data->cla_cat_prentid = intval($cats->cat_parent_id);
            //nếu chưa set trường sort thì lấy mặc định bằng date
            if ($data->cla_sort == 0) $data->cla_sort = $data->cla_date;
            $this->data_tmp['data_cla'] = $data;
            $this->insertUpdate($data);
            return 1;
        } else {//ngược lại nếu ko có thì xóa bên bảng filter luôn
            $classified_filter = ClassifiedFilter::find($cla_id);
            $classified_filter->delete();
        }
        return 0;
    }

    function insertUpdate($data)
    {
        $classified_filter = ClassifiedFilter::find($data->cla_id);
        $data_update = $data->toArray();
        if ($classified_filter) {
            unset($data_update['cla_id']);
            $this->data_tmp['data_update'] = $data_update;
            foreach ($data_update as $key => $value) {
                $classified_filter->{$key} = $value;
            }
            $classified_filter->save();
            $this->data_tmp['value'] = $classified_filter;
        } else {
            $classified_filter = new ClassifiedFilter();
            foreach ($data_update as $key => $value) {
                $classified_filter->{$key} = $value;
            }
            $classified_filter->save();
        }

    }

    function syncData($cla_id, $data_update = [])
    {
        $set_value = [];
        foreach ($data_update as $key => $value) {
            if (key_exists($key, $this->field_table)) {
                $set_value[] = " $key=$value ";
            }
        }
        $set_value = implode(',', $set_value);
        $sphinx = app()->make('sphinx');
        $sql_sphinx = "UPDATE " . getSphinxIndexClassified($cla_id) . "  SET $set_value WHERE id = $cla_id";
        $sphinx->query($sql_sphinx)->execute();
        $this->indexClassified($cla_id);
        $this->rsyncToFilter($cla_id);
    }


    function getCacheRedis(Request $request)
    {
        $this->validate($request, [
            'client_token' => 'required|string|min:30',
        ]);
        $key = $request->client_token;
        $key = getInfoFormSecret($key);
        if ($key == false) return response('Not auth', 401);
        $arr_cache = Cache::get($key->id);
        if ($arr_cache == null) $arr_cache = [];
        if (count($arr_cache) > 1) $arr_cache = array_reverse($arr_cache);
        return $this->setResponse(200, $arr_cache);

    }

    function updateClassifiedFilter($cla_id, $data_update)
    {
        $data_update = $data_update->toArray();
        //update cla_filter
        $column_cla_filter = ['cla_cat_id', 'cla_cit_id', 'cla_dis_id', 'cla_ward_id', 'cla_street_id', 'cla_active', 'cla_proj_id', 'cla_price', 'cla_date', 'cla_expire', 'cla_use_id', 'cla_vg_id', 'cla_lat', 'cla_lng', 'cla_fields_check', 'cla_type_vip', 'cla_type', 'cla_has_picture', 'cla_rew_id', 'cla_type_cat', 'cla_citid', 'cla_disid', 'cla_wardid', 'cla_streetid', 'cla_cat_parent_id', 'cla_sort', 'cla_has_video'];
        $fields_update = [];
        foreach ($data_update as $key => $value) {
            if (in_array($key, $column_cla_filter)) {
                $fields_update[$key] = $value;
            }
        }
        if (count($fields_update) != 0) {
            ClassifiedFilter::where('cla_id', $cla_id)->update($fields_update);
        }

    }

    function updateRewriteV2($cla_id, $data_update)
    {
        $data_update = $data_update->toArray();
        $rewrite = Rewrite::select('rew_id')->where('rew_cat_id', $data_update['cla_cat_id'])->where('rew_cit_id', $data_update['cla_cit_id'])->where('rew_dis_id', $data_update['cla_dis_id'])->where('rew_ward_id', $data_update['cla_ward_id'])->where('rew_street_id', $data_update['cla_street_id'])->where('rew_proj_id', $data_update['cla_proj_id'])->first();
        if (empty($rewrite)) {
            $rewrite = Rewrite::select('rew_id')->where('rew_cat_id', $data_update['cla_cat_id'])->where('rew_cit_id', $data_update['cla_cit_id'])->where('rew_dis_id', $data_update['cla_dis_id'])->where('rew_ward_id', $data_update['cla_ward_id'])->where('rew_street_id', $data_update['cla_street_id'])->where('rew_proj_id', 0)->first();
        }
        if (empty($rewrite)) {
            $rewrite = Rewrite::select('rew_id')->where('rew_cat_id', $data_update['cla_cat_id'])->where('rew_cit_id', $data_update['cla_cit_id'])->where('rew_dis_id', $data_update['cla_dis_id'])->where('rew_ward_id', $data_update['cla_ward_id'])->where('rew_street_id', 0)->where('rew_proj_id', 0)->first();
        }
        if (empty($rewrite)) {
            $rewrite = Rewrite::select('rew_id')->where('rew_cat_id', $data_update['cla_cat_id'])->where('rew_cit_id', $data_update['cla_cit_id'])->where('rew_dis_id', $data_update['cla_dis_id'])->where('rew_ward_id', 0)->where('rew_street_id', 0)->where('rew_proj_id', 0)->first();
        }
        if (!empty($rewrite)) {
            $this->data_tmp['rewrite'] = $rewrite;
            Classified::where('cla_id', $cla_id)->update(['cla_rew_id' => $rewrite->rew_id]);
            ClassifiedFilter::where('cla_id', $cla_id)->update(['cla_rew_id' => $rewrite->rew_id]);
        } else {
            $this->data_tmp['rewrite'] = 'not found';
        }
    }

    //end hai_fix

//    function deleteCla(Request $request){
//        $terms_elastic_data = [];
//        if($request->input('where')!=null){
//            $where_list = explode(',',$request->input('where'));
//            $field_where = config('alias_database.mysql.classifieds');
//            foreach ($where_list as $data){
//                $data = explode(' ',$data);
//                if(isset($data[1])){
//                    $terms_elastic_data[array_search($data[0],$field_where)] =(int)$data[1];
//                }
//            }
//        }
//        $source_elastic_data = [];
//        if ($this->fields != null) {
//            foreach ($this->field_table as $key=>$value){
//                if(strpos($this->fields,$value)!==false){
//                    $source_elastic_data[] = $key;
//                }
//            }
//        } else {
//            $source_elastic_data = array_keys($this->field_table);
//        }
//        $index_elastic_data = "bds_classifieds";
//        $fields_elastic_data = ["cla_title"];
//        $query_elastic_data = 'king bay';
//        $_doc = 'classifieds';
//        $offset = $this->page * $this->limit - $this->limit;
//        $classifieds = searchDataElasticV2($index_elastic_data,$fields_elastic_data,$query_elastic_data,$terms_elastic_data,null,$source_elastic_data,30,$_doc,$offset);
//        $arr_cla_id = [];
//        foreach ($classifieds as $cla){
//            $arr_cla_id[] = $cla['cla_id'];
//        }
//        if($request->method()!='DELETE'){
//            return $classifieds;
//        }
//
//        $classifieds = new Classified();
//        $classifieds = $classifieds->whereIn('cla_id',$arr_cla_id)->get();
//        $arr_classified = [];
//        $error = [];
//        foreach ($classifieds as $classified){
//            $id = $classified->cla_id;
//            $arr_classified[] = ['title'=>$classified->cla_title,'id'=>$classified->cla_id,'rewrite'=>$classified->cla_rewrite,'date'=>date('d-m-Y H:i',$classified->cla_date),'proj_id'=>$classified->cla_proj_id];
//            $classified->delete();
//            ClassifiedFilter::where('cla_id',$id)->delete();
//            try{
//                $sphinx = app()->make('sphinx');
//                $sphinx->query("DELETE FROM " . getSphinxIndexClassified($id) . " WHERE id = $id")->execute();
//            }catch (\Exception $e){
//                $error[] = $e->getMessage();
//            }
//
//            $params = [
//                'index' => 'bds_classifieds',
//                'type' => 'classifieds',
//                'id' => $id
//            ];
//            try{
//                app('elastic')->delete($params);
//            }catch (\Exception $e){
//                $error[] = $e->getMessage();
//            }
//        }
//        return ['cla_delete'=>$arr_classified,'error'=>$error];
//    }

}
