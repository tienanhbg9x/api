<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\RelationshipThemeCategory;
use App\Models\Theme;
use App\Models\UserTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use \DB;
use ZipArchive;
use Illuminate\Support\Facades\Validator;
use Spatie\ImageOptimizer\OptimizerChainFactory;

/**
 * @resource v2 Themes
 *
 * Api for Themes
 */
class ThemeController extends Controller
{

    protected $regex_tag = [
        [
            'type' => 'js',
            'tag' => 'script',
            'att_value' => 'src',
            'regex' => '/<\s*script[^>]*>(.*?)<\s*\/\s*script>/',
            'regex_att_value' => '/(src\s*=\s*"(.+?)")|(src\s*=\s*\'(.+?)\')/'
        ],
        [
            'type' => 'css',
            'tag' => 'link',
            'att_value' => 'href',
            'regex' => '/<link([\w\W]+?)>/',
            'regex_att_value' => '/(href\s*=\s*"(.+?)")|(href\s*=\s*\'(.+?)\')/'
        ],
        [
            'type' => 'ico',
            'tag' => 'link',
            'att_value' => 'href',
            'regex' => '/<link([\w\W]+?)>/',
            'regex_att_value' => '/(href\s*=\s*"(.+?)")|(href\s*=\s*\'(.+?)\')/'
        ],
        [
            'type' => 'img',
            'tag' => 'img',
            'att_value' => 'src',
            'regex' => '/<img([\w\W]+?)>/',
            'regex_att_value' => '/(src\s*=\s*"(.+?)")|(src\s*=\s*\'(.+?)\')/'
        ]
    ];
    protected $arr_type = ['js', 'css', 'jpeg', 'jpg', 'png', 'woff', 'ttf', 'woff2', 'otf', 'gif', 'svg', 'webp', 'tiff', 'zip', 'eot'];

    protected $regex_att_value = '/((?:(?!\s|=).)*)\s*?=\s*?["\']?((?:(?<=")(?:(?<=\\\\)"|[^"])*|(?<=\')(?:(?<=\\\\)\'|[^\'])*)|(?:(?!"|\')(?:(?!\/>|>|\s).)+))/';
    //

    protected $regex_css_url = '/url\((?![\'"]?(?:data):)[\'"]?([^\'"\)]*)[\'"]?\)/';

    protected $source_file_html = [];

    protected $regex_tag_link_html = [
        'type' => 'link',
        'tag' => 'a',
        'att_value' => 'href',
        'regex' => '/<a([\w\W]+?)>/',
        'regex_att_value' => '/href\s*=\s*"(.+?)"/'
    ];

    protected $static_url = null;


    /**
     * GET v2/themes
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | ThemeController
     * `route_name` | themes
     *
     * ### Thông số dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu themes ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong themes ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi themes cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang themes cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@permissions` | NULL | Lấy giá trị với quyền đặc biệt( yêu cầu cần phải có tham số `access_token` đi kèm)
     * `@cat_id` | NULL | Lấy dữ liệu theo chuyên mục
     *
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'fiedls' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3'
        ]);
        $this->static_url = config('configuration.con_static_url').'/duan';
        $offset = $this->page * $this->limit - $this->limit;
        $themes = new Theme();
        $themes = $themes->select($themes->alias($this->fields));
        if($request->input('type')=='search'){
            if($request->input('key_search')!==null){
                $data = [
                    'current_page' => $this->page,
                    'per_page' => $this->limit,
                    'themes' => $this->searchTheme($request->input('key_search'),$request)
                ];
                return $this->setResponse(200, $data);
            }else{
                return response($this->setResponse(500,null,'Not found parama `key_search`'),500);
            }
        }
        if ($request->input('permissions') == 'manage_data') {
            if (($info_token = getInfoToken($request->input('access_token'))) !== false) {
                if ($info_token->rol != "admin"&&$info_token->rol != "developer") return $this->setResponse(401, null, 'Not auth!');
            } else {
                return $this->setResponse(401, null, 'Not auth!');
            }
        } else {
            $themes = $themes->where('thm_active', 1);
        }
        if ($request->input('cat_id')) {
            $arr_cat = RelationshipThemeCategory::select('rtc_theme_id')->where('rtc_cat_id', $request->input('cat_id'))->orderBy('rtc_theme_id', 'desc')->offset($offset)->limit($this->limit)->get();
            $arr_cat = $arr_cat->map(function ($item) {
                return $item->rtc_theme_id;
            })->toArray();
            $themes = $themes->whereIn('thm_id', $arr_cat);
        }
        if ($this->where != null) $themes = whereRawQueryBuilder($this->where, $themes, 'mysql', 'themes');
        $themes = $themes->orderBy('thm_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $check_user_theme = [];
        if ($request->input('access_token') && strpos($this->fields, 'check_theme_user') !== false && strpos($this->fields, 'id') !== false) {
            $check_user_theme = $this->checkUserTheme($themes, $request);
        }
        foreach ($themes as $key => $theme) {
            $themes[$key] = $this->filterTheme($theme, $check_user_theme);
        }
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'themes' => $themes,
        ];
        return $this->setResponse(200, $data);
    }


    /**
     * GET v2/themes/{id}
     *
     * Lấy dữ liệu theo  `id`
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID themes
     * `@fields` | List fields themes
     * `@check_theme_user` | Kiểm tra xem theme đã được sử dụng bởi người dùng hay chưa(Yêu cầu cần có tham số `access_token` đi kèm).
     * `@type` | Kieu lay du lieu.
     * `@html_path_content`|  `show_user_manual`: hien thi goi y chinh sua.
     * @return \Illuminate\Http\Response
     */
    function show(Request $request, $id)
    {
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        $this->static_url = config('configuration.con_static_url').'/duan';
        $theme = new Theme();
        $theme = $theme->select($theme->alias($this->fields))->where('thm_id', $id)->first();
        if ($theme) {
            if ($request->input('access_token') && strpos($this->fields, 'check_theme_user') !== false && strpos($this->fields, 'id') !== false) {
                $user_id = getUserId($request->input('access_token'));
                $user_themes = UserTheme::select('uth_theme_id')->where('uth_deleted_at',null)->where('uth_theme_id', $theme->id)->where('uth_user_id', $user_id)->first();
             if($user_themes){
                 $theme->check_theme_user =1;
             }else{
                 $theme->check_theme_user =0;
             }
            }
            $theme = $this->filterTheme($theme,[],$request);
            return $this->setResponse(200, $theme);
        }
        return response($this->setResponse(404, null, 'Not found id'), 404);

    }


    /**
     * POST v2/themes
     *
     * Thêm dữ liệu vào database
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID themes
     * `@fields` | List fields themes
     *`@cat_id` | mảng chứa id chuyên mục
     *
     * ### Chú ý: Trường `cat_id` có nhận hai kiêu dữ liệu:
     * - string: dạng json(được convert ra từ mảng cat_id)(Được sử dụng trong trường hợp url gửi data đi với : 'content-type': 'multipart/form-data'(data có kèm theo file)).
     * - array: dạng mảng(giữ nguyên)
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:8|max:255',
            'cat_id' => 'required',
            'price' => 'required|max:12',
            'thumbnail' => 'required',
            'zip_file_html' => 'required'
        ],[
            'name.required'=>'Not found param `name`',
            'name.string'=>'Type `name` error',
            'name.min'=>'`name` min(8) error',
            'name.max'=>'`name` mix(255) error',
            'cat_id.required'=>'Not found param `cat_id`',
            'price.required'=>'Not found param `price`',
            'price.numeric'=>'`price` type error',
            'price.min'=>'`price` min(1)',
            'price.max'=>'`price` max(12)',
            'thumbnail.required'=>'Not found param `thumbnail`',
            'zip_file_html.required' =>'Not found param `zip_file_html`'
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return response($this->setResponse(500, '', collect($validator->messages())->collapse()),500);
        }
        $this->static_url = config('configuration.con_static_url').'/duan';
        $theme = new Theme();
        $theme->thm_name = $request->input('name');
        $theme->thm_project_id = $request->input('project_id') ? $request->input('project_id') : 0;
        $theme->thm_cat_id = is_array($request->input('cat_id')) ? json_encode($request->input('cat_id')) : $request->input('cat_id');
        $theme->thm_price =(int) $request->input('price');
        $theme->thm_created_at = date('Y-m-d H:i:s');
        // get config static url
        if ($theme->save()) {
            try {
                $this->updateRelationshipTable($theme->thm_id, [], (is_array($request->input('cat_id')) ? $request->input('cat_id') : json_decode($request->input('cat_id'))));
                $uploadedFile = $request->file('thumbnail');
                $name = $uploadedFile->getClientOriginalName();
                $name = $this->filterFileName($name);
                $type = $name['type'];
                if ($name == false) {
                    return response($this->setResponse(500, null, 'Error type file!'), 500);
                }
                if ($type == 'bmp' || $type == 'gif' || $type == 'jpg' || $type == 'png' || $type == 'jpeg') {
                    $folder_save = 'template/' . $theme->thm_id . '/thumbnail';
                } else {
                    $theme->delete();
                    return response($this->setResponse(500, null, 'Type file error!'), 500);
                }
                Storage::disk('local')->putFileAs(
                    $folder_save,
                    $uploadedFile,
                    'thumbnail.' . $name['type']
                );
                resizeImage(base_path('storage/app/' . $folder_save . '/' . 'thumbnail.' . $name['type']), 500, 'auto');
                optimizeImage(base_path('storage/app/' . $folder_save . '/' . 'thumbnail.' . $name['type']));
                $theme->thm_thumbnail = $folder_save . '/' . 'thumbnail.' . $name['type'];
                $zip_file_html = $request->file('zip_file_html');
                $name = $zip_file_html->getClientOriginalName();
                $name = $this->filterFileName($name);
                if ($name == null) {
                    return response($this->setResponse(500, null, 'Error type file html_zip!'), 500);
                }
                if ($name['type'] == 'zip') {
                    $folder_save = 'template/' . $theme->thm_id;
                } else {
                    Storage::delete($theme->thm_thumbnail);
                    $theme->delete();
                    return response($this->setResponse(500, null, 'Type file error(zip file html)!'), 500);
                }
                Storage::disk('local')->putFileAs(
                    $folder_save,
                    $zip_file_html,
                    $name['name']
                );
                $theme->thm_url = config('app.domain_theme_page_html'). '/' . $folder_save.'/';
                try{
                    $file_source = $this->filterFileTheme($folder_save . '/' . $name['name'], $folder_save);
                }catch (\Exception $error){
                    Storage::deleteDirectory('template/' . $theme->thm_id);
                    $theme->delete();
                    return response($this->setResponse(500, null, "Error(Filter file):" . $error->getMessage()), 500);
                }
                $theme->thm_html_path = json_encode($file_source['html_db']);
                $theme->thm_source = json_encode($file_source['file_source']);
                $theme->save();
                $this->updateThemeElasticSearch($theme->thm_id);
                return $this->setResponse(200, ['id' => $theme->thm_id, 'thumbnail' => $theme->thm_thumbnail, 'source' => $file_source['file_source'], 'html' => $file_source['html']]);
            } catch (\Exception $error) {
                $theme->delete();
                return response($this->setResponse(500, null, "Error:" . $error->getMessage()), 500);
            }

        }
        return response($this->setResponse(500, null, "Save error"), 500);
    }


    /**
     * PUT v2/themes/{id}
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID themes
     * `@fields` | List fields themes
     *`@cat_id` | mảng chứa id chuyên mục
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        $this->static_url = config('configuration.con_static_url').'/duan';
        $theme = Theme::find($id);
        if ($theme) {
            $theme->thm_source = "";
            if ($request->input('name')!==null) $theme->thm_name = $request->input('name');
            if ($request->input('active') !==null) $theme->thm_active = $request->input('active');
            if ($request->input('html_path')!==null) {
                $html_data = $request->input('html_path');
                foreach ($html_data as $key => $html) {
                    Storage::put($html['path'], $html['content']);
                    unset($html_data[$key]['content']);
                }
                $theme->thm_html_path = json_encode($html_data);
            }
            if ($request->input('js_extend')!==null) $theme->thm_js_extend = $request->input('js_extend');
            if ($request->input('project_id')!==null) $theme->thm_js_extend = $request->input('project_id');
            if ($request->input('css_extend')!==null) $theme->thm_css_extend = $request->input('css_extend');
            if ($request->input('price')!==null) $theme->thm_price =(int) $request->input('price');
            if ($request->input('thumbnail')!==null) $theme->thm_thumbnail = $request->input('thumbnail');
            if ($request->input('cat_id')!==null) {
                $this->updateRelationshipTable($theme->thm_id, json_decode($theme->thm_cat_id), (is_array($request->input('cat_id')) ? $request->input('cat_id') : json_decode($request->input('cat_id'))));
                $theme->thm_cat_id = json_encode($request->input('cat_id'));
            }
            if ($theme->save()) {
                $this->updateThemeElasticSearch((int)$id);
                return $this->setResponse(200, 'Saved');
            }
            return response($this->setResponse(500, null, 'Save error'), 500);
        }
        return response($this->setResponse(404, null, 'not found id'), 404);
    }


    /**
     * DELETE v2/themes/{id}
     *
     * Xóa dữ liệu khởi csdl
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID themes
     * `@fields` | List fields themes
     * @return \Illuminate\Http\Response
     */
    function destroy(Request $request, $id)
    {
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        $theme = Theme::find($id);
        if ($theme) {
            try {
                if ($request->input('type') == 'delete_source') {
                    $folder = ['js', 'fonts', 'css', 'images'];
                    foreach ($folder as $dir) {
                        Storage::deleteDirectory('template/' . $id . '/' . $dir);
                    }
                    $theme->source = null;
                    $theme->save();
                    return $this->setResponse(200, 'Deleted');
                }
                Storage::deleteDirectory('template/' . $id);
            } catch (\Exception $error) {
                return response($this->setResponse(500, null, 'Delete file error!:' . $error->getMessage()), 500);
            }
            $theme->delete();
            RelationshipThemeCategory::where('rtc_theme_id', $id)->delete();
            $this->deleteElasticSearch($id);
            return $this->setResponse(200, 'Deleted');
        }
        return response($this->setResponse(404, null, 'Not found id'), 404);

    }

    /**
     * POST v2/themes/{id}
     *  Quản lí file tài nguyên cho templates
     * Update file for theme
     * @return \Illuminate\Http\Response
     */

    function manageFile(Request $request, $id)
    {
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        $theme = Theme::find($id);
        if ($theme) {
            if ($request->input('delete_file')) {
                if (Storage::has($request->input('delete_file'))) {
                    $sources = collect(json_decode($theme->thm_source));
                    $sources = $sources->whereNotIn('path', [$request->input('delete_file')]);
                    $theme->thm_source = json_encode($sources);
                    $theme->save();
                    Storage::delete($request->input('delete_file'));
                    return $this->setResponse(200, 'Delete success');
                }
                return response($this->setResponse(500, null, 'Delete error'), 500);
            }
            $uploadedFile = $request->file('file');
            $name = $uploadedFile->getClientOriginalName();
            $exp_name = explode('.', $name);
            $type = isset($exp_name[count($exp_name) - 1]) ? $exp_name[count($exp_name) - 1] : null;
            if ($type == null) {
                return response($this->setResponse(500, null, 'Error type file!'), 500);
            }
            $data = [];
            if ($request->input('type') == 'thumbnail') {
                if ($type == 'jpg' || $type == 'png' || $type == 'jpeg') {
                    $folder_save = 'template/' . $id . '/thumbnail';
                    Storage::delete($theme->thm_thumbnail);
                    $data = saveFileLocal('thumbnail.' . $type, $uploadedFile, $folder_save);
                    resizeImage(base_path('storage/app/' . $folder_save . '/' . 'thumbnail.' . $type), 500, 'auto');
                    optimizeImage(base_path('storage/app/' . $folder_save . '/' . 'thumbnail.' . $type));
                    $theme->thm_thumbnail = $folder_save . '/' . 'thumbnail.' . $type;
                    $theme->save();
                    return $this->setResponse(200, $data);
                } else {
                    return response($this->setResponse(500, null, 'Error type file!'), 500);
                }
            } else if ($type == 'html') {
                $folder_save = 'template/' . $id;
            } else if ($type == 'css') {
                $folder_save = 'template/' . $id . '/css';
            } else if ($type == 'js') {
                $folder_save = 'template/' . $id . '/js';
            } else if ($type == 'woff' || $type == 'ttf' || $type == 'woff2' || $type == 'otf' || $type == 'eot') {
                $folder_save = 'template/' . $id . '/fonts';
            } else if ($type == 'bmp' || $type == 'gif' || $type == 'jpg' || $type == 'png' || $type == 'svg' || $type == 'webp' || $type == 'tiff') {
                $folder_save = 'template/' . $id . '/images';
            } else {
                return response($this->setResponse(500, null, 'Type file error!'), 500);
            }
            if (Storage::has($folder_save . '/' . $name)) {
                return response($this->setResponse(500, null, 'File existed!'), 500);
            }
            $data = saveFileLocal($name, $uploadedFile, $folder_save);
            if ($type == 'html') {
                $data['type'] = $request->input('html_type');
                if ($theme->thm_html_path == null || $theme->thm_html_path == "") {

                    $theme->thm_html_path = json_encode([$data]);
                } else {
                    $data_tmp = json_decode($theme->thm_html_path);
                    $data_tmp[] = $data;
                    $theme->thm_html_path = json_encode($data_tmp);
                }
                $theme->save();
                $data['content_html'] = getHtmlContent('template/' . $id . '/' . $name);
            } else if ($type == 'css') {
                if ($theme->thm_source == null || $theme->thm_source == "") {
                    $theme->thm_source = json_encode([$data]);
                } else {
                    $data_tmp = json_decode($theme->thm_source);
                    $data_tmp[] = $data;
                    $theme->thm_source = json_encode($data_tmp);
                }
                $data['content_css'] = minimizeCSS(getHtmlContent($folder_save . '/' . $name));
            } else {
                if ($theme->thm_source == null || $theme->thm_source == "") {
                    $theme->thm_source = json_encode([$data]);
                } else {
                    $data_tmp = json_decode($theme->thm_source);
                    $data_tmp[] = $data;
                    $theme->thm_source = json_encode($data_tmp);
                }
                $theme->save();
            }

            return $this->setResponse(200, $data);

        } else {
            return response($this->setResponse(404, null, 'Not found id'), 404);
        }

    }

    function filterTheme($theme, $check_user_theme = [], $request=null)
    {
        if(is_array($theme)){
            if (isset($theme['id']) && count($check_user_theme) != 0) {
                if (in_array($theme['id'], $check_user_theme)) {
                    $theme['check_user_theme'] = 1;
                } else {
                    $theme['check_user_theme'] = 0;
                }
            } else {
                $theme['check_user_theme'] = 0;
            }

            if (isset($theme['cat_id'])) $theme['cat_id'] = json_decode($theme['cat_id']);
            if (isset($theme['source'])) {
                $theme['source'] = json_decode($theme['source']);
            }
            if (isset($theme['thumbnail'])) {
                $theme['thumbnail'] = $this->static_url . '/' . $theme['thumbnail'];
            }
            if (isset($theme['html_path'])) {
                $theme['html_path'] = json_decode($theme['html_path']);
                if (!is_array($theme['html_path'])) $theme['html_path'] = [];
                foreach ($theme['html_path'] as $key => $data) {
                    if (Storage::has($data->path)) {
                        $content_html = Storage::get($data->path);
                        if($request!=null&&$request->input('html_path_content') == 'show_user_manual'){
                            $content_html = str_replace("contenteditable=\"false\"", "contenteditable=\"true\"", $content_html);
                            $content_html = str_replace('onclick="return true"', 'onclick="return false"', $content_html);
                        }
                        $data->content = $content_html;
                    } else {
                        $data->content = "";
                    }
                }
                $url_home = collect($theme['html_path'])->where('name', 'index.html')->first();
                if ($url_home) {
                    $theme['url_home'] = config('app.domain_theme_page_html') . '/' . $url_home->path;
                }
            }
        }else{
            if ($theme->id && count($check_user_theme) != 0) {
                if (in_array($theme->id, $check_user_theme)) {
                    $theme->check_user_theme = 1;
                } else {
                    $theme->check_user_theme = 0;
                }
            } else {
                $theme->check_user_theme = 0;
            }
            if ($theme->cat_id) $theme->cat_id = json_decode($theme->cat_id);
            if ($theme->source) {
                $theme->source = json_decode($theme->source);
            }
            if ($theme->thumbnail) {
                $theme->thumbnail = $this->static_url . '/' . $theme->thumbnail;
            }
            if ($theme->html_path) {
                $theme->html_path = json_decode($theme->html_path);
                if (!is_array($theme->html_path)) $theme->html_path = [];
                foreach ($theme->html_path as $key => $data) {
                    if (Storage::has($data->path)) {
                        $content_html = Storage::get($data->path);
                        if($request!=null&&$request->input('html_path_content') == 'show_user_manual'){
                            $content_html = str_replace("contenteditable=\"false\"", "contenteditable=\"true\"", $content_html);
                            $content_html = str_replace('onclick="return true"', 'onclick="return false"', $content_html);
                        }
                        $data->content = $content_html;
                    } else {
                        $data->content = "";
                    }
                }
                $url_home = collect($theme->html_path)->where('name', 'index.html')->first();
                if ($url_home) {
                    $theme->url_home = $this->static_url . '/' . $url_home->path;
                }
            }
        }

        return $theme;
    }

    function updateThemeElasticSearch($theme_id){
        $theme = new Theme();
        $theme = $theme->select($theme->alias('*'))->where('thm_id',$theme_id)->first();
        if($theme){
            $data_return = $theme->toArray();
            unset($data_return['source']);
            unset($data_return['html_path']);
            $data_return["name1"] = BuildTrigrams($data_return['name']);
            $data_return["name2"] = BuildPhraseTrigrams($data_return['name']);
            $params['body'][] = [
                'index' => [
                    '_index' => 'themes',
                    '_type' => '_doc',
                    '_id' => $theme->id,
                ]
            ];
            $params['body'][] = $data_return;
            app('elastic')->bulk($params);
        }else{
            return false;
        }
    }

    function deleteElasticSearch($theme_id){
        $params = [
            'index' => 'themes',
            'type' => '_doc',
            'id' => (int)$theme_id
        ];
        app('elastic')->delete($params);
    }

    function checkUserTheme($themes, $request)
    {
        $user_id = getUserId($request->input('access_token'));
        $arr_theme_id = $themes->map(function ($item) {
            if(is_array($item)){
                return $item['id'];
            }
            return $item->id;

        });

        $user_themes = UserTheme::select('uth_theme_id')->where('uth_deleted_at',null)->whereIn('uth_theme_id', $arr_theme_id)->where('uth_user_id', $user_id)->get();
        return $user_themes->map(function ($item) {
            return $item->uth_theme_id;
        })->toArray();
    }

    function filterCss($css_files)
    {
        try {
            $source_css = [];
            foreach ($css_files as $file) {
                $css_content = Storage::get($file['path']);
                preg_match_all($this->regex_css_url, $css_content, $matches, PREG_SET_ORDER, 0);
                $check_url = collect([]);
                foreach ($matches as $item_url) {
                    $info_file = $this->filterFileName($item_url[1]);
                    if ($info_file !== false) {
                        if ($check_url->where('url', $item_url[1])->first()) {
                            continue;
                        } else {
                            $check_url->push(['url' => $item_url[1]]);
//                            $name_encode = cleanRewriteAccent(html_entity_decode( $info_file['name'], ENT_COMPAT, 'UTF-8'));
                            $name_encode = cleanUrl($info_file['name']);
                            $source_css[$file['path']][] = ['content' => $item_url[0], 'url' => $item_url[1], 'file_name' => $info_file['name'], 'type_file' => $info_file['type'],'name_encode'=>$name_encode];
                        }
                    }

                }
            }
            return $source_css;
        } catch (\Exception $error) {
            throw new \Exception('filterCss error:' . $error->getMessage());
        }

    }

    function scanFolder($folder)
    {
        $source_system = getSourceTheme();
        $files = Storage::allFiles($folder);
        $list_file = ['html' => [], 'css' => [], 'all_file' => []];
        foreach ($files as $file) {
            $info_file = $this->filterFileName($file);
            if ($info_file !== false) {
                $find_file_system = $source_system->where('name', $info_file['name'])->first();
                if ($find_file_system) {
                    $url_public = $this->static_url . '/' . $find_file_system['path'];
                } else {
                    $url_public = $this->static_url . '/' . $file;
                }
                $path_file_in_template = str_replace($folder.'/','',$file);
//                $path_encode = cleanRewriteAccent(html_entity_decode($path_file_in_template, ENT_COMPAT, 'UTF-8'));
//                $name_encode =  cleanRewriteAccent(html_entity_decode($info_file['name'], ENT_COMPAT, 'UTF-8'));
                $path_encode = cleanUrl($path_file_in_template);
                $name_encode = cleanUrl($info_file['name']);
                if ($info_file['type'] == 'html') {
                    if ($find_file_system) {
                        $url_public = config('app.domain_theme_page_html') . '/' . $find_file_system['path'];
                    } else {
                        $url_public = config('app.domain_theme_page_html') . '/' . $file;
                    }
                    $list_file['html'][] = ['url_public' => $url_public, 'path' => $file, 'name' => $info_file['name'], 'type' => $info_file['type'],'path_file_in_template'=>$path_file_in_template,'path_encode'=>$path_encode,'name_encode'=>$name_encode];
                } else if ($info_file['type'] == 'css') {

                    $list_file['css'][] = ['url_public' => $url_public, 'path' => $file, 'name' => $info_file['name'], 'type' => $info_file['type'],'path_file_in_template'=>$path_file_in_template,'path_encode'=>$path_encode,'name_encode'=>$name_encode];
                }
                $list_file['all_file'][] = ['url_public' => $url_public, 'path' => $file, 'name' => $info_file['name'], 'type' => $info_file['type'],'path_file_in_template'=>$path_file_in_template,'path_encode'=>$path_encode,'name_encode'=>$name_encode];
            }
        }
        return $list_file;
    }

    function filterCssInHtml($content){
        $value= [];
        preg_match_all($this->regex_css_url, $content, $matches_url_css, PREG_SET_ORDER, 0);
        foreach ($matches_url_css as $item){
            if(isset($item[1])){
                if(strpos($item[1],'http')===false){
                    $file_info = $this->filterFileName($item[1]);
                    $full_path=  explode('#', explode('?', $item[1])[0])[0];
//                    $full_path = cleanRewriteAccent(html_entity_decode( $full_path, ENT_COMPAT, 'UTF-8'));
                    $full_path =cleanUrl($full_path);
                    $value[] = ['name'=>$file_info['name'],'type'=>$file_info['type'],'full_path'=>$item[1],'url'=>$item[0],'full_path_encode'=>$full_path];
                }
            }
        }
        return $value;
    }

    function filterFileTheme($file, $folder)
    {
        try {
            $file_zip = base_path('/storage/app/' . $file);
            $zip = new ZipArchive();
            $res = $zip->open($file_zip);
            if ($res === TRUE) {
                // extract it to the path we determined above
                $zip->extractTo(base_path('/storage/app/' . $folder));
                $zip->close();
            } else {
                throw new \Exception('Unzip error');
            }
            Storage::delete($file);
            $list_file = $this->scanFolder($folder);
            $source_css_file = $this->filterCss($list_file['css']);
            $source_html_file = $this->filterHtml($list_file['html']);
            $status_filter_file = $this->replaceHtml($source_html_file, collect($list_file['all_file']));
            $this->replaceCssFile($source_css_file, collect($list_file['all_file']));
            $this->optimizeImage($list_file['all_file']);
            $source = [];
            foreach ($status_filter_file['content'] as $path => $info) {
                $info_file = $this->filterFileName($path);
                $source['html'][] = ['name' => $info_file['name'], 'path' => $path, 'title' => $info['name'], 'content' => $info['content'], 'url' => config('app.domain_theme_page_html'). '/' . $path];
                $source['html_db'][] = ['name' => $info_file['name'], 'path' => $path, 'title' => $info['name'], 'url' => config('app.domain_theme_page_html') . '/' . $path];
            }
            $source['file_source'] = $status_filter_file['source'];
            return $source;
        } catch (\Exception $error) {
            throw new \Exception('Scanfile error:' . $error->getMessage());
        }
    }

    function optimizeImage($images){
        $images = collect($images)->whereIn('type',['jpg','jpeg']);
        foreach ($images as $image){
            if(file_exists(base_path('storage/app/'.$image['path']))){
                $optimizerChain = OptimizerChainFactory::create();
                $optimizerChain->optimize(base_path('storage/app/'.$image['path']));
            }

        }
    }

    function replaceHtml($html_file, $list_file)
    {
        $file_error = null;
        try {
            $content_file_replace = [];
            $source_html = collect([]);
            foreach ($html_file as $key => $files) {
                $content_file = Storage::get($key);
                $value_filter_css_file = $this->filterCssInHtml($content_file);
                foreach ($value_filter_css_file as $url_css_file){
                    $file_in_folder = $list_file->where('path_encode',$url_css_file['full_path_encode'])->first();
                    if($file_in_folder){
                        if(strpos($url_css_file['url'],'/'.$file_in_folder['path_file_in_template'])!==false){
                            $url_replace = str_replace('/'.$file_in_folder['path_file_in_template'],$file_in_folder['url_public'],$url_css_file['url']);
                        }else{
                            $url_replace = str_replace($file_in_folder['path_file_in_template'],$file_in_folder['url_public'],$url_css_file['url']);
                        }
                        if(isset($url_replace)){
                            $content_file = str_replace($url_css_file['url'],$url_replace,$content_file);
                        }
                    }

                }
//                $content_file = minimizeHtml($content_file);
                $file_error = $key;
                $content_file = $this->replaceTagA($content_file, $list_file);
                foreach ($files as $file) {
                    $find_file = $list_file->where('path_encode', $file['att_value_encode'])->first();
                    if ($find_file) {
                        $file['status'] = true;
                        if (!$file['loop']) {
                            $tag_replace = str_replace($file['att_value'],$find_file['url_public'],$file['html']);
                            $content_file = str_replace($file['html'],$tag_replace, $content_file);
                        }
                    } else {
                        $file['status'] = false;
                    }
                    if (!$source_html->where('file_name', $file['file_name'])->where('tag', $file['tag'])->where('att_value', $file['att_value'])->first()) {
                        $source_html->push($file);
                    }
                }
                preg_match_all('/<title[^>]*\>([^\/]*)<\/title>/m', $content_file, $matches, PREG_SET_ORDER, 0);
                Storage::put($key, $content_file);
                if(!isset($matches[0][1])){
                    throw new \Exception('Not found tag title');
                }
                $content_file_replace[$key] = ['name' => $matches[0][1], 'content' => $content_file];
            }
            return ['source' => $source_html, 'content' => $content_file_replace];
        } catch (\Exception $error) {
            throw new \Exception('replaceHtml error:' . $error->getMessage() .' - Line: '.$error->getLine().' - File error:'.$file_error);
        }

    }

    function replaceCssFile($css_file, $list_file)
    {
        $content_file_replace = [];
        foreach ($css_file as $key => $files) {
            $content_file = Storage::get($key);
            foreach ($files as $file) {
                $find_file = $list_file->where('name_encode', $file['name_encode'])->first();
                if ($find_file) {
                    $content_file = str_replace($file['url'], $find_file['url_public'], $content_file);
                }
            }
            $content_file_replace[$key] = $content_file;
            Storage::put($key, $content_file);
        }
        //update file
        return $content_file_replace;
    }

    function filterHtml($html_files)
    {
        try {
            $list_file = [];
            foreach ($html_files as $file) {
                $html_content = Storage::get($file['path']);
                $sources = collect([]);
                foreach ($this->regex_tag as $regex) {
                    preg_match_all($regex['regex'], $html_content, $matches, PREG_SET_ORDER, 0);
                    foreach ($matches as $html_tag) {
                        preg_match_all($regex['regex_att_value'], $html_tag[0], $matches_2, PREG_SET_ORDER, 0);
                        if (!isset($matches_2[0])) continue;
                        $attribute_value = isset($matches_2[0][4])?$matches_2[0][4]:$matches_2[0][2];
                        $info_file = $this->filterFileName($attribute_value);
                        if ($info_file === false) continue;
                        if (!in_array($info_file['type'], $this->arr_type)) continue;
                        $att_path = explode('#', explode('?', $attribute_value)[0])[0];
                        if ($sources->where('att_value', $attribute_value)->first()) {
                            $sources->push([
                                'html' => $html_tag[0],
                                'type' => $info_file['type'],
                                'att_value' => $attribute_value,
                                'att_name_value' => $regex['att_value'],
                                'tag' => $regex['tag'],
                                'file_name' => $info_file['name'],
                                'loop' => false,
//                                'att_value_encode'=> cleanRewriteAccent(html_entity_decode($att_path, ENT_COMPAT, 'UTF-8'))
                                'att_value_encode'=> cleanUrl($att_path)
                            ]);
                        } else {
                            $sources->push([
                                'html' => $html_tag[0],
                                'type' => $info_file['type'],
                                'att_value' => $attribute_value,
                                'att_name_value' => $regex['att_value'],
                                'tag' => $regex['tag'],
                                'file_name' => $info_file['name'],
                                'loop' => false,
//                                'att_value_encode'=> cleanRewriteAccent(html_entity_decode($att_path, ENT_COMPAT, 'UTF-8')),
                                'att_value_encode'=>cleanUrl($att_path)
                            ]);
                        }

                    }
                }

                $list_file[$file['path']] = $sources;
            }
        } catch (\Exception $err) {
            throw new \Exception('error filter html:' . $err->getMessage());
        }
        return $list_file;
    }

    function searchTheme($key_search,$request){

        $terms_elastic_data = [];
        if ($request->input('where')) {
            $where = explode(',', $request->input('where'));
            foreach ($where as $item) {
                $item = explode(" ", $item);
                if (isset($item[1])) $terms_elastic_data[$item[0]] = $item[1];
            }
        }

        if ($request->input('permissions') == 'manage_data') {
            if (($info_token = getInfoToken($request->input('access_token'))) !== false) {
                if ($info_token->rol != "admin") return $this->setResponse(401, null, 'Not auth!');
            } else {
                return $this->setResponse(401, null, 'Not auth!');
            }
        } else {
            $terms_elastic_data['active'] = 1;
        }

        if ($this->fields != null) {
            $source_elastic_data = explode(',', $this->fields);
        } else {
            $source_elastic_data = ["id", "name", "html_path", "url", "thumbnail", "source", "active", "project_id", "js_extend", "css_extend", "cat_id", "price", "created_at"];
        }
        $query_elastic_data = BuildPhraseTrigrams($key_search);
        $data_return =  searchDataElastic('themes', ["name2"], $query_elastic_data, $terms_elastic_data, $source_elastic_data, $this->limit);
        $check_user_theme = [];
        if ($request->input('access_token') && strpos($this->fields, 'check_theme_user') !== false && strpos($this->fields, 'id') !== false) {
            $check_user_theme = $this->checkUserTheme(collect($data_return), $request);
        }
        foreach ( $data_return as $key => $theme) {
            $data_return[$key] = $this->filterTheme($theme, $check_user_theme);
        }
        return $data_return;
    }

    function replaceTagA($content, $list_files)
    {
        preg_match_all($this->regex_tag_link_html['regex'], $content, $matches, PREG_SET_ORDER, 0);
//        $list_files_html = $list_files->where('type', 'html');
        foreach ($matches as $link) {
            preg_match_all($this->regex_tag_link_html['regex_att_value'], $link[0], $matches_2, PREG_SET_ORDER, 0);
            if (isset($matches_2[0][1]) && strpos($matches_2[0][1], 'http') === false && $matches_2[0][1] != '#' && strpos($matches_2[0][1], '.html') !== false) {
//                $info_file = $this->filterFileName($matches_2[0][1]);
//                $url_public = $list_files_html->where('name', $info_file['name'])->first();
//                if ($url_public) {
//                    $href_replace = str_replace($info_file['name'], $url_public['url_public'], $matches_2[0][1]);
//                    $content = str_replace($matches_2[0][0], 'href="' . $href_replace . '"', $content);
//                }
                continue;
            }else if(isset($matches_2[0][1]) && strpos($matches_2[0][1], 'http') === false && $matches_2[0][1] != '#'&&strpos($matches_2[0][1], '.') !== false){
                $file_info = $this->filterFileName($matches_2[0][1]);
                if($file_info){
//                    $value_match_encode = cleanRewriteAccent(html_entity_decode($matches_2[0][1], ENT_COMPAT, 'UTF-8'));
                    $value_match_encode =cleanUrl($matches_2[0][1]);
                    $file_in_folder = $list_files->where('path_encode',$value_match_encode)->first();
                    if($file_in_folder){
                        if(strpos($matches_2[0][0],'/'.$file_in_folder['path_file_in_template'])!==false){
                            $url_replace = str_replace('/'.$file_in_folder['path_file_in_template'],$file_in_folder['url_public'],$matches_2[0][0]);
                        }else{
                            $url_replace = str_replace($file_in_folder['path_file_in_template'],$file_in_folder['url_public'],$matches_2[0][0]);
                        }
                        if(isset($url_replace)){
                            $content = str_replace($matches_2[0][0],$url_replace,$content);
                        }
                    }
                }

            }

        }
        return $content;
    }


    function filterFileName($path)
    {
        $path = html_entity_decode($path);
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

    function updateRelationshipTable($theme_id, $cat_id_old, $cat_id_new)
    {
        $arr_insert = [];
        if (count($cat_id_old) > count($cat_id_new)) {
            DB::table('relationship_theme_category')->where('rtc_id', $theme_id)->whereNotIn('rtc_cat_id', $cat_id_new)->delete();
        } else if (count($cat_id_old) < count($cat_id_new)) {
            foreach ($cat_id_new as $value) {
                if (in_array($value, $cat_id_old) == false) {
                    $arr_insert[] = ['rtc_theme_id' => $theme_id, 'rtc_cat_id' => $value];
                }
            }
        } else if (count($cat_id_old) == 0) {
            $arr_insert = [];
            foreach ($cat_id_new as $value) {
                if (in_array($value, $cat_id_old) == false) {
                    $arr_insert[] = ['rtc_theme_id' => $theme_id, 'rtc_cat_id' => $value];
                }
            }
        }
        if (count($arr_insert) != 0) {
            DB::table('relationship_theme_category')->insert($arr_insert);
        }
    }


}
