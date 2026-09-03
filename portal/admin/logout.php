<?php
require_once __DIR__ . '/../includes/bootstrap.php';
unset($_SESSION['is_admin']);
redirect('login.php');
