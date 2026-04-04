<?php
// =========================================
// KAISEKI SHUNEI — MIGRATION.PHP
// Script à exécuter UNE SEULE FOIS
// pour hasher les mots de passe du JSON
// Accès : http://localhost/YumlandTest/migration.php
// SUPPRIMER CE FICHIER APRÈS UTILISATION !
// =========================================

require_once 'includes/config.php';
require_once 'includes/fonctions.php';

$data = lireJSON(JSON_USERS);

$motsDePasseSimples = [
    'U001' => 'motdepasse1',
    'U002' => 'motdepasse2',
    'U003' => 'motdepasse3',
    'U004' => 'motdepasse4',
    'U005' => 'motdepasse5',
    'A001' => 'admin1234',
    'A002' => 'admin5678',
    'R001' => 'kenji1234',
    'L001' => 'kovacs1234',
];

foreach ($data['utilisateurs'] as &$user) {
    if (isset($motsDePasseSimples[$user['id']])) {
        $user['mot_de_passe'] = password_hash($motsDePasseSimples[$user['id']], PASSWORD_DEFAULT);
    }
}

ecrireJSON(JSON_USERS, $data);

echo "<h2 style='font-family:sans-serif; color:green;'>✅ Migration terminée !</h2>";
echo "<p style='font-family:sans-serif;'>Mots de passe hashés avec succès.</p>";
echo "<hr>";
echo "<h3 style='font-family:sans-serif;'>Comptes de test :</h3>";
echo "<table border='1' cellpadding='10' style='font-family:sans-serif; border-collapse:collapse;'>";
echo "<tr><th>ID</th><th>Login</th><th>Mot de passe</th><th>Rôle</th></tr>";

$comptes = [
    ['U001', 'jean.dupont@email.com',       'motdepasse1', 'client'],
    ['U002', 'elena.rodriguez@email.com',    'motdepasse2', 'client'],
    ['U003', 'thomas.miller@email.com',      'motdepasse3', 'client (suspendu)'],
    ['U004', 'yuki.tanaka@email.com',        'motdepasse4', 'client'],
    ['U005', 'jabar.abdul@email.com',        'motdepasse5', 'client'],
    ['A001', 'admin@kaiseki.com',            'admin1234',   'admin'],
    ['A002', 'superadmin@kaiseki.com',       'admin5678',   'admin'],
    ['R001', 'kenji@kaiseki.com',            'kenji1234',   'restaurateur'],
    ['L001', 't.kovacs@kaiseki.com',         'kovacs1234',  'livreur'],
];

foreach ($comptes as $c) {
    echo "<tr><td>{$c[0]}</td><td>{$c[1]}</td><td>{$c[2]}</td><td>{$c[3]}</td></tr>";
}
echo "</table>";
echo "<br><p style='font-family:sans-serif; color:red;'><strong>⚠️ SUPPRIME CE FICHIER après utilisation !</strong></p>";
?>