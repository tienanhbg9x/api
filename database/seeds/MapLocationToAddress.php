<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapLocationToAddress extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection("mysql")->update("UPDATE address,location SET add_cit_id = loc_cit_id,add_dis_id = loc_dis_id,add_ward_id = loc_ward_id,add_street_id = loc_street_id
WHERE add_citid = loc_citid AND add_disid = loc_disid AND add_wardid = loc_wardid AND add_streetid = loc_streetid");
    }
}
