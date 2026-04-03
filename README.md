# Leccionario Digital

Sistema de gestión de leccionarios para instituciones educativas.

## Requisitos

- **Servidor web:** Apache con mod_rewrite
- **PHP:** 8.0 o superior
- **Base de datos:** MySQL 5.7+ o MariaDB 10.2+
- **XAMPP/LAMP** (o similar) para desarrollo local

## Instalación

### 1. Clonar o copiar el proyecto

Copia la carpeta `leccionario-digital` en tu servidor web.

Para XAMPP:
```
C:\xampp\htdocs\leccionario-digital\
```

### 2. Crear la base de datos

Ejecuta el script SQL en MySQL:

```bash
mysql -u root -p < database/schema.sql
```

O desde phpMyAdmin:
1. Importa el archivo `database/schema.sql`

### 3. Configurar la conexión

Edita el archivo `config/config.php`:

```php
'database' => [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'leccionario_digital',
    'username' => 'root',        // Tu usuario MySQL
    'password' => '',            // Tu contraseña MySQL
    'charset' => 'utf8mb4',
],
```

### 4. Configurar el servidor

 Asegúrate de que el archivo `.htaccess` en `public/` tenga el RewriteBase correcto:

```apache
RewriteBase /leccionario-digital/public/
```

### 5. Acceder al sistema

Abre en tu navegador:
```
http://localhost/leccionario-digital/
```

## Credenciales por defecto

| Usuario | Contraseña |
|---------|------------|
| admin@leccionario.local | admin123 |

> **Importante:** Cambia la contraseña del administrador después del primer inicio de sesión.

## Características

- **Multi-rol:** Docentes y Coordinadores
- **Temas:** Litera (claro) y Slate (oscuro) con toggle
- **Offline:** Todos los assets son locales (no requiere CDN)
- **Responsive:** Funciona en móvil, tablet y desktop

## Estructura del proyecto

```
leccionario-digital/
├── app/
│   ├── Controllers/     # Controladores MVC
│   ├── Core/            # Clases base (Router, Database, Auth, etc.)
│   ├── Models/          # Modelos de datos
│   ├── Repositories/     # Patrón Repository
│   └── Views/           # Vistas PHP
├── config/
│   └── config.php       # Configuración
├── database/
│   └── schema.sql       # Estructura de BD
├── public/
│   ├── assets/          # CSS, JS, fonts
│   ├── .htaccess        # URL rewriting
│   └── index.php        # Entry point
└── routes/
    └── web.php          # Rutas
```

## Módulos

### Módulo Docente
- Definición de horario semanal
- Calendario con estado de leccionarios
- Registro de leccionarios
- Historial de leccionarios

### Módulo Coordinador
- Dashboard con estadísticas
- Gestión de profesores (CRUD)
- Gestión de cursos (CRUD)
- Gestión de asignaturas (CRUD)
- Revisión de leccionarios
- Reportes y exportación
- Configuración (habilitar edición de horarios)

## Temas

El sistema incluye dos temas de Bootswatch:

- **Litera** (claro) - Tema por defecto
- **Slate** (oscuro) - Alternativo

El cambio de tema se guarda en localStorage y funciona offline.

## Notificaciones (opcional)

Para habilitar el envío de correos, configura en `config/config.php`:

```php
'smtp' => [
    'host' => 'smtp.tuservidor.com',
    'port' => 587,
    'username' => 'tu@email.com',
    'password' => 'tu_password',
    'from_email' => 'noreply@tu-dominio.com',
    'from_name' => 'Leccionario Digital'
],
```

## Actualizar contraseña de usuario

Para cambiar la contraseña de un usuario directamente en MySQL:

```sql
UPDATE usuarios 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'admin@leccionario.local';
-- La contraseña será: admin123
```

O genera una nueva con PHP:

```php
echo password_hash('tu_nueva_contraseña', PASSWORD_DEFAULT);
```

## Solución de problemas

### Error de conexión a la base de datos
- Verifica que MySQL esté corriendo
- Comprueba las credenciales en `config/config.php`
- Asegúrate de que la base de datos `leccionario_digital` exista

### Página en blanco
- Activa el modo debug en `config/config.php`:
```php
'app' => [
    'debug' => true,
],
```
- Revisa los errores en `php_error.log`

### URLs no funcionan (404)
- Asegúrate de que `mod_rewrite` esté habilitado en Apache
- Verifica el `RewriteBase` en `.htaccess`

### Tema no cambia
- Verifica que JavaScript esté habilitado
- Comprueba la consola del navegador por errores

## Licencia

Este proyecto es privado y fue desarrollado para uso de la institución.

## Soporte

Christian Rodriguez
