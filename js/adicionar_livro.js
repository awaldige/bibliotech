
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-adicionar-livro');
    const selectAutor = document.getElementById('select-autor');
    const selectEditora = document.getElementById('select-editora');
    const botaoSalvar = form.querySelector('[type="submit"]');

    // Carrega autores e editoras.
    async function carregarAuxiliares() {
        try {
            const resposta = await fetch('api/buscar_auxiliares.php');

            if (!resposta.ok) {
                throw new Error('Falha ao consultar autores e editoras.');
            }

            const dados = await resposta.json();

            if (!Array.isArray(dados.autores) ||
                !Array.isArray(dados.editoras)) {
                throw new Error('Formato de dados inválido.');
            }

            preencherSelect(selectAutor, dados.autores, 'Selecione um autor');
            preencherSelect(selectEditora, dados.editoras, 'Selecione uma editora');

        } catch (erro) {
            console.error('Erro ao carregar auxiliares:', erro);

            selectAutor.replaceChildren(
                new Option('Não foi possível carregar autores', '')
            );

            selectEditora.replaceChildren(
                new Option('Não foi possível carregar editoras', '')
            );
        }
    }

    // Preenche os selects com segurança.
    function preencherSelect(select, itens, mensagem) {
        select.replaceChildren(new Option(mensagem, ''));

        itens.forEach(item => {
            const opcao = new Option(
                String(item.nome ?? '').trim(),
                String(item.id ?? '')
            );

            select.add(opcao);
        });
    }

    // Envia o cadastro.
    form.addEventListener('submit', async evento => {
        evento.preventDefault();

        if (!form.reportValidity()) {
            return;
        }

        const textoOriginal = botaoSalvar.innerHTML;

        botaoSalvar.disabled = true;
        botaoSalvar.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Salvando...';

        try {
            const resposta = await fetch('api/salvar_livro.php', {
                method: 'POST',
                body: new FormData(form)
            });

            const resultado = await resposta.json();

            if (!resposta.ok || !resultado.sucesso) {
                throw new Error(
                    resultado.erro || 'Não foi possível cadastrar o livro.'
                );
            }

            alert('Livro cadastrado com sucesso!');
            window.location.href = 'livros.html';

        } catch (erro) {
            console.error('Erro ao salvar livro:', erro);
            alert(erro.message || 'Erro na conexão com o servidor.');

        } finally {
            botaoSalvar.disabled = false;
            botaoSalvar.innerHTML = textoOriginal;
        }
    });

    carregarAuxiliares();
});
