<?php
$conn = new mysqli("localhost", "root", "", "fantasticfour_p4") or die('Error: '.$conn->connect_error);
?>


<div id = "whiteTextDiv">
<h2><?=$company->name?></h2>

<p><b>Events</b></p>

<?php
$query = "SELECT * FROM `unitevent` WHERE `unitID` LIKE '".$company->id."'";
  if ($result = mysqli_query($conn, $query)) {
   while ($row = $result->fetch_assoc()) {
    printf("%s - %s (%s)", $row["eventName"], $row["type"], $row["date"]);
    echo "<br>";
    echo "<br>";
   }
 }
   else {
    echo $query;
   }
?>

<br>
<p><b>People</b></p>

<?php
$query = "SELECT * FROM `person` WHERE `unitID` LIKE '".$company->id."'";
  if ($result = mysqli_query($conn, $query)) {
   while ($row = $result->fetch_assoc()) {
    printf("%s, %s", $row["lastname"], $row["firstname"]);
    echo "<br>";
    echo "<br>";
   }
 }
   else {
    echo $query;
   }
?>

</div>