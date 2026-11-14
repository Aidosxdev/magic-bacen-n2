<?php
/**
 * Umumiy <head> + <body> ochilishi.
 * Sahifa oldindan belgilaydi:  $pageTitle (majburiy), $bodyClass (ixtiyoriy)
 */
require_once __DIR__ . '/functions.php';
$cssFile = dirname(__DIR__) . '/assets/css/neon.css';
$cssVer  = is_file($cssFile) ? filemtime($cssFile) : 1;
$menuCssFile = dirname(__DIR__) . '/assets/css/menu.css';
$menuVer = is_file($menuCssFile) ? filemtime($menuCssFile) : 1;
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Magic Manga') ?></title>
    <link rel="icon" type="image/x-icon" href="<?= url('images/favicon.ico') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= url('images/logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/menu.css') ?>?v=<?= (int)$menuVer ?>">
    <link rel="stylesheet" href="<?= url('assets/css/neon.css') ?>?v=<?= (int)$cssVer ?>">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
