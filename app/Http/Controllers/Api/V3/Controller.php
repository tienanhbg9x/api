<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 21/10/2019
 * Time: 21:20
 */

namespace App\Http\Controllers\Api\V3;
use Illuminate\Http\Request;

use Laravel\Lumen\Routing\Controller as BaseController;


class Controller extends BaseController
{
    public $page = 1;
    public $fields = '*';
    public $where  = null;
    public $limit = 30;
    public $access_token= null;

    function __construct(Request $request)
    {
        if($request->input('page')){
            $page = (int) $request->input('page');
            if($page<1||$page>100000){
                $this->page  = 1;
            }else{
                $this->page = $page;
            }
        }

        if($request->input('fields')) $this->fields = $request->input('fields');
        if($request->input('where')) $this->where = $request->input('where');
        if($request->input('access_token')) $this->access_token = $request->input('access_token');
        if($request->input('limit')){
            $limit = (int) $request->input('limit');
            if($limit<1||$limit>100){
                $this->limit  = 30;
            }else{
                $this->limit = (int) $request->input('limit');
            }
        }

    }

    function createResponse($data,$status_code=200,$header=[]){
        $response = ['status'=>$status_code,'data'=>$data];
        return response($response,$status_code,$header);
    }

}