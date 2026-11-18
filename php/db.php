
<?php

$host = "aws-0-ap-northeast-2.pooler.supabase.com";
$port = "5432";
$dbname = "postgres";
$user = "postgres.uebvnwupbxkaghmuvfhz";
$password = "L1nk1N_Ub4ddies";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Database connection failed. Check your configuration.');
}
