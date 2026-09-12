<?php
$pageTitle = 'Read Online';
$pageDesc = 'Read the Telugu Sundarakanda and Hanuman Chalisa online, page by page, composed by Shri Sunderdas M.S. Rama Rao.';
$activeNav = 'downloads';
include __DIR__ . '/inc/header.php';

$pagesDir = __DIR__ . '/assets/flipbook/pages';
$pageFiles = glob($pagesDir . '/page-*.jpg');
sort($pageFiles);
$totalPages = count($pageFiles);
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <span>/</span> <a href="downloads.php">Downloads</a> <span>/</span> <span>Read Online</span></div>
    <p class="kicker" style="color:#f0c987">Devotional Resources</p>
    <h1>Sundarakanda &amp; Hanuman Chalisa</h1>
    <p class="lead">Turn through the Telugu devotional text page by page, composed by Shri Sunderdas M.S. Rama Rao.</p>
  </div>
</section>

<?php if ($totalPages === 0): ?>
<section>
  <div class="container">
    <div class="card medium center" style="margin:0 auto;max-width:520px">
      <p>The page images for the online reader are not available right now.</p>
      <a class="btn btn-primary" href="downloads.php">Back to Downloads</a>
    </div>
  </div>
</section>
<?php else: ?>
<section class="tight">
  <div class="container">
    <div class="reader">
      <button type="button" class="reader-nav reader-prev" aria-label="Previous page">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><polyline points="15 18 9 12 15 6"/></svg>
      </button>

      <div class="reader-frame">
        <img id="reader-image" src="assets/flipbook/pages/page-01.jpg" alt="Page 1 of <?= (int)$totalPages ?>">
      </div>

      <button type="button" class="reader-nav reader-next" aria-label="Next page">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>

    <div class="reader-controls">
      <label for="reader-jump">Page</label>
      <input type="number" id="reader-jump" min="1" max="<?= (int)$totalPages ?>" value="1" aria-label="Jump to page">
      <span>of <?= (int)$totalPages ?></span>
      <a class="btn btn-ghost btn-sm" href="downloads/hanuman-chalisa-telugu.pdf" download="Hanuman-Chalisa-Sundarakanda-Telugu.pdf" style="margin-left:auto">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Download full PDF
      </a>
    </div>
  </div>
</section>
<script>
(function () {
  var total = <?= (int)$totalPages ?>;
  var img = document.getElementById('reader-image');
  var jump = document.getElementById('reader-jump');
  var prev = document.querySelector('.reader-prev');
  var next = document.querySelector('.reader-next');
  var current = 1;

  function pad(n) { return n < 10 ? '0' + n : '' + n; }

  function preload(n) {
    if (n < 1 || n > total) return;
    var im = new Image();
    im.src = 'assets/flipbook/pages/page-' + pad(n) + '.jpg';
  }

  function show(n) {
    n = Math.max(1, Math.min(total, n));
    current = n;
    img.src = 'assets/flipbook/pages/page-' + pad(n) + '.jpg';
    img.alt = 'Page ' + n + ' of ' + total;
    jump.value = n;
    prev.disabled = (n === 1);
    next.disabled = (n === total);
    preload(n + 1);
    preload(n - 1);
  }

  prev.addEventListener('click', function () { show(current - 1); });
  next.addEventListener('click', function () { show(current + 1); });
  jump.addEventListener('change', function () { show(parseInt(jump.value, 10) || 1); });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowLeft') show(current - 1);
    if (e.key === 'ArrowRight') show(current + 1);
  });

  show(1);
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/inc/footer.php'; ?>
