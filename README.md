# 📚 BiblioTech — Sistema de Gestão de Biblioteca

O **BiblioTech** é um projeto demonstrativo de desenvolvimento web Full Stack, criado para apresentar uma aplicação de gerenciamento de biblioteca com controle de acervo, leitores, empréstimos e reservas.

O projeto demonstra a integração entre frontend, backend e banco de dados relacional, além da construção de interfaces dinâmicas, responsivas e organizadas.

> **Nota:** Este sistema foi desenvolvido exclusivamente para fins demonstrativos e composição de portfólio. Não se destina à operação de uma biblioteca real nem ao armazenamento de dados pessoais ou históricos reais.

---

## 🚀 Funcionalidades

- **Dashboard:** visualização de indicadores do acervo, exemplares disponíveis, leitores, empréstimos em aberto, atrasos, reservas pendentes e atividades recentes.
- **Gestão do acervo:** cadastro, consulta, edição e exclusão de livros, com informações de autores, editoras e quantidade de exemplares.
- **Gestão de leitores:** cadastro, consulta, edição e controle dos usuários da biblioteca.
- **Empréstimos:** registro, acompanhamento, renovação e devolução de livros.
- **Reservas:** criação, acompanhamento, cancelamento e efetivação de reservas.
- **Gestão de autores e editoras:** cadastro, edição, consulta e exclusão com proteção dos registros vinculados.
- **Controle de disponibilidade:** cálculo dos exemplares disponíveis considerando empréstimos em aberto e reservas pendentes.
- **Interface responsiva:** adaptação para diferentes tamanhos de tela.
- **Interface dinâmica:** utilização de modais e requisições assíncronas para operações sem recarregamento completo das páginas.

---

## 🛠️ Tecnologias utilizadas

| Área | Tecnologias |
|---|---|
| Frontend | HTML5, CSS3 e JavaScript Vanilla |
| Backend | PHP 8+ |
| API | PHP com endpoints modulares |
| Banco de dados | MySQL / MariaDB |
| Ícones | Font Awesome 6 |
| Ambiente de desenvolvimento | XAMPP |

---

## 🔗 Acesse o projeto online

[**BiblioTech — Demonstração online**](https://awaldige.infinityfree.me/bibliotech/)

---

## 📸 Prévia do sistema

### 📊 Dashboard

![Dashboard do BiblioTech](https://github.com/user-attachments/assets/49a93dca-ca9d-4231-adf5-5c2de9647dbe)

*Painel institucional com indicadores e atividades recentes.*

### 📚 Gestão do acervo

![Lista de livros](https://github.com/user-attachments/assets/a6416112-4ab8-4487-b189-121094352c48)

*Consulta e gerenciamento dos livros cadastrados.*

### 🔄 Gestão de empréstimos

![Gerenciamento de empréstimos](https://github.com/user-attachments/assets/10ea27ed-49a1-4908-af14-078f3762793a)

*Controle de empréstimos, devoluções e renovações.*

### ⚙️ Gerenciamento do sistema

![Gerenciamento do sistema](https://github.com/user-attachments/assets/1a135a6c-4403-4147-9c2d-457e33afa91f)

*Interface de gerenciamento dos cadastros.*

### 👥 Gestão de usuários

![Interface de usuários](https://github.com/user-attachments/assets/fd3ff64f-cb56-432a-a832-24fa449a2843)

*Cadastro e gerenciamento dos leitores.*

### 🖥️ Interface do sistema

![Tela do sistema](https://github.com/user-attachments/assets/6a73ba93-76af-46cc-9040-2edbebb5c07f)

*Outra visão da interface do BiblioTech.*

---

## 📦 Como instalar o projeto

### 1. Clonar o repositório

```bash
git clone https://github.com/awaldige/bibliotech.git
```

### 2. Configurar o servidor local

Coloque a pasta do projeto no diretório `htdocs` do XAMPP.

Inicie os serviços Apache e MySQL pelo painel de controle do XAMPP.

### 3. Criar o banco de dados

No phpMyAdmin ou em outro cliente MySQL:

1. Crie um banco de dados chamado `biblioteca`.
2. Utilize o charset `utf8mb4`.
3. Importe o arquivo `biblioteca.sql`, localizado na raiz do projeto.

### 4. Configurar a conexão com o banco

O arquivo `api/config.php` contém a configuração padrão da conexão.

Para utilizar configurações locais, crie o arquivo:

```text
api/config.local.php
```

Configure nele as credenciais correspondentes ao seu ambiente. Esse arquivo é local e não deve ser enviado ao repositório.

### 5. Acessar o sistema

Com Apache e MySQL em execução, acesse:

```text
http://localhost/bibliotech/
```

---

## 💡 Destaques técnicos

- Organização modular dos endpoints PHP.
- Integração entre frontend e backend.
- Utilização de consultas preparadas para operações no banco de dados.
- Validação de dados recebidos pela API.
- Manipulação dinâmica do DOM com JavaScript.
- Requisições assíncronas utilizando Fetch API.
- Tratamento de erros e respostas HTTP.
- Utilização de transações para operações que exigem consistência.
- Proteção de registros históricos de empréstimos e reservas.
- Controle de disponibilidade dos exemplares.
- Interface responsiva e componentes visuais padronizados.

---

## 👨‍💻 Autor

**André Waldige — AW TECHNOLOGY**

Projeto desenvolvido para **portfólio profissional e demonstração de habilidades em desenvolvimento web Full Stack**.

[GitHub](https://github.com/awaldige)