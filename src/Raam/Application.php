<?php
namespace Raam;

use Raam\Di\Container;
use Raam\Support\Facades\Facade;
use Raam\Support\Facades\Loader;
use Raam\Support\Facades\Config;
use Raam\Support\Facades\Route;

class Application extends Container
{
    protected $rootPath = '';
    
    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath . DIRECTORY_SEPARATOR;
        Facade::$app = $this;
    }

    // 运行
    public function run()
    {
        $this->singleton();
        $loaderPath = Config::get('autoload.path');
        // print_r($loaderPath);die;
        Loader::addAutoLoadPath($loaderPath);
        Loader::import(APP_PATH . 'routes.php');
        Loader::register();
        Route::run();
    }

    public function singleton()
    {
        $this->setSingleton('Raam\Application', $this);
        $this->setSingleton('Raam\Config');
        $this->setSingleton('Config', ['class' => 'Raam\Config', 'configPath' => ROOT_PATH . CONFIG_FOLDER]);
        $this->setSingleton('Loader', 'Raam\Loader');
        $this->setSingleton('Raam\Loader');
        $this->setSingleton('Request', 'Raam\Request');
        $this->setSingleton('Raam\Request');
        $this->setSingleton('Route', 'Raam\Route');
        $this->setSingleton('Raam\Route');
    }

    // 获取框架根目录
    public function rootPath()
    {
        return $this->rootPath;
    }

    public function getApp()
    {
        return $this;
    }

}
