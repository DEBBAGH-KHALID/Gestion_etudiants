// Confirmation avant suppression
document.querySelectorAll('a.supprimer').forEach(function (lien) {
    lien.addEventListener('click', function (e) {
        if (!confirm('Voulez-vous vraiment supprimer cet étudiant ?')) {
            e.preventDefault();
        }
    });
});

// Recherche instantanée
var champ = document.getElementById('q');
if (champ) {
    champ.addEventListener('input', function () {
        var texte = champ.value.toLowerCase().trim();
        document.querySelectorAll('tbody tr[data-recherche]').forEach(function (ligne) {
            ligne.style.display = ligne.dataset.recherche.includes(texte) ? '' : 'none';
        });
    });
}
