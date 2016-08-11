<?php
namespace Raam;

use Raam\Di\Container;
use Raam\Support\Facades\Facade;
use Raam\Support\Facades\Loader;
use Raam\Support\Facades\Config;
use Raam\Support\Facades\Route;

defined('CONFIG_FOLDER') OR define('CONFIG_FOLDER', 'config');


class Application extends Container
{
    const VERSION = '0.1.0'; 
    protected $rootPath = '';
    
    public function __construct($rootPath)
    {
        $this->registerBaseBindings();
        $this->rootPath = $rootPath . DIRECTORY_SEPARATOR;
        Facade::$app = $this;
    }

    public function version()
    {
        return static::VERSION;
    }

    // 运行
    public function run()
    {
        $this->init();

        $loaderPath = Config::get('autoload.path');
        Loader::addAutoLoadPath($loaderPath);
        Loader::import(APP_PATH . 'routes.php');
        Loader::register();
        Route::run();
    }

    public function init()
    {
        $this->setSingleton('config', ['class' => 'Raam\Config', 'configPath' => ROOT_PATH . CONFIG_FOLDER]);
    }

    public function make($class)
    {
        return $this->get($class);
    }

    // 基础绑定
    protected function registerBaseBindings()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance('Raam\Application', $this);
        $this->instance('Raam\Di\Container', $this);
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

    /*$this->setSingleton('Raam\Application', $this);
    $this->setSingleton('Raam\Config');
    $this->setSingleton('Config', ['class' => 'Raam\Config', 'configPath' => ROOT_PATH . CONFIG_FOLDER]);
    $this->setSingleton('Loader', 'Raam\Loader');
    $this->setSingleton('Raam\Loader');
    $this->setSingleton('Request', 'Raam\Request');
    $this->setSingleton('Raam\Request');
    $this->setSingleton('Route', 'Raam\Route');
    $this->setSingleton('Raam\Route');*/


}
