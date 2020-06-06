<?php

namespace App\Http\Controllers\Api\V2;

use App\Jobs\sendMailBaoKimV4;
use App\Jobs\sendMailNotificationToAdmin;
use App\Jobs\SendMailPaymentToUser;
use App\Jobs\sendMailUserBackPayment;
use App\Models\MomoNotify;
use App\Models\UserSpendHistory;
use BaoKimPayment;
use BaoKimPaymentPro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maknz\Slack\Client;

use Illuminate\Support\Facades\Validator;

/**
 * @resource v2 Payment
 *
 * Api for Payment
 */
class PaymentController extends Controller
{
    //


    /**
     * GET v2/payment/request
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | PaymentController
     * `route_name` | payment
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu payment ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong payment ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi payment cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang payment cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function request(Request $request)
    {
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        $this->validate($request, [
            'payer_name' => 'required|string|max:255',
            'payer_email' => 'required|string|email|min:1|max:255',
            'bank_id' => 'required|numeric|min:1|max:255',
            'bank_payment_method_id' => 'required|numeric|min:1',
            'message' => 'required|string|min:1',
            'payer_phone_no'=>'required|string|min:1',
            'total_amount'=>'required|numeric',
            'user_id'=>'required|numeric',
            'payer_address'=>'required|string|max:255'
        ]);
        $data = [];
        $_POST = $request->all();
        unset($_POST['access_token']);
        $baokim_url = '';
        $order_id = time().'-add_money-'.$request->input('user_id');;
        $_POST['order_id'] =  $order_id;
        $_POST['address'] = $_POST['payer_address'];
        //return $this->setResponse(200,$_POST);
        $data["status"] = 1;
        $order_type = 0;
        if (isset($_POST['bank_payment_method_id']) && !empty($_POST['bank_payment_method_id'])) {
            $baokim = new BaoKimPaymentPro();
            $_POST['bank_payment_method_id']= intval($request->input('bank_id'));
            if($request->input('bank_payment_method_id',0) == 1){
                $order_type = 3;
            }else if($request->input('bank_payment_method_id',0)  == 2){
                $order_type = 5;
            }
            $result = $baokim->pay_by_card($_POST);
            if (!empty($result['error'])) {
                $data["error"] = $result['error'];
                $data["status"] = 0;
                createUserSpendHistory($request->input('user_id'),$request->ip(),$request->server('HTTP_USER_AGENT'),$request->input('total_amount'),$order_id,$request->input('message').'-'.$request->input('order_description'),6,$order_type);
            } else {
                $baokim_url = isset($result['redirect_url'] )? $result['redirect_url'] : $result['guide_url'];
            }
        } else {
            try {
                $baokim = new BaoKimPayment();
                $baokim_url = $baokim->createRequestUrl($_POST);
            }catch (\Exception $error){
                return response($error->getMessage(),500);
            }
        }
        createUserSpendHistory($request->input('user_id'),$request->ip(),$request->server('HTTP_USER_AGENT'),$request->input('total_amount'),$order_id,$request->input('message').'-'.$request->input('order_description'),0,$order_type);
        $data["redirect_url"] = $baokim_url;
        $data['type']='redirect';
        return $this->setResponse(200, $data);
    }



    function bank_list(Request $request)
    {
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        if (Cache::has('bk-list-bank-4')) {
            return $this->setResponse(200, Cache::get('bk-list-bank-4'));
        }
        $baokim = new BaoKimPaymentPro();
        $banks = $baokim->get_seller_info();
        if(is_array($banks)){
            Cache::rememberForever('bk-list-bank-4', function () use ($banks) {
                return $banks;
            });
        }
        return $this->setResponse(200, $banks);
    }

    function momoCreatePayment(Request $request){
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        $this->validate($request, [
            'amount' => 'required|integer',
        ]);
        $money = (int) $request->input('amount');
        $user_id = getUserId($request->input('access_token'));
        $orderInfo = "Nạp Xu sosanhnha.com";
        $returnUrl = config('momo.returnUrl');
        $notifyurl = config('momo.notifyurl');
        $amount =(string) $money;
        $orderid = time()."MOMO".$user_id;
        $ush = createUserSpendHistory($user_id, $request->ip(), $request->server('HTTP_USER_AGENT'),$money, $orderid,$orderInfo, 0,1);
        $orderid = $orderid . 'USH' . $ush->ush_id;
        $ush->ush_order_id = $orderid;
        $ush->save();
        $requestId = $orderid;
        $requestType = "captureMoMoWallet";
        $extraData =  "";//pass empty value if your merchant does not have stores else merchantName=[storeName]; merchantId=[storeId] to identify a transaction map with a physical store
        //before sign HMAC SHA256 signature
        $rawHash = "partnerCode=".config('momo.partnerCode')."&accessKey=".config('momo.accessKey')."&requestId=".$requestId."&amount=".$amount."&orderId=".$orderid."&orderInfo=".$orderInfo."&returnUrl=".$returnUrl."&notifyUrl=".$notifyurl."&extraData=".$extraData;
        $signature = hash_hmac("sha256", $rawHash, config('momo.serectkey'));

        $data =  array('partnerCode' => config('momo.partnerCode'),
            'accessKey' => config('momo.accessKey'),
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderid,
            'orderInfo' => $orderInfo,
            'returnUrl' => $returnUrl,
            'notifyUrl' => $notifyurl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature);
        $result = MomoExecPostRequest(json_encode($data));
        $jsonResult =json_decode($result,true);
        return $this->setResponse(200,$jsonResult);
    }

    function momoNotify(Request $request){
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
//        $this->validate($request, [
//            'partnerCode' => 'required',
//            'accessKey' => 'required',
//            'requestId' => 'required',
//            'amount' => 'required',
//            'orderId' => 'required',
//            'orderInfo' => 'required',
//            'orderType' => 'required',
//            'transId' => 'required',
//            'message' => 'required',
//            'localMessage' => 'required',
//            'responseTime' => 'required',
//            'errorCode' => 'required',
//            'payType' => 'required',
//            'extraData' => 'required',
//            'signature' => 'required',
//        ]);

        $validator = Validator::make($request->all(),[
            'partnerCode' => 'required|string|max:255',
            'accessKey' => 'required|string|max:255',
            'requestId' => 'required|string|max:255',
            'amount' => 'required|string|max:255',
            'orderId' => 'required|string|max:255',
            'orderInfo' => 'required|string|max:255',
            'orderType' => 'required|string|max:255',
            'transId' => 'required|string|max:255',
            'message' => 'required|string|max:255',
            'localMessage' => 'required|string|max:255',
            'responseTime' => 'required|string|max:255',
            'errorCode' => 'required|string|max:255',
            'payType' => 'required|string|max:255',
            'signature' => 'required|string|max:255',
        ]);
        $request = $request->all();
        @file_put_contents(storage_path('logs/momo.log'),date('d-m-Y H:i:s').json_encode($request,JSON_UNESCAPED_UNICODE,JSON_PRETTY_PRINT) . "\n",FILE_APPEND);
        if ($validator->fails()) {
            sendNotifySlack(['validate'=>json_encode(collect($validator->messages())->toArray())],'https://hooks.slack.com/services/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');
           return response('Request lỗi');
        }
//        sendNotifySlack($request,'https://hooks.slack.com/services/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');
        $partnerCode = $request['partnerCode'];
        $accessKey = $request['accessKey'];
        $requestId = $request['requestId'];
        $amount = $request['amount'];
        $orderId = $request['orderId'];
        $orderInfo = $request['orderInfo'];
        $orderType = $request['orderType'];
        $transId = $request['transId'];
        $message = $request['message'];
        $localMessage = $request['localMessage'];
        $responseTime = $request['responseTime'];
        $errorCode = $request['errorCode'];
        $payType = $request['payType'];
        $signature = $request['signature'];
        $extraData = arrGetVal('extraData',$request,'');
        $str_verify = "partnerCode=$partnerCode&accessKey=$accessKey&requestId=$requestId&amount=$amount&orderId=$orderId&orderInfo=$orderInfo&orderType=$orderType&transId=$transId&message=$message&localMessage=$localMessage&responseTime=$responseTime&errorCode=$errorCode&payType=$payType&extraData=$extraData";
        $mySign = hash_hmac('sha256', $str_verify, config('momo.serectkey'));
        $payUrl = config('momo.payUrl')."?partnerCode=$partnerCode&accessKey=$accessKey&requestId=$requestId&amount=$amount&orderId=$orderId&signature=$signature&requestType=captureMoMoWallet";
        $res_sign = "requestId=$requestId&orderId=$orderId&message=$message&localMessage=$localMessage&payUrl=$payUrl&errorCode=$errorCode&requestType=captureMoMoWallet";
        $data_response = [
            'requestId'=>$request['requestId'],
            'errorCode'=>$request['errorCode'],
            'message'=>$request['message'],
            'localMessage'=>$request['localMessage'],
            'requestType'=>$request['payType'],
            'payUrl'=>$payUrl,
            'signature'=> hash_hmac('sha256', $res_sign, config('momo.serectkey'))
        ];
        if($mySign==$request['signature']){
            $order_id = $request['orderId'];
            $order_id_array = explode('MOMO',$order_id);
            $info_order = explode('USH',$order_id_array[1]);
            $user_id = current($info_order);
            $ush_id = end($info_order);
            $user_spend_history = UserSpendHistory::where('ush_id',$ush_id)->where('ush_order_id',$order_id)->first();
            if(empty($user_spend_history)){
                return response('Không tìm thấy đơn hàng trên hệ thống!',500);
            }else if($user_spend_history->ush_status==4){
                return response('Đơn hàng này đã được xử lí!',500);
            }
            if($request['errorCode']==0){
                updateMoneyBuyUserId($user_id,(float) $amount);
                $user_spend_history->ush_status = 4;
                $user_spend_history->save();
                dispatch(new SendMailPaymentToUser(['ush_id'=>$user_spend_history->ush_id,$user_spend_history->ush_status]));
//                SendMailPaymentToUser::dispatch(['ush_id'=>$user_spend_history->ush_id,$user_spend_history->ush_status]);
//                sendMailBaoKimV4::dispatch($request,$user_spend_history->ush_id);
                sendNotifySlack(['total_amount'=>$amount,'type'=>'Momo Payment','order_id'=>$order_id],'https://hooks.slack.com/services/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');
            }else if($request['errorCode']==49){
                $user_spend_history->ush_status = 5;
                $user_spend_history->save();
            }
            else{
                $user_spend_history->ush_status = 8;
                $user_spend_history->save();
            }
            dispatch(new sendMailNotificationToAdmin($request,$user_spend_history));
//            sendMailNotificationToAdmin::dispatch($request,$user_spend_history);
            $momo_notify = new MomoNotify();
            foreach ($request as $key=>$value){
                $momo_notify->{$key} = $value;
            }
            $momo_notify->save();
            $request['status_web']='ok';
            sendNotifySlack($request,'https://hooks.slack.com/services/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');
            return response($data_response);
        }else{
            $request['status_web']='fail';
            sendNotifySlack($request,'https://hooks.slack.com/services/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');
           return response('Chữ ký không đúng!');
        }


//        @file_put_contents(storage_path('logs/momo.log'),date('d-m-Y H:i:s').json_encode($request,JSON_UNESCAPED_UNICODE,JSON_PRETTY_PRINT) . "\n",FILE_APPEND);
    }

    function momoReturn(Request $request){
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        return  $request->all();
    }

    function returnAddMoney(Request $request){
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        $user_id = getUserId($request->input('access_token'));
        $request = $request->all();
        unset($request['access_token']);
        $this->sendNotifySlack($request);
//        sendMailUserBackPayment::dispatch($request,$user_id);
        return $this->setResponse(200,'ok');

    }

    function sendNotifySlack($request){
        if($request->header("isdoc",0)==1){
            return 'ok';
        }
        $message = "Khách hàng thoát khỏi hệ thông nạp tiền sosanhnha.com \n";
        $id = isset($request['id'])?$request['id']:'';
        $customer_name =  isset($request['fullname'])?$request['fullname']:'';
        $customer_phone = isset($request['phone'])?$request['phone']:'';
        $email = isset($request['email'])?$request['email']:'';
        $customer_address = isset($request['address'])?$request['address']:'';
        $email_payment = isset($request['email_payment'])?$request['email_payment']:'';
        if(!empty($customer_name)) $message .= "ID: $id \n";
        if(!empty($customer_name)) $message .= "Họ tên: $customer_name \n";
        if(!empty($customer_address)) $message .= "Địa chỉ: $customer_address \n";
        if(!empty($customer_phone)) $message .= "Điện thoại: $customer_phone \n";
        if(!empty($email)) $message .= "Email: $email \n";
        if(!empty($email_payment)) $message .= "Email 2: $email_payment \n";
        $client = new Client('https://hooks.slack.com/services/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');
        $client->to('#payment')->send($message);
    }


    function createQrCodeMomo(Request $request){
        $domain = 'https://test-payment.momo.vn';
        $partnerCode = 'MOMOLCQL20190320';
        $storeId = 53645;
        $storeSlug = $partnerCode.'-'.$storeId;
        $amount = 1000000;
        $billId = '1571979165MOMO233558USH482';
        $mySign = hash_hmac('sha256', "storeSlug=$storeSlug&amount=$amount&billId=$billId", config('momo.serectkey'));
        return "$domain/pay/store/$storeSlug?a=$amount&b=$billId&s=$mySign";
    }

}
