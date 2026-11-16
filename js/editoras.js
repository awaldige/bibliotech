
document.addEventListener('DOMContentLoaded', () => {
    const tabela = document.getElementById('tabela-editoras');
    const form = document.getElementById('form-editora');
    const formEdicao = document.getElementById('form-editar-editora');

    let editoras = [];

    // Protege os valores inseridos no HTML.
    function escaparHTML(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, caractere => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[caractere]);
    }

    // Carrega e exibe as editoras.
    async function carregarEditoras() {
        try {
            const res = await fetch('api/listar_editoras.php');

            if (!res.ok) {
                throw new Error('Falha ao consultar editoras.');
            }

            const dados = await res.json();

            if (!Array.isArray(dados)) {
                throw new Error(dados.erro || 'Resposta inválida do servidor.');
            }

            editoras = dados;

            tabela.innerHTML = editoras.map(editora => `
                <tr>
                    <td><strong>${escaparHTML(editora.nome)}</strong></td>
                    <td>${escaparHTML(editora.localizacao || '---')}</td>
                    <td>
                        <button
                            type="button"
                            class="btn-icon"
                            data-acao="editar"
                            data-id="${Number(editora.id)}"
                            aria-label="Editar editora"
                            style="color:#3498db; border:none; background:none; cursor:pointer; margin-right:10px;">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button
                            type="button"
                            class="btn-icon delete"
                            data-acao="excluir"
                            data-id="${Number(editora.id)}"
                            aria-label="Excluir editora"
                            style="color:#e74c3c; border:none; background:none; cursor:pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

        } catch (error) {
            console.error('Erro ao carregar editoras:', error);

            tabela.innerHTML = `
                <tr>
                    <td colspan="3" style="text-align:center;">
                        Erro ao carregar editoras.
                    </td>
                </tr>
            `;
        }
    }

    // Cadastra uma nova editora usando JSON.
    if (form) {
        form.addEventListener('submit', async event => {
            event.preventDefault();

            const nome = document.getElementById('nome-editora').value;
            const localizacao = document.getElementById('local-editora').value;

            try {
                const res = await fetch('api/salvar_editora.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ nome, localizacao })
                });

                const dados = await res.json();

                if (!res.ok || !dados.sucesso) {
                    throw new Error(dados.erro || 'Não foi possível cadastrar a editora.');
                }

                alert('Editora cadastrada com sucesso!');
                form.reset();
                await carregarEditoras();

            } catch (error) {
                alert(error.message || 'Erro ao cadastrar editora.');
            }
        });
    }

    // Salva a edição de uma editora.
    if (formEdicao) {
        formEdicao.addEventListener('submit', async event => {
            event.preventDefault();

            const id = document.getElementById('edit-id').value;
            const nome = document.getElementById('edit-nome').value;
            const localizacao = document.getElementById('edit-local').value;

            try {
                const res = await fetch('api/editar_editora.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id, nome, localizacao })
                });

                const dados = await res.json();

                if (!res.ok || !dados.sucesso) {
                    throw new Error(dados.erro || 'Não foi possível atualizar a editora.');
                }

                alert('Editora atualizada com sucesso!');
                fecharModal('modalEditarEditora');
                await carregarEditoras();

            } catch (error) {
                alert(error.message || 'Erro ao editar editora.');
            }
        });
    }

    // Trata os cliques nos botões da tabela.
    if (tabela) {
        tabela.addEventListener('click', async event => {
            const botao = event.target.closest('button[data-acao]');

            if (!botao || !tabela.contains(botao)) {
                return;
            }

            const id = Number(botao.dataset.id);
            const acao = botao.dataset.acao;

            if (!Number.isInteger(id) || id <= 0) {
                alert('ID da editora inválido.');
                return;
            }

            if (acao === 'editar') {
                const editora = editoras.find(item => Number(item.id) === id);

                if (!editora) {
                    alert('Editora não encontrada na listagem.');
                    return;
                }

                document.getElementById('edit-id').value = editora.id;
                document.getElementById('edit-nome').value = editora.nome;
                document.getElementById('edit-local').value = editora.localizacao || '';

                document.getElementById('modalEditarEditora').style.display = 'flex';
            }

            if (acao === 'excluir') {
                await excluirEditora(id);
            }
        });
    }

    // Exclui somente por POST.
    async function excluirEditora(id) {
        if (!confirm('Deseja realmente excluir esta editora?')) {
            return;
        }

        const formData = new FormData();
        formData.append('id', id);

        try {
            const res = await fetch('api/deletar_editora.php', {
                method: 'POST',
                body: formData
            });

            const dados = await res.json();

            if (!res.ok || !dados.sucesso) {
                throw new Error(dados.erro || 'Não foi possível excluir a editora.');
            }

            alert('Editora excluída com sucesso!');
            await carregarEditoras();

        } catch (error) {
            alert(error.message || 'Erro ao excluir editora.');
        }
    }

    // Inicializa a tabela.
    carregarEditoras();
});

// Mantém compatibilidade com o botão cancelar do modal.
function fecharModal(id) {
    const modal = document.getElementById(id);

    if (modal) {
        modal.style.display = 'none';
    }
}
