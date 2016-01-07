<?php

$context = new ZMQContext();
$sender = new ZMQSocket($context, ZMQ::SOCKET_PAIR);
$sender->connect("tcp://127.0.0.1:5557");
$sender->send('');
echo 'Отправил сообщение', PHP_EOL;