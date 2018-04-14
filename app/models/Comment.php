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
 * Represents a comment left by a User on a Unit page.
 *
 * Class Comment
 * @package app\models
 */
class Comment implements UserEvent
{
    private $id = -1; // The comment ID
    private $user = null; // A User object representing the user that left this comment
    private $unit = -1; // The ID of the Unit that this comment was left on.
    private $timestamp = ""; // String timestamp for the time this comment was left.
    private $text = ""; // The comment text.

    private $changed = false; // True if the comment model is out of sync with the database.

    /**
     * Convenience method that builds a Comment object.
     * @param int $id The comment ID
     * @param int $user A User object representing the user that left this comment
     * @param int $unit The ID of the Unit that this comment was left on.
     * @param string $timestamp String timestamp for the time this comment was left.
     * @param string $text The comment text.
     * @return Comment - A Comment object.
     */
    public static function build(int $id, User $user, int $unit, string $timestamp, string $text) : Comment {
        $model = new Comment();
        $model->id = $id;
        $model->user = $user;
        $model->unit = $unit;
        $model->timestamp = $timestamp;
        $model->text = $text;
        return $model;
    }

    /**
     * Fetches all of the Comments left on a Unit page.
     * @param PDO $db
     * @param int $unit - The Unit ID
     * @return Comment[]
     */
    public static function fetchByUnit(PDO $db, int $unit) : ?array {
        $sql = "SELECT * FROM Comment JOIN User U ON Comment.user = U.userId WHERE unit = ? ORDER BY timestamp";

        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$unit]);

        if($res == false) {
            error_log("Could not query Comment (" . $stmt->queryString ."):" . $stmt->errorCode());
            return null;
        }

        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = User::build($row);
            array_push($results, self::build($row['id'], $user, $row['unit'], $row['timestamp'], $row['text']));
        }

        return $results;
    }

    /**
     * Fetches all of the comments left by a User.
     * @param PDO $db
     * @param int $user - The user's ID
     * @return Comment[]
     */
    public static function fetchByUser(PDO $db, int $user) : ?array {
        $sql = "SELECT * FROM Comment JOIN User U ON Comment.user = U.userId WHERE user = ? ORDER BY timestamp";

        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$user]);

        if($res == false) {
            error_log("Could not query Comment (" . $stmt->queryString ."):" . $stmt->errorCode());
            return null;
        }

        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = User::build($row);
            array_push($results, self::build($row['id'], $user, $row['unit'], $row['timestamp'], $row['text']));
        }

        return $results;
    }

    /**
     * Fetches all off the comments left by the user's followed users within a certain duration.
     * @param PDO $db
     * @param int $user - The user's ID
     * @param int $hoursAgo - The duration (in hours) to search. By default, it is unlimited
     * @return Comment[]
     */
    public static function fetchByFollow(PDO $db, int $user, int $hoursAgo = -1) : ?array {
        $sql = <<<SQL
SELECT * 
FROM Comment 
JOIN User U ON Comment.user = U.userId
JOIN Following F ON U.userId = F.userTo AND F.userFrom = ?
ORDER BY timestamp
SQL;
        $params = [$user];
        if($hoursAgo != -1) { // limit by duration
            $sql = <<<SQL
SELECT * 
FROM Comment 
JOIN User U ON Comment.user = U.userId
JOIN Following F ON U.userId = F.userTo AND F.userFrom = ?
WHERE TIMESTAMPDIFF(HOUR, timestamp, NOW()) < ?
ORDER BY timestamp
SQL;
            array_push($params, $hoursAgo);
        }

        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);

        if($res == false) {
            error_log("Could not query Comment (" . $stmt->queryString ."):" . $stmt->errorCode());
            return null;
        }

        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = User::build($row);
            array_push($results, self::build($row['id'], $user, $row['unit'], $row['timestamp'], $row['text']));
        }

        return $results;
    }

    /**
     * Deletes a comment.
     * @param PDO $db
     * @param int $commentid - ID of comment to delete.
     * @return bool - true if successful.
     */
    public static function delete(PDO $db, int $commentid): bool {
        $stmt = $db->prepare('DELETE FROM Comment WHERE id = ?');
        $res = $stmt->execute([$commentid]);

        if(!$res) {
            error_log('Unable to delete Comment!: ' . $stmt->errorCode());
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
            $stmt = $db->prepare('INSERT INTO Comment VALUE (0,?,?,?,?)');
            $res = $stmt->execute([
                $this->getUser()->getUserId(), $this->unit, $this->timestamp, $this->text
            ]);
        }  else { // update existing
            $stmt = $db->prepare('UPDATE Comment SET user = ?, unit = ?, timestamp = ?, text = ? WHERE id = ?');
            $res = $stmt->execute([
                $this->getUser()->getUserId(), $this->unit, $this->timestamp, $this->text, $this->id
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

    /**
     * Convert this Comment object into a serializable associative array.
     */
    public function serialize() {
        return [
            "id" => $this->getId(),
            "user" => $this->getUser()->serialize(),
            "unit" => $this->getUnitID(),
            "timestamp" => $this->getTimestamp(),
            "text" => $this->getTimestamp()
        ];
    }

    /**
     * Parse a comment from a serialized associative array
     * @param $row
     * @return Comment
     */
    public static function deserialize($row) : Comment {
        return self::build($row['id'], null, $row['unit'], $row['timestamp'], $row['text']);
    }

    /***
    * Getters & Setters
     ***/

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getUnitID(): int
    {
        return $this->unit;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): Comment
    {
        $this->changed = true;
        $this->user = $user;
        return $this;
    }

    /**
     * @param string $text
     * @return Comment
     */
    public function setText(string $text): Comment
    {
        $this->changed = true;
        $this->text = $text;
        return $this;
    }

    /**
     * @param string $timestamp
     * @return Comment
     */
    public function setTimestamp(string $timestamp): Comment
    {
        $this->timestamp = $timestamp;
        $this->changed = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return 'comment';
    }
}