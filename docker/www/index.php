<?php
require_once "./database/connessione.php";

$result = $conn->query("SHOW TABLES LIKE 'film'");
if ($result->num_rows === 0) {
    require_once "./database/database.php";
}

header("Location: ./homepage.php");
exit();
?>