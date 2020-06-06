<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Category;
use App\Models\Classified;
use App\Models\RewriteNoaccent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateRewriteNoaccent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:rewrite-noaccent';

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
        $time_now = time();
        $page = 1;
        $this->info("Inserting classified data....");
        $max_row = 1000;

        for (; ;) {
            $offset = $page * $max_row - $max_row;
            $myModel = new Category();
            $myModel = $myModel->select("cat_id","cat_slug","cat_name","cat_type")->whereIn('cat_type',['bannhadat','chothue'])->get();
            $categories = [];
            foreach ($myModel as $row) {
                $row = $row->toArray();
                $categories[$row["cat_id"]] = $row;
            }
            $myModel = new Address();
            $myModel = $myModel->select("add_id","add_citid","add_disid","add_projid","add_wardid","add_streetid","add_rewrite","add_name","add_address","add_short_address","add_cit_name")->offset($offset)->limit($max_row)->get();
            if ($myModel->count() == 0) break;
            $data = [];
            foreach ($myModel as $row) {
                $row = $row->toArray();
                foreach ($categories as $cat){
                    $slug = createRewriteNoaccent($cat["cat_slug"] . " tại " . $row["add_rewrite"]);
                    $dbclassified = new Classified();
                    $save = false;
                    $dbclassified = $dbclassified->select("cla_id");
                    if($cat["cat_id"] > 0) $dbclassified = $dbclassified->where("cla_cat_id",$cat["cat_id"]);
                    if($row["add_citid"] > 0){
                        $save = true;
                        $dbclassified = $dbclassified->where("cla_citid",$row["add_citid"]);
                    }
                    if($row["add_disid"] > 0) $dbclassified = $dbclassified->where("cla_disid",$row["add_disid"]);
                    if($row["add_streetid"] > 0) $dbclassified = $dbclassified->where("cla_disid",$row["add_streetid"]);
                    if($row["add_wardid"] > 0) $dbclassified = $dbclassified->where("cla_wardid",$row["add_wardid"]);
                    if(!$save) continue;
                    $dbclassified = $dbclassified->first();
                    if(!empty($dbclassified) && !empty($dbclassified->toArray())){
                        $rewriteNoaccent = new RewriteNoaccent();
                        $rewriteNoaccent->rew_title = $cat["cat_name"] . " " . $row["add_short_address"];
                        $rewriteNoaccent->rew_rewrite = $slug;
                        $rewriteNoaccent->rew_md5 = md5($slug);
                        $rewriteNoaccent->rew_table = "category_address";
                        $rewriteNoaccent->rew_length = strlen($slug);
                        $rewriteNoaccent->rew_count_word = getCountWordRewrite($slug);
                        $rewriteNoaccent->rew_cat_id = $cat["cat_id"];
                        $rewriteNoaccent->rew_citid = $row["add_citid"];
                        $rewriteNoaccent->rew_disid = $row["add_disid"];
                        $rewriteNoaccent->rew_wardid = $row["add_wardid"];
                        $rewriteNoaccent->rew_streetid = $row["add_streetid"];
                        $rewriteNoaccent->rew_projid = $row["add_projid"];
                        $rewriteNoaccent->rew_param = bdsEncode(["iCat" => $cat["cat_id"]
                                    ,"citid" => $row["add_citid"]
                                    ,"disid" => $row["add_disid"]
                                    ,"wardid" => $row["add_wardid"]
                                    ,"streetid" => $row["add_streetid"]
                        ]);

                        $rewriteNoaccent->save();
                        $this->warn($slug);
                    }else{
                        $this->info($slug);
                    }
                    unset($dbclassified);

                }
                //`exit();
            }
            if(!empty($data)){
                //ClassifiedData::insert($data);
            }
            $this->info($offset);
            $page++;
        }
        //
    }
}
