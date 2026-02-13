/**
 * ARQUIVO: js/emprestimos.js
 * Descrição: Gerencia a listagem, cadastro, devolução e renovação de livros.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Configura data padrão para +7 dias no formulário
    const dtDevolucao = document.getElementById('data-devolucao');
    if (dtDevolucao) {
        const hoje = new Date();
        hoje.setDate(hoje.getDate() + 7);
        dtDevolucao.value = hoje.toISOString().split('T')[0];
    }

    // 2. Inicializa os dados da página
    popularSelects();
    carregarEmprestimosAtivos();

    // 3. Ouvinte do formulário de novo empréstimo
    const form = document.getElementById('form-emprestimo');
    if (form) {
        form.addEventListener('submit', salvarEmprestimo);
    }
});

/**
 * BUSCA LIVROS E LEITORES PARA OS CAMPOS DE SELEÇÃO
 */
async function popularSelects() {
    try {
        const resLivros = await fetch('api/listar_livros.php');
        const livros = await resLivros.json();
        const selectLivro = document.getElementById('select-livro');
        
        if (selectLivro) {
            selectLivro.innerHTML = '<option value="">Selecione um livro...</option>' + 
                livros.map(l => `<option value="${l.id}">${l.titulo}</option>`).join('');
        }

        const resLeitores = await fetch('api/listar_usuarios.php'); 
        const leitores = await resLeitores.json();
        const selectLeitor = document.getElementById('select-leitor');

        if (selectLeitor) {
            selectLeitor.innerHTML = '<option value="">Selecione um leitor...</option>' + 
                leitores.map(u => `<option value="${u.id}">${u.nome}</option>`).join('');
        }
    } catch (e) {
        console.error("Erro ao popular selects:", e);
    }
}

/**
 * CARREGA A TABELA DE EMPRÉSTIMOS ATIVOS
 */
async function carregarEmprestimosAtivos() {
    const tabela = document.getElementById('tabela-emprestimos');
    if (!tabela) return;

    try {
        const res = await fetch('api/listar_emprestimos.php?t=' + new Date().getTime());
        if (!res.ok) throw new Error("Arquivo listar_emprestimos.php não encontrado.");

        const dados = await res.json();
        
        if (!Array.isArray(dados) || dados.length === 0) {
            tabela.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#666;">Nenhum empréstimo ativo no momento.</td></tr>';
            return;
        }

        tabela.innerHTML = dados.map(emp => `
            <tr>
                <td><strong>${emp.leitor}</strong></td>
                <td>${emp.livro}</td>
                <td>${new Date(emp.data_emprestimo).toLocaleDateString('pt-BR')}</td>
                <td>
                    <span style="color:#e67e22; font-weight:bold;">
                        ${new Date(emp.data_devolucao_prevista).toLocaleDateString('pt-BR')}
                    </span>
                </td>
                <td>
                    <button onclick="renovarEmprestimo(${emp.id})" class="btn-icon" title="Renovar +7 dias" style="color:#3498db; background:none; border:none; cursor:pointer; font-size:1.1rem; margin-right:12px;">
                        <i class="fas fa-sync-alt"></i>
                    </button>

                    <button onclick="confirmarDevolucao(${emp.id})" class="btn-icon" title="Dar Baixa" style="color:#27ae60; background:none; border:none; cursor:pointer; font-size:1.1rem;">
                        <i class="fas fa-check-circle"></i>
                    </button>
                </td>
            </tr>
        `).join('');

    } catch (e) { 
        console.error("Erro ao carregar empréstimos:", e);
        tabela.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center; padding:20px;">Erro ao carregar dados: ${e.message}</td></tr>`; 
    }
}

/**
 * FUNÇÃO PARA RENOVAR EMPRÉSTIMO (+7 DIAS)
 */
async function renovarEmprestimo(id) {
    if (confirm("Deseja renovar este empréstimo por mais 7 dias a partir de hoje?")) {
        try {
            const res = await fetch(`api/renovar_emprestimo.php?id=${id}`);
            const data = await res.json();

            if (data.sucesso) {
                alert("✅ Empréstimo renovado com sucesso!");
                carregarEmprestimosAtivos();
            } else {
                alert("❌ Erro ao renovar: " + data.erro);
            }
        } catch (e) {
            console.error("Erro na renovação:", e);
            alert("❌ Erro de conexão com o servidor.");
        }
    }
}

/**
 * ENVIA O FORMULÁRIO DE EMPRÉSTIMO
 */
async function salvarEmprestimo(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btn-confirmar');
    const idLivro = document.getElementById('select-livro').value;
    const idLeitor = document.getElementById('select-leitor').value;
    const dataDev = document.getElementById('data-devolucao').value;

    if (!idLivro || !idLeitor) {
        alert("Por favor, selecione o livro e o leitor.");
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gravando...';

    const formData = new FormData();
    formData.append('id_livro', idLivro);
    formData.append('id_leitor', idLeitor);
    formData.append('data_devolucao', dataDev);

    try {
        const res = await fetch('api/salvar_emprestimo.php', { 
            method: 'POST', 
            body: formData 
        });
        const data = await res.json();

        if (data.sucesso) {
            alert("✅ Empréstimo registrado com sucesso!");
            location.reload(); 
        } else {
            alert("❌ Erro: " + (data.erro || "Falha ao salvar"));
            btn.disabled = false;
            btn.innerHTML = 'Confirmar Saída';
        }
    } catch (err) {
        console.error("Erro na requisição:", err);
        alert("❌ Erro de conexão com o servidor.");
        btn.disabled = false;
        btn.innerHTML = 'Confirmar Saída';
    }
}

/**
 * FUNÇÃO DE BAIXA (DEVOLUÇÃO)
 */
async function confirmarDevolucao(id) {
    if (confirm("Deseja confirmar a devolução deste livro?")) {
        try {
            const res = await fetch(`api/devolver_livro.php?id=${id}`);
            const data = await res.json();
            
            if (data.sucesso) {
                carregarEmprestimosAtivos();
                if (typeof atualizarContadores === 'function') atualizarContadores();
            } else {
                alert("Erro ao processar devolução: " + data.erro);
            }
        } catch (e) {
            alert("Erro na comunicação com o servidor.");
        }
    }
}