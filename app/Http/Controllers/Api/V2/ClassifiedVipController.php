<?php

namespace App\Http\Controllers\Api\V2;

use App\Jobs\JobSendMailUpdateVip;
use App\Models\Classified;
use App\Models\ClassifiedVip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
/**
 * @resource v2 Classifieds-vip
 *
 * Api for Classifieds-vip
 */
            

class ClassifiedVipController  extends  Controller
{
    //

     
     /**
     * GET v2/classifieds-vip
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classifieds-vip
     * `@fields` | List fields classifieds-vip
     * @return \Illuminate\Http\Response
     */
    function show(Request $request,$id){
        $classified_vip = new ClassifiedVip();
        $classified_vip = $classified_vip->select($classified_vip->alias());
        if($request->input('type_id')=='classified_id'){
            $classified_vip =  $classified_vip->where('clv_cla_id',$id);
        }else{
            $classified_vip =  $classified_vip->where('clv_id',$id);
        }
        if ($this->where != null) $classified_vip = whereRawQueryBuilder($this->where, $classified_vip, 'mysql', 'classifieds_vip');
        $classified_vip = $classified_vip->first();
        if($classified_vip&&!empty($classified_vip->date)){
            $classified_vip->date = date('H:i d/m/Y',$classified_vip->date);
        }
        return $this->setResponse(200,$classified_vip);
    }
        
    
     /**
     * GET v2/classifieds-vip
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Tham số | Mô tả
     * --------- | -------
     * `type` | Kiểu lấy duữ liệu
     * `date_start` | Thời gian bắt đầu
     * `date_end` | Thơi gian kết thúc
     * `where` | Điều kiện lấy dữ liệu
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@type` | NULL | Với type='check', Kiểm tra với khoảng thời gian (date_start,date_end) chọn làm mới tin có hợp lệ hay không.
     * `@where` | NULL | Điều kiện lấydữ liệu (where='tên trường'+'giá trị')
     * @return \Illuminate\Http\Response
     */
    function index(Request $request){
        $this->validate($request, [
            'type' => 'string|min:1|max:255',
            'date_start'=>'string|max:11|min:7',
            'date_end'=>'string|max:11:min:7'
        ]);
        $classified_vip = new ClassifiedVip();
        $classified_vip = $classified_vip->select($classified_vip->alias());
        if ($this->where != null) $classified_vip = whereRawQueryBuilder($this->where, $classified_vip, 'mysql', 'classifieds_vip');
        $request = $request->all();
        if(isset($request['type'])){
            if($request['type']=='check'){
                if(!isset($request['date_start'])||!isset($request['date_end'])){
                    return response('Not found params `date_start`, `date_end`',500);
                }
                $date_start = strtotime($request['date_start']);
                $date_end = strtotime($request['date_end']);
                $time_now = strtotime(date('d-m-Y H:i:s'));
                if(isset($request['times_update'])) $time_update = json_decode($request['times_update']);
                $classified_vip = $classified_vip->where('clv_date','>',$time_now)->get();

                $time_tmp = $date_start;
                $value_check = [];
                $total_money = 0;
                if(!empty($time_update)){
                    foreach ($time_update as $key=>$time){
                        $time_update[$key] = ($time->HH*3600)+($time->mm*60);
                    }
                }
                sort($time_update);
                $date_end = $date_end+max($time_update);
                for(;;){
                    foreach ($time_update as $time_queue){
                        $time_check = $time_tmp+$time_queue;
                        $clv_check = $classified_vip->where('date',$time_check)->first();
                        if($clv_check){
                            $clv_check = $clv_check->toArray();
                            $clv_check['status_check'] = false;
                            $clv_check['date'] = date('d-m-Y H:i',$time_check);
                            $clv_check['time'] = $time_check;
                            $value_check[] = $clv_check;
                        }elseif($time_now >$time_check ){
                            $clv_check['status_check'] = false;
                            $clv_check['date'] = date('d-m-Y H:i',$time_check);
                            $clv_check['time'] = $time_check;
                            $value_check[] = $clv_check;
                        }else{
                            $clv_check = [];
                            $clv_check['status_check'] = true;
                            $clv_check['date'] = date('d-m-Y H:i',$time_check);
                            $clv_check['time'] = $time_check;
                            $value_check[] = $clv_check;
                            $total_money += config('configuration.con_price_cla_update');
                        }
                    }

                    $time_tmp+=86400;
                    if($time_tmp>$date_end){
                        break;
                    }
                }
                return $this->setResponse(200,['value_check'=>$value_check,'total_money'=>$total_money]);
            }elseif($request['type']=='show'){
                $classified_vip = $classified_vip->get();
                $data_return = [];
                foreach ($classified_vip as $data){
                    $clv_check['status_check'] = true;
                    $clv_check['date'] = date('d-m-Y H:i',$data->date);
                    $data_return[] = $clv_check;
                }
                return $this->setResponse(200,$data_return);
            }
        }

        return 'ok';


        ///////

//        if(isset($request['type'])&&$request['type']=='check'){
//            if(!isset($request['date_start'])||!isset($request['date_end'])){
//                return response('Not found params `date_start`, `date_end`',500);
//            }
//            $date_start = strtotime($request['date_start']);
//            $date_end = strtotime($request['date_end']);
//            $time_now = date('d-m-Y');
//            $time_now = strtotime($time_now);
//            if($time_now>$date_start||$time_now>$date_end||$date_end<$date_start){
//                return $this->setResponse(500,null,'Vui lòng chọn lại khoảng thời gian');
//            }
//            $classified_vip = $classified_vip->where('clv_date','>=',$time_now);
//        }
//
//        $offset = $this->page * $this->limit - $this->limit;
//        if ($this->where != null) $classified_vip = whereRawQueryBuilder($this->where, $classified_vip, 'mysql', 'classifieds_vip');
//        $classified_vip = $classified_vip->orderBy('clv_id', 'desc');
//        if(isset($request['type'])&&$request['type']=='check'){
//            $classified_vip = $classified_vip->get();
//            $time_tmp =$date_start;
//            $value_check = [];
//            $total_money = 0;
//            $money_reset = 0;
//            if(isset($request['times_update'])&&count($request['times_update'])>0){
//                $money_reset = count($request['times_update'])*5000;
//
//            }
//            for(;;){
//                $time_check = $classified_vip->where('date',$time_tmp)->first();
//                if($time_check){
//                    $time_check = $time_check->toArray();
//                    $time_check['status_check'] = false;
//                    $time_check['date'] = date('d-m-Y',$time_tmp);
//                    $value_check[] = $time_check;
//                }else{
//                    $time_check = [];
//                    $time_check['status_check'] = true;
//                    $time_check['date'] = date('d-m-Y',$time_tmp);
//                    $value_check[] = $time_check;
//                    $total_money += (30000+$money_reset);
//                }
//                $time_tmp+=86400;
//                if($time_tmp>$date_end){
//                    break;
//                }
//            }
//            if($date_start==$time_now&&$money_reset>0){
//                $money_error = 0;
//                foreach ($request['times_update'] as $data){
//                    $time_update = $time_now + ($data->HH*60*60) + ($data->mm*60);
//                    if($time_update<time()){
//                        $money_error += 5000;
//                    }
//                }
//                $total_money -=$money_error;
//            }
//
//            return $this->setResponse(200,['value_check'=>$value_check,'total_money'=>$total_money]);
//        }
//        $classified_vip = $classified_vip->offset($offset)->limit($this->limit)->get();
//        $data = [
//            'current_page' => $this->page,
//            'per_page' => $this->limit,
//            'classifieds' => $classified_vip
//        ];
//
//        return $this->setResponse(200,$data);
    }

     /**
     * PUT v2/classifieds-vip
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classifieds-vip
     * `@fields` | List fields classifieds-vip
     * @return \Illuminate\Http\Response
     */
    function update(Request $request,$id){
//        $classified_vip = ClassifiedVip::where('clv_cla_id',$id)->first();
//        $classified_vip = $this->addDataModel($classified_vip,$request);
//
//        if($classified_vip->save()){
//            $classified = Classified::find($id);
//            $classified->cla_fields_check = createScoreFieldCheckClassified(null,$classified_vip);
//            $classified->save();
//            $classified_vip = convertColumnDataBase($classified_vip->fillable,$classified_vip);
//            return $this->setResponse(200,$classified_vip);
//        }else{
//            return $this->setResponse(500,null,'Update fail!!!');
//        }
    }

     /**
     * POST v2/classifieds-vip
     *
     * Thêm dữ liệu vào database
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@cla_id` | ID của classified
     * `@order_update` | Dữ liệu cấu hình làm mới tin
     * @return \Illuminate\Http\Response
     */

    function store(Request $request){
        $validator = Validator::make($request->all(), [
            'cla_id' =>'required|integer',
            'order_update'=>'required'
        ], [
            'cla_id.required' => ' Không tìm thấy tham số `cla_id`',
            'cla_id.integer' => 'Kiểu dữ liệu sai(cla_id), chỉ nhận kiểu int',
            'order_update.required' => ' Không tìm thấy tham số `order_update`'
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return response($this->setResponse(500, '', collect($validator->messages())->collapse()),500);
        }
        $cla_id = $request->input('cla_id');
        $classified_check = Classified::find($cla_id);
        if(empty($classified_check)){
            return response('Not found classified',404);
        }
        $time_now = time();
        $order_update = $request->input('order_update');
        $data_insert = [];
        $total_money = 0;
        foreach ($order_update as $order){
            if($order['status_check']==true&&$time_now<$order['time']){
                $data_insert[] = [
                  'clv_cla_id' => $cla_id,
                    'clv_date' =>$order['time'],
                    'clv_type_vip'=>1
                ];
                $total_money += config('configuration.con_price_cla_update');
            }
        }
        if(count($data_insert)==0) return response('order check false',500);
        try{
            $money = updateMoneyBuyUserId($classified_check->cla_use_id, -$total_money);
            if($money==false){
                return response($this->setResponse(500,null,'Không đủ tiền để giao dịch'),500);
            }
           createUserSpendHistory($classified_check->cla_use_id, $request->ip(), $request->server('HTTP_USER_AGENT'), -$total_money, time() . '-update_news-' . $classified_check->cla_use_id . '-' .$cla_id, 'Đặt lịch làm mới tin ' . $cla_id. ' :' . $classified_check->cla_title, 4,6);
            ClassifiedVip::insert($data_insert);
        }catch (\Exception $error){
            return response(500,'Server error');
        }
        createClassifiedLog($classified_check,$total_money,1);
        return $this->setResponse(200,'Inserted');
    }
     
     /**
     * DELETE v2/classifieds-vip/{classified_id}
     *
     * Xóa dữ liệu trong DB
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classifieds-vip
     * `@fields` | List fields classifieds-vip
     * @return \Illuminate\Http\Response
     */
    function destroy($id,Request $request){
//            $classified_vip = ClassifiedVip::find($id);
//            $classified = Classified::select('cla_use_id')->where('cla_use_id',$id)->first();
//            if ($classified_vip) {
//                if (checkAuthUser($request->access_token, $classified->cla_use_id)) {
//                    if ($classified_vip->delete()) {
//                        return $this->setResponse(200, ['message' => "Xóa thành công"]);
//                    }
//                }
//
//                return response($this->setResponse(401, '', ' Bạn không có quyền tháo tác với dữ liệu này!'), 401);
//
//            }
//            return response($this->setResponse(401, '', 'Không tìm thấy dữ liệu'), 404);
    }
        
}
