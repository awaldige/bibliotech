document.addEventListener('DOMContentLoaded', () => {
    // 1. Carregar autores e editoras nos selects
    async function carregarAuxiliares() {
        try {
            const res = await fetch('api/buscar_auxiliares.php');
            const data = await res.json();

            const selectAutor = document.getElementById('select-autor');
            const selectEditora = document.getElementById('select-editora');

            selectAutor.innerHTML = '<option value="">Selecione um Autor</option>' + 
                data.autores.map(a => `<option value="${a.id}">${a.nome}</option>`).join('');

            selectEditora.innerHTML = '<option value="">Selecione uma Editora</option>' + 
                data.editoras.map(e => `<option value="${e.id}">${e.nome}</option>`).join('');
        } catch (e) {
            console.error("Erro ao carregar auxiliares");
        }
    }

    // 2. Enviar Formulário
    const form = document.getElementById('form-adicionar-livro');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('api/salvar_livro.php', {
                method: 'POST',
                body: formData
            });

            const resultado = await response.json();
            
            if (resultado.sucesso) {
                alert("Livro cadastrado com sucesso!");
                window.location.href = 'livros.html';
            } else {
                alert("Erro ao salvar: " + resultado.erro);
            }
        } catch (error) {
            alert("Erro na conexão com o servidor.");
        }
    });

    carregarAuxiliares();
});
