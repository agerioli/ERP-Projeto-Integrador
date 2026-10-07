<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth/verifica_login.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Sistema LGPD</title>

    <link rel="icon"
          type="image/x-icon"
          href="favicon.ico">

    <link rel="stylesheet"
          href="assets/css/estilo.css">

</head>

<body>