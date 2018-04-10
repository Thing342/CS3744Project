<?php
include "app/views/_header.phtml"
?>

<?php
$conn = new mysqli("localhost", "root", "", "fantasticfour_p4") or die('Error: '.$conn->connect_error);
?>


<div id = "whiteTextDiv" style="padding-left: 10px">

<?php if($this->getLoggedInUser()->getType() == (int)3): ?>
	<h1 style="padding-top: 20px"><b>Admin Controls</b></h1>

	<h3>Commenters</h3>
  <div style="padding-left: 10px">
  	<?php
   	$query = "SELECT * FROM `user` WHERE `type` = 1";
    	if ($result = mysqli_query($conn, $query)) {
    		while ($row = $result->fetch_assoc()) {
          printf("%s", $row["username"]);
         	echo "<form action = \"";
         	echo $_ENV['SUBDIRECTORY'];
         	echo "\deleteUser\" onSubmit=\"return verification()\", method=\"post\">";
     			echo"<input type=\"hidden\" name=\"idToDelete\" value=\"";
          printf("%s", $row["userId"]);
      		echo "\"</label>";
     			echo"<input type=\"submit\" value=\"Delete Account\">";
      		echo "</form>";

          echo "<form action = \"";
          echo $_ENV['SUBDIRECTORY'];
          echo "\oneToTwo\" onSubmit=\"return verification()\", method=\"post\">";
          echo"<input type=\"hidden\" name=\"idToPromote\" value=\"";
          printf("%s", $row["userId"]);
          echo "\"</label>";
          echo"<input type=\"submit\" value=\"Make Editor\">";
          echo "</form>";

      		echo "<br>";
          }
      }
      ?>
    </div>
    <h3>Editors</h3>
    <div style="padding-left: 10px">
    <?php
 	$query = "SELECT * FROM `user` WHERE `type` LIKE 2";
  	if ($result = mysqli_query($conn, $query)) {
  		while ($row = $result->fetch_assoc()) {
        printf("%s", $row["username"]);
        echo "<form action = \"";
        echo $_ENV['SUBDIRECTORY'];
        echo "\deleteUser\" onSubmit=\"return verification()\", method=\"post\">";
        echo"<input type=\"hidden\" name=\"idToDelete\" value=\"";
        printf("%s", $row["userId"]);
        echo "\"</label>";
        echo"<input type=\"submit\" value=\"Delete Account\">";
        echo "</form>";

        echo "<form action = \"";
        echo $_ENV['SUBDIRECTORY'];
        echo "\TwoToOne\" onSubmit=\"return verification()\", method=\"post\">";
        echo"<input type=\"hidden\" name=\"idToDemote\" value=\"";
        printf("%s", $row["userId"]);
        echo "\"</label>";
        echo"<input type=\"submit\" value=\"Remove Editing Access\">";
        echo "</form>";

        echo "<form action = \"";
        echo $_ENV['SUBDIRECTORY'];
        echo "\TwoToThree\" onSubmit=\"return verification()\", method=\"post\">";
        echo"<input type=\"hidden\" name=\"idToPromote\" value=\"";
        printf("%s", $row["userId"]);
        echo "\"</label>";
        echo"<input type=\"submit\" value=\"Make Admin\">";
        echo "</form>";

    		echo "<br>";
        }
    }
    ?>
  </div>
    <h3>Admins</h3>
    <div style="padding-left: 10px">
    <?php
 	$query = "SELECT * FROM `user` WHERE `type` LIKE 3";
  	if ($result = mysqli_query($conn, $query)) {
  		while ($row = $result->fetch_assoc()) {
        printf("%s", $row["username"]);
        echo "<form action = \"";
        echo $_ENV['SUBDIRECTORY'];
        echo "\deleteUser\" onSubmit=\"return verification()\", method=\"post\">";
        echo"<input type=\"hidden\" name=\"idToDelete\" value=\"";
        printf("%s", $row["userId"]);
        echo "\"</label>";
        echo"<input type=\"submit\" value=\"Delete Account\">";
        echo "</form>";
    		
        echo "<form action = \"";
        echo $_ENV['SUBDIRECTORY'];
        echo "\ThreeToTwo\" onSubmit=\"return verification()\", method=\"post\">";
        echo"<input type=\"hidden\" name=\"idToDemote\" value=\"";
        printf("%s", $row["userId"]);
        echo "\"</label>";
        echo"<input type=\"submit\" value=\"Remove Admin Access\">";
        echo "</form>";

        echo "<br>";
      }
    }
    ?>
  </div>
<?php else: ?>
    <p><i>You do not have permissions to view this page.</i></p>
<?php endif; ?>

</div>
<?php
include "app/views/_footer.phtml"
?>
