## Deploy to Render (via GitHub)

This repo includes a `render.yaml` blueprint and a `Dockerfile` suitable for Render.

### Steps

1. Push to GitHub.
2. In Render, click **New +** → **Blueprint** and select this repo.
3. Render will create:
   - A web service (`likha-ph`)
   - A Postgres database (`likha-ph-db`)
4. After the first deploy, set a permanent `APP_KEY` (recommended):
   - Render → service → **Environment** → add `APP_KEY`
   - Generate locally: `php artisan key:generate --show`
5. Optional: run `php artisan storage:link` locally for development only.

### Notes

- Migrations run automatically on startup (`RUN_MIGRATIONS=1`).
- Vite assets are built inside the container at build time.
- Uploaded files use the `public` disk. On free plans, storage may be ephemeral.

