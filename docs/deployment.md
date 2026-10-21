# Guía de despliegue — Next Level School

> Documento operativo para despliegue en servidor Linux/Windows con PHP, Nginx/Apache, MySQL 8 y Node.js.

## 1. Requisitos del servidor

| Componente | Versión recomendada | Notas |
|---|---|---|
| Sistema operativo | Ubuntu 24.04 LTS / Debian 12 / Windows Server 2022 | Cualquiera compatible con PHP 8.2+. |
| PHP | 8.2 o 8.3 | Extensiones requeridas: `bcmath`, `ctype`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`. |
| MySQL/MariaDB | MySQL 8.0 (recomendado) | MariaDB 10.4+ puede funcionar, pero el proyecto fue validado con MySQL 8. |
| Composer | 2.x | `--no-dev` en producción. |
| Node.js + npm | 20 LTS / 22 LTS | Para compilar assets con Vite. |
| Servidor web | Nginx 1.24+ o Apache 2.4+ | Con HTTPS. |
| Git | 2.x | Para despliegue con `git pull`. |

## 2. Preparación de la base de datos

```sql
CREATE DATABASE nextlevel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nextlevel_user'@'localhost' IDENTIFIED BY 'CONTRASEÑA_SEGURA';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER, DROP ON nextlevel.* TO 'nextlevel_user'@'localhost';
FLUSH PRIVILEGES;
```

> Usa un usuario con **privilegios mínimos** para la aplicación. Solo el proceso de migración/restauración requiere permisos adicionales en operaciones puntuales.

## 3. Clonación e instalación

La **rama oficial** es `master`.

```bash
git clone --branch master https://github.com/Isaknz/NEXT_LEVEL.git
cd NEXT_LEVEL
composer install --no-dev --prefer-dist --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

## 4. Configuración de `.env`

Configura al menos lo siguiente:

```env
APP_NAME="Next Level School"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://tu-dominio.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nextlevel
DB_USERNAME=nextlevel_user
DB_PASSWORD=CONTRASEÑA_SEGURA

MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=587
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@tu-dominio.com
MAIL_FROM_NAME="Next Level School"
```

## 5. Migraciones, seeders y assets

```bash
php artisan migrate --force
php artisan db:seed --force
npm ci
npm run build
php artisan storage:link
```

Las cuentas iniciales se crean con `UsuariosSeeder` (incluye `admin`, `gerente`, `secretaria`, `cajero`) con **clave temporal**. El primer ingreso obliga a cambiarlas.

## 6. Optimizaciones para producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 7. Permisos de carpetas

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 8. Configuración del servidor web

### Nginx (recomendado)

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tu-dominio.com;
    root /ruta/a/NEXT_LEVEL/public;

    ssl_certificate /ruta/cert.pem;
    ssl_certificate_key /ruta/key.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache

Asegura `AllowOverride All` en el `VirtualHost` apuntando a `public/`. Usa `.htaccess` incluido por Laravel o configura `mod_rewrite`.

## 9. HTTPS

Usa Let's Encrypt con Certbot:

```bash
sudo certbot --nginx -d tu-dominio.com
```

Renovación automática ya queda configurada por Certbot.

## 10. Tareas programadas (Scheduler)

Añade al crontab del servidor:

```cron
* * * * * cd /ruta/a/NEXT_LEVEL && php artisan schedule:run >> /dev/null 2>&1
```

## 11. Colas (opcional pero recomendado)

Si se usan colas en el futuro:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Gestionar con Supervisor en producción.

## 12. Respaldos

Ver procedimiento completo en [`docs/backup-restore.md`](backup-restore.md).

**Regla crítica:** mantener **copia externa cifrada** fuera del servidor de aplicación.

```bash
php artisan respaldo:crear
```

Programar con cron y subir a almacenamiento externo (S3/Google Drive/rclone) con rotación.

## 13. Health check

Verificar tras despliegue:

- [ ] `https://tu-dominio.com/login` carga correctamente
- [ ] Login con cuenta temporal obliga a cambio de clave
- [ ] Dashboard accesible según rol
- [ ] Módulos financieros responden correctamente
- [ ] Reportes y comprobantes generan PDF
- [ ] Auditoría registra operaciones
- [ ] `php artisan test` pasa en entorno de staging
- [ ] Assets compilados correctamente (`/build/` accesible)

## 14. Rollback

En caso de fallo:

```bash
git log --oneline -5
git checkout <commit-anterior>
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci && npm run build
php artisan migrate --force  # solo si las migraciones son reversibles
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Si hay cambios destructivos en BD, restaurar desde el último respaldo válido con `respaldo:restaurar`.

## 15. Checklist de producción

- [ ] `APP_DEBUG=false`
- [ ] HTTPS configurado y forzado
- [ ] Usuario MySQL con privilegios mínimos
- [ ] Claves SMTP configuradas
- [ ] Permisos de `storage/` y `bootstrap/cache/`
- [ ] Scheduler configurado
- [ ] Backup local + copia externa cifrada
- [ ] Prueba de restauración documentada
- [ ] Monitoreo/alertas configurados
- [ ] Acceso SSH restringido
- [ ] Secretos fuera del repositorio
