document.addEventListener('DOMContentLoaded', () => {
    carregarUsuarios();
    configurarCadastro();
    configurarEdicao();
});

async function carregarUsuarios() {
    const tabela = document.getElementById('tabela-usuarios');
    try {
        const res = await fetch('api/listar_usuarios.php');
        const usuarios = await res.json();

        tabela.innerHTML = usuarios.map(user => `
            <tr>
                <td><i class="fas fa-user-circle" style="color: #3498db;"></i></td>
                <td><strong>${user.nome}</strong></td>
                <td>
                    <small><i class="fas fa-envelope"></i> ${user.email}</small><br>
                    <small><i class="fas fa-phone"></i> ${user.telefone || 'N/A'}</small>
                </td>
                <td>
                    <button onclick="abrirModalUser(${user.id}, '${user.nome}', '${user.email}', '${user.telefone || ''}')" class="btn-icon edit" style="color:#3498db; background:none; border:none; cursor:pointer;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="excluirUsuario(${user.id})" class="btn-icon delete" style="color:#e74c3c; background:none; border:none; cursor:pointer; margin-left:10px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tabela.innerHTML = '<tr><td colspan="4">Erro ao carregar leitores.</td></tr>';
    }
}

function abrirModalUser(id, nome, email, telefone) {
    document.getElementById('edit-id-user').value = id;
    document.getElementById('edit-nome-user').value = nome;
    document.getElementById('edit-email-user').value = email;
    document.getElementById('edit-tel-user').value = telefone; // Preenche o telefone
    document.getElementById('modalEditarUser').style.display = 'flex';
}

function fecharModalUser() {
    document.getElementById('modalEditarUser').style.display = 'none';
}

function configurarEdicao() {
    document.getElementById('form-editar-user').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('id', document.getElementById('edit-id-user').value);
        fd.append('nome', document.getElementById('edit-nome-user').value);
        fd.append('email', document.getElementById('edit-email-user').value);
        fd.append('telefone', document.getElementById('edit-tel-user').value);

        const res = await fetch('api/editar_usuario.php', { method: 'POST', body: fd });
        const data = await res.json();
        if(data.sucesso) {
            alert("✅ Leitor atualizado!");
            fecharModalUser();
            carregarUsuarios();
        }
    });
}

// Reutilize sua função de cadastro aqui...
function configurarCadastro() {
    document.getElementById('form-usuario').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('nome', document.getElementById('nome-usuario').value);
        fd.append('email', document.getElementById('email-usuario').value);
        fd.append('telefone', document.getElementById('tel-usuario').value);

        const res = await fetch('api/salvar_usuario.php', { method: 'POST', body: fd });
        const data = await res.json();
        if(data.sucesso) {
            alert("✅ Cadastrado!");
            e.target.reset();
            carregarUsuarios();
        }
    });
}

async function excluirUsuario(id) {
    if(confirm("Excluir este leitor?")) {
        const res = await fetch(`api/excluir_usuario.php?id=${id}`);
        const data = await res.json();
        if(data.sucesso) carregarUsuarios();
    }
}