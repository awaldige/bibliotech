document.addEventListener('DOMContentLoaded', () => {
    const dataElemento = document.getElementById('data-atual');
    const hoje = new Date();
    if (dataElemento) dataElemento.innerText = hoje.toLocaleDateString('pt-br', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    atualizarContadores();
    carregarAtividades();
});

async function atualizarContadores() {
    try {
        const res = await fetch('api/buscar_estatisticas.php');
        const dados = await res.json();
        const ids = { 'total-livros': dados.livros, 'total-usuarios': dados.leitores, 'total-emprestimos': dados.emprestimos, 'total-reservas': dados.reservas };
        for (const [id, valor] of Object.entries(ids)) {
            const el = document.getElementById(id);
            if (el) el.innerText = valor || 0;
        }
    } catch (error) { console.error("Erro nos contadores:", error); }
}

async function carregarAtividades() {
    const listaContainer = document.getElementById('lista-atividades');
    if (!listaContainer) return;
    
    try {
        const res = await fetch(`api/buscar_atividade.php?t=${new Date().getTime()}`); 
        const atividades = await res.json();

        if (!Array.isArray(atividades) || atividades.length === 0) {
            listaContainer.innerHTML = '<p style="padding: 20px; color: #777;">Nenhuma atividade recente encontrada.</p>';
            return;
        }

        listaContainer.innerHTML = atividades.map(atv => {
            let icone, corIcone, textoAcao;

            switch(atv.tipo) {
                case 'livro': icone = 'fa-book'; corIcone = '#3498db'; textoAcao = 'Novo livro cadastrado'; break;
                case 'leitor':
                case 'usuario': icone = 'fa-user'; corIcone = '#2ecc71'; textoAcao = 'Novo leitor registrado'; break;
                case 'reserva': icone = 'fa-bookmark'; corIcone = '#e67e22'; textoAcao = 'Nova reserva efetuada'; break;
                case 'emprestimo': icone = 'fa-hand-holding-heart'; corIcone = '#e74c3c'; textoAcao = 'Novo empréstimo realizado'; break;
                case 'devolucao': icone = 'fa-undo-alt'; corIcone = '#1abc9c'; textoAcao = 'Livro devolvido ao acervo'; break;
                case 'autor': icone = 'fa-feather'; corIcone = '#9b59b6'; textoAcao = 'Novo autor adicionado'; break;
                case 'editora': icone = 'fa-building'; corIcone = '#34495e'; textoAcao = 'Nova editora cadastrada'; break;
                case 'devolucao':icone = 'fa-undo-alt'; corIcone = '#1abc9c'; textoAcao = 'Livro devolvido ao acervo'; break;
                default: icone = 'fa-info-circle'; corIcone = '#95a5a6'; textoAcao = 'Atividade registrada';
            }
            
            const dataISO = atv.data ? atv.data.replace(' ', 'T') : null;
            const dataObj = new Date(dataISO);
            
            // Lógica para mostrar "Hoje" ou a data formatada
            const hojeStr = new Date().toLocaleDateString('pt-br');
            const dataFormatada = dataObj.toLocaleDateString('pt-br') === hojeStr 
                ? `Hoje às ${dataObj.toLocaleTimeString('pt-br', {hour: '2-digit', minute:'2-digit'})}`
                : dataObj.toLocaleDateString('pt-br');

            return `
                <div class="activity-item" style="display: flex; align-items: center; padding: 12px 15px; border-bottom: 1px solid #f0f0f0;">
                    <div style="background: ${corIcone}22; color: ${corIcone}; width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-size: 1.1rem;">
                        <i class="fas ${icone}"></i>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin: 0; font-size: 0.9rem; color: #333; font-weight: 600;">${atv.item}</h4>
                        <small style="color: #888;">${textoAcao}</small>
                    </div>
                    <div style="text-align: right; font-size: 0.7rem; color: #aaa; min-width: 85px;">
                        ${dataFormatada}
                    </div>
                </div>
            `;
        }).join('');

    } catch (error) {
        console.error("Erro no feed:", error);
        listaContainer.innerHTML = '<p style="padding: 20px; color: red;">Erro ao carregar o feed.</p>';
    }
}