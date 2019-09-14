<?php

//$steps = [0,1,0,2,5,0,1,3,2,4,0,2,0,1];  //测试数据
$steps = [0,1,0,2,1,0,1,3,2,1,2,1];

$leftTopStep = get_left_top_steps($steps); //数组中每个台阶左边最高台阶高度，
//将字符串翻转，计算左边最高台阶后，再翻转一次则为右边最高台阶
$rightTopStep = get_left_top_steps(array_reverse($steps));
$rightTopStep = array_reverse($rightTopStep);//数组中每个台阶右边最高台阶高度，

//计算所有台阶积水量
$sum = 0;
foreach ($steps as $k => $high){
    $weater = 0;
    if($steps[$k] < $leftTopStep[$k] && $steps[$k] < $rightTopStep[$k]){
        $weater = $leftTopStep[$k] - $steps[$k];
        if($weater > ($rightTopStep[$k] - $steps[$k])){
            $weater = $rightTopStep[$k] - $steps[$k];
        }
        $sum += $weater;
    }
    $weaterStep[$k] = $weater; //记录每个台阶积水量，可忽略
}
echo '所有台阶积水量为：'.$sum.'<br>';

//以下为测试输出
echo "台阶高度：<br>";
foreach ($steps as $k => $v){
    echo $v."__";
}
echo "<br>";

echo "左边台阶最高高度：<br>";
foreach ($leftTopStep as $k => $v){
    echo $v."__";
}
echo "<br>";

echo "右边台阶最高高度：<br>";
foreach ($rightTopStep as $k => $v){
    echo $v."__";
}
echo "<br>";

echo "每个台阶积水量：<br>";
foreach ($weaterStep as $k => $v){
    echo $v."__";
}
echo "<br>";

/**
 * 计算数组中每个台阶左边最高台阶高度，不存在则置0
 * @param $steps
 * @return array
 */
function get_left_top_steps($steps){
    $leftTopStep = [];
    $count = count($steps);
    for($i = 0 ; $i < $count ; $i++){
        if(!isset($steps[$i-1])){
            $leftTopStep[$i] = 0;
        }else{
            if($steps[$i-1] > $leftTopStep[$i-1]){
                $leftTopStep[$i] = $steps[$i-1];
            }else{
                $leftTopStep[$i] = $leftTopStep[$i-1];
            }
        }
    }
    return $leftTopStep;
}