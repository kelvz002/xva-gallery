# XVA Gallery Elementor imports

These exports are generated from the approved standalone mockups in `website-redevelopment/`.

The public page exports use one Elementor Shortcode widget per page. The widget calls the
mockup renderer registered by XVA Gallery Core, so the imported Elementor page and the
staging pages stay on the same responsive composition instead of drifting into generic
Elementor defaults. The renderer reads the ACF artist and exhibition fields and outputs the
same page-level layout, image treatment, hover effects and mobile breakpoints as the HTML
mockups.

## Preferred import

Import the complete Website Kit on a staging WordPress installation. Then assign these Theme Builder conditions:

- `global-header.json` → Entire Site / Header.
- `global-footer.json` → Entire Site / Footer.
- `artist-single.json` → Single → Artist (`xva_artist`).
- `exhibition-single.json` → Single → Exhibition (`xva_exhibition`).
- `artists-archive.json` → Archive → Artist (`xva_artist`).
- `exhibitions-archive.json` → Archive → Exhibition (`xva_exhibition`).
- `404.json` → Error 404.

The remaining page templates are assigned to WordPress Pages with the matching slugs.

## Individual JSON imports

Each JSON file can be uploaded through Elementor’s Saved Templates / Template Library. After import:

1. Install and activate XVA Gallery Core.
2. Import the ACF groups from `../xva-gallery-core/acf-json/`.
3. Create the `primary` and `footer` WordPress menus.
4. Import the global header, footer and popup first.
5. Import the loop templates and assign them to Loop Grid widgets.
6. Import the archive, single and page templates.
7. Regenerate Elementor CSS and Data.

The page files map as follows:

- Public pages use `[xva_mockup_page page="..."]` (`home`, `artists`, `exhibitions`, `press`, `contact`, `art-consultancy`, `hotel-cafe`, `story-of-art`, `path-to-modernism`, and `privacy-policy`).
- `artist-single.json` and `artist-huma-shoaib.json` use `[xva_mockup_single type="artist"]`.
- `exhibition-single.json` and `exhibition-oblivion.json` use `[xva_mockup_single type="exhibition"]`.

The template files use XVA Core shortcodes for portable dynamic content. They are backed by ACF fields and remain editable inside Elementor. XVA Core also registers `XVA Gallery → XVA ACF Text` and `XVA Gallery → XVA ACF Image` Dynamic Tags for native Elementor Pro use when the live content model is connected.

## Image handling

The theme contains the local mockup image set for a visual preview. Production images should be uploaded to the WordPress Media Library and connected to the ACF image/gallery fields. Do not hotlink the live gallery site.
