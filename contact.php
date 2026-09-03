<?php
$pageTitle = 'Contact';
$pageDesc = 'Contact Sundarakanda USA — MS Rama Rao Memorial Foundation. Address, phone, email, and message form.';
$activeNav = 'contact';
include __DIR__ . '/inc/header.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.php">Home</a> <span>/</span> <span>Contact</span></div>
    <p class="kicker" style="color:#f0c987">Get In Touch</p>
    <h1>Contact Us</h1>
    <p class="lead">We would love to hear from you &mdash; whether it's about a Parayanam session, a donation, or just to say namaste.</p>
  </div>
</section>
<section>
  <div class="container">
    <div class="grid grid-3" style="margin-bottom:40px">
      <div class="info-tile">
        <div class="icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></div>
        <div><h3>Visit Us</h3><p>4425 Bidwell Dr #4104<br>Fremont, CA 94538, USA</p></div>
      </div>
      <div class="info-tile">
        <div class="icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.77.65 2.6a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.48-1.22a2 2 0 0 1 2.11-.45c.83.31 1.7.53 2.6.65A2 2 0 0 1 22 16.92z"/></svg></div>
        <div><h3>Call Us</h3><p><a href="tel:+15108772424">+1 (510) 877-2424</a></p></div>
      </div>
      <div class="info-tile">
        <div class="icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6c0-1.1-.9-2-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6Z"/><path d="m2 7 10 6 10-6"/></svg></div>
        <div><h3>Email Us</h3><p><a href="mailto:info@sundarakanda.com">info@sundarakanda.com</a></p></div>
      </div>
    </div>

    <div class="grid grid-2" style="align-items:start">
      <div class="form-card">
        <h2 style="font-size:1.4rem">Send us a message</h2>
        <form>
          <div class="field-row">
            <div class="field"><label for="c-name">Name</label><input id="c-name" name="name" type="text" autocomplete="name"></div>
            <div class="field"><label for="c-email">Email</label><input id="c-email" name="email" type="email" autocomplete="email"></div>
          </div>
          <div class="field"><label for="c-subject">Subject</label><input id="c-subject" name="subject" type="text"></div>
          <div class="field"><label for="c-message">Message</label><textarea id="c-message" name="message" rows="5"></textarea></div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6c0-1.1-.9-2-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6Z"/><path d="m2 7 10 6 10-6"/></svg> <span>Send Email</span></button>
        </form>
      </div>
      <div>
        <div class="map-wrap">
          <iframe src="https://www.google.com/maps?q=4425+Bidwell+Dr+%234104,+Fremont,+CA+94538&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Sundarakanda location"></iframe>
        </div>
        <div class="card" style="margin-top:24px;display:flex;gap:16px;align-items:center">
          <img src="img/janardhana_1.jpg" alt="Janardhana Polapragada" style="width:64px;height:64px;border-radius:50%;object-fit:cover">
          <div>
            <p class="kicker" style="margin-bottom:2px">Hanumath Upasaka</p>
            <h3 style="margin:0">Sundardasu Janardhana Polapragada</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
