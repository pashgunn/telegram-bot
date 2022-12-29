<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use mysqli;

class LoginCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'login';
    
    /**
     * @var string
     */
    protected $usage = '/login';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Conversation Object
     *
     * @var Conversation
     */
    protected $conversation;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    
    public static function checkReg($conn, $user_id)
    {
        $query = "SELECT *
                  FROM users
                  WHERE id = $user_id";
        $result = $conn->query($query) or die($conn->error);
        if ($result->num_rows == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getData($conn, $column_name, $user_id)
    {
        $query = "SELECT $column_name
                  FROM users
                  WHERE id = $user_id";
        $result = $conn->query($query) or die($conn->error);
        $user = $result->fetch_assoc();
        return $user[$column_name];
    }
     
    public function execute(): ServerResponse
    {
        $conn = new mysqli('localhost', 'u1875477_root', 'mf2-E38-tUk-U3A', 'u1875477_telegram-bot') or die($this->replyToChat('die'));
        
        $message = $this->getMessage();
        
        $text = trim($message->getText(true));
        $user_id = $message->getFrom()->getId();
        $chat_id = $message->getChat()->getId();

        // Preparing response
        $data = [
            'chat_id' => $chat_id,
        ];

        // Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        // Load any existing notes from this conversation
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        // Load the current state of the conversation
        $state = $notes['state'] ?? 0;

        $result = Request::emptyResponse();

        // State machine
        // Every time a step is achieved the state is updated
        switch ($state) {
            case 0:
                if (!self::checkReg($conn, $user_id)) {
                    $this->replyToChat(
                        "Вы еще не зарегистрированы!" . PHP_EOL .
                        "Используйте команду /register, чтобы зарегистрироваться."
                    );
                    break;
                }
            case 1:
                if ($text === '') {
                    $notes['state'] = 1;
                    $this->conversation->update();
                    $data['text'] = 'Введите почту:';
                    $result = Request::sendMessage($data);
                    break;
                }
                $email = $this->getData($conn, 'email', $user_id);
                if ($text != $email) {
                    $this->replyToChat("Неверный адрес электронной почты." . PHP_EOL . "Повторите попытку:");
                    break;
                }
                $text = '';
                
            case 2:
                if ($text === '') {
                    $notes['state'] = 2;
                    $this->conversation->update();
                    $data['text'] = 'Введите пароль:';
                    $result = Request::sendMessage($data);
                    break;
                }
                $password = $this->getData($conn, 'password', $user_id);
                if ($text != $password) {
                    $this->replyToChat("Неверный пароль." . PHP_EOL .  "Повторите попытку:");
                    break;
                }
                $text = '';
                
            case 3:
                $this->conversation->update();
                unset($notes['state']);
                $name = $this->getData($conn, 'name', $user_id);
                $surname = $this->getData($conn, 'surname', $user_id);
                $data['text'] = "Добро пожаловать, $name $surname!";
                $this->conversation->stop();
                $result = Request::sendMessage($data);
                break;
        }
    }
}