<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use mysqli;

class CatalogsearchbysizeCommand extends UserCommand
{

    /**
     * @var string
     */
    protected $usage = '/catalog_search_by_size <command>';

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

        $size = explode(" ", $message->getText());
        $size = $size[1];
        $conn = new mysqli('localhost', 'u1875477_root', 'mf2-E38-tUk-U3A', 'u1875477_telegram-bot') or die($this->replyToChat('die'));

        $query = "SELECT * FROM sneakers WHERE `size` = $size";
        $result = $conn->query($query) or die($conn->error);
        if($size != '') {
            if($result->num_rows == 0)
                 $this->replyToChat("Кроссовки с указанным размером не найдены");
            else 
                $this->printDatabase($conn, $result);
        } else {
            $this->replyToChat(
                "Введите команду с указанием размера:" . PHP_EOL .
                "/catalog_search_by_size <size>"
            );
        }
    }

}