<?php if (!$docs): ?>
  <p class="muted">Nothing published yet. Check back soon.</p>
<?php else: ?>
  <div class="cards">
    <?php foreach ($docs as $d): ?>
      <a class="card" href="<?= $base ?>/<?= e($d['slug'] ?? '') ?>">
        <h3><?= e($d['title'] ?? '') ?></h3>
        <p><?= e($d['excerpt'] ?? '') ?></p>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
