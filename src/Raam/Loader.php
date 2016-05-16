<?php
namespace Raam;

/**
* 自动加载类 
*/
class Loader
{
    // 自动加载搜索目录
    private $loadPaths = [];
    // 这个暂时没用
    private $LoadedCache = [];

    // 添加自动加载路径
    public function addAutoLoadPath($path)
    {
        if (is_array($path)) {
            $this->loadPaths = array_merge($this->loadPaths, $path);
        } else {
            $this->loadPaths[] = $path;
        }
    }

    // 注册自动加载函数
    public function register()
    {
        spl_autoload_register([$this, 'autoload']);
    }

    // 自动加载函数
    public function autoload($className)
    {
        $className = ltrim($className, '\\');
        $namespace = '';
        $fileName = '';
        if ($lastPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastPos);
            $className = substr($className, $lastPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $className) . '.php';
        foreach ($this->loadPaths as $loadPath) {
            $loadPath = rtrim($loadPath, DIRECTORY_SEPARATOR);
            $path = $loadPath . DIRECTORY_SEPARATOR . $fileName;
            if ($this->import($path)) {
                break;
            }
        }
    }

    // 包含一个已存在的文件
    public function import($path)
    {
        return file_exists($path) ? include_once($path) : false;
    }

}
