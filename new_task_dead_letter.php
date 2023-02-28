<?php

require_once __DIR__ . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->load();

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection(
    $_ENV["RABBITMQ_HOST"], 
    $_ENV["RABBITMQ_PORT"], 
    $_ENV["RABBITMQ_USER"], 
    $_ENV["RABBITMQ_PASSWORD"]
);

$channel = $connection->channel();

$exchange = 'task';

$data = implode(' ', array_slice($argv, 1));

if (!$data) {
    $data = 'H3ll0 W0rld';
}

$msg = new AMQPMessage(
    $data,
    ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
);

$channel->basic_publish($msg, '', 'task');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();