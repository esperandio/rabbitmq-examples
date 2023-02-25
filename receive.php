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

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function (AMQPMessage $msg) {
    echo ' [x] Received ', $msg->body, "\n";
};

// An ack(nowledgement) is sent back by the consumer to tell RabbitMQ that a particular message had been received, 
// processed and that RabbitMQ is free to delete it.
$channel->basic_consume(queue: 'hello', no_ack: true, callback: $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
