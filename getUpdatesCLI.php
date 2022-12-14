<?php

/**
 * This file is used to run the bot with the getUpdates method.
 */

// Load composer
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

require_once __DIR__ . '/vendor/autoload.php';

// Load all configuration options
$config = require __DIR__ . '/config.php';


try {
    // Create Telegram API object
    $telegram = new Telegram($config['api_key'], $config['bot_username']);

    $telegram->enableMySql($config['mysql']);

    // Handle telegram getUpdates request
    $server_response = $telegram->handleGetUpdates();

    if ($server_response->isOk()) {
        $update_count = count($server_response->getResult());
        echo date('Y-m-d H:i:s') . ' - Processed ' . $update_count . ' updates';
    } else {
        echo date('Y-m-d H:i:s') . ' - Failed to fetch updates' . PHP_EOL;
        echo $server_response->printError();
    }

    $result = $server_response->getRawData()["result"];

    foreach($result as $value) {
        $text = $value["message"]["text"]; //Текст сообщения
        $chat_id = $value["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
        if ($text == "/start") {
            $reply = "Добро пожаловать в бота!";

            Request::sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
        }
    }

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Log telegram errors
    Longman\TelegramBot\TelegramLog::error($e);

    // Uncomment this to output any errors (ONLY FOR DEVELOPMENT!)
    echo $e;
}