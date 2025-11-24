<?php
/**
 * Capivarinha_Curitibaninha - Rodapé Padrão
 * Responsável por fechar o corpo do HTML e carregar scripts globais.
 * Autor: Maoly Lara Serrano
 */

// Impede acesso direto ao arquivo
if (basename($_SERVER['PHP_SELF']) == 'footer.php') {
    die('Acesso direto não permitido');
}
?>
    <!-- Scripts Globais (Validações, Navegação, UI) -->
    <script src="assets/js/main.js"></script>

    <!-- 
       Nota: Scripts específicos (como game.js) devem ser incluídos 
       nas suas respectivas views antes deste footer ou gerenciados condicionalmente.
    -->
</body>
</html>