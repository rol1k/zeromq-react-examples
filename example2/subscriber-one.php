<?php

require __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);

$sub = $context->getSocket(ZMQ::SOCKET_SUB);
$sub->connect('tcp://127.0.0.1:5555');
$sub->subscribe('Автомобили');
echo 'Подписался на тему "Автомобили"', PHP_EOL;

$sub->on('message', function ($msg) {
    printf('Получил сообщение [%s]'.PHP_EOL, $msg);
});

$loop->run();
