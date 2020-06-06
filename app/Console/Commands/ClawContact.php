<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClawContact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contact:claw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $text_data = '';

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
        dd('exit');
        $date_start = strtotime('2019-02-01');
        $date_end  = strtotime('2019-04-06');
        $asscess_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImlkIjoyOTI4NTQsInVzZXJfbmFtZSI6Ik5nXHUwMGY0IE1pbmggTmdcdTFlY2RjIiwidXNlcl9lbWFpbCI6ImdnMTA0NDM5NDQ5MjU1NTkyMjYyNTQ1Iiwicm9sIjoiYWRtaW4ifSwiaWF0IjoxNTU0MjA3MjEyLCJleHAiOjE1ODU2NTY4MTJ9._ZbanZq4EL9fcGbphqmEwIrUD8H48EYXqQGyH1USJQs";
        $fields = "id,user_id,rew_id,date,user_phone";
        $date = $date_start;
        for(;;){
            $this->text_data = '';
            $this->info(date('d-m-Y',$date));
            $date_query = date('d-m-Y',$date);
            if($date_end==$date){
                $page = 0;
                for(;;){
                    $page++;
                    $this->warn('Page:'.$page);
                    $url = "https://sosanhnha.com/api/v2/user-contacts?fields=$fields&date=$date_query&access_token=$asscess_token&type=filter_date&page=$page";
                    $content = file_get_contents($url);
                    $this->saveData($content,$date,$page);
                    break;
                }
                break;
            }else{
                $page = 0;
                for(;;){
                    $page++;
                    $this->warn('Page:'.$page);
                    $url = "https://sosanhnha.com/api/v2/user-contacts?fields=$fields&date=$date_query&access_token=$asscess_token&type=filter_date&page=$page";
                    $content = file_get_contents($url);
                    if($this->saveData($content,$date,$page)==false){
                        break;
                    }
                }
            }
            $date= $date + 86400;

        }
        $this->info('Finish!!');
        //
    }

    function saveData($data,$date,$page){
        $data_encode = json_decode(remove_utf8_bom($data),1);
        if(count($data_encode['data']['user_contacts'])>0){
            $date_decode = date('dmY',$date);
            foreach ($data_encode['data']['user_contacts'] as $key=>$item){
                if(!isset($item['title'])){
                    continue;
                }
                $this->text_data.="\n";
                $this->text_data.="========\n";
                $this->text_data.="STT: $key \n";
                $this->text_data.="Tên: ".$item['title']." \n";
                $this->text_data.="Date: ".$item['date']." \n";
                $this->text_data.="Phone: ".$item['user_phone']." \n";
            }
            Storage::put("$date-$date_decode-$page.txt",$this->text_data);
            if(!$data_encode['data']['next_page']){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }
}
