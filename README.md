# ZeroMQ-React-Examples
Примеры ZeroMQ + ReactPHP

## Установка

Рекомендованный способ установки rol1k/zeromq-react-examples [через composer](http://getcomposer.org).

```bash
composer require rol1k/zeromq-react-examples
```

## Шаблоны обмена сообщениями ZeroMQ
 - **Request**/**Reply** - двусторонняя связь между программами-абонентами распределенной MQ-системы: одна программа-клиент может взаимодействовать с одной или несколькими программами-серверами. Каждое отправленное сообщение предусматривает уведомление о доставке.
 - **Publish**/**Subscribe** - опубликовать сообщение для множества подписчиков. От предыдущего метода отличается тем, что программа-отправитель не получает уведомлений о получении сообщений программами-подписчиками.
 - **Pipeline** - этот метод используется для иерархической рассылки сообщений, *Downstream* используется для рассылки вниз по иерархии, а *Upstream* - наоборот. Программа-отправитель не получает уведомлений о доставке.
 - **Exclusive Pair** - взаимодействие только между клиентом и сервером. Данный тип взаимодействия не предполагает маршрутизации сообщений и не содержит уведомлений о доставке.

## Пример 1. Request/Reply

*Схема*
![Request/Reply схема](https://github.com/imatix/zguide/raw/master/images/fig2.png)

*client.php*
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$requester = $context->getSocket(ZMQ::SOCKET_REQ);
$requester->on('message', function($reply)
{
	printf('Получил ответ [%s]' . PHP_EOL, $reply);
});
$requester->connect('tcp://127.0.0.1:5555');
$i = 0;
$loop->addPeriodicTimer(1, function() use (&$i, $requester)
{
	printf('Отправил запрос [%d]' . PHP_EOL, $i);
	$requester->send($i);
	$i++;
});
$loop->run();
```

*server.php*
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$responder = $context->getSocket(ZMQ::SOCKET_REP);
$timestamp = new ZeroReactExamples\Utilits\Timestamp();
$responder->on('message', function($request) use ($loop, $responder, $timestamp)
{
	printf("Получил запрос [%s]" . PHP_EOL, $request);
	$receiveTime = $timestamp->getTime();
	// задержка ответа 2-4 сек
	$loop->addTimer(rand(2, 4), function() use ($responder, $request, $receiveTime, $timestamp)
	{
		// отправляем ответ клиенту
		printf("Отправил ответ [%s]" . PHP_EOL, $request);
		$responder->send(sprintf("%s | Получил: %s | Ответил: %s", $request, $receiveTime, $timestamp->getTime()));
	});
});
$responder->bind('tcp://127.0.0.1:5555');
$loop->run();
```

*Результат*
![Request/Reply результат](http://i.imgur.com/tQjQLUm.png)

## Пример 2. Publish/Subscribe

*Схема*
![Publish/Subscribe](https://github.com/imatix/zguide/raw/master/images/fig4.png)

*publisher.php*
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$pub = $context->getSocket(ZMQ::SOCKET_PUB);
$pub->bind('tcp://127.0.0.1:5555');
$i = 0;
$loop->addPeriodicTimer(1, function() use (&$i, $pub)
{
	$i++;
	switch (mt_rand(1, 3)) {
		case 1:
			$subject = 'Автомобили';
			break;
		case 2:
			$subject = 'Электроника';
			break;
		case 3:
			$subject = 'Спорт';
			break;
	}
	printf('Отправил сообщение [%d] | Тема сообщения [%s]' . PHP_EOL, $i, $subject);
	$pub->send(sprintf('%s %d', $subject, $i));
});
$loop->run();
```

*subscriber-one.php*
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$sub = $context->getSocket(ZMQ::SOCKET_SUB);
$sub->connect('tcp://127.0.0.1:5555');
$sub->subscribe('Автомобили');
echo 'Подписался на тему "Автомобили"', PHP_EOL;
$sub->on('message', function($msg)
{
	printf('Получил сообщение [%s]' . PHP_EOL, $msg);
});
$loop->run();
```

*subscriber-two.php*
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$sub = $context->getSocket(ZMQ::SOCKET_SUB);
$sub->connect('tcp://127.0.0.1:5555');
$sub->subscribe('Электроника');
echo 'Подписался на тему "Электроника"', PHP_EOL;
$sub->on('message', function($msg)
{
	printf('Получил сообщение [%s]' . PHP_EOL, $msg);
});
$loop->run();
```

*Результат*
![Publish/Subscribe результат](http://i.imgur.com/Prqnl8C.jpg)

##Пример 3. Pipeline

*Схема*
![Pipeline схема](https://github.com/imatix/zguide/raw/master/images/fig5.png)

*ventilator.php*
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$sender = $context->getSocket(ZMQ::SOCKET_PUSH);
$sender->bind('tcp://127.0.0.1:5557');
$i = 0;
$loop->addPeriodicTimer(1, function() use (&$i, $sender)
{
	$i++;
	echo "Отправил сообщение [$i]", PHP_EOL;
	$sender->send($i);
});
$loop->run();
```

*worker.php*
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$receiver = $context->getSocket(ZMQ::SOCKET_PULL);
$receiver->connect('tcp://127.0.0.1:5557');
$sender = $context->getSocket(ZMQ::SOCKET_PUSH);
$sender->connect('tcp://127.0.0.1:5558');
$receiver->on('message', function($msg) use ($loop, $sender)
{
	echo "Получил сообщение [$msg]", PHP_EOL;
	// задержка ответа 2-4 сек
	$loop->addTimer(rand(2, 4), function() use ($sender, $msg)
	{
		// отправляем ответ клиенту
		printf("Отправил результат [%s]" . PHP_EOL, $msg);
		$sender->send(sprintf("%s", $msg));
	});
});
$loop->run();
```

*sink.php*
```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$loop = React\EventLoop\Factory::create();
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5558');
$pull->on('message', function($msg)
{
	echo "Получил результат [$msg]", PHP_EOL;
});
$loop->run();

```

*Результат*
![Pipeline результат](http://i.imgur.com/lcg9xOY.png)

##Пример 4. Exclusive Pair

*Схема*
![Exclusive Pair схема](https://github.com/imatix/zguide/raw/master/images/fig21.png)

*step-one.php*
```php
<?php
$context = new ZMQContext();
$sender = new ZMQSocket($context, ZMQ::SOCKET_PAIR);
$sender->connect("tcp://127.0.0.1:5557");
$sender->send('');
echo 'Отправил сообщение', PHP_EOL;
```

*step-two.php*
```php
<?php
$context = new ZMQContext();
$receiver = new ZMQSocket($context, ZMQ::SOCKET_PAIR);
$receiver->bind("tcp://127.0.0.1:5557");
$receiver->recv();
echo "Получил сообщение", PHP_EOL;
$sender = new ZMQSocket($context, ZMQ::SOCKET_PAIR);
$sender->connect("tcp://127.0.0.1:5558");
$sender->send("");
echo "Отправил сообщение", PHP_EOL;
```

*step-three.php*
```php
<?php
$context = new ZMQContext();
$receiver = new ZMQSocket($context, ZMQ::SOCKET_PAIR);
$receiver->bind("tcp://127.0.0.1:5558");
$receiver->recv();
echo "Получил сообщение", PHP_EOL;
echo "Test succesful!", PHP_EOL;
```

*Результат*
![Exclusive Pair результат](http://i.imgur.com/9hoHwry.png)

**TODO**
- Убрать надписи "Hello", "World" со схемы Request/Reply
