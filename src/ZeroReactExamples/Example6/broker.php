<?php

require __DIR__.'/../../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);

$router = $context->getSocket(ZMQ::SOCKET_ROUTER);
$router->bind('tcp://127.0.0.1:5555');

$dealer = $context->getSocket(ZMQ::SOCKET_DEALER);
$dealer->bind('tcp://127.0.0.1:5556');

$router->on('messages', function ($msg) use ($dealer) {
	printf('Получил запрос "%s". Переправил'.PHP_EOL, $msg[2]);
	$dealer->send($msg);
});

$dealer->on('messages', function ($msg) use ($router) {
	printf('Получил ответ "%s". Переправил'.PHP_EOL, $msg[2]);
	$router->send($msg);
});

$loop->run();
