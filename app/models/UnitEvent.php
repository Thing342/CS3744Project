<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 3/22/18
 * Time: 10:45 PM
 */

namespace app\models;


use PDO;

class UnitEvent
{
    private $id = -1;
    private $unitID = -1;
    private $type = "NoType";
    private $date = "1900-00-00";
    private $description = "NoDescription";
    private $locationName = "NoLocation";
    private $latitude = 0.0;
    private $longitude = 0.0;

    private $changed = false;

    public static function build(int $id, int $unitID, string $type, string $date, string $description, string $locationName, float $latitude, float $longitude) : UnitEvent {
        $event = new UnitEvent();
        $event->id = $id;
        $event->unitID = $unitID;
        $event->type = $type;
        $event->date = $date;
        $event->description = $description;
        $event->locationName = $locationName;
        $event->latitude = $latitude;
        $event->longitude = $longitude;
        return $event;
    }

    public static function fetchAllInUnit(PDO $db, int $unitID, string $sql = "", array $params=[]) : ?array {
        if ($sql == "") {
            $sql = "SELECT * FROM UnitEvent WHERE unitID = :uid";
        }

        $params['uid'] = $unitID;

        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);

        if($res == false) {
            error_log("Could not query UnitEvents (" . $stmt->queryString . "):" . $stmt->errorCode());
            return null;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\UnitEvent::build');
        return $rows;
    }

    public static function fetchAllInUnitByType(PDO $db, int $unitID, string $type) : ?array {
        $query = 'SELECT * FROM UnitEvent WHERE unitID = :uid AND type = :type';
        $params = ['type' => $type];
        return self::fetchAllInUnit($db, $unitID, $query, $params);
    }

    public static function fetch(PDO $db, int $eventid) : ?UnitEvent {
        $sql = 'SELECT * FROM UnitEvent WHERE id = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$eventid]);

        if($res == false) {
            error_log("Could not query UnitEvents (" . $stmt->queryString . "):" . $stmt->errorCode());
            return null;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\UnitEvent::build');
        if (sizeof($rows) < 1) {
            error_log("No event matching id: " . $eventid);
            return null;
        } else {
            return $rows[0];
        }
    }

    /**
     * Deletes the model from the database
     * @param PDO $db
     * @param int $eventid
     * @return bool false if the query failed
     */
    public static function delete(PDO $db, int $eventid) : bool {
        $stmt = $db->prepare('DELETE FROM UnitEvent WHERE id = ?');
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
            $stmt = $db->prepare('INSERT INTO UnitEvent VALUE (0, :uid, :t, :d, :des, :ln, :lat, :lon)');
            $res = $stmt->execute([
                "uid" => $this->unitID, "t" => $this->type, 'd' => $this->date, 'des' => $this->description,
                'ln' => $this->locationName, 'lat' => $this->latitude, 'lon' => $this->longitude
            ]);
        } else {
            $stmt = $db->prepare('UPDATE UnitEvent SET unitID=:uid, type=:t, date=:d, description=:des, locationName=:ln, latitude=:lat, longitude=:lon WHERE id = :id');
            $res = $stmt->execute([
                "uid" => $this->unitID, "t" => $this->type, 'd' => $this->date, 'des' => $this->description,
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
            "unitID" => $this->unitID,
            "type" => $this->type,
            "date" => $this->date,
            "description" => $this->description,
            "locationName" => $this->locationName,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude
        ];
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
     * @return UnitEvent
     */
    public function setUnitID(int $unitID): UnitEvent
    {
        $this->unitID = $unitID;
        $this->changed = true;
        return $this;
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
     * @return UnitEvent
     */
    public function setType(string $type): UnitEvent
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
     * @return UnitEvent
     */
    public function setDate(string $date): UnitEvent
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
     * @return UnitEvent
     */
    public function setDescription(string $description): UnitEvent
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
     * @return UnitEvent
     */
    public function setLocationName(string $locationName): UnitEvent
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
     * @return UnitEvent
     */
    public function setLatitude(float $latitude): UnitEvent
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
     * @return UnitEvent
     */
    public function setLongitude(float $longitude): UnitEvent
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

}