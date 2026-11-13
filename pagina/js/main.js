/* ============================================================
   Public site + shared demo interactions
   ============================================================ */
(function () {
  'use strict';

  /* Hydrate decorative icons declared in the HTML */
  if (window.AppIcon) {
    document.querySelectorAll('[data-icon]').forEach(function (element) {
      element.innerHTML = window.AppIcon(element.getAttribute('data-icon'));
    });
  }

  var header = document.getElementById('siteHeader');
  var mobileToggle = document.getElementById('mobileToggle');
  var mobileMenu = document.getElementById('mobileMenu');

  function onScroll() {
    if (header) {
      header.classList.toggle('is-scrolled', window.scrollY > 12);
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', function () {
      var open = mobileMenu.classList.toggle('is-open');
      mobileToggle.setAttribute('aria-expanded', String(open));
      mobileToggle.innerHTML = open
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>';
    });

    mobileMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        mobileMenu.classList.remove('is-open');
        mobileToggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* Reveal on scroll */
  var revealItems = document.querySelectorAll('.reveal');
  if (revealItems.length && 'IntersectionObserver' in window) {
    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    revealItems.forEach(function (item) { revealObserver.observe(item); });
  } else {
    revealItems.forEach(function (item) { item.classList.add('is-visible'); });
  }

  /* Login: show / hide password */
  var passwordToggle = document.getElementById('passwordToggle');
  var passwordInput = document.getElementById('password');
  if (passwordToggle && passwordInput) {
    passwordToggle.addEventListener('click', function () {
      var show = passwordInput.type === 'password';
      passwordInput.type = show ? 'text' : 'password';
      passwordToggle.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
      passwordToggle.innerHTML = show
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.9 8.3 2 12 2 12s3.5 7 10 7c1.9 0 3.6-.6 5-1.4"/><path d="M10.6 5.2A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a17.8 17.8 0 0 1-2.4 3.4"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    });
  }

  /* Login: fill the form with a test account (does not block submission) */
  document.querySelectorAll('[data-demo-email]').forEach(function (link) {
    link.addEventListener('click', function (event) {
      event.preventDefault();
      var email = document.getElementById('email');
      var password = document.getElementById('password');

      if (email) {
        email.value = link.getAttribute('data-demo-email') || '';
      }
      if (password) {
        password.value = link.getAttribute('data-demo-password') || '';
      }
      if (email) {
        email.focus();
      }
    });
  });

  /* Demo toasts */
  document.querySelectorAll('[data-toast]').forEach(function (element) {
    element.addEventListener('click', function (event) {
      if (element.tagName === 'A' && element.getAttribute('href') === '#') {
        event.preventDefault();
      }
      if (window.showToast) window.showToast(element.getAttribute('data-toast'));
    });
  });

  /* Visual-only filter chips */
  document.querySelectorAll('[data-chip-group]').forEach(function (group) {
    group.querySelectorAll('.chip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        group.querySelectorAll('.chip').forEach(function (c) { c.classList.remove('is-active'); });
        chip.classList.add('is-active');
      });
    });
  });

  /* Visual-only segmented attendance controls */
  document.querySelectorAll('.seg').forEach(function (seg) {
    seg.querySelectorAll('button').forEach(function (button) {
      button.addEventListener('click', function () {
        seg.querySelectorAll('button').forEach(function (b) {
          b.classList.remove('is-on', 'good', 'bad', 'warn');
        });
        button.classList.add('is-on');
        if (button.dataset.tone) button.classList.add(button.dataset.tone);
      });
    });
  });

  /* Modal open / close */
  function openModal(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    var first = modal.querySelector('input, select, textarea, button');
    if (first) first.focus();
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('[data-modal-open]').forEach(function (trigger) {
    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      openModal(trigger.getAttribute('data-modal-open'));
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      closeModal(trigger.closest('.modal-backdrop'));
    });
  });

  document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
    backdrop.addEventListener('click', function (event) {
      if (event.target === backdrop) closeModal(backdrop);
    });

    var form = backdrop.querySelector('form');
    if (form && !form.hasAttribute('data-real-form')) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        closeModal(backdrop);
        if (window.showToast) {
          window.showToast(backdrop.getAttribute('data-success') || 'Vista previa: registro guardado (demo).');
        }
      });
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      document.querySelectorAll('.modal-backdrop.is-open').forEach(closeModal);
    }
  });

  /* Current year in footers */
  document.querySelectorAll('[data-year]').forEach(function (element) {
    element.textContent = new Date().getFullYear();
  });

  /* Demo contact form */
  var contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', function (event) {
      event.preventDefault();
      contactForm.reset();
      if (window.showToast) {
        window.showToast('¡Gracias! Te contactaremos en menos de 24 horas.');
      }
    });
  }
})();
