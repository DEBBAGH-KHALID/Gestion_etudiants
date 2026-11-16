<?php
require_once 'functions.php';
exiger_connexion();

$etudiants = array_values(lire_etudiants());

// Recherche
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $etudiants = array_values(array_filter($etudiants, function ($e) use ($q) {
        return stripos($e['nom'], $q) !== false || stripos($e['prenom'], $q) !== false;
    }));
}

// Tri
$tri = $_GET['tri'] ?? 'ajout';
if (!in_array($tri, ['ajout', 'nom', 'filiere', 'note'])) {
    $tri = 'ajout';
}
if ($tri === 'nom') {
    usort($etudiants, fn($a, $b) => strcasecmp($a['nom'], $b['nom']));
} elseif ($tri === 'filiere') {
    usort($etudiants, fn($a, $b) => strcasecmp($a['filiere'], $b['filiere']));
} elseif ($tri === 'note') {
    usort($etudiants, fn($a, $b) => $b['note'] <=> $a['note']);
}

// Statistiques
$nb        = count($etudiants);
$notes     = array_column($etudiants, 'note');
$moyenne   = $nb > 0 ? array_sum($notes) / $nb : 0;
$meilleure = $nb > 0 ? max($notes) : 0;

// Message flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$titre = 'Liste des étudiants';
include 'header.php';
?>
<?php if ($flash): ?>
    <p class="message <?= h($flash['type']) ?>"><?= h($flash['texte']) ?></p>
<?php endif; ?>

<form method="GET" class="recherche">
    <input type="text" id="q" name="q" placeholder="Rechercher un nom ou prénom" value="<?= h($q) ?>">
    <select name="tri">
        <option value="ajout"   <?= $tri === 'ajout'   ? 'selected' : '' ?>>Ordre d'ajout</option>
        <option value="nom"     <?= $tri === 'nom'     ? 'selected' : '' ?>>Nom</option>
        <option value="filiere" <?= $tri === 'filiere' ? 'selected' : '' ?>>Filière</option>
        <option value="note"    <?= $tri === 'note'    ? 'selected' : '' ?>>Note décroissante</option>
    </select>
    <button type="submit">Valider</button>
</form>

<p class="stats">
    <strong><?= $nb ?></strong> étudiant(s) |
    Moyenne : <strong><?= number_format($moyenne, 2) ?></strong> |
    Meilleure note : <strong><?= number_format($meilleure, 2) ?></strong>
</p>

<table>
    <thead>
        <tr><th>Photo</th><th>Nom</th><th>Prénom</th><th>Filière</th><th>Note</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php if ($nb === 0): ?>
        <tr><td colspan="6">Aucun étudiant</td></tr>
    <?php else: ?>
        <?php foreach ($etudiants as $e): ?>
            <tr data-recherche="<?= h(strtolower($e['nom'] . ' ' . $e['prenom'])) ?>">
                <td>
                    <?php if ($e['photo'] !== '' && file_exists(UPLOADS . $e['photo'])): ?>
                        <img class="photo" src="uploads/<?= h($e['photo']) ?>" alt="photo">
                    <?php else: ?>-<?php endif; ?>
                </td>
                <td><?= h(strtoupper($e['nom'])) ?></td>
                <td><?= h(ucfirst($e['prenom'])) ?></td>
                <td><?= h($e['filiere']) ?></td>
                <td class="<?= $e['note'] >= 10 ? 'vert' : 'rouge' ?>"><?= number_format($e['note'], 2) ?></td>
                <td>
                    <a href="ajouter.php?id=<?= $e['id'] ?>">Modifier</a> |
                    <a class="supprimer" href="supprimer.php?id=<?= $e['id'] ?>">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

