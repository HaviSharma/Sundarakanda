<?php
/**
 * Anonymous-RSVP modal, shared by portal/events.php and index.php's
 * homepage "Upcoming Events" section. Buttons with data-rsvp-open /
 * data-event-id / data-event-title (see render_event_card() and the
 * homepage featured-event markup) open this.
 *
 * Set $rsvpFormAction to the events.php path relative to the including
 * page before including this file (defaults to 'events.php', correct for
 * portal/events.php itself).
 */
$rsvpFormAction = $rsvpFormAction ?? 'events.php';
?>
<div class="modal" id="rsvp-modal" hidden>
  <div class="modal-card">
    <button class="modal-close" type="button" data-rsvp-close aria-label="Close">&times;</button>
    <h2 style="font-size:1.2rem">RSVP</h2>
    <p class="muted" id="rsvp-modal-event" style="margin-bottom:16px"></p>
    <form method="post" action="<?= e($rsvpFormAction) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="rsvp">
      <input type="hidden" name="event_id" id="rsvp-event-id" value="">
      <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div class="field">
        <label for="rsvp-name">Your name <span class="req">*</span></label>
        <input id="rsvp-name" name="full_name" type="text" required>
      </div>
      <div class="field">
        <label for="rsvp-email">Your email <span class="req">*</span></label>
        <input id="rsvp-email" name="email" type="email" required>
      </div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-primary" type="submit" style="flex:1;justify-content:center">Confirm RSVP</button>
        <button class="btn btn-ghost" type="button" data-rsvp-close style="flex:1;justify-content:center">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
(function () {
  var modal = document.getElementById('rsvp-modal');
  if (!modal) return;
  var idField = document.getElementById('rsvp-event-id');
  var label = document.getElementById('rsvp-modal-event');
  function open(btn) {
    idField.value = btn.getAttribute('data-event-id');
    label.textContent = btn.getAttribute('data-event-title') || '';
    modal.hidden = false;
    document.getElementById('rsvp-name').focus();
  }
  function close() { modal.hidden = true; }
  document.querySelectorAll('[data-rsvp-open]').forEach(function (b) {
    b.addEventListener('click', function () { open(b); });
  });
  document.querySelectorAll('[data-rsvp-close]').forEach(function (b) {
    b.addEventListener('click', close);
  });
  modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
