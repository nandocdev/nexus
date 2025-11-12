# Mi Framework PHP

Una base sólida para proyectos profesionales en PHP sin frameworks externos.

## Características

- **Autoloader PSR-4**: Carga automática de clases.
- **Sistema de Configuración**: Configuraciones flexibles con variables de entorno.
- **Base de Datos**: Soporte para MySQL, PostgreSQL y SQLite con PDO.
- **Modelo ORM**: CRUD completo con fillable, hidden y validaciones.
- **Router**: Enrutamiento con parámetros y middleware.
- **Middleware**: Sistema de middleware para autenticación y más.
- **Autenticación**: Sistema de login/logout con sesiones.
- **Validación**: Validación de datos con reglas personalizables.
- **Logging**: Sistema de logs para debugging y monitoreo.
- **Migraciones**: Gestión de esquema de base de datos.
- **Seeders**: Población de datos de prueba.
- **Vistas**: Sistema de templates con layouts.
- **Contenedor DI**: Inyección de dependencias básica.

## Instalación

1. Clona el repositorio.
2. Configura tu base de datos en `app/Config/database.php` o en el archivo `.env`.
3. Ejecuta las migraciones (si implementas el comando).
4. Inicia el servidor: `php -S localhost:8000 -t public/`

## Estructura del Proyecto

```
app/
├── Config/          # Configuraciones
├── Controllers/     # Controladores
├── Core/           # Núcleo del framework
├── Models/         # Modelos
└── Views/          # Vistas
public/
└── index.php       # Punto de entrada
storage/
└── logs/           # Logs
vendor/             # Autoloader de Composer
```

## Uso Básico

### Crear un Modelo

```php
<?php
namespace Scheduler\Models;

use Scheduler\Core\Model;

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
}
```

### Crear un Controlador

```php
<?php
namespace Scheduler\Controllers;

use Scheduler\Core\Controller;

class UserController extends Controller {
    public function index() {
        $users = User::all();
        $this->view('users.index', ['users' => $users]);
    }
}
```

### Definir Rutas

En `public/index.php`:

```php
$router->add('GET', '/users', 'UserController@index', 'users.index');
```

### Validación

```php
$validator = new Validator($data, [
    'name' => 'required|min:2|max:255',
    'email' => 'required|email'
]);

if (!$validator->validate()) {
    // Manejar errores
}
```

## Contribuir

Este proyecto es una base para aprender y construir. Siéntete libre de extenderlo con más funcionalidades como:
- API REST
- Caché
- Cola de trabajos
- Tests automatizados
- Más validaciones
- Internacionalización

## Licencia

Este proyecto es de código abierto y gratuito.