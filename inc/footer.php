</main>

<footer class="site-footer">
  <div class="container footer-top">
    <div class="footer-grid">
      <div class="footer-about">
        <img src="img/logo_s.png" alt="Sundarakanda">
        <p>Preserving and sharing the Telugu Hanuman Chalisa and Sundarakanda composed by Shri Sunderdas M.S. Rama Rao &mdash; carrying his devotional legacy forward for the next generation.</p>
        <div class="footer-social">
          <a href="https://www.youtube.com/@Jhoney786" aria-label="Subscribe on YouTube" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M23 12s0-3.6-.5-5.3a3 3 0 0 0-2.1-2.1C18.7 4 12 4 12 4s-6.7 0-8.4.6A3 3 0 0 0 1.5 6.7C1 8.4 1 12 1 12s0 3.6.5 5.3a3 3 0 0 0 2.1 2.1C5.3 20 12 20 12 20s6.7 0 8.4-.6a3 3 0 0 0 2.1-2.1C23 15.6 23 12 23 12Z"/><path d="M10 15.5v-7l6 3.5-6 3.5Z" fill="#fff"/></svg></a>
          <a href="https://www.facebook.com/" aria-label="Facebook" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M13.5 22v-8.5H16l.5-3.5h-3V7.7c0-1 .3-1.7 1.8-1.7H16V2.8C15.6 2.8 14.5 2.7 13.3 2.7c-2.6 0-4.4 1.6-4.4 4.5V10H6v3.5h2.9V22h4.6Z"/></svg></a>
        </div>
      </div>
      <div>
        <h4>Explore</h4>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="parayanam.php">Services</a></li>
          <li><a href="portal/events.php">Events</a></li>
          <li><a href="downloads.php">Downloads</a></li>
          <li><a href="photogallery.php">Photo Gallery</a></li>
          <li><a href="videogallery.php">Video Gallery</a></li>
        </ul>
      </div>
      <div>
        <h4>About</h4>
        <ul>
          <li><a href="msramarao.php">Shri M.S. Rama Rao</a></li>
          <li><a href="janardhana.php">Janardhana Polapragada</a></li>
          <li><a href="aluri.php">Aluri</a></li>
          <li><a href="donations.php">Ways to Give</a></li>
        </ul>
      </div>
      <div>
        <h4>Members</h4>
        <ul>
          <?php if (current_user()): ?>
            <li><a href="portal/dashboard.php">My Dashboard</a></li>
            <li><a href="portal/donate.php">Donations</a></li>
            <li><a href="portal/schedule-event.php">Schedule an Event</a></li>
            <li><a href="portal/logout.php">Sign Out</a></li>
          <?php else: ?>
            <li><a href="portal/signup.php">Become a Member</a></li>
            <li><a href="portal/login.php">Login</a></li>
          <?php endif; ?>
          <li><a href="portal/subscribe.php">Daily Devotional</a></li>
        </ul>
      </div>
      <div>
        <h4>Contact</h4>
        <ul class="footer-contact">
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg> <span>4425 Bidwell Dr #4104<br>Fremont, CA 94538, USA</span></li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.77.65 2.6a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.48-1.22a2 2 0 0 1 2.11-.45c.83.31 1.7.53 2.6.65A2 2 0 0 1 22 16.92z"/></svg> <span>+1 (510) 877-2424</span></li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M22 6c0-1.1-.9-2-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6Z"/><path d="m2 7 10 6 10-6"/></svg> <span>info@sundarakanda.com</span></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>&copy; <?= date('Y') ?> Sundarakanda &mdash; MS Rama Rao Memorial Foundation USA. All rights reserved.</span>
    <span><a href="portal/events.php">Events</a> &middot; <a href="contact.php">Contact</a></span>
  </div>
</footer>

<dialog class="lightbox" id="lightbox">
  <div class="lightbox-inner">
    <button class="lightbox-close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <img src="" alt="">
  </div>
</dialog>
<dialog class="lightbox" id="video-lightbox">
  <div class="lightbox-inner">
    <button class="lightbox-close" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <div class="video-frame-wrap"><iframe src="" title="Video" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>
  </div>
</dialog>

<script src="assets/js/site.js"></script>
</body>
</html>
