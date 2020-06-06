<?php

namespace App\Http\Controllers\Api\V2;

use App\Jobs\sendMailNotifyFromSystemToUser;
use App\Models\User;
use App\Models\UserNotify;
use Illuminate\Http\Request;

/**
 * @resource v2 User-notify
 *
 * Api for User-notify
 */
class UserNotifyController extends Controller
{
    //


    /**
     * GET v2/user-notify
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | UserNotifyController
     * `route_name` | user-notify
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu user-notify ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong user-notify ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi user-notify cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang user-notify cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
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
            'date_start' => 'string|max:255|min:3',
            'date_end' => 'string|max:255|min:3',
        ]);
        $offset = $this->page * $this->limit - $this->limit;
        $user_notify = new UserNotify();
        $user_notify = $user_notify->select($user_notify->alias($this->fields));
        if ($this->where != null) $user_notify = whereRawQueryBuilder($this->where, $user_notify, 'mysql', 'user_notify');
        $user_notify = $user_notify->orderBy('usn_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $create_user_id = $user_notify->map(function ($item) {
            return $item->create_use_id;
        })->toArray();
        $user_notify = $this->filterUserNotify($user_notify, 'multi');
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'user_notify' => $user_notify
        ];
        return $this->setResponse(200, $data);


    }


    /**
     * POST v2/user-notify
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-notify
     * `@fields` | List fields user-notify
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|min:1|max:255',
            'email' => 'required|email',
            'status' => 'numeric|min:1',
            'content' => 'required|string',
        ]);
        $user_created_id = getUserId($request->input('access_token'));
        $user_notify = new UserNotify();
        $user_notify->usn_subject = trim($request->input('subject'));
        $user_notify->usn_name = trim($request->input('name'));
        $user_notify->usn_use_id = $request->input('use_id');
        $user_notify->usn_create_use_id = $user_created_id;
        $user_notify->usn_email = trim($request->input('email'));
        if ($request->input('email_cc') !== null) {
            $array_email = explode(',', $request->input('email_cc'));
            foreach ($array_email as $key => $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $this->setResponse(403, null, 'Not email:' . $email);
                    break;
                }
            }
            $user_notify->usn_email_cc = trim($request->input('email_cc'));
        }
        if ($request->input('status') != null) {
            $user_notify->usn_status = $request->input('status');
        }

        $user_notify->usn_content = trim($request->input('content'));
        $user_notify->usn_created_at = time();
        $user_notify->save();
        if($user_notify->usn_status!=2){
            $data_job = [
                'id' => $user_notify->usn_id,
                'user_email' => $user_notify->usn_email,
                'user_name' => $user_notify->usn_name,
                'subject' => $user_notify->usn_subject,
                'body' => $user_notify->usn_content
            ];
            if ($user_notify->usn_email_cc != null) {
                $data_job['email_cc'] = explode(',', $user_notify->usn_email_cc);
            }
            sendMailNotifyFromSystemToUser::dispatch($data_job);
        }
        return $this->setResponse(200, $user_notify);
    }


    /**
     * PUT v2/user-notify
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-notify
     * `@fields` | List fields user-notify
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {
        $user_created_id = getUserId($request->input('access_token'));
        $user_notify = UserNotify::find($id);
        $user_notify->usn_subject = trim($request->input('subject'));
        $user_notify->usn_name = trim($request->input('name'));
        $user_notify->usn_use_id = $request->input('use_id');
        $user_notify->usn_create_use_id = $user_created_id;
        $user_notify->usn_email = trim($request->input('email'));
        if ($request->input('email_cc') !== null) {
            $array_email = explode(',', $request->input('email_cc'));
            foreach ($array_email as $key => $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $this->setResponse(403, null, 'Not email:' . $email);
                    break;
                }
            }
            $user_notify->usn_email_cc = trim($request->input('email_cc'));
        }
        if ($request->input('status') !== null) {
            $user_notify->usn_status = $request->input('status');
        }

        $user_notify->usn_content = trim($request->input('content'));
        $user_notify->usn_created_at = time();
        $user_notify->save();
        if($user_notify->usn_status!=2){
            $data_job = [
                'id' => $user_notify->usn_id,
                'user_email' => $user_notify->usn_email,
                'user_name' => $user_notify->usn_name,
                'subject' => $user_notify->usn_subject,
                'body' => $user_notify->usn_content
            ];
            if ($user_notify->usn_email_cc != null) {
                $data_job['email_cc'] = explode(',', $user_notify->usn_email_cc);
            }
            sendMailNotifyFromSystemToUser::dispatch($data_job);
        }
        return $this->setResponse(200, $user_notify);
    }

    /**
     * GET v2/user-notify/{id}
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-notify
     * `@fields` | List fields user-notify
     * @return \Illuminate\Http\Response
     */
    function show(Request $request, $id)
    {
        $user_notify = new UserNotify();
        $user_notify = $user_notify->select($user_notify->alias($this->fields))->where('usn_id', $id)->first();
        if ($user_notify) {
            $user_notify = $this->filterUserNotify($user_notify);
            return $this->setResponse(200, $user_notify);
        }
        return response('ID not found', 404);
    }

    function filterUserNotify($notify, $type = 'single')
    {
        if ($type == 'single') {
            if ($notify->created_at) {
                $notify->created_at = date('d-m-Y h:i:s', $notify->created_at);
            }
        } else {
            if (isset($notify[0]) && isset($notify[0]->create_use_id)) {
                $create_user_id = $notify->groupBy('create_use_id')->keys()->toArray();
                $list_user = User::select('use_id', 'use_email', 'use_email_payment')->whereIn('use_id', $create_user_id)->get()->groupBy('use_id')->toArray();
            }
            if (!empty($list_user)) {
                $notify = $notify->map(function ($item) use ($list_user) {
                    $item->create_use_id = $list_user[$item->create_use_id][0];
                    $item->created_at = date('d-m-Y h:i:s', $item->created_at);
                    return $item;
                });
            } else {
                $notify = $notify->map(function ($item) {
                    if ($item->created_at) {
                        $item->created_at = date('d-m-Y h:i:s', $item->created_at);
                    }
                    return $item;
                });
            }

        }
        return $notify;
    }

}
