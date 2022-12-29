<?php


namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use mysqli;

class MyorderCommand extends UserCommand
{

    /**
     * @var string
     */
    protected $usage = '/my_order';

    private function printDatabase($conn, $result)
    {
        foreach ($result as $row) {
            $this->replyToChat(
            "ID заказа: " . $row['order_id'] . PHP_EOL .
            "ID пользователя: " . $row['user_id'] . PHP_EOL .
            "Список товаров: " . PHP_EOL . $row['list_of_sneakers'] . PHP_EOL .
            "Статус: " . $row['status'] . PHP_EOL
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
        $user_id = $message->getFrom()->getId();
        
        $conn = new mysqli('localhost', 'u1875477_root', 'mf2-E38-tUk-U3A', 'u1875477_telegram-bot') or die($this->replyToChat('die'));
        
        $query = "SELECT
                      orders.order_id,
                      orders.user_id,
                      GROUP_CONCAT(sneakers.brand, ' ', sneakers.sneaker_name SEPARATOR '\n') AS list_of_sneakers,
                      orders.status
                  FROM orders
                  INNER JOIN sneakers_orders ON orders.order_id = sneakers_orders.order_id
                  INNER JOIN sneakers ON sneakers_orders.sneaker_id = sneakers.sneaker_id
                  WHERE orders.user_id = $user_id
                  GROUP BY orders.order_id";
        $result = $conn->query($query) or die($conn->error);
        
        if ($result->num_rows == 0) {
            $this->replyToChat("Вы еще ничего не заказывали :(");
        } else {
            $this->printDatabase($conn, $result);
        }

    }
}