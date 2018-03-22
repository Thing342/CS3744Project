<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/26/18
 * Time: 2:59 PM
 */

namespace app\models;

use PDO;

/**
 * Class Token - Represents a logged-in user session. Will expire and be removed from the database if the user logs out, or is inactive for too long.
 * @property User user - Associated user
 * @property int tokenId - Token's unique ID
 * @property string created - When the token was created
 * @property string expires - The token's expiration date; if the current time is greater than this, this token will be deleted.
 * @package app\models
 */
class Token
{
    private $tokenId = -1; // int
    private $user; // User
    private $created; //  string
    private $expires; // string

    private $changed = false; // true if the token is synchronized with the database

    /**
     * Fetches a token from the database.
     * @param PDO $db - database connection
     * @param int $tokenid - token id to search for
     * @return Token|null - A token if the ID is valid. Otherwise, null.
     */
    public static function fetch(\PDO $db, int $tokenid) : ?Token{
        self::deleteExpired($db); // flush out old tokens

        // Retireve from database
        $fetch_sql = 'SELECT * FROM UserToken JOIN User ON UserToken.user = User.userId WHERE tokenId = ? LIMIT 1';
        $stmt = $db->prepare($fetch_sql);
        $res = $stmt->execute([$tokenid]);

        if($res == false) {
            error_log("Unable to fetch user token!: ".$stmt->errorCode());
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row == null) return null;

        // extend token lease and save back to database
        $token = self::build($row);
        $token->setExpires(date("Y-m-d H:i:s", time() + 30 * 60));
        $token->commit($db);

        return $token;
    }

    /**
     * Builds a token from an dictionary.
     * @param $row - dictionary returned from database
     * @return Token
     */
    public static function build($row) : Token
    {
        $token = new Token();
        $token->tokenId = $row['tokenId'];
        $token->created = $row['created'];
        $token->expires = $row['expires'];
        $token->user = User::build($row);

        return $token;
    }

    /**
     * Saves the changes made to this token to the database
     * @param PDO $db
     * @return bool
     */
    public function commit(\PDO $db): bool {
        $res = false;
        if($this->tokenId == -1) { // New token
            $stmt = $db->prepare('INSERT INTO UserToken VALUE (0, :userid, :created, :expires)');
            $res = $stmt->execute([
                "userid" => $this->user->getUserId(), "created" => $this->created, "expires" => $this->expires
            ]);
        } else { // update existing
            $stmt = $db->prepare('UPDATE UserToken SET user = :userid, created = :created, expires = :expires WHERE tokenId = :tokenid');
            $res = $stmt->execute([
                "tokenid"=>$this->tokenId,
                "userid" => $this->user->getUserId(),
                "created" => $this->created,
                "expires" => $this->expires
            ]);
        }

        if ($res) { // update token id
            $this->changed = false;
            $this->tokenId = $db->lastInsertId();
        }
        else error_log("Unable to insert or update user object!: ".$stmt->errorCode());
        return $res;
    }

    /**
     * Delete a token from the database
     * @param PDO $db - database connection
     * @param int $tokenId - Token id to delete
     * @return bool - true if successful.
     */
    public static function delete(\PDO $db, int $tokenId ) {
        $stmt = $db->prepare('DELETE FROM UserToken WHERE tokenId = ?');
        $res = $stmt->execute([$tokenId]);

        if(!$res) {
            error_log("Unable to delete user token!: ".$stmt->errorCode());
        }

        return $res;
    }

    /**
     * Delete all of a user's tokens (thus logging them out on all devices)
     * @param PDO $db - database connection
     * @param int $userId - user to log out
     * @return bool - true if successful
     */
    public static function deleteUser(\PDO $db, int $userId ) {
        $stmt = $db->prepare('DELETE FROM UserToken WHERE user = ?');
        $res = $stmt->execute([$userId]);

        if(!$res) {
            error_log("Unable to delete user tokens!: ".$stmt->errorCode());
        }

        return $res;
    }

    /**
     * Delete expired tokens from the database.
     * @param PDO $db - database connection
     * @return bool - true if successful
     */
    public static function deleteExpired(\PDO $db) {
        $stmt = $db->prepare('DELETE FROM UserToken WHERE expires <= NOW()');
        $res = $stmt->execute();

        if(!$res) {
            error_log("Unable to delete user token!: ".$stmt->errorCode());
        }

        return $res;
    }

    /***
    * Getters & Setters
     ***/

    /**
     * @return int
     */
    public function getTokenId(): int
    {
        return $this->tokenId;
    }

    /**
     * @param int $tokenId
     */
    public function setTokenId(int $tokenId): void
    {
        $this->tokenId = $tokenId;
        $this->changed = true;
    }

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->changed = true;
    }

    /**
     * @return string
     */
    public function getCreated() : string
    {
        return $this->created;
    }

    /**
     * @param string $created
     */
    public function setCreated(string $created): void
    {
        $this->created = $created;
        $this->changed = true;
    }

    /**
     * @return string
     */
    public function getExpires() : string
    {
        return $this->expires;
    }

    /**
     * @param string $expires
     */
    public function setExpires(string $expires): void
    {
        $this->expires = $expires;
        $this->changed = true;
    }
}