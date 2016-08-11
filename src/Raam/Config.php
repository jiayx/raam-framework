<?php
namespace Raam;
/**
* 配置文件读取
*/

class Config
{
    private $configPath = ROOT_PATH . CONFIG_FOLDER;

    // 配置文件缓存
    private $configCache = [];
    // 已加载文件缓存
    private $loadedCache = [];

    public function __construct($config = [])
    {
        if (isset($config['configPath'])) {
            $this->configPath = $config['configPath'];
        }
    }

    // 取配置
    public function get($key, $default = null)
    {
        // 先查找取缓存
        if (isset($this->configCache[$key])) {
            return $this->configCache[$key];
        }

        // 缓存没有的话查找文件
        $keys = explode('.', $key);
        $fileName = array_shift($keys);

        if (empty($this->loadedCache[$fileName])) {

            $envCconfig = [];
            $commonConfig = [];
            // 当前环境配置文件路径
            $envConfigPath = $this->configPath . DIRECTORY_SEPARATOR . ENV . DIRECTORY_SEPARATOR . $fileName . '.php';
            // 公共配置文件路径
            $commonConfigPath = $this->configPath . DIRECTORY_SEPARATOR . $fileName . '.php';

            if (file_exists($envConfigPath)) {
                $envCconfig = (array) require $envConfigPath;
            }
            if (file_exists($commonConfigPath)) {
                $commonConfig = (array) require $commonConfigPath;
            }
            // 合并环境配置和公共配置 并写入缓存
            $this->loadedCache[$fileName] = $envCconfig + $commonConfig;
        }
        
        $config = $this->loadedCache[$fileName];
        // 循环取出要获取的值 没有则返回默认值
        foreach ($keys as $k) {
            if (isset($config[$k])) {
                $config = $config[$k];
            } else {
                return $default;
            }
        }
        return $this->configCache[$key] = $config;
    }

    // 设定配置 - 设置完之后 在此脚本之后的代码获取到的值都变为此值 - 通过set(key, '') 来恢复默认 
    public function set($key, $value)
    {
        if (! empty($key)) {
            $this->configCache[$key] = $value;
        }
    }
}
