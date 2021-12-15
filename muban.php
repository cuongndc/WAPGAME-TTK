<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/31 0031
 * Time: 11:03
 */

header("Content-type: text/html; charset=utf-8");
//获取文件列表
function getFile($dir) {
    $fileArray[]=NULL;
    if (false != ($handle = opendir ( $dir ))) {
        $i=0;
        while ( false !== ($file = readdir ( $handle )) ) {
            //去掉"“.”、“..”以及带“.xxx”后缀的文件
            if ($file != "." && $file != ".."&&strpos($file,".")) {
                $fileArray[$i]= iconv('GBK', 'UTF-8', $file);
                if($i==100){
                    break;
                }
                $i++;
            }
        }
        //关闭句柄
        closedir ( $handle );
    }
    return $fileArray;
}

$file = getFile("./game/muban/");
echo json_encode($file);
