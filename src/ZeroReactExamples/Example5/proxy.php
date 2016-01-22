<?php

require __DIR__.'/../../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);

$sub = $context->getSocket(ZMQ::SOCKET_SUB);
$sub->bind('tcp://127.0.0.1:5555');
$sub->subscribe('');

$pub = $context->getSocket(ZMQ::SOCKET_PUB);
$pub->bind('tcp://127.0.0.1:5556');

$sub->on('message', function ($msg) use ($pub) {
	printf('Переправил сообщение %s'.PHP_EOL, $msg);
	$pub->send($msg);
});

$loop->run();

// Через Device без ReactPHP
// $context = new ZMQContext();

// $sub = $context->getSocket(ZMQ::SOCKET_XSUB);
// $sub->bind('tcp://127.0.0.1:5555');

// $pub = $context->getSocket(ZMQ::SOCKET_XPUB);
// $pub->bind('tcp://127.0.0.1:5556');

// $device = new ZMQDevice($sub, $pub);
// $device->run();