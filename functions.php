<?php

session_start();

const FICHIER   = __DIR__ . '/data/etudiants.txt';
const UPLOADS   = __DIR__ . '/uploads/';
const FILIERES  = ['GI', 'IDIA', 'GE', 'GC'];
const UTILISATEURS = [
    'admin' => '12344321',
    'prof'  => 'prof2024',
];

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function connecte(): bool
{
    return isset($_SESSION['user']);
}

function exiger_connexion(): void
{
    if (!connecte()) {
        header('Location: index.php');
        exit;
    }
}

function lire_etudiants(): array
{
    $liste = [];
    if (!file_exists(FICHIER)) {
        return $liste;
    }
    $lignes = file(FICHIER, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $p = explode(';', $ligne);
        if (count($p) < 6) {
            continue;
        }
        $id = (int)$p[0];
        $liste[$id] = [
            'id'      => $id,
            'nom'     => $p[1],
            'prenom'  => $p[2],
            'filiere' => $p[3],
            'note'    => (float)$p[4],
            'photo'   => $p[5],
        ];
    }
    return $liste;
}

function sauver_etudiants(array $liste): void
{
    $lignes = [];
    foreach ($liste as $e) {
        $lignes[] = implode(';', [$e['id'], $e['nom'], $e['prenom'], $e['filiere'], $e['note'], $e['photo']]);
    }
    $contenu = $lignes ? implode("\n", $lignes) . "\n" : '';
    file_put_contents(FICHIER, $contenu, LOCK_EX);
}

function nettoyer(string $s): string
{
    return trim(str_replace([';', "\r", "\n"], ' ', trim($s)));
}

// Message flash
function flash(string $type, string $texte): void
{
    $_SESSION['flash'] = ['type' => $type, 'texte' => $texte];
}
