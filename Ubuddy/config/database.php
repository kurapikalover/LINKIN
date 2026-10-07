<?php

$host = "aws-0-ap-northeast-2.pooler.supabase.com";
$port = "5432";
$dbname = "postgres";
$user = "postgres.uebvnwupbxkaghmuvfhz";
$password = "L1nk1N_Ub4ddies";

$conn = pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password sslmode=require"
);

if (!$conn) {
    die("Database connection failed.");
}

?>