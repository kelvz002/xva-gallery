# XVA Gallery backup

This repository is the GitHub backup for the local XVA Gallery WordPress build.

## Canonical full backup

The complete deployable snapshot is in [`ionos-deploy-20260821/`](ionos-deploy-20260821/):

- `database/wordpress-20260821-current.sql` — fresh export of the local Docker WordPress database.
- `uploads/` — WordPress media, artwork images, exhibition images, artist images, PDFs, and the IONOS repair aliases.
- `vendor-plugins/` — Elementor, Elementor Pro, and Advanced Custom Fields plugin files.
- `wordpress-core-7.1/` — WordPress core files from the running local container. Runtime `wp-config.php` files are intentionally excluded.
- `wordpress-theme/` and `xva-gallery-core/` — the current XVA theme and custom plugin.
- `elementor-json/`, `acf-json/`, and `tools/` — imported templates, field definitions, and migration tools.

The top-level `wordpress-theme/` and `xva-gallery-core/` directories are the editable source copies and are mirrored into the deploy snapshot.

## Restore locally

1. Install Docker Desktop.
2. Run `docker compose up -d` from this repository.
3. Restore `ionos-deploy-20260821/database/wordpress-20260821-current.sql` into the MySQL container.
4. Copy the snapshot's `uploads/` into `wp-content/uploads/`, the vendor plugins into `wp-content/plugins/`, and the XVA theme/plugin into their matching locations.
5. Update the local site URL with WP-CLI or the WordPress database if the restore URL differs.

The database dump contains the site's WordPress content and should be kept in a private repository. No live domain or production credentials are included in the core snapshot.

## Intentionally omitted duplicates

The repository ignores older/generated duplicate folders (`backups/`, `media-sync-20260821/`, build caches) and large ZIP distributions. The unpacked canonical snapshot is committed so the backup remains usable without a 1.7 GB archive that GitHub cannot store as a normal file.
