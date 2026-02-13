document.addEventListener('DOMContentLoaded', carregarLivros);

// 1. CARREGAR LIVROS NA TABELA
async function carregarLivros() {
    const tabela = document.getElementById('tabela-livros');
    try {
        const res = await fetch('api/listar_livros.php');
        const livros = await res.json();

        if (livros.length === 0) {
            tabela.innerHTML = '<tr><td colspan="6" style="text-align:center;">Nenhum livro cadastrado.</td></tr>';
            return;
        }

        // CORREÇÃO AQUI: l.nome_autor e l.nome_editora (conforme seu PHP)
        tabela.innerHTML = livros.map(l => `
            <tr>
                <td><i class="fas fa-book" style="color:#3498db;"></i></td>
                <td><strong>${l.titulo}</strong></td>
                <td>${l.nome_autor || 'Não informado'}</td>
                <td>${l.nome_editora || 'Não informado'}</td>
                <td>${l.quantidade}</td>
                <td>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="abrirModal(${l.id}, '${l.titulo.replace(/'/g, "\\'")}', ${l.quantidade})" class="btn-icon edit" style="background:none; border:none; color:#3498db; cursor:pointer;" title="Editar">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        
                        <button onclick="excluirLivro(${l.id})" class="btn-icon delete" style="background:none; border:none; color:#e74c3c; cursor:pointer;" title="Excluir">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        console.error(e);
        tabela.innerHTML = '<tr><td colspan="6" style="color:red;">Erro ao carregar dados.</td></tr>';
    }
}

// 2. FUNÇÕES DO MODAL (EDITAR)
function abrirModal(id, titulo, qtd) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-titulo').value = titulo;
    document.getElementById('edit-qtd').value = qtd;
    document.getElementById('modalEditarLivro').style.display = 'flex';
}

function fecharModal() {
    document.getElementById('modalEditarLivro').style.display = 'none';
}

// 3. SALVAR EDIÇÃO
document.getElementById('form-editar-livro').addEventListener('submit', async (e) => {
    e.preventDefault();
    const dados = new FormData();
    dados.append('id', document.getElementById('edit-id').value);
    dados.append('titulo', document.getElementById('edit-titulo').value);
    dados.append('quantidade', document.getElementById('edit-qtd').value);

    const res = await fetch('api/editar_livro.php', { method: 'POST', body: dados });
    const result = await res.json();

    if(result.sucesso) {
        alert("✅ Livro atualizado!");
        fecharModal();
        carregarLivros();
    } else {
        alert("❌ Erro ao editar: " + result.erro);
    }
});

// 4. FUNÇÃO EXCLUIR
async function excluirLivro(id) {
    if (confirm("Tem certeza que deseja excluir este livro? Esta ação não pode ser desfeita.")) {
        try {
            const res = await fetch(`api/excluir_livro.php?id=${id}`);
            const result = await res.json();

            if (result.sucesso) {
                alert("✅ Livro removido com sucesso!");
                carregarLivros(); 
            } else {
                alert("❌ Erro ao excluir: " + result.erro);
            }
        } catch (error) {
            alert("⚠️ Erro de conexão com o servidor.");
        }
    }
}