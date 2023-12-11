<?php
include("env.php");

// Define database information 
$sname = getenv('DB_HOST');
$unmae = getenv('DB_USER');
$password = getenv('DB_PASS');
$db_name = getenv('DB_NAME');    // Check Database Name Before.


// Connect to the database
$conn = mysqli_connect($sname, $unmae, $password, $db_name);       //   no need write any thing in die() for server connection problem.

echo $conn->error;

if (!$conn) {
    header("location: ./404.php");
    exit();
} 
// else {
//     header("location: ./index.php");
//     exit();
// }

if (!mysqli_query($conn, "SELECT `allposets`.`idx` FROM posets.`allposets` LIMIT 1;")) {
    echo "<div class='error' style='margin-top: 10rem'>Please Upload Data To The Database To See The Web Page Properly.</div>";
    header("location: ./404.php");
    exit();
}
?>
