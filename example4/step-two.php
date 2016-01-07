<?php

$context = new ZMQContext();
$receiver = new ZMQSocket($context, ZMQ::SOCKET_PAIR);
$receiver->bind("tcp://127.0.0.1:5557");

$receiver->recv();

echo "Получил сообщение", PHP_EOL;

$sender = new ZMQSocket($context, ZMQ::SOCKET_PAIR);
$sender->connect("tcp://127.0.0.1:5558");
$sender->send("");
echo "Отправил сообщение", PHP_EOL;