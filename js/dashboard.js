
document.addEventListener('DOMContentLoaded', () => {
    atualizarData();
    atualizarContadores();
    carregarAtividades();
});

// Data atual do painel
function atualizarData() {
    const elemento = document.getElementById('data-atual');

    if (!elemento) return;

    elemento.textContent = new Date().toLocaleDateString('pt-BR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Indicadores da biblioteca
async function atualizarContadores() {
    try {
        const resposta = await fetch('api/buscar_estatisticas.php');

        if (!resposta.ok) {
            throw new Error('Não foi possível consultar as estatísticas.');
        }

        const dados = await resposta.json();

        if (dados.sucesso === false) {
            throw new Error(dados.erro || 'Erro ao consultar estatísticas.');
        }

        const indicadores = {
            'total-livros': dados.livros,
            'total-exemplares': dados.exemplares,
            'total-disponiveis': dados.disponiveis,
            'total-usuarios': dados.leitores,
            'total-emprestimos': dados.emprestimos,
            'total-atrasados': dados.atrasados,
            'total-reservas': dados.reservas
        };

        for (const [id, valor] of Object.entries(indicadores)) {
            const elemento = document.getElementById(id);

            if (elemento) {
                const numero = Number(valor);
                elemento.textContent = Number.isFinite(numero) ? numero : 0;
            }
        }

    } catch (erro) {
        console.error('Erro ao atualizar indicadores:', erro);
    }
}

// Protege os dados textuais antes de inseri-los no HTML
function escaparHTML(valor) {
    return String(valor ?? '').replace(/[&<>"']/g, caractere => {
        const entidades = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };

        return entidades[caractere];
    });
}

// Configuração visual de cada tipo de atividade
function obterTipoAtividade(tipo) {
    const tipos = {
        livro: {
            icone: 'fa-book',
            classe: 'activity-book',
            descricao: 'Novo livro cadastrado'
        },
        leitor: {
            icone: 'fa-user',
            classe: 'activity-reader',
            descricao: 'Novo leitor registrado'
        },
        usuario: {
            icone: 'fa-user',
            classe: 'activity-reader',
            descricao: 'Novo leitor registrado'
        },
        reserva: {
            icone: 'fa-bookmark',
            classe: 'activity-reservation',
            descricao: 'Nova reserva efetuada'
        },
        emprestimo: {
            icone: 'fa-exchange-alt',
            classe: 'activity-loan',
            descricao: 'Novo empréstimo realizado'
        },
        devolucao: {
            icone: 'fa-undo-alt',
            classe: 'activity-return',
            descricao: 'Livro devolvido ao acervo'
        },
        autor: {
            icone: 'fa-feather-alt',
            classe: 'activity-author',
            descricao: 'Novo autor adicionado'
        },
        editora: {
            icone: 'fa-building',
            classe: 'activity-publisher',
            descricao: 'Nova editora cadastrada'
        }
    };

    return tipos[tipo] || {
        icone: 'fa-info-circle',
        classe: 'activity-default',
        descricao: 'Atividade registrada'
    };
}

// Formata a data da movimentação
function formatarDataAtividade(data) {
    if (!data) return 'Data não informada';

    const dataObj = new Date(String(data).replace(' ', 'T'));

    if (Number.isNaN(dataObj.getTime())) {
        return 'Data indisponível';
    }

    const hoje = new Date();

    const mesmaData =
        dataObj.getDate() === hoje.getDate() &&
        dataObj.getMonth() === hoje.getMonth() &&
        dataObj.getFullYear() === hoje.getFullYear();

    if (mesmaData) {
        return `Hoje às ${dataObj.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit'
        })}`;
    }

    return dataObj.toLocaleDateString('pt-BR');
}

// Histórico de movimentações
async function carregarAtividades() {
    const listaContainer = document.getElementById('lista-atividades');

    if (!listaContainer) return;

    try {
        const resposta = await fetch(
            `api/buscar_atividade.php?t=${Date.now()}`
        );

        if (!resposta.ok) {
            throw new Error('Não foi possível consultar as atividades.');
        }

        const atividades = await resposta.json();

        if (!Array.isArray(atividades) || atividades.length === 0) {
            listaContainer.innerHTML = `
                <div class="activity-empty">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhuma movimentação recente encontrada.</p>
                </div>
            `;
            return;
        }

        listaContainer.innerHTML = atividades.map(atividade => {
            const configuracao = obterTipoAtividade(atividade.tipo);
            const titulo = escaparHTML(atividade.item || 'Registro sem identificação');
            const descricao = escaparHTML(configuracao.descricao);
            const data = escaparHTML(formatarDataAtividade(atividade.data));

            return `
                <article class="activity-item">
                    <div class="activity-icon ${configuracao.classe}">
                        <i class="fas ${configuracao.icone}" aria-hidden="true"></i>
                    </div>

                    <div class="activity-details">
                        <h3 class="activity-title">${titulo}</h3>
                        <p class="activity-action">${descricao}</p>
                    </div>

                    <time class="activity-date">${data}</time>
                </article>
            `;
        }).join('');

    } catch (erro) {
        console.error('Erro ao carregar atividades:', erro);

        listaContainer.innerHTML = `
            <div class="activity-error">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Não foi possível carregar as movimentações.</p>
            </div>
        `;
    }
}
