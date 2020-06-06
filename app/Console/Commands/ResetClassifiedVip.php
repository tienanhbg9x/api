<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V2\ClassifiedController;
use App\Models\Classified;
use App\Models\ClassifiedVip;
use Illuminate\Console\Command;

class ResetClassifiedVip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'classified-vip:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật tin vip';

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
    public function handle()
    {
        $time_now = time();
        $arr_cla_id = ClassifiedVip::select('clv_cla_id as id')->where('clv_date','<',$time_now)->where('clv_type_vip',2)->get()->map(function($item){return $item->id;})->toArray();
        if(count($arr_cla_id)==0){
            $this->info('No id');
            return true;
        }
        $this->info('ID:'.implode(',',$arr_cla_id));
        Classified::whereIn('cla_id',$arr_cla_id)->update(['cla_type_vip'=>1]);
        ClassifiedVip::whereIn('clv_cla_id',$arr_cla_id)->where('clv_type_vip',2)->delete();
        $classified_controller = new ClassifiedController();
        foreach ($arr_cla_id as $id){
            $classified_controller->syncData($id,['cla_type_vip'=>1]);
        }
    }
}
