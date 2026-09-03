<?php
require_once __DIR__ . '/includes/bootstrap.php';

$campaign = get_campaign('one-crore-parayanam');
if (!$campaign) {
    http_response_code(404);
    die('Campaign not found. Run sql/migrations/002_campaign_platform.sql to seed it.');
}
$stats = campaign_stats((int)$campaign['id']);
$recent = recent_participation((int)$campaign['id'], 8);
$target = (int)$campaign['target_count'];
$completed = $stats['completed'];
$remaining = max(0, $target - $completed);
$percent = $target > 0 ? min(100, round(($completed / $target) * 100, 2)) : 0;
$user = current_user();

$pageTitle = '1 Crore Hanuman Jayanti Parayanam';
$activeNav = 'campaign';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Portal</a> <span>/</span> <span>1 Crore Parayanam</span></div>
    <p class="kicker" style="color:#f0c987">A Collective Prayer for Universal Peace</p>
    <h1>1 Crore Hanuman Jayanti Parayanam</h1>
    <p class="lead">Together, our community is offering 1,00,00,000 Parayanams for universal peace and wellbeing. Every recitation you record joins one living, growing total.</p>
    <div class="hero-actions" style="margin-top:24px">
      <?php if ($user): ?>
        <a class="btn btn-primary" href="add-participation.php">Add My Parayanam</a>
        <a class="btn btn-outline" href="my-participation.php">My Contribution</a>
      <?php else: ?>
        <a class="btn btn-primary" href="signup.php?redirect=add-participation.php">Participate Now</a>
        <a class="btn btn-outline" href="login.php?redirect=add-participation.php">Sign In</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="tight">
  <div class="container">
    <div class="card" style="padding:36px" data-campaign-total="<?= (int)$completed ?>" data-campaign-target="<?= (int)$target ?>">
      <div style="display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:12px;margin-bottom:14px">
        <div>
          <span class="js-count-up" style="font-family:var(--font-display);font-size:clamp(2rem,5vw,3rem);color:var(--maroon-800);font-weight:700" data-target="<?= (int)$completed ?>"><?= number_format($completed) ?></span>
          <span class="muted"> / <?= number_format($target) ?></span>
        </div>
        <div class="pill pill-active" style="font-size:1rem;padding:8px 16px"><?= e((string)$percent) ?>% complete</div>
      </div>
      <div style="background:var(--cream-300);border-radius:999px;height:16px;overflow:hidden">
        <div style="background:linear-gradient(90deg,var(--saffron-600),var(--gold-500));height:100%;width:<?= e((string)$percent) ?>%;transition:width 1s ease"></div>
      </div>
      <div class="stat-tiles" style="margin-top:28px;margin-bottom:0">
        <div class="stat-tile"><b><?= number_format($remaining) ?></b><span>Remaining to goal</span></div>
        <div class="stat-tile"><b><?= number_format($stats['participants']) ?></b><span>Total participants</span></div>
        <div class="stat-tile"><b><?= number_format($stats['today']) ?></b><span>Today's Parayanams</span></div>
        <div class="stat-tile"><b><?= number_format($stats['week']) ?></b><span>This week</span></div>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap">
      <button class="btn btn-ghost btn-sm" id="share-whatsapp" type="button"><svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.2-.7.8-.8 1-.2.2-.3.2-.5.1a6.7 6.7 0 0 1-2-1.2 7.4 7.4 0 0 1-1.4-1.7c-.1-.2 0-.4.1-.5l.4-.4.2-.4v-.4c0-.1-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.2-.9.9-.9 2.2s1 2.6 1.1 2.7c.1.2 2 3 4.7 4.2.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.6-.3Z"/></svg> WhatsApp</button>
      <button class="btn btn-ghost btn-sm" id="share-fb" type="button">Facebook</button>
      <button class="btn btn-ghost btn-sm" id="share-linkedin" type="button">LinkedIn</button>
      <button class="btn btn-ghost btn-sm" id="share-copy" type="button">Copy Link</button>
    </div>
  </div>
</section>

<section class="bg-cream">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Community</p>
      <h2>Recent Participation</h2>
    </div>
    <?php if (!$recent): ?>
      <p class="muted text-center">Be the first to record a Parayanam for this campaign.</p>
    <?php else: ?>
      <div class="table-wrap medium" style="margin:0 auto">
        <table class="data-table">
          <thead><tr><th>Participant</th><th>Date</th><th>Count</th></tr></thead>
          <tbody>
            <?php foreach ($recent as $r): ?>
              <tr><td><?= e($r['full_name']) ?></td><td><?= e(format_date($r['participation_date'])) ?></td><td><?= (int)$r['count'] ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function(){
  var el = document.querySelector('.js-count-up');
  if (!el || !('IntersectionObserver' in window)) return;
  var target = parseInt(el.dataset.target, 10) || 0;
  var done = false;
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if (entry.isIntersecting && !done) {
        done = true;
        var start = 0, duration = 1200, startTime = null;
        function step(ts){
          if (!startTime) startTime = ts;
          var progress = Math.min((ts - startTime) / duration, 1);
          el.textContent = Math.floor(progress * target).toLocaleString();
          if (progress < 1) requestAnimationFrame(step); else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(step);
      }
    });
  });
  obs.observe(el);

  var completed = <?= (int)$completed ?>;
  var target2 = <?= (int)$target ?>;
  var shareText = 'Join the 1 Crore Hanuman Jayanti Parayanam for Universal Peace. Together we have completed ' + completed.toLocaleString() + ' toward our goal of ' + target2.toLocaleString() + '. ' + window.location.href;

  var wa = document.getElementById('share-whatsapp');
  if (wa) wa.addEventListener('click', function(){ window.open('https://wa.me/?text=' + encodeURIComponent(shareText), '_blank'); });
  var fb = document.getElementById('share-fb');
  if (fb) fb.addEventListener('click', function(){ window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank'); });
  var li = document.getElementById('share-linkedin');
  if (li) li.addEventListener('click', function(){ window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(window.location.href), '_blank'); });
  var cp = document.getElementById('share-copy');
  if (cp) cp.addEventListener('click', function(){
    navigator.clipboard.writeText(window.location.href).then(function(){ cp.textContent = 'Link Copied!'; setTimeout(function(){ cp.textContent = 'Copy Link'; }, 1800); });
  });
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
