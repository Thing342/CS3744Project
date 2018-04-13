<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 4/13/18
 * Time: 4:27 PM
 */

namespace app\models;


interface UserEvent
{
    public function getTimestamp() : string;
    public function getText() : string;
    public function getUser() : User;
    public function getId() : int ;
    public function getEventType() : string;

}

function UserEvent_sorting_key(UserEvent &$a, UserEvent &$b): int
{
    return -strcmp($a->getTimestamp(), $b->getTimestamp());
}