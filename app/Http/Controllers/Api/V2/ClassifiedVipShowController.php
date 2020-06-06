<?php

namespace App\Http\Controllers\Api\V2;

use App\Jobs\JobSendMailUpdateVip;
use App\Models\Classified;
use App\Models\ClassifiedVipConfig;
use App\Models\ClassifiedVipShow;
use Illuminate\Http\Request;

/**
 * @resource v2 Classified-vip-show
 *
 * Api for Classified-vip-show
 */
class ClassifiedVipShowController extends Controller
{
    //
    /**
     * GET v2/classified-vip-show
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | ClassifiedVipShowController
     * `route_name` | classified-vip-show
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu classified-vip-show ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong classified-vip-show ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi classified-vip-show cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang classified-vip-show cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'where' => 'string|max:255|min:3',
            'date_start' => 'string|max:10',
            'date_end' => 'string|max:10',
            'cvc_id' => 'max:10',
            'permissions' => 'string|max:225',
        ]);
        $cla_vip_show = new ClassifiedVipShow();
        $cla_vip_show = $cla_vip_show->select($cla_vip_show->alias($this->fields));
        if ($this->where != null) $cla_vip_show = whereRawQueryBuilder($this->where, $cla_vip_show, 'mysql', 'classified_vip_show');
        if ($request->input('date_start')) {
            $date_start = strtotime(trim($request->input('date_start')));
            $cla_vip_show = $cla_vip_show->where('cvs_date', '>=', $date_start);
        }
        if ($request->input('date_end')) {
            $date_end = strtotime(trim($request->input('date_end')));
            $cla_vip_show = $cla_vip_show->where('cvs_date', '<=', $date_end);
        }
        $cla_vip_show = $cla_vip_show->get();
        $data = ['classified_vip_show' => $cla_vip_show];
        if ($request->input('where')) {
            if (strpos($request->input('where'), 'cvc_id') !== false) {
                if ($request->input('type') == 'check_show_vip') {
                    $where = $request->input('where');
                    $where = explode(',', $where);
                    foreach ($where as $item) {
                        if (strpos($item, 'cvc_id') !== false) {
                            $cvc_id = explode(' ', $item);
                            if (isset($cvc_id[1])) {
                                $cvc_id = (int)$cvc_id[1];
                                break;
                            }
                            return response($this->setResponse(500, null, 'Not found classified config id'), 500);
                        }
                    }
                    if (!$request->input('classified_id')) {
                        return response($this->setResponse(500, null, 'Not found classified_id'), 500);
                    }
                    $data = $this->createOrderUpdateVip($data['classified_vip_show'], $date_start, $date_end, $cvc_id, (int)$request->input('classified_id'));
                }
            }
        }
        return $this->setResponse(200, $data);
    }


    /**
     * POST v2/classified-vip-show
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classifieds-vip
     * `@fields` | List fields classifieds-vip
     * @return \Illuminate\Http\Response
     */

    function store(Request $request)
    {
        $this->validate($request, [
            'cvc_id' => 'numeric|required',
            'cla_id' => 'numeric|required',
            'option_selected' => 'required',
        ]);
        $classified = Classified::select('cla_id as id','cla_title as title','cla_rewrite as rewrite')->where('cla_id', (int)$request->input('cla_id'))->first();
        if ($classified == null) {
            return response($this->setResponse(500, null, 'Id Classified fail'), 500);
        }
        $cla_vip_config = ClassifiedVipConfig::find($request->input('cvc_id'));
        if ($cla_vip_config == null) {
            return response($this->setResponse(500, null, 'Id ClassifiedVipConfig fail'), 500);
        }
        $total_money = 0;
        $option_selected = (array)$request->input('option_selected');
        $cvs_queue = [];
        foreach ($option_selected as $item) {
            $time = strtotime($item);
            $cvs = ClassifiedVipShow::where('cvs_date', $time)->where('cvs_cvc_id',$cla_vip_config->cvc_id)->first();
            if ($cvs != null && $cvs->cvs_count < $cla_vip_config->cvc_classified_limit) {
                $arr_cla_id = (array)json_decode($cvs->cvs_cla_id);
                if (!in_array($classified->id, $arr_cla_id)) {
                    $arr_cla_id[] = $classified->id;
                    $cvs->cvs_cla_id = json_encode($arr_cla_id);
                    $cvs->cvs_count = count($arr_cla_id);
                    $cvs_queue[] = $cvs;
                    $total_money += (int)$cla_vip_config->cvc_price;
                }
            } else {
                $cvs = new ClassifiedVipShow();
                $cvs->cvs_date = $time;
                $cvs->cvs_cvc_id = $cla_vip_config->cvc_id;
                $cvs->cvs_cla_id = json_encode([$classified->id]);
                $cvs->cvs_count = 1;
                $cvs_queue[] = $cvs;
                $total_money += (int)$cla_vip_config->cvc_price;
            }
        }

        if ($total_money > 0) {
            $user_id = getUserId($request->input('access_token'));
            $money = updateMoneyBuyUserId($user_id, -$total_money);
            $ush = createUserSpendHistory($user_id, $request->ip(), $request->server('HTTP_USER_AGENT'), -$total_money, time() . '-update_vip-' . $request->input('cvc_id'). '-' . $request->input('cla_id'), 'Nâng cấp tin vip ' . $request->input('cla_id'), 4);
            if ($money) {
                JobSendMailUpdateVip::dispatch($user_id,$cla_vip_config,$classified->id,$ush,$money);
//                sendMailUpdateClassifiedVip::dispatch($user_id,$cla_vip_config,$classified,$ush,$money);
                foreach ($cvs_queue as $item) {
                    $item->save();
                }
                return $this->setResponse(200,'Giao dịch thành công!');
            } else {
                createUserSpendHistory($user_id, $request->ip(), $request->server('HTTP_USER_AGENT'), -$total_money, time() . '-update_vip-' . $request->input('cvc_id'). '-' . $request->input('cla_id'), 'Nâng cấp tin vip ' . $request->input('cla_id'), 8);
                return response($this->setResponse(500,null,'Giao dịch thất bại'),500);
            }
        }
        return response($this->setResponse(500,null,'Giao dịch thất bại'),500);

    }

    /**
     * GET v2/classified-vip-show
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classified-vip-show
     * `@fields` | List fields classified-vip-show
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {

    }


    /**
     * PUT v2/classified-vip-show
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classified-vip-show
     * `@fields` | List fields classified-vip-show
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {

    }


    /**
     * DELETE v2/classified-vip-show
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classified-vip-show
     * `@fields` | List fields classified-vip-show
     * @return \Illuminate\Http\Response
     */
    function destroy($id)
    {

    }

    function createOrderUpdateVip($data, $date_start, $date_end, $cvc_id, $classified_id)
    {
        $cla_vip_config = getClassifiedConfig($cvc_id);
        $order = [];
        $day_plus = 0;
        for (; ;) {
            $date_order = date('d-m-Y', strtotime("+$day_plus day", $date_start));
            $check_data = $data->where('date', strtotime("+$day_plus day", $date_start))->first();
            $status = true;
            if ($check_data) {
                if (json_decode($check_data->cla_id) != null && in_array($classified_id, json_decode($check_data->cla_id))) {
                    $status = false;
                }
                $limit = (int)$cla_vip_config['cvc_classified_limit'];
                $available = $limit - (int)$check_data->count;
                if ($available == 0) {
                    $status = false;
                }
                $order[] = ['date' => $date_order, 'limit' => $limit, 'available' => $available, 'status' => $status];
            } else {
                $limit = $cla_vip_config['cvc_classified_limit'];
                $order[] = ['date' => $date_order, 'limit' => $limit, 'available' => $limit, 'status' => $status];
            }
            $day_plus++;
            if ($date_end == strtotime("+$day_plus day", $date_start)) {
                break;
            }
            if ($day_plus > 500) {
                break;
            }
        }
        $classified = Classified::select('cla_id', 'cla_title', 'cla_rewrite')->where('cla_id', $classified_id)->first();
        return ['order' => $order, 'cla_vip_config' => $cla_vip_config, 'classified_vip_show' => $data, 'classified' => $classified];
    }
}
