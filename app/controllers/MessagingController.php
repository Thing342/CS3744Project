<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 4/13/18
 * Time: 11:03 AM
 */

namespace app\controllers;

require_once 'app/models/UnitNote.php';
require_once 'app/models/Comment.php';

use app\models\Token;
use app\models\User;
use app\models\Message;

use lib\Controller;

/**
 * Class MessagingController
 * @package app\controllers
 *
 * Endpoint for messaging operations
 * Prefix: '/messages'
 * Responsibilities:
 *  - Creating, reading, and deleting messages between Users
 *  - Notifying users of new messages.
 */
class MessagingController extends BaseController
{

    /**
     * Factory method
     */
    public static function init() : MessagingController {
        return new MessagingController();
    }

    /**
     * Routing Table
     */
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

    /**
     * Full path: GET '/messages'
     *
     * Displays the message inbox UI with no message selected.
     */
    public function inbox($params) {
        $token = $this->require_authentication();
        $this->showInboxUI($token->getUser());
    }

    /**
     * Full path: GET '/messages/:msgId'
     *
     * Displays the message inbox UI with one message selected.
     */
    public function read($params) {
        $token = $this->require_authentication();
        $msgId = $params['msgID'];
        $db = $this->getDBConn();

        // Fetch message to display
        $message = Message::fetch($db, $msgId);

        if($message->getUserTo()->getUserId() != $token->getUser()->getUserId() || $message == null) {
            $this->addFlashMessage('Could not find message', self::FLASH_LEVEL_USER_ERR);
            $this->showInboxUI($token->getUser(), null);
        }

        // Show message page.
        $this->showInboxUI($token->getUser(), $message);
    }

    /**
     * Full path: POST 'messages/:msgId/delete'
     *
     * Deletes a message from the server. Only a the sender or recipient can delete a message.
     */
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

        // Actually delete message.
        if (Message::delete($this->getDBConn(), $msgId)) {
            $this->addFlashMessage("Deleted message", self::FLASH_LEVEL_INFO);
        } else {
            $this->addFlashMessage("Unable to delete message", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect('/messages');
    }

    /**
     * Full path: POST '/messages/clear'
     *
     * Deletes all of the messages sent to the user and redirects back to the message page
     */
    public function clear($params) {
        $token = $this->require_authentication();

        if (Message::deleteToReciever($this->getDBConn(), $token->getUser()->getUserId())) {
            $this->addFlashMessage("Inbox cleared.", self::FLASH_LEVEL_INFO);
        } else {
            $this->addFlashMessage("Unable to delete messages", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect('/messages');
    }

    /**
     * Full path: '/messages/create'
     *
     * Creates a message on the server.
     */
    public function create($params) {
        $token = $this->require_authentication();
        $recipientID = $_POST['recipientId'];
        $msgText = $_POST['messageText'];
        $db = $this->getDBConn();

        // Check that we can message the user and get their info if so.
        $recipientInfo = Message::canMessageUser($db, $token->getUser()->getUserId(), $recipientID);
        if (!$recipientInfo) {
            $this->addFlashMessage("Cannot message user! Make sure you both follow each other, or their account is listed as Public.", self::FLASH_LEVEL_USER_ERR);
            $this->redirect('/messages');
        }

        // Create message object
        $message = Message::build2($recipientInfo['followid'], date('Y-m-d H:i:s'), $msgText);

        // Save message
        if(!$message->commit($db)) {
            $this->addFlashMessage("Unable to send message.", self::FLASH_LEVEL_SERVER_ERR);
        } else {
            $this->addFlashMessage("Sent message.", self::FLASH_LEVEL_INFO);
        }

        $this->redirect('/messages');
    }


    /**
     * Helper function, displays the Inbox page
     * @param User $u - Current user
     * @param Message|null $message - Message to display, no message displayed if null
     */
    private function showInboxUI(User $u, ?Message $message = null) {
        $db = $this->getDBConn();

        $recieved = Message::fetchAllRecipient($db, $u->getUserId());
        $sent = Message::fetchAllSender($db, $u->getUserId());
        $messageableUsers = Message::getMessageableUsers($db, $u->getUserId());

        require 'app/views/inbox.phtml';
    }
}