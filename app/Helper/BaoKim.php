<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 06/12/2018
 * Time: 10:55
 */

namespace App\Helper;

class Rest_Request_Baokim{


    var $authentication_username	= "";
    var $authentication_password	= "";

    var $rest_url						= "";
    var $error_msg						= "";

    var $rest_access_key_id			= "";
    var $rest_payload					= "";
    var $rest_share_key				= "";
    var $rest_checksum				= "";
    var $rest_Authentication		= "";

    var $user_agent					= "";
    var $domain							= "";

    //arrray chua noi dung post di
    var $array_data					= array();

    //CURL Handle
    var $ch;

    //Log file
    var $log_file						= "baokim_restful_log.cfn";

    /**
     * [Rest_Request description]
     */
    function Rest_Request_Baokim(){

    }

    //Thiết lập user_name và password cho Basic_Authentication
    function Set_Basic_Authentication($user_name, $password){

        $this->rest_Authentication			= "basic";
        $this->authentication_username	= $user_name;
        $this->authentication_password	= $password;

        return $this;
    }

    /*
    //thiết lập user_name và password cho digest_authentication

    */
    function Set_Digest_Authentication($user_name, $password){

        $this->rest_Authentication 		= "digest";
        $this->authentication_username 	= $user_name;
        $this->authentication_password 	= $password;

        return $this;

    }

    /*
    Khởi tạo CURL
    */
    function CURL_init(){
        //Khởi tao CURL
        $this->ch = curl_init();
        //Set URL
        curl_setopt($this->ch, CURLOPT_URL, $this->rest_url);

        //SSL
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        //Timeout
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10); // timeout on connect = 10s
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30); // timeout on response = 30s

        //Set Fail on Error khi gặp http code > 400 (401: Unauthorized ...)
        //curl_setopt($this->ch, CURLOPT_FAILONERROR, true);

        //Thiết lập user và password cho Basic_Authentication hoặc digest_Authentication
        switch ($this->rest_Authentication){
            case "basic":
                curl_setopt($this->ch, CURLOPT_USERPWD, $this->authentication_username . ":" . $this->authentication_password);
                curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                break;
            case "digest":
                curl_setopt($this->ch, CURLOPT_USERPWD, $this->authentication_username . ":" . $this->authentication_password);
                curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                break;
        }

        //Set return
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        // Nếu là server dev thì set thêm cookie để dùng Bảo Kim bản beta
//        if(checkIsDevServer())
curl_setopt($this->ch, CURLOPT_COOKIE, "_env_=beta");

    } // End CURL_init()

    /*
    Kiểm tra lỗi và đóng CURL
    */
    function CURL_error(){

        //Nếu có lỗi thì return lại lỗi
        if(curl_errno($this->ch)){

            //Set error
            $this->Set_Request_Error("Error Number = " . curl_errno($this->ch) . ". Error Message = " . curl_error($this->ch));
            curl_close($this->ch);

            return false;

        }

        // close cURL resource, and free up system resources
        curl_close($this->ch);
        return true;

    } // End CURL_error()

    /*
    Post dữ liệu
    */
    function Post_Data(){
        $this->CURL_init();
        /*
        Add dữ liệu vào để POST
        */
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->array_data));

        // Data dạng json nhận được
        $data												= curl_exec($this->ch);
        // Status Code
        $httpStatusCode								= curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        // Bẻ dữ liệu nhận được thành mảng
        $arrTmp											= @json_decode($data, 1);

        // Ghép mảng dữ liệu trả về gồm httpCode + dữ liệu nhận được
        $arrDataReturn									= array("httpStatusCode" => $httpStatusCode);
        if(is_array($arrTmp)) $arrDataReturn	= array_merge($arrDataReturn, $arrTmp);

        // Dữ liệu trả về dạng json
        $dataReturn										= json_encode($arrDataReturn);

        $this->error_msg								.= "DATA RETURN: " . $dataReturn;
        if($httpStatusCode != 200) $this->error_msg	.= " | DATA POST: " . @json_encode($this->array_data);

        //ghi log lai
        $this->Log_Request($this->log_file);

        //Check xem có lỗi gì ko?
        if (!$this->CURL_error()){
            $this->error_msg	.= " | CURL ERROR: " . @json_encode(@curl_getinfo($this->ch));
            $this->Log_Request($this->log_file);
            return false;
        }

        return $dataReturn;

    } // End Post_Data()

    /*
    Get_Data: Lấy dữ liệu theo method GET
    */
    function Get_Data(){
        $this->CURL_init();

        $data = curl_exec($this->ch);

        //echo $data;
        //echo $this->CURL_error();
        //Check xem có lỗi gì ko?
        if (!$this->CURL_error()) return false;

        return $data;

    } //End Get_Data()


    /*
    Lấy error
    */
    function Get_Request_Error(){

        return $this->error_msg;

    }

    /*
    Set error
    $error_msg 	: Thông báo lỗi
    $clear 		: Clear các thông báo lỗi ở trước
    */
    function Set_Request_Error($error_msg, $clear=0){
        if ($clear != 0) $this->error_msg = "";
        $this->error_msg .= $error_msg;
    }

    /*
    Tạo checksum cho POST
    */
    function Generate_Checksum(){
        return hash("sha256", hash("sha256", $this->rest_payload) . $this->rest_share_key);
    }

    function Log_Request($log_file){
        $dirname = dirname(__FILE__);
        $dirname = str_replace("/classes/restful","/ipstore/",$dirname);
        $dirname = str_replace('\classes\restful','\ipstore\\',$dirname);
        $filename = $dirname. $log_file;
        $handle = @fopen($filename, 'a');
        //Nếu handle chưa có mở thêm ../
        if (!$handle) $handle = @fopen($filename, 'a');
        //Nếu ko mở đc lần 2 thì exit luôn
        if (!$handle) return;
        $str = "//--------------------------------------------------------------------------------------------------------------------------------->\n";

        fwrite($handle,$str . date("d/m/Y h:i:s A") . " " . $_SERVER['REMOTE_ADDR'] . " " . $_SERVER['SCRIPT_NAME'] . "?" . @$_SERVER['QUERY_STRING'] . "\n" . $this->error_msg . "\n");
        fclose($handle);
    }
}
