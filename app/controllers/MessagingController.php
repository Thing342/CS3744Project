<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 4/13/18
 * Time: 11:03 AM
 */

namespace app\controllers;

require_once 'app/models/UnitEvent.php';
require_once 'app/models/Comment.php';

use app\models\Token;
use app\models\User;
use app\models\Message;

use lib\Controller;

class MessagingController extends BaseController
{

    public static function init() : MessagingController {
        return new MessagingController();
    }

    public function routes(): array
    {
        return [
            self::route('POST', '/create', 'create'),
            self::route('POST', '/clear', 'clear'),
            self::route('POST', '/:msgID/delete', 'delete'),
            self::route('GET', '/:msgID', 'read'),
            self::route('GET', '/', 'inbox'),
        ];
    }

    public function inbox($params) {
        $token = $this->require_authentication();
        $this->showInboxUI($token->getUser());
    }

    public function read($params) {
        $token = $this->require_authentication();
        $msgId = $params['msgID'];
        $db = $this->getDBConn();

        $message = Message::fetch($db, $msgId);

        if($message->getUserTo()->getUserId() != $token->getUser()->getUserId() || $message == null) {
            $this->addFlashMessage('Could not find message', self::FLASH_LEVEL_USER_ERR);
            $this->showInboxUI($token->getUser(), null);
        }

        $this->showInboxUI($token->getUser(), $message);
    }

    public function delete($params) {
        $token = $this->require_authentication();
        $msgId = $params['msgID'];

        // Verify that we can delete message
        $message = Message::fetch($this->getDBConn(), $msgId);
        if($message == null) {
            $this->addFlashMessage('Could not find message', self::FLASH_LEVEL_USER_ERR);
            $this->redirect('/messages');
        } else {
            $userId = $token->getUser()->getUserId();
            $senderId = $message->getUserFrom()->getUserId();
            $recipientId = $message->getUserTo()->getUserId();

            if($senderId != $userId && $recipientId != $userId) {
                $this->addFlashMessage('Could not find message', self::FLASH_LEVEL_USER_ERR);
                $this->redirect('/messages');
            }
        }

        if (Message::delete($this->getDBConn(), $msgId)) {
            $this->addFlashMessage("Deleted message", self::FLASH_LEVEL_INFO);
        } else {
            $this->addFlashMessage("Unable to delete message", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect('/messages');
    }

    public function clear($params) {
        $token = $this->require_authentication();

        if (Message::deleteToReciever($this->getDBConn(), $token->getUser()->getUserId())) {
            $this->addFlashMessage("Inbox cleared.", self::FLASH_LEVEL_INFO);
        } else {
            $this->addFlashMessage("Unable to delete messages", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect('/messages');
    }

    public function create($params) {
        $token = $this->require_authentication();
        $recipientID = $_POST['recipientId'];
        $msgText = $_POST['messageText'];
        $db = $this->getDBConn();

        $recipientInfo = Message::canMessageUser($db, $token->getUser()->getUserId(), $recipientID);
        if (!$recipientInfo) {
            $this->addFlashMessage("Cannot message user! Make sure you both follow each other, or their account is listed as Public.", self::FLASH_LEVEL_USER_ERR);
            $this->redirect('/messages');
        }

        $message = Message::build2($recipientInfo['followid'], date('Y-m-d H:i:s'), $msgText);

        if(!$message->commit($db)) {
            $this->addFlashMessage("Unable to send message.", self::FLASH_LEVEL_SERVER_ERR);
        } else {
            $this->addFlashMessage("Sent message.", self::FLASH_LEVEL_INFO);
        }

        $this->redirect('/messages');
    }


    private function showInboxUI(User $u, ?Message $message = null) {
        $db = $this->getDBConn();

        $recieved = Message::fetchAllRecipient($db, $u->getUserId());
        $sent = Message::fetchAllSender($db, $u->getUserId());
        $messageableUsers = Message::getMessageableUsers($db, $u->getUserId());

        require 'app/views/inbox.phtml';
    }
}