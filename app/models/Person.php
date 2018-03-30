<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 3/22/18
 * Time: 12:23 PM
 */

namespace app\models;


use PDO;

class Person
{
    private $id = -1;
    private $unitID = 0;
    private $rank = "NoRank";
    private $firstname = "NoFirstName";
    private $lastname = "NoLastName";

    private $changed = false;

    public static function build(int $id, int $unitID, string $rank, string $firstname, string $lastname) : Person {
        $person = new Person();
        $person->id = $id;
        $person->unitID = $unitID;
        $person->rank = $rank;
        $person->firstname = $firstname;
        $person->lastname = $lastname;

        return $person;
    }

    /**
     * Fetches the entire list of people from the database
     * @param PDO $db
     * @param string $sql - Optional SQL query to use to filter objects
     * @param array $params - Prepared paramters to privde to the sql statement, if it needs parameters
     * @return array|null - null if the query failed
     */
    public static function fetchAll(PDO $db, string $sql = "SELECT * FROM Person ORDER BY rank, lastname, firstname", array $params=[]): ?array {
        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);

        if($res == false) {
            error_log("Could not query People (" . $stmt->queryString ."):" . $stmt->errorCode());
            return null;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\Person::build');
        return $rows;
    }

    // All wrappers for above function, just with different filtering settings
    public static function fetchAllInUnit(PDO $db, int $unitID): ?array {
        return self::fetchAll($db, 'SELECT * FROM Person WHERE unitID = ? ORDER BY rank, lastname, firstname', [$unitID]);
    }
    public static function fetchAllWithRank(PDO $db, string $rank): ?array {
        return self::fetchAll($db, 'SELECT * FROM Person WHERE rank = ? ORDER BY rank, lastname, firstname', [$rank]);
    }
    public static function fetchAllWithRankInUnit(PDO $db, string $rank, int $unitID): ?array {
        return self::fetchAll($db, 'SELECT * FROM Person WHERE rank = ? AND unitID = ? ORDER BY rank, lastname, firstname', [$rank, $unitID]);
    }
    public static function fetchAllWithLastname(PDO $db, string $lastname): ?array {
        return self::fetchAll($db, 'SELECT * FROM Person WHERE lastname = ? ORDER BY rank, lastname, firstname', [$lastname]);
    }

    /**
     * Searches for people given a firstname and a lastname
     * @param PDO $db - DB connection
     * @param string $firstnameQuery - query string for firstname
     * @param string $lastnameQuery - query string for lastname
     * @return array|null - null if query is unsuccessful
     */
    public static function search(PDO $db, string $firstnameQuery, string $lastnameQuery): ?array {
        $sql = "SELECT U.name as unitName, Person.rank as rank, Person.unitID as unitID, Person.firstname as firstname, Person.lastname as lastname, Person.id AS id FROM Person JOIN Unit U ON Person.unitID = U.id WHERE firstname LIKE ? AND lastname LIKE ? ORDER BY lastname, firstname";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute(['%'.$firstnameQuery.'%', '%'.$lastnameQuery.'%']);

        if ($res == false) {
            error_log("Unable to search!: " . $stmt->errorCode());
            return null;
        }

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $val = [
                'person' => self::build($row['id'], $row['unitID'], $row['rank'], $row['firstname'], $row['lastname']),
                'unitName' => $row['unitName']
            ];
            array_push($results, $val);
        }

        return $results;
    }

    /**
     * Fetches the model from the database
     * @param PDO $db
     * @param int $personid
     * @return Person|null null if a person with the given ID doesn't exist, or if the query failed.
     */
    public static function fetch(PDO $db, int $personid): ?Person {
        $fetch_sql = 'SELECT * FROM Person WHERE id = ? LIMIT 1';
        $stmt = $db->prepare($fetch_sql);
        $res = $stmt->execute([$personid]);

        if ($res == false) {
            error_log("Unable to fetch Person!: " . $stmt->errorCode());
            return null;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\Person::build');
        if (sizeof($rows) < 1) {
            error_log("No people matching id: " . $personid);
            return null;
        } else {
            return $rows[0];
        }
    }

    /**
     * Deletes the model from the database
     * @param PDO $db
     * @param int $personid
     * @return bool false if the query failed
     */
    public static function delete(PDO $db, int $personid) : bool {
        $stmt = $db->prepare('DELETE FROM Person WHERE id = ?');
        $res = $stmt->execute([$personid]);

        if(!$res) {
            error_log('Unable to delete Person!: ' . $stmt->errorCode());
        }

        return $res;
    }

    /**
     * Saves the model to the database
     * @param PDO $db
     * @return bool false if the query failed
     */
    public function commit(PDO $db) : bool {
        $res = false;
        if ($this->id == -1) { // new object
            $stmt = $db->prepare('INSERT INTO Person VALUE (0,:u,:r,:fn,:ln)');
            $res = $stmt->execute([
                "u"=>$this->unitID, "r"=>$this->rank, "fn"=>$this->firstname, "ln"=>$this->lastname
            ]);
        }  else { // update existing
            $stmt = $db->prepare('UPDATE Person SET unitID=:u, rank=:r, firstname=:fn, lastname=:ln WHERE id = :id');
            $res = $stmt->execute([
                "u"=>$this->unitID, "r"=>$this->rank, "fn"=>$this->firstname, "ln"=>$this->lastname, "id"=>$this->id
            ]);
        }

        if ($res) { // update person id
            $this->changed = false;
            if ($this->id == -1) {
                $this->id = $db->lastInsertId();
            }
        }
        else {
            error_log("Unable to commit Person object!: " . $stmt->errorCode());
        }

        return $res;
    }

    /**
     * Convenience method to get the full name of a Person
     * @return string
     */
    public function getName() : string {
        return $this->getFirstname() . " " . $this->getLastname();
    }

    /**
     * @return array - json-encodeable array containing the data in this class.
     */
    public function serialize() : array {
        return [
            "id" => $this->id,
            "unitID" => $this->unitID,
            "rank" => $this->rank,
            "firstname" => $this->firstname,
            "lastname" => $this->lastname
        ];
    }

    /***
     * GETTERS & SETTERS
     ***/

    public function getId(): int
    {
        return $this->id;
    }

    public function getChanged(): bool
    {
        return $this->changed;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): Person {
        $this->firstname = $firstname;
        $this->changed = true;
        return $this;
    }

    public function getLastname(): string {
        return $this->lastname;
    }

    public function setLastname(string $lastname): Person {
        $this->lastname = $lastname;
        $this->changed = true;
        return $this;
    }

    public function getFullName() : string {
        return $this->getFirstname() . " " . $this->getLastname();
    }

    public function getRank(): string
    {
        return $this->rank;
    }

    public function setRank(string $rank): Person {
        $this->rank = $rank;
        $this->changed = true;
        return $this;
    }

    public function getUnitID(): int
    {
        return $this->unitID;
    }

    public function setUnitID(int $unitID): Person {
        $this->unitID = $unitID;
        $this->changed = true;
        return $this;
    }
}