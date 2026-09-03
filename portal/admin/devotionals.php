<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $date = $_POST['scheduled_date'] ?? '';
    $verse = trim($_POST['verse_text'] ?? '');
    $translation = trim($_POST['translation'] ?? '');
    $reflection = trim($_POST['reflection'] ?? '');

    if (!$date || !$verse) {
        flash_set('error', 'Please provide at least a date and verse text.');
    } else {
        $stmt = db()->prepare('INSERT INTO daily_devotionals (scheduled_date, verse_text, translation, reflection) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE verse_text = VALUES(verse_text), translation = VALUES(translation), reflection = VALUES(reflection)');
        $stmt->bind_param('ssss', $date, $verse, $translation, $reflection);
        $stmt->execute();
        $stmt->close();
        flash_set('success', 'Devotional saved for ' . $date . '.');
    }
    redirect('devotionals.php');
}

$devotionals = db()->query('SELECT * FROM daily_devotionals ORDER BY scheduled_date DESC LIMIT 30')->fetch_all(MYSQLI_ASSOC);

admin_top('Daily Devotionals', 'devotionals');
?>
<h1 style="margin-bottom:28px">Daily Devotionals</h1>

<div class="grid grid-2" style="align-items:start">
  <div class="form-card">
    <h2 style="font-size:1.1rem">Queue a devotional</h2>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field">
        <label for="scheduled_date">Date <span class="req">*</span></label>
        <input id="scheduled_date" name="scheduled_date" type="date" required value="<?= e(date('Y-m-d', strtotime('+1 day'))) ?>">
      </div>
      <div class="field">
        <label for="verse_text">Verse (Telugu or transliteration) <span class="req">*</span></label>
        <textarea id="verse_text" name="verse_text" rows="3" required></textarea>
      </div>
      <div class="field">
        <label for="translation">Meaning / Translation</label>
        <textarea id="translation" name="translation" rows="2"></textarea>
      </div>
      <div class="field">
        <label for="reflection">Reflection</label>
        <textarea id="reflection" name="reflection" rows="3"></textarea>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Save Devotional</button>
      <p class="muted" style="font-size:.82rem;margin-top:10px">The daily cron script sends whichever devotional matches today's date and hasn't been sent yet.</p>
    </form>
  </div>

  <div>
    <h2 style="font-size:1.1rem">Upcoming &amp; recent</h2>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Verse</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($devotionals as $d): ?>
            <tr>
              <td><?= e(format_date($d['scheduled_date'])) ?></td>
              <td><?= e(mb_strimwidth($d['verse_text'], 0, 60, '…')) ?></td>
              <td><span class="pill <?= $d['sent'] ? 'pill-active' : 'pill-inactive' ?>"><?= $d['sent'] ? 'Sent' : 'Queued' ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$devotionals): ?><tr><td colspan="3" class="muted">Nothing queued yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php admin_bottom(); ?>
