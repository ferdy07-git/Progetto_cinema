<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Logout</title>
</head>
<body>
    <script>
        // Distrugge la sessione JS
        sessionStorage.clear();

        // Redirect
        window.location.href = "../../homepage.php";
    </script>
</body>
</html>