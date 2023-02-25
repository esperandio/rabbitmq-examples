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

$channel->exchange_declare(exchange: 'logs', type: 'fanout', auto_delete: false);

$data = implode(' ', array_slice($argv, 1));

if (empty($data)) {
    $data = "info: Hello World!";
}

$msg = new AMQPMessage($data);

$channel->basic_publish(msg: $msg, exchange: 'logs');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();