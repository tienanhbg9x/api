<?php

namespace App\Http\Controllers\Api\V2;

use App\Jobs\sendMailChangeMoneyUser;
use App\Models\Money;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @resource v2 Money
 *
 * Api for Money
 */
class MoneyController extends Controller
{
    //


    /**
     * GET v2/money
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | MoneyController
     * `route_name` | money
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu money ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong money ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi money cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang money cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api?where=id+1223,title+ads-asd-fasdf)
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'type' => 'string',
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
        ]);
        $money = new Money();
        $offset = $this->page * $this->limit - $this->limit;
        $money = $money->select($money->alias($this->fields));
        if ($this->where != null) $money = whereRawQueryBuilder($this->where, $money, 'mysql', 'money');
        $money = $money->orderBy('mon_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'classifieds' => $money
        ];
        return $this->setResponse(200, $data);
    }


    /**
     * GET v2/money/{id}
     *
     * Lấy dữ liệu theo id
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID money
     * `@fields` | List fields money
     * `@type` | Kiểu lấy dữ liệu
     *
     * ### Tùy chọn với tham số `type`:
     *  Giá trị (Value) | Điều kiện | Mô tả chi tiết
     * --------- | ------- | -------
     * `user_id` |  null | Thay vì theo dữ liệu theo id của bảng `money` thì dữ liệu sẽ lây theo id của người dùng
     *
     * @return \Illuminate\Http\Response
     */
    function show(Request $request,$id)
    {
        $money = new Money();
        $money = $money->select($money->alias($this->fields));
        if($request->input('type')=='user_id'){
            $money = $money->where('mon_user_id',$id);
        }else{
            $money = $money->where('mon_id', $id);
        }
        $money = $money->first();
        if ($money) {
            return $this->setResponse(200, $money);
        }else if($request->input('type')=='user_id'){
            $user = User::find($id);
            if($user){
                $money = new Money();
                $money->mon_user_id = $id;
                $money->mon_count = 0;
                $money->save();
                return $this->setResponse(200, $money);
            }

        }
        return $this->setResponse(404, null,'Not found data');
    }


    /**
     * PUT v2/money/{id}
     *
     * Cập nhật dữ liệu theo `id`
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID money
     * `@fields` | List fields money
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {
        $this->validate($request, [
            'user_id' => 'required|integer',
            'count' => 'required|integer',
            'message' =>'required|string|min:10|max:255',
            'status' => 'required|integer'
        ]);
        $money = Money::where('mon_id',$id)->where('mon_user_id',$request->input('user_id'))->first();
        if(((int)$request->input('count'))<0){
            return response($this->setResponse(500,null,'error'),500);
        }
        $money_old = $money->mon_count;
        $money_add = (int)$request->input('count');
        $money->mon_count = $money_old + $money_add;
        try{
            $user_admin_id = getUserId($request['access_token']);
            $money->save();
            $status = $request->input('status');
            $order_id = time().'-update_money-'. $request->input('user_id').'-'.$user_admin_id;
            $ush = createUserSpendHistory($request->input('user_id'),$request->ip(),$request->server('HTTP_USER_AGENT'),$money_add,$order_id,$request->input('message')."($user_admin_id)",$status);
            $request = $request->all();
            unset($request['access_token']);
            $request['user_admin_id'] = $user_admin_id;
            dispatch(new sendMailChangeMoneyUser($request,$ush->ush_id));
//            sendMailChangeMoneyUser::dispatch($request,$ush->ush_id);
            return $this->setResponse(200,['money'=>$money,'money_old'=>$money_old,'money_change'=>$money_add]);
        }catch (\Exception $error){
            return response()->json($this->setResponse(500,null,$error->getMessage()));
        }
    }

}
