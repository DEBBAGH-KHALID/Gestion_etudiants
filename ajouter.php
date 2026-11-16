<?php
require_once 'functions.php';
exiger_connexion();

$etudiants = lire_etudiants();
$id = (int)($_GET['id'] ?? 0);
$modif = $id > 0;

if ($modif && !isset($etudiants[$id])) {
    flash('erreur', 'Étudiant introuvable.');
    header('Location: liste.php');
    exit;
}

$valeurs = $modif
    ? $etudiants[$id]
    : ['nom' => '', 'prenom' => '', 'filiere' => '', 'note' => '', 'photo' => ''];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = nettoyer($_POST['nom'] ?? '');
    $prenom  = nettoyer($_POST['prenom'] ?? '');
    $filiere = $_POST['filiere'] ?? '';
    $note    = str_replace(',', '.', trim($_POST['note'] ?? ''));

    $valeurs = array_merge($valeurs, ['nom' => $nom, 'prenom' => $prenom, 'filiere' => $filiere, 'note' => $note]);

    if ($nom === '')    $erreurs[] = 'Le nom est obligatoire.';
    if ($prenom === '') $erreurs[] = 'Le prénom est obligatoire.';
    if (!in_array($filiere, FILIERES)) $erreurs[] = 'Filière invalide.';
    if (!is_numeric($note) || $note < 0 || $note > 20) $erreurs[] = 'La note doit être un nombre entre 0 et 20.';

    // Photo (facultative)
    $nouvellePhoto = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['photo'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = "Erreur lors de l'envoi de la photo.";
        } else {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $erreurs[] = 'Photo : extensions autorisées jpg, jpeg, png.';
            } elseif ($f['size'] > 1024 * 1024 * 2) {
                $erreurs[] = 'Photo : taille maximale 2 Mo.';
            } else {
                $nouvellePhoto = uniqid('etu_') . '.' . $ext;
            }
        }
    }

    if (!$erreurs && $nouvellePhoto !== '') {
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], UPLOADS . $nouvellePhoto)) {
            $erreurs[] = "Impossible d'enregistrer la photo.";
        }
    }

    if (!$erreurs) {
        $photo = $valeurs['photo'];
        if ($nouvellePhoto !== '') {
            if ($photo !== '' && file_exists(UPLOADS . $photo)) {
                unlink(UPLOADS . $photo); // supprime l'ancienne photo
            }
            $photo = $nouvellePhoto;
        }

        if (!$modif) {
            $id = $etudiants ? max(array_keys($etudiants)) + 1 : 1;
        }
        $etudiants[$id] = [
            'id' => $id, 'nom' => $nom, 'prenom' => $prenom,
            'filiere' => $filiere, 'note' => (float)$note, 'photo' => $photo,
        ];
        sauver_etudiants($etudiants);
        flash('succes', $modif ? 'Étudiant modifié avec succès.' : 'Étudiant ajouté avec succès.');
        header('Location: liste.php');
        exit;
    }
}

$titre = $modif ? "Modifier l'étudiant" : 'Ajouter un étudiant';
include 'header.php';
?>
<?php foreach ($erreurs as $err): ?>
    <p class="message erreur"><?= h($err) ?></p>
<?php endforeach; ?>

<form method="POST" enctype="multipart/form-data" class="carte">
    <label>Nom</label>
    <input type="text" name="nom" value="<?= h($valeurs['nom']) ?>">

    <label>Prénom</label>
    <input type="text" name="prenom" value="<?= h($valeurs['prenom']) ?>">

    <label>Filière</label>
    <select name="filiere">
        <option value="">-- Choisir --</option>
        <?php foreach (FILIERES as $f): ?>
            <option value="<?= h($f) ?>" <?= $valeurs['filiere'] === $f ? 'selected' : '' ?>><?= h($f) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Note (0 à 20)</label>
    <input type="text" name="note" value="<?= h((string)$valeurs['note']) ?>">

    <label>Photo (jpg, png – 2 Mo max, facultative)</label>
    <input type="file" name="photo" accept=".jpg,.jpeg,.png">

    <button type="submit"><?= $modif ? 'Modifier' : 'Ajouter' ?></button>
</form>

