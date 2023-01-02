<?php
// Define database informations 
$sname = "localhost";
$unmae = "root";
$password = "";
$db_name = "Posets";    // Check Database Name Before.

// Connect to the database
$conn = mysqli_connect($sname, $unmae, $password, $db_name);       //   no need write any thing in die() for server connection problem.

if (!$conn) {
    header("location: ./404.php");
}
?>


<!-- INSERT INTO `users` (`idx`, `user_name`, `password`, `fullName`) VALUES (NULL, 'neamul', '@@@', 'Muhammad Neamul Hoqe'), (NULL, 'msu', 'msu', 'Prof. Dr. Muhammad Salah Uddin') -->

<!-- CREATE TABLE `posets`.`users` (`id` TINYINT(2) NOT NULL AUTO_INCREMENT , `user_name` VARCHAR(11) NOT NULL , `password` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`), UNIQUE (`user_name`(255)), UNIQUE `Password` (`password`(255))) ENGINE = InnoDB; -->