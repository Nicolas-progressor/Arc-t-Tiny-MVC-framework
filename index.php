<?php

// Define root dirs
define('ROOT_DIR', realpath(dirname(__FILE__)) .'/');
define('APP_DIR', ROOT_DIR .'app/');
define('SUB_DIR', basename(__DIR__));

// Define root urls
define('ROOT_URL', 'http://'.$_SERVER['HTTP_HOST'].'/');
define('APP_URL', 'http://'.$_SERVER['HTTP_HOST'].'/' . 'app/');
define('SCRIPT_URL', $_SERVER['PHP_SELF']);

$config = new Config();
$router = new Router();

if(!isset($router->segments[0])){
    $router->segments[0] = 'index';         
}
if(!isset($router->segments[1])){
    $router->segments[1] = 'index';
}
if(!isset($router->segments[0]) || $router->segments[0] == 'index'){
    $router->segments[0] = $config->default_rte;          
}
$router->addRule(array(
    'id' => 0,
    'rule' => 'controller'
)); 
$router->addRule(array(
    'id' => 1,
    'rule' => 'action'
));
$router->segVar = array_slice($router->segments, 2);
$router->RouteFile();

$mvc = new controller($router->controller, $router->action, $router->getVar, $router->segVar);

class Router {
    
    public $segments;
    public $getVar;
    public $segVar;
    public $rte_file;


    public function __construct() {
        $this->getUrl();        
    }
    
    public function load() {
        return new self();
    }
    
    function getUrl(){  
        
        $script_url = SCRIPT_URL;              
        $request_url = $_SERVER['REQUEST_URI'];         
        
        if ($script_url == $request_url) {$request_url = substr($request_url,0,strpos($request_url,'.'));}          
        
        $request_url = trim(
                preg_replace('/'. 
                        str_replace('/', '\/', str_replace('index.php', '', $script_url)) 
                        .'/', '', $request_url, 1), '/');
        
        parse_str(parse_url($request_url,PHP_URL_QUERY), $this->getVar);
        
        $request_url = parse_url($request_url, PHP_URL_PATH);  
        $request_url = preg_replace('/\/$/', '', $request_url);
        $this->request_url = $request_url; 
              
        if($request_url){            
            $segments = explode('/', $request_url);        
        } else {
            $segments = array();
        }     
        
        $this->segments = $segments;
    }
    
     public function addRule($setup = NULL) {
        $id = NULL; $remap = FALSE; $rule = NULL; $get = NULL;
        if (isset($setup['id'])){ $id = $setup['id']; }
        if (isset($setup['remap'])){ $remap = $setup['remap']; }
        if (isset($setup['rule'])) { $rule = $setup['rule']; }
        if (isset($setup['get'])) { $get = $setup['get']; }
       
        if ($id !== NULL && $rule !== NULL){ 
            $this->$rule = $this->segments[$id];
            if ($remap){ 
                unset($this->segments[$id]); 
                $this->segments = array_values($this->segments);                 
            }
        }
        
        if ($get !== NULL && $rule !== NULL){ 
            $this->$rule = $this->getVar[$get];
            if ($remap){ 
                unset($this->getVar[$get]);                
            }
        }
    }
    
    public function RouteFile($filename = NULL){
        if(!$filename){
            $filename = $this->segments[0];
        }
        $rte_file = APP_DIR . '/routes/' . $filename . '.php';
       
        if (file_exists($rte_file)){
            $this->rte_file = include $rte_file;   
            $fileAction = $this->controller;            
                if(!key_exists($fileAction, $this->rte_file)){
                    $fileAction = 'index';
                }            
                if(key_exists($fileAction, $this->rte_file)){
                    if(isset($this->rte_file[$fileAction]['controller'])){
                        $this->controller = $this->rte_file[$fileAction]['controller'];
                    }
                    if(isset($this->rte_file[$fileAction]['action'])){
                        $this->action = $this->rte_file[$fileAction]['action'];
                    }
                } else {                    
                    return FALSE;
                }
        } 
    }        
}

class Config{

    private $data = array();
    
    private static $instances = array();
    
    public static function getInstance($key = 'config'){
        if (!isset(self::$instances[$key])) {            
            self::$instances[$key] = new self($key);            
        }        
        return self::$instances[$key];
    }

    public function __construct($cfg_file='config'){
        $this->data = $this->load($cfg_file);
    }

    public function __get($name) {
		if (!isset($this->data[$name])){ return false; }
        return $this->data[$name];
    }
    
    public function __isset($name) {
		return isset($this->data[$name]);                 
    }
        
    public function load($cfg_file = 'config'){
        $cfg_file = APP_DIR . $cfg_file . '.php';
        
        $data = include $cfg_file;

        return $data;
    }
    
}

class controller{
    
    private $folder = NULL;    
    private $name = NULL;
    private $controller = NULL;
    private $action = NULL;
    private $var;
    private $getVar = NULL;
    private $segVar = NULL;

    public function __construct($name, $action, $getVar = array(), $segVar = array()) {
        if(file_exists(APP_DIR . $name . '/') && file_exists(APP_DIR . $name . '/' . 'controller.php')){
            $this->folder = APP_DIR . $name . '/';
            $this->name = $name;
            $this->action = $action;
            $this->segVar = $segVar;
            $this->getVar = $getVar;
          
            $this->controller = $this->controller(APP_DIR . $name . '/' . 'controller.php', $name);
            
            $this->var = $this->getParameters($this->controller);

            self::start($this->controller, $this->action, $this->var);
        }        
    }
    
    public function controller($filename){
        include_once $filename;
        $class_name = 'controller_' . $this->name;
        $controller = new $class_name;             
        $this->model($controller);     
        $this->view($controller);
        return $controller;
    }
    
    function model($controller) {
        if (file_exists($this->folder . 'model.php')){
            include_once $this->folder . 'model.php';
            $model_name = 'model_' . $this->name;
            $controller->model = new $model_name;
            $sql_i = sql::getInstance();
            $controller->model->sql = $sql_i->DB;
        }
    }
    
    function view($controller){        
        if (file_exists($this->folder . 'view.php')){            
            $controller->view = new view($this->folder, $this->name);               
        }
    }
    
    function getParameters($controller) {
        $reg_var = array();        
        $crossfire = FALSE; 
        if(!$this->getVar){
            if (!empty($this->segVar[0])){ $reg_var = $this->segVar; }   
        }
        if (!empty($this->segVar[0]) && $this->getVar){ $crossfire = TRUE;}
        if ($this->getVar){
            $ref = new ReflectionMethod($controller, $this->action);
            foreach ($ref->getParameters() as $arg){      
                if (isset($this->getVar[$arg->name])){ $reg_var[$arg->name] = $this->getVar[$arg->name]; } 
                else { if (!$crossfire){$reg_var[$arg->name] = null;}}                              
            }
        }
        if($crossfire){
            $reg_var = array_merge($reg_var, $this->segVar);
        }
        return $reg_var;        
    }    
    
    public function start($controller, $action, $var = array()){
        call_user_func_array(array($controller, $action), $var);  
    }
}

class view{
    
    private $filename;
    
    private $data = NULL;
    public $resDir;
    public $resUrl;

    public function __construct($folder, $name) {
        $filename = $folder . 'view.php';
        $this->filename = $filename;
        $this->name = $name;
        $this->resDir = $folder . '/resources/';
        $this->resUrl = APP_URL . $name . '/resources/';
    }
    
    public function render($data = NULL){
        $this->data = $data;
        $this->capture();
    }
    
    public function renderTo($filename, $data = NULL) {
        $this->filename = APP_DIR . $this->name . '/view/' . $filename . '.php';
        $this->data = $data;
        $this->capture();
    }
    
    protected function capture()
    {
        if (isset($this->data)){
            extract($this->data, EXTR_SKIP);
        }
            
	ob_start();
	try
	{		
            if (file_exists($this->filename)){
		include $this->filename;
            } else { echo ' View render error '; }
        }
            catch (Exception $e)
	{			
            ob_end_clean();
			
            throw $e;
	}

        echo ob_get_clean();
    }
}

class sql{
    
    public $DB;

    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function __construct() {
        require_once ROOT_DIR . 'vendor/medoo/medoo.php';
        $config = Config::getInstance();
             
        $DB = new medoo([
       // required
       'database_type' => $config->db_type,
       'database_name' => $config->db_name,
       'server' => $config->db_host,
       'username' => $config->db_user,
       'password' => $config->db_pass,
       'charset' => $config->db_charset,
         
        // [optional]
        'port' => 3306,
         
        // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
        'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL
        ]
        ]);
        $this->DB = $DB;
    }    
    
    
}