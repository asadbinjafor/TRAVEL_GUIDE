<?php

$title = $title ?? 'Travel Guide';

$pageTitle = Security::e($title);

$isLoggedIn = is_array($authUser ?? null);

$authRole = $authUser['role'] ?? '';

$authVerified = !empty($authUser['is_verified']);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="app-base" content="<?= Security::e(url('/')) ?>">

    <title><?= $pageTitle ?> | Travel Guide</title>

    <link rel="stylesheet" href="<?= asset('public/css/base.css') ?>">

    <link rel="stylesheet" href="<?= asset('public/css/components.css') ?>">

    <link rel="stylesheet" href="<?= asset('public/css/responsive.css') ?>">

</head>

<body>

<nav class="site-nav">

    <div class="nav-inner">

        <a class="nav-brand" href="<?= url('/') ?>">Travel Guide</a>

        <div class="nav-links">

            <?php if (!$isLoggedIn): ?>

                <a href="<?= url('/posts') ?>">Explore</a>

                <a href="<?= url('/login') ?>">Login</a>

                <a class="btn btn-primary btn-sm" href="<?= url('/register') ?>">Register</a>

            <?php else: ?>

                <a href="<?= url('/') ?>">Home</a>

                <a href="<?= url('/posts') ?>">Browse</a>

                <?php if ($authRole === 'admin'): ?>

                    <a href="<?= url('/admin') ?>">Admin</a>

                <?php endif; ?>

                <?php if ($authVerified): ?>

                    <?php if ($authRole === 'user'): ?>

                        <a href="<?= url('/wishlist') ?>">Wishlist</a>

                    <?php endif; ?>

                    <?php if ($authRole === 'scout'): ?>

                        <a href="<?= url('/scout/requests') ?>">My Requests</a>

                        <a href="<?= url('/scout/approved') ?>">Approved</a>

                    <?php endif; ?>

                <?php endif; ?>

                <a href="<?= url('/profile') ?>">Profile</a>

                <span class="badge badge-role"><?= Security::e($authRole) ?></span>

                <a href="<?= url('/logout') ?>">Logout</a>

            <?php endif; ?>

        </div>

    </div>

</nav>

<main class="container">

