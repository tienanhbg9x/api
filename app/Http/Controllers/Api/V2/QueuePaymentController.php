<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\QueuePayment;
use Illuminate\Http\Request;

/**
 * @resource v2 Queue-payment
 *
 * Api for Queue-payment
 */
class QueuePaymentController extends Controller
{
    //


    /**
     * GET v2/queue-payment
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | QueuePaymentController
     * `route_name` | queue-payment
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu queue-payment ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong queue-payment ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi queue-payment cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang queue-payment cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
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
            'where' => 'string|max:255|min:3'
        ]);
        $queue_payment = new QueuePayment();
        $offset = $this->page * $this->limit - $this->limit;
        $user_id = getUserId($request->input('access_token'));
        $queue_payment = $queue_payment->select($queue_payment->alias($this->fields))->where('qpm_user_id', $user_id);
        if ($this->where != null) $queue_payment = whereRawQueryBuilder($this->where, $queue_payment, 'mysql', 'queue_payment');
        $queue_payment = $queue_payment->orderBy('qpm_id', 'desc')->offset($offset)->limit($this->limit)->get();
        if (isset($queue_payment[0]) && $queue_payment[0]->created_at) {
            foreach ($queue_payment as $key => $value) {
                $queue_payment[$key]->created_at = date("d/m/Y H:i:s", $value->created_at);
            }
        }
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'data' => $queue_payment
        ];
        return $this->setResponse(200, $data);
    }


    /**
     * GET v2/queue-payment/{id}
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID queue-payment
     * `@fields` | List fields queue-payment
     * @return \Illuminate\Http\Response
     */
    function show(Request $request, $id)
    {
        $user_id = getUserId($request->input('access_token'));
        $queue_payment = new QueuePayment();
        $queue_payment = $queue_payment->select($queue_payment->alias($this->fields))->where('qpm_user_id', $user_id)->where('qpm_id', $id)->first();
        if ($queue_payment) {
            return $this->setResponse(200, $queue_payment);
        }
        return response($this->setResponse(404, null, 'Not found id'), 404);
    }


    /**
     * DELETE v2/queue-payment/{id}
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID queue-payment
     * `@fields` | List fields queue-payment
     * @return \Illuminate\Http\Response
     */
    function destroy(Request $request, $id)
    {
        $user_id = getUserId($request->input('access_token'));
        $status_delete = QueuePayment::where('qpm_id', $id)->where('qpm_user_id', $user_id)->delete();
        if ($status_delete) {
            return $this->setResponse(200, 'Deleted');
        }
        return response($this->setResponse(404, null, 'Not found id'), 404);
    }

    /**
     * POST v2/queue-payment
     *
     * Thêm dữ liệu vào db
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID users
     * `@fields` | List fields users
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|min:1|max:255',
            'url' => 'required|string|min:1|max:255',
            'type' => 'required|string|min:1|max:20'
        ]);
        $url = trim($request->input('url'));
        $user_id = getUserId($request->input('access_token'));
        $check_queue = $this->checkStatusQueue($user_id, $url);
        if ($check_queue !== false) {
            return $this->setResponse(200, $check_queue);
        }
        $queue_payment = new QueuePayment();
        $queue_payment->qpm_name = $request->input('name');
        $queue_payment->qpm_url = $url;
        $queue_payment->qpm_md5_url = md5($url);
        $queue_payment->qpm_user_id = $user_id;
        $queue_payment->qpm_type = trim($request->input('type'));
        $queue_payment->qpm_created_at = time();
        if ($queue_payment->save()) {
            return $this->setResponse(200, $queue_payment);
        }
        return response($this->setResponse(500, null, 'Save error'), 500);

    }

    function checkStatusQueue($user_id, $url)
    {
        $queue_theme = QueuePayment::where('qpm_user_id', $user_id)->where('qpm_md5_url', md5($url))->first();
        if ($queue_theme) {
            return $queue_theme;
        }
        return false;
    }


}
