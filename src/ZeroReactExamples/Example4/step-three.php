<?php

$context = new ZMQContext();
$receiver = new ZMQSocket($context, ZMQ::SOCKET_PAIR);
$receiver->bind("tcp://127.0.0.1:5558");

$receiver->recv();
echo "Получил сообщение", PHP_EOL;
echo "Test succesful!", PHP_EOL;