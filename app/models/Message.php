<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 4/12/18
 * Time: 3:22 PM
 */

namespace app\models;


use PDO;

/**
 * Model class representing a Message sent between to Users, one whom Follows the other.
 * Class Message
 * @package app\models
 */
class Message implements UserEvent
{
    private $id = -1;
    private $userFrom = null;
    private $userTo = null;
    private $followId = -1;
    private $timestamp = "";
    private $text = "";

    private $changed = false;

    /**
     * Convenience method that builds a Message object
     * @param int $followId - The ID of the Follow relation to ship the message over.
     * @param string $timestamp - A string timestamp of when this message was sent.
     * @param string $text - The text content of the message.
     * @param int $id - The id of the message (by default 1 for an uninitialized object
     * @return Message - a new, uncomitted Message object.
     */
    public static function build2(int $followId, string $timestamp, string $text, int $id=-1) : Message {
        $model = new Message();
        $model->id = $id;
        $model->timestamp = $timestamp;
        $model->text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');;
        $model->followId = $followId;
        return $model;
    }

    /**
     * Parses a Message for a numbered MySQL row.
     * @param array $row - Numbered array containing message info
     * @return Message
     */
    public static function build(array $row): Message {
        $model = new Message();
        $model->id = $row[0];
        $model->timestamp = $row[2];
        $model->text = $row[3];
        $model->followId = $row[4];
        $model->userFrom = User::build([
            'userId' => $row[7],
            'username' => $row[8],
            'pword_hash' => $row[9],
            'email' => $row[10],
            'type' => $row[11],
            'firstname' => $row[12],
            'lastname' => $row[13],
            'privacy' => $row[14],
        ]);

        $model->userTo = User::build([
            'userId' => $row[15],
            'username' => $row[16],
            'pword_hash' => $row[17],
            'email' => $row[18],
            'type' => $row[19],
            'firstname' => $row[20],
            'lastname' => $row[21],
            'privacy' => $row[22],
        ]);

        return $model;
    }

    /**
     * Fetches all of the Messages sent to a given recipient within a given duration.
     * @param PDO $db
     * @param int $recipientID - The ID of the recipient
     * @param int $hoursAgo - How far back to search (in hours), by default, unlimited
     * @return array|null - null if the results set is empty or if they query could not be completed.
     */
    public static function fetchAllRecipient(PDO $db, int $recipientID, int $hoursAgo = -1) : ?array {
        $sql = <<<SQL
SELECT * 
FROM Message 
JOIN Following F ON Message.follow = F.id
JOIN User UF ON F.userFrom = UF.userId
JOIN User UT ON F.userTo = UT.userId
WHERE F.userTo = ?
ORDER BY timestamp
SQL;
        $params = [$recipientID];
        if ($hoursAgo != -1) { // need to filter by timespan
            $sql = <<<SQL
SELECT * 
FROM Message 
JOIN Following F ON Message.follow = F.id
JOIN User UF ON F.userFrom = UF.userId
JOIN User UT ON F.userTo = UT.userId
WHERE F.userTo = ? AND TIMESTAMPDIFF(HOUR, timestamp, NOW()) < ?
ORDER BY timestamp
SQL;
            array_push($params, $hoursAgo);
        }

        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);

        if($res == false) {
            error_log("Could not query Message (" . $stmt->queryString ."):" . $stmt->errorCode());
            return null;
        }

        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            array_push($results, self::build($row));
        }


        return $results;
    }

    /**
     * Fetches all of the Messages sent by a given sender within a given duration.
     * @param PDO $db
     * @param int $recipientID - The ID of the sender
     * @return array|null - null if the results set is empty or if they query could not be completed.
     */
    public static function fetchAllSender(PDO $db, int $recipientID) : ?array {
        $sql = <<<SQL
SELECT * 
FROM Message 
JOIN Following F ON Message.follow = F.id
JOIN User UF ON F.userFrom = UF.userId
JOIN User UT ON F.userTo = UT.userId
WHERE F.userFrom = ?
ORDER BY timestamp
SQL;
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$recipientID]);

        if($res == false) {
            error_log("Could not query Message (" . $stmt->queryString ."):" . $stmt->errorCode());
            return null;
        }

        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            array_push($results, self::build($row));
        }


        return $results;
    }

    /**
     * Fetches a single message
     * @param PDO $db
     * @param int $msgId - The ID of the message
     * @return Message|null - null if the results set is empty or if they query could not be completed.
     */
    public static function fetch(PDO $db, int $msgId) : ?Message {
        $sql = <<<SQL
SELECT * 
FROM Message 
JOIN Following F ON Message.follow = F.id
JOIN User UF ON F.userFrom = UF.userId
JOIN User UT ON F.userTo = UT.userId
WHERE Message.id = ?
LIMIT 1
SQL;
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$msgId]);

        if($res == false) {
            error_log("Could not query Message (" . $stmt->queryString ."):" . $stmt->errorCode());
            return null;
        }

        $results = null;
        if($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $results = self::build($row);
        }

        return $results;
    }

    /**
     * Checks if a User can send a message to one another.
     * If the recipient's account is PUBLIC, then the sender only needs to follow them to send a message.
     * If the recipient's account is PRIVATE, then they must both follow each other to send messages.
     * @param PDO $db
     * @param int $sender - User ID of the sender.
     * @param int $reciever - User ID of the reciever
     * @return array|null - null if the query failed or if the Users cannot message each other. Otherwise,
     * a dict containing the follow ID between the two, plus the user object of the recipient.
     */
    public static function canMessageUser(PDO $db, int $sender, int $reciever) : ?array {
        $sql = <<<SQL
SELECT F.id AS followid, U.*
FROM Following F
LEFT JOIN Following F2 ON (F.userFrom = F2.userTo AND F2.userFrom = F.userTo)
JOIN User U ON F.userTo = U.userId
WHERE F.userFrom = ? AND F.userTo = ? AND (
  (U.privacy = 'PUBLIC') OR (F2.id IS NOT NULL)
)
LIMIT 1
SQL;

        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$sender, $reciever]);

        if($res == false) {
            error_log("Could not query messageable users! (" . $stmt->queryString ."):" . $stmt->errorCode());
            return null;
        }

        $result = null;
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = [
                "followid" => $row['followid'],
                "user" => User::build($row)
            ];
        }

        return $result;
    }

    /**
     * Gets a list of Users that the sender can send messages to.
     * If the recipient's account is PUBLIC, then the sender only needs to follow them to send a message.
     * If the recipient's account is PRIVATE, then they must both follow each other to send messages.
     * @param PDO $db
     * @param int $sender - User ID of the sender.
     * @return User[]|null - null if the query failed of if the results set is empty.
     */
    public static function getMessageableUsers(PDO $db, int $sender) : ?array {
        $sql = <<<SQL
SELECT F.id AS followid, U.*
FROM Following F
LEFT JOIN Following F2 ON (F.userFrom = F2.userTo AND F2.userFrom = F.userTo)
JOIN User U ON F.userTo = U.userId
WHERE F.userFrom = ? AND (
  (U.privacy = 'PUBLIC') OR (F2.id IS NOT NULL)
)
SQL;

        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$sender]);

        if($res == false) {
            error_log("Could not query messageable users! (" . $stmt->queryString ."):" . $stmt->errorCode());
            return null;
        }

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['followid']] = User::build($row);
        }

        return $results;
    }

    /**
     * Deletes a message.
     * @param PDO $db
     * @param int $msgid - ID of the message to delete.
     * @return bool - True on success.
     */
    public static function delete(PDO $db, int $msgid): bool {
        $stmt = $db->prepare('DELETE FROM Message WHERE id = ?');
        $res = $stmt->execute([$msgid]);

        if(!$res) {
            error_log('Unable to delete Message!: ' . $stmt->errorCode());
        }

        return $res;
    }

    /**
     * Deletes all messages from a given sender.
     * @param PDO $db
     * @param int $senderId - User ID of the sender to delete.
     * @return bool - True on success.
     */
    public static function deleteFromSender(PDO $db, int $senderId): bool {
        $stmt = $db->prepare('DELETE Message FROM Message JOIN Following F ON Message.follow = F.id WHERE userFrom = ?');
        $res = $stmt->execute([$senderId]);

        if(!$res) {
            error_log('Unable to delete Messages!: ' . $stmt->errorCode());
        }

        return $res;
    }

    /**
     * Deletes all messages to a given receiver.
     * @param PDO $db
     * @param int $recieverId - The user id of the reciever.
     * @return bool - true if successful.
     */
    public static function deleteToReciever(PDO $db, int $recieverId): bool {
        $stmt = $db->prepare('DELETE Message FROM Message JOIN Following F ON Message.follow = F.id WHERE userTo = ?');
        $res = $stmt->execute([$recieverId]);

        if(!$res) {
            error_log('Unable to delete Messages!: ' . $stmt->errorCode());
        }

        return $res;
    }

    /**
     * Saves the changes made to this comment to the database
     * @param PDO $db
     * @return bool - true if successful
     */
    public function commit(PDO $db): bool {
        $res = false;
        if ($this->id == -1) { // new object
            $stmt = $db->prepare('INSERT INTO Message VALUE (0,?,?,?)');
            $res = $stmt->execute([
                $this->followId, $this->timestamp, $this->text
            ]);
        }  else { // update existing
            $stmt = $db->prepare('UPDATE Message SET follow = ?, timestamp = ?,  text = ? WHERE id = ?');
            $res = $stmt->execute([
                $this->followId, $this->timestamp, $this->text, $this->id
            ]);
        }

        if ($res) { // update id
            $this->changed = false;
            if ($this->id == -1) {
                $this->id = $db->lastInsertId();
            }
        }
        else {
            error_log("Unable to commit Comment object!: " . $stmt->errorCode());
        }

        return $res;
    }

    // GETTERS AND SETTERS

    /**
     * @return int
     */
    public function getFollowId(): int
    {
        return $this->followId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @return User
     */
    public function getUserFrom() : User
    {
        return $this->userFrom;
    }

    /**
     * @return User
     */
    public function getUserTo() : User
    {
        return $this->userTo;
    }

    public function getUser(): User
    {
        return $this->getUserFrom();
    }

    public function getEventType(): string
    {
        return 'message';
    }
}