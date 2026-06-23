<?php
// index.php
require_once 'auth/check_session.php';

if (isset($_SESSION['user_id'])) {
    redirectToDashboard();
} else {
    header('Location: login.php');
}
exit;
