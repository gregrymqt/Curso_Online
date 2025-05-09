// Validação do formulário no cliente
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            
            // Validação básica de campos obrigatórios
            const requiredInputs = form.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = 'red';
                    valid = false;
                } else {
                    input.style.borderColor = '';
                }
            });
            
            // Validação específica para senhas
            if (form.querySelector('#senha') && form.querySelector('#confirmar_senha')) {
                const senha = form.querySelector('#senha').value;
                const confirmarSenha = form.querySelector('#confirmar_senha').value;
                
                if (senha !== confirmarSenha) {
                    alert('As senhas não coincidem!');
                    valid = false;
                }
                
                if (senha.length < 6) {
                    alert('A senha deve ter pelo menos 6 caracteres!');
                    valid = false;
                }
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    });
    
    // Máscara para telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Formatação: (XX) XXXXX-XXXX
            if (value.length > 2) {
                value = `(${value.substring(0, 2)}) ${value.substring(2)}`;
            }
            if (value.length > 10) {
                value = `${value.substring(0, 10)}-${value.substring(10)}`;
            }
            
            e.target.value = value;
        });
    }
    
    // Carregar mais conteúdo (para a área de vídeos)
    const loadMoreBtn = document.getElementById('load-more');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            // Implementar AJAX para carregar mais conteúdo
            console.log('Carregar mais conteúdo...');
        });
    }
});


document.addEventListener('DOMContentLoaded', function() {
    // Preview da imagem antes de enviar
    const fotoInput = document.getElementById('foto_perfil');
    const previewImg = document.getElementById('profile-pic-preview');
    
    if (fotoInput && previewImg) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    
                    // Mostra mensagem de carregamento
                    const loadingMsg = document.createElement('div');
                    loadingMsg.className = 'alert info';
                    loadingMsg.textContent = 'Enviando foto...';
                    previewImg.parentNode.insertBefore(loadingMsg, previewImg.nextSibling);
                    
                    // Envia o formulário automaticamente
                    const form = fotoInput.closest('form');
                    if (form) {
                        form.submit();
                    }
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
});