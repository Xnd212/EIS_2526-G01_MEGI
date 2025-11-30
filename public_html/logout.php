<?php
session_start();

// Limpa TODAS as variáveis da sessão
session_unset();

// Destrói a sessão no servidor
session_destroy();

// Redireciona para a página de login
header("Location: login.php");
exit();
?>
