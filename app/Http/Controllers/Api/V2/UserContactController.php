<?php

namespace App\Http\Controllers\Api\V2;

use App\Jobs\sendMailUserContact;
use App\Models\Classified;
use App\Models\Rewrite;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserContactBuy;
use App\Models\UserCustomer;
use Illuminate\Http\Request;

/**
 * @resource v2 User-contacts
 *
 * Api for User-contacts
 */
            

class UserContactController extends Controller
{
    //

     
     /**
     * GET v2/user-contacts
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-contacts
     * `@fields` | List fields user-contacts
     * @return \Illuminate\Http\Response
     */
    function show($id){
    
    }
        
    
     /**
     * GET v2/user-contacts
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | UserContactController
     * `route_name` | user-contacts
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu user-contacts ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong user-contacts ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi user-contacts cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang user-contacts cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request){
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
            'type'=>'string|max:255|min:3'
        ]);
        $offset = $this->page * $this->limit - $this->limit;
        $user_contacts = new UserContact();
        $user_contacts = $user_contacts->select($user_contacts->alias($this->fields))->where('usc_user_phone','!=','')->where('usc_rew_id','!=',0);
        if ($this->where != null) $user_contacts = whereRawQueryBuilder($this->where, $user_contacts, 'mysql', 'user_contacts');
        if($request->input('type')=='filter_date'){
            if($request->input('date_start')!=null&&$request->input('date_end')!=null){
                return 'ok';
            }else if($request->input('date')){
                $date = strtotime($request->input('date'));
                $date_start = $date;
                $date_end = $date + 86400;
                $user_contacts = $user_contacts->whereBetween('usc_date',[$date_start,$date_end]);
            }else{
                return response($this->setResponse(500,null,'Not params date_star date_end'),500);
            }
        }
        if($request->input('type')=='my_contact'){
           $user_id = getUserId($this->access_token);
           $arr_contact_id = UserContactBuy::select('ucb_usc_id')->where('ucb_use_id',$user_id)->offset($offset)->limit($this->limit)->get()->map(function($item){
               return $item->ucb_usc_id;
           })->toArray();
           if(count($arr_contact_id)!=0){
               $user_contacts = $user_contacts->whereIn('usc_id',$arr_contact_id)->get();
               $user_contacts =  $this->filterUser($user_contacts,$arr_contact_id);
           }else{
               $user_contacts = collect([]);
           }
        }else{
            $user_contacts = $user_contacts->orderBy('usc_id','desc')->offset($offset)->limit($this->limit)->get();
            $user_contacts =  $this->filterUser($user_contacts);
        }

        $data = [
            'current_page' => $this->page,
            'per_page' => $user_contacts->count(),
            'user_contacts' => $user_contacts
        ];
        return $this->setResponse(200,$data);
    }
        
    
     /**
     * PUT v2/user-contacts
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-contacts
     * `@fields` | List fields user-contacts
     * @return \Illuminate\Http\Response
     */
    function update(Request $request,$id){
    
    }


    /**
     * DELETE v2/user-contacts/{id}
     *
     * Xóa dữ liệu
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-contacts
     * @return \Illuminate\Http\Response
     */
    function destroy($id){
        $user_contact = UserContact::find($id);
        if($user_contact->delete()){
            return $this->setResponse(200,"Deleted $id");
        }
        return response("error",500);
    }

    function store(Request $request){
        $type = $request->input('type','');
        if($type=='classified_request'&&$request->input('secret_key')!=null){
            $token_info = getInfoTokenUserContact($request->input('secret_key'));
            if($token_info){
                    $info_contact = [];
                    $cla_id = $token_info->cla_id;
                    $classified = Classified::select('cla_title','cla_contact_name','cla_rewrite','cla_email','cla_use_id','cla_rew_id','cla_cit_id','cla_cat_id','cla_dis_id','cla_proj_id','cla_ward_id')->where('cla_id',$cla_id)->first();
                    if(empty($classified)) return response('Not found classified id',404);
                    $guest_id =(int) $request->input('guest_id',0);
                    $user_contact = UserContact::select('usc_user_phone')->where('usc_rew_id',$classified->cla_rew_id)->where('usc_guest_id',$guest_id)->first();
                    if($guest_id!=0&&empty($user_contact)){
                        $user_contact = new UserContact();
                        $user_contact->usc_rew_id = $classified->cla_rew_id;
                        $user_contact->usc_cat_id = $classified->cla_cat_id;
                        $user_contact->usc_proj_id = $classified->cla_proj_id;
                        $user_contact->usc_cit_id = $classified->cla_cit_id;
                        $user_contact->usc_user_phone = $request->input('user_phone');
                        $user_contact->usc_dis_id =  $classified->cla_dis_id;
                        $user_contact->usc_ward_id = $classified->cla_ward_id;
                        $user_contact->usc_user_id =isset($token_info->user_id)?$token_info->user_id:0;
                        $user_contact->usc_guest_id =$guest_id;
                        $user_contact->usc_date = time();
                        $user_contact->save();
                    }
                    if(isset($token_info->user_id)){
                        $user =  User::select('use_fullname','use_email','use_email_payment','use_phone','use_mobile','use_address')->where('use_id',(int)$token_info->user_id)->first();
                        if($user){
                            if($user->use_fullname!=null) $info_contact['Tên'] = $user->use_fullname;
                            if($user->use_address!=null) $info_contact['Địa chỉ'] = $user->use_address;
                            if($user->use_email!=null&&strpos($user->use_email,"@facebook")===false&&filter_var(trim($user->use_email), FILTER_VALIDATE_EMAIL)!==false) $info_contact['Email 1'] = $user->use_email;
                            if($user->use_email_payment!=null) $info_contact['Email 2'] =$user->use_email_payment;
                            if($user->use_phone!=null) $info_contact['Phone'] = $user->use_phone;
                            if($user->use_mobile) $info_contact['Mobile'] = $user->use_mobile;
                        }
                    }else{
                        if($request->input('user_phone')!==null){
                            $info_contact['Phone'] = $request->input('user_phone');
                        }else{
                            $user_contact = UserContact::select('usc_user_phone')->where('usc_guest_id',$guest_id)->orderBy('usc_id','desc')->first();
                            if($user_contact){
                                if($user_contact->usc_user_phone!=null) $info_contact['Mobile'] = $user_contact->usc_user_phone;
                            }
                        }
                    }

                    if(count($info_contact)!=0){
                            $user_cla = User::select('use_email','use_email_payment')->where('use_id',$classified->cla_use_id)->first();
                            $data_send_job = ['cla_title'=>$classified->cla_title];
                            $data_send_job['user_email']= (filter_var(trim($classified->cla_email), FILTER_VALIDATE_EMAIL)!==false&&strpos($classified->cla_email,"@facebook")===false)?$classified->cla_email:null;
                            $data_send_job['cla_rewrite']= $classified->cla_rewrite;
                            $data_send_job['user_name']= $classified->cla_contact_name==''?'Your name':$classified->cla_contact_name;
                            if($user_cla){
                                if($data_send_job['user_email']==null){
                                    $data_send_job['user_email'] = ($user_cla->use_email!=null&&strpos($user->use_email,"@facebook")===false)?$user_cla->use_email:($user_cla->use_email_payment!=null?$user_cla->use_email_payment:null);
                                }
                                if($data_send_job['user_email']!=null){
                                    if($data_send_job['user_email']!=$user_cla->use_email_payment){
                                        $data_send_job['user_mail_cc'] = $user_cla->use_email_payment;
                                    }
                                }
                            }
                            if($data_send_job['user_email']!=null){
                                $data_send_job['user_contact'] = $info_contact;
                                dispatch(new sendMailUserContact($data_send_job));
//                                sendMailUserContact::dispatch($data_send_job);
                                $user_customer = new UserCustomer();
                                $user_customer->ucm_use_id = $classified->cla_use_id;
                                if(!empty($user)){
                                     $user_customer->ucm_name = $user->use_name;
                                     $user_customer->ucm_address =$user->use_address;
                                     $user_customer->ucm_email = $user->use_email.','.$user->use_email_payment;
                                }
                                $phone = (isset($info_contact['Phone'])?$info_contact['Phone'].',':'').(isset($info_contact['Mobile'])?$info_contact['Mobile']:'');
                                $user_customer->ucm_phone = $phone;
                                $user_customer->ucm_cla_id = $cla_id;
                                $user_customer->ucm_type = 1;
                                $user_customer->ucm_created_at = time();
                                $user_customer->save();
                                return $this->setResponse(200,'sent contact');
                            }
                    }
                    return 'Not found contact';
            }
            return response('Not auth', 401);
        }else if($type=='check_contact'){
            $status_check = ['has_phone'=>false,'has_guest'=>false,'secret_key'=>''];
            $guest_id = (int) $request->input('guest_id',0);
            if($guest_id!=0){
                if($request->input('cla_id')==null) return response('Not found cla_id',500);
                $classified = Classified::select('cla_rew_id')->where('cla_id',$request->input('cla_id'))->first();
                if(empty($classified)) return response("Not found classified id",404);
                if($request->input('user_id')){
                    $status_check['secret_key'] = createSecretKeyApi($request->input('cla_id'),$request->input('user_id'));
                }else{
                    $status_check['secret_key'] = createSecretKeyApi($request->input('cla_id'));
                }
                $contact = UserContact::where('usc_guest_id',$guest_id)->orderBy('usc_id','desc')->first();
                if($contact){
                    $status_check['has_guest'] = true;
                    if(!empty($contact->usc_user_phone)&&isMobilePhone($contact->usc_user_phone)){
                        $status_check['has_phone'] = true;
                        $status_check['phone'] = $contact->usc_user_phone;
                    }
                }
            }else{
                return response('Not found guest_id',500);
            }
            return $this->setResponse(200,$status_check);
        }else if($type=='chat_contact_request'&&$request->input('secret_key')){
            $token_info = getInfoTokenUserContact($request->input('secret_key'));
            if($token_info){
                $this->validate($request, [
                    'rew_id' => 'required|numeric',
                    'user_phone' => 'required',
                    'user_id' => 'required'
                ]);
                $rewrite = Rewrite::find($request->rew_id);
                if($rewrite){
                    $user_contact = new UserContact();
                    $user_contact->usc_rew_id = $request->rew_id;
                    $user_contact->usc_cat_id = $rewrite->rew_cat_id;
                    $user_contact->usc_proj_id = $rewrite->rew_proj_id;
                    $user_contact->usc_cit_id = $rewrite->rew_cit_id;
                    $user_contact->usc_user_phone = $request->input('user_phone');
                    $user_contact->usc_dis_id =  $rewrite->rew_dis_id;
                    $user_contact->usc_ward_id = $rewrite->rew_ward_id;
                    $user_contact->usc_user_id = $request->user_id<1000000?$request->user_id:0;
                    $user_contact->usc_guest_id = $request->user_id;
                    $user_contact->usc_date = time();
                    $user_contact->save();
                    return 'ok';
                }else{
                    return response($this->setResponse(500,null,'Not found rew_id'),500);
                }

            }
        }
        return $request->all();
    }

    function filterUser($user_contacts,$arr_ucb_id=null){
        $rew_id = $user_contacts->groupBy('rew_id')->keys()->toArray();
        $usc_id = $user_contacts->groupBy('id')->keys()->toArray();
        $rewrite = Rewrite::select('rew_id as id','rew_title as title','rew_rewrite as rewrite')->whereIn('rew_id',$rew_id)->get();
        if($arr_ucb_id!=null){
            $user_contacts_buy = $arr_ucb_id;
        }else{
            $user_id = getUserId($this->access_token);
            $user_contacts_buy = UserContactBuy::select('ucb_usc_id')->where('ucb_use_id',$user_id)->whereIn('ucb_usc_id',$usc_id)->get()->groupBy('ucb_usc_id')->keys()->toArray();
        }
        $user_contacts = $user_contacts->map(function($item)use($rewrite,$user_contacts_buy){
          $rew_find = $rewrite->where('id',$item->rew_id)->first();
          if(in_array($item->id,$user_contacts_buy)){
              $item->is_buy = 1;
          }else{
              $item->is_buy = 0;
              $item->user_phone = substr( $item->user_phone,  0, 5).'*****';
          }
          if($rew_find){
              $item->title = str_replace('Bán ','Cần mua ',$rew_find->title);
              $item->title = str_replace('Cho thuê ','Cần thuê ', $item->title);
              $item->url = $rew_find->rewrite;
          }
          if($item->date){
//              $item->date = date('d-m-Y h:i:s',$item->date);
              $item->date = today_yesterday_v2($item->date);
          }

          return $item;
        });
        return $user_contacts;
    }

}
