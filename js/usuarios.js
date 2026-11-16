
document.addEventListener('DOMContentLoaded', () => {
    carregarUsuarios();
    configurarCadastro();
    configurarEdicao();
});

// Escapa valores antes de inseri-los em atributos HTML.
function escaparHTML(valor) {
    return String(valor ?? '').replace(/[&<>"']/g, caractere => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[caractere]);
}

// CARREGAR LEITORES
async function carregarUsuarios() {
    const tabela = document.getElementById('tabela-usuarios');

    try {
        const res = await fetch('api/listar_usuarios.php');

        if (!res.ok) {
            throw new Error('Não foi possível carregar os leitores.');
        }

        const usuarios = await res.json();

        if (!Array.isArray(usuarios)) {
            throw new Error('Resposta inválida do servidor.');
        }

        tabela.innerHTML = usuarios.map(user => `
            <tr>
                <td>
                    <i class="fas fa-user-circle" style="color: #3498db;"></i>
                </td>
                <td><strong>${escaparHTML(user.nome)}</strong></td>
                <td>
                    <small>
                        <i class="fas fa-envelope"></i>
                        ${escaparHTML(user.email)}
                    </small><br>
                    <small>
                        <i class="fas fa-phone"></i>
                        ${escaparHTML(user.telefone || 'N/A')}
                    </small>
                </td>
                <td>
                    <button
                        type="button"
                        class="btn-icon edit"
                        data-acao="editar"
                        data-id="${escaparHTML(user.id)}"
                        style="color:#3498db; background:none; border:none; cursor:pointer;"
                        aria-label="Editar leitor"
                    >
                        <i class="fas fa-edit"></i>
                    </button>

                    <button
                        type="button"
                        class="btn-icon delete"
                        data-acao="excluir"
                        data-id="${escaparHTML(user.id)}"
                        style="color:#e74c3c; background:none; border:none; cursor:pointer; margin-left:10px;"
                        aria-label="Excluir leitor"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        if (usuarios.length === 0) {
            tabela.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align:center;">
                        Nenhum leitor cadastrado.
                    </td>
                </tr>
            `;
        }

        // Eventos dos botões da tabela.
        tabela.querySelectorAll('[data-acao="editar"]').forEach(botao => {
            botao.addEventListener('click', () => {
                const usuario = usuarios.find(
                    user => String(user.id) === botao.dataset.id
                );

                if (usuario) {
                    abrirModalUser(
                        usuario.id,
                        usuario.nome,
                        usuario.email,
                        usuario.telefone || ''
                    );
                }
            });
        });

        tabela.querySelectorAll('[data-acao="excluir"]').forEach(botao => {
            botao.addEventListener('click', () => {
                excluirUsuario(botao.dataset.id);
            });
        });

    } catch (error) {
        console.error('Erro ao carregar leitores:', error);

        tabela.innerHTML = `
            <tr>
                <td colspan="4" style="text-align:center;">
                    Erro ao carregar leitores.
                </td>
            </tr>
        `;
    }
}

// ABRIR MODAL DE EDIÇÃO
function abrirModalUser(id, nome, email, telefone) {
    document.getElementById('edit-id-user').value = id;
    document.getElementById('edit-nome-user').value = nome;
    document.getElementById('edit-email-user').value = email;
    document.getElementById('edit-tel-user').value = telefone;

    document.getElementById('modalEditarUser').style.display = 'flex';
}

// FECHAR MODAL
function fecharModalUser() {
    document.getElementById('modalEditarUser').style.display = 'none';
}

// EDIÇÃO DE LEITORES

function configurarEdicao() {
    document.getElementById('form-editar-user')
        .addEventListener('submit', async event => {
            event.preventDefault();

            const dados = new FormData();

            dados.append(
                'id',
                document.getElementById('edit-id-user').value
            );

            dados.append(
                'nome',
                document.getElementById('edit-nome-user').value
            );

            dados.append(
                'email',
                document.getElementById('edit-email-user').value
            );

            dados.append(
                'telefone',
                document.getElementById('edit-tel-user').value
            );

            try {
                const res = await fetch('api/editar_usuario.php', {
                    method: 'POST',
                    body: dados
                });

                const data = await res.json();

                if (!res.ok || !data.sucesso) {
                    alert('❌ Erro ao atualizar: ' +
                        (data.erro || 'Falha ao atualizar leitor.'));
                    return;
                }

                alert('✅ Leitor atualizado com sucesso!');

                fecharModalUser();
                await carregarUsuarios();

            } catch (error) {
                console.error('Erro ao editar leitor:', error);
                alert('⚠️ Erro de conexão ao atualizar leitor.');
            }
        });
}


// CADASTRO DE LEITORES

function configurarCadastro() {
    document.getElementById('form-usuario')
        .addEventListener('submit', async event => {
            event.preventDefault();

            const form = event.currentTarget;
            const dados = new FormData();

            dados.append(
                'nome',
                document.getElementById('nome-usuario').value
            );

            dados.append(
                'email',
                document.getElementById('email-usuario').value
            );

            dados.append(
                'telefone',
                document.getElementById('tel-usuario').value
            );

            try {
                const res = await fetch('api/salvar_usuario.php', {
                    method: 'POST',
                    body: dados
                });

                const data = await res.json();

                if (!res.ok || !data.sucesso) {
                    alert('❌ Erro ao cadastrar: ' +
                        (data.erro || 'Falha ao cadastrar leitor.'));
                    return;
                }

                alert('✅ Leitor cadastrado com sucesso!');

                form.reset();
                await carregarUsuarios();

            } catch (error) {
                console.error('Erro ao cadastrar leitor:', error);
                alert('⚠️ Erro de conexão ao cadastrar leitor.');
            }
        });
}

// EXCLUSÃO DE LEITORES
async function excluirUsuario(id) {
    if (!confirm('Tem certeza que deseja excluir este leitor?')) {
        return;
    }

    try {
        const dados = new FormData();
        dados.append('id', id);

        const res = await fetch('api/excluir_usuario.php', {
            method: 'POST',
            body: dados
        });

        const data = await res.json();

        if (!res.ok || !data.sucesso) {
            alert('❌ Erro ao excluir: ' +
                (data.erro || 'Falha ao excluir leitor.'));
            return;
        }

        alert('✅ Leitor removido com sucesso!');
        await carregarUsuarios();

    } catch (error) {
        console.error('Erro ao excluir leitor:', error);
        alert('⚠️ Erro de conexão ao excluir leitor.');
    }
}
