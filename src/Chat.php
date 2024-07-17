<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        // Load old messages from file and send to the new client
        if (file_exists('messages.txt')) {
            $messages = file_get_contents('messages.txt');
            $conn->send(json_encode([
                'type' => 'load',
                'messages' => $messages
            ]));
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $msgData = json_decode($msg);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($msgData->type)) {
                $type = $msgData->type;

                if ($type === 'clear') {
                    // Clear the messages.txt file
                    file_put_contents("messages.txt", "");

                    // Notify all clients to clear their messages
                    foreach ($this->clients as $client) {
                        $client->send(json_encode([
                            'type' => 'clear'
                        ]));
                    }
                } else if (isset($msgData->tier) && isset($msgData->content)) {
                    $tier = $msgData->tier;
                    $message = $msgData->content;
                    $timestamp = $msgData->timestamp;

                    foreach ($this->clients as $client) {
                        $client->send(json_encode([
                            'type' => $type,
                            'tier' => $tier,
                            'timestamp' => $timestamp,
                            'message' => $message
                        ]));
                    }

                    // Save message to file
                    file_put_contents("messages.txt", json_encode([
                        'type' => $type,
                        'tier' => $tier,
                        'timestamp' => $timestamp,
                        'message' => $message
                    ]) . PHP_EOL, FILE_APPEND);
                } else {
                    error_log("Received message with missing 'tier' or 'message' key: " . $msgData);
                }
            } else {
                error_log("Received message with missing 'type' key: " . $msgData);
            }
        } else {
            error_log("Failed to decode JSON: " . $msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}
