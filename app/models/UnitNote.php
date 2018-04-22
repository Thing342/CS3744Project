<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 3/22/18
 * Time: 10:45 PM
 */

namespace app\models;


use PDO;

/**
 * Class UnitNote
 * @package app\models
 *
 * Represents a single event belonging to a Unit.
 */
class UnitNote
{
    private $id = -1;
    private $unitID = -1;
    private $title = "NoName";
    private $content = "NoDescription";
    private $imageURL = null;

    private $changed = false;

    /**
     * Builds a UnitNote object from the parameters. (Used by the DB querying code)
     */
    public static function build(int $id, int $unitID, string $title, string $description, string $imageURL) : UnitNote {
        $event = new UnitNote();
        $event->id = $id;
        $event->unitID = $unitID;
        $event->title = $title;
        $event->content = $description;
        $event->imageURL = $imageURL;
        return $event;
    }

    /**
     * Fetches all the events owned by a Unit.
     * @param PDO $db - DB connection
     * @param int $unitID - Unit to search through
     *
     * @param string $sql - optional field to use differing SQL query
     * @param array $params - array or map to use as prepared stament values for this query
     *
     * @return array|null - An array of events, or null if there's an error.
     */
    public static function fetchAllInUnit(PDO $db, int $unitID, string $sql = "", array $params=[]) : ?array {
        // Use default statement if `$sql` is not set
        if ($sql == "") {
            $sql = "SELECT * FROM UnitNote WHERE unitID = :uid";
        }

        $params['uid'] = $unitID;

        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);

        if($res == false) {
            error_log("Could not query UnitNotes (" . $stmt->queryString . "):" . $stmt->errorCode());
            return null;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\UnitNote::build');
        return $rows;
    }

    /**
     * Fetches a single event by ID
     * @param PDO $db - DB Connection
     * @param int $noteid - ID to query for
     * @return UnitNote|null - A new UnitNote, or null if no such event exists
     */
    public static function fetch(PDO $db, int $noteid) : ?UnitNote {
        $sql = 'SELECT * FROM UnitNote WHERE id = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$noteid]);

        if($res == false) {
            error_log("Could not query UnitNotes (" . $stmt->queryString . "):" . $stmt->errorCode());
            return null;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\UnitNote::build'); // calls build() with the columns as parameters and stores results in array

        if (sizeof($rows) < 1) { // No such event exists
            error_log("No event matching id: " . $noteid);
            return null;
        } else {
            return $rows[0];
        }
    }

    /**
     * Deletes the model from the database
     * @param PDO $db
     * @param int $noteid
     * @return bool false if the query failed
     */
    public static function delete(PDO $db, int $noteid) : bool {
        $stmt = $db->prepare('DELETE FROM UnitNote WHERE id = ?');
        $res = $stmt->execute([$noteid]);

        if(!$res) {
            error_log('Unable to delete Event!: ' . $stmt->errorCode());
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
            $stmt = $db->prepare('INSERT INTO UnitNote VALUE (0, :uid, :en, :des, :img)');
            $res = $stmt->execute([
                "uid" => $this->unitID, "en" => $this->title, 'des' => $this->content, 'img' => $this->imageURL
            ]);
        } else {
            $stmt = $db->prepare('UPDATE UnitNote SET unitID=:uid, title=:en, content=:des, imageURL=:img WHERE id = :id');
            $res = $stmt->execute([
                "uid" => $this->unitID, "en" => $this->title, 'des' => $this->content,
                'img' => $this->imageURL,  'id' => $this->id
            ]);
        }

        if ($res) { // update event id
            $this->changed = false;
            if ($this->id == -1) {
                $this->id = $db->lastInsertId();
            }
        }
        else {
            error_log("Unable to commit Event object!: " . $stmt->errorCode());
        }

        return $res;
    }

    /**
     * @return array - json-encodeable array containing the data in this class.
     */
    public function serialize(): array {
        return [
            "id" => $this->id,
            "unitID" => $this->unitID,
            "eventName" => $this->title,
            "description" => $this->content,
            "imageURL" => $this->imageURL,
        ];
    }

    /**
     * Returns the url-compatible name for this event
     * (suitable for wikipedia API)
     * @return string
     */
    public function getURLName() : string {
        return urlencode($this->title);
    }

    /***
     * GETTERS & SETTERS
     ***/

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUnitID(): int
    {
        return $this->unitID;
    }

    /**
     * @param int $unitID
     * @return UnitNote
     */
    public function setUnitID(int $unitID): UnitNote
    {
        $this->unitID = $unitID;
        $this->changed = true;
        return $this;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): UnitNote {
        $this->title = $title;
        $this->changed = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return UnitNote
     */
    public function setContent(string $content): UnitNote
    {
        $this->content = $content;
        $this->changed = true;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getImageURL(): ?string
    {
        if (ctype_space($this->imageURL)) {
            return $this->imageURL;
        } else {
            return null;
        }
    }

    /**
     * @param string|null $imageURL
     */
    public function setImageURL(?string $imageURL): UnitNote
    {
        if (ctype_space($imageURL)) {
            $this->imageURL = $imageURL;
        } else {
            $this->imageURL = null;
        }

        $this->changed = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->changed;
    }

}