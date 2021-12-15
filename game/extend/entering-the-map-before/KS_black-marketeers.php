<?php
/**
 * Created by KS(QQ:492607291).
 * Date: 2018-02-27
 * Time: 2018-02-27 21:27:21
 */

//扩展php输出内容
$extend_result_arr=array('extend_type' =>'entering-the-map-before' //当前扩展类型：进入地图前
    , 'jump_url' =>'' //页面跳转到那个URL
    , 'output_html' =>'' //输出内容显示在地图排版中的{$扩展->进入地图前_输出} 位置
    );

$game = new \main\game();

//FIXME 编辑器支持后传人的参数
//编辑器-地图，选中地图，进入该地图前的扩展。
$target_type=3; //扩展对象是 1=boss；2=怪物；3=NPC
$nid=39;//扩展对象（某NPC)的id
$verification_standard=' mt_rand(1,100) <=5 '; //高级条件编辑框内容,判断条件 mt_rand(1,100) <= 5

$check=eval($verification_standard);;

$gnhtml='';

if ($check){
    $gmcmd = $game->create_url("cmd=npc&cmd2=gogoumai&nid=$nid");

    $gnhtml = "<br/>路上偶遇商人xxx<br/>";//FIXME xxx=暂时不知道怎么取对应NPC的名字
    $gnhtml .= "<br/><a href='$gmcmd'>购买物品</a><br/>";
    
    $extend_result_arr['output_html']=$gnhtml;

    //这样调用扩展时可直接判断  is_array($gnhtml) & osset($gnhtml['jump_url']) 就跳转页面；为空就忽略；否则就直接输出$gnhtml
    $gnhtml=$extend_result_arr;
   
}
