<?php

use Workerman\Worker;
require_once __DIR__ . '/workerman/Autoloader.php';
header("content-type:text/html;charset=utf-8");
$ws_worker = new Worker("websocket://127.0.0.1:8080");
$ws_worker->count = 4;
// 注意：这里与上个例子不通，使用的是websocket协议
$global_uid = 0;
$global_num =0;

// 当客户端连上来时分配uid，并保存连接，并通知所有客户端
function user_online($connection) {
    global $ws_worker, $global_uid,$global_num;
    ++$global_num;
    // 为这个链接分配一个uid
    $connection->uid = ++$global_uid;
    foreach ($ws_worker->connections as $conn) {
        $conn->send("用户[{$connection->uid}] 上线,".$global_num."人在线");
    }
}


// 当客户端断开时，广播给所有客户端
function user_outline($connection) {
    global $ws_worker,$global_num;
    --$global_num;
    foreach ($ws_worker->connections as $conn) {
        $conn->send("用户[{$connection->uid}] 离线,".$global_num."人在线");
    }
}

function user_message($connection, $data) //发消息
{
    global $ws_worker, $global_uid;
    // $pdo = new PDO('mysql:host=127.0.0.1;dbname=database_eleven', 'root', 'root');
    // $pdo->query('set names utf8');
    // $sql = 'insert into `message`(`message`,`uid`) values('."'$data'".','."'$connection->uid'".')';
    // if ($pdo->exec($sql)) {
        foreach ($ws_worker->connections as $conn) {
            $conn->send("用户[{$connection->uid}]说".$data);
        }
    // }
};

$ws_worker->onMessage ='user_message';
$ws_worker->onConnect ='user_online';
$ws_worker->onClose = 'user_outline';

// 运行worker
Worker::runAll();
