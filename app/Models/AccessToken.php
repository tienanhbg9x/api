<?php

namespace App\Models;

use Firebase\JWT\JWT;

class AccessToken
{
    //
    public function create($data = []){
        if(!empty(app("access_token"))) return app("access_token");
        $data = str_random(30);
        $key = config("app.secret_access_token");
        $token = array(
            "client_id" => $this->getGuestId(),
        );
        $jwt = JWT::encode($token, $key);
        return $jwt;
    }

    function getGuestId($cookie_id = ""){
        return (substr(number_format(time() * rand(),0,'',''),0,10));
    }

    public function verify(){

    }
}
