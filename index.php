<?php
session_start();
require 'auth/verifica_login.php';
header('Location: gestao_loja.php');
exit;
