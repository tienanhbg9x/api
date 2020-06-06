<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateResourceApi extends Command
{
    private $route_name = null;
    private $version = null;
    private $controller = null;
    private $method = 'all';
    private $model = '';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:create {route_name} {--version_route=default} {--controller=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create resource api';

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

        if (!$this->createOptionCommand()) {
            $this->error('Error!!!');
            return;
        }
        $infor_option = "- Route: $this->route_name \n- Version: $this->version \n- Controller: $this->controller \n- Methods: $this->method \n";
        $this->info($infor_option);
        if ($this->confirm('Continue create resource?')) {
            if ($this->createResource()) {

            } else {
                $this->error('Error!!!');
            }
        }
        $this->info('Finish!!!');
        $this->warn('GoodBye!!!');
        //
    }

    function createOptionCommand()
    {
        $this->route_name = $this->argument('route_name');
        $this->version = $this->option('version_route');
        $this->controller = $this->option('controller');
        if ($this->controller == 'null') {
            $this->error('The "--controller" option does not accept a value.');
            return false;
        }
        if ($this->confirm('Enter [no] to create all methods resource api Or [yes] to enter methods?')) {
            $this->method = $this->ask('Enter name methods of controller(Example:show,index,delete,update,store,destroy,...)?');
        }
        if ($this->confirm('Do you wish to create model?')) {
            $this->model = $this->ask('Enter name model?');
        }
        return true;
    }

    function createResource()
    {
        if(!$this->createController()){
            return false;
        }
        $this->createModel();
        $this->createRoute();
        return true;
    }

    function createController()
    {
        $version = strtoupper($this->version);
        $controller = $this->controller;
        $path_class = $this->version=='default'?"App\Http\Controllers\\{$controller}":"App\Http\Controllers\Api\\{$version}\\{$controller}";
        if (class_exists($path_class)) {
            $this->error("Controller $controller existed!!!");
            return false;
        }
        $controller_command = $this->version=='default'?"$controller":"Api/$version/$controller";
        $var = exec("(cd " . base_path() . " && php artisan make:controller $controller_command)");
        $this->comment($var);
        if($this->method=='all'){
            $name_methods = explode(',', 'show,index,update,store,destroy,create');
        }else{
            $name_methods = explode(',', $this->method);
        }
        $methods = "";
        foreach ($name_methods as $method) {
            if ($method == 'store') {
                $methods .= "
    function $method(Request \$request){
    
    }
        ";
            } else if ($method == 'destroy' || $method == 'show' || $method == 'edit') {
                $methods .= "
    function $method(\$id){
    
    }
        ";
            } else if ($method == 'update') {
                $methods .= "
    function $method(\$id,Request \$request){
    
    }
        ";
            } else {
                $methods .= "
    function $method(){
    
    }
        ";
            }
        }
        try{
            $path_class = $this->version=='default'?app_path("Http/Controllers/$controller.php"):app_path("Http/Controllers/Api/$version/$controller.php");
            $content = file_get_contents($path_class);
            $content = str_replace('extends Controller','',$content);
            $content = str_replace('use App\Http\Controllers\Controller;','',$content);
            $content = str_replace('}',$methods .'}',$content);
            file_put_contents($path_class,$content);
        }catch (\Exception $error){
            $this->warn($error->getMessage());
            return false;
        }
        return true;

    }

    function createModel(){
        if($this->model!=''){
             exec("(cd " . base_path() . " && php artisan make:model Models/$this->model)");
            $this->warn('Model created successfully.');
        }
    }

    function createRoute(){
        if($this->method!='all'){
            $access_methods = '';
            $arr_methods = explode(',',$this->method);
            foreach ($arr_methods as $key=>$method){
                if($key==(count($arr_methods)-1)){
                    $access_methods.="'$method'";
                    break;
                }
                $access_methods.="'$method',";
            }
        }
        $str_route = $this->method=='all'?"\$router->resource('$this->route_name', '$this->controller');":"\$router->resource('$this->route_name', '$this->controller',
            ['only' => [$access_methods]]);";
        if($this->version=='default'){
            file_put_contents(base_path('routes/web.php'),$str_route,FILE_APPEND);
        }else{
            if(file_exists(base_path("routes/$this->version.php"))){
                file_put_contents(base_path("routes/$this->version.php"),$str_route,FILE_APPEND);
            }else{
                $file = fopen(base_path("routes/$this->version.php"),"w") or die("Unable to open file!");
                fwrite($file, '<?php '.$str_route);
                fclose($file);
            }
        }
        $this->warn('Route created successfully.');
    }
}
