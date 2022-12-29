<?php


namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class StartCommand extends SystemCommand
{

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        return $this->replyToChat(
            'Привет, я бот для покупки кроссовок!' . PHP_EOL .
            'Введите / или нажмите на меню, чтобы увидеть все команды!'
        );
    }
}