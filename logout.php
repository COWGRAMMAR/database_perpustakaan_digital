<?php
// logout.php
require_once 'auth/check_session.php';

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
