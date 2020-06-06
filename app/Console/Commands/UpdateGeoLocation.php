<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V2\AddressController;
use App\Http\Controllers\Api\V2\LocationController;
use App\Models\GeoLocation;
use Dingo\Api\Http\Middleware\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateGeoLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geolocation:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật quận huyện cho bảng geolocation';

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
        $this->updateGeolocation();
    }

    public function updateGeolocation(){
        $page = 1;
        $this->info("Inserting....");
        $params = ['body' => []];

        for (;;) {
            $myDetectLocation = new LocationController();
            $myDetectAddress  = new AddressController();
            $offset = $page * 1000 - 1000;
            $query = new GeoLocation();
            $result = $query->offset($offset)->limit(1000)->get();
            if($result->count()==0) break;
            foreach ($result as $row){
                echo "----------------------->\n";
                $address = $this->formatCoccocAddress($row->geo_address);
                echo $this->cutAddress($address) . "\n";
                $arrUpdate = [];
                $loc = $myDetectLocation->SearchDetect($address);
                if(!empty($loc)){
                    echo $loc["address"] . "\n";
                    $arrUpdate["geo_cit_id"] = $loc["cit_id"];
                    $arrUpdate["geo_dis_id"] = $loc["dis_id"];
                    $arrUpdate["geo_ward_id"] = $loc["ward_id"];
                    $arrUpdate["geo_street_id"] = $loc["street_id"];
                }else{

                }
                $add = $myDetectAddress->SearchDetect($address);
                if(!empty($add)){
                    echo $add["address"] . "\n";
                    $arrUpdate["geo_citid"] = $add["citid"];
                    $arrUpdate["geo_disid"] = $add["disid"];
                    $arrUpdate["geo_wardid"] = $add["wardid"];
                    $arrUpdate["geo_streetid"] = $add["streetid"];
                    $arrUpdate["geo_proj_id"] = $add["projid"];
                }

                if(!empty($arrUpdate)){
                    DB::table('geolocation')
                    ->where('geo_id', intval($row->geo_id))
                    ->update($arrUpdate);
                }
                echo "----------------------->\n";
                unset($add,$loc,$address,$arrUpdate);
                //exit();
            }
            $this->info($offset);
            $page++;
            unset($result,$query,$myDetectLocation,$myDetectAddress);
            flush();
        }
    }

    public function cutAddress($address){
        $arr = explode(",",$address);
        $total = count($arr);
        if($total < 4) return $address;
        $start = ($total >= 4) ? $total - 4 : 0;
        $new_address = "";
        for ($i = $start; $i < $total; $i++){
            $val = trim($arr[$i]);
            if(intval($val) > 0) continue;
            $new_address .= $val . ", ";
        }
        $new_address = replace_double_space($new_address);
        return $new_address;
    }

    /**
     * Created by Lê Đình Toản
     * Hàm formart lại định dạng của địa chỉ coccoc
     * User: dinhtoan1905@gmail.com
     * Date: 4/6/2019
     * Time: 3:30 PM
     * @param $address
     */
    public function formatCoccocAddress($address){
        $arrayReplace = [
            "Q." => "Quận",
            "H." => "Huyện",
            "Tp." => "",
            "P." => "Phường ",
            "T." => "",
            "Tx." => "Thị xã",
            "Tt." => "Thị trấn",
            "Đ." => "Đường",
        ];
        foreach ($arrayReplace as $key => $value){
            $address = str_replace($key,$value,$address);
        }
        return $address;
    }
}
