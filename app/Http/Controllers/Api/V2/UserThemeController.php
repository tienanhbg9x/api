<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Theme;
use App\Models\UserTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\sendMailNotificationTheme;

/**
 * @resource v2 User-themes
 *
 * Api for User-themes
 */
class UserThemeController extends Controller
{
    //

    protected $regex_tag_link_html = [
        'type' => 'link',
        'tag' => 'a',
        'att_value' => 'href',
        'regex' => '/<a([\w\W]+?)>/',
        'regex_att_value' => '/(href\s*=\s*"(.+?)")|(href\s*=\s*\'(.+?)\')/'
    ];

    protected $regex_meta_tag = '/<meta([\w\W]+?)>/';

    protected $regex_title_tag = '/<title[^>]*\>([^\/]*)<\/title>/m';

    protected $meta_attr_tag = ['keywords', 'description', 'og:title', 'og:description', 'og:image', 'title', 'og:url', 'abstract', 'classification', 'area', 'copyright', 'owner', 'generator', 'language', 'article:section', 'article:tag', 'geo:placename', 'author', 'og:type'];


    /**
     * GET v2/user-themes
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | UserThemeController
     * `route_name` | user-themes
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu user-themes ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong user-themes ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi user-themes cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang user-themes cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@type` | NULL | Kiểu lấy giá trị
     * `@key_search` | NULL | Key tìm kiếm (Yêu cầu cần có tham số `type = search`)
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
            'type' => 'string|max:255|min:3'
        ]);
        if ($request->input('type') == 'search') {
            if ($request->input('key_search')) {
//                return $this->searchUser($request->input('key_search'));
            } else {
                return responce($this->setResponse(500, null, 'Not found param `key_search`'), 500);
            }
        }

        $offset = $this->page * $this->limit - $this->limit;
        $themes = new UserTheme();
        $themes = $themes->select($themes->alias($this->fields))->where('uth_deleted_at', null);
        if ($this->where != null) $themes = whereRawQueryBuilder($this->where, $themes, 'mysql', 'user_themes');
        $themes = $themes->orderBy('uth_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'themes' => $themes,

        ];
        return $this->setResponse(200, $data);
    }

    /**
     * GET v2/user-themes/{id}
     *
     * Hiển thị dữ liệu theo id
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-themes
     * `@fields` | List fields user-themes
     * `@html_path_content`\ Với giá trị bằng  'edit': sẽ trả về code của  file html(Yêu cầu `fields` có trường `html_path`).
     * @return \Illuminate\Http\Response
     */
    function show(Request $request, $id)
    {
        $theme = new UserTheme();
        $theme = $theme->select($theme->alias($this->fields))->where('uth_id', $id)->where('uth_deleted_at', null)->first();
        if ($theme) {
            if ($theme->html_path) {
//                    dd(json_decode($theme->html_path));
                $theme->html_path = json_decode($theme->html_path);
                $arr_html = $theme->html_path;
//                    dd($theme->html_path);
                if ($request->input('html_path_content') == 'edit') {
                    foreach ($theme->html_path as $key => $data) {
                        $content = Storage::get($data->path);
                        $content = str_replace("contenteditable=\"false\"", "contenteditable=\"true\"", $content);
                        $content = str_replace('onclick="return true"', 'onclick="return false"', $content);
                        $arr_html[$key]->content_html = $content;
                    }
                }
                $theme->html_path = $arr_html;
                if ($theme->seo_content) {
                    $theme->seo_content = json_decode($theme->seo_content);
                }
            }
            return $this->setResponse(200, $theme);
        }
        return response($this->setResponse(404, null, 'Not found id'), 404);
    }


    /**
     * PUT v2/user-themes/{id}
     *
     * Cập nhật dữ liệu mới
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-themes
     * `@fields` | List fields user-themes
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {
        $theme = UserTheme::find($id);
        if ($theme) {
            if (checkAuthUser($request->input('access_token'), $theme->uth_user_id)) {
                if ($request->input('seo_content')) {
                    $theme->uth_seo_content = json_encode($this->updateDataSeo($theme, $request->input('seo_content')));
                }
                if ($request->input('active') !== null) $theme->uth_active = $request->input('active');
                if ($request->input('html_path') !== null) {
                    foreach ($request->input('html_path') as $data) {
                        if (isset($data['content_html'])) {
                            $data['content_html'] = str_replace("contenteditable=\"true\"", "contenteditable=\"false\"", $data['content_html']);
                            $data['content_html'] = str_replace('onclick="return false"', 'onclick="return true"', $data['content_html']);
                            Storage::put($data['path'], $data['content_html']);
                        }
                    }
                }
                $theme->save();
                return $this->setResponse(200, 'Updated');
            } else {
                return response($this->setResponse(401, null, 'Not auth'), 401);
            }
        }
        return response($this->setResponse(404, 'Not found id'), 404);

    }


    /**
     * POST v2/user-themes
     *
     * Thêm dữ liệu vào database
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        $this->validate($request, [
            'rewrite' => 'required|string|min:5|max:500',
            'theme_id' => 'required|integer',
            'seo_content' => 'string',
        ]);
        $info_user = getInfoToken($request->input('access_token'));
        $theme = Theme::find($request->input('theme_id'));
        if ($theme) {
            $price = (int)$theme->thm_price;
            $money = updateMoneyBuyUserId($info_user->id, -$price);
            if ($money !== false) {
                $ush = createUserSpendHistory($info_user->id, $request->ip(), $request->server('HTTP_USER_AGENT'), -$price, time() . '-buy_template-' . $info_user->id . '-' . $request->input('theme_id'), 'Mua template ' . $theme->thm_id . ' :' . $theme->thm_name, 4);
                $rewrite = trim($request->input('rewrite'));
                $rewrite = str_replace(config('app.slug_user_theme'), '', $rewrite);
                $info_copy_theme = $this->copyTheme($info_user->id, $theme, $rewrite);
                if ($info_copy_theme === false) {
                    updateMoneyBuyUserId($info_user->id, $price);
                    return response($this->setResponse(500, null, 'Lỗi xử lí theme'), 500);
                }
                $user_theme = new UserTheme();
                $user_theme->uth_rewrite = $rewrite;
                $user_theme->uth_thumbnail = $info_copy_theme['thumbnail'];
                $user_theme->uth_html_path = json_encode($info_copy_theme['html_path']);
                $user_theme->uth_rew_md5 = md5($rewrite);
                $user_theme->uth_name = $info_copy_theme['name'];
                $user_theme->uth_created_at = date('Y-m-d H:m:s', time());
                $user_theme->uth_theme_id = $theme->thm_id;
                $user_theme->uth_user_id = $info_user->id;
                $user_theme->uth_seo_content = json_encode($info_copy_theme['seo_content']);
                $user_theme->uth_active = 1;
                $user_theme->save();
                sendMailNotificationTheme::dispatch($user_theme, $ush, $money);
                return $this->setResponse(200, $user_theme);
            } else {
                createUserSpendHistory($request->input('user_id'), $request->ip(), $request->server('HTTP_USER_AGENT'), -$price, time() . '-buy_template-' . $info_user->id . '-' . $request->input('theme_id'), 'Mua template ' . $theme->thm_id . ' :' . $theme->thm_name, 8);
                return response($this->setResponse(500, null, 'Kiểm tra tài khoản của bạn'), 500);
            }
        }
        return response($this->setResponse(404, null, 'Không tìm thấy theme'), 404);
    }

    /**
     * DELETE v2/spend-history/{id}
     *
     * Xóa dữ liệu trong database
     * `@fields` | List fields spend-history
     * @return \Illuminate\Http\Response
     */
    function destroy(Request $request, $id)
    {
        $theme = UserTheme::find($id);
        if ($theme) {
            if (checkAuthUser($request->input('access_token'), $theme->uth_user_id)) {
                $theme->delete();
                return $this->setResponse('Deleted', 200);
            }
        }
        return response($this->setResponse('Not found id', 404), 404);

    }

    /**
     * GET v2/spend-history/check-domain
     *
     *  Kiểm tra slug
     * `@slug` | Kiểm tra slug xem đã tồn tại trên hệ thống chưa
     * @return \Illuminate\Http\Response
     */
    function checkSlug(Request $request)
    {
        if ($request->input('slug') !== null) {
            if($request->input('name_theme')&&$request->input('user_id')){
                $slug = str_slug($request->input('name_theme').'-'.$request->input('user_id').'-'.time());
                $type= 'demo';
            }else{
                $slug = str_slug($request->input('slug'));
                $type= 'check';
            }
            $theme = UserTheme::where('uth_rew_md5', md5($request->input('slug')))->first();
            if($theme===null){
                return $this->setResponse(200, ['status' => true, 'slug' => config('app.slug_user_theme') . $slug,'type'=>$type]);
            }
            return $this->setResponse(200, ['status' => false, 'slug' => config('app.slug_user_theme') . $slug,'type'=>$type]);
        } else {
            return response($this->setResponse(500, null, 'Not found param slug'), 500);
        }
    }

    function copyTheme($user_id, $theme, $rewrite)
    {
        try {
            $value_return = [];
            $value_return['name'] = $theme->thm_name;
            $value_return['thumbnail'] = $theme->thm_thumbnail;
            $path_html = json_decode($theme->thm_html_path);
            if (Storage::has('user_theme/' . $user_id . '/' . $theme->thm_id)) {
                Storage::deleteDirectory('user_theme/' . $user_id . '/' . $theme->thm_id);
            }
            foreach ($path_html as $data) {
                $path_html_copy = 'user_theme/' . $user_id . '/' . $theme->thm_id . '/' . $data->name;
                Storage::copy($data->path, $path_html_copy);
                $content_html = Storage::get($path_html_copy);
                $content_html = str_replace('api_add_user_customer', config('app.api_domain') . '/api/v2/user-customer/add-user-form-template', $content_html);
                $content_html = $this->replaceTagA($content_html, $path_html, $rewrite);
                $value_replace_html = $this->replaceSeoContent($content_html,$rewrite);
                $content_html = $value_replace_html['content'];
                Storage::put($path_html_copy, $content_html);
                $data->path = $path_html_copy;
                $data->url = config('app.source_path') . '/' . $path_html_copy;
                $value_return['seo_content'][$path_html_copy] =$value_replace_html['seo_content'];
                $value_return['html_path'][] = $data;
            }
            return $value_return;
        } catch (\Exception $error) {
            return false;
        }
    }

    function replaceTagA($content, $list_files_html, $rewrite)
    {
        $list_files_html = collect($list_files_html);
        preg_match_all($this->regex_tag_link_html['regex'], $content, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $link) {
            preg_match_all($this->regex_tag_link_html['regex_att_value'], $link[0], $matches_2, PREG_SET_ORDER, 0);
            if(isset($matches_2[0][4])){
                $att_href = $matches_2[0][0];
                $value_href = $matches_2[0][4];
            }else if(isset($matches_2[0][2])){
                $att_href = $matches_2[0][0];
                $value_href = $matches_2[0][2];
            }else{
                continue;
            }
            if ( strpos($value_href, 'http') === false && $value_href != '#' && strpos($value_href, '.html') !== false) {
                $info_file = filterFileName($value_href);
                $url_public = $list_files_html->where('name', $info_file['name'])->first();
                if ($url_public) {
                    $href_replace = str_replace($info_file['name'], config('app.slug_user_theme') . $rewrite . '/' . $info_file['name'], $value_href);
                    $content = str_replace($att_href, 'href="' . $href_replace . '"', $content);
                }
            }

        }
        return $content;
    }

    function replaceSeoContent($content, $rewrite)
    {
        $value_filter = null;
        preg_match_all($this->regex_meta_tag, $content, $matches, PREG_SET_ORDER, 0);
        $arr_meta = [];
        preg_match_all($this->regex_title_tag, $content, $matches_title, PREG_SET_ORDER, 0);
        foreach ($matches as $data) {
            if (strpos($data[0], 'charset') !== false) continue;
            preg_match_all('/name\s*=\s*"(.+?)"/', $data[0], $matches_2, PREG_SET_ORDER, 0);
            if (isset($matches_2[0]) && $matches_2[0][1] == 'viewport') continue;
            if (count($matches_2) == 0) {
                preg_match_all('/property\s*=\s*"(.+?)"/', $data[0], $matches_2, PREG_SET_ORDER, 0);
                if (count($matches_2) == 0) continue;
            }
            if (isset($matches_2[0])) {
                if (in_array($matches_2[0][1], $this->meta_attr_tag)) {
                    preg_match_all('/content\s*=\s*"(.+?)"/', $data[0], $matches_3, PREG_SET_ORDER, 0);

                    if (isset($matches_3[0])) $arr_meta[$matches_2[0][1]] = ['content' => $matches_3[0][1], 'meta_tag' => $data[0]];
                } else {
                    continue;
                }
            }
        }
        if (isset($matches_title[0][1])) {
            $title = ['content' => $matches_title[0][0], 'value' => $matches_title[0][1]];
        } else {
            $title = ['content' => '<title></title>', 'value' => ''];
        }
        $value_filter = ['meta' => $arr_meta, 'title' => $title];
        $meta_config = config('app.seo_theme_config');
        $meta_config['og:url'] = config('app.slug_user_theme') . $rewrite;
        foreach ($value_filter['meta'] as $name_attribute => $value) {
            if (array_key_exists($name_attribute, $meta_config)) {
                $meta_tag_new = str_replace($value['content'], $meta_config[$name_attribute], $value['meta_tag']);
                $content = str_replace($value['meta_tag'], $meta_tag_new, $content);
                $value_filter['meta'][$name_attribute] = ['content' => $meta_config[$name_attribute], 'meta_tag' => $meta_tag_new];
            }
        }
        return ['seo_content'=>$value_filter,'content'=>$content];
    }

//    function filterSeoContent($html_path)
//    {
//        $meta_attr_tag = ['keywords', 'description', 'og:title', 'og:description', 'og:image', 'title', 'og:url'];
//        $regex_meta_tag = '/<meta([\w\W]+?)>/';
//        $value_filter = [];
//        foreach ($html_path as $html) {
//            $content = Storage::get($html->path);
//            preg_match_all($regex_meta_tag, $content, $matches, PREG_SET_ORDER, 0);
//            $arr_meta = [];
//            preg_match_all('/<title>(.*?)<\/title>/m', $content, $matches_title, PREG_SET_ORDER, 0);
//            foreach ($matches as $data) {
//                if (strpos($data[0], 'charset') !== false) continue;
//                preg_match_all('/name\s*=\s*"(.+?)"/', $data[0], $matches_2, PREG_SET_ORDER, 0);
//                if (isset($matches_2[0]) && $matches_2[0][1] == 'viewport') continue;
//                if (count($matches_2) == 0) {
//                    preg_match_all('/property\s*=\s*"(.+?)"/', $data[0], $matches_2, PREG_SET_ORDER, 0);
//                    if (count($matches_2) == 0) continue;
//                }
//                if (isset($matches_2[0])) {
//                    if (in_array($matches_2[0][1], $meta_attr_tag)) {
//                        preg_match_all('/content\s*=\s*"(.+?)"/', $data[0], $matches_3, PREG_SET_ORDER, 0);
//
//                        if (isset($matches_3[0])) $arr_meta[$matches_2[0][1]] = ['content' => $matches_3[0][1], 'meta_tag' => $data[0]];
//                    } else {
//                        continue;
//                    }
//                }
//            }
//            if (isset($matches_title[0][1])) {
//                $title = ['content' => $matches_title[0][0], 'value' => $matches_title[0][1]];
//            } else {
//                $title = ['content' => '<title></title>', 'value' => ''];
//            }
//            $value_filter[$html->path] = ['meta' => $arr_meta, 'title' => $title];
//
//        }
//        return $value_filter;
//    }

    function updateDataSeo($theme, $seo_content)
    {
        $seo_old = json_decode($theme->uth_seo_content);
        $data_update = [];
        $title_update = [];
        foreach ($seo_content as $file => $seo_info) {
            foreach ($seo_info['meta'] as $attribute => $meta_tag) {
                $meta_old = $seo_content[$file]['meta'][$attribute]['meta_tag'];
                $value_old = $seo_old->{$file}->meta->{$attribute}->content;
                if ($value_old != $meta_tag['content']) {
                    $meta_update = str_replace($value_old, $meta_tag['content'], $meta_old);
                    $seo_content[$file]['meta'][$attribute]['meta_tag'] = $meta_update;
                    $data_update[$file][] = ['meta_old' => $meta_old, 'meta_update' => $meta_update];
                    if ($attribute == 'og:title') {
                        $title_update[$file]['title_old'] = $seo_old->{$file}->title->content;
                        $title_update[$file]['title_update'] = str_replace($seo_old->{$file}->title->value, $meta_tag['content'], $seo_old->{$file}->title->content);
                        $seo_content[$file]['title'] = ['content' => $title_update[$file]['title_update'], 'value' => $meta_tag['content']];
                    }
                }

            }
        }

        foreach ($data_update as $file => $data) {
            $file_content = Storage::get($file);
            foreach ($data as $meta) {
                $file_content = str_replace($meta['meta_old'], $meta['meta_update'], $file_content);
            }
            if (isset($title_update[$file])) {
                $file_content = str_replace($title_update[$file]['title_old'], $title_update[$file]['title_update'], $file_content);
            }
            Storage::put($file, $file_content);
        }
        return $seo_content;
    }
}
