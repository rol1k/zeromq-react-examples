<?php

require __DIR__.'/../../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);

$sender = $context->getSocket(ZMQ::SOCKET_PUSH);
$sender->bind('tcp://127.0.0.1:5557');

$i = 0;
$loop->addPeriodicTimer(1, function () use (&$i, $sender) {
	$i++;
	echo "Отправил сообщение [$i]", PHP_EOL;
	$sender->send($i);
});

$loop->run();
