<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 23/08/2019
 * Time: 14:56
 */

namespace App\Http\Controllers\Api\V2;

use App\Models\UserContact;
use App\Models\UserContactBuy;
use Illuminate\Http\Request;

class UserContactBuyController extends Controller
{
    function index(Request $request){

    }

    function show(Request $request,$id){

    }

    function store(Request $request){
        $this->validate($request, [
            'usc_id' =>  'required|numeric'
        ]);
        $user_id = getUserId($request->access_token);
        $check_exist = UserContactBuy::where('ucb_usc_id',$request->usc_id)->where('ucb_use_id',$user_id)->first();
        if(empty($check_exist)){
            $price = (int)config('configuration.con_price_buy_contact');
            $money = updateMoneyBuyUserId($user_id, -$price);
            if($money==false){
                return response($this->setResponse(500,null,'Không đủ xu để giao dịch'),500);
            }
            $user_contact_buy = new UserContactBuy();
            $user_contact_buy->ucb_usc_id = $request->usc_id;
            $user_contact_buy->ucb_use_id = $user_id;
            $user_contact_buy->save();
            createUserSpendHistory($user_id, $request->ip(), $request->server('HTTP_USER_AGENT'), -$price, time() . '-contact-' . $request->usc_id . '-' .$user_id, 'Mua liên hệ khách hàng ' . $request->usc_id, 4,7);
            $user_contact = UserContact::find($request->usc_id);
            return $this->setResponse(200,$user_contact);
        }
        return $check_exist;
    }

}