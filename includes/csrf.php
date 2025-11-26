<?php
/**
 * Capivarinha_Curitibaninha - Proteção contra CSRF (Cross-Site Request Forgery)
 * Implementa tokens únicos de sessão para validação de formulários
 * Autor: Maoly Lara Serrano
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Gera um token CSRF se ainda não existir
 * @return string Token CSRF codificado em hexadecimal
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida o token CSRF contra o armazenado na sessão
 * @param string $token Token fornecido pelo cliente
 * @return bool True se válido, false caso contrário
 */
function validateCsrfToken($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }
    
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    
    return true;
}

/**
 * Valida o token CSRF e mata a execução se inválido
 * @param string $token Token fornecido pelo cliente (opcional, procura em $_POST)
 * @throws Exception Se o token for inválido
 */
function checkCsrfToken($token = null) {
    if (!validateCsrfToken($token)) {
        http_response_code(403);
        die("⚠️ Ação não autorizada (Token inválido). Atualize a página e tente novamente.");
    }
}

/**
 * Retorna um input HTML oculto com o token CSRF
 * @return string HTML do input hidden
 */
function csrfInput() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}
?>
