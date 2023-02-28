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
$deadLetterExchange = 'retry';

$channel->exchange_declare(exchange: $exchange, type: 'direct', durable: true);
$channel->exchange_declare(exchange: $deadLetterExchange, type: 'direct', durable: true);

$queue = 'task';
$retryQueue = 'retry_task';

// Normal queue
$channel->queue_declare(
    queue: $queue, 
    durable: true, 
    auto_delete: false, 
    arguments: new \PhpAmqpLib\Wire\AMQPTable([
        'x-dead-letter-exchange' => '',
        'x-dead-letter-routing-key' => $retryQueue
    ])
);

// Retry queue
$channel->queue_declare(
    queue: $retryQueue, 
    durable: true, 
    auto_delete: false
);

$channel->queue_bind($queue, $exchange);
$channel->queue_bind($retryQueue, $deadLetterExchange);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function (AMQPMessage $msg) {
    echo " [x] Received ", $msg->body, " on ", date('Y-m-d, H:i:s'),"\n";
    echo " [-] Cannot process crap. Nacking message. \n";
    $msg->nack();
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume(queue: $queue, callback: $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();