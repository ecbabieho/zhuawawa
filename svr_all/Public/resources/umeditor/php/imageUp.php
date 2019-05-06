<?php
    header("Content-Type:text/html;charset=utf-8");
    error_reporting( E_ERROR | E_WARNING );
    date_default_timezone_set("Asia/chongqing");
    include "Uploader.class.php";
    //上传配置
    $config = array(
        "savePath" => "upload/" ,             //存储文件夹
        "maxSize" => 1000 ,                   //允许的文件最大尺寸，单位KB
        "allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" )  //允许的文件格式
    );
    //上传文件目录
    $Path = "upload/";

    //背景保存在临时目录中
    $config[ "savePath" ] = $Path;
    $up = new Uploader( "upfile" , $config );
    $type = $_REQUEST['type'];
    $callback=$_GET['callback'];

    $info = $up->getFileInfo();
    
    //$image = new \Think\Image();
    // 在图片右下角添加水印文字 ThinkPHP 并保存为new.jpg
    //$image->open('./Public/resources/umeditor/php/'.$info['url'])->text('悦美经纪人','./font/pingfang.ttf',25,'#8E8E8E',\Think\Image::IMAGE_WATER_SOUTHEAST,-20)->save('./Public/resources/umeditor/php/'.$info['url']);
    /*if($info['type'] == '.png'){
        $im=imagecreatefrompng('./Public/resources/umeditor/php/'.$info['url']); //取出原图
        $hb=imagecolorallocate($im,142,142,142);
        imagettftext($im,25,0,20,40,$hb,"pingfang.ttf","悦美经纪人"); //加水印
        imagepng($im,'./Public/resources/umeditor/php/'.$info['url']); //保存水印图到本文件夹下images文件夹，水印图命名为water1.png
    }
    if($info['type'] == '.jpg'||$info['type'] == '.jpeg'){
        $im=imagecreatefromjpeg($info['url']); //取出原图
        $hb=imagecolorallocate($im,142,142,142);
        imagettftext($im,25,0,20,40,$hb,"pingfang.ttf","悦美经纪人"); //加水印
        imagejpeg($im,$info['url']); //保存水印图到本文件夹下images文件夹，水印图命名为water1.png
    }
    if($info['type'] == '.gif'){
        $im=imagecreatefromgif('./Public/resources/umeditor/php/'.$info['url']); //取出原图
        $hb=imagecolorallocate($im,142,142,142);
        imagettftext($im,25,0,20,40,$hb,"pingfang.ttf","悦美经纪人"); //加水印
        imagegif($im,'./Public/resources/umeditor/php/'.$info['url']); //保存水印图到本文件夹下images文件夹，水印图命名为water1.png
    }
    //imagedestroy($im);*/
    /**
     * 返回数据
     */
    if($callback) {
        echo '<script>'.$callback.'('.json_encode($info).')</script>';
    } else {
        echo json_encode($info);
    }
