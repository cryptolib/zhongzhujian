<?php

// 监听端口
$worker = new GlobalData\Server(
    \Config\GlobalData::$address,
    \Config\GlobalData::$port,
    \Config\GlobalData::$persistence,
    \Config\GlobalData::$datapath
);
