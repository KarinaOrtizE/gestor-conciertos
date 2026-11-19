# Gestor de Conciertos

Aplicación web para publicar y administrar conciertos: artistas, eventos con su afiche y una página pública donde cualquiera puede buscar los próximos eventos.

Hecha con Laravel 12 y Breeze (Blade, Tailwind y Alpine). Los listados usan DataTables.

## Funcionalidades

- Página de inicio pública con buscador por evento o artista y los próximos eventos.
- Registro e inicio de sesión.
- CRUD de artistas y de eventos (con subida de afiche).
- Roles:
  - **admin**: crea, edita y elimina artistas y eventos.
  - **usuario**: consulta los listados y el detalle.

## Requisitos

- PHP 8.2 o superior con las extensiones `pdo_sqlite`, `gd`, `mbstring`, `xml` y `fileinfo`
- Composer
- Node.js 20 o superior y npm

## Instalación

```bash
git clone https://github.com/KarinaOrtizE/gestor-conciertos.git
cd gestor-conciertos

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

# Necesario para que se vean los afiches subidos
php artisan storage:link

npm run build
php artisan serve
```

La aplicación queda en <http://localhost:8000>.

Para desarrollar con recarga automática de estilos, usa `composer run dev` en lugar de `npm run build` y `php artisan serve`.

## Usuarios de prueba

`php artisan migrate --seed` crea estas cuentas, ambas con la contraseña `password`:

| Correo | Rol |
|---|---|
| admin@example.com | admin |
| test@example.com | usuario |

Para dar el rol de admin a una cuenta que ya existe:

```bash
php artisan usuario:admin correo@ejemplo.com
```

## Pruebas

```bash
php artisan test
```

Las pruebas usan una base SQLite en memoria, así que no tocan los datos de `database/database.sqlite`.

## Configuración regional

El `.env.example` trae la app en español (`APP_LOCALE=es`) y con hora de Colombia (`APP_TIMEZONE=America/Bogota`). Si ya tenías un `.env` creado antes, actualiza esas dos variables y `APP_NAME`.
