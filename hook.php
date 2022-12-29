<?php


/**
 * This configuration file is used to run the bot with the webhook method.
 */

// Load composer
require_once __DIR__ . '/vendor/autoload.php';

// Load all configuration options
/** @var array $config */
$config = require __DIR__ . '/config.php';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($config['api_key'], $config['bot_username']);

    // Enable admin users
    $telegram->enableAdmins($config['admins']);

    // Add commands paths containing your custom commands
    $telegram->addCommandsPaths($config['commands']['paths']);

    // Enable MySQL if required
     $telegram->enableMySql($config['mysql']);

    \Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . '/' . 'Market_Place_Laravel_bot' . '_error.log');
    \Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . '/' . 'Market_Place_Laravel_bot' . '_debug.log');
    \Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . '/' . 'Market_Place_Laravel_bot' . '_update.log');

    // Load all command-specific configurations
    foreach ($config['commands']['configs'] as $command_name => $command_config) {
        $telegram->setCommandConfig($command_name, $command_config);
    }

    // Handle telegram webhook request
    $telegram->handle();

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Log telegram errors
    Longman\TelegramBot\TelegramLog::error($e);

    // Uncomment this to output any errors (ONLY FOR DEVELOPMENT!)
     echo $e;
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    // Uncomment this to output log initialisation errors (ONLY FOR DEVELOPMENT!)
     echo $e;
}
?>