<section class="page-hd"><div class="wrap"><h1>Contact Us</h1><p>Tell us about your project.</p></div></section>
<section class="wrap block contact-grid">
  <form method="post" class="form">
    <?php if ($msg): ?><div class="alert <?= $msg[0] ?>"><?= e($msg[1]) ?></div><?php endif; ?>
    <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
    <label>Name <input name="name" required value="<?= e($old['name'] ?? '') ?>"></label>
    <label>Email <input type="email" name="email" required value="<?= e($old['email'] ?? '') ?>"></label>
    <label>Phone <input name="phone" value="<?= e($old['phone'] ?? '') ?>"></label>
    <label>Service
      <select name="service">
        <option value="">Select</option>
        <?php $sel = $old['service'] ?? ($_GET['service'] ?? ''); foreach ($site['services'] as $g) foreach ($g['items'] as $n => $b): ?>
          <option <?= $sel === $n ? 'selected' : '' ?>><?= e($n) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Message <textarea name="message" rows="5" required><?= e($old['message'] ?? '') ?></textarea></label>
    <button class="btn" type="submit">Send</button>
  </form>
  <aside class="prose">
    <h3>Get in touch</h3>
    <p><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a><br><?= e($site['phone']) ?></p>
  </aside>
</section>
