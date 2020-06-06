<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Classified;
use App\Models\ClassifiedLog;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use \DB;
use phpDocumentor\Reflection\Types\Object_;

/**
 * @resource v2 Classified-log
 *
 * Api for Classified-log
 */
            

class ClassifiedLogController extends Controller
{
    //

    private $fillable = ['id','cla_id','cat_id','cit_id','dis_id','ward_id','street_id','proj_id','lat','lng','citid','disid','wardid','streetid','use_id','money_count','type_vip','created_at'];

     /**
     * GET v2/classified-log
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | ClassifiedLogController
     * `route_name` | classified-log
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu classified-log ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong classified-log ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi classified-log cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang classified-log cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request){
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
            'type' => 'string'
        ]);
        if($request->input('type')=='check_sum'){
            $date_start  = strtotime($request->input('date_start')?$request->input('date_start').' 00:00:00':date('Y-m-01 00:00:00'));
            $date_end = strtotime($request->input('date_end')?$request->input('date_end').' 23:59:59':date('Y-m-d 23:59:59'));
            $total_info = ClassifiedLog::select(DB::raw(" count(cla_id) as total_classified,sum( money_count) as total_money,count(if (type_vip=1,1,NULL)) 'total_vip_1',count(if (type_vip=2,1,NULL)) 'total_vip_2'"))->whereBetween('created_at',[$date_start,$date_end]);

            if ($this->where != null)$total_info =  $this->buildQueryWhere($total_info);
            $total_info = $total_info->first();
//            dd($total_info->total_money);
            $total_info->total_money = $total_info->total_money==null?0:$total_info->total_money;
            return $this->setResponse(200,['date_start'=>date('Y-m-d',$date_start),'date_end'=>date('Y-m-d',$date_end),'total_info'=>$total_info]);
        }


//        thong ke luong up tin
        else if($request->input('type')=='statistical'){
            $list_mounth_has_new = [];
            $date_start = strtotime($request->input('date_start')?$request->input('date_start').'-01':date('Y-m-01 00:00:00'));
            $date_end = strtotime($request->input('date_end')?$request->input('date_end').'-31':date('Y-m-d 23:59:59'));
            $total_info = ClassifiedLog::select('id','cit_id','money_count','type_vip','created_at')->whereBetween('created_at',[$date_start,$date_end])->get();

            //repair
            $vip_news = [];
            $new_news = [];
            foreach ($total_info as $value){
                if ($value->type_vip == 1) $new_news[] = $value;
                if ($value->type_vip == 2) $vip_news[] = $value;
            }
            foreach ($total_info as $value){
                $list_mounth_has_new[] = date('m-Y', $value->created_at);
            }

            $list_mounth_has_new = array_unique($list_mounth_has_new);
            $total_info_mounth = [];
            $total_info_mounth_parent = [];
            foreach ($list_mounth_has_new as $lm){
                $date_start_has_news = strtotime( '01-'.$lm);
                $date_end_has_news = strtotime('31-'.$lm);
                $total_info_mounth_child = ClassifiedLog::select(DB::raw(" sum( money_count) as total_money_has_news,count(if (type_vip=1,1,NULL)) 'total_vip_1_has_news',count(if (type_vip=2,1,NULL)) 'total_vip_2_has_news'"))->whereBetween('created_at',[$date_start_has_news,$date_end_has_news])->get();
                $total_info_mounth_child = $total_info_mounth_child->map(function ($item) use($lm){
                    $item->time = $lm;
                    return $item;
                });


                $total_info_mounth_parent[] = $total_info_mounth_child;
            }
            $total_info_mounth_parent = array_reverse($total_info_mounth_parent);

            foreach ($total_info_mounth_parent as $value){
                $total_info_mounth[] = $value[0];
            }
            $max_total_new = 0;
            $max_total_vip = 0;
            $max_total_city_news = 0;
            $max_total_city_money = 0;
            $max_total_mounth_money = 0;

            foreach ($total_info_mounth as $value){
                    if($value->total_vip_1_has_news > $max_total_new) $max_total_new = $value->total_vip_1_has_news;
                    if($value->total_vip_2_has_news > $max_total_vip) $max_total_vip = $value->total_vip_2_has_news;
                    if($value->total_money_has_news > $max_total_mounth_money) $max_total_mounth_money = $value->total_money_has_news;

            }

            // end repair
            $list_city_has_new = [];
            foreach ($total_info as $value){
                $list_city_has_new[] = $value->cit_id;
            }
            $total_arr = [];
            $list_city_has_new = array_unique($list_city_has_new);
            foreach ($list_city_has_new as $value){
                $arr_top_city_news = array('cit_id' => '','total_news' => null, 'total_money' => null );
                $arr_top_city_news['cit_id'] = $value;
                foreach ($total_info as $val){
                    if ($val->cit_id == $value){
                        $arr_top_city_news['total_news'] += 1;
                        $arr_top_city_news['total_money'] += $val->money_count;
                    }
                    if($arr_top_city_news['total_news'] > $max_total_city_news) $max_total_city_news = $arr_top_city_news['total_news'];
                    if($arr_top_city_news['total_money'] > $max_total_city_money) $max_total_city_money = $arr_top_city_news['total_money'];
                }
                $arr_top_city_news = (object) $arr_top_city_news;
                $total_arr[] = $arr_top_city_news;
            }
            for ($i = 0; $i < (count($total_arr) - 1); $i++){
                for ($j = $i +1; $j < count($total_arr); $j++){
                    if ($total_arr[$i]->total_news < $total_arr[$j]->total_news){
                        $tmp = $total_arr[$j];
                        $total_arr[$j] = $total_arr[$i];
                        $total_arr[$i] = $tmp;
                    }
                }
            }
            $total_arr = array_slice($total_arr, 0, 5);
            $statistical_up_news = array('statis_mounth' => $total_info_mounth, 'statis_city' => $total_arr, 'max_total_new' => $max_total_new, 'max_total_vip' => $max_total_vip, 'max_total_mounth_money' => $max_total_mounth_money, 'max_total_city_news' => $max_total_city_news, 'max_total_city_money' => $max_total_city_money );
            return $statistical_up_news;

        }
//        end thong ke;
        $offset = $this->page * $this->limit - $this->limit;
        $cla_log = new ClassifiedLog();
        $cla_log = $cla_log->selectRaw($cla_log->alias($this->fields));
        if ($this->where != null)$cla_log =  $this->buildQueryWhere($cla_log);
        $cla_log = $cla_log->orderBy('id', 'desc')->offset($offset)->limit($this->limit)->get();
        $cla_log = $this->filterLog($cla_log);
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'classified_log' => $cla_log,

        ];
        return $this->setResponse(200, $data);
    }
        
     
     /**
     * GET v2/classified-log
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classified-log
     * `@fields` | List fields classified-log
     * @return \Illuminate\Http\Response
     */
    function show($id){
    
    }

    function buildQueryWhere($model){
        $this->where = explode(',',$this->where);
        foreach ($this->where as $item){
            $condition = explode(" ",$item);
            if(isset($condition[1])&& in_array($condition[0],$this->fillable)) $model =  $model->where($condition[0],$condition);
        }
        return $model;
    }

    function filterLog($classified_log){
//        dd($classified_log);
        if(!empty($classified_log[0]->cla_id)){
            $arr_cla_id = $classified_log->map(function($log){
                return $log->cla_id;
            })->toArray();
            $arr_cla_id = array_unique($arr_cla_id);
            $classifieds = Classified::select('cla_id','cla_title','cla_rewrite')->whereIn('cla_id',$arr_cla_id)->get();
        }
        foreach ($classified_log as $key=> $log){
            if(!empty($log->created_at))$log->created_at = date('d/m/Y h:i',$log->created_at);
            if(!empty($classifieds))$log->cla_info = $classifieds->where('cla_id',$log->cla_id)->first();
            $classified_log[$key] = $log;
        }
        return $classified_log;
    }

    //Hai fix
     function statisticalMounth(){

     }
        
}
