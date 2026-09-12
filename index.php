<?php
$pageTitle = 'Home';
$pageDesc = 'Sundarakanda USA — MS Rama Rao Memorial Foundation. Telugu Hanuman Chalisa and Sundarakanda Parayanam, community service, and devotional heritage.';
$activeNav = 'home';
include __DIR__ . '/inc/header.php';

// The hero and Upcoming Events section below share one query: the soonest
// upcoming event with a banner becomes the featured poster (and retires
// itself automatically once its date passes, since get_upcoming_events()
// only returns today-onward events).
$upcomingEvents = get_upcoming_events(6);
$heroEvent = null;
foreach ($upcomingEvents as $ev) {
    if (!empty($ev['banner_image'])) { $heroEvent = $ev; break; }
}
?>
<section class="hero">
  <div class="container">
    <div class="hero-copy">
      <p class="kicker">Sundarakanda &middot; MS Rama Rao Memorial Foundation USA</p>
      <h1>A living tradition of devotion.</h1>
      <p class="lead">Experience the Telugu Hanuman Chalisa and Sundarakanda through collective recitation, community gatherings, and the enduring legacy of Shri Sunderdas M.S. Rama Rao.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="#upcoming-events"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> <span>Upcoming Events</span></a>
        <a class="btn btn-outline" href="parayanam.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg> <span>Explore Our Services</span></a>
      </div>
    </div>
    <div class="hero-media">
      <?php if ($heroEvent): ?>
        <p class="hero-poster-label">Featured gathering</p>
        <button type="button" class="hero-poster-frame" data-lightbox="portal/uploads/events/<?= e($heroEvent['banner_image']) ?>" data-caption="<?= e($heroEvent['title']) ?>" aria-label="View full poster for <?= e($heroEvent['title']) ?>">
          <img src="portal/uploads/events/<?= e($heroEvent['banner_image']) ?>" alt="<?= e($heroEvent['title']) ?> event poster">
        </button>
        <a class="hero-poster-link" href="#upcoming-events">View event details &rarr;</a>
      <?php else: ?>
        <div class="frame"><img src="img/h_1.png" alt="Lord Hanuman idol adorned for worship"></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section id="upcoming-events">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Gather With Us</p>
      <h2>Upcoming Events</h2>
      <p>Join our community for prayer, recitation, and shared devotion.</p>
    </div>

    <?php if ($upcomingEvents): ?>
      <?php
      $featured = array_shift($upcomingEvents);
      $featuredDesc = trim(strip_tags($featured['description'] ?? ''));
      if (mb_strlen($featuredDesc) > 220) { $featuredDesc = mb_substr($featuredDesc, 0, 220) . '…'; }
      ?>
      <div class="featured-event">
        <div class="featured-event-media">
          <?php if (!empty($featured['banner_image'])): ?>
            <button type="button" class="featured-event-poster" data-lightbox="portal/uploads/events/<?= e($featured['banner_image']) ?>" data-caption="<?= e($featured['title']) ?>" aria-label="View full poster for <?= e($featured['title']) ?>">
              <img src="portal/uploads/events/<?= e($featured['banner_image']) ?>" alt="<?= e($featured['title']) ?> event poster">
            </button>
          <?php else: ?>
            <div class="featured-event-poster featured-event-poster--placeholder" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="48" height="48"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
          <?php endif; ?>
        </div>
        <div class="featured-event-body">
          <span class="pill pill-active">Featured gathering</span>
          <h3><?= e($featured['title']) ?></h3>
          <p class="featured-event-when">
            <strong><?= e(format_date($featured['event_date'])) ?></strong>
            <?php if (!empty($featured['start_time'])): ?>
              &middot; <?= e(date('g:i A', strtotime($featured['start_time']))) ?><?= empty($featured['end_time']) ? ' Pacific' : ' – ' . e(date('g:i A', strtotime($featured['end_time']))) ?>
            <?php endif; ?>
            &middot; <span class="pill"><?= e(event_mode_label($featured['event_mode'])) ?></span>
          </p>
          <?php if (!empty($featured['address']) && in_array($featured['event_mode'], ['in_person', 'hybrid'], true)): ?>
            <p class="featured-event-loc">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              <?= e($featured['address']) ?>
            </p>
          <?php endif; ?>
          <?php if ($featuredDesc !== ''): ?><p><?= e($featuredDesc) ?></p><?php endif; ?>

          <div class="featured-event-actions">
            <?php if (!empty($featured['rsvp_link'])): ?>
              <a class="btn btn-primary btn-sm" href="<?= e($featured['rsvp_link']) ?>" target="_blank" rel="noopener">RSVP</a>
            <?php elseif ($siteUser && user_has_rsvped((int)$featured['id'], (int)$siteUser['id'])): ?>
              <span class="pill pill-active">You're going</span>
            <?php elseif ($siteUser): ?>
              <form method="post" action="portal/events.php" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="rsvp">
                <input type="hidden" name="event_id" value="<?= (int)$featured['id'] ?>">
                <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                <button class="btn btn-primary btn-sm" type="submit">RSVP</button>
              </form>
            <?php else: ?>
              <button class="btn btn-primary btn-sm" type="button"
                      data-rsvp-open data-event-id="<?= (int)$featured['id'] ?>"
                      data-event-title="<?= e($featured['title']) ?>">RSVP</button>
            <?php endif; ?>

            <a class="btn btn-ghost btn-sm" href="portal/events.php?action=download_ics&amp;event=<?= e(rawurlencode($featured['slug'])) ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              Add to Calendar
            </a>

            <?php if (!empty($featured['address']) && in_array($featured['event_mode'], ['in_person', 'hybrid'], true)): ?>
              <a class="btn btn-ghost btn-sm" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode($featured['address'])) ?>" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                Get Directions
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if ($upcomingEvents): ?>
        <div class="events-pipeline" style="margin-top:28px">
          <?php foreach ($upcomingEvents as $ev): $ts = strtotime($ev['event_date']); ?>
            <div class="pipeline-item">
              <div class="pipeline-date">
                <span class="d"><?= e(date('j', $ts)) ?></span>
                <span class="m"><?= e(date('M', $ts)) ?></span>
              </div>
              <div class="pipeline-body">
                <h3><?= e($ev['title']) ?></h3>
                <p>
                  <?php if ($ev['start_time']): ?><?= e(date('g:i A', strtotime($ev['start_time']))) ?> &middot; <?php endif; ?>
                  <?= e(event_mode_label($ev['event_mode'])) ?>
                </p>
              </div>
              <a class="btn btn-ghost btn-sm" href="portal/events.php">Details</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!$siteUser): ?>
        <?php $rsvpFormAction = 'portal/events.php'; include __DIR__ . '/portal/includes/rsvp_modal.php'; ?>
      <?php endif; ?>
    <?php else: ?>
      <div class="card medium center" style="margin:0 auto;max-width:520px">
        <p style="color:var(--ink-500)">Check our events calendar for the latest Sundarakanda Parayanam sessions and community meetings.</p>
      </div>
    <?php endif; ?>

    <div class="text-center" style="margin-top:32px">
      <a class="btn btn-ghost" href="portal/events.php"><span>View all events</span></a>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="grid grid-3">
      <div class="card">
        <div class="icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg></div>
        <h3>Sundarakanda Parayanam</h3>
        <p>Group and individual recitations of the Telugu Sundarakanda, composed and sung by Shri M.S. Rama Rao himself.</p>
      </div>
      <div class="card">
        <div class="icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M12 2s5 4.5 5 10a5 5 0 0 1-10 0c0-1.5.7-2.6 1.4-3.6.4.9 1.1 1.6 1.9 1.6.9 0 1-1 .8-2.1C10.6 6.3 12 4 12 2Z"/></svg></div>
        <h3>Hanuman Chalisa</h3>
        <p>The Telugu translation of Tulsidas's Hanuman Chalisa, preserved in song for devotees across the world.</p>
      </div>
      <div class="card">
        <div class="icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg></div>
        <h3>Community Service</h3>
        <p>Philanthropic outreach in the spirit of seva &mdash; from student support to community gatherings.</p>
      </div>
    </div>
  </div>
</section>

<section class="bg-cream">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Our Legacy</p>
      <h2>Three generations, one devotional thread</h2>
      <p>From the original composer to the grandson who founded this foundation, and the president who leads it forward today.</p>
    </div>
    <div class="grid grid-3">
      <a class="profile-card" href="msramarao.php">
        <div class="ph"><img src="img/msramarao_1.jpg" alt="Shri Sunderdas M.S. Rama Rao"></div>
        <div class="body">
          <span class="role">1921 &ndash; 1992</span>
          <h3>Shri Sunderdas M.S. Rama Rao</h3>
          <p>The first Telugu playback singer, who translated Hanuman Chalisa and Sundarakanda into Telugu song.</p>
        </div>
      </a>
      <a class="profile-card" href="janardhana.php">
        <div class="ph"><img src="img/janardhana_1.jpg" alt="Janardhana Polapragada"></div>
        <div class="body">
          <span class="role">Founder</span>
          <h3>Janardhana Polapragada</h3>
          <p>Hanumath Upasaka and grandson of M.S. Rama Rao, who founded Sundarakanda USA to carry the legacy forward.</p>
        </div>
      </a>
      <a class="profile-card" href="aluri.php">
        <div class="ph"><img src="img/aluri.jpg" alt="Aluri"></div>
        <div class="body">
          <span class="role">President</span>
          <h3>Aluri Sivananda Phani Chakravarthi</h3>
          <p>President of Sundarakanda USA, guiding the foundation's devotional and community initiatives.</p>
        </div>
      </a>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="grid grid-2" style="align-items:stretch">
      <div class="card" style="display:flex;flex-direction:column;justify-content:center">
        <p class="kicker">A Gift That Matters</p>
        <h2 style="font-size:1.6rem">Please don't trash your old laptop or tablet</h2>
        <p style="color:var(--ink-500)">If it still works, ship it to Sundarakanda &mdash; it becomes a gift to a student in India studying online who needs it most.</p>
        <a class="btn btn-ghost" style="align-self:flex-start" href="donations.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v9h14v-9"/><path d="M12 8c-1.7 0-3-1.1-3-2.5S10.3 3 12 3s3 1.1 3 2.5S13.7 8 12 8Z"/></svg> <span>See how to donate</span></a>
      </div>
      <div class="card" style="display:flex;flex-direction:column">
        <p class="kicker">Join Us</p>
        <h2 style="font-size:1.6rem;margin-bottom:16px">What's coming up</h2>
        <?php $pipeline = get_upcoming_events(3); ?>
        <?php if ($pipeline): ?>
          <div class="events-pipeline">
            <?php foreach ($pipeline as $ev): $ts = strtotime($ev['event_date']); ?>
              <div class="pipeline-item">
                <div class="pipeline-date">
                  <span class="d"><?= e(date('j', $ts)) ?></span>
                  <span class="m"><?= e(date('M', $ts)) ?></span>
                </div>
                <div class="pipeline-body">
                  <h3><?= e($ev['title']) ?></h3>
                  <p>
                    <?php if ($ev['start_time']): ?><?= e(date('g:i A', strtotime($ev['start_time']))) ?> &middot; <?php endif; ?>
                    <?= e(event_mode_label($ev['event_mode'])) ?>
                  </p>
                </div>
                <a class="btn btn-ghost btn-sm" href="portal/events.php">Details</a>
              </div>
            <?php endforeach; ?>
          </div>
          <a class="btn btn-ghost" style="align-self:flex-start;margin-top:16px" href="portal/events.php"><span>See all events</span></a>
        <?php else: ?>
          <p style="color:var(--ink-500)">Check our events calendar for the latest Sundarakanda Parayanam sessions and community meetings.</p>
          <a class="btn btn-ghost" style="align-self:flex-start" href="portal/events.php"><span>View events</span></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="bg-cream">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Gallery</p>
      <h2>Moments from our gatherings</h2>
    </div>
    <div class="grid grid-4">
      <a class="gallery-item" href="photogallery.php"><img src="img/ms_1.jpg" alt="Gallery preview"></a>
      <a class="gallery-item" href="photogallery.php"><img src="img/janardhana/2.jpg" alt="Gallery preview"></a>
      <a class="gallery-item" href="photogallery.php"><img src="img/silicon/2.jpg" alt="Gallery preview"></a>
      <a class="gallery-item" href="videogallery.php"><img src="img/janardhana/5.jpg" alt="Gallery preview"></a>
    </div>
    <div class="text-center" style="margin-top:32px">
      <a class="btn btn-ghost" href="photogallery.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg> <span>View full gallery</span></a>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="cta-band">
      <div>
        <h2>Be part of Sundarakanda's story</h2>
        <p>Join as a free member for event and Parayanam updates by email, register for an account, or reach out with a question &mdash; we would love to hear from you.</p>
      </div>
      <div style="display:flex;gap:14px;flex-wrap:wrap">
        <a class="btn" style="background:#fff;color:var(--maroon-800)" href="portal/membership.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg> <span>Become a Member &mdash; Free</span></a>
        <a class="btn btn-outline" href="contact.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6c0-1.1-.9-2-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6Z"/><path d="m2 7 10 6 10-6"/></svg> <span>Contact Us</span></a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/inc/footer.php'; ?>
