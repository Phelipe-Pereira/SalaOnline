# Sistema de Reservas de Salas - Sala Online

Um sistema completo para gerenciamento de reservas de salas desenvolvido em PHP, MySQL, HTML, CSS e JavaScript.

## 🚀 Funcionalidades

### Para Usuários:
- ✅ Registro e login de usuários
- ✅ Visualização de salas disponíveis
- ✅ Calendário interativo
- ✅ Realização de reservas
- ✅ Confirmação por e-mail
- ✅ Consulta de reservas realizadas
- ✅ Cancelamento de reservas
- ✅ Perfil do usuário

### Para Administradores:
- ✅ Dashboard com estatísticas
- ✅ Gerenciamento completo de reservas
- ✅ Gestão de salas
- ✅ Gestão de usuários
- ✅ Relatórios e exportação

## 📋 Requisitos do Sistema

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL, mbstring

## 🛠️ Instalação

### 1. Clone ou baixe o projeto
```bash
git clone [URL_DO_REPOSITORIO]
cd SalaOnline
```

### 2. Configure o banco de dados
- Crie um banco de dados MySQL
- Importe o arquivo `database.sql` no seu banco de dados

### 3. Configure as credenciais
Edite o arquivo `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sala_online');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 4. Configure o servidor web
- Coloque os arquivos no diretório do seu servidor web
- Certifique-se de que o PHP tem permissões de escrita

### 5. Acesse o sistema
- Acesse: `http://localhost/SalaOnline`
- Use as credenciais padrão do administrador:
  - Email: `admin@salaonline.com`
  - Senha: `password`

## 📁 Estrutura do Projeto

```
SalaOnline/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── includes/
│   ├── config.php
│   ├── database.php
│   ├── auth.php
│   └── functions.php
├── admin/
│   ├── dashboard.php
│   ├── reservas.php
│   ├── salas.php
│   └── usuarios.php
├── api/
│   ├── criar_reserva.php
│   ├── cancelar_reserva.php
│   └── disponibilidade.php
├── index.php
├── login.php
├── register.php
├── reservas.php
├── perfil.php
├── logout.php
├── database.sql
└── README.md
```

## 🔧 Configurações

### Configuração de E-mail
Para enviar e-mails de confirmação, configure no arquivo `includes/config.php`:
```php
define('EMAIL_FROM', 'sistema@suaempresa.com');
```

### Configuração de Horários
```php
define('HORARIO_INICIO', '08:00');
define('HORARIO_FIM', '18:00');
define('DURACAO_MINIMA', 30);
define('DURACAO_MAXIMA', 240);
```

## 👥 Usuários Padrão

### Administrador
- **Email:** admin@salaonline.com
- **Senha:** password
- **Tipo:** admin

### Salas Padrão
- Sala de Reunião 1 (10 pessoas)
- Sala de Reunião 2 (15 pessoas)
- Sala de Treinamento (25 pessoas)
- Sala Executiva (6 pessoas)

## 🔒 Segurança

- Senhas criptografadas com `password_hash()`
- Proteção contra SQL Injection usando PDO
- Validação de dados de entrada
- Sessões seguras
- Controle de acesso por tipo de usuário

## 📱 Responsividade

O sistema é totalmente responsivo e funciona em:
- Desktop
- Tablet
- Smartphone

## 🎨 Personalização

### Cores e Estilo
Edite o arquivo `assets/css/style.css` para personalizar:
- Cores do tema
- Tipografia
- Layout
- Componentes

### Configurações do Sistema
Modifique `includes/config.php` para:
- Nome do sistema
- URL base
- Configurações de banco
- Configurações de e-mail

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
- Verifique as credenciais em `includes/config.php`
- Certifique-se de que o MySQL está rodando
- Verifique se o banco de dados existe

### E-mails não são enviados
- Configure um servidor SMTP
- Verifique as configurações de e-mail
- Teste com `mail()` do PHP

### Páginas não carregam
- Verifique as permissões de arquivo
- Confirme se o PHP está configurado
- Verifique os logs de erro do servidor

## 📞 Suporte

Para suporte técnico ou dúvidas:
- Abra uma issue no repositório
- Entre em contato com o desenvolvedor

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## 🔄 Atualizações

Para atualizar o sistema:
1. Faça backup do banco de dados
2. Substitua os arquivos
3. Execute scripts de migração se necessário
4. Teste todas as funcionalidades

## 🎯 Próximas Funcionalidades

- [ ] Notificações push
- [ ] Relatórios avançados
- [ ] Integração com calendário externo
- [ ] API REST completa
- [ ] Aplicativo mobile
- [ ] Sistema de notificações por SMS


