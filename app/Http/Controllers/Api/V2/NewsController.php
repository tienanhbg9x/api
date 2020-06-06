<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\News;

/**
 * @resource v2 News
 *
 * Api for News
 */
class NewsController extends Controller
{
    //


    /**
     * GET v2/news
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | NewsController
     * `route_name` | news
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu news ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong news ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi news cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang news cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api?where=id+1223,title+ads-asd-fasdf)
     * `@ssr` | boolean | Chế độ gọi api cho web .  Mặc định là `false`
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
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
            'ssr' =>'boolean'
        ]);
        $offset = $this->page * $this->limit - $this->limit;
        $news = new News();
        $news = $news->select($news->alias($this->fields));
        if ($this->where != null) $news = whereRawQueryBuilder($this->where, $news, 'mysql', 'news');
        $news = $news->orderBy('new_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $data = [
            'total_record' => 1000,
            'current_page' => $this->page,
            'per_page'=>$this->limit,
            'news' => $news,

        ];
        return $this->setResponse(200,$data);
    }


    /**
     * GET v2/news
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID news
     * `@fields` | List fields news
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {
        $news = new News();
        $news = $news->select($news->alias($this->fields))->where('new_id',$id);
        if ($this->where != null) $news = whereRawQueryBuilder($this->where, $news, 'mysql', 'news');
        $news = $news->first();
        if($news){
            $meta = [];
            $meta['title'] =$news->meta_title;
            $title = $meta['title'];
            $meta['og:title'] = $news->meta_title;
            $meta['description'] = $news->meta_description;
            $meta['og:description'] =$news->meta_description;
            $meta['revisit-after'] = "1 days";
            $meta['og:url'] = env('APP_DOMAIN') . '/'.$news->rewrite;
            $meta['DC.language'] = "scheme=utf-8 content=vi";
            $meta['robots'] = "index, follow";
            $meta['og:image'] = $news->picture;
            $meta['og:image:width'] = '400px';
            $meta['og:image:height'] = '400px';
            $meta['og:image:type'] = 'png';
            $meta = createMeta($meta);
            $data = [
                'meta' => $meta,
                'title' => $news->title,
                'news' => $news,

            ];
            return $this->setResponse(200,$data);

        }
        return $this->setResponse(404,null,"Not found data!!!");

    }

}