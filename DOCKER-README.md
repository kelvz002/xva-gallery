# XVA Gallery WordPress Docker environment

## Start

From this directory:

```powershell
docker compose up -d db wordpress
```

Open [http://localhost:8090](http://localhost:8090).

## Development credentials

The stack uses local-only development credentials:

- Site: `http://localhost:8090`
- Admin: `xvaadmin`
- Password: `XvaGalleryDev2026!`
- Email: `xva@example.com`
- Database: `wordpress`
- Database user: `wordpress`
- Database password: `xva_wp_db_dev_password`

Change these values before using the stack outside a local machine.

## Initialize WordPress

After the containers are healthy, run:

```powershell
docker compose --profile tools run --rm wpcli wp core install `
  --url=http://localhost:8090 `
  --title="XVA Gallery Dev" `
  --admin_user=xvaadmin `
  --admin_password='XvaGalleryDev2026!' `
  --admin_email=xva@example.com `
  --skip-email
```

Then activate the local theme and core plugin:

```powershell
docker compose --profile tools run --rm wpcli wp theme activate xva-gallery
docker compose --profile tools run --rm wpcli wp plugin activate xva-gallery-core
```

Elementor and ACF free can be installed for a baseline test:

```powershell
docker compose --profile tools run --rm wpcli wp plugin install elementor --activate
docker compose --profile tools run --rm wpcli wp plugin install advanced-custom-fields --activate
```

Seed the imported pages, mockup-faithful page bodies, the full staging artist roster and exhibition archive:

```powershell
docker compose --profile tools run --rm wpcli wp eval-file /var/www/html/wp-content/uploads/xva-tools/wp-bootstrap.php
docker compose --profile tools run --rm wpcli wp elementor flush_css
```

Elementor Pro and ACF Pro must be uploaded from the customer licenses through the WordPress admin. The JSON exports are available inside the container at:

`/var/www/html/wp-content/uploads/xva-elementor-json`

The source theme and plugin are bind-mounted, so edits in this workspace are reflected in the running site after refreshing the browser.

## Stop / reset

Stop containers while preserving the database:

```powershell
docker compose down
```

To remove this project’s containers and database volumes and start over, first verify the volume names are `xva_db_data` and `xva_wordpress_data`, then run:

```powershell
docker compose down -v
```
