<?php

require __DIR__.'/../../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);

$req = $context->getSocket(ZMQ::SOCKET_REQ);
$req->connect('tcp://127.0.0.1:5555');

$i = 0;
$loop->addPeriodicTimer(1, function () use (&$i, $req) {
    $i++;
    switch(mt_rand(1,3)){
    	case 1: $subject = 'Автомобили'; break;
    	case 2: $subject = 'Электроника'; break;
    	case 3: $subject = 'Спорт'; break;
    }
    printf('Отправил запрос [%d] | Тема сообщения [%s]'.PHP_EOL, $i, $subject);
    $req->send( sprintf('%s %d', $subject, $i) );
});

$req->on('message', function ($msg) use ($req) {
    printf('Получил ответ [%s]'.PHP_EOL, $msg);
});

$loop->run();
