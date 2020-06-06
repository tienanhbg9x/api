<?php

namespace App\Http\Controllers\Api\V2;

use App\Helper\Rest_Request_Baokim;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BaoKimController extends Controller
{
    function getSellerInfo(Request $request)
    {
//        if(Cache::has('bk-list-bank')){
//            return $this->setResponse(200,Cache::get('bk-list-bank'));
//        }
        $bk_restful_username = config('baokim_config.bk_restful_username');
        $bk_restful_pass = config('baokim_config.bk_restful_pass');
        $BK_priKey = config('baokim_config.bk_private_key');
        $bk_get_seller_info_url = config('baokim_config.bk_url') . "/payment/rest/payment_pro_api/get_seller_info?signature=";
        $logMsg = "";
        $Rest_Request_Baokim = new Rest_Request_Baokim();
        $array_data = array("business" => config('baokim_config.bk_business'));
        $logMsg .= "Data: " . json_encode($array_data);

        $ref_signature = makeBaoKimAPISignature("GET", "/payment/rest/payment_pro_api/get_seller_info", $array_data, array(), $BK_priKey);
        $Rest_Request_Baokim->rest_url = $bk_get_seller_info_url . $ref_signature . "&" . http_build_query($array_data);
        $Rest_Request_Baokim->array_data = $array_data;
        $Rest_Request_Baokim->Set_Digest_Authentication($bk_restful_username, $bk_restful_pass);

        $logMsg .= " | Info: U(" . $bk_restful_username . ")";

        $result = $Rest_Request_Baokim->Get_Data();
        $array_return = json_decode($result, 1);

        if (!isset($array_return["seller_account"]) || !isset($array_return["bank_payment_methods"])) {

            $logMsg .= " => Error: " . var_export($result, 1);
            return response()->json($this->setResponse(500, $logMsg), 500);

        }

//        Cache::remember('bk-list-bank',60*1000,function()use($array_return){
//           return $array_return;
//        });
        return $this->setResponse(200, $array_return);
    }

    function addMoney(Request $request)
    {
        $this->validate($request, [
            'payer_name' => 'required|string|max:255',
            'payer_email' => 'required|string|email|min:1|max:255',
            'bank_id' => 'required|numeric|min:1|max:255',
            'bank_payment_methods' => 'required|numeric|min:1',
            'message' => 'required|string|min:1',
            'payer_phone_no' => 'required|string|min:1',
            'total_amount' => 'required|numeric',
            'user_id' => 'required|numeric',
            'payer_address' => 'required|string|max:255'
        ]);
        if (checkAuthUser($request->input('access_token'), $request->input('user_id'))) {
            $order_id = time() . '-add_money-' . $request->input('user_id');
            $url_cancel = config('baokim_config.api_domain') . 'v2/cancel-payment';
            $url_success = config('baokim_config.api_doamin') . 'v2/baokim_payment_notification';
            $url = '/payment/rest/payment_pro_api/pay_by_card';
            if ($request->input('bank_payment_methods') == 1 || $request->input('bank_payment_methods') == 3) {
                $arrayPost = array(
                    'order_id' => $order_id,
                    'total_amount' => (int)$request->input('total_amount'),
                    'business' => config('baokim_config.bk_business'),
                    'order_description' => 'Nạp tiền sosanhnha',
                    'shipping_fee' => '0',
                    'tax_fee' => '0',
                    'url_cancel' => $url_cancel,
                    'url_success' => $url_success,
                    'payer_name' => $request->input('payer_name'),
                    'payer_email' => $request->input('payer_email'),
                    'payer_phone_no' => $request->input('payer_phone_no'),
                    'payer_address' => $request->input('payer_address'),
                    'message' => $request->input('message'),
                    'bank_payment_method_id' => $request->input('bank_id'),
                    'transaction_mode_id' => '1', // 2- trực tiếp
                    'escrow_timeout' => 0,
                    'mui' => 'charge',
                    'currency' => 'VND' // USD
                );
            }
            //ksort($arrayPost);
            $signature = makeSignatureBaoKim('POST', $url, array(), $arrayPost, config('baokim_config.bk_private_key'));
            $bk_get_url = config('baokim_config.bk_url') . "/payment/rest/payment_pro_api/pay_by_card?signature=";
            $url_signature = $bk_get_url . $signature;
            $curl = curl_init($url_signature);
//            dd($arrayPost);

            curl_setopt_array($curl, array(
                CURLOPT_POST => true,
                CURLOPT_HEADER => false,
                CURLINFO_HEADER_OUT => true,
                CURLOPT_TIMEOUT => 50,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPAUTH => CURLAUTH_DIGEST | CURLAUTH_BASIC,
                CURLOPT_USERPWD => config('baokim_config.bk_restful_username') . ':' . config('baokim_config.bk_restful_pass'),
                CURLOPT_POSTFIELDS => $arrayPost
            ));
            $data = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            $result = json_decode($data, true);
            if ($status == 200) {
                switch ($result['next_action']) {
                    case 'redirect' :
                        $url = $result['redirect_url'];
                        createUserSpendHistory($request->input('user_id'), $request->ip(), $request->server('HTTP_USER_AGENT'), $request->input('total_amount'), $order_id, $arrayPost['message'] . '-' . $arrayPost['order_description'], 0);
                        return $this->setResponse(200, ['redirect_url' => $url, 'type' => 'redirect', 'data_send' => $arrayPost]);
                    case 'display_guide' :
                        $url = $result['guide_url'];
                        createUserSpendHistory($request->input('user_id'), $request->ip(), $request->server('HTTP_USER_AGENT'), $request->input('total_amount'), $order_id, $arrayPost['message'] . '-' . $arrayPost['order_description'], 0);
                        return $this->setResponse(200, ['guide_url' => $url, 'type' => 'guide', 'data_send' => $arrayPost]);
                }
            } elseif ($status == 450) {
                createUserSpendHistory($request->input('user_id'), $request->ip(), $request->server('HTTP_USER_AGENT'), $request->input('total_amount'), $order_id, $arrayPost['message'] . '-' . $arrayPost['order_description'] . '-' . $error, 0);
                return response()->json($this->setResponse(405, null, [json_decode($result), 'error' => $error]), 405);
            } else {
                createUserSpendHistory($request->input('user_id'), $request->ip(), $request->server('HTTP_USER_AGENT'), $request->input('total_amount'), $order_id, $arrayPost['message'] . '-' . $arrayPost['order_description'], 0);
                return response()->json($this->setResponse($status, [$status, 'error' => $error]), $status);
            }
        }

    }

    function cancelPayment()
    {

    }

    function downloadLogBaoKim(Request $request,$filename = '')
    {
        if($request->header("isdoc",0)) return '';
//        $type = 'text/plain';
//        $headers = ['Content-Type' => $type];
//        $filename = storage_path() . "/logs/" . $filename;
//        if (file_exists($file_path)) {
//            // Send Download
//            $response = new BinaryFileResponse($file_path, 200, $headers);
//        } else {
//            // Error
//            exit('Requested file does not exist on our server!');
//        }
//        return $response;
        $filename = storage_path() . "/logs/" . $filename;

//        $filename = $file_url;

        $chunksize = 5 * (1024 * 1024); //5 MB (= 5 242 880 bytes) per one chunk of file.

        if(file_exists($filename))
        {
            set_time_limit(300);

            $size = intval(sprintf("%u", filesize($filename)));

            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: '.$size);
            header('Content-Disposition: attachment;filename="'.basename($filename).'"');

            if($size > $chunksize)
            {
                $handle = fopen($filename, 'rb');

                while (!feof($handle))
                {
                    print(@fread($handle, $chunksize));

                    ob_flush();
                    flush();
                }

                fclose($handle);
            }
            else readfile($filename);

            exit;
        }else{
            exit('Requested file does not exist on our server!');
        }
    }


}
