<?php

$conn = new mysqli("localhost", "root", "", "fantasticfour_p4") or die('Error: '.$conn->connect_error);

//Gets ID of user to delete
$idToDelete = $_POST['idToDelete'];
$idToDeleteInt = (int)$idToDelete;

//Deletes entry from database
$q = "DELETE FROM `user` WHERE `user`.`userID`=".$idToDeleteInt."";

//Redirects upon successful addition to database
if($conn->query($q)===TRUE) {
  echo "User deleted."
}
else {
  echo "failed";
}

?>