<?php
$pw = 'nextl3v3l_2026_app!';
$adminPw = getenv('DB_ADMIN_PASS');
if ($adminPw === false || $adminPw === '') {
    fwrite(STDERR, "Define DB_ADMIN_PASS con la clave de root antes de ejecutar.\n");
    exit(1);
}
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', $adminPw, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec("DROP USER IF EXISTS 'nextlevel_app'@'localhost'");
$pdo->exec("CREATE USER 'nextlevel_app'@'localhost' IDENTIFIED BY '$pw'");
$pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES ON reportesnextlevel_v2.* TO 'nextlevel_app'@'localhost'");
$pdo->exec('FLUSH PRIVILEGES');
echo "Usuario creado\n";

// Verificación de conexión y privilegios
$nuevo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=reportesnextlevel_v2;charset=utf8mb4', 'nextlevel_app', $pw, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
echo "Conexion OK, users: " . $nuevo->query('SELECT COUNT(*) FROM users')->fetchColumn() . "\n";

// Comprobar que NO puede tocar otras bases
try {
    $nuevo->query('SELECT 1 FROM mysql.user');
    echo "ADVERTENCIA: puede leer mysql.user\n";
} catch (PDOException $e) {
    echo "mysql.user inaccesible (correcto)\n";
}
