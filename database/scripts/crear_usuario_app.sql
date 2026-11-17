-- Usuario dedicado para la aplicación Next Level.
-- Ejecutar como root una sola vez. Cambia 'CAMBIA_ESTA_CLAVE' antes de correrlo.
-- En MariaDB/MySQL 8 el cliente CLI puede fallar con caching_sha2_password;
-- usa database/scripts/crear_usuario_app.php (vía PDO) como alternativa.
-- Principio de mínimo privilegio: solo la base reportesnextlevel_v2.

CREATE USER IF NOT EXISTS 'nextlevel_app'@'localhost' IDENTIFIED WITH mysql_native_password BY 'CAMBIA_ESTA_CLAVE';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
  ON reportesnextlevel_v2.* TO 'nextlevel_app'@'localhost';

FLUSH PRIVILEGES;
