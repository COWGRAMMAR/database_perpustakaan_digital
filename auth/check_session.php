<?php
// auth/check_session.php
// Kumpulan fungsi untuk cek session login & role
// Cara pakai: include file ini di paling atas halaman yang butuh login

session_start();

/**
 * Cek apakah user sudah login.
 * Kalau belum, redirect ke login.php
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . getBasePath() . 'login.php');
        exit;
    }
}

/**
 * Cek apakah user sudah login DAN role-nya sesuai yang diizinkan.
 * Contoh: requireRole(['Admin']) atau requireRole(['Admin', 'Staff'])
 */
function requireRole(array $allowedRoles) {
    requireLogin();

    if (!in_array($_SESSION['role'], $allowedRoles)) {
        // Role tidak sesuai, lempar ke dashboard masing-masing
        redirectToDashboard();
        exit;
    }
}

/**
 * Redirect user ke dashboard sesuai role-nya.
 * Dipakai setelah login, atau saat akses ditolak.
 */
function redirectToDashboard() {
    $base = getBasePath();

    if (!isset($_SESSION['role'])) {
        header('Location: ' . $base . 'login.php');
        exit;
    }

    switch ($_SESSION['role']) {
        case 'Admin':
            header('Location: ' . $base . 'admin/dashboard.php');
            break;
        case 'Staff':
            header('Location: ' . $base . 'staff/dashboard.php');
            break;
        case 'Pembaca':
            header('Location: ' . $base . 'pembaca/dashboard.php');
            break;
        default:
            header('Location: ' . $base . 'login.php');
    }
    exit;
}


function getBasePath() {
    $folderName = 'sisbad/database_perpustakaan_digital';
    return '/' . $folderName . '/';
}
