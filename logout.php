<?php
session_start();
session_destroy();
// Удаляем cookie "запомнить меня"
setcookie("remember_user", "", time() - 3600, "/");
header("Location: login.php");
exit;