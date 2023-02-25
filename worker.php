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

$channel->queue_declare(queue: 'task_queue', durable: true, auto_delete: false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function (AMQPMessage $msg) {
    echo ' [x] Received ', $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));
    echo " [x] Done\n";
    $msg->ack();
};

// basic.qos method make it possible to limit the number of unacknowledged messages on a channel (or connection) 
// when consuming (aka "prefetch count")
$channel->basic_qos(null, 1, null);
$channel->basic_consume(queue: 'task_queue', callback: $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
