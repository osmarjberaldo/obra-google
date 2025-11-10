<?php
function getDbConnection() {
    $host = 'srv1782.hstgr.io';
    $db   = 'u558355875_monitorobra';
    $user = 'u558355875_monitorobra';
    $pass = 'Zi362514*';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        error_log("Erro ao conectar ao banco: " . $e->getMessage());
        throw new RuntimeException('Erro ao conectar ao banco: ' . $e->getMessage());
    }
}
