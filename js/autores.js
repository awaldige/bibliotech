document.addEventListener('DOMContentLoaded', () => {
    carregarAutores();
    configurarCadastroAutor();
    configurarEdicaoAutor();
});

// 1. CARREGAR AUTORES
async function carregarAutores() {
    const tabela = document.getElementById('tabela-autores');
    try {
        const res = await fetch('api/listar_autores.php');
        const autores = await res.json();

        tabela.innerHTML = autores.map(a => `
            <tr>
                <td><strong>${a.nome}</strong></td>
                <td>${a.nacionalidade || 'Não informada'}</td>
                <td>
                    <button onclick="abrirModalAutor(${a.id}, '${a.nome}', '${a.nacionalidade || ''}')" class="btn-icon edit" style="color:#3498db; background:none; border:none; cursor:pointer; margin-right:10px;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="excluirAutor(${a.id})" class="btn-icon delete" style="color:#e74c3c; background:none; border:none; cursor:pointer;">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tabela.innerHTML = '<tr><td colspan="3">Erro ao carregar autores.</td></tr>';
    }
}

// 2. FUNÇÕES DO MODAL
function abrirModalAutor(id, nome, nacionalidade) {
    document.getElementById('edit-id-autor').value = id;
    document.getElementById('edit-nome-autor').value = nome;
    document.getElementById('edit-nacionalidade-autor').value = nacionalidade;
    document.getElementById('modalEditarAutor').style.display = 'flex';
}

function fecharModalAutor() {
    document.getElementById('modalEditarAutor').style.display = 'none';
}

// 3. SALVAR EDIÇÃO
function configurarEdicaoAutor() {
    document.getElementById('form-editar-autor').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('id', document.getElementById('edit-id-autor').value);
        fd.append('nome', document.getElementById('edit-nome-autor').value);
        fd.append('nacionalidade', document.getElementById('edit-nacionalidade-autor').value);

        const res = await fetch('api/editar_autor.php', { method: 'POST', body: fd });
        const data = await res.json();
        if(data.sucesso) {
            alert("✅ Autor atualizado!");
            fecharModalAutor();
            carregarAutores();
        }
    });
}

// 4. CADASTRAR AUTOR
function configurarCadastroAutor() {
    document.getElementById('form-autor').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('nome', document.getElementById('nome-autor').value);
        fd.append('nacionalidade', document.getElementById('nacionalidade-autor').value);

        const res = await fetch('api/salvar_autor.php', { method: 'POST', body: fd });
        const data = await res.json();
        if(data.sucesso) {
            alert("✅ Autor cadastrado!");
            e.target.reset();
            carregarAutores();
        }
    });
}

// 5. EXCLUIR AUTOR
async function excluirAutor(id) {
    if(confirm("Deseja excluir este autor?")) {
        const res = await fetch(`api/excluir_autor.php?id=${id}`);
        const data = await res.json();
        if(data.sucesso) carregarAutores();
        else alert("Erro: " + data.erro);
    }
}