<?php

// Load composer
use Longman\TelegramBot\Commands\SystemCommands\StartCommand;
use Longman\TelegramBot\Request;

require_once __DIR__ . '/vendor/autoload.php';

// Load all configuration options
/** @var array $config */
$config = require __DIR__ . '/config.php';

$telegram = new Longman\TelegramBot\Telegram($config['api_key'], $config['bot_username']);

