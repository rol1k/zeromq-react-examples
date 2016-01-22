<?php

require __DIR__.'/../../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);

$receiver = $context->getSocket(ZMQ::SOCKET_PULL);
$receiver->connect('tcp://127.0.0.1:5557');

$sender = $context->getSocket(ZMQ::SOCKET_PUSH);
$sender->connect('tcp://127.0.0.1:5558');

$receiver->on('message', function ($msg) use ($loop, $sender) {
	echo "Получил сообщение [$msg]", PHP_EOL;

	// задержка ответа 2-4 сек
	$loop->addTimer(rand(2,4), function() use ($sender, $msg){
		// отправляем ответ клиенту
		printf("Отправил результат [%s]".PHP_EOL, $msg);
		$sender->send(sprintf("%s", $msg));
	});
});

$loop->run();
