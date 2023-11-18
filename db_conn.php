<?php
// Define database informations 
$sname = "localhost";
$unmae = "root";
$password = "";
$db_name = "posets";    // Check Database Name Before.

// Connect to the database
$conn = mysqli_connect($sname, $unmae, $password, $db_name);       //   no need write any thing in die() for server connection problem.

if (!$conn) {
    header("location: ./404.php");
    exit();
}

if (!mysqli_query($conn, "SELECT `allposets`.`idx` FROM posets.`allposets` LIMIT 1;")) {
    echo "<div class='error' style='margin-top: 10rem'>Please Upload Data To The Database To See The Web Page Properly.</div>";
    // header("location: ./404.php");
    exit();
}
?>



<!-- CREATE TABLE `posets`.`users`(
    `id` TINYINT(11) NOT NULL AUTO_INCREMENT,
    `user_name` VARCHAR(11) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    PRIMARY KEY(`id`),
    UNIQUE(`user_name`),
    UNIQUE `Password`(`password`)
    ) ENGINE = InnoDB; -->
<!-- INSERT INTO `users` (`id`, `user_name`, `password`) VALUES (NULL, 'neamul', '@@@'), (NULL, 'msu', 'msu'); -->