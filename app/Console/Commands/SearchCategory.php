<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Classified;
use Illuminate\Console\Command;

class SearchCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:searchcategories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->index();
    }

    function index(){
        $query = new Classified();
        $this->info("Inserting....");
        $categories = (new Category())->select("cat_id","cat_name")->get();

        foreach($categories as $cat){
            $arrayData = [];
            $results = $query->select("cla_id","cla_title","cla_teaser")->where("cla_cat_id",$cat->cat_id)->limit(15000)->get();
            foreach ($results as $data){
                $params = [
                    'index' => 'categories_detect',
                    'type' => '_doc',
                    'id' => $data->cla_id,
                    'body' => [
                        "id"=>$data->cla_id,
                        "name"=>$data->cla_title
                    ]
                ];
                $params = [
                    'id' => $cat->cat_id,
                    'name' => $cat->cat_name,
                    'title' => $data->cla_title,
                    'description' => removeHTML($data->cla_teaser)
                ];
                $arrayData[] = $params;
                //app('elastic')->index($params);
                //$this->info("inserted:". $cat->cat_id . "-".$data->cla_title);
                flush();
            }
            unset($results);
            flush();
            if(!empty($arrayData)) file_put_contents(storage_path("train/category_" . $cat->cat_id . ".json"),json_encode($arrayData,JSON_UNESCAPED_UNICODE));
        }

        $this->warn("insert finish");
    }
}
