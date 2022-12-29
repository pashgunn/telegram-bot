<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use mysqli;

class RegisterCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'register';

    /**
     * @var string
     */
    protected $usage = '/register';

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

    public function checkReg($conn, $user_id): bool|ServerResponse
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

    public function insertId($conn, $user_id)
    {
        $query = "INSERT INTO users (id)
                  VALUES ($user_id)";
        $conn->query($query) or die($conn->error);
    }

    public function updateInfo($conn, $column_name, $value, $user_id)
    {
        $query = "UPDATE users
                  SET $column_name = '$value'
                  WHERE id = $user_id";
        $conn->query($query) or die($conn->error);
    }

    public function checkEmail($email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
            return true;
        return false;
    }

    public function checkPassword($password): bool
    {
        $pattern = "/^[(@#$)(a-zA-Z)(0-9)]{8,}$/";
        if (preg_match($pattern, $password))
            return true;
        return false;
    }

    public function checkPaymentMethod($paymentMethod): bool
    {
        $pattern = "/^[0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}$/";
        if (preg_match($pattern, $paymentMethod))
            return true;
        return false;
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
                if ($this->checkReg($conn, $user_id) == true) {
                    $this->replyToChat(
                        "Вы уже зарегистрированы!" . PHP_EOL .
                        "Используйте команду /login, чтобы авторизоваться."
                    );
                    break;
                } else {
                    $this->insertId($conn, $user_id);
                }
            case 1:
                if ($text === '') {
                    $notes['state'] = 1;
                    $this->conversation->update();
                    $data['text'] = 'Введите своё имя:';
                    $result = Request::sendMessage($data);
                    break;
                }
                $this->updateInfo($conn, 'name', $text, $user_id);
                $text = '';

            case 2:
                if ($text === '') {
                    $notes['state'] = 2;
                    $this->conversation->update();
                    $data['text'] = 'Введите свою фамилию:';
                    $result = Request::sendMessage($data);
                    break;
                }
                $this->updateInfo($conn, 'surname', $text, $user_id);
                $text = '';

            case 3:
                if ($text === '') {
                    $notes['state'] = 3;
                    $this->conversation->update();
                    $data['text'] = "Введите свою почту\nФормат - email_name@domen.ru:";
                    $result = Request::sendMessage($data);
                    break;
                }
                if ($this->checkEmail($text)) {
                    $this->updateInfo($conn, 'email', $text, $user_id);
                } else {
                    $this->replyToChat("Неверный формат почты." . PHP_EOL . "Повторите попытку:");
                    break;
                }
                $text = '';

            case 4:
                if ($text === '') {
                    $notes['state'] = 4;
                    $this->conversation->update();
                    $data['text'] = "Введите пароль\nТребование - длина не менее 8 символов:";
                    $result = Request::sendMessage($data);
                    break;
                }
                if ($this->checkPassword($text)) {
                    $this->updateInfo($conn, 'password', $text, $user_id);
                } else {
                    $this->replyToChat("Пароль слишком короткий." . PHP_EOL . "Повторите попытку:");
                    break;
                }
                $text = '';

            case 5:
                if ($text === '') {
                    $notes['state'] = 5;
                    $this->conversation->update();
                    $data['text'] = "Введите номер банковской карты\nФормат 1234 1234 1234 1234:";
                    $result = Request::sendMessage($data);
                    break;
                }
                if ($this->checkPaymentMethod($text)) {
                    $this->updateInfo($conn, 'payment_method', $text, $user_id);
                } else {
                    $this->replyToChat("Неверный формат банковской карты." . PHP_EOL . "Повторите попытку:");
                    break;
                }
                $text = '';

            case 6:
                $this->conversation->update();
                unset($notes['state']);
                $data['text'] = 'Регистрация успешно завершена!';
                $this->conversation->stop();
                $result = Request::sendMessage($data);
                break;
        }
    }
}
