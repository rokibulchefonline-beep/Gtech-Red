<section class="page-hd"><div class="wrap"><h1><?= e($group['title']) ?></h1><p><?= e($group['intro']) ?></p></div></section>
<section class="wrap block">
  <div class="cards">
    <?php foreach ($group['items'] as $n => $b): ?>
      <a class="card" href="/services/<?= $gslug ?>/<?= slug($n) ?>"><h3><?= e($n) ?></h3><p><?= e($b) ?></p></a>
    <?php endforeach; ?>
  </div>
</section>
<section class="cta"><div class="wrap"><h2>Talk to us about <?= e($group['title']) ?></h2><a class="btn light" href="/contact">Contact us</a></div></section>
