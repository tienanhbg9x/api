<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 24/06/2019
 * Time: 11:35
 */

namespace App\Http\Controllers\Api\V2;


use App\Models\Job;
use Illuminate\Support\Facades\Request;

class JobController extends Controller
{
    function index(Request $request){
        $offset = $this->page * $this->limit - $this->limit;
        $jobs = Job::orderBy('id', 'desc')->offset($offset)->limit($this->limit)->get();
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'jobs' => $jobs,

        ];
        return $this->setResponse(200, $data);
    }
}