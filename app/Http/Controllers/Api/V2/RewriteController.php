<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Category;
use App\Models\RewriteNoaccent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Firebase\JWT\JWT;

use App\Models\Rewrite;

/**
 * @resource v2 Rewrites
 *
 * Api for Rewrites
 */
class RewriteController extends Controller
{
    //


    /**
     * GET v2/rewrites
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
     * `@slug` | null | rewrite của bảng rewrites. Để  sử dụng được bắt buộc phải có `type=detail` gửi kèm theo.
     * `@page` | 1 | Phân trang
     * `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api/rewrites?where=id+1223,title+ads-asd-fasdf)
     * `@ssr` | boolean | Chế độ gọi api cho web .  Mặc định là `false`
     *
     * ### Tùy chọn với tham số `type`:
     *  Giá trị (Value) | Điều kiện | Mô tả chi tiết
     * --------- | ------- | -------
     * `detail` |  Phải có tham số `slug` đi kèm  | Lấy chi tiết của một rewrite , nó có thể là một classified,category, project  với `slug` lương ứng
     * `search` | Phải có tham số  `keyword` đi kèm |Lọc `rewrites` theo từ khóa theo `keyword` tương ứng
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
            'where' => 'string|max:255|min:3',
            'ssr' =>'boolean'
        ]);
        if ($request->input('type') == 'detail') {

            if ($request->input('slug')) {
                return $this->CheckSlug($request->input('slug'));
            } else {
                return response('Not found param `slug`',404);
            }
        } else if ($request->input('type')  == 'search') {
            if ($request->input('keyword')) {
                return $this->searchRewrite();
            } else {
                return response($this->setResponse(404, null, 'Not found param `keyword`'),404);
            }
        }
        $rewrite = new Rewrite();
        $offset = $this->page * $this->limit - $this->limit;
        $rewrite = $rewrite->select($rewrite->alias($this->fields))->orderBy('rew_id', 'desc');
        if ($this->where != null) $rewrite = whereRawQueryBuilder($this->where, $rewrite, 'mysql', 'rewrites');
        $rewrite = $rewrite->offset($offset)->limit($this->limit)->get();
        return $this->setResponse(200, $rewrite);
    }

    /**
     * GET v2/rewrites/{id}
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID rewrites
     * `@fields` | List fields rewrites
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {
        $rewrite = new Rewrite();
        $rewrite = $rewrite->select($rewrite->alias($this->fields))->where('rew_id',$id)->first();
        if (!$rewrite) return $this->setResponse(404, null, 'Not found id ' . $id);
        return $this->setResponse(200, $rewrite);
    }

    /**
     * POST v2/rewrites
     * Thêm mới rewrite
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@fields` | List fields rewrites
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:255',
            //'rewrite' => 'required|string|min:5|max:255',
            'what' => 'required|string|min:5|max:255',
            'where' => 'required|string|min:5|max:255'
        ], [
            'title.required' => 'Bạn chưa cung cấp tiêu đề bài viết(title)',
            'title.string' => 'Kiểu dữ liệu sai(title)',
            'title.min' => 'Tiêu đề quá ngắn(title)',
            'title.max' => 'Tiêu đề quá dài(title)',
            'what.required' => 'Bạn chưa cung cấp tiêu đề bài viết(what)',
            'what.string' => 'Kiểu dữ liệu sai(what)',
            'what.min' => 'Tiêu đề quá ngắn(what)',
            'what.max' => 'Tiêu đề quá dài(what)',
            'where.required' => 'Bạn chưa cung cấp tiêu đề bài viết(where)',
            'where.string' => 'Kiểu dữ liệu sai(where)',
            'where.min' => 'Tiêu đề quá ngắn(where)',
            'where.max' => 'Tiêu đề quá dài(where)',
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return $this->setResponse(500, '', collect($validator->messages())->collapse());
        }
        //Bắt đầu xử lý ------------------------------------------------------------------------>
        $myModel = new RewriteNoaccent();
        $title = trim(convertToUnicode($request->input('title')));
        $what = trim(convertToUnicode($request->input('what')));
        $where = trim(convertToUnicode($request->input('where')));
        $rewrite = trim(convertToUnicode($request->input('rewrite')));
        if(empty($rewrite)) $rewrite = $myModel->createRewrite($title);
        $myModel->rew_title             = $title;
        $myModel->rew_md5               = md5($rewrite);
        $myModel->rew_length            = strlen($rewrite);
        $myModel->rew_date              = time();
        $myModel->rew_table             = "categories_location";
        $myModel->rew_what              = $what;
        $myModel->rew_where             = $where;
        $rew_cat_id                     = (new CategoryController())->findCatId($what);
        $arrAddress                     = (new AddressController())->SearchDetect($where);
        $arrLocation                    = (new LocationController())->SearchDetect($where);
        print_r($arrLocation);exit();
        return $this->setResponse(500, null, $arrAddress);
    }

    function createRewrite($catid = 0,$citid = 0,$disid = 0,$wardid = 0,$streetid = 0,$projid = 0){

    }

    function searchRewrite()
    {
        //hai_fix
        $keyword = urldecode($_REQUEST['keyword']);
        $index = 'rewrites';
        $fields = ['title'];
        $_doc = '_doc';
        $source_elastic_data = ['title', 'rewrite'];
        $result = searchDataElasticV4($index, $fields, $keyword, null, null, $source_elastic_data, 20, $_doc);
//        $sphinx = app()->make('sphinx');
//        $offset = $this->page * $this->limit - $this->limit;
//        $query_sphinx = $sphinx->select('id')->from('bds_autocomplete')->where('rew_table', 'in', ['categories_city','categories_location']);
//        if ($this->where != null) $query_sphinx = whereRawQueryBuilder($this->where, $query_sphinx, 'mysql', 'rewrite');
//        $query_sphinx->match('rew_title', $keyword);
//        $query_sphinx->orderBy('id', 'desc');
//        $query_sphinx->limit($offset, $this->limit);
//        $result = $query_sphinx->execute()->fetchAllAssoc();
//        $result = filterIdSphinx($result);
//        dd($cla);
//        $rewrites = new Rewrite();
//        $rewrites = $rewrites->select($rewrites->alias($this->fields))->whereIn('rew_id', $result)->orderBy('rew_id', 'desc')->get();
//
        return $this->setResponse(200,$result);
        //end hai_fix
    }

    function CheckSlug($slug)
    {
        $slug = urldecode($slug);
        $slug_md5 = md5($slug);
        $rewrite = new Rewrite();
        $rewrite = $rewrite->select('rew_id', 'rew_title', 'rew_cat_id', 'rew_cit_id', 'rew_dis_id', 'rew_ward_id', 'rew_street_id', 'rew_proj_id', 'rew_table', 'rew_id_value', 'rew_param', 'rew_description', 'rew_keyword', 'rew_picture')->where('rew_length', strlen($slug))->where('rew_md5', $slug_md5);

        if ($this->where != null) $rewrite = whereRawQueryBuilder($this->where, $rewrite, 'mysql', 'rewrites');
        $rewrite = $rewrite->first();
        if ($rewrite) {
            if ($rewrite->rew_table == 'categories_multi'||$rewrite->rew_table == 'categories_city'||$rewrite->rew_table == 'categories_location') {
                $categories = Category::select('cat_id','cat_type')->where('cat_active',1)->where('cat_id',$rewrite->rew_cat_id)->first();
                if($categories){
                    if($categories->cat_type=='project'){

                        $projects = new ProjectController();
                        return $projects->getListProject(['proj_cat_id'=>$rewrite->rew_cat_id]);
                    }
                }

                $classifieds = new ClassifiedController();
                return $classifieds->getClassifiedsCategory($rewrite);
            } else if ($rewrite->rew_table == 'investors') {

            } else if ($rewrite->rew_table == 'location') {

            } else if ($rewrite->rew_table == 'projects') {
                $project = new ProjectController();
                return $project->getProject($rewrite);
            } else if ($rewrite->rew_table == 'projects_compare') {
                $project_compare = new ProjectCompareController();
                return $project_compare->getProjectCompare($rewrite);
            }
        } else {
            $classified = new ClassifiedController();
            return $classified->getClassified($slug);
        }
    }

    /**
     * GET v2/rewrites/bread-crumb
     *
     * Cho phép trả về danh sách breadCrumb của link tương ứng.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | RewriteController
     * `route_name` | rewrites/bread-crumb
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@id` | 0 | Trường rew_id của bảng classifieds.
     * `@project_id` | 0 | Trường  loc_id của bảng classifieds.
     *
     * @return \Illuminate\Http\Response
     */

    function getBreadCrumb(Request $request){

        $rew_id = $request->input('id',0);
        $proj_id = $request->input('project_id',0);
        $breadCrumb  = getBreadCrumb($rew_id,$proj_id);
        //hai_fix
        if ($request->has('client_token') && count($breadCrumb) > 1){
            $key = $request->input('client_token');
            $key = getInfoFormSecret($key);
            if($key==false) return response('Not auth',401);
            $key =  $key->id;
            $arr_cache = Cache::get($key);
            if($arr_cache == null) $arr_cache = [];
            if(count($breadCrumb) - 1>=0){
                $rewrite = $breadCrumb[count($breadCrumb) - 1]['rew_rewrite'];
                $title = $breadCrumb[count($breadCrumb) - 1]['rew_title'];
            }

            foreach ($arr_cache as $value){
                if ($value->title == $title){
                    return $this->setResponse(200,$breadCrumb);
                }
            }
            if(count($arr_cache)==10){
                unset($arr_cache[9]);
                array_filter($arr_cache);
            }
            $arr_cache[] = (object)['title' => $title, 'rewrite' => $rewrite];
            Cache::put($key, $arr_cache, 43200);
//            if(!array_key_exists($rewrite, $arr_cache)){
//                $arr_cache[$rewrite] = $title;
//                Cache::put($key, $arr_cache, 30);
//            }
        }

        //end hai_fix
        return $this->setResponse(200,$breadCrumb);
    }

}
