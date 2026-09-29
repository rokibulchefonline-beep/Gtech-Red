document.querySelectorAll('.mega-tabs a').forEach(function (a) {
  a.addEventListener('mouseenter', function () {
    document.querySelectorAll('.mega-tabs a, .mega-pane').forEach(function (n) { n.classList.remove('on'); });
    a.classList.add('on');
    document.querySelector('.mega-pane[data-pane="' + a.dataset.tab + '"]').classList.add('on');
  });
});
document.querySelectorAll('.dd-t').forEach(function (t) {
  t.addEventListener('click', function (e) { e.preventDefault(); t.parentElement.classList.toggle('open'); });
});
