<?php

require __DIR__.'/../../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);

$pub = $context->getSocket(ZMQ::SOCKET_PUB);
$pub->bind('tcp://127.0.0.1:5555');

$i = 0;
$loop->addPeriodicTimer(1, function () use (&$i, $pub) {
    $i++;
    switch(mt_rand(1,3)){
    	case 1: $subject = 'Автомобили'; break;
    	case 2: $subject = 'Электроника'; break;
    	case 3: $subject = 'Спорт'; break;
    }
    printf('Отправил сообщение [%d] | Тема сообщения [%s]'.PHP_EOL, $i, $subject);
    $pub->send( sprintf('%s %d', $subject, $i) );
});

$loop->run();
