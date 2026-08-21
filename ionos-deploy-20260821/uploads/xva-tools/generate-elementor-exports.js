const fs = require('fs');
const path = require('path');

const output = path.resolve(__dirname, '..', 'elementor-json');
fs.mkdirSync(output, { recursive: true });

let sequence = 0;
function id() {
  sequence += 1;
  return sequence.toString(36).padStart(7, '0').slice(-7);
}

function container(settings = {}, elements = []) {
  return {
    id: id(),
    elType: 'container',
    isInner: false,
    settings: { content_width: 'boxed', ...settings },
    elements,
  };
}

function widget(widgetType, settings = {}, cssClasses = '') {
  return {
    id: id(),
    elType: 'widget',
    widgetType,
    settings: { ...settings, ...(cssClasses ? { css_classes: cssClasses } : {}) },
    elements: [],
  };
}

function heading(title, size = 'h2', cssClasses = 'xva-serif') {
  return widget('heading', { title, header_size: size }, cssClasses);
}

function text(editor, cssClasses = '') {
  return widget('text-editor', { editor }, cssClasses);
}

function shortcode(value, cssClasses = '') {
  return widget('shortcode', { shortcode: value }, cssClasses);
}

function button(textValue, url = '#', cssClasses = 'xva-button-link') {
  return widget('button', { text: textValue, link: { url, is_external: '', nofollow: '' }, button_type: '', size: 'xs' }, cssClasses);
}

function html(value, cssClasses = '') {
  return widget('html', { html: value }, cssClasses);
}

function section(cssClasses, elements = [], settings = {}) {
  return container({ css_classes: cssClasses, ...settings }, [container({ css_classes: 'xva-shell' }, elements)]);
}

function page(title, type, content, meta = {}) {
  return {
    title,
    type,
    version: '0.4',
    page_settings: {},
    content,
    metadata: {
      xva_template_version: '1.0.0',
      xva_source_mockup: meta.mockup || '',
      xva_display_condition: meta.condition || '',
      xva_dynamic_content: meta.dynamic || 'ACF fields are rendered through XVA Core shortcodes for portable imports.',
    },
  };
}

function write(name, value) {
  fs.writeFileSync(path.join(output, name), JSON.stringify(value, null, 2) + '\n', 'utf8');
}

const arrowIcon = '<svg class="xva-icon xva-icon--arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 5l7 7-7 7"/></svg>';
const externalIcon = '<svg class="xva-icon xva-icon--external" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9"/><path d="M19 13v6a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg>';

write('global-header.json', page('XVA Global Header', 'header', [
  container({ css_classes: 'xva-site-header xva-site-header--dark' }, [
    container({ css_classes: 'xva-header-shell' }, [
      shortcode('[xva_logo class="xva-logo"]', 'xva-brand'),
      container({ css_classes: 'xva-header-meta' }, [
        text('<p class="xva-language-switch"><a href="#">AR</a> <span aria-hidden="true">/</span> <a href="#" aria-current="true">EN</a></p>'),
        widget('icon', { icon: { value: 'fas fa-shopping-bag', library: 'solid' }, link: { url: '/shop/' }, size: 'xs' }, 'xva-shop-link'),
        widget('button', { text: 'Menu', button_type: '', size: 'xs', icon_align: 'right' }, 'xva-menu-toggle'),
      ]),
    ]),
    html('<div class="xva-header-rule" aria-hidden="true"></div>'),
  ]),
], { condition: 'Entire site / Header', mockup: 'homepage.html and all page mockups' }));

write('global-footer.json', page('XVA Global Footer', 'footer', [
  container({ css_classes: 'xva-site-footer' }, [
    container({ css_classes: 'xva-shell' }, [
      container({ css_classes: 'xva-footer-top' }, [
        shortcode('[xva_logo class="xva-logo xva-logo--light"]'),
        text('<p class="xva-footer-social"><a href="https://www.instagram.com/xvagallery/">Instagram</a><a href="https://www.facebook.com/xvagallery/">Facebook</a><a href="https://twitter.com/XVAGALLERY">Twitter</a></p>'),
      ]),
      html('<div class="xva-footer-grid"><div><span class="xva-footer-label">Visit</span><p>Al Fahidi Historical Neighbourhood<br>Dubai, United Arab Emirates</p></div><div><span class="xva-footer-label">Contact</span><p><a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a><br><a href="tel:+97143533462">+971 4 353 3462</a></p></div><div><span class="xva-footer-label">Explore</span><p><a href="/artists/">Artists</a><br><a href="/exhibitions/">Exhibitions</a></p></div><div><span class="xva-footer-label">Opening hours</span><p>Monday–Sunday<br>10:00–18:00</p></div></div>'),
      html('<div class="xva-footer-bottom"><span>© XVA Gallery</span><a href="/privacy-policy/">Privacy policy</a></div>'),
    ]),
  ]),
], { condition: 'Entire site / Footer', mockup: 'homepage.html and all page mockups' }));

write('popup-menu.json', page('XVA Fullscreen Menu', 'popup', [
  container({ css_classes: 'xva-menu-drawer' }, [
    container({ css_classes: 'xva-menu-intro' }, [
      text('<span class="xva-kicker xva-kicker--light">XVA Gallery / Dubai</span><h2 class="xva-serif">Contemporary art, considered.</h2>'),
      text('<p class="xva-menu-foot"><a href="/contact/">Visit the gallery</a><a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a></p>'),
    ]),
    widget('nav-menu', { menu: 'primary', layout: 'vertical', pointer: 'none', submenu_icon: { value: 'fas fa-arrow-right', library: 'solid' } }, 'xva-menu-nav'),
  ]),
], { condition: 'Popup triggered by global menu button', mockup: 'homepage.html' }));

write('homepage.json', page('XVA Homepage', 'page', [
  container({ css_classes: 'xva-hero', min_height: { unit: 'vh', size: 94 } }, [
    shortcode('[xva_theme_image file="hero-01.webp" alt="XVA Gallery exhibition installation"]', 'xva-hero-slide is-active'),
    shortcode('[xva_theme_image file="hero-02.webp" alt="Artwork installation at XVA Gallery"]', 'xva-hero-slide'),
    shortcode('[xva_theme_image file="hero-03.webp" alt="Contemporary artwork at XVA Gallery"]', 'xva-hero-slide'),
    text('<span class="xva-kicker xva-kicker--light">Now on view / XVA Gallery</span><h1 class="xva-serif">Oblivion</h1><p>20 January – 28 February 2026</p>', 'xva-hero-caption'),
    html('<div class="xva-hero-controls"><div class="xva-carousel-dots"><button class="xva-carousel-dot is-active" type="button" aria-label="Show slide 1"></button><button class="xva-carousel-dot" type="button" aria-label="Show slide 2"></button><button class="xva-carousel-dot" type="button" aria-label="Show slide 3"></button></div><div class="xva-carousel-buttons"><button class="xva-carousel-button" type="button" aria-label="Previous slide">' + arrowIcon + '</button><button class="xva-carousel-button" type="button" aria-label="Next slide">' + arrowIcon + '</button></div></div>'),
  ]),
  section('xva-section', [
    text('<span class="xva-kicker">The programme</span><h2 class="xva-serif">Contemporary art from the Arab world and the Sub-continent.</h2><p>Established in 2003, XVA Gallery presents the work of the region’s foremost artists alongside emerging voices, from its home in Dubai’s heritage district.</p>', 'xva-section-header'),
    button('Explore exhibitions', '/exhibitions/'),
  ]),
  section('xva-section xva-section--dark', [
    text('<span class="xva-kicker xva-kicker--light">Current exhibition</span><h2 class="xva-serif">Oblivion</h2><p>A considered exhibition of contemporary practices, material memory and shifting states.</p>', 'xva-section-header'),
    shortcode('[xva_exhibition_grid limit="3" status="current"]'),
  ]),
  section('xva-section', [
    text('<span class="xva-kicker">Represented artists</span><h2 class="xva-serif">The gallery roster</h2><p>Explore the artists represented by XVA Gallery.</p>', 'xva-section-header'),
    shortcode('[xva_artist_grid limit="6"]'),
    button('View all artists', '/artists/'),
  ]),
], { mockup: 'homepage.html' }));

write('artists-archive.json', page('Artists Archive', 'archive', [
  section('xva-page-hero', [
    text('<span class="xva-kicker">Artists / XVA Gallery</span><h1 class="xva-serif">Artists</h1><p class="xva-page-hero__meta">Al Fahidi Neighbourhood / Dubai</p>', 'xva-page-hero__top'),
  ]),
  section('xva-section xva-section--tight', [
    text('<span class="xva-kicker">Represented artists</span><h2 class="xva-serif">The gallery roster</h2><p>Hover or focus a name to bring the work forward. Each artist card links to the dynamic Artist Page template.</p>', 'xva-section-header'),
    shortcode('[xva_artist_grid limit="24"]'),
  ]),
], { mockup: 'artists.html', condition: 'Archive: xva_artist' }));

write('artist-single.json', page('Artist Page Template', 'single', [
  section('xva-page-hero xva-page-hero--night', [
    text('<span class="xva-kicker xva-kicker--light">XVA Gallery / Artist</span><h1 class="xva-serif">[xva_title]</h1><p class="xva-page-hero__meta">[xva_field field="origin" fallback="Contemporary artist"]</p>', 'xva-page-hero__top'),
  ]),
  section('xva-section', [
    container({ css_classes: 'xva-bio-layout' }, [
      shortcode('[xva_image field="portrait" size="xva-portrait"]', 'xva-detail-media'),
      container({ css_classes: 'xva-prose' }, [
        shortcode('[xva_field field="intro" fallback="Add the artist introduction here."]', 'xva-detail-copy__lede'),
        shortcode('[xva_field field="biography" fallback="Add the artist biography here."]'),
        shortcode('[xva_field field="artist_statement" fallback="Add the artist statement here."]'),
        button('Back to artists', '/artists/'),
      ]),
    ]),
  ]),
  section('xva-section xva-section--dark', [
    text('<span class="xva-kicker xva-kicker--light">Selected work</span><h2 class="xva-serif">Artwork gallery</h2>', 'xva-section-header'),
    shortcode('[xva_gallery field="artwork_gallery"]'),
  ]),
], { mockup: 'artist-page-template.html and artist-huma-shoaib.html', condition: 'Single: xva_artist', dynamic: 'Artist title, portrait, intro, biography, statement, gallery and relationships are supplied by ACF.' }));

write('artist-huma-shoaib.json', page('Huma Shoaib Artist Page', 'page', [
  section('xva-page-hero xva-page-hero--night', [
    text('<span class="xva-kicker xva-kicker--light">XVA Gallery / Artist</span><h1 class="xva-serif">Huma Shoaib</h1><p class="xva-page-hero__meta">Lahore / Pakistan</p>', 'xva-page-hero__top'),
  ]),
  section('xva-section', [
    container({ css_classes: 'xva-bio-layout' }, [
      shortcode('[xva_theme_image file="artist-huma.jpg" alt="Huma Shoaib"]', 'xva-detail-media'),
      container({ css_classes: 'xva-prose' }, [
        text('<p class="xva-detail-copy__lede">Huma Shoaib’s practice considers the intimate relationship between memory, image and place.</p>'),
        text('<p>Use this page-level export as the visual reference for the live ACF-powered Artist Page template. Replace this content with the approved biography and statement during migration.</p><p>Artwork images, selected exhibitions and press links are connected to the artist record through ACF relationships and galleries.</p>'),
        button('Back to artists', '/artists/'),
      ]),
    ]),
  ]),
  section('xva-section xva-section--dark', [
    text('<span class="xva-kicker xva-kicker--light">Selected work</span><h2 class="xva-serif">Material and memory.</h2>', 'xva-section-header'),
    shortcode('[xva_theme_image file="huma-02.jpg" alt="Artwork by Huma Shoaib"]', 'xva-card__media'),
  ]),
], { mockup: 'artist-huma-shoaib.html', condition: 'Page: /artists/huma-shoaib/' }));

write('exhibitions-archive.json', page('Exhibitions Archive', 'archive', [
  section('xva-page-hero', [
    text('<span class="xva-kicker">Programme / XVA Gallery</span><h1 class="xva-serif">Exhibitions</h1><p class="xva-page-hero__meta">Current, upcoming and archive</p>', 'xva-page-hero__top'),
  ]),
  section('xva-section xva-section--tight', [
    text('<span class="xva-kicker">Programme archive</span><h2 class="xva-serif">Exhibitions at XVA</h2><p>Filter the gallery programme by current, upcoming or archived exhibitions.</p>', 'xva-section-header'),
    html('<div class="xva-filter-tabs"><button class="xva-filter-tab is-active" type="button">Current</button><button class="xva-filter-tab" type="button">Upcoming</button><button class="xva-filter-tab" type="button">Archive</button></div>'),
    shortcode('[xva_exhibition_grid limit="24"]'),
  ]),
], { mockup: 'exhibitions.html', condition: 'Archive: xva_exhibition' }));

write('exhibition-single.json', page('Exhibition Page Template', 'single', [
  section('xva-page-hero', [
    text('<span class="xva-kicker">XVA Gallery / Exhibition</span><h1 class="xva-serif">[xva_title]</h1><p class="xva-page-hero__meta">[xva_field field="start_date"] – [xva_field field="end_date"]</p>', 'xva-page-hero__top'),
  ]),
  section('xva-section', [
    container({ css_classes: 'xva-detail-layout' }, [
      shortcode('[xva_image field="hero_image" size="xva-card"]', 'xva-detail-media'),
      container({ css_classes: 'xva-detail-copy' }, [
        shortcode('[xva_field field="subtitle" fallback="Exhibition subtitle"]', 'xva-kicker'),
        shortcode('[xva_field field="intro" fallback="Add the exhibition introduction here."]', 'xva-detail-copy__lede'),
        shortcode('[xva_field field="curatorial_text" fallback="Add the curatorial text here."]', 'xva-detail-copy__body'),
        button('View press releases', '/press/'),
        button('Plan a visit', '/contact/'),
      ]),
    ]),
  ]),
  section('xva-section xva-section--dark', [
    text('<span class="xva-kicker xva-kicker--light">Exhibition context</span><h2 class="xva-serif">Material, gesture, conversation.</h2><p>Use this section for selected works, installation views and connected artists.</p>', 'xva-section-header'),
    shortcode('[xva_gallery field="artwork_gallery"]'),
    shortcode('[xva_related_artists]'),
  ]),
], { mockup: 'exhibition-page-template.html and exhibition-oblivion.html', condition: 'Single: xva_exhibition', dynamic: 'Exhibition title, dates, hero image, subtitle, intro, curatorial text, gallery and relationships are supplied by ACF.' }));

write('exhibition-oblivion.json', page('Oblivion Exhibition Page', 'page', [
  section('xva-page-hero', [
    text('<span class="xva-kicker">XVA Gallery / Exhibition</span><h1 class="xva-serif">Oblivion</h1><p class="xva-page-hero__meta">20 January – 28 February 2026</p>', 'xva-page-hero__top'),
  ]),
  section('xva-section', [
    container({ css_classes: 'xva-detail-layout' }, [
      shortcode('[xva_theme_image file="oblivion.webp" alt="Oblivion exhibition installation"]', 'xva-detail-media'),
      container({ css_classes: 'xva-detail-copy' }, [
        text('<span class="xva-kicker">Current exhibition</span><p class="xva-detail-copy__lede">Oblivion brings together practices that move between material trace, memory and the unstable image.</p><div class="xva-detail-copy__body"><p>Use this concrete page export as the migration reference for the live Exhibition Page template.</p><p>The production record will pull the title, dates, poster, artists, curatorial text, installation views and press PDF from ACF.</p></div>'),
        button('View press releases', '/press/'),
        button('Plan a visit', '/contact/'),
      ]),
    ]),
  ]),
  section('xva-section xva-section--dark', [
    text('<span class="xva-kicker xva-kicker--light">Exhibition context</span><h2 class="xva-serif">Material, gesture, conversation.</h2><p>Connect this dark editorial module to the exhibition’s curatorial text and artwork gallery.</p>', 'xva-section-header'),
  ]),
], { mockup: 'exhibition-oblivion.html', condition: 'Page: /exhibitions/oblivion/' }));

function editorialPage(file, title, eyebrow, lead, body, options = {}) {
  const sections = [
    section('xva-page-hero' + (options.night ? ' xva-page-hero--night' : ''), [
      text('<span class="xva-kicker' + (options.night ? ' xva-kicker--light' : '') + '">' + eyebrow + '</span><h1 class="xva-serif">' + title + '</h1><p class="xva-page-hero__meta">XVA Gallery / Dubai</p>', 'xva-page-hero__top'),
    ]),
    section('xva-section', [
      text('<span class="xva-kicker">' + eyebrow + '</span><h2 class="xva-serif">' + lead + '</h2>', 'xva-section-header'),
      text('<div class="xva-prose"><p>' + body + '</p></div>'),
    ]),
  ];
  if (options.dark) {
    sections.push(section('xva-section xva-section--dark', [text('<span class="xva-kicker xva-kicker--light">XVA Gallery</span><h2 class="xva-serif">' + (options.darkTitle || 'A considered programme.') + '</h2><p>' + (options.darkBody || 'Connect this module to the relevant ACF content, related exhibitions or an Elementor Pro form.') + '</p>', 'xva-section-header'), button(options.button || 'Contact the gallery', '/contact/') ]));
  }
  write(file, page(title, 'page', sections, { mockup: options.mockup || file.replace('.json', '.html') }));
}

editorialPage('art-consultancy.json', 'Art Consultancy', 'Services / XVA Gallery', 'Art advisory for considered spaces.', 'XVA Gallery works with collectors, hospitality groups and private clients to place contemporary art with context, care and a clear point of view.', { dark: true, darkTitle: 'A collection with context.', mockup: 'art-consultancy.html' });
editorialPage('press.json', 'Press', 'Press / XVA Gallery', 'News, releases and conversations.', 'Use the Press Release post type to manage announcements, interviews, downloadable PDFs and exhibition-related coverage from one archive.', { dark: false, mockup: 'press.html' });
editorialPage('contact.json', 'Contact', 'Visit / XVA Gallery', 'Come to Al Fahidi.', 'XVA Gallery is located in Dubai’s historic Al Fahidi neighbourhood. Use the Elementor Pro Form widget for enquiries, visits and artwork requests.', { dark: true, darkTitle: 'Plan your visit.', mockup: 'contact.html' });
editorialPage('hotel-cafe.json', 'Hotel & Café', 'Stay / XVA Gallery', 'A slower rhythm in the historic district.', 'The XVA Hotel and Café extend the gallery experience through rooms, food and conversation in the heart of Al Fahidi.', { dark: true, darkTitle: 'Stay a little longer.', mockup: 'hotel-cafe.html' });
editorialPage('privacy-policy.json', 'Privacy Policy', 'Legal / XVA Gallery', 'Your information, handled with care.', 'Replace this text with the approved legal policy and connect it to the forms, analytics and mailing tools configured for the production website.', { night: true, mockup: 'privacy-policy.html' });

write('404.json', page('XVA 404', 'error-404', [
  section('xva-page-hero xva-page-hero--night', [
    text('<span class="xva-kicker xva-kicker--light">XVA Gallery</span><h1 class="xva-serif">Page not found.</h1><p class="xva-page-hero__meta">The page may have moved. Return to the gallery programme.</p>', 'xva-page-hero__top'),
    button('Return home', '/'),
  ]),
], { mockup: '404.html', condition: '404 error template' }));

write('loop-artist-card.json', page('Artist Loop Card', 'loop-item', [
  container({ css_classes: 'xva-card xva-artist-card' }, [
    widget('theme-post-featured-image', { image_size: 'xva-portrait', image_link_to: 'post' }, 'xva-card__media'),
    container({ css_classes: 'xva-artist-card__meta' }, [widget('theme-post-title', { title_tag: 'h3', link: { url: '' } }, 'xva-artist-card__name'), text('<span class="xva-artist-card__index">01</span>')]),
  ]),
], { mockup: 'artists.html', condition: 'Used by Artist Loop Grid' }));

write('loop-exhibition-card.json', page('Exhibition Loop Card', 'loop-item', [
  container({ css_classes: 'xva-card xva-exhibition-card' }, [
    widget('theme-post-featured-image', { image_size: 'xva-card', image_link_to: 'post' }, 'xva-card__media'),
    text('<span class="xva-meta">[xva_field field="start_date"]</span><h3>[xva_title]</h3><p>[xva_field field="intro"]</p><span class="xva-card-link">View exhibition ' + externalIcon + '</span>'),
  ]),
], { mockup: 'exhibitions.html', condition: 'Used by Exhibition Loop Grid' }));

write('loop-press-card.json', page('Press Loop Card', 'loop-item', [
  container({ css_classes: 'xva-accordion-item' }, [
    widget('accordion', { tabs: [{ tab_title: '[xva_title]', tab_content: '[xva_field field="summary"]' }] }, 'xva-press-loop-accordion'),
  ]),
], { mockup: 'press.html', condition: 'Used by Press Loop Grid' }));

const manifest = {
  generated_by: 'tools/generate-elementor-exports.js',
  generated_at: new Date().toISOString(),
  widgets: ['container', 'heading', 'text-editor', 'shortcode', 'button', 'html', 'image', 'gallery', 'nav-menu', 'accordion', 'theme-post-title', 'theme-post-featured-image'],
  dynamic_source: 'XVA Core shortcodes backed by ACF fields; replace shortcode placeholders with native Elementor Dynamic Tags after import where desired.',
  pages: [
    'homepage.json', 'artists-archive.json', 'artist-single.json', 'artist-huma-shoaib.json', 'exhibitions-archive.json', 'exhibition-single.json', 'exhibition-oblivion.json',
    'art-consultancy.json', 'press.json', 'contact.json', 'hotel-cafe.json', 'privacy-policy.json', '404.json'
  ],
  site_parts: ['global-header.json', 'global-footer.json', 'popup-menu.json'],
  loops: ['loop-artist-card.json', 'loop-exhibition-card.json', 'loop-press-card.json'],
};
write('manifest.json', manifest);
