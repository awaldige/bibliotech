
document.addEventListener('DOMContentLoaded', () => {
    carregarAutores();
    configurarCadastroAutor();
    configurarEdicaoAutor();
});

// Escapa dados antes de inseri-los no HTML.
function escaparHTML(valor) {
    return String(valor ?? '').replace(/[&<>"']/g, caractere => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[caractere]);
}

// 1. CARREGAR AUTORES
async function carregarAutores() {
    const tabela = document.getElementById('tabela-autores');

    try {
        const res = await fetch('api/listar_autores.php');

        if (!res.ok) {
            throw new Error('Erro ao consultar autores.');
        }

        const autores = await res.json();

        if (!Array.isArray(autores)) {
            throw new Error('Resposta inválida da API.');
        }

        if (autores.length === 0) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="3">Nenhum autor cadastrado.</td>
                </tr>
            `;
            return;
        }

        tabela.innerHTML = autores.map(autor => `
            <tr>
                <td><strong>${escaparHTML(autor.nome)}</strong></td>
                <td>${escaparHTML(autor.nacionalidade || 'Não informada')}</td>
                <td>
                    <button
                        type="button"
                        class="btn-icon edit"
                        data-acao="editar"
                        data-id="${escaparHTML(autor.id)}"
                        style="color:#3498db; background:none; border:none; cursor:pointer; margin-right:10px;"
                        aria-label="Editar autor"
                    >
                        <i class="fas fa-edit"></i>
                    </button>

                    <button
                        type="button"
                        class="btn-icon delete"
                        data-acao="excluir"
                        data-id="${escaparHTML(autor.id)}"
                        style="color:#e74c3c; background:none; border:none; cursor:pointer;"
                        aria-label="Excluir autor"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');

    } catch (erro) {
        console.error('Erro ao carregar autores:', erro);

        tabela.innerHTML = `
            <tr>
                <td colspan="3">Erro ao carregar autores.</td>
            </tr>
        `;
    }
}

// 2. EVENTOS DA TABELA
document.getElementById('tabela-autores').addEventListener('click', async evento => {
    const botao = evento.target.closest('button[data-acao]');

    if (!botao) return;

    const id = botao.dataset.id;
    const acao = botao.dataset.acao;

    if (acao === 'editar') {
        const res = await fetch('api/listar_autores.php');

        if (!res.ok) {
            alert('Não foi possível carregar os dados do autor.');
            return;
        }

        const autores = await res.json();
        const autor = autores.find(item => String(item.id) === id);

        if (!autor) {
            alert('Autor não encontrado.');
            return;
        }

        abrirModalAutor(autor.id, autor.nome, autor.nacionalidade || '');
    }

    if (acao === 'excluir') {
        excluirAutor(id);
    }
});

// 3. FUNÇÕES DO MODAL
function abrirModalAutor(id, nome, nacionalidade) {
    document.getElementById('edit-id-autor').value = id;
    document.getElementById('edit-nome-autor').value = nome;
    document.getElementById('edit-nacionalidade-autor').value = nacionalidade || '';

    document.getElementById('modalEditarAutor').style.display = 'flex';
}

function fecharModalAutor() {
    document.getElementById('modalEditarAutor').style.display = 'none';
}

// 4. SALVAR EDIÇÃO
function configurarEdicaoAutor() {
    document.getElementById('form-editar-autor').addEventListener('submit', async evento => {
        evento.preventDefault();

        const form = evento.target;
        const botao = form.querySelector('button[type="submit"]');

        const fd = new FormData();
        fd.append('id', document.getElementById('edit-id-autor').value);
        fd.append('nome', document.getElementById('edit-nome-autor').value);
        fd.append('nacionalidade', document.getElementById('edit-nacionalidade-autor').value);

        botao.disabled = true;

        try {
            const res = await fetch('api/editar_autor.php', {
                method: 'POST',
                body: fd
            });

            const data = await res.json();

            if (!res.ok || !data.sucesso) {
                throw new Error(data.erro || 'Não foi possível atualizar o autor.');
            }

            alert('Autor atualizado com sucesso!');
            fecharModalAutor();
            await carregarAutores();

        } catch (erro) {
            console.error('Erro ao editar autor:', erro);
            alert('Erro ao atualizar: ' + erro.message);

        } finally {
            botao.disabled = false;
        }
    });
}

// 5. CADASTRAR AUTOR
function configurarCadastroAutor() {
    document.getElementById('form-autor').addEventListener('submit', async evento => {
        evento.preventDefault();

        const form = evento.target;
        const botao = form.querySelector('button[type="submit"]');

        const fd = new FormData();
        fd.append('nome', document.getElementById('nome-autor').value);
        fd.append('nacionalidade', document.getElementById('nacionalidade-autor').value);

        botao.disabled = true;

        try {
            const res = await fetch('api/salvar_autor.php', {
                method: 'POST',
                body: fd
            });

            const data = await res.json();

            if (!res.ok || !data.sucesso) {
                throw new Error(data.erro || 'Não foi possível cadastrar o autor.');
            }

            alert('Autor cadastrado com sucesso!');
            form.reset();
            await carregarAutores();

        } catch (erro) {
            console.error('Erro ao cadastrar autor:', erro);
            alert('Erro ao cadastrar: ' + erro.message);

        } finally {
            botao.disabled = false;
        }
    });
}

// 6. EXCLUIR AUTOR
async function excluirAutor(id) {
    if (!confirm('Deseja excluir este autor?')) {
        return;
    }

    const fd = new FormData();
    fd.append('id', id);

    try {
        const res = await fetch('api/excluir_autor.php', {
            method: 'POST',
            body: fd
        });

        const data = await res.json();

        if (!res.ok || !data.sucesso) {
            throw new Error(data.erro || 'Não foi possível excluir o autor.');
        }

        alert('Autor excluído com sucesso!');
        await carregarAutores();

    } catch (erro) {
        console.error('Erro ao excluir autor:', erro);
        alert('Erro ao excluir: ' + erro.message);
    }
}
