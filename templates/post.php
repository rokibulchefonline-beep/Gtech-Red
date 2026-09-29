<section class="page-hd"><div class="wrap"><a href="<?= $back ?>">&larr; <?= e($label) ?></a><h1><?= e($doc['title'] ?? '') ?></h1></div></section>
<article class="wrap block prose"><?= nl2br(e($doc['body'] ?? '')) ?></article>
