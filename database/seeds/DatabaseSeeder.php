<?php

use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\Location;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//      $this->mapLocationToAddress();
    }
    function mapLocationToAddress(){
//        DB::connection("mysql")->update("UPDATE address,location SET add_cit_id = loc_cit_id,add_dis_id = loc_dis_id,add_ward_id = loc_ward_id,add_street_id = loc_street_id
//WHERE add_citid = loc_citid AND add_disid = loc_disid AND add_wardid = loc_wardid AND add_streetid = loc_streetid");
        $limit = 2000;

        $types = ['city'=>'citid','district'=>'disid','ward'=>'wardid','street'=>'streetid'];
        foreach ($types as $key=>$type){
            $offset = 0;
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
}
