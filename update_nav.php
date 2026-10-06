<?php

$file = 'config/navigation.php';
$content = file_get_contents($file);

$search = '    // fallback';
$replace = "    'satpam' => [
        ['label' => 'Beranda', 'icon' => 'home', 'route' => 'satpam.dashboard'],
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'profil'],
    ],

    // fallback";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
