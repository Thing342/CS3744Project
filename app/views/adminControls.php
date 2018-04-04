<?php
include "app/views/_header.phtml"
?>

<?php
$conn = new mysqli("localhost", "root", "", "fantasticfour_p4") or die('Error: '.$conn->connect_error);
?>


<div id = "whiteTextDiv">

<?php if($this->getLoggedInUser()->getType() == (int)3): ?>
	<h2>Admin Controls</h2>

	<h3>Commenters</h3>
	<?php
 	$query = "SELECT * FROM `user` WHERE `type` LIKE 1";
  	if ($result = mysqli_query($conn, $query)) {
  		while ($row = $result->fetch_assoc()) {
        	printf("%s", $row["username"]);

        	echo "<form action = \"";
        	echo $_ENV['SUBDIRECTORY'];
        	echo "\adminDelete\" onSubmit=\"return verification()\", method=\"post\">";
   			echo"<input type=\"hidden\" name=\"idToDelete\" value=\"";
   			printf("%s", $row["userID"]);
    		echo "\"</label>";
   			echo"<input type=\"submit\" value=\"Delete Account\">";
    		echo "</form>";
    		echo "<br>";
        }
    }
    ?>
    <h3>Editors</h3>
    <?php
 	$query = "SELECT * FROM `user` WHERE `type` LIKE 2";
  	if ($result = mysqli_query($conn, $query)) {
  		while ($row = $result->fetch_assoc()) {
        	printf("%s", $row["username"]);
        	echo "<form action = \"";
        	echo $_ENV['SUBDIRECTORY'];
        	echo "\adminDelete\" onSubmit=\"return verification()\", method=\"post\">";
   			echo"<input type=\"hidden\" name=\"idToDelete\" value=\"";
   			printf("%s", $row["userID"]);
    		echo "\"</label>";
   			echo"<input type=\"submit\" value=\"Delete Account\">";
    		echo "</form>";
    		echo "<br>";
        }
    }
    ?>
    <h3>Admins</h3>
    <?php
 	$query = "SELECT * FROM `user` WHERE `type` LIKE 3";
  	if ($result = mysqli_query($conn, $query)) {
  		while ($row = $result->fetch_assoc()) {
        	printf("%s", $row["username"]);
        	echo "<form action = \"";
        	echo $_ENV['SUBDIRECTORY'];
        	echo "\adminDelete\" onSubmit=\"return verification()\", method=\"post\">";
   			echo"<input type=\"hidden\" name=\"idToDelete\" value=\"";
   			printf("%s", $row["userID"]);
    		echo "\"</label>";
   			echo"<input type=\"submit\" value=\"Delete Account\">";
    		echo "</form>";
    		echo "<br>";
        }
    }
    ?>
<?php else: ?>
    <p><i>You do not have permissions to view this page.</i></p>
<?php endif; ?>

</div>
<?php
include "app/views/_footer.phtml"
?>
