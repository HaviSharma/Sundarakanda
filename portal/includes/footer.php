</main>

<footer class="site-footer">
  <div class="container footer-top">
    <div class="footer-grid">
      <div class="footer-about">
        <img src="../img/logo_s.png" alt="Sundarakanda">
        <p>Sundarakanda &mdash; MS Rama Rao Memorial Foundation USA. A community-supported, non-profit devotional foundation preserving the Telugu Hanuman Chalisa and Sundarakanda.</p>
        <div class="footer-social">
          <a href="https://www.youtube.com/@Jhoney786" aria-label="Subscribe on YouTube" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4L15.8 12Z"/></svg></a>
        </div>
      </div>
      <div>
        <h4>Portal</h4>
        <ul>
          <?php if (current_user()): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="donate.php">Donations</a></li>
            <li><a href="schedule-event.php">Schedule an Event</a></li>
            <li><a href="blessings.php">Request a Blessing</a></li>
          <?php else: ?>
            <li><a href="login.php">Sign In</a></li>
            <li><a href="signup.php">Become a Member</a></li>
          <?php endif; ?>
          <li><a href="subscribe.php">Daily Devotional</a></li>
          <li><a href="events.php">Events</a></li>
        </ul>
      </div>
      <div>
        <h4>Main Site</h4>
        <ul>
          <li><a href="../index.php">Home</a></li>
          <li><a href="../parayanam.php">Services</a></li>
          <li><a href="../photogallery.php">Photo Gallery</a></li>
          <li><a href="../contact.php">Contact</a></li>
        </ul>
      </div>
      <div>
        <h4>Contact</h4>
        <ul class="footer-contact">
          <li><span><?= e(ORG_PHONE) ?></span></li>
          <li><span><?= e(ORG_EMAIL) ?></span></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>&copy; <?= date('Y') ?> Sundarakanda &mdash; MS Rama Rao Memorial Foundation USA. All rights reserved.</span>
  </div>
</footer>

<script src="../assets/js/site.js"></script>
</body>
</html>
