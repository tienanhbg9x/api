<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\UserSpendHistory;
use Illuminate\Http\Request;

/**
 * @resource v2 Spend-history
 *
 * Api for Spend-history
 */
class UserSpendHistoryController extends Controller
{
    //


    /**
     * GET v2/spend-history
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | UserSpendHistoryController
     * `route_name` | spend-history
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu spend-history ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong spend-history ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi spend-history cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang spend-history cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api?where=id+1223,title+ads-asd-fasdf)
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'type' => 'string',
            'fiedls' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
        ]);
        $spend_history = new UserSpendHistory();
        $offset = $this->page * $this->limit - $this->limit;
        $spend_history = $spend_history->select($spend_history->alias($this->fields));
        if ($this->where != null) $spend_history = whereRawQueryBuilder($this->where, $spend_history, 'mysql', 'user_spend_history');
        $spend_history = $spend_history->orderBy('ush_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $spend_history = $this->convertStatus($spend_history);
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'user_spend_history' => $spend_history
        ];
        return $this->setResponse(200, $data);

    }


    /**
     * GET v2/spend-history/{id}
     *
     * Lấy dữ liệu theo `id`
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID spend-history
     * `@fields` | List fields spend-history
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {
        $spend_history = new UserSpendHistory();
        $spend_history = $spend_history->select($spend_history->alias($this->fields))->where('ush_id', $id)->first();
        if ($spend_history) {
            $spend_history->status = isset(config('baokim_config.bk_status')[$spend_history->status])?config('baokim_config.bk_status')[$spend_history->status]:'Trạng thái không xác định';
            return $this->setResponse(200, $spend_history);
        }
        return $this->setResponse(404, 'Not found data');
    }


//    /**
//     * PUT v2/spend-history/{id}
//     *
//     * Cập nhật dữ liệu
//     * ### Thông số lấy dữ liệu:
//     * Trường dữ liệu (Param) | Mô tả chi tiết
//     * --------- | -------
//     * `@id` | ID spend-history
//     * `@fields` | List fields spend-history
//     * @return \Illuminate\Http\Response
//     */
//    function update($id, Request $request)
//    {
//        $this->validate($request, [
//            'user_id' => 'required|integer',
//            'count' => 'required|integer',
//            'content' => 'required',
//            'ip' => 'required',
//            'user_agent' => 'required',
//        ]);
//        if (checkAuthUser($request->input('access_token'), $request->input('user_id'))){
//            $spend_history = UserSpendHistory::where('ush_id',$id)->where('ush_user_id',$request->input('user_id'))->first();
//
//            try{
//                $spend_history->save();
//                return $this->setResponse(200,$spend_history);
//            }catch (\Exception $error){
//                return response()->json($this->setResponse(500,null,$error->getMessage()));
//            }
//        }
//
//    }


    /**
     * POST v2/spend-history
     *
     * Thêm dữ liệu vào database
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID spend-history
     * `@fields` | List fields spend-history
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer',
            'count' => 'required|integer',
            'content' => 'required',
        ]);
        if (checkAuthUser($request->input('access_token'), $request->input('user_id'))) {
            $spend_history = new UserSpendHistory();
            $spend_history->ush_user_id = (int)$request->input('user_id');
            $spend_history->ush_count = (int)$request->input('count');
            $spend_history->ush_message = $request->input('content');
            $spend_history->ush_ip = $request->ip();
            $spend_history->ush_user_agent = $request->server('HTTP_USER_AGENT');
            try {
                $spend_history->save();
                return $this->setResponse(200, $spend_history);
            } catch (\Exception $error) {
                return response()->json($this->setResponse(500, null, $error->getMessage()));
            }
        }

    }

    function convertStatus($history){
        $arr_status = config('app.status_payment');
        foreach ($history as $key=>$data){
            $history[$key]->status = isset($arr_status[$data->status])?$arr_status[$data->status]:'Trạng thái không xác định';
        }
        return $history;
    }

}
