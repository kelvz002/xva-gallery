# XVA Gallery — IONOS deployment

This package is intended for a new IONOS WordPress installation while the current local Docker site remains available as the backup.

## Safety rule

Do not delete, overwrite, or point the production domain to the new installation until the new site has passed content, image, mobile, and link checks.

The local backup created for this deployment is in:

`backups/local-wordpress-20260821/`

It contains the current database export, uploaded media, and source package.

## IONOS setup

Use a separate temporary domain or staging URL for the new site.

For Managed WordPress, create a new project from IONOS **Web Projects**. For standard web hosting, create a new MariaDB/MySQL database from **Hosting → Databases**, record its host, database name, username, and password, then create an SFTP account for the new webspace.

Do not put IONOS passwords in this repository or in a chat message.

## Content migration

1. Install the latest WordPress in the new IONOS project.
2. Upload and activate `wordpress-theme/` as `xva-gallery`.
3. Upload and activate `xva-gallery-core/` as `xva-gallery-core`.
4. Upload the `uploads/` directory into `wp-content/uploads/`.
5. Import `database/wordpress.sql` into the new IONOS database.
6. Replace `http://localhost:8090` with the final HTTPS domain in the database, excluding GUID values.
7. Confirm the WordPress `home` and `siteurl` options use the final HTTPS domain.
8. Regenerate Elementor CSS and Data, save permalinks, and test the complete site before cutover.

The database export preserves the current local content and settings. The database itself is new on IONOS; the export is only the content/settings source for that new database.

## Required plugins

- Elementor
- Elementor Pro
- Advanced Custom Fields Pro
- XVA Gallery Core

Elementor Pro and ACF Pro must be installed from the customer's licensed downloads.
