# Gestor de Conciertos

Aplicación web para publicar y administrar conciertos: artistas, eventos con su afiche y una página pública donde cualquiera puede buscar los próximos eventos.

Hecha con Laravel 12 y Breeze (Blade, Tailwind y Alpine) sobre PostgreSQL. Los listados usan DataTables.

## Funcionalidades

- Página de inicio pública con buscador por evento o artista y los próximos eventos.
- Registro, inicio de sesión y recuperación de contraseña por correo.
- CRUD de artistas y de eventos (con subida de afiche).
- API REST en JSON para consultar y gestionar eventos y artistas.
- Reporte de eventos y ficha de cada evento en PDF.
- Búsqueda de artistas en Spotify para llenar el formulario.
- Pronóstico del clima en el detalle de cada evento (Open-Meteo con MET Norway de respaldo, sin clave).
- Eventos favoritos: cada usuario marca eventos con el corazón y los ve en *Mis favoritos*.
- Reproductor de Spotify en el detalle del artista, en el del evento y en *Mis favoritos*.
- Listo para desplegar en Render con Docker.
- Roles:
  - **admin**: crea, edita y elimina artistas y eventos.
  - **usuario**: consulta los listados y el detalle.

## Requisitos

- PHP 8.2 o superior con las extensiones `pdo_pgsql`, `gd`, `mbstring`, `xml` y `fileinfo` (y `pdo_sqlite` para las pruebas)
- PostgreSQL 14 o superior
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

# Crea la base en PostgreSQL y configura DB_* en el .env (ver «Base de datos»)
php artisan migrate --seed

# Necesario para que se vean los afiches subidos
php artisan storage:link

npm run build
php artisan serve
```

La aplicación queda en <http://localhost:8000>.

Para desarrollar con recarga automática de estilos, usa `composer run dev` en lugar de `npm run build` y `php artisan serve`.

### Base de datos

La base del proyecto es PostgreSQL. En el `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=gestor_conciertos
DB_USERNAME=postgres
DB_PASSWORD=tu-clave
```

También se puede usar una sola variable: `DB_URL=postgresql://usuario:clave@host:5432/base`. Para probar rápido sin instalar PostgreSQL, `DB_CONNECTION=sqlite` con `touch database/database.sqlite` funciona igual. Las pruebas automáticas usan SQLite en memoria.

## Datos de prueba

`php artisan migrate --seed` crea estas cuentas, ambas con la contraseña `password`:

| Correo | Rol |
|---|---|
| admin@example.com | admin |
| test@example.com | usuario |

También crea contenido de ejemplo con `DatosDePruebaSeeder`: 5 artistas (4 con Spotify ID y reproductor), 11 eventos próximos (uno con afiche) y 4 favoritos de `test@example.com`. Las fechas se calculan desde el día en que se siembra, así que los eventos siempre salen como próximos. Dos eventos sirven para ver el clima: *Concierto Clima Demo* (dentro de 3 días, muestra el pronóstico) y *Ciudad Rara Demo* (ciudad inexistente, muestra el aviso).

Para sembrar solo el contenido de ejemplo en una base que ya existe:

```bash
php artisan db:seed --class=DatosDePruebaSeeder
```

Para borrarlo (pide confirmación; borra los artistas de ejemplo con sus eventos, afiches y favoritos, y no toca lo demás):

```bash
php artisan datos-prueba:borrar
php artisan datos-prueba:borrar --usuarios   # también borra admin@example.com y test@example.com
```

Antes de usar `--usuarios`, dale el rol de admin a tu propia cuenta:

```bash
php artisan usuario:admin correo@ejemplo.com
```

Para quitar los datos de prueba del proyecto, borra `database/seeders/DatosDePruebaSeeder.php`, la carpeta `database/seeders/datos-prueba/`, `app/Console/Commands/BorrarDatosDePrueba.php`, `tests/Feature/DatosDePruebaTest.php` y la línea `$this->call(DatosDePruebaSeeder::class);` de `DatabaseSeeder.php`.

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

### Reproductor

Los artistas con Spotify ID muestran el reproductor oficial de Spotify en su detalle, en el de sus eventos y en *Mis favoritos*, donde se puede elegir entre los artistas de los eventos guardados. No necesita credenciales. Quien no haya iniciado sesión en Spotify en ese navegador escucha avances de 30 segundos.

## Correo (recuperar contraseña)

*¿Olvidaste tu contraseña?* envía un enlace por correo. En local los correos quedan en `storage/logs/laravel.log` (`MAIL_MAILER=log`). En producción se envían con la API HTTP de [Brevo](https://www.brevo.com) (gratis hasta 300 correos al día), porque Render bloquea SMTP en el plan gratuito:

1. Crea una cuenta en Brevo y verifica el correo remitente en *Senders, Domains & Dedicated IPs → Senders*.
2. Genera una clave en *SMTP & API → API Keys*.
3. Configura `MAIL_MAILER=brevo`, `BREVO_API_KEY` y `MAIL_FROM_ADDRESS` (el remitente verificado). En Render basta con `BREVO_API_KEY` y `MAIL_FROM_ADDRESS`; el contenedor activa Brevo solo.

## Clima

El detalle de cada evento muestra el pronóstico para su ciudad a la hora en que empieza. Usa [Open-Meteo](https://open-meteo.com) y, si no responde o agotó su límite diario (pasa en servidores compartidos como Render), [MET Norway](https://api.met.no). Los dos son gratis y no necesitan clave. El pronóstico existe hasta 15 días antes del evento (unos 10 con MET Norway); para fechas más lejanas la página indica desde cuándo estará disponible. Las consultas se guardan en caché una hora, y las coordenadas de cada ciudad, 30 días.

## API

API REST en JSON bajo `/api`. La consulta es pública; los favoritos requieren el token de cualquier usuario, y crear, editar y eliminar, el de un admin.

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
| GET | `/api/favoritos` | token | Eventos favoritos del dueño del token |
| PUT | `/api/favoritos/{id}` | token | Agrega un evento a sus favoritos |
| DELETE | `/api/favoritos/{id}` | token | Quita un evento de sus favoritos |

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

## Despliegue en Render

El repositorio trae `Dockerfile` y `render.yaml` (Blueprint) para publicarlo en el plan gratuito de [Render](https://render.com): un servicio web y una base PostgreSQL.

1. En Render: **New → Blueprint** y elige este repositorio (conectado con GitHub, o pegando la URL pública del repositorio).
2. Render pide tres valores:
   - `APP_KEY`: genérala con `php artisan key:generate --show` y pega el resultado completo (empieza con `base64:`).
   - `SPOTIFY_CLIENT_ID` y `SPOTIFY_CLIENT_SECRET`: los de la app de Spotify. Si se dejan vacíos, el buscador de Spotify no aparece.
3. **Apply**. La primera construcción tarda unos minutos; luego la app queda en `https://gestor-conciertos.onrender.com` (o el nombre que asigne Render).

Cómo funciona en el plan gratuito:

- Al arrancar, el contenedor aplica las migraciones en PostgreSQL y, si la base está vacía, la llena con los [datos de prueba](#datos-de-prueba) (`docker/iniciar.sh`).
- Los registros (eventos, artistas, cuentas, favoritos) se guardan en PostgreSQL y no se pierden al reiniciar.
- Los archivos subidos (afiches nuevos y fotos de perfil) sí se pierden en cada reinicio, porque el disco del plan gratuito no es persistente; la app deja de mostrarlos en lugar de mostrar una imagen rota. El afiche de ejemplo se repone solo.
- El servidor se apaga tras 15 minutos sin visitas y la primera visita después tarda cerca de un minuto en responder.
- Render permite una sola base PostgreSQL gratuita por workspace y la borra a los 30 días.

Sin `DB_URL`, el contenedor usa una base SQLite temporal que se pierde al reiniciar.

Para probar la imagen en local:

```bash
docker build -t gestor-conciertos .
docker run --rm -p 8080:8080 -e APP_KEY="$(php artisan key:generate --show)" -e DB_URL="postgresql://usuario:clave@host:5432/base" gestor-conciertos
```

## Pruebas

```bash
php artisan test
```

Las pruebas usan una base SQLite en memoria, así que no tocan los datos de `database/database.sqlite`.

## Configuración regional

El `.env.example` trae la app en español (`APP_LOCALE=es`) y con hora de Colombia (`APP_TIMEZONE=America/Bogota`). Si ya tenías un `.env` creado antes, actualiza esas dos variables y `APP_NAME`.
