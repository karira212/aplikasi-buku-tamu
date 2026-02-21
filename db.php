<?php
require_once __DIR__ . '/config.php';

function db_connect()
{
    static $conn;
    if ($conn instanceof mysqli) {
        return $conn;
    }
    $conn = new mysqli($GLOBALS['db_host'], $GLOBALS['db_user'], $GLOBALS['db_pass'], $GLOBALS['db_name']);
    if ($conn->connect_error) {
        http_response_code(500);
        echo 'Koneksi database gagal';
        exit;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
