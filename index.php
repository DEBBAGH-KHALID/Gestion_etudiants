<?php
require_once 'functions.php';

if (connecte()) {
    header('Location: liste.php');
    exit;
}

$erreur = '';
$login  = $_COOKIE['dernier_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mdp'] ?? '';

    if (array_key_exists($login, UTILISATEURS) && UTILISATEURS[$login] === $mdp) {
        $_SESSION['user'] = $login;
        setcookie('dernier_user', $login, time() + 7 * 24 * 3600);
        header('Location: liste.php');
        exit;
    }
    $erreur = 'Login ou mot de passe incorrect.';
}

$titre = 'Connexion';
include 'header.php';
?>
<?php if ($erreur): ?><p class="message erreur"><?= h($erreur) ?></p><?php endif; ?>

<form method="POST" class="carte">
    <label>Login</label>
    <input type="text" name="login" value="<?= h($login) ?>" required>
    <label>Mot de passe</label>
    <input type="password" name="mdp" required>
    <button type="submit">Se connecter</button>
</form>

