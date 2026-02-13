document.addEventListener('DOMContentLoaded', () => {
    const tabela = document.getElementById('tabela-editoras');
    const form = document.getElementById('form-editora');
    const formEdicao = document.getElementById('form-editar-editora');

    // 1. Função para carregar editoras
    async function carregarEditoras() {
        try {
            const res = await fetch('api/listar_editoras.php');
            const editoras = await res.json();
            
            tabela.innerHTML = editoras.map(e => `
                <tr>
                    <td><strong>${e.nome}</strong></td>
                    <td>${e.localizacao || '---'}</td>
                    <td>
                        <button onclick="prepararEdicao(${e.id}, '${e.nome}', '${e.localizacao || ''}')" class="btn-icon" style="color:#3498db; border:none; background:none; cursor:pointer; margin-right:10px;">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button onclick="deletarEditora(${e.id})" class="btn-icon delete" style="color:#e74c3c; border:none; background:none; cursor:pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } catch (error) {
            tabela.innerHTML = '<tr><td colspan="3" style="text-align:center;">Erro ao carregar editoras.</td></tr>';
        }
    }

    // 2. Gravar nova editora
    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const nome = document.getElementById('nome-editora').value;
            const localizacao = document.getElementById('local-editora').value;

            try {
                const res = await fetch('api/salvar_editora.php', {
                    method: 'POST',
                    body: JSON.stringify({ nome, localizacao }),
                    headers: { 'Content-Type': 'application/json' }
                });

                const data = await res.json();
                if(data.sucesso) {
                    form.reset();
                    carregarEditoras();
                }
            } catch (e) {
                alert("Erro ao comunicar com o servidor.");
            }
        });
    }

    // 3. Salvar Edição
    if (formEdicao) {
        formEdicao.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('edit-id').value;
            const nome = document.getElementById('edit-nome').value;
            const localizacao = document.getElementById('edit-local').value;

            try {
                const res = await fetch('api/editar_editora.php', {
                    method: 'POST',
                    body: JSON.stringify({ id, nome, localizacao }),
                    headers: { 'Content-Type': 'application/json' }
                });

                const data = await res.json();
                if(data.sucesso) {
                    alert("Editora atualizada!");
                    fecharModal('modalEditarEditora');
                    carregarEditoras();
                } else {
                    alert("Erro: " + data.erro);
                }
            } catch (err) {
                alert("Erro ao editar editora.");
            }
        });
    }

    // Inicializa a tabela
    carregarEditoras();
});

/**
 * FUNÇÕES GLOBAIS (Fora do DOMContentLoaded para o onclick funcionar)
 */

function prepararEdicao(id, nome, local) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nome').value = nome;
    document.getElementById('edit-local').value = local;
    document.getElementById('modalEditarEditora').style.display = 'flex';
}

function fecharModal(id) {
    document.getElementById(id).style.display = 'none';
}

async function deletarEditora(id) {
    if(confirm("Deseja realmente excluir esta editora?")) {
        try {
            const res = await fetch(`api/deletar_editora.php?id=${id}`);
            const data = await res.json();
            if(data.sucesso) location.reload();
        } catch (e) { alert("Erro ao deletar."); }
    }
}