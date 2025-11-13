# Nexus Framework

Un framework PHP profesional y modular construido desde cero sin dependencias externas pesadas. Diseñado para ser ligero, extensible y educativo.

## 🚀 Características

### 🏗️ Arquitectura
- **MVC Pattern**: Separación clara de responsabilidades
- **PSR-4 Autoloading**: Usando Composer para carga automática de clases
- **Service Providers**: Arquitectura modular con proveedores de servicios
- **Dependency Injection**: Contenedor DI para gestión de dependencias

### 🗄️ Base de Datos
- **Multi-driver Support**: MySQL, PostgreSQL y SQLite con PDO
- **Query Builder**: Constructor de consultas fluido
- **ORM Avanzado**: Modelo con CRUD completo, fillable, hidden, validaciones y relaciones (hasMany, belongsTo, etc.)
- **Migrations**: Sistema de migraciones completo con comandos CLI para gestión de esquema
- **Seeders**: Población automática de datos de prueba

### 🔐 Seguridad y Autenticación
- **Session-based Auth**: Sistema de autenticación con sesiones
- **Password Hashing**: Hashing seguro de contraseñas
- **Middleware Protection**: Protección de rutas con middleware
- **Input Sanitization**: Sanitización automática de entrada

### 🛣️ Routing y Middleware
- **Flexible Router**: Enrutamiento con parámetros dinámicos
- **Middleware Pipeline**: Sistema de middleware encadenable
- **Built-in Middleware**:
  - `auth`: Protección de rutas autenticadas
  - `guest`: Restricción para usuarios no autenticados
  - `log`: Logging automático de requests
  - `sanitize`: Sanitización de input
  - `cors`: Configuración CORS
  - `throttle`: Rate limiting
  - `validate`: Validación de datos

### ✅ Validación y Logging
- **Custom Validator**: Sistema de validación extensible con reglas personalizables
- **File-based Logging**: Sistema de logs configurable
- **Error Handling**: Manejo graceful de errores

### 🎨 Frontend
- **Template Engine**: Sistema de vistas con layouts y herencia
- **Asset Management**: Estructura organizada para CSS/JS
- **Bootstrap Integration**: Framework CSS incluido

### 🧪 Testing
- **Unit Tests**: Suite de pruebas para componentes core
- **Test Helpers**: Utilidades para testing automatizado

## 📋 Requisitos

- **PHP**: 8.0 o superior
- **Composer**: Para gestión de dependencias
- **Base de Datos**: MySQL, PostgreSQL o SQLite
- **Servidor Web**: Apache/Nginx o servidor de desarrollo PHP

## 🛠️ Instalación

1. **Clona el repositorio**
   ```bash
   git clone <repository-url>
   cd scheduler
   ```

2. **Instala dependencias con Composer**
   ```bash
   composer install
   ```

3. **Configura el entorno**
   ```bash
   cp .env.example .env
   ```
   Edita `.env` con tus configuraciones:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=scheduler_db
   DB_USER=your_user
   DB_PASSWORD=your_password

   APP_DEBUG=true
   APP_KEY=your_secret_key
   ```

4. **Configura la base de datos**
   - Crea la base de datos
   - Ejecuta las migraciones:
     ```bash
     php nexus migrate
     ```

5. **Inicia el servidor de desarrollo**
   ```bash
   php -S localhost:8080 -t public/
   ```

## 📁 Estructura del Proyecto

```
scheduler/
├── app/                    # Código de la aplicación
│   ├── Config/            # Configuraciones específicas de la app
│   ├── Controllers/       # Controladores MVC
│   ├── Models/           # Modelos de datos
│   ├── Views/            # Vistas y templates
│   └── Migrations/       # Migraciones de BD
├── core/                  # Núcleo del framework (Nexus namespace)
│   ├── Bootstrap/        # Inicialización y Application
│   └── Modules/          # Módulos del framework
│       ├── Auth/         # Autenticación
│       ├── Config/       # Configuración
│       ├── Database/     # Base de datos
│       ├── Http/         # HTTP, Router, Middleware
│       ├── Logging/      # Sistema de logs
│       └── Validation/   # Validación
├── public/               # Punto de entrada público
│   └── index.php        # Archivo principal
├── router/              # Definiciones de rutas
│   └── web.php         # Rutas web
├── storage/             # Archivos generados
│   ├── logs/           # Archivos de log
│   └── migrations/     # Estado de migraciones
├── tests/              # Suite de pruebas
│   └── TestSuite.php   # Ejecutor de tests
├── vendor/             # Dependencias de Composer
├── .env               # Variables de entorno
├── composer.json      # Configuración de Composer
└── README.md          # Esta documentación
```

## 🎯 Uso Básico

### Sistema de Migraciones

El framework incluye un sistema completo de migraciones para gestionar el esquema de la base de datos.

**Crear una nueva migración:**
```bash
php nexus migrate:create create_users_table
```

**Ejecutar migraciones:**
```bash
php nexus migrate
```

**Ver estado de migraciones:**
```bash
php nexus migrate:status
```

**Revertir migraciones:**
```bash
php nexus migrate:rollback
```

**Ejemplo de migración:**
```php
<?php
use Nexus\Modules\Database\Migration;

class CreateUsersTable extends Migration {
    public function up() {
        $this->createTable('users', function($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down() {
        $this->dropTable('users');
    }
}
```

### Relaciones en el ORM

```php
<?php
namespace App\Models;

use Nexus\Modules\Database\Model;

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

    // Relaciones
    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}

class Post extends Model {
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function tags() {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }
}
```

### Crear un Controlador

```php
<?php
namespace App\Controllers;

use Nexus\Modules\Http\Controller;
use App\Models\User;

class UserController extends Controller {
    public function index() {
        $users = User::all();
        $this->view('users/index', [
            'users' => $users,
            'layout' => 'layouts/app'
        ]);
    }

    public function store() {
        $data = $_POST;

        // Validación con middleware
        // (configurado en router/web.php)

        $user = User::create($data);
        $this->redirect('/users');
    }
}
```

### Definir Rutas

En `router/web.php`:

```php
<?php
// Rutas públicas
$router->add('GET', '/', 'HomeController@index', 'home', ['web']);

// Rutas protegidas
$router->add('GET', '/users', 'UserController@index', 'users.index', ['web', 'auth']);
$router->add('POST', '/users', 'UserController@store', 'users.store', [
    'web',
    'auth',
    'validate' => [
        'name' => 'required|min:2|max:255',
        'email' => 'required|email',
        'password' => 'required|min:6'
    ]
]);

// Rutas de API
$router->add('GET', '/api/users', 'ApiController@index', 'api.users', ['api', 'auth']);

// Rutas con closures
$router->add('GET', '/health', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'timestamp' => date('c'),
        'version' => '1.0.0'
    ]);
}, 'health', ['cors']);
```

### Middleware Personalizado

```php
// En public/index.php
$middleware->add('admin', function($next) {
    session_start();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo "Access denied";
        exit;
    }
    return $next();
});
```

### Validación

```php
use Nexus\Modules\Validation\Validator;

$validator = new Validator($_POST, [
    'name' => 'required|min:2|max:255',
    'email' => 'required|email',
    'password' => 'required|min:6',
    'age' => 'numeric|min:18'
]);

if (!$validator->validate()) {
    $errors = $validator->errors();
    // Manejar errores de validación
}
```

### Sistema de Logs

```php
use Nexus\Modules\Logging\Logger;

Logger::info("Usuario creado: " . $user->id);
Logger::error("Error de base de datos: " . $e->getMessage());
Logger::warning("Intento de acceso no autorizado");
```

## 🧪 Testing

Ejecuta la suite de pruebas:

```bash
php tests/TestSuite.php
```

Las pruebas incluyen validación de:
- Sistema de configuración
- Validador de datos
- Router y rutas

## 🌐 API Endpoints

### Autenticación
- `GET /login` - Formulario de login
- `POST /login` - Procesar login
- `POST /logout` - Cerrar sesión

### Usuarios
- `GET /users` - Listar usuarios (requiere auth)
- `GET /users/create` - Formulario crear usuario
- `POST /users` - Crear usuario
- `GET /users/{id}` - Ver usuario
- `GET /users/{id}/edit` - Formulario editar
- `PUT /users/{id}` - Actualizar usuario
- `DELETE /users/{id}` - Eliminar usuario

### API REST
- `GET /api/users` - Listar usuarios (JSON)
- `POST /api/users` - Crear usuario (JSON)
- `GET /api/users/{id}` - Ver usuario (JSON)
- `PUT /api/users/{id}` - Actualizar usuario (JSON)
- `DELETE /api/users/{id}` - Eliminar usuario (JSON)

### Utilidades
- `GET /health` - Health check (JSON)
- `GET /test` - Página de prueba

## 🔧 Configuración Avanzada

### Variables de Entorno

El sistema soporta variables de entorno con prefijo `APP_`:

```env
APP_DEBUG=true
APP_NAME="Mi Aplicación"
APP_URL=http://localhost:8080
```

### Base de Datos

Configuración en `app/Config/database.php`:

```php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_NAME', 'scheduler'),
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
        ],
        // ... otras conexiones
    ]
];
```

## 🚀 Despliegue

### Producción
1. Configura variables de entorno de producción
2. Desactiva `APP_DEBUG`
3. Configura permisos apropiados en `storage/`
4. Usa un servidor web (Apache/Nginx) en lugar de `php -S`

### Docker (Opcional)
```dockerfile
FROM php:8.1-apache
COPY . /var/www/html
RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /var/www/html/storage
```

## 🤝 Contribuir

Este proyecto es educativo y extensible. Áreas para contribuir:

- **CLI Commands**: Comandos artisan-like para migraciones, seeders, etc.
- **Caching System**: Redis/Memcached integration
- **Queue System**: Procesamiento asíncrono de jobs
- **API Documentation**: Swagger/OpenAPI integration
- **Internationalization**: Sistema i18n
- **Advanced Testing**: Más cobertura de tests
- **Performance**: Optimizaciones y profiling

### Guías de Contribución
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto es de código abierto bajo la licencia MIT. Siéntete libre de usarlo, modificarlo y distribuirlo.

## 🙏 Agradecimientos

- Inspirado en frameworks como Laravel y Symfony
- Construido con PHP 8+ y mejores prácticas
- Diseñado para ser educativo y profesional

---

**Versión**: 1.0.0
**PHP**: 8.0+
**Estado**: Estable y en desarrollo activo