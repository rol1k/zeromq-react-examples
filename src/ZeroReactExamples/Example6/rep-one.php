<?php

require __DIR__.'/../../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);

$rep = $context->getSocket(ZMQ::SOCKET_REP);
$rep->connect('tcp://127.0.0.1:5556');

$rep->on('message', function ($msg) use ($rep) {
    printf('Получил сообщение [%s]'.PHP_EOL, $msg);
    printf('Ответил'.PHP_EOL);
    $rep->send('Ответ');
});

$loop->run();
