<?php

require __DIR__.'/../../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);

$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5558');

$pull->on('message', function ($msg) {
    echo "Получил результат [$msg]", PHP_EOL;
});

$loop->run();
