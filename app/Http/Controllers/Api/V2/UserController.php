<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use Illuminate\Http\Request;
use DB;
/**
 * @resource v2 Users
 *
 * Api for Users
 */
class UserController extends Controller
{
    //


    /**
     * GET v2/users
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | UserController
     * `route_name` | users
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu users ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong users ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi users cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang users cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3'
        ]);
        if($request->input('type')=='search'){
            if($request->input('key_search')){
                return $this->searchUser($request->input('key_search'));
            }else{
                return responce($this->setResponse(500,null,'Not found param `key_search`'),500);
            }
        }
        $offset = $this->page * $this->limit - $this->limit;
        $users = new User();
        $users = $users->select($users->alias($this->fields));
        if ($this->where != null) $users = whereRawQueryBuilder($this->where, $users, 'mysql', 'users');
        $users = $users->orderBy('use_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'users' => $users,

        ];
        return $this->setResponse(200, $data);
    }

    /**
     * PUT v2/spend-history
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID spend-history
     * `@fields` | List fields spend-history
     * @return \Illuminate\Http\Response
     */
    function update(Request $request,$id)
    {
        $user = User::find($id);
        if($user){
            $info_token = getInfoToken($request->input('access_token'));
            if($info_token->rol=='admin'){
                return $this->updateUserByAdmin($request,$user);
            }
            if(checkAuthUser($request->input('access_token'),$id)){
                return $this->updateUser($request,$user);
            }
            return response($this->setResponse(401,null,'Not auth'),401);
        }else{
            return respomse($this->setResponse(404,null,'Not found user'),404);
        }
    }

    function updateUser($request,$user){
        if($request->input('fullname')){
            $user->use_fullname = $request->input('fullname');
            $user->use_name = $request->input('fullname');
        }
        if($request->input('birthdays'))$user->use_birthdays = strtotime($request->input('birthdays'));
        if($request->input('web'))$user->use_web= $request->input('web');
//        if($request->input('rol')!==null) $user->use_rol = $request->input('rol');
//        if($request->input('active')!==null) $user->use_active = $request->input('active');
        if($request->input('mobile')){
            $user->use_mobile = $request->input('mobile');
            $user->use_phone = $request->input('mobile');
        }
//        if($request->input('email')!==null){
//            $user->use_email = trim($request->input('email'));
//        }
        if($request->input('email_payment')!==null){
            $user->use_email_payment = trim($request->input('email_payment'));
        }
        if($request->input('address')!=null) $user->use_address = trim($request->input('address'));
        if($user->save()){
        return $this->setResponse(200,'Saved');
        }
    }

    function updateUserByAdmin($request,$user){
        if($request->input('fullname')){
            $user->use_fullname = $request->input('fullname');
            $user->use_name = $request->input('fullname');
        }
        if($request->input('birthdays'))$user->use_birthdays = strtotime($request->input('birthdays'));
        if($request->input('web'))$user->use_web= $request->input('web');
        if($request->input('rol')!==null) $user->use_rol = $request->input('rol');
        if($request->input('active')!==null) $user->use_active = $request->input('active');
        if($request->input('mobile')){
            $user->use_mobile = $request->input('mobile');
            $user->use_phone = $request->input('mobile');
        }
        if($request->input('email_payment')!==null){
            $user->use_email_payment = trim($request->input('email_payment'));
        }
        if($request->input('address')!=null) $user->use_address = trim($request->input('address'));
        if($user->save()){
            return $this->setResponse(200,'Saved');
        }
    }


    /**
     * GET v2/users
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID users
     * `@fields` | List fields users
     * @return \Illuminate\Http\Response
     */
    function show(Request $request, $id)
    {
        $user = new User();
        $user = $user->select($user->alias($this->fields))->where('use_id', $id)->first();
        if (!$user) return $this->setResponse(404, null, 'Không tìm thấy tài khoản:' . $id);
        if ($request->input('add_fields') == 'classifieds_info') {
//            $cla_info = DB::table('classifieds')->selectRaw('cla_active,count(*) as count')->where('cla_use_id',$id)->groupBy('cla_active')->get();
//            $active = $cla_info->where('cla_active',1)->first();
//            $not_active =$cla_info->where('cla_active',0)->first();
//            $not_active_2 =$cla_info->where('cla_active',-1)->first();
//            $count_active  =$active!=null?$active->count:0;
//            $count_not_active = $not_active!=null?($not_active->count +($not_active_2!=null?$not_active_2->count:0) ):0;
//            $cla_total = $count_active + $count_not_active;

//            $user->cla_info = ['active'=>$count_active,'not_active'=>$count_not_active,'total'=>$cla_total];
            $user->cla_info = ['active'=>0,'not_active'=>0,'total'=>0];
        }
        if($request->input('type')=='load_config'){
            $config = config('configuration');
            $id = (int) $id;
            $config['access_token_chat'] = createJwtToken(env('CHAT_SOCKET_IO_SECRET_KEY'),json_encode([$id]),3600);
            $user->system_config = $config;
        }
        return $this->setResponse(200, $user);
    }


    /**
     * POST v2/users
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID users
     * `@fields` | List fields users
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {

    }

    function searchUser($keyword){
        $keyword = urldecode($keyword);
        
        $sphinx = app()->make('sphinx');
        $offset = $this->page * $this->limit - $this->limit;
        $query_sphinx = $sphinx->select('id')->from('bds_users');
        if ($this->where != null) $query_sphinx = whereRawQueryBuilder($this->where, $query_sphinx, 'mysql', 'users');
        $query_sphinx->match('use_email', $keyword);
        $query_sphinx->orderBy('id', 'desc');
        $query_sphinx->limit($offset, $this->limit);
        $result = $query_sphinx->execute()->fetchAllAssoc();
        $arr_id = $this->filterIdSphinx($result);
        $info_sphinx_query = collect($sphinx->query('show meta;')->execute()->fetchAllAssoc());
        $total_record_found = $info_sphinx_query->where('Variable_name', 'total_found')->first();
        $total_record_found = isset($total_record_found['Value']) ? $total_record_found['Value'] : 0;
        $users = new User();
        $users = $users->select($users->alias($this->fields))
            ->whereIn('use_id', $arr_id)->orderBy('use_id', 'desc')->get();
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'users' => $users,

        ];
        return $this->setResponse(200, $data);
    }

    function filterIdSphinx($arr_id)
    {
        $arr_id = collect($arr_id)->map(function ($item) {
            return $item['id'];
        });
        return $arr_id;
    }

}
