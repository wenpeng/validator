<?php
error_reporting(E_ALL);

require  'src/Validator.php';
use Wenpeng\Validator\Validator;

function dd($var)
{
    var_dump($var);exit();
}

function between($source, $min, $max)
{
    if (is_array($source)) {
        $count = count($source);
        return $count >= $min && $count <= $max;
    } else {
        $length = strlen((string) $source);
        return $length >= $min && $length <= $max;
    }
}

class Example {
    public static function isFile($path)
    {
        return is_file($path);
    }
}

$validator = Validator::make([
    'a' => 'aaaa',
    'b' => [],
    'c' => ['h', 'i', 'j', 'k'],
    'f' => __FILE__ .'.not_found'
], [
    'a' => 'require|is_string',
    'b' => 'require|is_array',
    'c' => 'is_array|between:1,4',
    'f' => 'Example@isFile'
], [
    'c.between' => 'c 的元素数必须在 1~4 之间',
    'f.Example@isFile' => 'f 中提交的文件找不到'
]);

if ($validator->fail()) {
    echo  $validator->error();
} else {
    echo '没有错误';
}
