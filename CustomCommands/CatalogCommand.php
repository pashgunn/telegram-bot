<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use mysqli;

class CatalogCommand extends UserCommand
{

    /**
     * @var string
     */
    protected $usage = '/catalog';


    private function printDatabase($conn, $result)
    {
        foreach ($result as $row) {
            $this->replyToChat(
            "Номер товара: " . $row['sneaker_id'] . PHP_EOL .
            "Название: " . $row['brand'] . " " . $row['sneaker_name'] . PHP_EOL .
            "Размер: " . $row['size'] . PHP_EOL .
            "Цена: " . $row['cost'] . PHP_EOL
            );
        }
    }

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $command = $message->getCommand();
        
        $conn = new mysqli('localhost', 'u1875477_root', 'mf2-E38-tUk-U3A', 'u1875477_telegram-bot') or die($this->replyToChat('die'));
        
        $query = "SELECT * FROM sneakers";
        $result = $conn->query($query) or die($conn->error);
        $this->printDatabase($conn, $result);
    }

}