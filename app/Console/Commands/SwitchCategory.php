<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Link;
use Illuminate\Console\Command;
use App\Models\News;

class SwitchCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'category:switch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "switch table category's data to table link";

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
        $categories = Link::where('lin_citid',0)->get();
        foreach($categories as $category){
            $category_old = Category::find($category->lin_catid);
            if($category_old){
                $category->lin_short_title = $category_old->cat_meta_title;
                $category->lin_length = strlen($category_old->cat_rewrite);
                $category->lin_md5 = md5($category_old->cat_rewrite);
                $category->lin_rewrite = $category_old->cat_rewrite;
                $category->lin_keyword = $category_old->cat_index_keyword;
                $category->lin_all_parent = $category_old->cat_parent_id;
                $category->lin_parentid = $category_old->cat_parent_id;
                $category->save();
            }
        }
    }

    function createSlugNews(){
        $news = News::all();
        foreach($news as $data){
            $data->new_rewrite = str_slug($data->new_title)."-news".$data->new_id;
            if($data->save()){
                $this->info($data->new_id);
            }
        }
        $this->comment("Update finish!!!");
    }

}
