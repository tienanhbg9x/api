<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 2/14/2019
 * Time: 1:56 PM
 */
namespace App\Console\Commands;

use App\Http\Controllers\Api\V2\ClassifiedController;
use App\Models\Classified;
use App\Models\ClassifiedVip;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;


class UpdateClassified extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "classified:update_date";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Cập lại thời gian đăng tin (Chức năng làm mới tin đăng)";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        $time_now = time();
        $classified_vip = ClassifiedVip::select('clv_cla_id','clv_id')->where('clv_date','<',$time_now)->where('clv_type_vip',1)->get();
        $arr_cla_id = $classified_vip->map(function($item){return $item->clv_cla_id;})->toArray();
        if(count($arr_cla_id)==0){
            $this->info('No id');
            return true;
        }
        $this->info('ID:'.implode(',',$arr_cla_id));
        Classified::whereIn('cla_id',$arr_cla_id)->update(['cla_date'=>$time_now]);
        $arr_clv_id = $classified_vip->map(function($item){return $item->clv_id;});
        ClassifiedVip::whereIn('clv_id',$arr_clv_id)->delete();
        $classified_controller = new ClassifiedController();
        foreach ($arr_cla_id as $id){
            $classified_controller->syncData($id,['cla_date'=>$time_now]);
        }
    }

}