<?php
require __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$responder = $context->getSocket(ZMQ::SOCKET_REP);
$timestamp = new ZeroReactExamples\Utilits\Timestamp();

$responder->on('message', function ($request) use ($loop, $responder, $timestamp) {
	printf("Получил запрос [%s]".PHP_EOL, $request);
	$receiveTime = $timestamp->getTime();

	// задержка ответа 2-4 сек
	$loop->addTimer(rand(2,4), function() use ($responder, $request, $receiveTime, $timestamp){
		// отправляем ответ клиенту
		printf("Отправил ответ [%s]".PHP_EOL, $request);
		$responder->send(sprintf("%s | Получил: %s | Ответил: %s", $request, $receiveTime, $timestamp->getTime()));
	});
});

$responder->bind('tcp://127.0.0.1:5555');

$loop->run();
