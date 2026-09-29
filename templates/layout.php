<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($site['tagline']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="hdr">
  <div class="wrap hdr-in">
    <a class="logo" href="/"><?= e($site['name']) ?></a>
    <button class="burger" aria-label="Menu" onclick="document.body.classList.toggle('nav-open')">&#9776;</button>
    <nav class="nav">
      <a href="/">Home</a>
      <a href="/about">About Us</a>

      <div class="dd mega">
        <a href="#" class="dd-t">Services &#9662;</a>
        <div class="dd-panel mega-panel">
          <div class="mega-tabs">
            <?php $i = 0; foreach ($site['services'] as $gs => $g): ?>
              <a href="/services/<?= $gs ?>" data-tab="<?= $gs ?>" class="<?= $i++ ? '' : 'on' ?>"><?= e($g['title']) ?></a>
            <?php endforeach; ?>
          </div>
          <div class="mega-body">
            <?php $i = 0; foreach ($site['services'] as $gs => $g): ?>
              <div class="mega-pane <?= $i++ ? '' : 'on' ?>" data-pane="<?= $gs ?>">
                <h4><?= e($g['title']) ?></h4>
                <div class="mega-grid">
                  <?php foreach ($g['items'] as $n => $b): ?>
                    <a href="/services/<?= slug($n) ?>"><i><?= e(mb_substr($n, 0, 1)) ?></i><?= e($n) ?></a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="dd">
        <a href="#" class="dd-t">Industries &#9662;</a>
        <div class="dd-panel ind-panel">
          <?php foreach ($site['industries'] as $n): ?>
            <a href="/industries/<?= slug($n) ?>"><?= e($n) ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <a href="/case-studies">Case Studies</a>
      <a href="/blogs">Blog</a>
      <a class="btn sm" href="/contact">Contact Us</a>
    </nav>
  </div>
</header>

<main><?= $content ?></main>

<footer class="ftr">
  <div class="wrap ftr-grid">
    <div>
      <a class="logo" href="/"><?= e($site['name']) ?></a>
      <p><?= e($site['tagline']) ?></p>
      <p><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a><br><?= e($site['phone']) ?></p>
    </div>
    <div><h5>Services</h5>
      <?php foreach ($site['services'] as $gs => $g): ?><a href="/services/<?= $gs ?>"><?= e($g['title']) ?></a><?php endforeach; ?>
    </div>
    <div><h5>Industries</h5>
      <?php foreach ($site['industries'] as $n): ?><a href="/industries/<?= slug($n) ?>"><?= e($n) ?></a><?php endforeach; ?>
    </div>
    <div><h5>Company</h5>
      <a href="/about">About Us</a><a href="/case-studies">Case Studies</a><a href="/blogs">Blog</a><a href="/contact">Contact Us</a>
      <a href="/privacy-policy">Privacy Policy</a><a href="/terms">Terms</a>
    </div>
  </div>
  <div class="wrap copy">&copy; <?= date('Y') ?> <?= e($site['name']) ?>. All rights reserved.</div>
</footer>
<script src="/assets/site.js"></script>
</body>
</html>
