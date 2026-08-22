(function () {
  'use strict';

  var body = document.body;
  var toggle = document.querySelector('[data-xva-menu-toggle]');
  var drawer = document.querySelector('[data-xva-menu-drawer]');
  var close = document.querySelector('[data-xva-menu-close]');
  var lastFocus = null;
  var header = document.querySelector('[data-xva-header]');

  function syncHeader() {
    if (!header) return;
    header.classList.toggle('is-scrolled', window.scrollY > 40);
  }

  if (header) {
    syncHeader();
    window.addEventListener('scroll', syncHeader, { passive: true });
  }

  function setMenu(open) {
    if (!toggle || !drawer) return;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    drawer.hidden = !open;
    body.classList.toggle('xva-menu-is-open', open);
    if (open) {
      lastFocus = document.activeElement;
      var first = drawer.querySelector('a, button');
      if (first) first.focus();
    } else if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus();
    }
  }

  if (toggle && drawer) {
    toggle.addEventListener('click', function () { setMenu(toggle.getAttribute('aria-expanded') !== 'true'); });
    if (close) close.addEventListener('click', function () { setMenu(false); });
    drawer.addEventListener('click', function (event) { if (event.target.closest('a')) setMenu(false); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') setMenu(false); });
  }

  function applyLanguage(value, persist) {
    var language = value === 'ar' ? 'ar' : 'en';
    document.querySelectorAll('[data-xva-language]').forEach(function (item) {
      item.setAttribute('aria-current', item.getAttribute('data-xva-language') === language ? 'true' : 'false');
    });
    document.documentElement.setAttribute('lang', language);
    document.documentElement.setAttribute('dir', language === 'ar' ? 'rtl' : 'ltr');
    if (persist) {
      try {
        window.localStorage.setItem('xva-language', language);
      } catch (error) {
        // Storage can be unavailable in privacy-restricted browsers.
      }
    }
  }

  var initialLanguage = document.documentElement.getAttribute('lang') === 'ar' ? 'ar' : 'en';
  try {
    var savedLanguage = window.localStorage.getItem('xva-language');
    if (savedLanguage === 'ar' || savedLanguage === 'en') initialLanguage = savedLanguage;
  } catch (error) {
    // Continue with the WordPress document language.
  }
  applyLanguage(initialLanguage, false);

  document.querySelectorAll('[data-xva-language]').forEach(function (link) {
    link.addEventListener('click', function (event) {
      applyLanguage(link.getAttribute('data-xva-language'), true);
      var targetLink = event.target && event.target.closest ? event.target.closest('a') : null;
      var href = link.getAttribute('href');
      if (!targetLink && (!href || href === '#')) event.preventDefault();
    });
  });

  document.querySelectorAll('[data-xva-artist-nav]').forEach(function (nav) {
    var links = Array.prototype.slice.call(nav.querySelectorAll('a[href^="#"]'));
    var sections = links.map(function (link) { return document.querySelector(link.getAttribute('href')); }).filter(Boolean);
    if (!sections.length || !('IntersectionObserver' in window)) return;
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        links.forEach(function (link) { link.setAttribute('aria-current', link.getAttribute('href') === '#' + entry.target.id ? 'true' : 'false'); });
      });
    }, { rootMargin: '-18% 0px -68% 0px', threshold: 0 });
    sections.forEach(function (section) { observer.observe(section); });
  });

  document.querySelectorAll('[data-xva-accordion-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
      var panel = document.getElementById(button.getAttribute('aria-controls'));
      var expanded = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      if (panel) panel.hidden = expanded;
    });
  });

  document.querySelectorAll('.xva-release-toggle').forEach(function (button) {
    button.addEventListener('click', function () {
      var release = button.closest('.xva-release');
      var expanded = button.getAttribute('aria-expanded') === 'true';
      if (!release) return;
      button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      button.textContent = expanded ? '+' : '–';
      release.classList.toggle('is-open', !expanded);
    });
  });

  document.querySelectorAll('[data-xva-newsletter-form], [data-xva-contact-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var status = form.querySelector('[data-xva-form-status]');
      if (!status) {
        status = document.createElement('div');
        status.setAttribute('data-xva-form-status', 'true');
        status.setAttribute('aria-live', 'polite');
        form.appendChild(status);
      }
      status.textContent = 'Thank you. This staging form is ready to connect to the gallery inbox or mailing service.';
    });
  });

  document.querySelectorAll('[data-xva-filter-group]').forEach(function (group) {
    var tabs = group.querySelectorAll('[data-xva-filter]');
    function cardMatchesFilter(card, value) {
      return value === 'all' || card.getAttribute('data-xva-filter-card') === value;
    }
    function syncTabs(value) {
      tabs.forEach(function (item) {
        var isActive = item.getAttribute('data-xva-filter') === value;
        item.classList.toggle('is-active', isActive);
        item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }
    function applyFilter(value) {
      group.setAttribute('data-xva-active-filter', value);
      var cards = document.querySelectorAll('[data-xva-filter-card]');
      cards.forEach(function (card) { card.classList.toggle('is-hidden', !cardMatchesFilter(card, value)); });
      var currentView = document.querySelector('[data-xva-exhibition-current-view]');
      var listingView = document.querySelector('[data-xva-exhibition-listing]');
      if (currentView) currentView.hidden = value !== 'current';
      if (listingView) listingView.hidden = value === 'current';
      syncTabs(value);
    }
    applyFilter(group.getAttribute('data-xva-filter-default') || 'all');
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var value = tab.getAttribute('data-xva-filter');
        applyFilter(value);
        document.dispatchEvent(new CustomEvent('xva:exhibition-filter-change', { detail: { value: value } }));
      });
    });
  });

  document.querySelectorAll('[data-xva-exhibition-archive]').forEach(function (grid) {
    var shell = grid.parentElement;
    var loader = shell && shell.querySelector('[data-xva-exhibition-loader]');
    if (!loader) return;

    var filterGroup = document.querySelector('[data-xva-filter-group]');
    var button = loader.querySelector('[data-xva-exhibition-load]');
    var status = loader.querySelector('[data-xva-exhibition-status]');
    var endpoint = grid.getAttribute('data-xva-endpoint');
    var offset = parseInt(grid.getAttribute('data-xva-next-offset') || '0', 10);
    var total = parseInt(grid.getAttribute('data-xva-total') || '0', 10);
    var perPage = parseInt(grid.getAttribute('data-xva-per-page') || '12', 10);
    var activeFilter = filterGroup ? filterGroup.getAttribute('data-xva-active-filter') || 'all' : 'all';
    var loading = false;
    var observer = null;

    function applyCurrentFilter() {
      grid.querySelectorAll('[data-xva-filter-card]').forEach(function (card) {
        card.classList.toggle('is-hidden', activeFilter !== 'all' && card.getAttribute('data-xva-filter-card') !== activeFilter);
      });
    }

    function updateLoader(hasMore) {
      loader.hidden = !hasMore;
      if (button) button.hidden = false;
    }

    function loadMore() {
      if (loading || !endpoint || ( total > 0 && offset >= total )) return Promise.resolve();
      loading = true;
      if (button) button.disabled = true;
      loader.classList.add('is-loading');
      if (status) status.textContent = 'Loading more exhibitions…';

      var url = new URL(endpoint, window.location.href);
      url.searchParams.set('offset', String(offset));
      url.searchParams.set('per_page', String(perPage));
      if (activeFilter === 'past' || activeFilter === 'archive') url.searchParams.set('status', activeFilter);
      return fetch(url.toString(), { credentials: 'same-origin' })
        .then(function (response) {
          if (!response.ok) throw new Error('Exhibition archive request failed');
          return response.json();
        })
        .then(function (payload) {
          if (payload.html) grid.insertAdjacentHTML('beforeend', payload.html);
          offset = parseInt(payload.next_offset || offset, 10);
          if (typeof payload.total !== 'undefined') total = parseInt(payload.total, 10);
          grid.setAttribute('data-xva-next-offset', String(offset));
          grid.setAttribute('data-xva-total', String(total));
          applyCurrentFilter();
          if (!payload.has_more || offset >= total) {
            if (status) status.textContent = '';
            updateLoader(false);
          } else if (status) {
            status.textContent = '';
            updateLoader(true);
          }
        })
        .catch(function () {
          if (status) status.textContent = 'Unable to load more exhibitions. Use the button to retry.';
          if (button) button.hidden = false;
        })
        .finally(function () {
          loading = false;
          if (button) button.disabled = false;
          loader.classList.remove('is-loading');
        });
    }

    function resetForFilter(value) {
      activeFilter = value;
      offset = 0;
      total = 0;
      grid.innerHTML = '';
      grid.setAttribute('data-xva-next-offset', '0');
      grid.setAttribute('data-xva-total', '0');
      if (status) status.textContent = '';
      updateLoader(true);
      return loadMore();
    }

    document.addEventListener('xva:exhibition-filter-change', function (event) {
      var value = event.detail && event.detail.value ? event.detail.value : 'all';
      if (value === activeFilter) return;
      if (value === 'current') {
        activeFilter = value;
        grid.innerHTML = '';
        offset = 0;
        total = 0;
        updateLoader(false);
        return;
      }
      resetForFilter(value);
    });

    if (button) button.addEventListener('click', loadMore);
    if ('IntersectionObserver' in window) {
      observer = new IntersectionObserver(function (entries) {
        if (entries.some(function (entry) { return entry.isIntersecting; })) loadMore();
      }, { rootMargin: '0px 0px 700px 0px', threshold: 0 });
      observer.observe(loader);
    }

    if (activeFilter === 'past' || activeFilter === 'archive') {
      resetForFilter(activeFilter);
    } else if (activeFilter === 'current') {
      updateLoader(false);
    }
  });

  // Elementor page imports keep the hero media as regular shortcode widgets.
  // Hydrate that structure into the same carousel contract used by the
  // standalone mockups, so the imported page behaves like the designed hero.
  document.querySelectorAll('.xva-hero').forEach(function (hero) {
    var inner = hero.querySelector('.e-con-inner');
    if (!inner) return;

    var media = Array.prototype.filter.call(inner.children, function (child) {
      return child.classList.contains('elementor-widget-shortcode') && child.querySelector('img');
    });
    if (!media.length) return;

    hero.setAttribute('data-xva-carousel', 'true');
    media.forEach(function (slide) {
      slide.classList.add('xva-hero-slide');
      slide.setAttribute('data-xva-slide', 'true');
    });

    var caption = Array.prototype.find.call(inner.children, function (child) {
      return child.classList.contains('elementor-widget-text-editor') && child.querySelector('h1, h2');
    });
    if (caption) caption.classList.add('xva-hero-caption');

    var controls = inner.querySelector('.xva-hero-controls');
    if (!controls) return;
    controls.querySelectorAll('.xva-carousel-dot').forEach(function (dot) {
      dot.setAttribute('data-xva-carousel-dot', 'true');
    });
    var buttons = controls.querySelectorAll('.xva-carousel-button');
    if (buttons[0]) buttons[0].setAttribute('data-xva-carousel-prev', 'true');
    if (buttons[1]) buttons[1].setAttribute('data-xva-carousel-next', 'true');
  });

  document.querySelectorAll('[data-xva-carousel]').forEach(function (carousel) {
    var slides = carousel.querySelectorAll('[data-xva-slide]');
    var dots = carousel.querySelectorAll('[data-xva-carousel-dot]');
    var current = 0;
    var timer;
    function show(index) {
      if (!slides.length) return;
      current = (index + slides.length) % slides.length;
      slides.forEach(function (slide, i) {
        var active = i === current;
        slide.classList.toggle('is-active', active);
        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
      });
      dots.forEach(function (dot, i) { dot.classList.toggle('is-active', i === current); dot.setAttribute('aria-current', i === current ? 'true' : 'false'); });
    }
    function restart() { window.clearInterval(timer); timer = window.setInterval(function () { show(current + 1); }, 7000); }
    carousel.querySelectorAll('[data-xva-carousel-prev]').forEach(function (button) { button.addEventListener('click', function () { show(current - 1); restart(); }); });
    carousel.querySelectorAll('[data-xva-carousel-next]').forEach(function (button) { button.addEventListener('click', function () { show(current + 1); restart(); }); });
    dots.forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); restart(); }); });
    show(0);
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) restart();
  });

  document.querySelectorAll('[data-xva-artist-preview]').forEach(function (grid) {
    var overlay = grid.parentElement && grid.parentElement.querySelector('[data-xva-preview-overlay]');
    if (!overlay) return;
    var image = overlay.querySelector('img');
    var previewItems = overlay.querySelectorAll('[data-xva-preview-item]');
    var timer;
    grid.querySelectorAll('[data-xva-artist-card]').forEach(function (card) {
      function show() {
        var src = card.getAttribute('data-xva-image');
        if (!src || window.innerWidth < 721) return;
        window.clearTimeout(timer);
        image.src = src;
        image.alt = card.getAttribute('data-xva-name') || '';
        previewItems.forEach(function (item) { item.classList.toggle('is-active', item.getAttribute('data-xva-preview-index') === card.getAttribute('data-xva-index')); });
        overlay.classList.add('is-visible');
        overlay.setAttribute('aria-hidden', 'false');
      }
      function hide() { timer = window.setTimeout(function () { overlay.classList.remove('is-visible'); overlay.setAttribute('aria-hidden', 'true'); previewItems.forEach(function (item) { item.classList.remove('is-active'); }); }, 120); }
      card.addEventListener('mouseenter', show);
      card.addEventListener('mouseleave', hide);
      card.addEventListener('focusin', show);
      card.addEventListener('focusout', hide);
    });
  });
}());
