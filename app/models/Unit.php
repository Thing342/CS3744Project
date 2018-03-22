<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 3/22/18
 * Time: 11:09 AM
 */

namespace app\models;

use PDO;

class Unit
{
    private $id = -1;
    private $name = "Noname";

    private $changed = false; // true when the model is no longer in sync with the DB.

    /**
     * Build a Unit from database params.
     * @param int $id - Public key of the Unit.
     * @param string $name - The name of the unit.
     * @return Unit
     */
    public static function build(int $id, string $name): Unit {
        $unit  = new Unit();
        $unit->id = $id;
        $unit->name = $name;
        return $unit;
    }

    /**
     * Fetches a Unit from the database.
     * @param PDO $db
     * @param int $unitid
     * @return Unit|null - null if no such unit was found, or if the query failed.
     */
    public static function fetch(PDO $db, int $unitid) : ?Unit {
        $fetch_sql = 'SELECT * FROM Unit WHERE id = ? LIMIT 1';
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
     * Fetches the complete list of Units from the database.
     * @param PDO $db
     * @param int $unitid
     * @return Unit|null - null if the query failed.
     */
    public static function fetchAll(PDO $db) : ?array {
        $fetch_sql = 'SELECT * FROM Unit';
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
            $stmt = $db->prepare('INSERT INTO Unit VALUE (0,?)');
            $res = $stmt->execute([
                $this->name
            ]);
        }  else { // update existing
            $stmt = $db->prepare('UPDATE Unit SET name = ? WHERE id = ?');
            $res = $stmt->execute([
                $this->name, $this->id
            ]);
        }

        if ($res) { // update token id
            $this->changed = false;
            $this->id = $db->lastInsertId();
        }
        else {
            error_log("Unable to commit Unit object!: " . $stmt->errorCode());
        }

        return $res;
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

    public function getChanged(): bool {
        return $this->changed;
    }

}