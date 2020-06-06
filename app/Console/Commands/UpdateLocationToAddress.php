<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 10/12/2019
 * Time: 16:55
 */

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Models\Location;
use App\Models\Address;


class UpdateLocationToAddress extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'address:location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật từ bảng location sang address';

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
        //
//        $this->mapLocationToAddress();
        $this->updateCity();
        $this->updateDistrict();
        $this->updateWard();
        $this->updateStreet();
    }

    function mapLocationToAddress(){
        $limit = 2000;

        $types = ['city'=>'citid','district'=>'disid','ward'=>'wardid','street'=>'streetid'];
        foreach ($types as $key=>$type){
            $offset = 0;
            $this->info('type:'.$type);
            for(;;){
                $locations = Location::select('loc_cit_id','loc_dis_id','loc_ward_id','loc_street_id','loc_citid','loc_disid','loc_wardid','loc_streetid')->where('loc_type',$key)->offset($offset)->limit($limit)->get();
                if($locations->count()==0) break;
                $add_loc_column_name = $key=='city'?'cit_id':($key=='district'?'dis_id':($key=='ward'?'ward_id':'street_id'));
                foreach ($locations as $loc){
                    $count_update = Address::where("add_$type",$loc->{"loc_$type"})->update(["add_$add_loc_column_name"=>$loc->{"loc_$add_loc_column_name"}]);
                    print_r("type $key:$count_update");
                    echo"\n";
                }
                $offset+=$limit;
            }
        }
    }

    function updateCity(){
        $limit = 2000;
        $offset = 0;
        $this->warn('Update city');
        for(;;){
            $this->info($offset);
            $locations = Location::select('loc_cit_id','loc_citid')->where('loc_type','city')->offset($offset)->limit($limit)->get();
            if($locations->count()==0) break;
            foreach ($locations as $loc){
                Address::where('add_citid',$loc->loc_citid)->update([ 'add_cit_id' => $loc->loc_cit_id]);
            }
            $offset+=$limit;
        }
    }

    function updateDistrict(){
        $limit = 2000;
        $offset = 0;
        $this->warn('Update district');
        for(;;){
            $this->info($offset);
            $locations = Location::select('loc_dis_id','loc_disid')->where('loc_type','district')->offset($offset)->limit($limit)->get();
            if($locations->count()==0) break;
            foreach ($locations as $loc){
                Address::where('add_disid',$loc->loc_disid)->update(['add_dis_id'=>$loc->loc_dis_id]);
            }
            $offset+=$limit;
        }
    }

    function updateWard(){
        $limit = 2000;
        $offset = 0;
        $this->warn('Update ward');
        for(;;){
            $this->info($offset);
            $locations = Location::select('loc_ward_id','loc_wardid')->where('loc_wardid','!=',0)->where('loc_type','ward')->offset($offset)->limit($limit)->get();
            if($locations->count()==0) break;
            foreach ($locations as $loc){
                Address::where('add_wardid',$loc->loc_wardid)->update(['add_ward_id'=>$loc->loc_ward_id]);
            }
            $offset+=$limit;
        }
    }

    function updateStreet(){
        $limit = 2000;
        $offset = 0;
        $this->warn('Update street');
        for(;;){
            $this->info($offset);
            $locations = Location::select('loc_street_id','loc_streetid')->where('loc_streetid','!=',0)->where('loc_type','street')->offset($offset)->limit($limit)->get();
            if($locations->count()==0) break;
            foreach ($locations as $loc){
                Address::where('add_streetid',$loc->loc_streetid)->update(['add_street_id'=>$loc->loc_street_id]);
            }
            $offset+=$limit;
        }
    }

}