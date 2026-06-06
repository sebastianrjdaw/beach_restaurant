# Docker

Start the application:

```bash
docker compose up --build
```

After the first build, keep the container running and edit files normally. The project directory is mounted into `/var/www/html`, so PHP, React and Tailwind changes are reflected without rebuilding the image. Vite runs in dev mode on port `5173` and uses polling inside Docker for reliable hot reload.

Use this for normal development:

```bash
docker compose up
```

Rebuild only when you change Docker-related files or need to recreate dependencies:

```bash
docker compose up --build
```

Open the app at `http://localhost:8000`. Vite is exposed at `http://localhost:5173`.

Open a shell in the PHP container:

```bash
./scripts/php-shell.sh
```

Run Artisan commands:

```bash
./scripts/artisan.sh migrate
./scripts/artisan.sh route:list
```

MySQL is exposed on the host as `127.0.0.1:3307`.

If Laravel config, routes or views look stale:

```bash
./scripts/artisan.sh optimize:clear
```
