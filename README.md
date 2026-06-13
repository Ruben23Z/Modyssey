# Modyssey - Plataforma MVC para Gestão e Partilha de Modificações de Jogos

## Introdução e Contextualização
O Modyssey é uma aplicação web desenvolvida no âmbito da unidade curricular de Sistemas de Multimédia na Internet (SMI). O projeto consiste numa plataforma dedicada à comunidade de modificadores (modders) e jogadores, permitindo a publicação, gestão de versões, subscrição e partilha de modificações (mods) de jogos de forma estruturada. 

O desenvolvimento desta plataforma teve como foco a criação de um sistema dinâmico, interativo e seguro, com uma clara separação de responsabilidades e aplicando as melhores práticas de engenharia de software para a web.

---

## Objetivos e Âmbito
O principal objetivo do Modyssey foi o desenvolvimento de uma aplicação web robusta, recorrendo à linguagem PHP sem o auxílio de frameworks comerciais adicionais. Com isto, procurou-se dominar a implementação manual de padrões de design, fluxos de autenticação, controlo de acessos e comunicação em rede.

O âmbito do projeto abrange:
* A conceção de uma arquitetura Model-View-Controller (MVC) limpa e expansível.
* O desenvolvimento de um instalador web automático para facilidade de deployment em ambiente de produção ou desenvolvimento local.
* A criação de mecanismos de interação com o utilizador final, tais como sistemas de subscrição e alertas automatizados por correio eletrónico.
* A integração com serviços terceiros para enriquecimento de dados e a disponibilização de dashboards visuais analíticos para monitorização da atividade da plataforma.

---

## Arquitetura do Sistema e Decisões de Design

### O Padrão Model-View-Controller (MVC)
A aplicação foi estruturada seguindo o padrão MVC para garantir a modularidade e manutenibilidade do código fonte:
* **Core**: Contém o motor da aplicação, incluindo o sistema de rotas dinâmicas, o carregador de traduções (internacionalização), a abstração de acesso à base de dados (PDO) e as classes genéricas de validação e upload de ficheiros.
* **Models**: Responsáveis por toda a lógica de negócio e interações diretas com o esquema relacional do MySQL.
* **Views**: Ficheiros de apresentação que utilizam layouts dinâmicos e PHP puro para renderização de dados no lado do servidor.
* **Controllers**: Componente intermediário que intercepta os pedidos HTTP, valida a autorização do utilizador, invoca as operações necessárias nos modelos e determina qual a vista a apresentar.

### Desenho da Base de Dados
O esquema da base de dados relacional foi desenhado para assegurar a integridade referencial e a eficiência nas consultas. Destacam-se:
* O relacionamento muitos-para-muitos entre modificações e categorias, assegurando que um mod possa ser classificado de forma precisa.
* O controlo de versionamento através de chaves estrangeiras com comportamento em cascata (ON DELETE CASCADE), garantindo a consistência dos dados históricos quando entidades principais são removidas.
* A separação de dados sensíveis de configuração através da geração e leitura dinâmica de ficheiros XML protegidos na pasta `config/configuracoes/`.

---

## Funcionalidades Implementadas

### Sistema de Instalação Automática (`setup.php`)
Foi desenhado um instalador web dinâmico que simplifica o processo de configuração inicial da plataforma:
* Validação de ligação ao servidor MySQL.
* Execução automática do script de estrutura SQL (`database-script.sql`).
* Configuração do servidor de envio de correio eletrónico (SMTP) e armazenamento parametrizado das credenciais em ficheiros XML gerados dinamicamente.
* Criação do perfil do Administrador do sistema e encriptação segura da credencial de acesso.

### Controlo de Acesso Baseado em Perfis (RBAC)
O sistema implementa perfis de utilizador com diferentes níveis de autorização:
* **Guest (Visitante)**: Apenas tem acesso a visualização pública, pesquisa e registo.
* **User (Utilizador)**: Pode publicar novos mods, gerir as suas publicações, submeter novas versões e subscrever novidades.
* **Sympathizer (Simpatizante)**: Perfil de apoiante com acesso facilitado.
* **Admin (Administrador)**: Controlo total da plataforma, incluindo gestão de utilizadores e configurações globais.

### Versionamento Incremental de Conteúdos
Diferenciando-se de plataformas simples de upload de ficheiros, o Modyssey permite a criação de um histórico real de desenvolvimento dos mods. Cada mod pode ter múltiplas submissões de ficheiros com os respetivos diários de alteração (changelogs), sendo que o utilizador final descarrega sempre a versão estável mais recente de forma automática.

### Subscrições e Notificações por Correio Eletrónico
Os utilizadores podem subscrever jogos ou categorias específicas de modificações. A plataforma utiliza um cliente SMTP baseado em sockets para o envio automático de e-mails em formato HTML a alertar os subscritores sempre que novos mods são disponibilizados na categoria pretendida.

### Dashboard Analítico e Integração de APIs
Foi integrada a API pública RAWG no fluxo de adição de jogos, permitindo ao administrador pesquisar e preencher automaticamente as metadados e imagens do jogo. Além disso, a página de estatísticas disponibiliza dados gráficos relativos aos utilizadores mais ativos, mods mais populares e métricas de uploads mensais utilizando a biblioteca Chart.js.

---

## Aprendizagem Prática e Competências Adquiridas
O desenvolvimento do projeto Modyssey proporcionou a consolidação de diversas competências no domínio do desenvolvimento web moderno:

### Desenvolvimento MVC Nativo
A decisão de não utilizar frameworks pré-construídas exigiu um entendimento profundo de como as rotas, o ciclo de vida de pedidos/respostas HTTP, as sessões e a segurança funcionam a baixo nível em PHP.

### Segurança Web Ativa
Foram aplicados mecanismos reais de proteção de aplicações web:
* Encriptação de passwords utilizando o algoritmo robusto `bcrypt`.
* Criação e validação de tokens de ativação de conta enviados por e-mail para evitar registos com contas inexistentes.
* Prevenção de ataques de força bruta e registo de bots automatizados através de CAPTCHA nativo.
* Configurações de diretórios via `.htaccess` para impedir o acesso HTTP direto a ficheiros de sistema e configurações privadas XML.

### Integração de Protocolos e Serviços de Terceiros
A aprendizagem do consumo de APIs RESTful utilizando PHP (cURL) e a manipulação direta do protocolo SMTP através de ligações baseadas em sockets TCP para o envio de e-mails permitiu compreender a integração de múltiplos serviços em arquiteturas de sistemas distribuídos.

---

## Instruções de Instalação e Requisitos do Sistema

### Requisitos Mínimos
* Servidor Web: Apache 2.4+ com módulo `mod_rewrite` ativo.
* PHP: Versão 8.0 ou superior (extensões requeridas: `pdo_mysql`, `simplexml`, `openssl`).
* Base de Dados: MySQL 5.7+ ou MariaDB.

### Passos de Instalação

1. Clone o repositório para o diretório de publicação do seu servidor local (ex: `htdocs` ou `www`):
   ```bash
   git clone https://github.com/Ruben23Z/Modyssey.git
   ```

2. Assegure que as permissões de escrita estão devidamente atribuídas aos diretórios de uploads e configurações locais.

3. Aceda ao instalador através do navegador web:
   ```text
   http://localhost/Modyssey/public/setup.php
   ```

4. Preencha os dados de conexão de base de dados, as credenciais SMTP (ex: Conta do Gmail e palavra-passe de aplicação) e as informações do Administrador.

5. Após a submissão, o instalador criará as tabelas, inserirá os registos base e reencaminhará para a página inicial da plataforma.
