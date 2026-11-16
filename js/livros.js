
document.addEventListener('DOMContentLoaded', () => {
    carregarLivros();

    const formulario = document.getElementById('form-editar-livro');

    if (formulario) {
        formulario.addEventListener('submit', salvarEdicaoLivro);
    }

    const tabela = document.getElementById('tabela-livros');

    if (tabela) {
        tabela.addEventListener('click', tratarAcaoLivro);
    }

    const modal = document.getElementById('modalEditarLivro');

    if (modal) {
        modal.addEventListener('click', (evento) => {
            if (evento.target === modal) {
                fecharModal();
            }
        });
    }

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            fecharModal();
        }
    });
});

// 1. CARREGAR LIVROS NA TABELA

async function carregarLivros() {
    const tabela = document.getElementById('tabela-livros');

    if (!tabela) return;

    try {
        const resposta = await fetch('api/listar_livros.php');

        if (!resposta.ok) {
            throw new Error('Não foi possível consultar o acervo.');
        }

        const livros = await resposta.json();

        if (!Array.isArray(livros)) {
            throw new Error('Resposta inválida da API de livros.');
        }

        tabela.replaceChildren();

        if (livros.length === 0) {
            const linha = document.createElement('tr');
            const celula = document.createElement('td');

            celula.colSpan = 6;
            celula.className = 'table-empty';
            celula.textContent = 'Nenhum livro cadastrado no acervo.';

            linha.appendChild(celula);
            tabela.appendChild(linha);
            return;
        }

        livros.forEach(livro => {
            const linha = document.createElement('tr');

            // Ícone do livro
            const celulaIcone = document.createElement('td');
            const icone = document.createElement('i');

            icone.className = 'fas fa-book book-row-icon';
            icone.setAttribute('aria-hidden', 'true');

            celulaIcone.appendChild(icone);

            // Título
            const celulaTitulo = document.createElement('td');
            const titulo = document.createElement('strong');

            titulo.className = 'book-title';
            titulo.textContent = livro.titulo || 'Sem título';

            celulaTitulo.appendChild(titulo);

            // Autor
            const celulaAutor = document.createElement('td');
            celulaAutor.textContent = livro.nome_autor || 'Não informado';

            // Editora
            const celulaEditora = document.createElement('td');
            celulaEditora.textContent = livro.nome_editora || 'Não informado';

            // Quantidade
            const celulaQuantidade = document.createElement('td');
            const quantidade = document.createElement('span');

            quantidade.className = 'book-quantity';
            quantidade.textContent = Number(livro.quantidade) || 0;

            celulaQuantidade.appendChild(quantidade);

            // Ações
            const celulaAcoes = document.createElement('td');
            const grupoAcoes = document.createElement('div');

            grupoAcoes.className = 'book-actions';

            const botaoEditar = document.createElement('button');

            botaoEditar.type = 'button';
            botaoEditar.className = 'btn-icon edit';
            botaoEditar.dataset.acao = 'editar';
            botaoEditar.dataset.id = livro.id;
            botaoEditar.dataset.titulo = livro.titulo || '';
            botaoEditar.dataset.quantidade = livro.quantidade;

            botaoEditar.title = 'Editar livro';
            botaoEditar.setAttribute('aria-label', `Editar ${livro.titulo || 'livro'}`);

            botaoEditar.innerHTML = '<i class="fas fa-edit" aria-hidden="true"></i> Editar';

            const botaoExcluir = document.createElement('button');

            botaoExcluir.type = 'button';
            botaoExcluir.className = 'btn-icon delete';
            botaoExcluir.dataset.acao = 'excluir';
            botaoExcluir.dataset.id = livro.id;

            botaoExcluir.title = 'Excluir livro';
            botaoExcluir.setAttribute('aria-label', `Excluir ${livro.titulo || 'livro'}`);

            botaoExcluir.innerHTML = '<i class="fas fa-trash" aria-hidden="true"></i> Excluir';

            grupoAcoes.append(botaoEditar, botaoExcluir);
            celulaAcoes.appendChild(grupoAcoes);

            linha.append(
                celulaIcone,
                celulaTitulo,
                celulaAutor,
                celulaEditora,
                celulaQuantidade,
                celulaAcoes
            );

            tabela.appendChild(linha);
        });

    } catch (erro) {
        console.error('Erro ao carregar livros:', erro);

        tabela.replaceChildren();

        const linha = document.createElement('tr');
        const celula = document.createElement('td');

        celula.colSpan = 6;
        celula.className = 'table-error';
        celula.textContent = 'Não foi possível carregar os livros. Tente novamente.';

        linha.appendChild(celula);
        tabela.appendChild(linha);
    }
}

// 2. TRATAR AÇÕES DA TABELA

async function tratarAcaoLivro(evento) {
    const botao = evento.target.closest('button[data-acao]');

    if (!botao) return;

    const id = botao.dataset.id;

    if (botao.dataset.acao === 'editar') {
        abrirModal(
            id,
            botao.dataset.titulo,
            botao.dataset.quantidade
        );
    }

    if (botao.dataset.acao === 'excluir') {
        await excluirLivro(id);
    }
}

// 3. FUNÇÕES DO MODAL

function abrirModal(id, titulo, quantidade) {
    const modal = document.getElementById('modalEditarLivro');

    if (!modal) return;

    document.getElementById('edit-id').value = id;
    document.getElementById('edit-titulo').value = titulo;
    document.getElementById('edit-qtd').value = quantidade;

    modal.style.display = 'flex';
    document.getElementById('edit-titulo').focus();
}

function fecharModal() {
    const modal = document.getElementById('modalEditarLivro');

    if (modal) {
        modal.style.display = 'none';
    }
}

// 4. SALVAR EDIÇÃO

async function salvarEdicaoLivro(evento) {
    evento.preventDefault();

    const botaoSalvar = evento.currentTarget.querySelector('[type="submit"]');

    const dados = new FormData();

    dados.append('id', document.getElementById('edit-id').value);
    dados.append('titulo', document.getElementById('edit-titulo').value.trim());
    dados.append('quantidade', document.getElementById('edit-qtd').value);

    if (!dados.get('titulo')) {
        alert('Informe o título do livro.');
        return;
    }

    try {
        botaoSalvar.disabled = true;

        const resposta = await fetch('api/editar_livro.php', {
            method: 'POST',
            body: dados
        });

        const resultado = await resposta.json();

        if (!resposta.ok || !resultado.sucesso) {
            throw new Error(resultado.erro || 'Não foi possível atualizar o livro.');
        }

        alert('Livro atualizado com sucesso!');

        fecharModal();
        await carregarLivros();

    } catch (erro) {
        console.error('Erro ao editar livro:', erro);
        alert('Erro ao editar: ' + erro.message);

    } finally {
        botaoSalvar.disabled = false;
    }
}

// 5. EXCLUIR LIVRO

async function excluirLivro(id) {
    const confirmar = confirm(
        'Tem certeza que deseja excluir este livro? ' +
        'Livros com histórico de empréstimos ou reservas não podem ser excluídos.'
    );

    if (!confirmar) return;

    try {
        const dados = new FormData();
        dados.append('id', id);

        const resposta = await fetch('api/deletar_livro.php', {
            method: 'POST',
            body: dados
        });

        const resultado = await resposta.json();

        if (!resposta.ok || !resultado.sucesso) {
            throw new Error(resultado.erro || 'Falha ao excluir o livro.');
        }

        alert('Livro removido com sucesso!');

        await carregarLivros();

    } catch (erro) {
        console.error('Erro ao excluir livro:', erro);
        alert('Erro ao excluir: ' + erro.message);
    }
}
