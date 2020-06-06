<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Category;
use App\Models\Classified;
use App\Models\RewriteNoaccent;
use Google_Client;
use Google_Service_Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use samdark\sitemap\Index;
use samdark\sitemap\Sitemap;

class CreateSitemapVatgia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vatgia:sitemap';

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
        $storage_path = storage_path();
        $sitemap_path = $storage_path . "/sitemap/";
        $google_key_path = $storage_path . "/google/sosanhnha-cc58dade1d96.json";
        if(!file_exists(dirname($sitemap_path))) mkdir(dirname($sitemap_path), 0777, true);

        $domainSitemap = "https://vatgia.com/batdongsan/";
        $sitemap = new Sitemap($sitemap_path . '/realestate.xml');
        $sitemap->setMaxUrls(20000);
        //$sitemap->setUseGzip(true);
        //$sitemap->setBufferSize(20);
        //$sitemap->setMaxBytes(20485760);
        for (; ;) {
            $offset = $page * $max_row - $max_row;
            $myModel = new RewriteNoaccent();
            $myModel = $myModel->where('rew_table','=','category_address')->offset($offset)->limit($max_row)->get();
            if ($myModel->count() == 0) break;
            $data = [];

            foreach ($myModel as $row) {
                $row = $row->toArray();
                // add some URLs
                $sitemap->addItem($domainSitemap . $row["rew_rewrite"], time(), Sitemap::DAILY, 0.3);

            }

            $this->info($offset);
            $page++;
        }
        $sitemap->write();
        $files = scandir ($sitemap_path);
        $index = new Index($sitemap_path . 'sitemap.xml');
        foreach($files as $file){
            if(strpos($file,".xml") !== false && $file != "sitemap.xml" && $file != "sitemap-new.xml") $index->addSitemap($domainSitemap . $file);
        }
        $index->write();

        //
    }
}
