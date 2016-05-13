<?php
namespace Raam;

use Raam\Di\Container;

class Application extends Container
{
    protected $rootPath = '';
    
    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath . DIRECTORY_SEPARATOR;
    }

    // 运行
    public function run()
    {
        
    }

    // 获取框架根目录
    public function rootPath()
    {
        return $this->rootPath;
    }

}
