<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use mysqli;

class BuyCommand extends UserCommand
{

    /**
     * @var string
     */
    protected $usage = '/buy <command>';


    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $user_id = $message->getFrom()->getId();
        
        $conn = new mysqli('localhost', 'u1875477_root', 'mf2-E38-tUk-U3A', 'u1875477_telegram-bot') or die($this->replyToChat('die'));
        
        if (!LoginCommand::checkReg($conn, $user_id)) {
            $this->replyToChat(
                "Вы еще не зарегистрированы!" . PHP_EOL .
                "Используйте команду /register, чтобы зарегистрироваться."
            );
        } else {
            $sneakersId = explode(" ", $message->getText());
            $sneakersId = $sneakersId[1];
            
            $query = "INSERT INTO orders (user_id, status) VALUES ($user_id, 'Assembly')";
            $result = $conn->query($query) or die($conn->error);
            
            $query = "SELECT MAX(order_id) as MAX from orders WHERE user_id = $user_id";
            $result = $conn->query($query) or die($conn->error);
            $order = $result->fetch_assoc();
            $orderId = $order['MAX'];
            
            $query = "INSERT INTO sneakers_orders (sneaker_id, order_id, count) VALUES ($sneakersId, $orderId, 1)";
            $conn->query($query) or die($conn->error);
            
            $this->replyToChat("Заказ успешно оформлен!");
        }
    }

}