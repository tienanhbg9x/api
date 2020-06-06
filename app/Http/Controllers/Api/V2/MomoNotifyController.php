<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 29/10/2019
 * Time: 14:47
 */

namespace App\Http\Controllers\Api\V2;


use App\Models\MomoNotify;
use Illuminate\Http\Request;

class MomoNotifyController extends Controller
{
    function index(Request $request){
        $offset = $this->page*$this->limit - $this->limit;
        $momo_notify = MomoNotify::orderBy('created_at','desc')->limit($this->limit)->offset($offset)->get();
        return $this->setResponse(200,$momo_notify);
    }

    function show(Request $request,$id){
        $notify = MomoNotify::find($id);
        if($notify){
            $this->setResponse(200,$notify);
        }
        return response('Not found id',404);
    }

}