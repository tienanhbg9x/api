<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 7/16/2019
 * Time: 7:46 PM
 */

namespace App\Console\Commands;
use Illuminate\Console\Command;


use App\Models\Address;

class CronGooglePlace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron-google-place';
    //protected $GOOGLE_API_KEY = "AIzaSyAyFL5-s-1vHQEQWaDnXT2gCDIrNZm4DSs";
    protected $GOOGLE_API_KEY = "AIzaSyC5Uu54VnlfZdB6lt0sjnT2_djzrOwMTrk";
    protected $arrayKey = [
        "AIzaSyAyFL5-s-1vHQEQWaDnXT2gCDIrNZm4DSs",
        "AIzaSyC5Uu54VnlfZdB6lt0sjnT2_djzrOwMTrk"
    ];
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
        $this->indexSearchPlace();
    }

    function indexSearchLanber()
    {
        $page = 1;
        $this->info("Inserting....");
        // create curl resource
        $ch = curl_init();
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        for (; ;) {
            $offset = $page * 1000 - 1000;
            $locations = new Address();
            $locations = $locations->select("add_id","add_address","add_type")->whereNull("add_place_id")->where('add_status',0)->offset($offset)->limit(1000)->get();
            if ($locations->count() == 0) break;
            foreach ($locations as $row) {
                $row = $row->toArray();
                $this->info($row["add_address"]);
                $address = urlencode($row["add_address"]);
                $url = "https://landber.com/api/place/autocomplete?input=$address";
                // set url
                curl_setopt($ch, CURLOPT_URL, $url);
                // $output contains the output string
                $data = curl_exec($ch);
                $data = json_decode($data,true);
                $predictions = isset($data["predictions"]) ? $data["predictions"] : [];
                if(count($predictions) > 1){
                    print_r($predictions);break;
                }
                foreach ($predictions as $map){
                    $geometry = ["location" => $map["location"],"viewport" => $map["viewport"]];
                    $result = Address::where("add_id",$row["add_id"])->update(["add_geometry" => json_encode($geometry),"add_place_address" => $map["fullName"],"add_place_name"=>$map["placeName"],"add_status" => 2]);
                    //print_r($map);
                    var_dump($result);
                    break;
                }

            }
            $this->info($offset);
            $page++;
        }
        // close curl resource to free up system resources
        curl_close($ch);
    }

    function indexSearchPlace()
    {
        $page = 1;
        $total_key = $this->GOOGLE_API_KEY;
        $pathSave = dirname(__FILE__) . "/../../../storage/data/";
        $pathSavePlace = dirname(__FILE__) . "/../../../storage/place/";
        if(!file_exists(dirname($pathSavePlace . "file.abc"))) mkdir(dirname($pathSavePlace . "file.abc"), 0777, true);
        if(!file_exists(dirname($pathSave . "file.abc"))) mkdir(dirname($pathSave . "file.abc"), 0777, true);
        $this->info("Inserting....");
        // create curl resource
        $ch = curl_init();
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        foreach ($this->arrayKey as $api_key){
            $offset = $page * 1000 - 1000;
            $locations = new Address();
            $locations = $locations->select("add_id","add_address","add_type")->whereNull("add_place_id")->where('add_status',0)->offset($offset)->limit(1000)->get();
            if ($locations->count() == 0) break;
            $this->info("Key: $api_key");
            foreach ($locations as $row) {
                $row = $row->toArray();
                $row["add_address"] = $row["add_address"] . ", Vietnam";
                $address = urlencode($row["add_address"]);
                //$url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$address&key=" . $this->GOOGLE_API_KEY . "&sessiontoken=1234567890&num=15&hl=vi";
                $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=$address&fields=formatted_address,geometry,name,photos,rating&key=" . $api_key . "&sessiontoken=" . $row["add_id"] . "&num=15&hl=vi";
                // set url
                curl_setopt($ch, CURLOPT_URL, $url);
                // $output contains the output string
                $data = curl_exec($ch);
                $data = json_decode($data,true);
                if(isset($data["status"])){
                    if($data["status"] == "OVER_QUERY_LIMIT"){
                        break;
                    }
                    if($data["status"] == "ZERO_RESULTS"){
                        $result = Address::where("add_id",$row["add_id"])->update(["add_status" => 1]);
                        var_dump($result);
                    }
                }
                $predictions = isset($data["results"]) ? $data["results"] : [];
                //nếu có kết quả thì lưu
                if(isset($predictions[0])){
                    file_put_contents($pathSave . $row["add_id"] . ".json",json_encode($predictions[0]));
                    $map = $predictions[0];
                    $result = Address::where("add_id",$row["add_id"])->update(["add_geometry" => json_encode($map["geometry"]),"add_place_address" => $map["formatted_address"],"add_place_name"=>$map["name"],"add_place_id" => $map["place_id"],"add_status" => 1]);
                    var_dump($result);
                    echo $row["add_id"];
                    /*
                    foreach ($predictions as $key => $pla){
                        echo $this->getDetailPlace($pla["place_id"],$ch,$pathSavePlace);
                    }
                    //*/
                }else{
                    print_r($data);
                }
            }
            $this->info($offset);
            $page++;
        }
        // close curl resource to free up system resources
        curl_close($ch);
    }

    function getDetailPlace($place_id,$ch,$pathSave){
        $url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=$place_id&key=" . $this->GOOGLE_API_KEY . "&sessiontoken=1234567890&hl=vi";
        curl_setopt($ch, CURLOPT_URL, $url);
        // $output contains the output string
        $data = curl_exec($ch);
        file_put_contents($pathSave . $place_id . ".json",$data);
        //print_r($data);exit();
    }
}