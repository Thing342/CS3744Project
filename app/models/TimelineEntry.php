<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 4/21/18
 * Time: 01:24 PM
 */

namespace app\models;


use PDO;

/**
 * Class TimelineEntry
 * @package app\models
 *
 * Represents a single event in the batallion's timeline.
 */
class TimelineEntry
{
    private $id = -1;
    private $eventName = "NoName";
    private $type = "NoType";
    private $date = "1900-00-00";
    private $description = "NoDescription";
    private $locationName = "NoLocation";
    private $latitude = 0.0;
    private $longitude = 0.0;

    private $changed = false;

    /**
     * Builds a TimelineEntry object from the parameters. (Used by the DB querying code)
     */
    public static function build(int $id, string $eventName, string $type, string $date, string $description, string $locationName, float $latitude, float $longitude) : TimelineEntry {
        $event = new TimelineEntry();
        $event->id = $id;
        $event->eventName = $eventName;
        $event->type = $type;
        $event->date = $date;
        $event->description = $description;
        $event->locationName = $locationName;
        $event->latitude = $latitude;
        $event->longitude = $longitude;
        return $event;
    }

    /**
     * Fetches a single event by ID
     * @param PDO $db - DB Connection
     * @param int $eventid - ID to query for
     * @return TimelineEntry|null - A new TimelineEntry, or null if no such event exists
     */
    public static function fetch(PDO $db, int $eventid) : ?TimelineEntry {
        $sql = 'SELECT * FROM TimelineEntry WHERE id = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$eventid]);

        if($res == false) {
            error_log("Could not query TimelineEntrys (" . $stmt->queryString . "):" . $stmt->errorCode());
            return null;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\TimelineEntry::build'); // calls build() with the columns as parameters and stores results in array

        if (sizeof($rows) < 1) { // No such event exists
            error_log("No event matching id: " . $eventid);
            return null;
        } else {
            return $rows[0];
        }
    }

    /**
     * Fetches all the events.
     * @param PDO $db - DB connection
     *
     * @param string $sql - optional field to use differing SQL query
     * @param array $params - array or map to use as prepared stament values for this query
     *
     * @return array|null - An array of events, or null if there's an error.
     */
    public static function fetchAll(PDO $db, string $sql = "", array $params=[]) : ?array {
        // Use default statement if `$sql` is not set
        if ($sql == "") {
            $sql = "SELECT * FROM TimelineEntry";
        }
        $params = [];
        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);
        if($res == false) {
            error_log("Could not query TimelineEntrys (" . $stmt->queryString . "):" . $stmt->errorCode());
            return null;
        }
        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\TimelineEntry::build');
        return $rows;
    }

    /**
     * Wrapper for fetchAll, filters by event type.
     * @param string $type - type of event to filter for
     */
    public static function fetchAllByType(PDO $db, string $type) : ?array {
        $query = 'SELECT * FROM TimelineEntry WHERE type = :type';
        $params = ['type' => $type];
        return self::fetchAll($db, $query, $params);
    }

    /**
     * Deletes the model from the database
     * @param PDO $db
     * @param int $eventid
     * @return bool false if the query failed
     */
    public static function delete(PDO $db, int $eventid) : bool {
        $stmt = $db->prepare('DELETE FROM TimelineEntry WHERE id = ?');
        $res = $stmt->execute([$eventid]);

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
            $stmt = $db->prepare('INSERT INTO TimelineEntry VALUE (0, :en, :t, :d, :des, :ln, :lat, :lon)');
            $res = $stmt->execute([
                "en" => $this->eventName, "t" => $this->type, 'd' => $this->date, 'des' => $this->description,
                'ln' => $this->locationName, 'lat' => $this->latitude, 'lon' => $this->longitude
            ]);
        } else {
            $stmt = $db->prepare('UPDATE TimelineEntry SET eventName=:en, type=:t, date=:d, description=:des, locationName=:ln, latitude=:lat, longitude=:lon WHERE id = :id');
            $res = $stmt->execute([
                "en" => $this->eventName, "t" => $this->type, 'd' => $this->date, 'des' => $this->description,
                'ln' => $this->locationName, 'lat' => $this->latitude, 'lon' => $this->longitude, 'id' => $this->id
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
            "eventName" => $this->eventName,
            "type" => $this->type,
            "date" => $this->date,
            "description" => $this->description,
            "locationName" => $this->locationName,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude
        ];
    }

    /**
     * Returns the url-compatible name for this event
     * (suitable for wikipedia API)
     * @return string
     */
    public function getURLName() : string {
        return urlencode($this->eventName);
    }

    /**
     * Returns a pretty string containing this event's location info
     * @return string
     */
    public function getLocationString(): string {
        return "$this->locationName ($this->latitude, $this->longitude)";
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return TimelineEntry
     */
    public function setType(string $type): TimelineEntry
    {
        $this->type = $type;
        $this->changed = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return TimelineEntry
     */
    public function setDate(string $date): TimelineEntry
    {
        $this->date = $date;
        $this->changed = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return TimelineEntry
     */
    public function setDescription(string $description): TimelineEntry
    {
        $this->description = $description;
        $this->changed = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationName(): string
    {
        return $this->locationName;
    }

    /**
     * @param string $locationName
     * @return TimelineEntry
     */
    public function setLocationName(string $locationName): TimelineEntry
    {
        $this->locationName = $locationName;
        $this->changed = true;
        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     * @return TimelineEntry
     */
    public function setLatitude(float $latitude): TimelineEntry
    {
        $this->latitude = $latitude;
        $this->changed = true;
        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     * @return TimelineEntry
     */
    public function setLongitude(float $longitude): TimelineEntry
    {
        $this->longitude = $longitude;
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

    public function getEventName(): string {
        return $this->eventName;
    }

    public function setEvent(string $eventName): TimelineEntry {
        $this->eventName = $eventName;
        $this->changed = true;
        return $this;
    }

}