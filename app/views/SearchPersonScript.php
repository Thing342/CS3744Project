<?php
$conn = new mysqli("localhost", "root", "", "fantasticfour_p4") or die('Error: '.$conn->connect_error);

$lname = mysqli_real_escape_string($conn, $_POST['lname']);

echo"<h4>Search Results</h4>";

//Searches through database for related individual
//$query = "SELECT person.firstname,person.lastname,person.rank,person.unitID,unit.name FROM person INNER JOIN unit ON person.unitID = unit.id";
$query = "SELECT person.firstname,person.lastname,person.rank,person.unitID, unit.name FROM person INNER JOIN unit ON person.unitID = unit.id WHERE person.lastname='".$lname."'";
//$query = "SELECT .* FROM `person` INNER JOIN `unit` ON (`person`.`unitID`=`unit`.`id`) WHERE `lastname` LIKE '".$lname."'";
  if ($result = mysqli_query($conn, $query)) {
   while ($row = $result->fetch_assoc()) {
    //echo"<a href = ";
    //echo $_ENV['SUBDIRECTORY'];
    //echo "/companies/";
    //echo $row["unitID"];
    //echo"\">";
    printf("%s %s (%s)", $row["firstname"], $row["lastname"], $row["rank"]);
    echo " - is a member of the ";
    printf("%s", $row["name"]);
    echo "<br>";

    // $query2 = "SELECT * FROM `unit` WHERE `id` LIKE '".$row["unitID"]."'";
    // if ($result = mysqli_query($conn, $query2)) {
    //   while ($unitRow = $result->fetch_assoc()) {
    //     echo $unitRow["name"];
    //   }
    // }

    //echo"</a>";
    //echo "<br>";
    //echo "<br>";
   }
   echo "<br>";
   echo "<br>";
   echo "If there are no results shown above, no results were found.";
   //echo "<a href =\"";
   //echo $_ENV['SUBDIRECTORY']; 
   //echo "/search\"'>";
   //echo "<br>";
   //echo "<br>";
   //echo "Search Again?";
   //echo "</a>";
 }
   else {
    echo $query;
   }
?>