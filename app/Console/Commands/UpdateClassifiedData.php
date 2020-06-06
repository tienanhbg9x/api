<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 2/14/2019
 * Time: 1:56 PM
 */
namespace App\Console\Commands;

use App\Models\Classified;
use App\Models\ClassifiedData;
use App\Models\ClassifiedFilter;
use Illuminate\Console\Command;


class UpdateClassifiedData extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "classified:update_data";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Cập nhật sang bảng phân tích";

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
        $page = 1;
        $this->info("Inserting classified data....");
        $max_row = 1000;

        for (; ;) {
            $offset = $page * $max_row - $max_row;
            $myModel = new Classified();
            $myModel = $myModel->select("cla_id","cla_date","cla_list_acreage","cla_price","cla_cit_id","cla_dis_id","cla_ward_id","cla_street_id","cla_cat_id","cla_citid","cla_disid","cla_wardid","cla_streetid")->offset($offset)->limit($max_row)->get();
            if ($myModel->count() == 0) break;
            $data = [];
            foreach ($myModel as $row) {
                $row = $row->toArray();
                $rowInsert = [];
                $rowInsert["cla_id"] = $row["cla_id"];
                $acreage = explode(",",$row["cla_list_acreage"]);
                $acreage= doubleval($acreage[0]);
                if($acreage <= 0 || $row["cla_price"] <= 0) continue;
                $rowInsert["cla_price_unit"] = intval($row["cla_price"] / $acreage);
                $rowInsert["cla_price"] = $row["cla_price"];
                $rowInsert["cla_cit_id"] = $row["cla_cit_id"];
                $rowInsert["cla_dis_id"] = $row["cla_dis_id"];
                $rowInsert["cla_ward_id"] = $row["cla_ward_id"];
                $rowInsert["cla_street_id"] = $row["cla_street_id"];
                $rowInsert["cla_cat_id"] = $row["cla_cat_id"];
                $rowInsert["cla_citid"] = $row["cla_citid"];
                $rowInsert["cla_disid"] = $row["cla_disid"];
                $rowInsert["cla_wardid"] = $row["cla_wardid"];
                $rowInsert["cla_streetid"] = $row["cla_streetid"];
                $rowInsert["cla_acreage"] = $acreage;
                $rowInsert["cla_strdate"] = date("Y-m-d",$row["cla_date"]);
                if($rowInsert["cla_price_unit"] <= 10) continue;
                $data[] = $rowInsert;
            }
            if(!empty($data)){
                ClassifiedData::insert($data);
            }
            $this->info($offset);
            $page++;
        }
    }

}