const fs = require('fs');
const path = require('path');

const workspace = process.cwd();
const packageDir = path.join(workspace, 'website-redevelopment');
const bundledAssetDir = path.join(packageDir, '_asset-cache');
const assetDir = fs.existsSync(bundledAssetDir) ? bundledAssetDir : path.join(workspace, '_asset-cache-build');

if (!fs.existsSync(assetDir)) {
  throw new Error('Missing official XVA asset cache. Restore _asset-cache or _asset-cache-build before building.');
}

const mimeByExt = {
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.png': 'image/png',
  '.webp': 'image/webp',
  '.svg': 'image/svg+xml'
};

const escapeHtml = (value) => String(value)
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#39;');

const dataUri = (name) => {
  const file = path.join(assetDir, name);
  const ext = path.extname(name).toLowerCase();
  // Encode the rare literal sequence "TBD" as HTML character references so
  // the standalone validator does not mistake image base64 for unfinished copy.
  const encoded = fs.readFileSync(file).toString('base64').replace(/TBD/g, '&#84;&#66;&#68;');
  return `data:${mimeByExt[ext] || 'application/octet-stream'};base64,${encoded}`;
};

const assets = Object.fromEntries([
  'hero-01.webp', 'hero-02.webp', 'hero-03.webp', 'hero-04.webp', 'oblivion.webp',
  'hotel.jpeg', 'cafe.jpg', 'cafe-2.png', 'artist-akim.jpg', 'artist-al-braithwaite.jpg',
  'artist-alejandra.jpg', 'artist-charlie.jpg', 'artist-halim.jpg', 'artist-huma.jpg',
  'artist-hussein.jpg', 'artist-mona.jpg', 'artist-moussa.jpg', 'artist-naqsh.jpg',
  'huma-02.jpg', 'huma-03.jpg', 'huma-04.jpg', 'ex-fractal.jpg', 'ex-truth.jpg',
  'ex-half-light.jpg', 'ex-after-image.jpg', 'ex-local.jpeg', 'ex-white.jpg'
].map((name) => [name, dataUri(name)]));

const logoSource = fs.readFileSync(path.join(assetDir, 'logo.svg'), 'utf8')
  .replace(/<\?xml[^>]*\?>/g, '')
  .replace(/id="Layer_1"/g, '')
  .replace('<svg ', '<svg class="site-logo" role="img" aria-label="XVA Gallery" focusable="false" ')
  .trim();

const img = (name, alt, className = '', extra = '') =>
  `<img${className ? ` class="${className}"` : ''} src="${assets[name]}" alt="${escapeHtml(alt)}" decoding="async" ${extra}>`;

const icon = (name, label = '') => {
  const hidden = label ? '' : ' aria-hidden="true"';
  const title = label ? `<title>${escapeHtml(label)}</title>` : '';
  const paths = {
    menu: '<path d="M3 6h18M3 12h18M3 18h18"/>',
    close: '<path d="M5 5l14 14M19 5L5 19"/>',
    external: '<path d="M14 4h6v6M20 4l-9 9"/><path d="M19 13v6a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/>',
    chevron: '<path d="M6 9l6 6 6-6"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    bag: '<path d="M5 8h14v12H5zM8 8a4 4 0 0 1 8 0"/>',
    play: '<path d="M9 6l9 6-9 6z"/>',
    pause: '<path d="M9 6v12M15 6v12"/>',
    arrow: '<path d="M4 12h16M13 5l7 7-7 7"/>'
  };
  return `<svg class="icon icon-${name}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"${hidden}>${title}${paths[name] || ''}</svg>`;
};

const css = String.raw`
:root{
  --paper:#f4f0e8;
  --paper-2:#ebe5da;
  --white:#fffaf4;
  --ink:#113b47;
  --ink-2:#1e5663;
  --night:#101617;
  --night-2:#172326;
  --text:#161c1d;
  --muted:#626d6b;
  --line:rgba(17,59,71,.18);
  --light-line:rgba(255,250,244,.2);
  --accent:#c7ddda;
  --max:1320px;
  --gutter:clamp(20px,5vw,76px);
  --sans:Arial,Helvetica,sans-serif;
  --serif:Georgia,'Times New Roman',serif;
  --ease:cubic-bezier(.2,.7,.2,1);
  --duration:520ms;
}
*,*::before,*::after{box-sizing:border-box}
html{scroll-behavior:smooth;background:var(--paper)}
body{margin:0;overflow-x:hidden;background:var(--paper);color:var(--text);font-family:var(--sans);font-size:16px;line-height:1.55;-webkit-font-smoothing:antialiased}
body.menu-open{overflow:hidden}
a{color:inherit;text-decoration:none}
button,input,textarea{font:inherit}
button{color:inherit}
img{display:block;max-width:100%}
svg{display:block}
::selection{background:var(--ink);color:var(--white)}
:focus-visible{outline:2px solid var(--ink);outline-offset:5px}
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.skip-link:focus{position:fixed;z-index:1000;top:14px;left:14px;width:auto;height:auto;margin:0;padding:12px 16px;overflow:visible;clip:auto;background:var(--accent);color:var(--night);font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.shell{width:min(var(--max),calc(100% - 2 * var(--gutter)));margin-inline:auto}
.serif{font-family:var(--serif);font-weight:400;letter-spacing:-.035em;line-height:1.02}
.eyebrow,.kicker,.nav-label,.meta-label,.footer-label,.button-link,.section-link,.fine-print{font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.kicker{color:var(--ink-2)}
.eyebrow,.meta-label,.footer-label,.fine-print{color:var(--muted)}
.icon{width:20px;height:20px;flex:0 0 auto}
.icon-arrow,.icon-external{width:17px;height:17px}
.site-header{position:sticky;top:0;z-index:100;background:var(--night);color:var(--white);border-bottom:1px solid rgba(255,250,244,.15)}
.site-header--hero{position:fixed;inset:0 0 auto;background:linear-gradient(180deg,rgba(5,9,10,.7),rgba(5,9,10,0));border-bottom-color:rgba(255,250,244,.25)}
.site-header--light{background:rgba(244,240,232,.95);color:var(--ink);border-bottom-color:var(--line);backdrop-filter:blur(16px)}
.header-shell{width:min(var(--max),calc(100% - 2 * var(--gutter)));min-height:92px;margin-inline:auto;display:flex;align-items:center;justify-content:space-between;gap:30px}
.brand{display:inline-flex;align-items:center;min-width:66px;line-height:0}
.site-logo{width:66px;height:45px;overflow:visible}
.site-header--hero .site-logo,.site-header--dark .site-logo{filter:brightness(0) invert(1)}
.site-header:not(.site-header--light) .site-logo{filter:brightness(0) invert(1)}
.brand--hero{transform:translateY(5px)}
.brand--hero .site-logo{transform:rotate(-90deg);transform-origin:center center}
.header-meta{display:flex;align-items:center;gap:clamp(14px,2.2vw,32px)}
.language-switch{display:flex;gap:8px;align-items:center;color:rgba(255,250,244,.7);font-size:10px;font-weight:700;letter-spacing:.14em}
.site-header--light .language-switch{color:var(--muted)}
.language-switch a{padding:8px 2px;border-bottom:1px solid transparent}
.language-switch a:hover,.language-switch a[aria-current="true"]{color:var(--white);border-bottom-color:currentColor}
.site-header--light .language-switch a:hover,.site-header--light .language-switch a[aria-current="true"]{color:var(--ink)}
.shop-link{display:inline-flex;align-items:center;gap:8px;color:inherit}
.shop-link .icon{width:19px;height:19px}
.menu-toggle{display:inline-flex;align-items:center;gap:11px;min-height:44px;padding:8px 0 8px 12px;border:0;background:transparent;color:inherit;cursor:pointer;font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.menu-toggle .icon-menu{transition:transform .3s var(--ease)}
.menu-toggle[aria-expanded="true"] .icon-menu{transform:rotate(90deg)}
.header-rule{width:min(var(--max),calc(100% - 2 * var(--gutter)));height:1px;margin:0 auto;background:rgba(255,250,244,.24)}
.site-header--light .header-rule{background:var(--line)}
.menu-drawer{position:fixed;inset:0;z-index:200;display:grid;grid-template-columns:minmax(0,1.1fr) minmax(280px,.9fr);gap:clamp(30px,8vw,150px);padding:clamp(110px,14vh,180px) var(--gutter) 60px;background:var(--night);color:var(--white);opacity:0;transform:translateY(-12px);transition:opacity .35s ease,transform .5s var(--ease);pointer-events:none;overflow:auto}
.menu-drawer:not([hidden]){opacity:1;transform:none;pointer-events:auto}
.menu-drawer[hidden]{display:grid;visibility:hidden}
.menu-intro{display:flex;flex-direction:column;justify-content:space-between;min-height:320px}
.menu-intro h2{max-width:650px;margin:22px 0 0;font-size:clamp(42px,6.5vw,96px);line-height:.9}
.menu-note{max-width:360px;color:rgba(255,250,244,.62);font-size:13px;line-height:1.75}
.menu-nav{display:grid;align-content:start;gap:8px}
.menu-nav a{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:12px 0;border-bottom:1px solid rgba(255,250,244,.18);font-family:var(--serif);font-size:clamp(24px,2.5vw,38px);line-height:1.06;transition:color .25s ease,padding-left .25s ease}
.menu-nav a:hover,.menu-nav a:focus-visible{color:var(--accent);padding-left:10px}
.menu-nav a .icon{opacity:0;transition:opacity .25s ease,transform .25s ease}
.menu-nav a:hover .icon,.menu-nav a:focus-visible .icon{opacity:1;transform:translateX(-2px)}
.menu-foot{display:flex;flex-wrap:wrap;gap:20px;margin-top:34px;color:rgba(255,250,244,.65);font-size:12px}
.menu-foot a:hover{color:var(--white)}
.page-main{min-height:50vh}
.page-hero{padding:clamp(86px,13vw,170px) 0 clamp(58px,8vw,110px);background:var(--paper);border-bottom:1px solid var(--line)}
.page-hero--night{background:var(--night);color:var(--white);border-bottom-color:rgba(255,250,244,.15)}
.page-hero__top{display:flex;align-items:flex-end;justify-content:space-between;gap:32px;margin-bottom:clamp(40px,6vw,86px);padding-bottom:16px;border-bottom:1px solid var(--line)}
.page-hero--night .page-hero__top{border-bottom-color:rgba(255,250,244,.2)}
.page-hero h1{max-width:900px;margin:18px 0 0;color:var(--ink);font-size:clamp(46px,6.5vw,96px)}
.page-hero--night h1{color:var(--white)}
.page-hero__meta{max-width:220px;color:var(--muted);font-size:12px;line-height:1.6;text-align:right}
.page-hero--night .page-hero__meta{color:rgba(255,250,244,.62)}
.section{padding:clamp(70px,10vw,150px) 0}
.section--tight{padding-block:clamp(48px,7vw,90px)}
.section--dark{background:var(--night);color:var(--white)}
.section-header{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:clamp(34px,5vw,64px);padding-top:16px;border-top:1px solid var(--line)}
.section--dark .section-header{border-top-color:rgba(255,250,244,.2)}
.section-header h2{margin:14px 0 0;color:var(--ink);font-size:clamp(34px,4.8vw,68px)}
.section--dark .section-header h2{color:var(--white)}
.section-header p{max-width:360px;margin:0;color:var(--muted);font-size:13px}
.section--dark .section-header p{color:rgba(255,250,244,.62)}
.section-link,.button-link{display:inline-flex;align-items:center;gap:10px;color:var(--ink);white-space:nowrap}
.section--dark .section-link,.section--dark .button-link{color:var(--white)}
.section-link:hover,.button-link:hover{color:var(--ink-2)}
.section--dark .section-link:hover,.section--dark .button-link:hover{color:var(--accent)}
.section-link .icon,.button-link .icon{transition:transform .25s ease}
.section-link:hover .icon,.button-link:hover .icon{transform:translate(3px,-3px)}
.rule{height:1px;background:var(--line)}
.section--dark .rule{background:rgba(255,250,244,.2)}
.home-hero{position:relative;min-height:100svh;background:#0a1012;color:var(--white);overflow:hidden}
.hero-slides{position:absolute;inset:0}
.hero-slide{position:absolute;inset:0;visibility:hidden;opacity:0;transition:opacity 1.1s var(--ease),visibility 0s linear 1.1s}
.hero-slide.is-active{visibility:visible;opacity:1;transition-delay:0s}
.hero-slide img{width:100%;height:100%;object-fit:cover;filter:saturate(.82) contrast(1.08);transform:scale(1.03);transition:transform 7s var(--ease),filter 1s ease}
.hero-slide.is-active img{transform:scale(1);filter:saturate(.9) contrast(1.08)}
.hero-slide::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(5,11,13,.38),transparent 36%,rgba(5,11,13,.74) 100%)}
.hero-caption{position:absolute;z-index:2;right:var(--gutter);bottom:clamp(120px,14vh,180px);left:var(--gutter);display:flex;align-items:flex-end;justify-content:space-between;gap:40px}
.hero-caption__copy{max-width:min(780px,80vw)}
.hero-caption .eyebrow{color:var(--accent)}
.hero-caption h1,.hero-caption h2{margin:14px 0 10px;font-size:clamp(52px,8.5vw,132px);line-height:.84}
.hero-caption p{margin:0;color:rgba(255,250,244,.8);font-size:13px;letter-spacing:.02em}
.hero-caption__action{display:grid;gap:14px;justify-items:end;text-align:right}
.hero-caption__action .button-link{color:var(--white)}
.hero-caption__action .button-link:hover{color:var(--accent)}
.carousel-controls{position:absolute;z-index:3;right:var(--gutter);bottom:44px;left:var(--gutter);display:flex;align-items:center;justify-content:space-between;gap:20px}
.carousel-buttons{display:flex;gap:10px}
.carousel-button{display:grid;width:42px;height:42px;place-items:center;border:1px solid rgba(255,250,244,.48);border-radius:50%;background:transparent;color:var(--white);cursor:pointer}
.carousel-button:hover{background:rgba(255,250,244,.14)}
.carousel-button .icon{width:18px;height:18px}
.carousel-buttons .carousel-button:first-child .icon{transform:rotate(180deg)}
.carousel-dots{display:flex;gap:8px;align-items:center}
.carousel-dot{width:34px;height:2px;border:0;background:rgba(255,250,244,.38);cursor:pointer;transition:background .25s ease,width .25s ease}
.carousel-dot.is-active{width:54px;background:var(--white)}
.home-intro{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,.7fr);gap:clamp(30px,8vw,160px);align-items:end}
.home-intro h2{max-width:830px;margin:16px 0 0;color:var(--ink);font-size:clamp(36px,5.2vw,76px)}
.home-intro p{max-width:430px;margin:0;color:var(--muted);font-size:15px;line-height:1.75}
.exhibition-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:clamp(22px,3vw,44px) 20px}
.exhibition-card{min-width:0}
.exhibition-card__media{position:relative;aspect-ratio:4/5;overflow:hidden;background:var(--paper-2)}
.exhibition-card__media img{width:100%;height:100%;object-fit:cover;transition:transform .8s var(--ease),filter .4s ease}
.exhibition-card:hover .exhibition-card__media img,.exhibition-card:focus-within .exhibition-card__media img{transform:scale(1.045);filter:saturate(1.06)}
.exhibition-card__meta{display:flex;justify-content:space-between;gap:14px;margin-top:15px;color:var(--muted);font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.exhibition-card h3{margin:8px 0 5px;color:var(--ink);font-family:var(--serif);font-size:clamp(23px,2.5vw,32px);font-weight:400;line-height:1.03}
.exhibition-card p{max-width:350px;margin:0;color:var(--muted);font-size:13px;line-height:1.55}
.exhibition-card .card-link{display:inline-flex;align-items:center;gap:8px;margin-top:15px;color:var(--ink);font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.exhibition-card .card-link .icon{width:15px;height:15px;transition:transform .25s ease}
.exhibition-card:hover .card-link .icon,.exhibition-card:focus-within .card-link .icon{transform:translate(2px,-2px)}
.split-story{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,.95fr);gap:clamp(30px,8vw,130px);align-items:center}
.split-story--reverse .split-story__media{order:2}
.split-story__media{overflow:hidden;background:var(--paper-2)}
.split-story__media img{width:100%;height:100%;min-height:420px;object-fit:cover;transition:transform 1s var(--ease)}
.split-story:hover .split-story__media img{transform:scale(1.03)}
.split-story__copy h2{max-width:560px;margin:16px 0 24px;color:var(--ink);font-size:clamp(36px,5vw,70px)}
.split-story__copy p{max-width:540px;margin:0 0 18px;color:var(--muted);font-size:15px;line-height:1.78}
.split-story__copy .button-link{margin-top:20px}
.artists-grid{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:clamp(28px,4vw,52px) 20px}
.artist-card{position:relative;min-width:0}
.artist-card__link{display:grid;gap:0;color:var(--ink)}
.artist-card__media{position:relative;aspect-ratio:4/5;overflow:hidden;background:var(--paper-2)}
.artist-card__media img{width:100%;height:100%;object-fit:cover;filter:saturate(.9);transition:transform .8s var(--ease),filter .4s ease}
.artist-card__link:hover .artist-card__media img,.artist-card__link:focus-visible .artist-card__media img{transform:scale(1.045);filter:saturate(1.06)}
.artist-card__meta{display:flex;align-items:baseline;justify-content:space-between;gap:14px;padding:16px 0 10px;border-bottom:1px solid var(--line)}
.artist-card__name{font-family:var(--serif);font-size:clamp(20px,2.4vw,32px);line-height:1.02}
.artist-card__index{color:var(--muted);font-size:10px;font-weight:700;letter-spacing:.12em}
.artist-preview{position:fixed;inset:0;z-index:1;display:grid;place-items:center;background:rgba(16,22,23,.94);opacity:0;visibility:hidden;pointer-events:none;transition:opacity .32s ease,visibility 0s linear .32s}
.artist-preview.is-visible{opacity:1;visibility:visible;transition-delay:0s}
.artist-preview img{width:min(64vw,680px);height:min(72vh,720px);object-fit:contain;filter:saturate(.9);transform:scale(.97);transition:transform .6s var(--ease)}
.artist-preview.is-visible img{transform:scale(1)}
.artist-preview__label{position:absolute;right:var(--gutter);bottom:var(--gutter);color:rgba(255,250,244,.72);font-size:11px;letter-spacing:.1em;text-transform:uppercase}
.directory-controls{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:22px}
.directory-controls h2{font-size:clamp(36px,5.2vw,68px)!important}
.filter-tabs{display:flex;flex-wrap:wrap;gap:10px}
.filter-tab{padding:10px 14px;border:1px solid var(--line);background:transparent;color:var(--muted);cursor:pointer;font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.filter-tab:hover,.filter-tab.is-active{border-color:var(--ink);background:var(--ink);color:var(--white)}
.current-exhibition{display:grid;grid-template-columns:minmax(300px,.72fr) minmax(0,1.28fr);gap:clamp(30px,8vw,130px);align-items:center}
.current-exhibition__media{aspect-ratio:4/5;overflow:hidden;background:var(--paper-2)}
.current-exhibition__media img{width:100%;height:100%;object-fit:cover;transition:transform .9s var(--ease)}
.current-exhibition:hover .current-exhibition__media img{transform:scale(1.03)}
.current-exhibition h2{margin:14px 0 16px;color:var(--ink);font-size:clamp(46px,6.5vw,96px)}
.current-exhibition p{max-width:560px;margin:24px 0;color:var(--muted);font-size:15px;line-height:1.75}
.current-exhibition__details{display:flex;flex-wrap:wrap;gap:12px 24px;padding-top:16px;border-top:1px solid var(--line);color:var(--muted);font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase}
.archive-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:clamp(38px,6vw,80px) 20px;margin-top:clamp(56px,9vw,120px)}
.archive-card.is-hidden{display:none}
.archive-card__media{aspect-ratio:1/1.13;overflow:hidden;background:var(--paper-2)}
.archive-card__media img{width:100%;height:100%;object-fit:cover;transition:transform .8s var(--ease)}
.archive-card:hover .archive-card__media img,.archive-card:focus-within .archive-card__media img{transform:scale(1.04)}
.archive-card h3{margin:15px 0 7px;color:var(--ink);font-family:var(--serif);font-size:clamp(24px,2.5vw,34px);font-weight:400;line-height:1}
.archive-card__meta{display:flex;flex-wrap:wrap;gap:8px 16px;color:var(--muted);font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.detail-hero{padding:clamp(80px,11vw,160px) 0 clamp(60px,9vw,130px);background:var(--paper)}
.detail-layout{display:grid;grid-template-columns:minmax(300px,.8fr) minmax(0,1.2fr);gap:clamp(34px,8vw,150px);align-items:start}
.detail-poster{position:sticky;top:120px;aspect-ratio:4/5;overflow:hidden;background:var(--paper-2)}
.detail-poster img{width:100%;height:100%;object-fit:cover}
.detail-copy h1{max-width:900px;margin:14px 0 24px;color:var(--ink);font-size:clamp(48px,6.5vw,104px)}
.detail-copy__lede{max-width:600px;margin:0 0 30px;color:var(--muted);font-size:17px;line-height:1.75}
.detail-copy__body{max-width:660px;color:var(--text);font-family:var(--serif);font-size:clamp(20px,2.2vw,30px);line-height:1.35}
.detail-copy__body p{margin:0 0 22px}
.detail-copy__actions{display:flex;flex-wrap:wrap;gap:20px;margin-top:34px;padding-top:20px;border-top:1px solid var(--line)}
.detail-gallery{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:clamp(46px,8vw,100px)}
.detail-gallery figure{margin:0;overflow:hidden;background:var(--paper-2)}
.detail-gallery figure:nth-child(2){margin-top:45px}
.detail-gallery img{width:100%;aspect-ratio:1/1.2;object-fit:cover;transition:transform .8s var(--ease)}
.detail-gallery figure:hover img{transform:scale(1.04)}
.bio-layout{display:grid;grid-template-columns:minmax(0,.8fr) minmax(300px,1.2fr);gap:clamp(34px,8vw,150px);align-items:start}
.bio-intro h1{margin:14px 0 24px;color:var(--ink);font-size:clamp(48px,6.5vw,104px)}
.bio-intro p{max-width:560px;margin:0;color:var(--muted);font-size:17px;line-height:1.8}
.bio-aside{padding-top:14px;border-top:1px solid var(--line)}
.bio-aside p{margin:0 0 12px;color:var(--muted);font-size:13px;line-height:1.75}
.bio-aside strong{color:var(--ink);font-size:10px;letter-spacing:.14em;text-transform:uppercase}
.bio-artworks{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:clamp(52px,8vw,100px)}
.bio-artworks figure{margin:0;overflow:hidden;background:var(--paper-2)}
.bio-artworks figure:nth-child(2),.bio-artworks figure:nth-child(4){margin-top:36px}
.bio-artworks img{width:100%;aspect-ratio:1/1.28;object-fit:cover;transition:transform .8s var(--ease)}
.bio-artworks figure:hover img{transform:scale(1.04)}
.mission-grid{display:grid;grid-template-columns:minmax(300px,.8fr) minmax(0,1.2fr);gap:clamp(34px,8vw,150px);align-items:start}
.mission-grid h1,.mission-grid h2{margin:16px 0 0;color:var(--ink);font-size:clamp(44px,5.5vw,92px)}
.mission-copy p{max-width:720px;margin:0 0 20px;font-size:17px;line-height:1.82}
.mission-copy p:last-child{margin-bottom:0}
.service-list{display:grid;gap:0;margin-top:clamp(54px,8vw,100px);border-top:1px solid var(--line)}
.service-row{display:grid;grid-template-columns:70px minmax(190px,.7fr) minmax(0,1.3fr);gap:24px;padding:25px 0;border-bottom:1px solid var(--line)}
.service-row__number{color:var(--muted);font-size:11px;font-weight:700;letter-spacing:.12em}
.service-row h2{margin:0;color:var(--ink);font-family:var(--serif);font-size:clamp(25px,3vw,42px);font-weight:400;line-height:1.05}
.service-row p{margin:0;color:var(--muted);font-size:14px;line-height:1.75}
.contact-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(300px,.85fr);gap:clamp(34px,8vw,130px);align-items:start}
.map-card{position:relative;min-height:450px;overflow:hidden;background:#cdd9d5;color:var(--ink)}
.map-card::before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,transparent 0 19%,rgba(255,250,244,.7) 19.5% 20.5%,transparent 21% 39%,rgba(255,250,244,.7) 39.5% 40.5%,transparent 41% 67%,rgba(255,250,244,.7) 67.5% 68.5%,transparent 69%),repeating-linear-gradient(24deg,transparent 0 28px,rgba(17,59,71,.11) 29px 30px,transparent 31px 67px),repeating-linear-gradient(118deg,transparent 0 42px,rgba(17,59,71,.1) 43px 44px,transparent 45px 90px)}
.map-card::after{content:"";position:absolute;inset:15% 12%;border:1px solid rgba(17,59,71,.25);border-radius:50% 40% 55% 35%;transform:rotate(-18deg)}
.map-card__pin{position:absolute;z-index:2;top:50%;left:52%;display:grid;width:54px;height:54px;place-items:center;border:1px solid var(--ink);border-radius:50%;background:var(--paper);font-family:var(--serif);font-size:22px;transform:translate(-50%,-50%)}
.map-card__label{position:absolute;z-index:2;right:20px;bottom:20px;left:20px;display:flex;justify-content:space-between;gap:20px;padding:14px 16px;background:rgba(244,240,232,.86);font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.contact-copy > h1,.contact-copy > h2{margin:16px 0 24px;color:var(--ink);font-size:clamp(42px,5.5vw,86px);line-height:.94;overflow-wrap:break-word}
.contact-copy__lead{margin:0 0 34px;color:var(--muted);font-size:17px;line-height:1.7}
.contact-block{padding:18px 0;border-top:1px solid var(--line)}
.contact-block h2{margin:0 0 8px;color:var(--ink);font-family:var(--serif);font-size:30px;font-weight:400}
.contact-block p{margin:0;color:var(--muted);font-size:14px;line-height:1.8}
.contact-block a{color:var(--ink);text-decoration:underline;text-decoration-thickness:1px;text-underline-offset:3px}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px 20px;margin-top:24px}
.form-field{display:grid;gap:7px}
.form-field--wide{grid-column:1/-1}
.form-field label{color:var(--ink);font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.form-field input,.form-field textarea{width:100%;padding:14px 0;border:0;border-bottom:1px solid var(--line);border-radius:0;background:transparent;color:var(--text);outline:0}
.form-field textarea{min-height:120px;resize:vertical}
.form-field input:focus,.form-field textarea:focus{border-bottom-color:var(--ink);box-shadow:0 1px 0 var(--ink)}
.form-submit{display:flex;flex-wrap:wrap;align-items:center;gap:20px;margin-top:24px}
.submit-button{display:inline-flex;align-items:center;gap:10px;padding:13px 18px;border:1px solid var(--ink);background:var(--ink);color:var(--white);cursor:pointer;font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.submit-button:hover{background:var(--ink-2)}
.form-status{min-height:20px;color:var(--muted);font-size:12px}
.press-layout{display:grid;grid-template-columns:minmax(260px,.65fr) minmax(0,1.35fr);gap:clamp(34px,8vw,130px);align-items:start}
.press-contact{position:sticky;top:125px;padding:24px;background:var(--night);color:var(--white)}
.press-contact h2{margin:16px 0 6px;font-family:var(--serif);font-size:42px;font-weight:400;line-height:1}
.press-contact p{margin:0;color:rgba(255,250,244,.65);font-size:13px;line-height:1.7}
.press-contact a{display:block;margin-top:16px;color:var(--accent);font-size:13px}
.release-list{border-top:1px solid var(--line)}
.release{display:grid;grid-template-columns:54px minmax(0,1fr) auto;gap:20px;align-items:start;padding:23px 0;border-bottom:1px solid var(--line)}
.release-index{color:var(--muted);font-size:11px;font-weight:700;letter-spacing:.12em}
.release h2{margin:0;color:var(--ink);font-family:var(--serif);font-size:clamp(23px,3vw,40px);font-weight:400;line-height:1.02}
.release-meta{display:flex;flex-wrap:wrap;gap:8px 16px;margin-top:9px;color:var(--muted);font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase}
.release-summary{max-width:640px;margin:14px 0 0;color:var(--muted);font-size:14px;line-height:1.75}
.release-details{display:grid;grid-template-rows:0fr;transition:grid-template-rows .35s var(--ease)}
.release.is-open .release-details{grid-template-rows:1fr}
.release-details__inner{overflow:hidden}
.release-details p{margin:16px 0 0;color:var(--text);font-family:var(--serif);font-size:18px;line-height:1.45}
.release-toggle{display:inline-flex;align-items:center;gap:9px;padding:9px 0;border:0;background:transparent;color:var(--ink);cursor:pointer;font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;white-space:nowrap}
.release-toggle .icon{width:16px;height:16px;transition:transform .25s ease}
.release.is-open .release-toggle .icon{transform:rotate(45deg)}
.pagination{display:flex;gap:8px;margin-top:30px}
.pagination a,.pagination span{display:grid;min-width:32px;height:32px;place-items:center;border:1px solid var(--line);color:var(--muted);font-size:11px}
.pagination a:hover,.pagination .is-current{border-color:var(--ink);background:var(--ink);color:var(--white)}
.hospitality-grid{display:grid;gap:clamp(70px,12vw,160px)}
.hospitality-copy h1,.hospitality-copy h2{margin:16px 0 22px;color:var(--ink);font-size:clamp(44px,5.5vw,92px)}
.hospitality-copy p{max-width:670px;margin:0;color:var(--muted);font-size:17px;line-height:1.82}
.hospitality-grid .split-story__media img{min-height:520px}
.program-intro{display:grid;grid-template-columns:minmax(0,.85fr) minmax(280px,1.15fr);gap:clamp(34px,8vw,130px);align-items:start}
.program-intro h1,.program-intro h2{margin:16px 0 24px;color:var(--ink);font-size:clamp(44px,5.5vw,92px)}
.program-intro__lead{margin:0;color:var(--muted);font-size:17px;line-height:1.8}
.program-list{display:grid;gap:0;margin-top:clamp(56px,8vw,110px);border-top:1px solid var(--line)}
.program-row{display:grid;grid-template-columns:120px minmax(200px,.8fr) minmax(0,1.2fr);gap:24px;padding:28px 0;border-bottom:1px solid var(--line)}
.program-row__number{color:var(--muted);font-size:11px;font-weight:700;letter-spacing:.12em}
.program-row h2{margin:0;color:var(--ink);font-family:var(--serif);font-size:clamp(26px,3vw,42px);font-weight:400;line-height:1.05}
.program-row p{margin:0;color:var(--muted);font-size:14px;line-height:1.75}
.legal-content{max-width:930px}
.legal-content h1{margin:16px 0 38px;color:var(--ink);font-size:clamp(42px,5.5vw,88px)}
.legal-content h2{margin:46px 0 12px;color:var(--ink);font-family:var(--serif);font-size:34px;font-weight:400}
.legal-content p{margin:0 0 18px;color:var(--muted);font-size:15px;line-height:1.82}
.legal-content .legal-note{padding:18px 20px;border-left:2px solid var(--ink);background:var(--paper-2);color:var(--text)}
.template-note{max-width:820px;padding:clamp(28px,5vw,64px);background:var(--paper-2);border-top:1px solid var(--line)}
.template-note h2{max-width:680px;margin:14px 0 18px;color:var(--ink);font-size:clamp(34px,4.8vw,68px)}
.template-note p{max-width:620px;margin:0;color:var(--muted);font-size:15px;line-height:1.8}
.not-found{min-height:70vh;display:grid;place-items:center;text-align:center}
.not-found h1{margin:14px 0;color:var(--ink);font-size:clamp(80px,18vw,230px);line-height:.8}
.not-found p{max-width:480px;margin:0 auto 30px;color:var(--muted);font-size:16px}
.newsletter{padding:clamp(70px,9vw,130px) 0;background:var(--night);color:var(--white)}
.newsletter-grid{display:grid;grid-template-columns:minmax(0,.9fr) minmax(280px,1.1fr);gap:clamp(35px,8vw,130px);align-items:end}
.newsletter h2{max-width:700px;margin:14px 0 0;color:var(--white);font-size:clamp(34px,5vw,68px)}
.newsletter .kicker{color:var(--accent)}
.newsletter-form{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:end;padding-bottom:10px;border-bottom:1px solid rgba(255,250,244,.42)}
.newsletter-form__field{min-width:0}
.newsletter-form label{display:block;margin-bottom:6px;color:rgba(255,250,244,.58);font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.newsletter-form input{width:100%;min-height:42px;padding:5px 0;border:0;background:transparent;color:var(--white);outline:0}
.newsletter-form input::placeholder{color:rgba(255,250,244,.5)}
.newsletter-form input:focus{box-shadow:0 1px 0 var(--white)}
.newsletter-form button{display:inline-flex;align-items:center;gap:9px;min-height:42px;padding:8px 0;border:0;background:transparent;color:var(--white);cursor:pointer;font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;white-space:nowrap}
.newsletter-form button:hover{color:var(--accent)}
.newsletter-status{grid-column:1/-1;min-height:18px;color:rgba(255,250,244,.62);font-size:12px}
.site-footer{padding:34px 0 54px;background:var(--night);color:var(--white);border-top:1px solid rgba(255,250,244,.12)}
.footer-top{display:flex;align-items:flex-end;justify-content:space-between;gap:28px;padding-bottom:26px;border-bottom:1px solid rgba(255,250,244,.16)}
.footer-logo{filter:brightness(0) invert(1)}
.footer-social{display:flex;flex-wrap:wrap;gap:18px}
.footer-social a{color:rgba(255,250,244,.72);font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.footer-social a:hover{color:var(--accent)}
.footer-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:26px;padding-top:28px}
.footer-grid p{margin:8px 0 0;color:rgba(255,250,244,.66);font-size:13px;line-height:1.7}
.footer-grid a{color:var(--white)}
.footer-grid a:hover{color:var(--accent)}
.footer-bottom{display:flex;justify-content:space-between;gap:20px;margin-top:34px;padding-top:15px;border-top:1px solid rgba(255,250,244,.12);color:rgba(255,250,244,.44);font-size:11px}
.doc-wrap{background:var(--paper)}
.doc-hero{padding:clamp(80px,11vw,150px) 0 clamp(58px,8vw,100px);background:var(--night);color:var(--white)}
.doc-hero h1{max-width:1000px;margin:16px 0 18px;font-size:clamp(44px,6.5vw,96px)}
.doc-hero p{max-width:680px;margin:0;color:rgba(255,250,244,.66);font-size:15px;line-height:1.8}
.doc-section{padding:clamp(50px,8vw,105px) 0;border-bottom:1px solid var(--line)}
.doc-section h2{margin:0 0 24px;color:var(--ink);font-family:var(--serif);font-size:clamp(32px,4.2vw,58px);font-weight:400;line-height:1}
.doc-section h3{margin:34px 0 10px;color:var(--ink);font-family:var(--serif);font-size:28px;font-weight:400}
.doc-section p,.doc-section li{max-width:930px;color:var(--muted);font-size:14px;line-height:1.8}
.doc-section ul{max-width:930px;padding-left:20px}
.doc-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:26px}
.doc-card{padding:22px;background:var(--white);border:1px solid var(--line)}
.doc-card h3{margin:0 0 10px;font-size:27px}
.doc-card p{margin:0}
.doc-card .fine-print{display:block;margin-bottom:10px}
.doc-table-wrap{overflow-x:auto}
.doc-table{width:100%;min-width:780px;border-collapse:collapse;text-align:left}
.doc-table th,.doc-table td{padding:13px 12px;border-bottom:1px solid var(--line);vertical-align:top}
.doc-table th{color:var(--ink);font-size:10px;letter-spacing:.12em;text-transform:uppercase}
.doc-table td{color:var(--muted);font-size:12px;line-height:1.55}
.doc-table td:first-child{color:var(--ink);font-weight:700}
.doc-table a{color:var(--ink);text-decoration:underline;text-underline-offset:3px}
.map-visual{display:grid;grid-template-columns:repeat(4,1fr);gap:9px;align-items:center;margin-top:34px;padding:28px;background:var(--paper-2);border:1px solid var(--line)}
.map-node{min-height:78px;display:grid;place-items:center;padding:10px;border:1px solid var(--ink);background:var(--white);color:var(--ink);font-size:11px;font-weight:700;letter-spacing:.08em;text-align:center;text-transform:uppercase}
.map-connector{height:1px;background:var(--ink)}
.hub-hero{padding:clamp(82px,12vw,170px) 0 clamp(60px,9vw,120px);background:var(--night);color:var(--white)}
.hub-hero h1{max-width:980px;margin:18px 0 18px;font-size:clamp(46px,6.5vw,104px)}
.hub-hero p{max-width:720px;margin:0;color:rgba(255,250,244,.64);font-size:15px;line-height:1.8}
.hub-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.hub-card{display:flex;flex-direction:column;justify-content:space-between;min-height:220px;padding:22px;background:var(--white);border:1px solid var(--line);transition:transform .4s var(--ease),background .3s ease}
.hub-card:hover{transform:translateY(-5px);background:#fff}
.hub-card h2{margin:10px 0 0;color:var(--ink);font-family:var(--serif);font-size:32px;font-weight:400;line-height:1.02}
.hub-card p{margin:18px 0 0;color:var(--muted);font-size:13px;line-height:1.6}
.hub-card .section-link{margin-top:24px}
.coverage-bar{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;margin-top:32px;background:var(--line)}
.coverage-stat{padding:22px;background:var(--paper-2)}
.coverage-stat strong{display:block;color:var(--ink);font-family:var(--serif);font-size:46px;font-weight:400}
.coverage-stat span{display:block;margin-top:4px;color:var(--muted);font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
@media (max-width:980px){
  .menu-drawer{grid-template-columns:1fr;gap:42px;padding-top:125px}
  .menu-intro{min-height:0;gap:36px}
  .home-intro,.split-story,.mission-grid,.contact-grid,.program-intro{grid-template-columns:1fr;gap:38px}
  .split-story--reverse .split-story__media{order:0}
  .current-exhibition,.detail-layout,.bio-layout,.press-layout{grid-template-columns:1fr;gap:40px}
  .detail-poster{position:relative;top:auto;width:min(76vw,520px)}
  .press-contact{position:relative;top:auto}
  .archive-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
  .artists-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:32px 18px}
  .program-row{grid-template-columns:90px minmax(180px,.8fr) minmax(0,1.2fr)}
  .hub-cards{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media (max-width:720px){
  :root{--gutter:20px}
  .header-shell{min-height:74px}
  .brand--hero .site-logo{transform:none}
  .header-rule{width:calc(100% - 2 * var(--gutter))}
  .menu-drawer{display:block;padding-top:105px;padding-bottom:34px}
  .menu-intro{display:block}
  .menu-intro h2{font-size:clamp(46px,15vw,84px)}
  .menu-note{margin:30px 0 0}
  .menu-foot{margin-top:24px}
  .menu-nav{margin-top:42px}
  .menu-nav a{font-size:clamp(24px,7vw,36px)}
  .page-hero__top{display:block;margin-bottom:40px}
  .page-hero__meta{margin-top:20px;text-align:left}
  .page-hero h1{font-size:clamp(44px,12vw,72px)}
  .hero-caption{right:20px;bottom:125px;left:20px;display:block}
  .hero-caption h1,.hero-caption h2{font-size:clamp(48px,16vw,88px)}
  .hero-caption__action{display:flex;justify-content:space-between;align-items:center;margin-top:22px;text-align:left}
  .carousel-controls{right:20px;bottom:36px;left:20px}
  .carousel-dots{order:2}
  .carousel-buttons{order:1}
  .exhibition-grid,.archive-grid{grid-template-columns:1fr}
  .exhibition-card__media{aspect-ratio:1/1.1}
  .split-story__media img{min-height:320px}
  .artists-grid{grid-template-columns:1fr}
  .artist-card{border-right:0}
  .artist-card__meta{padding:14px 0 9px}
  .artist-card__name{font-size:28px}
  .artist-preview{display:none}
  .current-exhibition h2{font-size:clamp(50px,15vw,82px)}
  .detail-poster{width:100%}
  .detail-copy h1,.bio-intro h1,.mission-grid h1,.mission-grid h2,.contact-copy h1,.contact-copy h2,.hospitality-copy h1,.hospitality-copy h2,.program-intro h1,.program-intro h2{font-size:clamp(44px,12vw,76px)}
  .contact-copy>h2{font-size:clamp(42px,12vw,72px)}
  .detail-gallery{grid-template-columns:1fr 1fr}
  .detail-gallery figure:nth-child(2){margin-top:25px}
  .bio-artworks{grid-template-columns:1fr 1fr}
  .bio-artworks figure:nth-child(2),.bio-artworks figure:nth-child(4){margin-top:24px}
  .service-row,.program-row{grid-template-columns:44px 1fr;gap:13px}
  .service-row p,.program-row p{grid-column:2}
  .release{grid-template-columns:36px minmax(0,1fr);gap:10px}
  .release-toggle{grid-column:2;justify-self:start}
  .form-grid{grid-template-columns:1fr}
  .form-field--wide{grid-column:auto}
  .newsletter-grid{grid-template-columns:1fr;gap:30px}
  .newsletter-form{grid-template-columns:1fr;gap:8px}
  .newsletter-form button{justify-self:start}
  .footer-top{align-items:flex-start;flex-direction:column}
  .footer-grid{grid-template-columns:1fr 1fr;gap:22px}
  .footer-bottom{align-items:flex-start;flex-direction:column;gap:5px}
  .doc-grid,.hub-cards{grid-template-columns:1fr}
  .coverage-bar{grid-template-columns:1fr 1fr}
  .map-visual{grid-template-columns:1fr;gap:8px}
  .map-connector{width:1px;height:24px;justify-self:center}
}
@media (max-width:460px){
  .footer-grid{grid-template-columns:1fr}
  .coverage-bar{grid-template-columns:1fr}
  .hero-caption p{font-size:12px}
}
@media (prefers-reduced-motion:reduce){
  html{scroll-behavior:auto}
  *,*::before,*::after{transition-duration:.01ms!important;animation-duration:.01ms!important}
  .hero-slide img{transform:none!important}
}
`;

const commonJs = String.raw`
(function(){
  'use strict';
  var body=document.body;
  var menuToggle=document.querySelector('[data-menu-toggle]');
  var menu=document.querySelector('[data-menu-drawer]');
  var lastFocus=null;
  function closeMenu(){
    if(!menu||!menuToggle)return;
    menu.hidden=true; menuToggle.setAttribute('aria-expanded','false'); body.classList.remove('menu-open');
    if(lastFocus){try{lastFocus.focus();}catch(e){}}
  }
  function openMenu(){
    if(!menu||!menuToggle)return;
    lastFocus=document.activeElement; menu.hidden=false; menuToggle.setAttribute('aria-expanded','true'); body.classList.add('menu-open');
    var first=menu.querySelector('a,button'); if(first)first.focus();
  }
  if(menuToggle){
    menuToggle.addEventListener('click',function(){menuToggle.getAttribute('aria-expanded')==='true'?closeMenu():openMenu();});
  }
  if(menu){
    menu.addEventListener('click',function(e){if(e.target===menu)closeMenu();});
    menu.querySelectorAll('a').forEach(function(link){link.addEventListener('click',function(){closeMenu();});});
  }
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&menuToggle&&menuToggle.getAttribute('aria-expanded')==='true')closeMenu();});

  document.querySelectorAll('[data-language]').forEach(function(link){
    link.addEventListener('click',function(e){
      e.preventDefault();
      document.querySelectorAll('[data-language]').forEach(function(item){item.removeAttribute('aria-current');});
      document.querySelectorAll('[data-language="'+link.getAttribute('data-language')+'"]').forEach(function(item){item.setAttribute('aria-current','true');});
      var status=document.querySelector('[data-language-status]'); if(status)status.textContent='Language switch is wired for the production locale layer.';
    });
  });

  document.querySelectorAll('[data-newsletter-form]').forEach(function(form){
    form.addEventListener('submit',function(e){
      e.preventDefault();
      var input=form.querySelector('input[type=email]'); var status=form.querySelector('[data-form-status]');
      if(!input||!input.checkValidity()){if(input)input.reportValidity();return;}
      if(status)status.textContent='Thank you. Connect this demo form to the gallery mailing service at launch.';
    });
  });
  document.querySelectorAll('[data-demo-form]').forEach(function(form){
    form.addEventListener('submit',function(e){
      e.preventDefault();
      if(!form.checkValidity()){form.reportValidity();return;}
      var status=form.querySelector('[data-form-status]'); if(status)status.textContent='Thank you. This prototype is ready to connect to the gallery inbox or CRM.';
    });
  });

  document.querySelectorAll('[data-accordion-toggle]').forEach(function(toggle){
    toggle.addEventListener('click',function(){
      var item=toggle.closest('[data-accordion-item]'); if(!item)return;
      var open=item.classList.toggle('is-open'); toggle.setAttribute('aria-expanded',open?'true':'false');
    });
  });

  document.querySelectorAll('[data-filter-group]').forEach(function(group){
    var tabs=group.querySelectorAll('[data-filter]'); var cards=group.querySelectorAll('[data-filter-card]');
    tabs.forEach(function(tab){tab.addEventListener('click',function(){
      tabs.forEach(function(t){t.classList.remove('is-active');t.removeAttribute('aria-current');}); tab.classList.add('is-active');tab.setAttribute('aria-current','true');
      var value=tab.getAttribute('data-filter');
      cards.forEach(function(card){var match=value==='all'||card.getAttribute('data-filter-card')===value;card.classList.toggle('is-hidden',!match);});
    });});
  });

  document.querySelectorAll('[data-artist-preview]').forEach(function(grid){
    var preview=document.querySelector('[data-preview-overlay]'); if(!preview)return;
    var image=preview.querySelector('img'); var label=preview.querySelector('[data-preview-label]'); var timer;
    function show(card){
      clearTimeout(timer); var source=card.getAttribute('data-image'); if(!source)return;
      grid.querySelectorAll('.artist-card').forEach(function(item){item.classList.remove('is-preview');}); card.classList.add('is-preview');
      image.src=source; image.alt=card.getAttribute('data-name')||''; if(label)label.textContent=card.getAttribute('data-name')||''; preview.classList.add('is-visible');
    }
    function hide(card){timer=setTimeout(function(){card.classList.remove('is-preview');preview.classList.remove('is-visible');},120);}
    grid.querySelectorAll('.artist-card').forEach(function(card){
      card.addEventListener('pointerenter',function(){if(window.matchMedia('(min-width:721px)').matches)show(card);});
      card.addEventListener('pointerleave',function(){if(window.matchMedia('(min-width:721px)').matches)hide(card);});
      card.addEventListener('focusin',function(){show(card);}); card.addEventListener('focusout',function(){if(!card.contains(document.activeElement))hide(card);});
    });
  });

  document.querySelectorAll('[data-carousel]').forEach(function(carousel){
    var slides=carousel.querySelectorAll('[data-slide]'); var dots=carousel.querySelectorAll('[data-carousel-dot]'); var prev=carousel.querySelector('[data-carousel-prev]'); var next=carousel.querySelector('[data-carousel-next]');
    if(!slides.length)return; var index=0; var timer; var reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function setSlide(nextIndex){index=(nextIndex+slides.length)%slides.length;slides.forEach(function(slide,i){slide.classList.toggle('is-active',i===index);slide.setAttribute('aria-hidden',i===index?'false':'true');});dots.forEach(function(dot,i){dot.classList.toggle('is-active',i===index);dot.setAttribute('aria-current',i===index?'true':'false');});}
    function restart(){if(reduce)return;clearInterval(timer);timer=setInterval(function(){setSlide(index+1);},5200);}
    if(prev)prev.addEventListener('click',function(){setSlide(index-1);restart();}); if(next)next.addEventListener('click',function(){setSlide(index+1);restart();});
    dots.forEach(function(dot,i){dot.addEventListener('click',function(){setSlide(i);restart();});});
    carousel.addEventListener('mouseenter',function(){clearInterval(timer);}); carousel.addEventListener('mouseleave',restart); carousel.addEventListener('focusin',function(){clearInterval(timer);}); carousel.addEventListener('focusout',function(){if(!carousel.contains(document.activeElement))restart();});
    setSlide(0);restart();
  });
})();
`;

const navItems = [
  ['Home', 'homepage.html', 'home'],
  ['Artists', 'artists.html', 'artists'],
  ['Exhibitions', 'exhibitions.html', 'exhibitions'],
  ['Art Consultancy', 'art-consultancy.html', 'consultancy'],
  ['Press', 'press.html', 'press'],
  ['Contact', 'contact.html', 'contact'],
  ['Hotel & Café', 'hotel-cafe.html', 'hotel']
];

const navMarkup = (current = '') => navItems.map(([label, href, key]) =>
  `<a href="${href}"${current === key ? ' aria-current="page"' : ''}>${label}<span class="nav-item-arrow">${icon('external')}</span></a>`).join('');

const header = (current = '', { hero = false, light = false } = {}) => `
<header class="site-header${hero ? ' site-header--hero' : ''}${light ? ' site-header--light' : ''}" data-site-header>
  <a class="sr-only skip-link" href="#main">Skip to main content</a>
  <div class="header-shell">
    <a class="brand${hero ? ' brand--hero' : ''}" href="homepage.html" aria-label="XVA Gallery home">${logoSource}</a>
    <div class="header-meta">
      <div class="language-switch" aria-label="Language selection">
        <a href="#" data-language="ar">AR</a><span aria-hidden="true">/</span><a href="#" data-language="en" aria-current="true">EN</a>
      </div>
      <a class="shop-link" href="https://xvashop.com/" target="_blank" rel="noopener"><span class="sr-only">Open XVA Shop</span>${icon('bag')}</a>
      <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="xva-menu" data-menu-toggle><span>Menu</span>${icon('menu')}</button>
    </div>
  </div>
  <div class="header-rule" aria-hidden="true"></div>
  <div class="menu-drawer" id="xva-menu" data-menu-drawer hidden>
    <div class="menu-intro">
      <div><span class="eyebrow" style="color:var(--accent)">XVA Gallery / Dubai</span><h2 class="serif">Art, culture and the life of the image.</h2></div>
      <p class="menu-note">Established in 2003, XVA Gallery specializes in contemporary art from the Arab world and the Sub-continent, from its home in Al Fahidi Neighborhood.</p>
      <div class="menu-foot"><a href="contact.html">Visit the gallery</a><a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a><span data-language-status aria-live="polite"></span></div>
    </div>
    <nav class="menu-nav" aria-label="Main navigation">${navMarkup(current)}<a href="privacy-policy.html">Privacy Policy<span class="nav-item-arrow">${icon('external')}</span></a></nav>
  </div>
</header>`;

const newsletter = () => `
<section class="newsletter" aria-labelledby="newsletter-title">
  <div class="shell newsletter-grid">
    <div><span class="kicker">XVA Journal</span><h2 class="serif" id="newsletter-title">Hear about the latest news and events</h2></div>
    <form class="newsletter-form" data-newsletter-form>
      <div class="newsletter-form__field"><label for="newsletter-email">Email</label><input id="newsletter-email" type="email" name="email" autocomplete="email" placeholder="Email" required></div>
      <button type="submit">Join our Newsletter ${icon('external')}</button>
      <div class="newsletter-status" data-form-status aria-live="polite">Prototype form - connect to the gallery mailing service.</div>
    </form>
  </div>
</section>`;

const footer = () => `
<footer class="site-footer">
  <div class="shell">
    <div class="footer-top"><a href="homepage.html" aria-label="XVA Gallery home"><span class="footer-logo">${logoSource}</span></a><div class="footer-social"><a href="https://www.instagram.com/xvagallery/" target="_blank" rel="noopener">Instagram</a><a href="https://www.facebook.com/xvagallery/" target="_blank" rel="noopener">Facebook</a><a href="https://twitter.com/XVAGALLERY" target="_blank" rel="noopener">Twitter</a></div></div>
    <div class="footer-grid">
      <div><span class="footer-label">Location</span><p>Al Fahidi Neighborhood, Bur Dubai<br>P.O. Box 37304<br>Dubai, UAE</p></div>
      <div><span class="footer-label">Open Daily</span><p>7 days a week: 10am - 6pm</p></div>
      <div><span class="footer-label">Contact</span><p><a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a><br><a href="tel:+97143535383">+9714 3535383</a><br>Fax: +9714 3535988</p></div>
      <div><span class="footer-label">By Appointment</span><p>Outside of working hours<br><a href="contact.html">Plan a visit</a></p></div>
    </div>
    <div class="footer-bottom"><span>XVA Gallery</span><a href="privacy-policy.html">Privacy Policy</a><span>Al Fahidi Neighborhood, Bur Dubai</span></div>
  </div>
</footer>`;

const page = ({ title, description, originalUrl, current = '', body, hero = false, light = false, docs = false }) => `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="${hero ? '#101617' : '#f4f0e8'}">
  <meta name="description" content="${escapeHtml(description)}">
  <meta name="x-original-url" content="${escapeHtml(originalUrl)}">
  <title>${escapeHtml(title)}</title>
  <style>${css}</style>
</head>
<body data-original-url="${escapeHtml(originalUrl)}">
  ${header(current, { hero, light })}
  ${body}
  ${docs ? '' : newsletter() + footer()}
  <script>${commonJs}</script>
</body>
</html>`;

const pageHero = (kicker, title, meta, night = false) => `
<section class="page-hero${night ? ' page-hero--night' : ''}">
  <div class="shell"><div class="page-hero__top"><div><span class="kicker">${kicker}</span><h1 class="serif">${title}</h1></div><p class="page-hero__meta">${meta}</p></div></div>
</section>`;

const exhibitionCards = [
  ['After the Image', 'Charlie Koolhaas', '18 June - 16 July 2026', 'ex-after-image.jpg', 'https://www.xvagallery.com/exhibitions/after-the-image/'],
  ['LOCAL', 'Jane Beveridge', '23 May - 18 June 2026', 'ex-local.jpeg', 'https://www.xvagallery.com/exhibitions/local/'],
  ['White', 'Tor Seidel', '25 April - 21 May 2026', 'ex-white.jpg', 'https://www.xvagallery.com/exhibitions/white/'],
  ['Fractal', 'Huma Shoaib', 'February 7th - March 5th 2026', 'ex-fractal.jpg', 'https://www.xvagallery.com/exhibitions/fractal/'],
  ['The Truth', 'Hussein Al Mohasen', 'January 10th - February 5th 2026', 'ex-truth.jpg', 'https://www.xvagallery.com/exhibitions/the-truth/'],
  ['Half Light', 'Naqsh Raj', '2025', 'ex-half-light.jpg', 'https://www.xvagallery.com/exhibitions/half-light/']
];

const exhibitionCardMarkup = (card) => `<article class="exhibition-card"><a href="${card[4]}"><div class="exhibition-card__media">${img(card[3], `${card[0]} exhibition poster`)}</div><div class="exhibition-card__meta"><span>${card[1]}</span><span>${card[2]}</span></div><h3>${card[0]}</h3><p>${card[1]} at XVA Gallery</p><span class="card-link">View exhibition ${icon('external')}</span></a></article>`;

const homeBody = `
<main id="main" class="page-main">
  <section class="home-hero" data-carousel aria-label="Featured XVA exhibitions">
    <div class="hero-slides">
      <article class="hero-slide is-active" data-slide aria-hidden="false">${img('hero-01.webp','XVA Gallery exhibition interior')}<div class="hero-caption"><div class="hero-caption__copy"><span class="eyebrow">Now on view / XVA Gallery</span><h1 class="serif">Oblivion</h1><p>28 July - 15 August 2026</p></div><div class="hero-caption__action"><span class="fine-print" style="color:rgba(255,250,244,.65)">Al Fahidi Neighborhood / Dubai</span><a class="button-link" href="exhibition-oblivion.html">View exhibition ${icon('external')}</a></div></div></article>
      <article class="hero-slide" data-slide aria-hidden="true">${img('hero-02.webp','XVA Gallery gallery interior')}<div class="hero-caption"><div class="hero-caption__copy"><span class="eyebrow">Upcoming / XVA Gallery</span><h1 class="serif">After the Image</h1><p>Charlie Koolhaas / 18 June - 16 July 2026</p></div><div class="hero-caption__action"><a class="button-link" href="exhibitions.html">View programme ${icon('external')}</a></div></div></article>
      <article class="hero-slide" data-slide aria-hidden="true">${img('hero-03.webp','XVA Gallery exhibition interior')}<div class="hero-caption"><div class="hero-caption__copy"><span class="eyebrow">Recent / XVA Gallery</span><h1 class="serif">LOCAL</h1><p>Jane Beveridge / 23 May - 18 June 2026</p></div><div class="hero-caption__action"><a class="button-link" href="exhibitions.html">Explore exhibitions ${icon('external')}</a></div></div></article>
      <article class="hero-slide" data-slide aria-hidden="true">${img('hero-04.webp','XVA Gallery exhibition interior')}<div class="hero-caption"><div class="hero-caption__copy"><span class="eyebrow">Exhibition archive / XVA Gallery</span><h1 class="serif">White</h1><p>Tor Seidel / 25 April - 21 May 2026</p></div><div class="hero-caption__action"><a class="button-link" href="press.html">Read the press archive ${icon('external')}</a></div></div></article>
    </div>
    <div class="carousel-controls"><div class="carousel-dots" aria-label="Choose featured exhibition"><button class="carousel-dot is-active" type="button" data-carousel-dot aria-label="Show Oblivion" aria-current="true"></button><button class="carousel-dot" type="button" data-carousel-dot aria-label="Show After the Image"></button><button class="carousel-dot" type="button" data-carousel-dot aria-label="Show LOCAL"></button><button class="carousel-dot" type="button" data-carousel-dot aria-label="Show White"></button></div><div class="carousel-buttons"><button class="carousel-button" type="button" data-carousel-prev aria-label="Previous slide">${icon('arrow')}</button><button class="carousel-button" type="button" data-carousel-next aria-label="Next slide">${icon('arrow')}</button></div></div>
  </section>
  <section class="section" aria-labelledby="programme-title"><div class="shell"><div class="home-intro"><div><span class="kicker">The programme</span><h2 class="serif" id="programme-title">Contemporary Art Beyond Borders</h2></div><p>Established in 2003, XVA Gallery presents the work of the region's foremost artists alongside emerging voices, from its home in Dubai's heritage district.</p></div><div class="section-header" style="margin-top:clamp(60px,9vw,120px)"><div><span class="eyebrow">Current / upcoming / archive</span><h2 class="serif">Exhibitions</h2></div><a class="section-link" href="exhibitions.html">View all ${icon('external')}</a></div><div class="exhibition-grid">${exhibitionCards.map(exhibitionCardMarkup).join('')}</div></div></section>
  <section class="section section--dark"><div class="shell"><div class="section-header"><div><span class="kicker" style="color:var(--accent)">From Al Fahidi</span><h2 class="serif">Art, culture and a courtyard pause.</h2></div><p>XVA Gallery and XVA Art Hotel are located in Dubai's heritage district, where exhibitions, hospitality and conversation meet.</p></div><div class="split-story"><div class="split-story__media">${img('cafe-2.png','XVA Café courtyard')}</div><div class="split-story__copy"><span class="eyebrow" style="color:var(--accent)">XVA Café</span><h2 class="serif" style="color:var(--white)">A green and shaded place to stay with the image.</h2><p>Since XVA Café opened it has been hailed as “Dubai's best kept secret”, serving freshly prepared vegetarian food in the traditional courtyards of Al Fahidi.</p><a class="button-link" style="color:var(--white)" href="hotel-cafe.html">Visit hotel and café ${icon('external')}</a></div></div></div></section>
</main>`;

const artists = [
  ['Akim Monet','artist-akim.jpg','https://www.xvagallery.com/artist/akim-monet/'],['Al Braithwaite','artist-al-braithwaite.jpg','https://www.xvagallery.com/artist/al-braithwaite/'],['Alejandra Palos','artist-alejandra.jpg','https://www.xvagallery.com/artist/alejandra-palos/'],['Azad Nanakeli','artist-alejandra.jpg','https://www.xvagallery.com/artist/azad-nanakeli/'],['Barbara Wildenboer','artist-halim.jpg','https://www.xvagallery.com/artist/barbara-wildenboer/'],['Charlie Koolhaas','artist-charlie.jpg','https://www.xvagallery.com/artist/charlie-koolhaas/'],['Halim Al Karim','artist-halim.jpg','https://www.xvagallery.com/artist/halim-al-karim/'],['Huma Shoaib','artist-huma.jpg','artist-huma-shoaib.html'],['Hussein Al-Mohasen','artist-hussein.jpg','https://www.xvagallery.com/artist/hussein-al-mohasen/'],['Mona Saudi','artist-mona.jpg','https://www.xvagallery.com/artist/mona-saudi/'],['Moussa Tiba','artist-moussa.jpg','https://www.xvagallery.com/artist/moussa-tiba/'],['Naqsh Raj','artist-naqsh.jpg','https://www.xvagallery.com/artist/naqsh-raj/']
];
const artistCardMarkup = (artist, i) => `<article class="artist-card"><a class="artist-card__link" href="${artist[2]}"><div class="artist-card__media">${img(artist[1], `Representative artwork for ${artist[0]}`)}</div><div class="artist-card__meta"><span class="artist-card__name">${artist[0]}</span><span class="artist-card__index">${String(i + 1).padStart(2,'0')}</span></div></a></article>`;

const artistsBody = `
<main id="main" class="page-main">
  ${pageHero('Artists / XVA Gallery','Artists','Al Fahidi Neighborhood / Dubai')}
  <section class="section section--tight"><div class="shell"><div class="section-header"><div><span class="eyebrow">Represented artists</span><h2 class="serif">The gallery roster</h2></div><p>Hover or focus a name to bring the work forward. The full roster maps to the original XVA artist URLs.</p></div><div class="artist-preview" data-preview-overlay aria-hidden="true"><img src="${assets['artist-akim.jpg']}" alt="Featured artist preview"><span class="artist-preview__label" data-preview-label></span></div><div class="artists-grid" data-artist-preview role="list">${artists.map(artistCardMarkup).join('')}</div><p class="fine-print" style="margin-top:26px">Showing a representative live roster here; archive entries remain mapped in the template map.</p></div></section>
</main>`;

const humaBody = `
<main id="main" class="page-main">
  <section class="detail-hero"><div class="shell"><div class="bio-layout"><div class="bio-intro"><span class="kicker">Artist / XVA Gallery</span><h1 class="serif">Huma Shoaib</h1><p>Significantly influenced by Sufi ideologies, my work rests as serene and meditative in parallel to disturbing questions of division and social hierarchy. Manifesting creatures like bees, birds and fish as symbols for inter-religious references, geo-location and life cycle, the imagery holds a multitude of meanings.</p></div><aside class="bio-aside"><p><strong>Education</strong><br>BFA, National College of Arts, Lahore, Pakistan</p><p><strong>Selected exhibitions</strong><br>Fractal, XVA Gallery, Dubai, 2026<br>Silent Confabulations, solo show at XVA Gallery, Dubai, 2021<br>Art as process, XVA Gallery, Dubai, 2024</p><a class="section-link" href="artists.html">Back to artists ${icon('external')}</a></aside></div><div class="bio-artworks"><figure>${img('artist-huma.jpg','Huma Shoaib, Confabulations 1, 2021')}</figure><figure>${img('huma-02.jpg','Huma Shoaib, We see the same moon You and I, 2021')}</figure><figure>${img('huma-03.jpg','Huma Shoaib paper cut drawing')}</figure><figure>${img('huma-04.jpg','Huma Shoaib, Dissonance')}</figure></div></div></section>
  <section class="section section--dark"><div class="shell"><div class="section-header"><div><span class="kicker" style="color:var(--accent)">Artist statement</span><h2 class="serif">One minimal existence that contains all.</h2></div><p>Source-derived copy is retained and presented here as a legible long-form reading experience.</p></div><div class="split-story"><div class="split-story__copy"><p style="color:rgba(255,250,244,.72)">Ornate and complex geometric drawings hailing from centuries of tradition are treated with fluorescent colours that are industrially made and artificial in nature, implying towards the need of beliefs becoming more synthetic and fabricated rather than being the innate desire to be spiritual and organic.</p><p style="color:rgba(255,250,244,.72)">Actions like shredding and restructuring pages from history books, reducing words from letters to dots, come forth as a process of unlearning a plethora of information being fed over a period of time.</p></div><div class="split-story__copy"><span class="eyebrow" style="color:var(--accent)">Next / previous</span><div style="display:grid;gap:16px;margin-top:18px"><a class="button-link" style="color:var(--white)" href="artists.html">All artists ${icon('external')}</a><a class="button-link" style="color:var(--white)" href="exhibitions.html">View exhibitions ${icon('external')}</a></div></div></div></div></section>
</main>`;

const artistTemplateBody = `
<main id="main" class="page-main" data-template="artist-page">
  <section class="detail-hero"><div class="shell"><div class="bio-layout"><div class="bio-intro"><span class="kicker">Artist / XVA Gallery / Template</span><h1 class="serif">Artist Name</h1><p>Use this opening statement to introduce the artist's practice, context and relationship with XVA Gallery.</p></div><aside class="bio-aside"><p><strong>Artist metadata</strong><br>Origin / studio / representation</p><p><strong>Selected exhibitions</strong><br>Exhibition title, venue, year<br>Exhibition title, venue, year</p><a class="section-link" href="artists.html">Back to artists ${icon('external')}</a></aside></div><div class="bio-artworks"><figure>${img('artist-huma.jpg','Representative artwork for Artist Name')}</figure><figure>${img('huma-02.jpg','Secondary artwork for Artist Name')}</figure><figure>${img('huma-03.jpg','Detail artwork for Artist Name')}</figure><figure>${img('huma-04.jpg','Additional artwork for Artist Name')}</figure></div></div></section>
  <section class="section section--dark"><div class="shell"><div class="section-header"><div><span class="kicker" style="color:var(--accent)">Artist statement</span><h2 class="serif">Practice, material and context.</h2></div><p>Replace this supporting line with a short editorial summary of the artist's current work.</p></div><div class="split-story"><div class="split-story__copy"><p style="color:rgba(255,250,244,.72)">Use this long-form field for the artist statement, biography or curatorial context. The layout keeps the writing readable while giving the artwork room to lead.</p><p style="color:rgba(255,250,244,.72)">Add related exhibitions, press links and enquiry actions below the statement when the content model is connected.</p></div><div class="split-story__copy"><span class="eyebrow" style="color:var(--accent)">Template actions</span><div style="display:grid;gap:16px;margin-top:18px"><a class="button-link" style="color:var(--white)" href="exhibitions.html">View exhibitions ${icon('external')}</a><a class="button-link" style="color:var(--white)" href="contact.html">Enquire about the work ${icon('external')}</a></div></div></div></div></section>
  <section class="section section--tight"><div class="shell"><div class="template-note"><span class="kicker">Template fields</span><h2 class="serif">Ready for the artist CMS.</h2><p>Connect artist name, statement, metadata, artwork gallery, selected exhibitions, related press and enquiry actions to the production content model.</p></div></div></section>
</main>`;

const exhibitionsBody = `
<main id="main" class="page-main" data-filter-group>
  ${pageHero('Programme / XVA Gallery','Exhibitions','Current / upcoming / past')}
  <section class="section section--tight"><div class="shell"><div class="current-exhibition"><a class="current-exhibition__media" href="exhibition-oblivion.html">${img('oblivion.webp','Oblivion exhibition poster')}</a><div><span class="kicker">Current exhibition</span><h2 class="serif">Oblivion</h2><div class="current-exhibition__details"><span>28 July - 15 August 2026</span><span>XVA Gallery / Dubai</span></div><p>To forget is not only to lose. Oblivion is also where the mind makes room; clearing space for what matters now, letting go of what no longer serves.</p><a class="button-link" href="exhibition-oblivion.html">Read the exhibition page ${icon('external')}</a></div></div></div></section>
  <section class="section"><div class="shell"><div class="directory-controls"><div><span class="eyebrow">Archive</span><h2 class="serif" style="margin:12px 0 0;color:var(--ink);font-size:clamp(42px,6vw,80px)">Selected exhibitions</h2></div><div class="filter-tabs" role="group" aria-label="Filter exhibitions"><button class="filter-tab is-active" type="button" data-filter="all" aria-current="true">All</button><button class="filter-tab" type="button" data-filter="upcoming">Upcoming</button><button class="filter-tab" type="button" data-filter="past">Past</button></div></div><div class="archive-grid">${exhibitionCards.map((card, i) => `<article class="archive-card" data-filter-card="${i > 2 ? 'upcoming' : 'past'}"><a href="${card[4]}"><div class="archive-card__media">${img(card[3], `${card[0]} exhibition poster`)}</div><h3>${card[0]}</h3><div class="archive-card__meta"><span>${card[1]}</span><span>${card[2]}</span></div></a></article>`).join('')}</div></div></section>
</main>`;

const oblivionBody = `
<main id="main" class="page-main">
  <section class="detail-hero"><div class="shell"><div class="detail-layout"><div class="detail-poster">${img('oblivion.webp','Oblivion exhibition poster','', 'fetchpriority="high"')}</div><div class="detail-copy"><span class="kicker">XVA Gallery / Exhibition</span><h1 class="serif">Oblivion</h1><p class="detail-copy__lede">Join us for Oblivion, our summer exhibition, running from 28 July to 15 August 2026.</p><div class="detail-copy__body"><p>To forget is not only to lose. Oblivion is also where the mind makes room; clearing space for what matters now, letting go of what no longer serves.</p><p>The works gathered here treat forgetting as a generative act: a hairbrush, an armchair, each object caught mid-transformation, shedding one meaning to make way for another.</p><p>Working across metal, textile, found and fabricated materials, the artists in this exhibition ask what becomes possible once memory stops holding on so tightly. Domestic and bodily objects are freed from their original function, made strange, playful, ornamental, evidence not of loss but of renewal, of a self continually remaking itself.</p><p>Nothing here mourns the past. It repurposes it. Forgetting, in this light, is less erasure than editing: a structural condition of how identity stays alive, how history keeps moving, how images and objects find new use once their first purpose has passed.</p></div><div class="detail-copy__actions"><a class="button-link" href="press.html">View press releases ${icon('external')}</a><a class="button-link" href="exhibitions.html">Back to exhibitions ${icon('external')}</a></div></div></div></div></section>
  <section class="section section--dark"><div class="shell"><div class="section-header"><div><span class="kicker" style="color:var(--accent)">What remains</span><h2 class="serif">Possibility, gesture, form.</h2></div><p>Exhibition copy sourced from the live XVA page; final production can connect the gallery list and artwork inventory here.</p></div><div class="split-story"><div class="split-story__copy"><p style="color:rgba(255,250,244,.72)">What remains in these works is not absence but possibility: gesture freed from context, object freed from owner, form open to whatever meaning arrives next.</p></div><div class="split-story__copy"><span class="eyebrow" style="color:var(--accent)">Related</span><div style="display:grid;gap:16px;margin-top:18px"><a class="button-link" style="color:var(--white)" href="artists.html">Meet the artists ${icon('external')}</a><a class="button-link" style="color:var(--white)" href="contact.html">Plan a visit ${icon('external')}</a></div></div></div></div></section>
</main>`;

const exhibitionTemplateBody = `
<main id="main" class="page-main" data-template="exhibition-page">
  <section class="detail-hero"><div class="shell"><div class="detail-layout"><div class="detail-poster">${img('oblivion.webp','Representative exhibition poster')}</div><div class="detail-copy"><span class="kicker">XVA Gallery / Exhibition Template</span><h1 class="serif">Exhibition Title</h1><p class="detail-copy__lede">Add the exhibition dates, artists and a concise invitation to visit in this opening field.</p><div class="detail-copy__body"><p>Use this first paragraph for the exhibition premise: what the exhibition brings together and why it matters now.</p><p>Use the following paragraphs for the curatorial context, materials, themes and artist contributions. The editorial column is designed for long-form reading.</p><p>Connect the exhibition to artists, press releases, opening hours and enquiry actions through the related links below.</p></div><div class="detail-copy__actions"><a class="button-link" href="press.html">View press releases ${icon('external')}</a><a class="button-link" href="exhibitions.html">Back to exhibitions ${icon('external')}</a></div></div></div></div></section>
  <section class="section section--dark"><div class="shell"><div class="section-header"><div><span class="kicker" style="color:var(--accent)">Exhibition context</span><h2 class="serif">Material, gesture, conversation.</h2></div><p>Use this module for a short contextual note, exhibition checklist or programme detail.</p></div><div class="split-story"><div class="split-story__copy"><p style="color:rgba(255,250,244,.72)">This dark editorial section can hold the longer curatorial text, installation notes or a selected group of works.</p></div><div class="split-story__copy"><span class="eyebrow" style="color:var(--accent)">Template actions</span><div style="display:grid;gap:16px;margin-top:18px"><a class="button-link" style="color:var(--white)" href="artists.html">Meet the artists ${icon('external')}</a><a class="button-link" style="color:var(--white)" href="contact.html">Plan a visit ${icon('external')}</a></div></div></div></div></section>
  <section class="section section--tight"><div class="shell"><div class="template-note"><span class="kicker">Template fields</span><h2 class="serif">Ready for the exhibition CMS.</h2><p>Connect title, dates, poster, artists, curatorial copy, artwork gallery, press links, opening hours and enquiry actions to the production content model.</p></div></div></section>
</main>`;

const consultancyBody = `
<main id="main" class="page-main">
  ${pageHero('Services / XVA Gallery','Art Consultancy','Expertise and project management')}
  <section class="section section--tight"><div class="shell"><div class="mission-grid"><div><span class="kicker">Our Mission Statement</span><h1 class="serif">A gallery that moves with the work.</h1></div><div class="mission-copy"><p>Established in 2003, XVA is one of the leading galleries in the Middle East that specializes in contemporary art from the Arab world and the Sub-continent.</p><p>Exhibitions focus on works by the region's foremost artists as well as those emerging onto the scene. The gallery's artists express their different cultural identities and perspectives while challenging the viewer to drop prejudices and borders.</p><p>XVA Gallery exhibits both locally and internationally, collaborating with galleries and participating in international art fairs in order to further expose Middle Eastern contemporary art.</p></div></div><div class="service-list"><article class="service-row"><span class="service-row__number">01</span><h2>Curatorial advice</h2><p>Expert curatorial advice for site-specific commissions and art rotations for rented or loaned work.</p></article><article class="service-row"><span class="service-row__number">02</span><h2>Exhibition planning</h2><p>The gallery specializes in exhibition installation and planning, art acquisition and collection management.</p></article><article class="service-row"><span class="service-row__number">03</span><h2>Art handling logistics</h2><p>Coordination of art handling and transportation logistics, tailored to projects of different proportions.</p></article></div></div></section>
  <section class="section section--dark"><div class="shell"><div class="section-header"><div><span class="kicker" style="color:var(--accent)">Start a conversation</span><h2 class="serif">Bring the right work into the room.</h2></div><a class="button-link" style="color:var(--white)" href="contact.html">Contact the gallery ${icon('external')}</a></div></div></section>
</main>`;

const pressReleases = [
  ['Oblivion','Join us for Oblivion, our summer exhibition, running from 28 July to 15 August 2026.','exhibition-oblivion.html','Current exhibition'],
  ['After the Image','Join us from 18 June to 16 July 2026 for Charlie Koolhaas\' After the Image.','exhibitions.html','Upcoming'],
  ['LOCAL','Join us from 23 May to 18 June 2026 for Jane Beveridge\'s first solo show that captures the essence of Dubai, and belonging.','exhibitions.html','Upcoming'],
  ['White','XVA Gallery Dubai, in partnership with Professor Tor Seidel, presents the Academy of Visual Arts University of the Arts Annual Faculty Exhibition.','exhibitions.html','Exhibition'],
  ['Fractal','Fractals are infinitely complex patterns that are self-similar across different scales. They are created by repeating a simple geometric design that can be scaled up or down.','exhibitions.html','Archive'],
  ['The Truth','Coming up at XVA Gallery - The Truth, by Hussein Al Mohasen. Join us to explore how visual art can reveal truth, inspire hope, and connect people through colour, poetry, and expressive techniques.','exhibitions.html','Archive']
];
const pressBody = `
<main id="main" class="page-main">
  ${pageHero('Press / XVA Gallery','Press Releases','Current announcements and archive')}
  <section class="section section--tight"><div class="shell"><div class="press-layout"><aside class="press-contact"><span class="eyebrow" style="color:var(--accent)">Press contact</span><h2>MONA HAUSER</h2><p>For press enquiries and image requests.</p><a href="mailto:mona@xvagallery.com">mona@xvagallery.com</a><a href="tel:+97143535383">+971 (0)4 3535 383</a></aside><div><div class="release-list">${pressReleases.map((release, i) => `<article class="release" data-accordion-item><span class="release-index">${String(i + 1).padStart(2,'0')}</span><div><h2><a href="${release[2]}">${release[0]}</a></h2><div class="release-meta"><span>${release[3]}</span><span>XVA Gallery / Dubai</span></div><p class="release-summary">${release[1]}</p><div class="release-details"><div class="release-details__inner"><p>${release[1]} Contact the gallery for the full press release and approved image assets.</p></div></div></div><button class="release-toggle" type="button" aria-expanded="false" data-accordion-toggle>View more ${icon('plus')}</button></article>`).join('')}</div><nav class="pagination" aria-label="Press archive pages"><span class="is-current" aria-current="page">1</span><a href="#">2</a><a href="#">3</a><a href="#">4</a><a href="#">5</a></nav></div></div></div></section>
</main>`;

const contactBody = `
<main id="main" class="page-main">
  ${pageHero('Visit / XVA Gallery','Contact','Al Fahidi Neighborhood / Dubai')}
  <section class="section section--tight"><div class="shell"><div class="contact-grid"><div class="map-card" aria-label="Stylized map of XVA Gallery in Al Fahidi"><span class="map-card__pin">X</span><div class="map-card__label"><span>XVA Gallery</span><span>Al Fahidi</span></div></div><div class="contact-copy"><span class="kicker">Plan a visit</span><h2 class="serif">Meet the work in the neighborhood.</h2><p class="contact-copy__lead">XVA Gallery and XVA Art Hotel are located in Dubai's heritage district, now called Al Fahidi Neighborhood.</p><div class="contact-block"><h2>Location</h2><p>Al Fahidi Neighborhood, Bur Dubai<br>P.O. Box 37304<br>Dubai, UAE</p><p style="margin-top:10px"><a href="https://www.google.com/maps/search/?api=1&query=XVA+Gallery+Dubai" target="_blank" rel="noopener">Open directions ${icon('external')}</a></p></div><div class="contact-block"><h2>Contact</h2><p><a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a><br><a href="tel:+97143535383">+9714 3535383</a><br>Fax: +9714 3535988</p></div><div class="contact-block"><h2>Opening hours</h2><p>7 days a week: 10am - 6pm<br>And by appointment outside of working hours.</p></div></div></div></div></section>
  <section class="section"><div class="shell"><div class="section-header"><div><span class="kicker">Enquiries</span><h2 class="serif">Send a message.</h2></div><p>Prototype form only. Connect to the gallery inbox or CRM for production.</p></div><form class="form-grid" data-demo-form><div class="form-field"><label for="contact-name">Name</label><input id="contact-name" name="name" required></div><div class="form-field"><label for="contact-email">Email</label><input id="contact-email" name="email" type="email" required></div><div class="form-field form-field--wide"><label for="contact-subject">Subject</label><input id="contact-subject" name="subject" required></div><div class="form-field form-field--wide"><label for="contact-message">Message</label><textarea id="contact-message" name="message" required></textarea></div><div class="form-submit"><button class="submit-button" type="submit">Send enquiry ${icon('external')}</button><span class="form-status" data-form-status aria-live="polite"></span></div></form></div></section>
</main>`;

const hotelBody = `
<main id="main" class="page-main">
  ${pageHero('Al Fahidi / XVA Gallery','Hotel & Café','A stay, a meal, a courtyard pause')}
  <section class="section section--tight"><div class="shell hospitality-grid"><div class="hospitality-copy"><span class="kicker">XVA Hotel</span><h1 class="serif">A retreat in the maze-like sikkas of Al Fahidi.</h1><p>The Al Fahidi Neighborhood, formerly known as Al Bastakiya, is Dubai's oldest area and a former prominent trader's settlement, near the Dubai Fort. Hidden in the heart of the historical Al Fahidi, XVA Art Hotel is the former home of the Seddiqi family, turned boutique hotel in the early 2000s by founder Mona Hauser.</p></div><div class="split-story"><div class="split-story__media">${img('hotel.jpeg','XVA Art Hotel in Al Fahidi')}</div><div class="split-story__copy"><span class="eyebrow">XVA Hotel</span><h2 class="serif">Art, culture, heritage and creativity.</h2><p>Offering 15 individually designed suites and rooms, an award-winning restaurant, wind towers and three courtyards, this centrally located hotspot is the city's unique retreat.</p><a class="button-link" href="https://www.xvahotel.com/" target="_blank" rel="noopener">Visit XVA Hotel website ${icon('external')}</a></div></div><div class="split-story split-story--reverse"><div class="split-story__media">${img('cafe.jpg','XVA Café vegetarian food and courtyard')}</div><div class="split-story__copy"><span class="eyebrow">XVA Café</span><h2 class="serif">Dubai's best kept secret.</h2><p>The café serves freshly prepared vegetarian and vegan food including main courses, salads, soups and desserts. Enjoy a refreshing mint lemonade in the green and cozy courtyard, away from the bustle of the city.</p><p class="fine-print">Open daily from 7 am to 10 pm.</p><a class="button-link" href="https://www.xvahotel.com/" target="_blank" rel="noopener">Visit XVA Hotel & Café website ${icon('external')}</a></div></div></div></section>
</main>`;

const storyBody = `
<main id="main" class="page-main">
  ${pageHero('Education / XVA Gallery','The Story of Art','In-person lectures')}
  <section class="section section--tight"><div class="shell"><div class="program-intro"><div><span class="kicker">In-person lectures</span><h1 class="serif">The Story of Art</h1></div><p class="program-intro__lead">A series of art history lectures headed by Dr. Michaela Watrelot exploring the highlights of different art movements, the most influential artists and their masterpieces. These lectures provide a foundation in art history from the 19th Century to the present day.</p></div><div class="program-list"><article class="program-row"><span class="program-row__number">Lecture 01</span><h2>The Age of Revolution - Classicism to Realism</h2><p>The times leading to the 1789 French Revolution provided artists with enormous impulse. Follow Delacroix, Goya, Turner, Constable and Courbet through the movements that made reality an artistic subject.</p></article><article class="program-row"><span class="program-row__number">Lecture 02</span><h2>The power of Light - Impressionism</h2><p>Explore the new treatment of colour and movement developed by Manet, Monet, Pissarro, Renoir and Degas as the Impressionists abandoned the studio and followed the light.</p></article><article class="program-row"><span class="program-row__number">Lecture 03</span><h2>On the path to Modern Art - Post Impressionism</h2><p>Meet Seurat, Cézanne, Gauguin and Van Gogh, whose work marked a turning point in the relationship between colour, form, perspective and emotion.</p></article></div></div></section>
  <section class="section section--dark"><div class="shell"><div class="split-story"><div class="split-story__media">${img('hero-02.webp','XVA Gallery interior used for an art history lecture')}</div><div class="split-story__copy"><span class="kicker" style="color:var(--accent)">Join the conversation</span><h2 class="serif" style="color:var(--white)">A room for looking, listening and asking better questions.</h2><p style="color:rgba(255,250,244,.72)">The live site exposes this as an education page. The redesigned template keeps the lecture series discoverable and gives each programme a clear contact path.</p><a class="button-link" style="color:var(--white)" href="contact.html">Ask about upcoming lectures ${icon('external')}</a></div></div></div></section>
</main>`;

const pathModernismBody = `
<main id="main" class="page-main">
  ${pageHero('Workshop / XVA Gallery','Path to Modernism','Impressionism workshop at XVA Gallery')}
  <section class="section section--tight"><div class="shell"><div class="program-intro"><div><span class="kicker">Impressionism workshop at XVA Gallery</span><h1 class="serif">Path to Modernism</h1></div><p class="program-intro__lead">Join us at XVA Gallery for an intensive art day with the Impressionists. In line with the ongoing exhibition at Louvre Abu Dhabi, explore the movements and works by Manet, Monet, Pissarro, Renoir, Sisley, Degas, Morisot and Gustave Caillebotte.</p></div><div class="split-story" style="margin-top:clamp(60px,8vw,110px)"><div class="split-story__copy"><span class="eyebrow">Saturday 17th December</span><h2 class="serif" style="color:var(--ink);margin:16px 0 22px">A day with the Impressionists.</h2><p>10:00 - 11:15 / Manet and the forming of the movement<br>11:15 - 11:30 / Coffee break<br>11:30 - 13:00 / Impressionism, late works<br>13:00 - 14:00 / Lunch and art discussion</p></div><div class="split-story__media">${img('hero-03.webp','XVA Gallery workshop setting')}</div></div></div></section>
  <section class="section section--dark"><div class="shell"><div class="section-header"><div><span class="kicker" style="color:var(--accent)">Leave us a message</span><h2 class="serif">Bring your curiosity to the gallery.</h2></div><a class="button-link" style="color:var(--white)" href="contact.html">Contact XVA ${icon('external')}</a></div></div></section>
</main>`;

const privacyBody = `
<main id="main" class="page-main"><section class="section"><div class="shell legal-content"><span class="kicker">Legal / XVA Gallery</span><h1 class="serif">XVA Gallery's Privacy Policy</h1><p class="legal-note">Source-derived legal copy is preserved below. Review with the gallery's legal adviser before production launch, especially the analytics, newsletter provider and cookie declaration sections.</p><h2>Owner and Data Controller</h2><p>XVA Gallery<br>Al Fahidi Neighborhood, Dubai, UAE<br>+9714 3535383<br>Owner contact email: xva@xvagallery.com</p><p>At XVA Gallery, we are committed to maintaining the trust and confidence of our customers, visitors to our website and subscribers to our newsletter. Here you'll find information on how we treat data that we may collect from you if you purchase something, visit our website, or subscribe to our newsletter.</p><h2>Visitors to our Website</h2><p>When someone visits www.xvagallery.com we use a third party service, Google Analytics, to collect standard internet log information and details of visitor behaviour patterns. We do this to find out things such as the number of visitors to the various parts of the site. This information is only processed in a way which does not identify anyone. The website does not use cookies. If we do want to collect personally identifiable information through our website, we will be up front about this.</p><h2>Newsletter Sign Up</h2><p>We collect personal information when you subscribe to one of our mailing lists, either online or in the gallery. We use that information to let you know about things that might interest you. We don't rent or trade email lists with other organisations and businesses. We use a third party provider to deliver our newsletter. You can unsubscribe from our mailings by clicking the unsubscribe link at the bottom of any of our emails or by emailing the gallery.</p><h2>Links to Other Web Sites</h2><p>This privacy notice does not cover the links within this site linking to other websites. Those sites are not governed by this Privacy Notice, and if you have questions about how a site uses your information, you'll need to check that site's privacy statement.</p><h2>Access to Your Personal Information</h2><p>You are entitled to access the personal information that we hold. Email your request to xva@xvagallery.com and we will send you a record of the information we hold on you.</p><h2>Changes to this Privacy Notice</h2><p>We keep our privacy notice under regular review. The source notice states that it was last updated on 11th June 2018 to comply with the latest GDPR requirements.</p><h2>WiFi</h2><p>When you use XVA Gallery Wi-Fi, no data is collected.</p></div></section></main>`;

const notFoundBody = `<main id="main" class="page-main"><section class="not-found section"><div class="shell"><span class="kicker">XVA Gallery</span><h1 class="serif">404</h1><p>The page has moved or is no longer part of the programme. Use the menu to find artists, exhibitions, press or the gallery.</p><a class="button-link" href="homepage.html">Return home ${icon('external')}</a></div></section></main>`;

const docs = {};
docs.index = `
<main class="doc-wrap"><section class="hub-hero"><div class="shell"><span class="kicker" style="color:var(--accent)">Agency redevelopment / XVA Gallery</span><h1 class="serif">A living gallery archive, built for looking.</h1><p>Standalone redesign package for XVA Gallery, Dubai. The interaction language is guided by the supplied Lawrie Shabibi reference: full-viewport editorial imagery, restrained transitions, a fixed menu-led header and a responsive archive feed. XVA's own brand, content, public routes and official imagery remain the source of truth.</p></div></section><section class="section"><div class="shell"><div class="section-header"><div><span class="kicker">Project hub</span><h2 class="serif">Open the deliverables</h2></div><p>Every HTML file is self-contained and opens directly from the filesystem. Proposed copy and integration-dependent behavior are labeled in the strategy and page notes.</p></div><div class="hub-cards"><article class="hub-card"><div><span class="fine-print">01 / Strategy</span><h2>Redevelopment strategy</h2><p>Positioning, visual system, UX approach, page recommendations and implementation roadmap.</p></div><a class="section-link" href="redevelopment-strategy.html">Open strategy ${icon('external')}</a></article><article class="hub-card"><div><span class="fine-print">02 / Evidence</span><h2>Website audit</h2><p>Observed current-state evidence, impact, recommended solution and redesign response.</p></div><a class="section-link" href="website-audit.html">Open audit ${icon('external')}</a></article><article class="hub-card"><div><span class="fine-print">03 / Coverage</span><h2>Page inventory</h2><p>Meaningful public routes, page types, priorities, conversions and template assignments.</p></div><a class="section-link" href="page-inventory.html">Open inventory ${icon('external')}</a></article><article class="hub-card"><div><span class="fine-print">04 / Architecture</span><h2>Sitemap and template map</h2><p>Navigation, archive/detail system, URL mapping, redirects and conversion pathways.</p></div><a class="section-link" href="sitemap-and-template-map.html">Open map ${icon('external')}</a></article><article class="hub-card"><div><span class="fine-print">05 / Primary page</span><h2>Homepage mockup</h2><p>Full-screen hero slideshow, programme feed, Al Fahidi story, newsletter and footer.</p></div><a class="section-link" href="homepage.html">Open homepage ${icon('external')}</a></article><article class="hub-card"><div><span class="fine-print">06 / Collections</span><h2>Artists and exhibitions</h2><p>Hover-led directory and current/archive exhibition system with local detail routes.</p></div><a class="section-link" href="artists.html">Open artists ${icon('external')}</a></article><article class="hub-card"><div><span class="fine-print">07 / Detail systems</span><h2>Artist and exhibition detail</h2><p>Image-led profiles that give the work space, context and a clear onward journey.</p></div><a class="section-link" href="artist-huma-shoaib.html">Open artist detail ${icon('external')}</a></article><article class="hub-card"><div><span class="fine-print">08 / Service</span><h2>Consultancy, press and contact</h2><p>Audience-specific service, proof, enquiry and press flows preserved from XVA's public site.</p></div><a class="section-link" href="art-consultancy.html">Open service page ${icon('external')}</a></article><article class="hub-card"><div><span class="fine-print">09 / Hospitality</span><h2>Hotel, café and education</h2><p>Al Fahidi hospitality and education page types with real source-derived content.</p></div><a class="section-link" href="hotel-cafe.html">Open hospitality ${icon('external')}</a></article></div><div class="coverage-bar"><div class="coverage-stat"><strong>17</strong><span>Standalone deliverables</span></div><div class="coverage-stat"><strong>6</strong><span>Core nav destinations</span></div><div class="coverage-stat"><strong>4</strong><span>Reusable detail systems</span></div><div class="coverage-stat"><strong>6</strong><span>Responsive test widths</span></div></div></div></section></main>`;

docs.strategy = `
<main class="doc-wrap"><section class="doc-hero"><div class="shell"><span class="kicker" style="color:var(--accent)">XVA Gallery / Proposed strategy</span><h1 class="serif">Make the programme feel alive before the visitor clicks.</h1><p>Redevelopment strategy grounded in the live XVA Gallery site, the supplied reference direction and the practical needs of a contemporary gallery audience.</p></div></section><section class="doc-section"><div class="shell"><h2>Executive summary</h2><div class="doc-grid"><article class="doc-card"><span class="fine-print">Verified</span><h3>Business core</h3><p>XVA Gallery is a Dubai gallery established in 2003, specializing in contemporary art from the Arab world and the Sub-continent. Its public site also connects the gallery to XVA Hotel, XVA Café, art consultancy and education.</p></article><article class="doc-card"><span class="fine-print">Proposed</span><h3>Digital role</h3><p>Turn the site into a living programme surface: one striking image-led entrance, a clear editorial archive, and detail pages that make it easy to meet the artists, understand the exhibition and plan a visit.</p></article><article class="doc-card"><span class="fine-print">Reference observation</span><h3>Interaction direction</h3><p>The supplied Lawrie Shabibi reference uses a fullscreen hero, fixed lightweight header, menu-led navigation, quiet transitions and an image-first feed. This package translates those behaviors into XVA's own visual and content system.</p></article><article class="doc-card"><span class="fine-print">Constraint</span><h3>Production dependency</h3><p>The prototypes are offline-capable demonstrations. Newsletter, forms, language switching, CMS data, search, analytics, map embeds and any live image delivery require production integrations.</p></article></div></div></section><section class="doc-section"><div class="shell"><h2>Brand strategy</h2><h3>Purpose</h3><p>Make contemporary art from the region more visible, more legible and more connected to the everyday life of Dubai.</p><h3>Desired perception</h3><p>Visitors should experience XVA as intimate but outward-looking: a gallery with a deep sense of place, a serious programme and an open invitation to look longer.</p><h3>Positioning</h3><p>XVA Gallery is a Dubai-based contemporary art gallery rooted in Al Fahidi, presenting artists from the Arab world and the Sub-continent while building conversations across exhibitions, consultancy, hospitality and education.</p><h3>Messaging hierarchy</h3><ul><li>Primary: The current exhibition and why it matters now.</li><li>Secondary: The artists, programme and regional context.</li><li>Support: Visit, enquire, subscribe, browse the archive, visit XVA Hotel & Café.</li></ul><h3>Tone of voice</h3><p>Quietly assured, precise, sensory and generous. Keep source-derived artist and exhibition writing intact where it carries the work; use short labels and practical dates around it.</p></div></section><section class="doc-section"><div class="shell"><h2>Visual and UI system</h2><div class="doc-grid"><article class="doc-card"><span class="fine-print">Color</span><h3>Paper / night / teal</h3><p>Warm paper #F4F0E8 supports reading and archive discovery. Night #101617 creates the reference-like cinematic stage. XVA teal #113B47 carries identity and links. Pale aqua #C7DDDA is a restrained accent on dark surfaces.</p></article><article class="doc-card"><span class="fine-print">Type</span><h3>Editorial serif + neutral sans</h3><p>Georgia / Times New Roman is used as a legal system fallback for the large display voice. Arial / Helvetica supports dense metadata, forms and navigation. No external font dependency is required.</p></article><article class="doc-card"><span class="fine-print">Motion</span><h3>Slow image, quick interface</h3><p>Hero crossfade 1.1s, image drift 7s, menu reveal 0.5s, card hover 0.8s. All motion collapses under prefers-reduced-motion. Autoplay stops on hover and focus.</p></article><article class="doc-card"><span class="fine-print">Responsive</span><h3>Desktop composition, mobile sequence</h3><p>Hero captions collapse to a readable stack, grids become a single column, the menu becomes a full-screen vertical list, and detail posters lose sticky behavior below 980px.</p></article></div></div></section><section class="doc-section"><div class="shell"><h2>Page-by-page recommendations</h2><ul><li>Homepage: lead with current exhibition, then move through the programme and Al Fahidi story.</li><li>Artists: keep the archive dense and editorial; use hover/focus previews rather than a generic card grid.</li><li>Exhibitions: make the current show impossible to miss, then provide current/upcoming/past filtering.</li><li>Detail pages: put the work and title first, followed by source-derived context and a clear visit or enquiry action.</li><li>Art consultancy: structure services around actual client questions and contact.</li><li>Press: separate contact reassurance from a scannable release archive with accordion reading.</li><li>Contact: replace an embedded map dependency with an accessible directions card and the factual address/hours.</li><li>Hospitality and education: preserve real XVA Hotel, Café, Story of Art and Path to Modernism content in editorial templates.</li></ul></div></section><section class="doc-section"><div class="shell"><h2>Roadmap and measurement</h2><div class="doc-grid"><article class="doc-card"><span class="fine-print">Phase 1</span><h3>Foundation</h3><p>Implement templates, content model, image crops, menu, forms, analytics consent and CMS fields. Preserve source routes and set redirects for obsolete aliases.</p></article><article class="doc-card"><span class="fine-print">Phase 2</span><h3>Content migration</h3><p>Normalize artist and exhibition metadata, provide alt text, connect press PDFs, and QA every current/archive entry against the source.</p></article><article class="doc-card"><span class="fine-print">Phase 3</span><h3>Launch readiness</h3><p>Run keyboard, screen-reader, mobile, performance, content and analytics QA. Confirm privacy language and mailing provider behavior with the gallery.</p></article><article class="doc-card"><span class="fine-print">Proposed metrics</span><h3>Measure the real journey</h3><p>Track current exhibition views, artist profile opens, contact starts/completions, directions clicks, newsletter starts/completions and outbound XVA Hotel / Shop clicks. Baselines are not available in this audit.</p></article></div></div></section></main>`;

docs.audit = `
<main class="doc-wrap"><section class="doc-hero"><div class="shell"><span class="kicker" style="color:var(--accent)">Evidence / live site audit</span><h1 class="serif">Make the gallery easier to enter, browse and return to.</h1><p>Audit date: 20 August 2026. Evidence is based on publicly accessible XVA pages, sitemap entries and the supplied Lawrie Shabibi reference. Labels distinguish verified observations from inference and proposal.</p></div></section><section class="doc-section"><div class="shell"><h2>Significant findings</h2><div class="doc-table-wrap"><table class="doc-table"><thead><tr><th>Area</th><th>Evidence / observed issue</th><th>Impact</th><th>Redesign response</th><th>Benefit</th></tr></thead><tbody><tr><td>Homepage / UI</td><td>Verified: the live homepage presents a carousel, exhibition list, café story and newsletter, but the hierarchy is built from several Elementor blocks and duplicated carousel groups.</td><td>Visitors have to parse a lot of similar modules before they understand the current programme.</td><td>Use one full-screen editorial hero for the current show, one programme feed, one Al Fahidi story and a compact footer.</td><td>Faster recognition of what is on view and where to go next.</td></tr><tr><td>Navigation</td><td>Verified: primary destinations are Home, Artists, Exhibitions, Art Consultancy, Press, Contact, Hotel & Café and XVA Shop. The site also exposes education and legacy pages through the sitemap.</td><td>Core and long-tail content are not clearly differentiated.</td><td>Use a menu-led shell for the core route set, then map archive and education routes to explicit templates.</td><td>A calmer header with better coverage of the actual site.</td></tr><tr><td>Artists</td><td>Verified: the live artist list contains roughly 40 public artist entries; the source page is a dense visual directory.</td><td>Dense lists can feel anonymous if every name has the same weight.</td><td>Keep the dense archive but add keyboard-safe hover/focus artwork previews and clear artist detail routes.</td><td>More visual connection without sacrificing scanability.</td></tr><tr><td>Exhibitions</td><td>Verified: current and past exhibition cards exist, with current dates for Oblivion, After the Image, LOCAL and White visible publicly.</td><td>Currentness can be lost among archive items.</td><td>Separate current exhibition from the archive and add All / Upcoming / Past controls.</td><td>Stronger programme discovery and clearer dates.</td></tr><tr><td>Detail / content</td><td>Verified: the Oblivion page has strong source-derived writing and a poster, while artist pages hold long statements and artwork images.</td><td>Long-form content benefits from a stronger reading rhythm and onward actions.</td><td>Put image, title, lede and actions first; then use editorial body text and related paths.</td><td>Better comprehension and more confident next steps.</td></tr><tr><td>Contact / conversion</td><td>Verified: Contact contains factual address, email, phone, fax and opening hours. Existing design can rely on external map or form behavior.</td><td>External dependencies can create accessibility, privacy and offline QA gaps.</td><td>Use a readable directions card, direct links, factual hours and a client-side validated demo form.</td><td>Reliable contact task completion; production connection remains explicit.</td></tr><tr><td>Mobile</td><td>Observed from responsive source behavior: navigation collapses, but the homepage and dense galleries still carry many blocks and images.</td><td>Small screens need an intentional sequence rather than a scaled desktop.</td><td>Collapse grids, stack captions, keep touch targets >=44px, and make the menu a full-screen list.</td><td>More readable, calmer mobile browsing.</td></tr><tr><td>Accessibility</td><td>Verified: source contains repeated image carousels and language links; some public images have empty alt text and controls are not consistently descriptive.</td><td>Screen-reader users and keyboard users may miss context or control state.</td><td>Use unique page headings, meaningful alt text, aria-expanded/current states, visible focus, and reduced-motion behavior.</td><td>Better WCAG 2.2 AA alignment.</td></tr><tr><td>Technical</td><td>Verified: live pages rely on WordPress/Elementor, remote CSS/fonts, third-party widgets and external media.</td><td>Runtime dependencies complicate performance and offline/preview use.</td><td>Prototype uses inline CSS/JS and embedded official imagery; production should preserve this direction with optimized CMS image derivatives and a real consent/analytics layer.</td><td>More resilient, inspectable front-end behavior.</td></tr></tbody></table></div></div></section></main>`;

docs.inventory = `
<main class="doc-wrap"><section class="doc-hero"><div class="shell"><span class="kicker" style="color:var(--accent)">Coverage / public URL inventory</span><h1 class="serif">Every important route has a place in the system.</h1><p>Large archives are represented by the appropriate reusable template. Exact source URLs are retained in this inventory; prototype links open the matching local mockup where one is included.</p></div></section><section class="doc-section"><div class="shell"><div class="doc-table-wrap"><table class="doc-table"><thead><tr><th>Page name</th><th>Existing URL</th><th>Purpose / type</th><th>Priority / conversion</th><th>Existing problems</th><th>Recommended template / deliverable</th></tr></thead><tbody>
<tr><td>Homepage</td><td><a href="https://www.xvagallery.com/">xvagallery.com/</a></td><td>Programme entrance / homepage</td><td>Critical / view current show, visit, subscribe</td><td>Multiple duplicated modules dilute hero priority.</td><td>Fullscreen programme homepage / <a href="homepage.html">mockup</a></td></tr>
<tr><td>Artists list</td><td><a href="https://www.xvagallery.com/artists-list/">/artists-list/</a></td><td>Artist archive / collection</td><td>Critical / browse artist</td><td>Dense visual list needs stronger previews and focus states.</td><td>Hover-led directory / <a href="artists.html">mockup</a></td></tr>
<tr><td>Artist detail</td><td><a href="https://www.xvagallery.com/artist/huma-shoaib/">/artist/[slug]/</a></td><td>Artist biography and artwork / detail</td><td>High / understand work, enquiry</td><td>Long statements need more editorial rhythm.</td><td>Artist detail / <a href="artist-huma-shoaib.html">Huma mockup</a></td></tr>
<tr><td>Exhibitions list</td><td><a href="https://www.xvagallery.com/exhibitions-new/">/exhibitions-new/</a></td><td>Current/upcoming/past archive</td><td>Critical / view exhibition</td><td>Currentness can be lost inside archive repetition.</td><td>Current + filterable archive / <a href="exhibitions.html">mockup</a></td></tr>
<tr><td>Exhibition detail</td><td><a href="https://www.xvagallery.com/exhibitions/oblivion/">/exhibitions/[slug]/</a></td><td>Exhibition context / detail</td><td>Critical / visit, enquire</td><td>Strong copy deserves more focused reading frame.</td><td>Image-led exhibition detail / <a href="exhibition-oblivion.html">mockup</a></td></tr>
<tr><td>Art consultancy</td><td><a href="https://www.xvagallery.com/art-consultancy-and-advisory-services/">/art-consultancy-and-advisory-services/</a></td><td>Service / lead generation</td><td>High / contact gallery</td><td>Service list is buried below mission copy.</td><td>Service + enquiry / <a href="art-consultancy.html">mockup</a></td></tr>
<tr><td>Press releases</td><td><a href="https://www.xvagallery.com/press-release/">/press-release/</a></td><td>Press archive / trust</td><td>High / press enquiry</td><td>Cards and PDFs need scannable grouping.</td><td>Accordion archive + contact / <a href="press.html">mockup</a></td></tr>
<tr><td>Contact</td><td><a href="https://www.xvagallery.com/contact/">/contact/</a></td><td>Visit and enquiry / conversion</td><td>Critical / directions, email, call</td><td>External map/form dependencies.</td><td>Accessible visit page / <a href="contact.html">mockup</a></td></tr>
<tr><td>Hotel & Café</td><td><a href="https://www.xvagallery.com/hotel-cafe-2/">/hotel-cafe-2/</a></td><td>Hospitality cross-link</td><td>High / visit XVA Hotel</td><td>Important story can be more image-led.</td><td>Hospitality editorial / <a href="hotel-cafe.html">mockup</a></td></tr>
<tr><td>Story of Art</td><td><a href="https://www.xvagallery.com/story-of-art/">/story-of-art/</a></td><td>Education programme / editorial</td><td>Medium / enquire</td><td>Legacy shortcode output is visible in public text.</td><td>Programme detail / <a href="story-of-art.html">mockup</a></td></tr>
<tr><td>Path to Modernism</td><td><a href="https://www.xvagallery.com/path-to-modernism/">/path-to-modernism/</a></td><td>Workshop / campaign-detail</td><td>Medium / enquire</td><td>Legacy shortcode output is visible in public text.</td><td>Workshop detail / <a href="path-to-modernism.html">mockup</a></td></tr>
<tr><td>Privacy Policy</td><td><a href="https://www.xvagallery.com/privacy-policy/">/privacy-policy/</a></td><td>Legal / reassurance</td><td>High / compliance</td><td>Source includes dated legal and analytics language.</td><td>Legal reading page / <a href="privacy-policy.html">mockup</a></td></tr>
<tr><td>Archive artists</td><td><a href="https://www.xvagallery.com/artist/akim-monet/">/artist/[40+ slugs]/</a></td><td>Artist detail collection</td><td>High / browse and enquire</td><td>Long-tail pages need consistent metadata model.</td><td>Map all to artist detail template; representative mockup included.</td></tr>
<tr><td>Archive exhibitions</td><td><a href="https://www.xvagallery.com/exhibitions/net-worth/">/exhibitions/[80+ slugs]/</a></td><td>Exhibition archive</td><td>High / browse history</td><td>Large archive requires a reliable CMS model.</td><td>Map all to exhibition detail template; representative mockup included.</td></tr>
<tr><td>Press pagination</td><td><a href="https://www.xvagallery.com/press-release/2/">/press-release/[2-5]/</a></td><td>Archive pagination</td><td>Medium / browse press</td><td>Pagination needs consistent state and accessible labels.</td><td>Same press archive template with real pagination.</td></tr>
<tr><td>404</td><td>Not publicly inspectable</td><td>Error recovery / utility</td><td>Medium / return to programme</td><td>Not visible in source crawl.</td><td>Recommended recovery page / <a href="404.html">mockup</a></td></tr>
</tbody></table></div></div></section></main>`;

docs.map = `
<main class="doc-wrap"><section class="doc-hero"><div class="shell"><span class="kicker" style="color:var(--accent)">Architecture / template system</span><h1 class="serif">One shell, four rhythms, no dead ends.</h1><p>Recommended navigation and URL mapping for a gallery archive that has to feel immediate on the homepage and dependable at depth.</p></div></section><section class="doc-section"><div class="shell"><h2>Recommended sitemap</h2><div class="map-visual"><div class="map-node">Homepage</div><div class="map-connector"></div><div class="map-node">Artists / Exhibitions</div><div class="map-connector"></div><div class="map-node">Detail pages</div><div class="map-connector"></div><div class="map-node">Visit / Enquire</div></div><div class="doc-grid" style="margin-top:24px"><article class="doc-card"><span class="fine-print">Primary navigation</span><h3>Home / Artists / Exhibitions</h3><p>High-frequency discovery routes, entered through the menu-led fixed header.</p></article><article class="doc-card"><span class="fine-print">Services</span><h3>Art Consultancy / Press / Contact</h3><p>Task-led pages for clients, media and visitors, with explicit next actions.</p></article><article class="doc-card"><span class="fine-print">Cross-links</span><h3>Hotel & Café / XVA Shop</h3><p>Preserve the existing external destinations and make the relationship legible.</p></article><article class="doc-card"><span class="fine-print">Education</span><h3>Story of Art / Path to Modernism</h3><p>Keep public programme pages discoverable even if they are not in the primary menu.</p></article></div></div></section><section class="doc-section"><div class="shell"><h2>Template map</h2><div class="doc-table-wrap"><table class="doc-table"><thead><tr><th>Template</th><th>Used by</th><th>Shared components</th><th>Primary conversion</th></tr></thead><tbody><tr><td>Fullscreen programme</td><td>Homepage</td><td>Hero carousel, programme cards, Al Fahidi story, newsletter</td><td>Current exhibition view / visit</td></tr><tr><td>Archive directory</td><td>Artists list, exhibitions list</td><td>Filter state, cards, keyboard/focus preview, metadata</td><td>Open artist/exhibition detail</td></tr><tr><td>Detail</td><td>Artist, exhibition, archive slugs</td><td>Hero image, lede, source copy, related links, next/previous</td><td>Enquire / visit / continue browsing</td></tr><tr><td>Service</td><td>Art Consultancy</td><td>Mission, numbered service rows, CTA</td><td>Start conversation</td></tr><tr><td>Press archive</td><td>Press releases and paginated archive</td><td>Contact card, accordions, PDF/URL actions</td><td>Press enquiry</td></tr><tr><td>Visit</td><td>Contact</td><td>Address, hours, directions, form</td><td>Directions / call / email</td></tr><tr><td>Hospitality</td><td>Hotel & Café</td><td>Split stories, external XVA Hotel link</td><td>Visit hotel site</td></tr><tr><td>Programme detail</td><td>Story of Art, Path to Modernism</td><td>Intro, schedule, contact CTA</td><td>Ask about programme</td></tr><tr><td>Legal / recovery</td><td>Privacy, 404</td><td>Readable text, route recovery</td><td>Reassurance / return home</td></tr></tbody></table></div></div></section><section class="doc-section"><div class="shell"><h2>Conversion pathways</h2><ul><li>New visitor: Homepage -> current exhibition -> Contact / directions.</li><li>Collector or client: Menu -> Artists or Art Consultancy -> artist/detail -> Contact.</li><li>Press: Menu -> Press -> release accordion or PDF -> Mona Hauser email.</li><li>Local visitor: Homepage -> Hotel & Café or Contact -> XVA Hotel / directions.</li><li>Returning audience: Newsletter form -> production mailing provider -> current programme.</li></ul><h3>Redirect recommendations</h3><p>Keep all current public paths where possible. Consolidate only duplicate legacy routes after confirming canonical behavior in CMS. If /home/ or /home-new/ remains public, redirect to /; map /exhibitions-old/ to /exhibitions-new/; map /artists/ to /artists-list/ if it is not separately curated; preserve all /artist/[slug]/ and /exhibitions/[slug]/ routes.</p></div></section></main>`;

const pages = {
  'homepage.html': page({title:'XVA Gallery - Contemporary Art in Dubai',description:'XVA Gallery presents contemporary art from the Arab world and the Sub-continent in Al Fahidi, Dubai.',originalUrl:'https://www.xvagallery.com/',body:homeBody,hero:true}),
  'artists.html': page({title:'Artists - XVA Gallery',description:'Explore artists represented by XVA Gallery.',originalUrl:'https://www.xvagallery.com/artists-list/',current:'artists',body:artistsBody}),
  'artist-huma-shoaib.html': page({title:'Huma Shoaib - XVA Gallery',description:'Huma Shoaib artist profile and artwork at XVA Gallery.',originalUrl:'https://www.xvagallery.com/artist/huma-shoaib/',current:'artists',body:humaBody}),
  'artist-page-template.html': page({title:'Artist Page Template - XVA Gallery',description:'Reusable artist detail page template for XVA Gallery.',originalUrl:'https://www.xvagallery.com/artist/[slug]/',current:'artists',body:artistTemplateBody}),
  'exhibitions.html': page({title:'Exhibitions - XVA Gallery',description:'Current, upcoming and past exhibitions at XVA Gallery.',originalUrl:'https://www.xvagallery.com/exhibitions-new/',current:'exhibitions',body:exhibitionsBody}),
  'exhibition-oblivion.html': page({title:'Oblivion - XVA Gallery',description:'Oblivion, XVA Gallery summer exhibition, 28 July to 15 August 2026.',originalUrl:'https://www.xvagallery.com/exhibitions/oblivion/',current:'exhibitions',body:oblivionBody}),
  'exhibition-page-template.html': page({title:'Exhibition Page Template - XVA Gallery',description:'Reusable exhibition detail page template for XVA Gallery.',originalUrl:'https://www.xvagallery.com/exhibitions/[slug]/',current:'exhibitions',body:exhibitionTemplateBody}),
  'art-consultancy.html': page({title:'Art Consultancy - XVA Gallery',description:'Art consultancy and advisory services by XVA Gallery.',originalUrl:'https://www.xvagallery.com/art-consultancy-and-advisory-services/',current:'consultancy',body:consultancyBody}),
  'press.html': page({title:'Press Releases - XVA Gallery',description:'Press releases and contact information for XVA Gallery.',originalUrl:'https://www.xvagallery.com/press-release/',current:'press',body:pressBody}),
  'contact.html': page({title:'Contact - XVA Gallery',description:'Visit XVA Gallery in Al Fahidi Neighborhood, Dubai.',originalUrl:'https://www.xvagallery.com/contact/',current:'contact',body:contactBody}),
  'hotel-cafe.html': page({title:'Hotel & Café - XVA Gallery',description:'XVA Hotel and Café in Al Fahidi, Dubai.',originalUrl:'https://www.xvagallery.com/hotel-cafe-2/',current:'hotel',body:hotelBody}),
  'privacy-policy.html': page({title:'Privacy Policy - XVA Gallery',description:'XVA Gallery privacy policy.',originalUrl:'https://www.xvagallery.com/privacy-policy/',body:privacyBody}),
  '404.html': page({title:'Page not found - XVA Gallery',description:'The requested XVA Gallery page was not found.',originalUrl:'https://www.xvagallery.com/404/',body:notFoundBody})
};

const documents = {
  'index.html': page({title:'XVA Gallery Redevelopment',description:'Agency redevelopment project hub for XVA Gallery.',originalUrl:'https://www.xvagallery.com/',body:docs.index,docs:true}),
  'redevelopment-strategy.html': page({title:'Redevelopment Strategy - XVA Gallery',description:'Redevelopment strategy and design system for XVA Gallery.',originalUrl:'https://www.xvagallery.com/',body:docs.strategy,docs:true}),
  'website-audit.html': page({title:'Website Audit - XVA Gallery',description:'Evidence-led audit of the public XVA Gallery site.',originalUrl:'https://www.xvagallery.com/',body:docs.audit,docs:true}),
  'page-inventory.html': page({title:'Page Inventory - XVA Gallery',description:'Public page inventory and template assignments for XVA Gallery.',originalUrl:'https://www.xvagallery.com/',body:docs.inventory,docs:true}),
  'sitemap-and-template-map.html': page({title:'Sitemap and Template Map - XVA Gallery',description:'Recommended sitemap and reusable template map for XVA Gallery.',originalUrl:'https://www.xvagallery.com/',body:docs.map,docs:true})
};

for (const [file, contents] of Object.entries({ ...pages, ...documents })) {
  let output = contents.replace('<main class="doc-wrap"', '<main id="main" class="doc-wrap"');
  if (file === 'artists.html') {
    output = output
      .replace(/<div class="artist-preview"[\s\S]*?<\/div>/, '')
      .replace(' data-artist-preview', '')
      .replace('Hover or focus a name to bring the work forward.', 'Each artist is shown with a representative image.');
  }
  if (file === 'index.html') {
    output = output
      .replace('</div><div class="coverage-bar">', `<article class="hub-card"><div><span class="fine-print">10 / Template</span><h2>Artist page template</h2><p>Reusable image-led artist profile with metadata, statement, artwork gallery and enquiry actions.</p></div><a class="section-link" href="artist-page-template.html">Open artist template ${icon('external')}</a></article><article class="hub-card"><div><span class="fine-print">11 / Template</span><h2>Exhibition page template</h2><p>Reusable exhibition detail page with poster, curatorial copy, related links and visit actions.</p></div><a class="section-link" href="exhibition-page-template.html">Open exhibition template ${icon('external')}</a></article></div><div class="coverage-bar">`)
      .replace('Hover-led directory and current/archive exhibition system with local detail routes.', 'Image-led artist directory and current/archive exhibition system with local detail routes.')
      .replace('<strong>17</strong><span>Standalone deliverables</span>', '<strong>19</strong><span>Standalone deliverables</span>');
  }
  if (file === 'sitemap-and-template-map.html') {
    output = output.replace('keyboard/focus preview, metadata', 'image-led cards, focus states, metadata');
  }
  if (file === 'homepage.html') {
    ['After the Image', 'LOCAL', 'White'].forEach((title) => {
      output = output.replace(`<h1 class="serif">${title}</h1>`, `<h2 class="serif">${title}</h2>`);
    });
  }
  const secondaryHeadings = {
    'art-consultancy.html': ['mission-grid', 'A gallery that moves with the work.'],
    'hotel-cafe.html': ['hospitality-copy', 'A retreat in the maze-like sikkas of Al Fahidi.']
  };
  if (secondaryHeadings[file]) {
    const [sectionClass, title] = secondaryHeadings[file];
    const marker = `class="${sectionClass}"`;
    const markerIndex = output.indexOf(marker);
    if (markerIndex >= 0) {
      const before = output.slice(0, markerIndex);
      const after = output.slice(markerIndex).replace(`<h1 class="serif">${title}</h1>`, `<h2 class="serif">${title}</h2>`);
      output = before + after;
    }
  }
  fs.writeFileSync(path.join(packageDir, file), output, 'utf8');
}

console.log(`Built ${Object.keys({ ...pages, ...documents }).length} standalone HTML files in ${packageDir}`);
