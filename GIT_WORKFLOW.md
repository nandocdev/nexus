# NexusCore - Git Workflow

## Ramas y Flujo de Trabajo

### Estructura de Ramas

```
main (producción)
├── develop (desarrollo principal)
│   ├── feature/authentication
│   ├── feature/api-endpoints
│   ├── feature/database-optimization
│   └── feature/user-management
└── hotfix/security-patch
```

### Ramas Principales

#### `main`
- **Propósito**: Rama de producción con código estable y liberable
- **Contenido**: Solo versiones estables y probadas
- **Merge desde**: `develop` (cuando se libera una nueva versión)

#### `develop`
- **Propósito**: Rama principal de desarrollo
- **Contenido**: Funcionalidades core y desarrollo en curso
- **Merge desde**: `feature/*` (cuando las funcionalidades están completas)

### Ramas de Funcionalidades

#### `feature/*`
- **Convención**: `feature/nombre-descriptivo`
- **Creación**: Siempre desde `develop`
- **Merge**: De vuelta a `develop` cuando esté funcional
- **Ejemplos**:
  - `feature/user-authentication`
  - `feature/api-v1-endpoints`
  - `feature/database-migrations`

#### `hotfix/*`
- **Propósito**: Corrección rápida de bugs en producción
- **Creación**: Desde `main`
- **Merge**: Tanto a `main` como a `develop`

## Flujo de Trabajo

### Desarrollo de una Nueva Funcionalidad

```bash
# 1. Asegurarse de estar en develop y tener los últimos cambios
git checkout develop
git pull origin develop

# 2. Crear rama de feature desde develop
git checkout -b feature/nueva-funcionalidad

# 3. Desarrollar la funcionalidad
# ... hacer commits ...

# 4. Cuando esté lista, hacer merge a develop
git checkout develop
git merge feature/nueva-funcionalidad

# 5. Eliminar la rama de feature
git branch -d feature/nueva-funcionalidad

# 6. Push de develop
git push origin develop
```

### Liberación a Producción

```bash
# 1. Asegurarse de que develop esté estable
git checkout develop
git pull origin develop

# 2. Merge a main
git checkout main
git merge develop

# 3. Crear tag de versión
git tag -a v1.0.0 -m "Release v1.0.0"

# 4. Push a main y tags
git push origin main --tags
```

## Reglas Importantes

### Commits
- Usar mensajes descriptivos en inglés
- Seguir el formato: `tipo: descripción breve`
- Tipos comunes: `feat`, `fix`, `docs`, `style`, `refactor`, `test`

### Pull Requests
- Siempre crear PR desde feature branches hacia `develop`
- Incluir descripción detallada de los cambios
- Asegurar que los tests pasen
- Code review obligatorio

### Versionado
- Seguir [Semantic Versioning](https://semver.org/)
- `MAJOR.MINOR.PATCH`
- Tags en formato: `v1.0.0`

## Comandos Útiles

```bash
# Ver todas las ramas
git branch -a

# Cambiar a rama
git checkout <branch-name>

# Crear nueva rama
git checkout -b <new-branch-name>

# Ver estado
git status

# Ver historial
git log --oneline --graph --all

# Merge con fast-forward
git merge --no-ff <branch-name>

# Eliminar rama local
git branch -d <branch-name>

# Eliminar rama remota
git push origin --delete <branch-name>
```</content>
<parameter name="filePath">/srv/http/projects/scheduler/docs/GIT_WORKFLOW.md