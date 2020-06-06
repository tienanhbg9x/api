<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Investor;
use Illuminate\Http\Request;

/**
 * @resource v2 Investor
 *
 * Api for Investor
 */
class InvestorController extends Controller
{
    //


    /**
     * GET v2/investors
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@limit` | 30 |  Giới hạn bảng ghi trả về
     * `@page` | 1 | Phân trang hiện tại
     * `@fields` | all | Trường dữ liệu muốn lấy (id,name,...)
     * `@type` | null | `type=detail` (trả về thông tin chi tiết của một `investor`) với điều kiện có tham số `slug` đi kèm
     * `@slug` | null | Giá trị của trường `rewrite` của `investor`. Để  sử dụng được bắt buộc phải có `type=detail` đi kèm theo.
     * `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api/investors?where=id+1223,active+1)
     * `@ssr` | boolean | Chế độ gọi api cho web .  Mặc định là `false`
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
        if (isset($_REQUEST['type'])) {
            if ($_REQUEST['type'] == 'detail') {
                if (isset($_REQUEST['slug'])) {
                    return $this->getInvestor($_REQUEST['slug']);
                } else {
                    return $this->setResponse(404, null, 'Not found param `slug`');
                }
            }
        }
        $investor = new Investor();
        $offset = $this->page * $this->limit - $this->limit;
        $investor = $investor->select($investor->alias($this->fields))->whereNotIn('inv_rewrite',['','null']);
        if ($this->where != null) $investor = whereRawQueryBuilder($this->where,$investor,'mysql','investors');
        $investor = $investor->orderBy('inv_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $investor = $this->filterInvestor($investor);
        return $this->setResponse(200, $investor);
    }

    /**
     * GET v2/investors/{id}
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID investors
     * `@fields` | List fields investors
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {
        $investor = new Investor();
        $investor = $investor->select($investor->alias($this->fields))->where('inv_id',$id)->first();
        if($investor){
            $investor =  $this->getPictureInvestor($investor);
            return $this->setResponse(200,$investor);
        }
        return $this->setResponse(404,null,'Not found `slug`!');
    }


    function filterInvestor($investor){
        $investor = $investor->map(function ($item) {
            return $this->getPictureInvestor($item);
        });
        return $investor;
    }

    function getPictureInvestor($investor){
        if(isset($investor->picture)){
            $investor->picture = $investor->picture != null || $investor->picture != '' ? getUrlPictureInvestor($investor->picture,0,true) : config('app.thumbnail_default');

        }
        return $investor;
    }

    function getInvestor($slug){
        $investor = new Investor();
        $investor = $investor->select($investor->alias($this->fields))->where('inv_rewrite',$slug)->first();
        if($investor){
            $investor =  $this->getPictureInvestor($investor);
            return $this->setResponse(200,$investor);
        }
        return $this->setResponse(404,null,'Not found `slug`!');
    }


}
