CREATE DATABASE IF NOT EXISTS sala_online;
USE sala_online;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('usuario', 'admin') DEFAULT 'usuario',
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE salas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    capacidade INT NOT NULL,
    recursos TEXT,
    status ENUM('disponivel', 'indisponivel') DEFAULT 'disponivel',
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    sala_id INT NOT NULL,
    data_reserva DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    titulo VARCHAR(100),
    descricao TEXT,
    status ENUM('confirmada', 'cancelada', 'pendente') DEFAULT 'confirmada',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (sala_id) REFERENCES salas(id)
);

CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(50) UNIQUE NOT NULL,
    valor TEXT,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO usuarios (nome, email, senha, tipo) VALUES 
('Administrador', 'admin@salaonline.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO salas (nome, capacidade, recursos) VALUES 
('Sala de Reunião 1', 10, 'Projetor, Quadro Branco, Wi-Fi'),
('Sala de Reunião 2', 15, 'Projetor, Quadro Branco, Wi-Fi, Videoconferência'),
('Sala de Treinamento', 25, 'Projetor, Quadro Branco, Wi-Fi, Computadores'),
('Sala Executiva', 6, 'Projetor, Quadro Branco, Wi-Fi, Cafezinho');

INSERT INTO configuracoes (chave, valor) VALUES 
('horario_inicio', '08:00'),
('horario_fim', '18:00'),
('duracao_minima', '30'),
('duracao_maxima', '240'),
('email_sistema', 'sistema@salaonline.com');
