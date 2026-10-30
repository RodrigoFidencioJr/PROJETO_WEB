# 🌎 CRUD Mundo

Sistema web desenvolvido para gerenciamento de informações geográficas, permitindo cadastrar, consultar, atualizar e excluir dados relacionados a continentes, países, cidades e governantes.

## 📌 Sobre o projeto

O **CRUD Mundo** é uma aplicação web que centraliza informações geográficas em um banco de dados relacional. O sistema possui autenticação de usuários, controle de acesso e registro de atividades, além das operações de CRUD das principais entidades do projeto.

## ⚙️ Funcionalidades

- Cadastro, consulta, edição e exclusão de continentes
- Cadastro, consulta, edição e exclusão de países
- Cadastro, consulta, edição e exclusão de cidades
- Cadastro, consulta, edição e exclusão de governantes
- Autenticação de usuários
- Bloqueio de usuário após 3 tentativas consecutivas de senha incorreta
- Troca obrigatória de senha no primeiro acesso
- Controle de sessão
- Registro de ações na tabela de logs
- Administração de usuários
- Mapa interativo da sede da ETEC Profª Ilza Nascimento Pintus com a Google Maps JavaScript API

## 🛠️ Tecnologias utilizadas

- HTML5
- CSS3
- JavaScript
- Google Maps JavaScript API
- PHP
- MySQL
- Git
- GitHub

## 📁 Estrutura do projeto

```text
CRUD-MUNDO/
├── backend/
│   ├── Auth.php
│   ├── database.php
│   ├── sessao.php
│   ├── cidades.php
│   ├── continentes.php
│   ├── governantes.php
│   ├── paises.php
│   ├── usuarios.php
│   └── logs.php
├── database/
│   └── bd_mundo.sql
├── frontend/
│   ├── css/
│   ├── js/
│   │   ├── maps-config.example.js
│   │   ├── maps-config.js (local, não versionado)
│   │   └── mapa.js
│   ├── index.html
│   ├── login.html
│   ├── trocar_senha.html
│   ├── cidades.html
│   ├── continentes.html
│   ├── governantes.html
│   ├── paises.html
│   ├── usuarios.html
│   └── logs.html
├── .env.example
├── .gitignore
└── README.md
```

## 🗄️ Banco de dados

O banco de dados utilizado é o **MySQL**.

O script `database/bd_mundo.sql` cria a base `bd_mundo`, suas tabelas, relacionamentos, índices, gatilhos e dados iniciais.

Entre as principais tabelas estão:

- `continentes`
- `paises`
- `cidades`
- `governantes`
- `usuarios`
- `logs`

## 🔐 Autenticação e segurança

O sistema possui autenticação por usuário e senha.

Após **3 tentativas consecutivas de senha incorreta**, o usuário é bloqueado. No primeiro acesso, o sistema exige a alteração da senha antes de permitir o acesso às funcionalidades protegidas.

As ações de autenticação e outros eventos relevantes são registradas na tabela `logs`.

As configurações do banco de dados não ficam gravadas diretamente no código. Elas devem ser informadas em um arquivo local `.env`, que não é versionado pelo Git.

A chave da Google Maps JavaScript API também deve ficar em um arquivo local `frontend/js/maps-config.js`, ignorado pelo Git. Não publique uma chave irrestrita em repositórios públicos.

## 🗺️ Configuração do Google Maps

1. Obtenha uma chave de demonstração ou uma chave de API válida no Google Maps Platform.
2. Na pasta `frontend/js`, faça uma cópia de `maps-config.example.js` e renomeie a cópia para `maps-config.js`.
3. Abra `maps-config.js` e substitua o texto `COLOQUE_SUA_CHAVE_AQUI` pela sua chave.
4. Mantenha `maps-config.js` apenas localmente; esse arquivo está no `.gitignore` e não deve ser enviado ao GitHub.
5. Abra o sistema pelo servidor local com conexão à internet.

A chave de demonstração é destinada a testes e protótipos e possui recursos e cotas limitados. Para uso em produção, configure uma chave própria, restrinja-a à Google Maps JavaScript API e aos sites autorizados.

## ✅ Requisitos

Para executar o projeto é necessário possuir:

- Servidor web com suporte a PHP, como XAMPP
- PHP com extensão PDO MySQL habilitada
- MySQL ou MariaDB compatível
- Navegador web com acesso à internet
- Chave configurada para a Google Maps JavaScript API
- Git, caso deseje clonar o repositório

## 🚀 Como executar

1. Clone o repositório:

```bash
git clone https://github.com/RodrigoFidencioJr/PROJETO_WEB.git
```

2. Coloque o projeto em uma pasta atendida pelo seu servidor web local.

3. Acesse a pasta `CRUD-MUNDO`.

4. Crie seu arquivo de configuração local a partir de `.env.example`:

```text
.env.example  →  .env
```

5. Preencha o arquivo `.env` com as credenciais do seu banco de dados local.

6. Importe o arquivo `database/bd_mundo.sql` no MySQL.

7. Configure a chave do Google Maps conforme a seção **Configuração do Google Maps**.

8. Inicie o servidor web e o serviço do banco de dados.

9. Abra no navegador a página:

```text
frontend/login.html
```

> O caminho exato no navegador dependerá da pasta em que o projeto foi colocado no servidor local.

## 📝 Versionamento

O desenvolvimento do projeto é registrado por meio do Git e do GitHub, utilizando commits para representar as diferentes etapas de construção, correção e melhoria do sistema.

## 👨‍💻 Autor

**Rodrigo Fidêncio Júnior**

Curso Técnico em Desenvolvimento de Sistemas.
