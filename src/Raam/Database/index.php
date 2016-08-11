<?php 

// include './Connection.php';
use Database\ConnectionManager;

function array_get(array $array, $key, $default = null)
{
    return isset($array[$key]) ? $array[$key] : $default;
}

function autoload($className)
{
    // print_r($className);die;
    $searchPath = [
        '../',
    ];
    foreach ($searchPath as $path) {
        $filePath = $path.$className.'.php';
        $filePath = str_replace('\\', '/', $filePath);
        if (file_exists($filePath)) {
            include $filePath;
            // echo 'include', $filePath;
            return;
        }
    }
}
spl_autoload_register('autoload');
$config = include('./config.php');
// print_r($config);die;

$DB = ConnectionManager::get($config);
// $a = $DB->table('centers')->where('id', 15)->where('city_id', '1337')->fetch();
$data = [
    ['city_id' => 121, 'name' => '测试'],
    ['city_id' => 121, 'name' => '测试'],
];
$data = ['city_id' => 121, 'name' => '3232'];
// $a = $DB->table('centers_copy')->insert($data);
// $a = $DB->table('centers_copy')->insertGetId($data);
// $a = $DB->table('products_description')->getPk();
$a = $DB->table('centers_copy')->where('id', 15555)->update(['city_id' => 121, 'name' => '3232']);
// $a = $DB->table('centers_copy')->where('id', 1)->where('city_id', '121')->delete();

// print_r($a->toSql());
var_dump($a);die;


/*$sql = 'SELECT * FROM orders LIMIT 1';
$query = $pdo->query($sql);
foreach ($query as $r) {
    print_r($r);
}*/