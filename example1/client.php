<?php
require __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$requester = $context->getSocket(ZMQ::SOCKET_REQ);

$requester->on('message', function($reply) {
	printf('Получил ответ [%s]'.PHP_EOL, $reply);
});

$requester->connect('tcp://127.0.0.1:5555');

$i = 0;
$loop->addPeriodicTimer(1, function () use (&$i, $requester) {
	printf('Отправил запрос [%d]'.PHP_EOL, $i);
	$requester->send($i);
	$i++;
});

$loop->run();
