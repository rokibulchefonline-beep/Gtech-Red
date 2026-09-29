<section class="page-hd"><div class="wrap"><h1>Request Your Growth Proposal</h1><p>Fill out the form below to receive a custom proposal within 24 hours.</p></div></section>
<section class="wrap block contact-grid">
  <form method="post" action="/contact" class="form">
    <?php if ($msg): ?><div class="alert <?= $msg[0] ?>"><?= e($msg[1]) ?></div><?php endif; ?>
    <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
    <label>Name * <input name="name" required placeholder="John Smith" value="<?= e($old['name'] ?? '') ?>"></label>
    <label>Business Name * <input name="business" required placeholder="Your Company Ltd" value="<?= e($old['business'] ?? '') ?>"></label>
    <label>Email * <input type="email" name="email" required placeholder="john@company.com" value="<?= e($old['email'] ?? '') ?>"></label>
    <label>Phone * <input type="tel" name="phone" required placeholder="07123 456789" value="<?= e($old['phone'] ?? '') ?>"></label>
    <label>What do you need? *
      <select name="service" required>
        <option value="">Select required service...</option>
        <?php $sel = $old['service'] ?? ($_GET['service'] ?? ''); foreach ($site['services'] as $g): ?>
          <optgroup label="<?= e($g['title']) ?>">
            <?php foreach ($g['items'] as $n => $b): ?><option <?= $sel === $n ? 'selected' : '' ?>><?= e($n) ?></option><?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Monthly budget *
      <select name="budget" required>
        <option value="">Select estimated monthly budget...</option>
        <?php foreach ($site['budgets'] as $b): ?><option <?= ($old['budget'] ?? '') === $b ? 'selected' : '' ?>><?= e($b) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Message <textarea name="message" rows="5"><?= e($old['message'] ?? '') ?></textarea></label>
    <button class="btn" type="submit">Request Proposal</button>
  </form>
  <aside class="prose">
    <h3>Get in touch</h3>
    <p><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a><br><?= e($site['phone']) ?></p>
  </aside>
</section>
