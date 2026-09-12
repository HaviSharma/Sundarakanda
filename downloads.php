<?php
$pageTitle = 'Downloads';
$pageDesc = 'Download the Sundarakanda and Hanuman Chalisa devotional texts — Telugu script and Roman transliteration — for reading and daily recitation.';
$activeNav = 'downloads';
include __DIR__ . '/inc/header.php';

$teluguPdf = __DIR__ . '/downloads/hanuman-chalisa-telugu.pdf';
$teluguSize = file_exists($teluguPdf) ? round(filesize($teluguPdf) / 1024 / 1024, 1) . ' MB' : '';

$englishPdf = __DIR__ . '/downloads/hanuman-chalisa-english-transliteration.pdf';
$englishSize = file_exists($englishPdf) ? round(filesize($englishPdf) / 1024, 0) . ' KB' : '';
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <span>/</span> <span>Downloads</span></div>
    <p class="kicker" style="color:#f0c987">Devotional Resources</p>
    <h1>Downloads</h1>
    <p class="lead">Save the Sundarakanda and Hanuman Chalisa devotional text to your phone or computer, so it's with you for reading and daily recitation — with or without a connection.</p>
  </div>
</section>

<section>
  <div class="container">
    <div class="grid grid-2">
      <div class="donate-card">
        <img src="img/downloads/telugu-icon.png" alt="" width="64" height="64" style="margin-bottom:14px;border-radius:14px">
        <span class="pill" style="align-self:flex-start;margin-bottom:10px">తెలుగు</span>
        <h3>Hanuman Chalisa &amp; Sundarakanda &mdash; Telugu</h3>
        <p class="muted">The devotional text in Telugu script for reading and daily recitation.</p>
        <p class="muted" style="font-size:.82rem;margin-top:-6px">PDF<?= $teluguSize ? ' &middot; ' . e($teluguSize) : '' ?> &middot; 51 pages</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:auto">
          <a class="btn btn-primary" href="downloads/hanuman-chalisa-telugu.pdf" download="Hanuman-Chalisa-Sundarakanda-Telugu.pdf">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Download Telugu PDF</span>
          </a>
          <a class="btn btn-ghost" href="downloads/hanuman-chalisa-telugu.pdf" target="_blank" rel="noopener">Preview PDF</a>
        </div>
        <p style="margin-top:12px"><a href="sundarakanda.php" style="font-size:.88rem;font-weight:700;color:var(--saffron-600)">Read online, page by page &rarr;</a></p>
      </div>

      <div class="donate-card">
        <img src="img/downloads/english-icon.png" alt="" width="64" height="64" style="margin-bottom:14px;border-radius:14px">
        <span class="pill" style="align-self:flex-start;margin-bottom:10px">English</span>
        <h3>Hanuman Chalisa &mdash; English Transliteration</h3>
        <p class="muted">Roman-script transliteration of the Hanuman Chalisa, for readers who do not read Telugu script &mdash; a recitation aid, not a translation of the meaning.</p>
        <p class="muted" style="font-size:.82rem;margin-top:-6px">PDF<?= $englishSize ? ' &middot; ' . e($englishSize) : '' ?> &middot; 5 pages</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:auto">
          <a class="btn btn-primary" href="downloads/hanuman-chalisa-english-transliteration.pdf" download="Hanuman-Chalisa-English-Transliteration.pdf">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Download English PDF</span>
          </a>
          <a class="btn btn-ghost" href="downloads/hanuman-chalisa-english-transliteration.pdf" target="_blank" rel="noopener">Preview PDF</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-cream" style="border-top:1px solid var(--border-light)">
  <div class="container" style="padding:48px 0">
    <div class="section-head center" style="margin-bottom:36px">
      <h3 style="font-size:1rem;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;color:var(--text-muted);margin:0">Technology Partner</h3>
      <p style="font-size:1.15rem;font-weight:600;margin:8px 0 0 0"><a href="https://druthion.com" target="_blank" rel="noopener" style="color:inherit">Managed by Druthion Technology Services LLC</a></p>
    </div>
    <div class="grid grid-3" style="gap:28px">
      <a href="https://druthion.com" target="_blank" rel="noopener" style="text-align:center;text-decoration:none;color:inherit;transition:opacity 0.2s">
        <img src="img/partners/druthiai.png" alt="Dhruti.ai" style="height:64px;width:auto;object-fit:contain;margin:0 auto 16px;display:block">
        <h4 style="font-size:.95rem;margin:0 0 4px 0">Dhruti.ai</h4>
        <p style="font-size:.85rem;color:var(--text-muted);margin:0">AI-powered workflow automation</p>
      </a>
      <a href="https://druthion.com" target="_blank" rel="noopener" style="text-align:center;text-decoration:none;color:inherit;transition:opacity 0.2s">
        <img src="img/partners/mfinai.png" alt="mFin" style="height:64px;width:auto;object-fit:contain;margin:0 auto 16px;display:block">
        <h4 style="font-size:.95rem;margin:0 0 4px 0">mFin</h4>
        <p style="font-size:.85rem;color:var(--text-muted);margin:0">Financial management platform</p>
      </a>
      <a href="https://druthion.com" target="_blank" rel="noopener" style="text-align:center;text-decoration:none;color:inherit;transition:opacity 0.2s">
        <img src="img/partners/wfo.png" alt="Workforce Operations" style="height:64px;width:auto;object-fit:contain;margin:0 auto 16px;display:block">
        <h4 style="font-size:.95rem;margin:0 0 4px 0">Workforce Operations</h4>
        <p style="font-size:.85rem;color:var(--text-muted);margin:0">Team coordination and analytics</p>
      </a>
    </div>
    <p style="text-align:center;font-size:.8rem;color:var(--text-muted);margin-top:32px">
      <strong>Note:</strong> Placeholder descriptions pending final copy from Druthion. <a href="https://druthion.com" target="_blank" rel="noopener">Visit Druthion &rarr;</a>
    </p>
  </div>
</section>

<?php include __DIR__ . '/inc/footer.php'; ?>
