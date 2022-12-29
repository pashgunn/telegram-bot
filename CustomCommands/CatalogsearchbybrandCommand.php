<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use mysqli;

class CatalogsearchbybrandCommand extends UserCommand
{

    /**
     * @var string
     */
    protected $usage = '/catalog_search_by_brand <command>';

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
        
        $brand = explode(" ", $message->getText());
        $brand = $brand[1];
        $conn = new mysqli('localhost', 'u1875477_root', 'mf2-E38-tUk-U3A', 'u1875477_telegram-bot') or die($this->replyToChat('die'));
        
        $query = "SELECT * FROM sneakers WHERE `brand` = '$brand'";
        $result = $conn->query($query) or die($conn->error);
        if($result->num_rows == 0 && $brand != '') {
            $this->replyToChat("Указанный бренд не найден");
        } elseif ($result->num_rows == 0 && $brand == '') {
                $this->replyToChat(
                "Введите команду с указанием бренда:" . PHP_EOL . 
                "/catalog_search_by_brand <brand>"
                );
        } else {
            $this->printDatabase($conn, $result);
        }
    }

}