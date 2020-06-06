<?php

namespace App\Http\Controllers\Api\V2;

use App\Jobs\sendMailNotificationToAdmin;
use App\Models\BaoKimNotifyV4;
use App\Models\BaoKimPaymentNotification;
use App\Models\MomoNotify;
use App\Models\Money;
use App\Models\UserSpendHistory;
use Illuminate\Http\Request;
use App\Jobs\sendMailBaoKimNotification;
use DB;

/**
 * @resource v2 Baokim_payment_notification
 *
 * Api for Baokim_payment_notification
 */
            

class BaoKimPaymentNotificationController  extends  Controller
{
    //

    
     /**
     * GET v2/baokim_payment_notification
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | BaoKimPaymentNotificationController
     * `route_name` | baokim_payment_notification
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu baokim_payment_notification ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong baokim_payment_notification ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi baokim_payment_notification cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang baokim_payment_notification cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.* `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api?where=id+1223,title+ads-asd-fasdf)
      * `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api?where=id+1223,title+ads-asd-fasdf)
     * @return \Illuminate\Http\Response
     */
    function index(Request $request){
        $this->validate($request, [
            'type' => 'string',
            'fiedls' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
        ]);
        if($request->input('type')=='check_sum_money'){
            $date_start  = $request->input('date_start')?$request->input('date_start'):date('Y-m-01 00:00:00');
            $date_end = $request->input('date_end')?$request->input('date_start'):date('Y-m-d 23:59:59');
            $total_money = BaoKimPaymentNotification::select(DB::raw('SUM(bkp_order_amount) as total'))->whereBetween('bkp_created_at',[$date_start,$date_end])->where('bkp_transaction_status',4)->first();
            $money_momo = MomoNotify::select(DB::raw('SUM(amount) as total'))->whereBetween('created_at',[$date_start,$date_end])->where('errorCode',0)->first();
            $money_bk_v4 = BaoKimNotifyV4::select(DB::raw('SUM(ord_total_amount) as total'))->whereBetween('txn_created_at',[$date_start,$date_end])->where('txn_stat',4)->first();
            $total = $total_money->total + $money_bk_v4->total + $money_momo->total;
            return $this->setResponse(200,['date_start'=>$date_start,'date_end'=>$date_end,'total'=>$total]);
        }
        $bk_payment_notification = new BaoKimPaymentNotification();
        $offset = $this->page * $this->limit - $this->limit;
        $bk_payment_notification = $bk_payment_notification->select($bk_payment_notification->alias($this->fields));
        if ($this->where != null) $bk_payment_notification = whereRawQueryBuilder($this->where, $bk_payment_notification, 'mysql', 'baokim_payment_notification');
        $bk_payment_notification = $bk_payment_notification->orderBy('bkp_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'classifieds' => $bk_payment_notification
        ];
        return $this->setResponse(200, $data);


    }
        
     
     /**
     * GET v2/baokim_payment_notification/{id}
     *
     * Lấy thông báo theo id
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID baokim_payment_notification
     * `@fields` | List fields baokim_payment_notification
     * @return \Illuminate\Http\Response
     */
    function show($id){
        $bk_payment_notification = new BaoKimPaymentNotification();
        $bk_payment_notification = $bk_payment_notification->select($bk_payment_notification->alias($this->fields))->where('bkp_id',$id)->first();
        if($bk_payment_notification){
            return $this->setResponse(200,$bk_payment_notification);
        }
        return $this->setResponse(404,'Not found data');
    }
        
    
     /**
     * POST v2/baokim_payment_notification
     *
     * Lưu thông báo từ ví điện tử  bảo kim trả về
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID baokim_payment_notification
     * `@fields` | List fields baokim_payment_notification
     * @return \Illuminate\Http\Response
     */
    function store(Request $request){
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
//        $baokim = new BaoKimPayment();
        $check_bk_payment = $this->verifyData($request->all());
//        $check_bk_payment = true;
        @file_put_contents(storage_path('logs/bao_kim.log'),date('d-m-Y H:i:s').'('.($check_bk_payment?'Verify':'Verify fail').')'.':'.json_encode($request->all(),JSON_UNESCAPED_UNICODE,JSON_PRETTY_PRINT) . "\n",FILE_APPEND);
        //check_duplicate_notification_bao_kim
        $status_duplicate = UserSpendHistory::where('ush_order_id', $request->input('order_id'))->where('ush_status',4)->first();
        if($status_duplicate){
            $status_duplicate = true;
            @file_put_contents(storage_path('logs/bao_kim.log'),date('d-m-Y H:i:s').'(Verify DUPLICATE)'.':'.json_encode($request->all(),JSON_UNESCAPED_UNICODE,JSON_PRETTY_PRINT) . "\n",FILE_APPEND);
        }else{
            $status_duplicate = false;
        }
        if($check_bk_payment){
            $status_update_ush = updateStatusUserSpendHistory(null,$request->input('order_id'),(int)$request->input('transaction_status'));

        }else{
            $status_update_ush = updateStatusUserSpendHistory(null,$request->input('order_id'),16);
        }
        $bk_payment_notification = new BaoKimPaymentNotification();
        $bk_payment_notification = $this->setValueModel($request->all(),$bk_payment_notification);
        if($bk_payment_notification->save()&&$status_duplicate===false&&$status_update_ush!==false){
            if($check_bk_payment){
                if((int)$request->input('transaction_status')==4||(int)$request->input('transaction_status')==13){
                    if($this->checkSpendHistory($request)==false){
                        return response()->json($this->setResponse(200,'Saved'),200);
                    }
                    $money =  $this->addMoney($request);
                    dispatch(new sendMailBaoKimNotification($request->all(),$money));
//                    sendMailBaoKimNotification::dispatch($request->all(),$money);
                }else{
                    dispatch(new sendMailBaoKimNotification($request->all()));
//                    sendMailBaoKimNotification::dispatch($request->all());
                }
            }
            dispatch(new sendMailNotificationToAdmin($request->all(),$status_update_ush));
//            sendMailNotificationToAdmin::dispatch($request->all(),$status_update_ush);
            return response()->json($this->setResponse(200,'Saved'),200);
        }
        return response()->json($this->setResponse(500,'Save error!'),500);
    }

    function checkSpendHistory($request){
        //Tách user_id từ mã đơn hàng
        $order_id = explode('-add_money-', $request->input('order_id'));
        if (isset($order_id[1])) {
            $user_id = (int) $order_id[1];
            //Tìm kiếm đơn hàng tạm ứng
            $user_spend = UserSpendHistory::where('ush_user_id',$user_id)->where('ush_status',17)->get();
            if($user_spend->count()!=0){
                //Tính tổn số tiền tạm ứng
                $total_money_check = $user_spend->sum('ush_count');
                if($total_money_check>0){
                    $money = Money::where('mon_user_id',$user_id)->first();
                    $money_add = (double)$request->input('order_amount');
                    if($money){
                        //Nếu nạp vào lớn hơn số tiền tạm ứng thì cộng thêm vào tài khoản và cập nhật các đơn hàng tạm ứng về 4
                        if($money_add>=$total_money_check){
                            $money_add = $money_add - $total_money_check;
                            if($money_add!=0){
                                $money->mon_count = (double)$money->mon_count + (double)$money_add;
                                $money->save();
                            }
                            UserSpendHistory::where('ush_user_id',$user_id)->where('ush_status',17)->update(['ush_status'=>4]);
                        }else{
                            //Tìm đơn hàng tạm ứng có số tiền tạm ứng bằng số tiền nạp vào thì cập nhật về 4
                            $user_spend_exist = $user_spend->where('ush_count',$money_add)->first();
                            if($user_spend_exist){
                                $user_spend_exist->ush_status = 4;
                                $user_spend_exist->save();
                            }else{
                                $money_tmp = $money_add;
                                foreach($user_spend as $item){
                                    //Tìm đơn hàng tạm ứng có số tiền lớn hơn số tiền nạp vào, cập nhật số tiền còn tạm ứng của đơn hàng đó
                                    if($item->ush_count>$money_tmp&&$money_tmp>0){
                                        $item->ush_count = $item->ush_count - $money_tmp;
                                        $item->save();
                                        break;
                                        //Tìm đơn hàng tạm ứng có số tiền nhỏ hơn số tiền nạp vào, cập nhật số tiền còn tạm ứng của đơn hàng đó
                                    }else if($item->ush_count<$money_tmp&&$money_tmp>0){
                                        $money_tmp = $money_tmp - $item->ush_count;
                                        $item->ush_status = 4;
                                        $item->save();
                                    }
                                }
                            }
                        }
                    }
                    return false;
                }
            }
            return true;
        }
        return true;
    }

    function addMoney($request){
        $order_id = explode('-add_money-', $request->input('order_id'));
        if(isset($order_id[1])){
            $money = Money::where('mon_user_id',$order_id[1])->first();
            $money_add = (double)$request->input('order_amount');
            if($money){
                $money->mon_count = (double)$money->mon_count + (double)$money_add;
                $money->save();
            }else{
                $money = new Money();
                $money->mon_user_id = $order_id[1];
                $money->mon_count = (double)$money_add;
                $money->save();
            }
            return $money;
        }
    }

    function setValueModel($request,$model){
        unset($request['access_token']);
        foreach ($request as $key=>$data){
         if(in_array($key,$model->fillable)){
                $model->{'bkp_'.$key} = $data;
            }
        }
        return $model;
    }

    function verifyData($data){
        $strBPNMessage = "";
        foreach($data as $key => $value){
            $value			= urlencode(stripslashes($value));
            $strBPNMessage .= "&$key=$value";
        }

        // Verify lại lần nữa xem có đúng tin nhắn từ Bảo Kim hay không
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, BAOKIM_URL.BAOKIM_API_VERIFY);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $strBPNMessage);
        $result	= curl_exec($ch);
        $status	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error 	= curl_error($ch);
        if($result != "" && strstr($result, 'VERIFIED') && $status == 200){
            return true;
        }
        return false;
    }

}
