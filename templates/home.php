<section class="hero">
  <div class="wrap">
    <h1>We grow brands with <span class="hl">digital</span>, web &amp; software</h1>
    <p><?= e($site['tagline']) ?>. Strategy, design and engineering under one roof.</p>
    <a class="btn" href="/contact">Get a free consultation</a>
    <a class="btn ghost" href="/case-studies">See our work</a>
  </div>
</section>

<section class="dark">
  <div class="wrap">
    <h2>What we do</h2>
    <ul class="list2">
      <?php foreach (['Digital Advertising' => 'digital-advertising', 'Branding' => 'branding',
        'Search Engine Optimization' => 'search-engine-optimization', 'Website Design' => 'website-design',
        'Paid Media' => 'paid-media', 'Marketing Advisory' => 'marketing-advisory',
        'Social Media' => 'social-media-marketing', 'Conversion Rate Optimization' => 'conversion-rate-optimization'] as $n => $u): ?>
        <li><a href="/services/<?= $u ?>"><?= e($n) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="wrap block">
  <h2>Our services</h2>
  <div class="cards">
    <?php foreach ($site['services'] as $gs => $g): ?>
      <a class="card" href="/services/<?= $gs ?>">
        <h3><?= e($g['title']) ?></h3>
        <p><?= e($g['intro']) ?></p>
        <small><?= e(implode(' · ', array_slice(array_keys($g['items']), 0, 3))) ?></small>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="grad">
  <div class="wrap">
    <h2>Industries we serve</h2>
    <div class="chips">
      <?php foreach ($site['industries'] as $n): ?><a href="/industries/<?= slug($n) ?>"><?= e($n) ?></a><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="wrap block">
  <h2>Latest case studies</h2>
  <?php $docs = App\Site::docs('case_studies', [], 3); $base = '/case-studies'; require __DIR__ . '/_cards.php'; ?>
  <p><a href="/case-studies">All case studies &rarr;</a></p>
</section>

<section class="cta">
  <div class="wrap"><h2>Ready to grow?</h2><a class="btn light" href="/contact">Contact us</a></div>
</section>
