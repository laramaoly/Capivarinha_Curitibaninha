/**
 * Capivarinha_Curitibaninha - Scripts Gerais da Aplicação
 * Responsável por validações, navegação e interações globais.
 * Autor: Maoly Lara Serrano
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // Inicialização das funções globais
    highlightCurrentPage();
    initFormValidations();
    initLogoutConfirmation();

    // Efeito sutil de parallax no fundo (opcional, para dar profundidade)
    document.addEventListener('mousemove', (e) => {
        const bg = document.querySelector('.background-container');
        if (bg) {
            const x = (window.innerWidth - e.pageX * 2) / 100;
            const y = (window.innerHeight - e.pageY * 2) / 100;
            bg.style.backgroundPosition = `calc(50% + ${x}px) calc(50% + ${y}px)`;
        }
    });
});

/**
 * 1. Gerencia o destaque (classe 'active') no menu inferior
 * Baseado na query string da URL (?page=...)
 */
function highlightCurrentPage() {
    const params = new URLSearchParams(window.location.search);
    const currentPage = params.get('page') || 'game'; // 'game' é a home padrão
    
    // Mapeamento: página -> índice do menu (ou seletor específico)
    const menuMap = {
        'game': 0,
        'ranking': 1,
        'leagues': 2
    };

    const navItems = document.querySelectorAll('.bottom-nav .nav-item');
    
    // Remove ativo de todos
    navItems.forEach(item => item.classList.remove('active'));

    // Adiciona ao atual se existir no mapa
    if (menuMap.hasOwnProperty(currentPage)) {
        if (navItems[menuMap[currentPage]]) {
            navItems[menuMap[currentPage]].classList.add('active');
        }
    }
}

/**
 * 2. Validação de Formulários (Login e Cadastro)
 * Requisito: "Validação de campos de formulário"
 */
function initFormValidations() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            let firstErrorInput = null;

            // Limpa erros anteriores
            form.querySelectorAll('.error-msg').forEach(el => el.remove());
            form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

            // Validação genérica para campos 'required'
            const requiredInputs = form.querySelectorAll('input[required]');
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    showError(input, 'Bah, piá! Esse campo não pode ficar vazio.');
                    isValid = false;
                    if (!firstErrorInput) firstErrorInput = input;
                }
            });

            // Validação específica de Email
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput && emailInput.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value)) {
                    showError(emailInput, 'Eita! Digite um e-mail válido, né?');
                    isValid = false;
                    if (!firstErrorInput) firstErrorInput = emailInput;
                }
            }

            // Validação de Confirmação de Senha (apenas no cadastro)
            const passInput = form.querySelector('input[name="senha"]');
            const confirmPassInput = form.querySelector('input[name="confirmar_senha"]');
            
            if (passInput && confirmPassInput) {
                if (passInput.value !== confirmPassInput.value) {
                    showError(confirmPassInput, 'As senhas não batem! Tenta de novo.');
                    isValid = false;
                    if (!firstErrorInput) firstErrorInput = confirmPassInput;
                }
            }

            // Validação de Tamanho de Senha
            if (passInput && passInput.value.length > 0 && passInput.value.length < 6) {
                showError(passInput, 'A senha precisa ter pelo menos 6 caracteres.');
                isValid = false;
                if (!firstErrorInput) firstErrorInput = passInput;
            }

            if (!isValid) {
                e.preventDefault(); // Impede o envio se houver erros
                if (firstErrorInput) firstErrorInput.focus();
                
                // Feedback visual de "tremida" no form
                form.classList.add('shake-animation');
                setTimeout(() => form.classList.remove('shake-animation'), 500);
            }
        });
    });
}

/**
 * Helper para mostrar mensagem de erro abaixo do input
 */
function showError(inputElement, message) {
    inputElement.classList.add('input-error');
    
    const errorSmall = document.createElement('small');
    errorSmall.className = 'error-msg';
    errorSmall.style.color = '#D32F2F';
    errorSmall.style.display = 'block';
    errorSmall.style.marginTop = '5px';
    errorSmall.style.fontSize = '0.85rem';
    errorSmall.innerText = message;
    
    // Insere o erro logo após o input
    inputElement.parentNode.insertBefore(errorSmall, inputElement.nextSibling);
}

/**
 * 3. Confirmação de Logout
 * Evita sair sem querer
 */
function initLogoutConfirmation() {
    const logoutBtn = document.querySelector('a[href*="logout"]'); // Busca link com 'logout'
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            if (!confirm('Vai dar o perdido mesmo? Tem certeza que quer sair?')) {
                e.preventDefault();
            }
        });
    }
    
    // Alternativa: Se for o item do menu inferior
    const logoutNavItem = document.querySelector('.nav-item[onclick*="logout"]');
    if (logoutNavItem) {
        // Sobrescreve o onclick inline para adicionar confirmação
        logoutNavItem.onclick = (e) => {
            e.preventDefault();
            if (confirm('Vai dar o perdido mesmo? Tem certeza que quer sair?')) {
                window.location.href = 'logout.php';
            }
        };
    }
}

// Estilo CSS extra para a validação (injetado via JS ou poderia ir no style.css)
const style = document.createElement('style');
style.innerHTML = `
    .input-error {
        border-color: #D32F2F !important;
        background-color: #FFEBEE !important;
    }
    .shake-animation {
        animation: shake 0.5s;
    }
`;
document.head.appendChild(style);