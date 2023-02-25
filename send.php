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

$channel->queue_declare(queue: 'hello', auto_delete: false);

$msg = new AMQPMessage('Hello World!');
$channel->basic_publish(msg: $msg, routing_key: 'hello');

echo " [x] Sent 'Hello World!'\n";

$channel->close();
$connection->close();
