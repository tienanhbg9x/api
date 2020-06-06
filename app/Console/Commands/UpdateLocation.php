<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 07/08/2018
 * Time: 16:10
 */

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;


class UpdateLocation extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "sphinx:locations";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Update location";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        $this->info('Starting loading ...');
        for(;;){
            $results = DB::select("SELECT * FROM queue_classifieds
                       STRAIGHT_JOIN classifieds ON (id = cla_id)
                       LIMIT 1");
//$results = collect($results);
            if(!$results)break;
            foreach($results as $row){
                //_debug($row);
                $keyword = $row->cla_address;
                //$keyword = 'Dự án Hà Nội Homeland, Phường Thượng Thanh, Long Biên, Hà Nội';
                //_debug($row);exit();
//                echo $keyword . '<hr>';
                // create a SphinxQL Connection object to use with SphinxQL
                $arrLoc = ["catid" => $row->cla_cat_id];
                //*
                $myDetect = new \Toanld\Location\DetectLocation($keyword);
                $arrLoc["citid"] = $myDetect->getCity();
                $arrLoc["disid"] = $myDetect->getDistrict();
                $arrLoc["wardid"] = $myDetect->getWard();
                $arrLoc["streetid"] = $myDetect->getStreet();
                $arrLoc["projid"] = $myDetect->getProject($row->cla_title);

//                print_r($arrLoc);
                $update = [];

                $update["cla_citid"] = $arrLoc["citid"];
                $update["cla_disid"] = $arrLoc["disid"];
                $update["cla_wardid"] = $arrLoc["wardid"];
                $update["cla_streetid"] = $arrLoc["streetid"];
                $update["cla_projid"] = $arrLoc["projid"];
                //*/
                $links = new \Toanld\MakeLink\MakeLink($arrLoc);

                $update["cla_linid"] = $links->createLink();
                print_r($update);

                DB::table('classifieds')
                    ->where('cla_id',$row->cla_id)
                    ->update($update);
                DB::table('queue_classifieds')
                    ->where('id',$row->cla_id)
                    ->delete();
            }
            DB::disconnect('foo');
            app()->make("SphinxConnect")->close();
        }
        $this->info('Update finish!!!');
    }

}