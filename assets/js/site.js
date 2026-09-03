// Sundarakanda — site interactions (vanilla JS, no dependencies)
document.addEventListener('DOMContentLoaded', function () {

  /* Mobile nav toggle */
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.querySelector('.main-nav');
  var scrim = document.querySelector('.nav-scrim');

  function closeNav() {
    nav && nav.classList.remove('open');
    scrim && scrim.classList.remove('open');
    toggle && toggle.setAttribute('aria-expanded', 'false');
  }
  function openNav() {
    nav && nav.classList.add('open');
    scrim && scrim.classList.add('open');
    toggle && toggle.setAttribute('aria-expanded', 'true');
  }
  if (toggle) {
    toggle.addEventListener('click', function () {
      nav.classList.contains('open') ? closeNav() : openNav();
    });
  }
  if (scrim) scrim.addEventListener('click', closeNav);
  var navClose = document.querySelector('.nav-close');
  if (navClose) navClose.addEventListener('click', closeNav);

  // Mobile dropdown expand (About)
  document.querySelectorAll('.has-dropdown > a.nav-link').forEach(function (link) {
    link.addEventListener('click', function (e) {
      if (window.innerWidth <= 960) {
        e.preventDefault();
        link.parentElement.classList.toggle('open');
      }
    });
  });

  // Close mobile nav when a real link is followed
  document.querySelectorAll('.main-nav a.nav-link:not(.has-dropdown > a)').forEach(function (l) {
    l.addEventListener('click', closeNav);
  });
  document.querySelectorAll('.dropdown a').forEach(function (l) {
    l.addEventListener('click', closeNav);
  });

  /* Photo gallery filter */
  var filterBtns = document.querySelectorAll('.filter-btn');
  var groups = document.querySelectorAll('[data-gallery-group]');
  if (filterBtns.length) {
    filterBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        filterBtns.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var val = btn.getAttribute('data-filter');
        groups.forEach(function (g) {
          var show = val === 'all' || g.getAttribute('data-gallery-group') === val;
          g.style.display = show ? '' : 'none';
        });
      });
    });
  }

  /* Image lightbox */
  var lightbox = document.getElementById('lightbox');
  var lightboxImg = lightbox ? lightbox.querySelector('img') : null;
  document.querySelectorAll('[data-lightbox]').forEach(function (item) {
    item.addEventListener('click', function () {
      if (!lightbox) return;
      lightboxImg.src = item.getAttribute('data-lightbox');
      lightboxImg.alt = item.getAttribute('data-caption') || '';
      if (typeof lightbox.showModal === 'function') lightbox.showModal();
    });
  });
  if (lightbox) {
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) lightbox.close();
    });
    lightbox.querySelectorAll('.lightbox-close').forEach(function (b) {
      b.addEventListener('click', function () { lightbox.close(); });
    });
    lightbox.addEventListener('close', function () { lightboxImg.src = ''; });
  }

  /* Video lightbox */
  var videoBox = document.getElementById('video-lightbox');
  var videoFrame = videoBox ? videoBox.querySelector('iframe') : null;
  document.querySelectorAll('[data-video-id]').forEach(function (card) {
    card.addEventListener('click', function () {
      if (!videoBox) return;
      var id = card.getAttribute('data-video-id');
      videoFrame.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1&rel=0';
      if (typeof videoBox.showModal === 'function') videoBox.showModal();
    });
  });
  if (videoBox) {
    videoBox.addEventListener('click', function (e) {
      if (e.target === videoBox) videoBox.close();
    });
    videoBox.querySelectorAll('.lightbox-close').forEach(function (b) {
      b.addEventListener('click', function () { videoBox.close(); });
    });
    videoBox.addEventListener('close', function () { videoFrame.src = ''; });
  }

  /* Current year in footer */
  document.querySelectorAll('.js-year').forEach(function (el) {
    el.textContent = new Date().getFullYear();
  });
});
