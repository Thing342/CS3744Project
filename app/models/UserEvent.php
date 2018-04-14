<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 4/13/18
 * Time: 4:27 PM
 */

namespace app\models;


/**
 * Interface type for Models representing events that can go on the User's Activity Feed.
 *
 * Implementations:
 * - Comment
 * - Message
 *
 * Interface UserEvent
 * @package app\models
 */
interface UserEvent
{
    /**
     * Get a MySQL-compatible timestamp for this object.
     * @return string
     */
    public function getTimestamp() : string;

    /**
     * Gets the text content of this event
     * @return string
     */
    public function getText() : string;

    /**
     * Gets the User responsible for this event.
     * @return User
     */
    public function getUser() : User;

    /**
     * Gets the table ID for this event
     * @return int
     */
    public function getId() : int ;

    /**
     * Gets a string distinguishing the type of event.
     * @return string
     */
    public function getEventType() : string;

}

/**
 * Sorting key, sorts events in reverse chronological order.
 * (Located outside the class for dumb PHP reasons)
 */
function UserEvent_sorting_key(UserEvent &$a, UserEvent &$b): int
{
    return -strcmp($a->getTimestamp(), $b->getTimestamp());
}