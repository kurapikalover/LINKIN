<?php

require_once "config/database.php";

$result = pg_query($conn, "SELECT user_id, name, email, user_type FROM users");

if (!$result) {
    die("Query failed.");
}

echo "<h1>Users in database</h1>";

while ($user = pg_fetch_assoc($result)) {
    echo "ID: " . $user["user_id"] . "<br>";
    echo "Name: " . $user["name"] . "<br>";
    echo "Email: " . $user["email"] . "<br>";
    echo "Type: " . $user["user_type"] . "<br>";
    echo "<hr>";
}

?>