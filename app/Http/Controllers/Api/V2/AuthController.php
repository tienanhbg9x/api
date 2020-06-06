<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;

use Firebase\JWT\JWT;
use App\Models\User;


/**
 * @resource v2 Authentication
 *
 * Api for User
 */
class AuthController extends Controller
{
    /**
     * POST v2/login
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@token` | token user_info login
     * @return \Illuminate\Http\Response
     */
    function checkLogin(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string'
        ]);
        $token = $request->token;
        try {
            $user_info = JWT::decode($token, env('JWT_LOGIN_SECRET'), ['HS256']);
            $user = User::where('use_email', $user_info->data->email)->first();

            if (!$user) {
                $user = new User();
                $user->use_fullname = $user_info->data->name;
                $user->use_email = $user_info->data->email;
                $user->use_picture = $user_info->data->picture;
                $user->use_active = 1;
                $user->save();
            }
            $time_now = (int)explode(' ', microtime())[1];
            $config_exp_token = (int)config('app.exp_token');
            $exp = $time_now + $config_exp_token;
            $token = [
                "data" => [
                    "id" => $user->use_id,
                    "active" => $user->use_active
                ],
                "iat" => $time_now,
                "exp" => $exp
            ];
            $token = JWT::encode($token, config("app.secret_access_token"));
            return $this->setResponse(200, ['api_token' => $token]);

        } catch (\Exception $e) {
            return $this->setResponse(401, null, $e->getMessage());
        }
    }

    function login(Request $request)
    {
//        $this->validate($request, [
//            'token' => 'required|string'
//        ]);
//        $token = $request->token;
        try {
//            $user_info = JWT::decode($token, env('JWT_LOGIN_SECRET'), ['HS256']);
//            $user_info = $user_info->data;
            if($request->input('type_login')=='loginname'){
                $account = trim($request->input('account'));
                $password =  trim($request->input('password'));
                $check_account = $this->checkAccount($account);
                if($check_account){
                    $user = User::select('use_id','use_email','use_name','use_picture','use_rol','use_active','use_security','use_password')->where('use_loginname',$account)->first();
                    if($user==null||md5($password.$user->use_security)!=$user->use_password){
                        return $this->setResponse(401, null, 'Đăng nhập lỗi! Tài khoản bị khóa hoặc không tồn tại!!! Vui lòng liên hệ với quản trị viên!');
                    }
                }else{
                    return $this->setResponse(401, null, 'Đăng nhập lỗi! Tài khoản bị khóa hoặc không tồn tại!!! Vui lòng liên hệ với quản trị viên!');
                }
                return $this->getResponseToken($user);
            }
            $type_social = $request->input('type_social');
            $social_token = $request->input('social_token');
            $user_email = null;
            $user_id_social =  null;
            $user_name =  null;
            $user_picture = null;
            $user_login_name= null;
            if ($type_social == 'facebook') {
                $user_face_info = checkTokenFacebook($social_token["accessToken"]);
                if ($user_face_info->status_login == false) {
                    return $this->setResponse(401, null, 'Đăng nhập lỗi!');
                }
                $user_id_social = $user_face_info->id;
            } else if ($type_social == 'google') {
                $user_google_info = checkLoginGoogle($social_token);
                if ($user_google_info->status_login == false) {
                    return $this->setResponse(401, null, 'Đăng nhập lỗi!');
                }
                $user_email = $user_google_info->email;
                $user_name =   $user_google_info->name;
                $user_picture =  $user_google_info->picture;
                $user_id_social = $user_google_info->id;
                $user_login_name = "gg".$user_google_info->id;
            }
            if (!empty($user_id_social)) {
                $user_check =  User::select('use_id','use_email','use_name','use_picture','use_rol','use_active')->where('use_loginname', $user_login_name)->first();
                if ($user_check) {
                    if($user_check->use_active==0){
                        return $this->setResponse(401, null, 'Đăng nhập lỗi! Tài khoản bị khóa hoặc không tồn tại!!! Vui lòng liên hệ với quản trị viên!');
                    }
                    if($user_picture!=$user_check->use_picture){
                        $user = User::find($user_check->use_id);
                        $user->use_picture = $user_picture;
                        $user->save();
                    }
                    return $this->getResponseToken($user_check);
                } else {
                    $data_password = EncodePassword(str_random(10));
                    $user = new User();
                    $user->use_name = $user_name;
                    $user->use_fullname = $user_name;
                    $user->use_email_payment = $user_email;
                    $user->use_picture = $user_picture;
                    $user->use_email = $user_email;
                    $user->use_loginname = $user_login_name;
                    $user->use_password = $data_password['password'];
                    $user->use_security = $data_password['key_security'];
                    $user->use_rol = 0;
                    $user->use_active = 1;
                    if ($user->save()) {
                        return $this->getResponseToken($user);
                    } else {
                        return $this->setResponse(500, null, 'Đăng nhập không thành công!Lỗi hệ thống, vui lòng bảo cho quản trị viên!!!');
                    }
                }

            }
        } catch (\Exception $e) {
            return $this->setResponse(401, null, $e->getMessage());
        }
    }

    function getResponseToken($user){
        $token = createAccessToken($user);
        $user_info = [
            'id'=>$user->use_id,
            'name'=>$user->use_name,
            'picture'=>$user->use_picture,
            'rol'=>getUserRol($user->use_rol),
            'active'=>$user->active,
            'email'=>$user->email
        ];
        return $this->setResponse(200, ['user_info'=>$user_info ,'access_token' => $token]);
    }

    function checkAccount($account){
        if(strlen($account)<4){
            return false;
        }
        return true;
    }

}
