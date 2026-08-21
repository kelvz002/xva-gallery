# XVA Gallery WordPress implementation package

## Package contents

- `wordpress-theme/` — installable Elementor-ready theme. The root `screenshot.png` uses the XVA brand treatment for the Appearance → Themes thumbnail.
- `xva-gallery-core/` — companion plugin containing post types, taxonomies, ACF loading, dynamic shortcodes and Elementor query IDs.
- `elementor-json/` — page, site-part, loop and template JSON exports.
- `acf-json/` — manual ACF JSON copies.
- `tools/generate-elementor-exports.js` — deterministic generator for the Elementor JSON exports.
- `sample-data.json` — content and image-field mapping for initial staging population.

## Required plugins

1. Elementor.
2. Elementor Pro.
3. Advanced Custom Fields Pro.
4. XVA Gallery Core.

## Installation order

1. Install WordPress on staging and preserve the existing public URL structure.
2. Install and activate `wordpress-theme/`.
3. Install and activate `xva-gallery-core/`.
4. Sync the ACF field groups.
5. Create the matching WordPress Pages and menus.
6. Import the complete Elementor Website Kit if available. For individual imports, use the order in `elementor-json/README.md`.
7. Add artists, exhibitions and press releases; connect relationship and gallery fields.
8. Assign Theme Builder conditions.
9. Set the static homepage and the correct archive/permalink settings.
10. Regenerate Elementor CSS and Data and save permalinks.

## Visual acceptance

Compare the WordPress output against the standalone mockups at 360, 390, 768, 1024, 1440 and 1600px. Check header/menu behavior, the artist image preview, exhibition filters, relationship output, forms, galleries, keyboard focus, reduced motion and the 404 route.

The mockups intentionally use a system serif/sans stack and embedded/local images. Replace the sample image set with approved production media before launch.

The Docker preview uses Elementor Pages for `/artists/`, `/exhibitions/` and `/press/` so the archive designs are visible without relying on Elementor Pro display-condition metadata. For production, those JSON files can be assigned to the corresponding Elementor archive conditions if the CPT archives are enabled.
