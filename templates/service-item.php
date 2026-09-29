<section class="page-hd"><div class="wrap"><a href="/services/<?= $gslug ?>">&larr; <?= e($group['title']) ?></a><h1><?= e($name) ?></h1><p><?= e($blurb) ?></p></div></section>
<section class="wrap block prose">
  <h2>How we help</h2>
  <p>Replace with detailed copy for <?= e($name) ?>: approach, deliverables, tools, pricing model.</p>
  <h2>Related services</h2>
  <div class="chips dark-chips">
    <?php foreach ($group['items'] as $n => $b): if ($n === $name) continue; ?>
      <a href="/services/<?= $gslug ?>/<?= slug($n) ?>"><?= e($n) ?></a>
    <?php endforeach; ?>
  </div>
  <p><a class="btn" href="/contact?service=<?= urlencode($name) ?>">Get a quote</a></p>
</section>
