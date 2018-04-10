<?php
/**
* Created by PhpStorm.
* User: wes
* Date: 2/25/18
* Time: 1:10 PM
*/

namespace app\models;

use PDO;

/**
* Class User
* @package app\models
*
* Model class for a User.
*/
class User
{

  private $userId = -1; // int, the user's unique ID. Will == -1 if the user has not been committed to the database yet.
  private $username; // string, the user's unique username
  private $pword_hash; // string, a hashed representation of the password
  private $email; // string, the user's email address.

  private $type;

  private $changed = false; // bool, true if the model is not in sync with the database

  /**
  * Builds a User object from a database row.
  * @param array $row PDO database row.
  * @return User
  */
  public static function build(array $row) : User {
    $user = new User();
    $user->userId = (int) $row["userId"];
    $user->username = $row["username"];
    $user->pword_hash = $row["pword_hash"];
    $user->email = $row["email"];
    $user->type = (int)$row["type"];

    return $user;
  }

  /**
  * Fetches a User record from the database and returns a model
  * @param PDO $db DB connection
  * @param string $username username to look up
  * @return User A user object, or null if no matching userId could be found.
  */
  public static function fetchByName(\PDO $db, string $username) : ?User {
    $stmt = $db->prepare('SELECT * FROM User WHERE username = ? LIMIT 1');
    $res = $stmt->execute([$username]);

    if($res == false) {
      error_log("Unable to fetch user object!: ".$stmt->errorCode());
      return null;
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row == null) return null;

    return self::build($row);
  }

  /**
  * Fetches a User record from the database and returns a model
  * @param PDO $db DB connection
  * @param int $userId User ID to look up
  * @return User A user object, or null if no matching userId could be found.
  */
  public static function fetch(\PDO $db, int $userId): ?User {
    $stmt = $db->prepare('SELECT * FROM User WHERE userId = ? LIMIT 1');
    $res = $stmt->execute([$userId]);

    if($res == false) {
      error_log("Unable to fetch user object!: ".$stmt->errorCode());
      return null;
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row == null) return null;

    return self::build($row);
  }
  /**
  * Fetches the list of Users from the database.
  * @param PDO $db
  * @param int $userid
  * @return User|null - null if the query failed.
  */
  public static function fetchAll(PDO $db) : ?array {
    $fetch_sql = 'SELECT userId FROM User';
    $stmt = $db->prepare($fetch_sql);
    $res = $stmt->execute();

    if ($res == false) {
      error_log("Unable to fetch Users!: " . $stmt->errorCode());
      return null;
    }

    $results = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $rows = array();
    foreach ($results as $row){
      $rows[] = User::fetch($db, $row);
    }
    return $rows;
  }
  /**
  * Commits the changes made to this data model to the database.
  * @param \PDO $db database connection
  * @return bool True if the changes were successful
  */
  public function commit(\PDO $db): bool {
    $res = false;
    if ($this->userId == -1) { // new object
      $stmt = $db->prepare('INSERT INTO User VALUE (0, :username, :pword_hash, :email, :type)');
      $res = $stmt->execute([
        "username"=> $this->username, "pword_hash" => $this->pword_hash, "email" => $this->email, "type" => $this->type
      ]);
    } else {
      $stmt = $db->prepare('UPDATE User SET username = :username, pword_hash = :pword_hash, email = :email, type = :type WHERE userId = :userId');
      $res = $stmt->execute([
        "username"=> $this->username, "pword_hash" => $this->pword_hash, "email" => $this->email, "type" => $this->type
      ]);
    }

    if($res) {
      $this->changed = false;
      if ($this->userId == -1) {
        $this->userId = $db->lastInsertId();
      }
    }
    else error_log("Unable to insert or update user object!: ".$stmt->errorCode());
    return $res;
  }

  /**
  * Deletes this user object from the database.
  * @param PDO $db database connection
  * @return bool True if the changes were successful
  */
  public static function delete(\PDO $db, int $userId) : bool {
    $stmt = $db->prepare('DELETE FROM User WHERE userId = ?');
    $res = $stmt->execute([$userId]);

    if(!$res) {
      error_log("Unable to delete user object!: ".$stmt->errorCode());
    }

    return $res;
  }

  /****
  * Getter & Setters
  ****/

  /**
  * @return int
  */
  public function getUserId(): int
  {
    return $this->userId;
  }

  /**
  * @return string
  */
  public function getUsername(): string
  {
    return $this->username;
  }

  /**
  * @param string $username
  * @return User
  */
  public function setUsername(string $username): User
  {
    $this->username = $username;
    $this->changed = true;
    return $this;
  }

  /**
  * @return string
  */
  public function getPasswordHash(): string
  {
    return $this->pword_hash;
  }

  /**
  * @param string $password
  * @return User
  */
  public function setPassword(string $password): User
  {
    $this->pword_hash = password_hash($password, PASSWORD_BCRYPT);
    $this->changed = true;
    return $this;
  }

  /**
  * @return string
  */
  public function getEmail(): string
  {
    return $this->email;
  }

  /**
  * @param string $email
  * @return User
  */
  public function setEmail(string $email): User
  {
    $this->email = $email;
    $this->changed = true;
    return $this;
    /**
    * @return int
    */
    public function getType(): int
    {
      return $this->type;
    }

    /**
    * @param string $username
    * @return User
    */
    public function setType(int $type): User
    {
      $this->type = $type;
      $this->changed = true;
      return $this;
    }
}
