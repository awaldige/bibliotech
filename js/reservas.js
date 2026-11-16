document.addEventListener('DOMContentLoaded', () => {
    carregarLivrosSelect();
    carregarLeitoresSelect();
    carregarReservas();
});

async function carregarLivrosSelect() {
    const select = document.getElementById('select-livro');
    if (!select) return;

    try {
        const res = await fetch('api/listar_livros.php');

        if (!res.ok) {
            throw new Error("Erro ao carregar livros.");
        }

        const livros = await res.json();

        select.innerHTML =
            '<option value="">Selecione um livro...</option>' +
            livros.map(l => {
                const disponiveis = Number(l.disponiveis);
                const indisponivel = disponiveis <= 0;

                const descricao = indisponivel
                    ? 'Indisponível'
                    : `Disponíveis: ${disponiveis}`;

                return `
                    <option
                        value="${l.id}"
                        ${indisponivel ? 'disabled' : ''}
                    >
                        ${l.titulo} — ${descricao}
                    </option>
                `;
            }).join('');

    } catch (e) {
        console.error("Erro ao carregar livros:", e);
    }
}


async function carregarLeitoresSelect() {
    const select = document.getElementById('select-leitor');
    if (!select) return;
    try {
        const res = await fetch('api/listar_usuarios.php');
        const usuarios = await res.json();
        select.innerHTML = '<option value="">Selecione um Leitor...</option>' + 
            usuarios.map(u => `<option value="${u.id}">${u.nome}</option>`).join('');
    } catch (e) { console.error("Erro ao carregar leitores:", e); }
}

async function carregarReservas() {
    const tabela = document.getElementById('tabela-reservas');
    if (!tabela) return;

    try {
        const res = await fetch('api/listar_reservas.php');
        const dados = await res.json();

        if (dados.length === 0) {
            tabela.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px;">Nenhuma reserva pendente.</td></tr>';
            return;
        }

        tabela.innerHTML = dados.map(r => `
            <tr>
                <td><strong>${r.titulo_livro}</strong></td>
                <td>${r.nome_usuario}</td>
                <td>${new Date(r.data_reserva).toLocaleDateString('pt-BR')}</td>
                <td>
                    <button onclick="efetivarReserva(${r.id}, ${r.id_livro}, ${r.id_usuario})" class="btn-icon" style="color:var(--success); background:none; border:none; cursor:pointer; font-size: 1.2rem; margin-right: 15px;" title="Efetivar Empréstimo">
                        <i class="fas fa-check-circle"></i>
                    </button>

                    <button onclick="cancelarReserva(${r.id})" class="btn-icon" style="color:var(--danger); background:none; border:none; cursor:pointer; font-size: 1.2rem;" title="Excluir Reserva">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (e) { console.error("Erro ao carregar reservas:", e); }
}

// FUNÇÃO PARA TRANSFORMAR EM EMPRÉSTIMO
async function efetivarReserva(idReserva, idLivro, idUsuario) {
    if (!confirm("Confirmar a entrega do livro e gerar empréstimo?")) return;

    const formData = new FormData();
    formData.append('id_reserva', idReserva);
    formData.append('id_livro', idLivro);
    formData.append('id_usuario', idUsuario);

    try {
        const res = await fetch('api/efetivar_reserva.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.sucesso) {
            alert("Empréstimo registrado com sucesso!");
            carregarReservas();
        } else { alert("Erro: " + data.erro); }
    } catch (err) { console.error("Erro ao efetivar:", err); }
}

// FUNÇÃO PARA CANCELAR RESERVA, PRESERVANDO O HISTÓRICO
async function cancelarReserva(id) {
    if (!confirm("Deseja realmente cancelar esta reserva?")) return;

    try {
        const formData = new FormData();
        formData.append('id', id);

        const res = await fetch('api/cancelar_reserva.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (!res.ok || !data.sucesso) {
            alert("Erro ao cancelar: " + (data.erro || "Não foi possível cancelar a reserva."));
            return;
        }

        alert("Reserva cancelada com sucesso!");

        await carregarReservas();

    } catch (err) {
        console.error("Erro ao cancelar reserva:", err);
        alert("Erro na comunicação com o servidor. Consulte o Console.");
    }
}

// SALVAR NOVA RESERVA
document.getElementById('form-reserva')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('id_livro', document.getElementById('select-livro').value);
    formData.append('id_usuario', document.getElementById('select-leitor').value);

    try {
        const res = await fetch('api/salvar_reserva.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.sucesso) {
            alert("Reserva realizada!");
            carregarReservas();
            e.target.reset();
        } else { alert("Erro: " + data.erro); }
    } catch (err) { console.error("Erro ao salvar:", err); }
});