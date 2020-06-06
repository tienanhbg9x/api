<?php

namespace App\Console\Commands;

use App\Models\ClassifiedFilter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ArchiveTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xoa bot du lieu classifieds_filter';

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

        $this->deleteClassifiedsFilter();
        $this->info('Finish!!');
        //
    }

    function deleteClassifiedsFilter(){
        $this->info('Delete classifieds_filter!');
        //chi du lai du lieu 600 ngay thoi
        $date_delete = time() - (86400*200);
        ClassifiedFilter::where("cla_date","<",$date_delete)->whereIn("cla_cit_id",[25,30])->delete();
        $date_delete = time() - (86400*365);
        ClassifiedFilter::where("cla_date","<",$date_delete)->delete();
    }
}
