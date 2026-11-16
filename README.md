# Gestor de Conciertos

Aplicación web para publicar y administrar conciertos: artistas, eventos con su afiche y una página pública donde cualquiera puede buscar los próximos eventos.

Hecha con Laravel 12 y Breeze (Blade, Tailwind y Alpine). Los listados usan DataTables.

## Funcionalidades

- Página de inicio pública con buscador por evento o artista y los próximos eventos.
- Registro e inicio de sesión.
- CRUD de artistas y de eventos (con subida de afiche).
- API REST en JSON para consultar y gestionar eventos y artistas.
- Reporte de eventos y ficha de cada evento en PDF.
- Búsqueda de artistas en Spotify para llenar el formulario.
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

## Spotify (opcional)

El formulario de artistas puede buscar en Spotify y llenar el nombre, la foto y el Spotify ID. Para activarlo:

1. Entra a <https://developer.spotify.com/dashboard> con una cuenta **Premium** (Spotify la exige al dueño de la app desde 2026; si la suscripción vence, la búsqueda deja de funcionar).
2. Crea una app, marca *Web API* y en *Redirect URI* pon `http://127.0.0.1:8000/callback` (Spotify la pide, pero esta app no la usa).
3. Copia el *Client ID* y el *Client secret* en el `.env`:

   ```
   SPOTIFY_CLIENT_ID=tu-client-id
   SPOTIFY_CLIENT_SECRET=tu-client-secret
   ```

4. Ejecuta `php artisan config:clear`.

Spotify ya no envía los géneros a las apps en modo desarrollo, así que ese campo se llena a mano. Sin credenciales, el buscador no aparece y el formulario funciona igual que antes.

## API

API REST en JSON bajo `/api`. La consulta es pública; crear, editar y eliminar requiere el token de un usuario admin.

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| POST | `/api/login` | público | Recibe `email` y `password`; devuelve un `token` |
| POST | `/api/logout` | token | Revoca el token usado |
| GET | `/api/eventos` | público | Lista paginada de eventos con su artista |
| GET | `/api/eventos/{id}` | público | Detalle de un evento |
| POST | `/api/eventos` | admin | Crea un evento (acepta `afiche` como archivo) |
| PUT/PATCH | `/api/eventos/{id}` | admin | Actualiza un evento |
| DELETE | `/api/eventos/{id}` | admin | Elimina un evento |
| GET | `/api/artistas` | público | Lista paginada de artistas con su número de eventos |
| GET | `/api/artistas/{id}` | público | Detalle de un artista con sus eventos |
| POST | `/api/artistas` | admin | Crea un artista |
| PUT/PATCH | `/api/artistas/{id}` | admin | Actualiza un artista |
| DELETE | `/api/artistas/{id}` | admin | Elimina un artista y sus eventos |

Filtros de `GET /api/eventos`: `q` (título), `genero`, `ciudad`, `artista_id`, `proximos=1` y `por_pagina` (máximo 100). `GET /api/artistas` acepta `q` y `por_pagina`.

Respuestas: `200` correcto, `201` creado, `204` eliminado, `401` sin token, `403` sin rol admin, `404` no existe, `422` datos inválidos (con el detalle en `errors`), `429` demasiados intentos de login.

```bash
# Consultar los próximos eventos
curl "http://localhost:8000/api/eventos?proximos=1"

# Obtener un token
curl -X POST http://localhost:8000/api/login \
     -H "Accept: application/json" \
     -d email=admin@example.com -d password=password

# Crear un artista con el token
curl -X POST http://localhost:8000/api/artistas \
     -H "Accept: application/json" \
     -H "Authorization: Bearer <token>" \
     -d nombre="Grupo Niche"
```

## Pruebas

```bash
php artisan test
```

Las pruebas usan una base SQLite en memoria, así que no tocan los datos de `database/database.sqlite`.

## Configuración regional

El `.env.example` trae la app en español (`APP_LOCALE=es`) y con hora de Colombia (`APP_TIMEZONE=America/Bogota`). Si ya tenías un `.env` creado antes, actualiza esas dos variables y `APP_NAME`.
