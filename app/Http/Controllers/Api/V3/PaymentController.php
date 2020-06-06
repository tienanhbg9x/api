<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 21/10/2019
 * Time: 21:18
 */

namespace App\Http\Controllers\Api\V3;


use App\Helper\BaoKimAPI;
use App\Jobs\sendMailBaoKimV4;
use App\Jobs\sendMailNotificationToAdmin;
use App\Models\BaoKimNotifyV4;
use App\Models\UserSpendHistory;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{

    function createOrderBaoKim(Request $request)
    {
        $this->validate($request,   [
            'access_token' => 'required',
            'total_amount' => 'required|numeric',
            'description' => 'required|string|min:10|max:100',
            'bpm_id' => 'required|numeric',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'customer_address' => 'required|string|min:5|max:100',
            'customer_name' => 'required|string|min:5|max:50'
        ]);

        $user_id = getUserId($request->input('access_token'));
        $order_id = time() . 'AM' . $user_id;
        $ush = createUserSpendHistory($user_id, $request->ip(), $request->server('HTTP_USER_AGENT'), $request->input('total_amount'), $order_id, $request->input('description'), 0,2);
        $order_id = $order_id . 'USH' . $ush->ush_id;
        $ush->ush_order_id = $order_id;
        $ush->save();
        $client = new Client(['timeout' => 30.0]);
        $options['query']['jwt'] = BaoKimAPI::getToken();
        $payload['mrc_order_id'] = $order_id;
        $payload['total_amount'] = (int)$request->input('total_amount');
        $payload['description'] = $request->input('description');
        $payload['url_success'] = 'https://sosanhnha.com/quanly/user/profile';
        $payload['url_detail'] = 'https://sosanhnha.com/quanly/user/profile';
        $payload['lang'] = "vi";
        $payload['bpm_id'] = (int)$request->input('bpm_id');
        $payload['accept_bank'] = 1;
        $payload['accept_cc'] = 1;
        $payload['accept_qrpay(0,1)'] = 1;
        $payload['webhooks'] = 'https://sosanhnha.com/api/v3/payment/bao-kim/webhook-notification';
        $payload['customer_email'] = $request->input('customer_email');
        $payload['customer_phone'] = $request->input('customer_phone');
        $payload['customer_name'] = $request->input('customer_name');
        $payload['customer_address'] = $request->input('customer_address');
        $options['form_params'] = $payload;
        try{
        $response = $client->request("POST", BAOKIM_V4_URL . "order/send", $options);
        if ($response->getStatusCode() == 200) {
            $response =  $response->getBody()->getContents();
            $data_bk =  json_decode(remove_utf8_bom($response),1);
            $order_detail = [
              'payment_for'=>'CTCP TMTD BAO KIM',
              'url_success' => $payload['url_success'],
                'order_id'=>$order_id,
                'fee_amount'=>$payload['total_amount']*0.01,
                'total_amount'=> $payload['total_amount'],
                'ush_id'=>$ush->ush_id
            ];
            return $this->createResponse(['order_detail'=>$order_detail,'bk_response'=>$data_bk]);
        }
        sendNotifySlack(['message'=>'Create qr code error!'],SLACK_PAYMENT);
        return $this->createResponse('Send order fail!', 500);
        }catch (\Exception $error){
            sendNotifySlack(['message'=>'Create qr code error!','message_error'=>$error->getMessage()],SLACK_PAYMENT);
            return $this->createResponse("Request error:".$error->getMessage(), 500);
        }
    }


    function getPaymentMethodBaoKim()
    {
        if (Cache::has('bkv4pm_list')) {
            return Cache::get('bkv4pm_list');
        } else {
            $client = new Client(['timeout' => 20.0]);
            $options['query']['jwt'] = BaoKimAPI::getToken();
            $response = $client->request("GET", "https://api.baokim.vn/payment/api/v4/bpm/list", $options);
            if ($response->getStatusCode() == 200) {
                $content = $response->getBody()->getContents();
                Cache::rememberForever('bkv4pm_list', function () use ($content) {
                    return $content;
                });
                return $content;
            }
            return $this->createResponse('get list fail!', 500);
        }
    }

    function webhookNotificationBaoKim(Request $request)
    {
        try {
            $request = $request->all();
//        sendNotifySlack($request,'https://hooks.slack.com/services/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');
            if (!isset($request['sign']) || !isset($request['order']) || !isset($request['txn'])) {
                sendNotifySlack(['bk_v3' => 'request ko hop le'], SLACK_PAYMENT);
                return ["err_code"=> 0, "message"=> 'Request Không hợp lệ'];
            }
            $sign = $request['sign'];
            $order = $request['order'];
            $txn = $request['txn'];
            $dataBaoKim = $request;
            unset($dataBaoKim['sign']);
            $signData = json_encode($dataBaoKim);
            $mySign = hash_hmac('sha256', $signData, env('BAOKIM_V4_SECRET_KEY', ''));
            $bkp_v4 = new BaoKimNotifyV4();
            $order_id = $order['mrc_order_id'];
            $order_id_array = explode('AM', $order_id);
            if (!isset($order_id_array[1])) return $this->createResponse('order_id not found', 400);
            $info_order = explode('USH', $order_id_array[1]);
            $user_id = current($info_order);
            $ush_id = end($info_order);
            $user_spend_history = UserSpendHistory::where('ush_id', $ush_id)->where('ush_order_id', $order_id)->where('ush_status', '!=', 4)->first();
            if (empty($user_spend_history)) {
                $this->appendLog($request, false);
//                sendNotifySlack(['bk_v3' => 'Không tìm thấy đơn hàng', "order_id" => $order_id], 'https://hooks.slack.com/services/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');
                return ["err_code"=> 0, "message"=> 'Không tìm thấy đơn hàng trên hệ thống'];
            } else if ($user_spend_history->ush_status == 4) {
                $this->appendLog($request, false);
//                sendNotifySlack(['bk_v3' => 'Đơn hàng đã được xử lý', "order_id" => $order_id], 'https://hooks.slack.com/services/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');
                return ["err_code"=> 0, "message"=> 'Đơn hàng đã được xử lý'];
            }
            if ($sign == $mySign && $user_spend_history != null) {
                foreach ($order as $key => $value) {
                    $bkp_v4->{"ord_$key"} = $value;
                }
                unset($txn['bank_ref_no']);
                foreach ($txn as $key => $value) {
                    $bkp_v4->{"txn_$key"} = $value;
                }
                if ($order['stat'] == 'c') {
                    $user_spend_history->ush_status = 4;
                    $status_verify = 200;
                    $message_verify = 'ok';
                    updateMoneyBuyUserId($user_id, (float)$order['total_amount']);
                    sendNotifySlack(['total_amount' => $order['total_amount'], 'type' => 'BaoKim_QrCOde', 'order_id' => $order_id], SLACK_PAYMENT);
                } else {
                    $user_spend_history->ush_status = 8;
                    $status_verify = 503;
                    $message_verify = 'error';
                }
            } else {
                $user_spend_history->ush_status = 16;
                $message_verify = 'Sign error!';
                $status_verify = 400;
            }
            $user_spend_history->save();
            dispatch(new sendMailNotificationToAdmin($request, $user_spend_history));
//        sendMailNotificationToAdmin::dispatch($request,$user_spend_history);
            dispatch(new sendMailBaoKimV4($request, $user_spend_history->ush_id));
//        sendMailBaoKimV4::dispatch($request,$user_spend_history->ush_id);
            $this->appendLog($request, $status_verify == 200 ? true : false);
            $bkp_v4->save();
            return ["err_code"=> 0, "message"=> "ok: $message_verify"];
        }catch (\Exception $e){
            sendNotifySlack(['bk_v3' => 'Lỗi hệ thống', "message"=>$e->getMessage(),'line'=>$e->getLine(),'file'=>$e->getFile()], 'https://hooks.slack.com/services/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');
            return ["err_code"=> 0, "message"=> "ok: $message_verify"];
        }
    }

    function appendLog($request,$status){
        @file_put_contents(storage_path('logs/bao_kim_v4.log'), date('d-m-Y H:i:s') . '(' . ($status ? 'Verify' : 'Verify fail') . ')' . ':' . json_encode($request, JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
    }

    function orderIdCheck(Request $request){
        $this->validate($request,   [
            'ush_id'=>'required'
        ]);
        $ush_id = $request->input('ush_id');
        $ush = UserSpendHistory::where('ush_id',$ush_id)->first();
        if($ush){
            if($ush->ush_status==4){
                return $this->createResponse(['ush_id'=>$ush_id,'status'=>1]);
            }else{
                return $this->createResponse(['ush_id'=>$ush_id,'status'=>0]);
            }
        }
        $this->createResponse('Not found ush',404);
    }

}