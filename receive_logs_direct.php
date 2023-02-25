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

$channel->exchange_declare(exchange: 'direct_logs', type: 'direct', auto_delete: false);

list($queue_name, ,) = $channel->queue_declare(queue: "", exclusive: true, auto_delete: false);

$severities = array_slice($argv, 1);

if (empty($severities)) {
    file_put_contents('php://stderr', "Usage: $argv[0] [info] [warning] [error]\n");
    exit(1);
}

foreach ($severities as $severity) {
    $channel->queue_bind(queue: $queue_name, exchange: 'direct_logs', routing_key: $severity);
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