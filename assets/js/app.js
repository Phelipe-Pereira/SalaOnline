document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    setupEventListeners();
    setupCalendar();
    setupModals();
    setupForms();
}

function setupEventListeners() {
    const reservarBtns = document.querySelectorAll('.btn-reservar');
    reservarBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const salaId = this.dataset.salaId;
            const salaNome = this.dataset.salaNome;
            openReservaModal(salaId, salaNome);
        });
    });

    const cancelarBtns = document.querySelectorAll('.btn-cancelar');
    cancelarBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const reservaId = this.dataset.reservaId;
            if (confirm('Tem certeza que deseja cancelar esta reserva?')) {
                cancelarReserva(reservaId);
            }
        });
    });
}

function setupCalendar() {
    const calendarDays = document.querySelectorAll('.calendar-day');
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            if (!this.classList.contains('disabled')) {
                calendarDays.forEach(d => d.classList.remove('selected'));
                this.classList.add('selected');
                const data = this.dataset.date;
                carregarDisponibilidade(data);
            }
        });
    });
}

function setupModals() {
    const modals = document.querySelectorAll('.modal');
    const closeBtns = document.querySelectorAll('.modal-close');

    closeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.style.display = 'none';
        });
    });

    window.addEventListener('click', function(e) {
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
}

function setupForms() {
    const forms = document.querySelectorAll('form[data-ajax]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitFormAjax(this);
        });
    });
}

function openReservaModal(salaId, salaNome) {
    const modal = document.getElementById('modal-reserva');
    const salaNomeEl = modal.querySelector('.sala-nome');
    const salaIdInput = modal.querySelector('input[name="sala_id"]');
    
    salaNomeEl.textContent = salaNome;
    salaIdInput.value = salaId;
    
    modal.style.display = 'block';
}

function submitFormAjax(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading"></span> Enviando...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Status da resposta:', response.status);
        console.log('Headers da resposta:', response.headers);
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.error('Resposta não-JSON recebida:', text);
                throw new Error('Resposta inválida do servidor: ' + text.substring(0, 200));
            });
        }
    })
    .then(data => {
        console.log('Dados recebidos:', data);
        if (data.success) {
            showAlert('success', data.message);
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erro detalhado:', error);
        showAlert('danger', 'Erro ao processar solicitação: ' + error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function carregarDisponibilidade(data) {
    fetch('api/disponibilidade.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `data=${data}`
    })
    .then(response => response.json())
    .then(data => {
        atualizarDisponibilidade(data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function atualizarDisponibilidade(dados) {
    const salasContainer = document.querySelector('.salas-grid');
    if (!salasContainer) return;
    
    salasContainer.innerHTML = '';
    
    dados.forEach(sala => {
        const salaCard = criarSalaCard(sala);
        salasContainer.appendChild(salaCard);
    });
}

function criarSalaCard(sala) {
    const card = document.createElement('div');
    card.className = `sala-card ${sala.disponivel ? 'disponivel' : 'ocupada'}`;
    
    card.innerHTML = `
        <h3>${sala.nome}</h3>
        <p><strong>Capacidade:</strong> ${sala.capacidade} pessoas</p>
        <p><strong>Recursos:</strong> ${sala.recursos}</p>
        <span class="sala-status ${sala.disponivel ? 'status-disponivel' : 'status-ocupada'}">
            ${sala.disponivel ? 'Disponível' : 'Ocupada'}
        </span>
        ${sala.disponivel ? 
            `<button class="btn btn-primary btn-reservar" data-sala-id="${sala.id}" data-sala-nome="${sala.nome}">
                Reservar
            </button>` : 
            '<button class="btn btn-secondary" disabled>Indisponível</button>'
        }
    `;
    
    if (sala.disponivel) {
        card.querySelector('.btn-reservar').addEventListener('click', function(e) {
            e.preventDefault();
            openReservaModal(sala.id, sala.nome);
        });
    }
    
    return card;
}

function cancelarReserva(reservaId) {
    fetch('api/cancelar_reserva.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `reserva_id=${reservaId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Erro ao cancelar reserva');
        console.error('Error:', error);
    });
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function formatarData(data) {
    const [ano, mes, dia] = data.split('-');
    return `${dia}/${mes}/${ano}`;
}

function formatarHora(hora) {
    return hora.substring(0, 5);
}

function validarFormulario(form) {
    const campos = form.querySelectorAll('[required]');
    let valido = true;
    
    campos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.classList.add('error');
            valido = false;
        } else {
            campo.classList.remove('error');
        }
    });
    
    return valido;
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.querySelector(`[onclick="togglePasswordVisibility('${inputId}')"]`);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '👁️';
    } else {
        input.type = 'password';
        icon.textContent = '👁️‍🗨️';
    }
}

function filtrarTabela(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const filter = input.value.toLowerCase();
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

function exportarTabela(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
