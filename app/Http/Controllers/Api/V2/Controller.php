<?php

namespace App\Http\Controllers\Api\V2;

use Laravel\Lumen\Routing\Controller as BaseController;


class Controller extends BaseController
{

    public $api_token = null;
    public $sphinx = null;
    public $limit = null;
    public $fields = null;
    public $page = null;
    public $where = null;
    public $ssr = false;
    public $access_token= null;


    function __construct()
    {
        $this->fields = isset($_REQUEST['fields']) ? $_REQUEST['fields'] : null;

        $this->where = isset($_REQUEST['where']) ? urldecode($_REQUEST['where']) : null;

        $this->limit = isset($_REQUEST['limit']) ? ((int)$_REQUEST['limit'] >100?100:(int)$_REQUEST['limit']) : 30;

        $this->page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

        $this->ssr = isset($_REQUEST['ssr']) ? (boolean)$_REQUEST['ssr'] : false;

        $this->access_token = isset($_REQUEST['access_token']) ? $_REQUEST['access_token'] : null;
        
    }

    function setResponse($status, $data = null, $message_error = '')
    {
        if ($status != 200) {
            return [
                'status_code' => $status,
                'message' => $message_error
            ];
        } else {
            $data_return = ['status_code' => 200, 'data' => $data];
            return $data_return;
        }
    }

}
