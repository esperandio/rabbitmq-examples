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

$channel->exchange_declare(exchange: 'topic_logs', type: 'topic', auto_delete: false);

list($queue_name, ,) = $channel->queue_declare(queue: "", exclusive: true, auto_delete: false);

$binding_keys = array_slice($argv, 1);

if (empty($binding_keys)) {
    file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
    exit(1);
}

foreach ($binding_keys as $binding_key) {
    $channel->queue_bind(queue: $queue_name, exchange: 'topic_logs', routing_key: $binding_key);
}

echo " [*] Waiting for logs. To exit press CTRL+C\n";

$callback = function (AMQPMessage $msg) {
    echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
};

$channel->basic_consume(queue: $queue_name, no_ack: true, callback: $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();