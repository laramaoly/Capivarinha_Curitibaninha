-- Criação do Banco de Dados para o Capivarinha_Curitibaninha
-- Autor: Maoly Lara Serrano
-- Tema: Curitiba Raiz

-- 1. Criação do Banco (se não existir)
CREATE DATABASE IF NOT EXISTS capityper CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE capityper;

-- 2. Tabela de Usuários
-- Armazena login, senha (hash) e dados básicos
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL, -- Hash gerado via password_hash()
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabela de Ligas
-- Grupos de competição criados pelos usuários
CREATE TABLE ligas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_liga VARCHAR(100) NOT NULL,
    palavra_chave VARCHAR(50) NOT NULL, -- Senha para entrar na liga
    criador_id INT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (criador_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 4. Tabela de Membros da Liga
-- Relacionamento N:N entre Usuários e Ligas
CREATE TABLE liga_membros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    liga_id INT,
    data_entrada DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (liga_id) REFERENCES ligas(id) ON DELETE CASCADE
);

-- 5. Tabela de Partidas (Histórico)
-- Registra cada jogo finalizado para cálculo de ranking
CREATE TABLE partidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    pontuacao INT NOT NULL,
    palavras_acertadas INT,
    data_partida DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 6. Tabela de Palavras (Dicionário Curitibano)
-- O conteúdo do jogo
CREATE TABLE termos_jogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    termo VARCHAR(255) NOT NULL,
    dica VARCHAR(255) DEFAULT NULL,
    dificuldade ENUM('facil', 'medio', 'dificil') DEFAULT 'medio'
);

-- 7. Inserção de Dados Iniciais (Seed)
-- Gírias e expressões típicas para o jogo começar funcional
INSERT INTO termos_jogo (termo, dica, dificuldade) VALUES 
-- Fáceis
('Vina', 'Apelido curto usado localmente', 'facil'), 
('Piá', 'Garoto, menino', 'facil'), 
('Guria', 'Menina ou jovem mulher', 'facil'), 
('Capi', 'Diminutivo de capivara', 'facil'), 
('Chima', 'Gíria local para chimarrão', 'facil'), 
('Tubo', 'Onibus de linha', 'facil'), 
('Véi', 'Forma coloquial de chamar alguém (véio)', 'facil'), 
('Gela', 'Forma de dizer geladeira/gelo', 'facil'),
('Busão', 'Ônibus', 'facil'),

-- Médias
('Capivara', 'Animal símbolo da cidade', 'medio'), 
('Gurizão', 'Versão aumentativa de gurizinho', 'medio'), 
('Friaca', 'Frio intenso', 'medio'), 
('Faceiro', 'Alegre, satisfeito', 'medio'),
('Sinaleiro', 'Pessoa que usa muito os semáforos (brincadeira)', 'medio'), 
('Penal', 'Termo local para punição ou jogo', 'medio'), 
('Doleira', 'Gíria regional (carteira?)', 'medio'), 
('Japona', 'Jaqueta grande', 'medio'),
('Cancha', 'Quadra, espaço de jogo', 'medio'),

-- Difíceis
('Leite quente', 'Bebida, ou expressão idiomática', 'dificil'), 
('Pão com vina', 'Lanche típico com vinho/linguiça (regional)', 'dificil'), 
('Chuva oblíqua', 'Chuva inclinada devido ao vento', 'dificil'),
('Barigui lover', 'Brincadeira com o nome do parque Barigui', 'dificil'), 
('Biarticulado', 'Tipo de ônibus com duas articulações', 'dificil'), 
('Petit Pavê', 'Trocadilho culinário ou nome inventado', 'dificil'),
('Deus me livre', 'Expressão de surpresa/afirmação', 'dificil'),
('Quem me dera', 'Expressão de desejo irreal', 'dificil');

-- 8. Tabela de Tentativas de Login (Segurança)
-- CORREÇÃO: Adicionando tabela que faltava para o AuthController funcionar
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100),
    ip VARCHAR(45),
    sucesso TINYINT(1),
    tentativa_em DATETIME DEFAULT CURRENT_TIMESTAMP
);