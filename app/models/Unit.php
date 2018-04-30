<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 3/22/18
 * Time: 11:09 AM
 */

namespace app\models;

use PDO;

/**
 * Class Unit
 * @package app\models
 *
 * Model class for a company within the battalion.
 */
class Unit
{
    private $id = -1;
    private $name = "Noname";
    private $subunits = [];
    private $unitParentID = -1;

    private $changed = false; // true when the model is no longer in sync with the DB.

    const DEFAULT_PHOTO_PATH = "public/img/786th.jpg"; // TODO: replace

    /**
     * Build a Unit from database params.
     * @param int $id - Public key of the Unit.
     * @param string $name - The name of the unit.
     * @return Unit
     */
    public static function build(int $id, string $name, ?int $unitParent, ?string $subunits_str): Unit {
        $unit  = new Unit();
        $unit->id = $id;
        $unit->name = $name;

        if($unitParent != null) {
            $unit->unitParentID = $unitParent;
        } else {
            $unit->unitParentID = -1;
        }

        if($subunits_str != null) {
            $unit->subunits = [];
            foreach (explode(',', $subunits_str) as $subunitID_str) {
                array_push($unit->subunits, intval($subunitID_str));
            }
        } else {
            $unit->subunits = [];
        }

        return $unit;
    }

    /**
     * Fetches a Unit from the database.
     * @param PDO $db
     * @param int $unitid
     * @return Unit|null - null if no such unit was found, or if the query failed.
     */
    public static function fetch(PDO $db, int $unitid) : ?Unit {
        $fetch_sql = <<<SQL
SELECT U.*, S.children
FROM Unit U
LEFT JOIN (
    SELECT unitParent, GROUP_CONCAT(id) as children
    FROM Unit
    GROUP BY unitParent
    ) AS S ON S.unitParent = U.id
WHERE U.id = ?
LIMIT 1
SQL;
        $stmt = $db->prepare($fetch_sql);
        $res = $stmt->execute([$unitid]);

        if ($res == false) {
            error_log("Unable to fetch Unit!: " . $stmt->errorCode());
            return null;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\Unit::build');
        if (sizeof($rows) < 1) {
            error_log("No units matching id: " . $unitid);
            return null;
        } else {
            return $rows[0];
        }
    }

    /**
     * Fetches the list of Units from the database.
     * @param PDO $db
     * @param int $unitid
     * @return [Unit]|null - null if the query failed.
     */
    public static function fetchAll(PDO $db) : ?array {
        $fetch_sql = <<<SQL
SELECT U.*, S.children
FROM Unit U
LEFT JOIN (
    SELECT unitParent, GROUP_CONCAT(id) as children
    FROM Unit
    GROUP BY unitParent
    ) AS S ON S.unitParent = U.id
SQL;
        $stmt = $db->prepare($fetch_sql);
        $res = $stmt->execute();

        if ($res == false) {
            error_log("Unable to fetch Units!: " . $stmt->errorCode());
            return null;
        }

        $rows = $stmt->fetchAll(PDO::FETCH_FUNC, 'app\models\Unit::build');
        return $rows;
    }

    /**
     * Deletes a Unit from the database.
     * @param PDO $db
     * @param int $unitid
     * @return bool - true if successful
     */
    public static function delete(PDO $db, int $unitid): bool {
        $stmt = $db->prepare('DELETE FROM Unit WHERE id = ?');
        $res = $stmt->execute([$unitid]);

        if(!$res) {
            error_log('Unable to delete Unit!: ' . $stmt->errorCode());
        }

        return $res;
    }

    /**
     * Saves the changes made to this unit to the database
     * @param PDO $db
     * @return bool - true if successful
     */
    public function commit(PDO $db): bool {
        $res = false;
        if ($this->id == -1) { // new object
            $stmt = $db->prepare('INSERT INTO Unit VALUE (0,?,?)');
            $res = $stmt->execute([
                $this->name,
                ($this->unitParentID == -1) ? null : $this->unitParentID
            ]);
        }  else { // update existing
            $stmt = $db->prepare('UPDATE Unit SET name = ?, unitParent = ? WHERE id = ?');
            $res = $stmt->execute([
                $this->name,
                ($this->unitParentID == -1) ? null : $this->unitParentID,
                $this->id
            ]);
        }

        if ($res) { // update token id
            $this->changed = false;
            if ($this->id == -1) {
                $this->id = $db->lastInsertId();
            }
        }
        else {
            error_log("Unable to commit Unit object!: " . $stmt->errorCode());
        }

        return $res;
    }

    /**
     * Gets the IDs of the top-level units in the batallion (for whom no parent exists)
     * @param PDO $db
     * @return [int]|null - null if the query failed.
     */
    public static function getTopLevelUnitIDs(PDO $db) : ?array {
        $stmt = $db->prepare("SELECT GROUP_CONCAT(id) FROM Unit WHERE unitParent IS NULL");
        $res = $stmt->execute();

        if(!$res) {
            error_log("Unable to query top-level units: " . $stmt->errorCode());
            return null;
        }

        $result = $stmt->fetchColumn();
        $ids = [];

        foreach (explode(',', $result) as $subunitID_str) {
            array_push($ids, intval($subunitID_str));
        }

        return $ids;
    }

    public function serialize(): array {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "unitParent" => $this->unitParentID,
            "subunitIDs" => $this->subunits
        ];
    }

    /**
     * Returns the external (client-facing) URL for this image. (Use this for client stuff)
     * Example: /cs3744/project6/fantasticfour/uploads/images/companies/5.jpg
     * @return string
     */
    public function getPhotoFileURL(): string {
        return $_ENV['SUBDIRECTORY'] . "/uploads/images/companies/" .$this->getId(). '.jpg';
    }

    /**
     * Returns the internal (server-side) path for this image file. (Use this for server stuff)
     * Example: uploads/images/companies/5.jpg
     * @return string
     */
    public function getPhotoFilePath(): string {
        return self::getPhotoFilePathID($this->getId());
    }

    /**
     * Returns the internal (server-side) path for this image file. (Use this for server stuff)
     * Example: uploads/images/companies/5.jpg
     * @return string
     */
    public static function getPhotoFilePathID(int $id): string {
        return "uploads/images/companies/" .$id. '.jpg';
    }

    /***
     * Getters and Setters
     ***/

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): Unit {
        $this->name = $name;
        $this->changed = true;
        return $this;
    }

    /**
     * @return array[int]
     */
    public function getSubunits(): array
    {
        return $this->subunits;
    }

    /**
     * @return int
     */
    public function getUnitParentID(): int
    {
        return $this->unitParentID;
    }

    /**
     * @param int $unitParentID
     * @return Unit
     */
    public function setUnitParentID(int $unitParentID): Unit
    {
        $this->unitParentID = $unitParentID;
        $this->changed = true;
        return $this;
    }

    public function getChanged(): bool {
        return $this->changed;
    }

}
