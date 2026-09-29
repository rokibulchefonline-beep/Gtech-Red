<section class="page-hd"><div class="wrap"><h1><?= e($name) ?></h1><p>Marketing, websites and software for <?= e(strtolower($name)) ?> businesses.</p></div></section>
<section class="wrap block prose">
  <h2>What we do for <?= e($name) ?></h2>
  <p>Replace with industry-specific copy, challenges and results.</p>
  <div class="cards">
    <?php foreach ($site['services'] as $gs => $g): ?>
      <a class="card" href="/services/<?= $gs ?>"><h3><?= e($g['title']) ?></h3><p><?= e($g['intro']) ?></p></a>
    <?php endforeach; ?>
  </div>
</section>
