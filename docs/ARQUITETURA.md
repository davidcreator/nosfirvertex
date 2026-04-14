# Arquitetura AureaVertex

## Visao geral

AureaVertex usa arquitetura MVCL em PHP puro, com organizacao por areas:

- `catalog`: frontend e fluxo do usuario final
- `admin`: backoffice e governanca
- `install`: assistente de instalacao
- `system`: engine compartilhada, bibliotecas e infraestrutura

## Entradas da aplicacao

- `catalog/index.php` -> `Application('catalog')`
- `admin/index.php` -> `Application('admin')`
- `install/index.php` -> `Application('install')`
- `index.php` (raiz) redireciona para `catalog/index.php`

## Bootstrap e ciclo de request

Arquivo central: `system/engine/application.php`

Fluxo resumido:

1. carrega configs base (`system/config/default.php`) e config da area (`<area>/config.php`)
2. se existir, carrega `system/config/installed.php`
3. instancia objetos de infra:
   - `Request`, `Response`, `Session`, `Csrf`, `Logger`, `View`, `Auth`
4. cria `Database` quando credenciais estao configuradas
5. registra dependencias no `Registry`
6. resolve rota via `Router`
7. executa `Controller@metodo`
8. envia resposta final via `Response::send()`

## Roteamento

- cada area define suas rotas em `<area>/config.php`
- rotas aceitam parametros dinamicos no padrao `{id}`
- fallback para rota `404` quando nao houver correspondencia
- o `Request::getPath()` aceita:
  - query string `?route=...`
  - caminho amigavel relativo ao `index.php`

## Camadas

### Controller

Responsavel por fluxo de entrada, validacoes de contexto, redirecionamentos e composicao de dados para view.

Base compartilhada: `system/engine/controller.php`

Recursos comuns da base:
- `page()` para renderizar view + layout
- `redirect()` com `base_url`
- `validateCsrfToken()`
- `flash()` de mensagens em sessao

### Model

Responsavel por acesso a dados e regras de persistencia.

Exemplos:
- `catalog/model/resumemodel.php`: CRUD de curriculo, versionamento e persistencia de secoes
- `admin/model/*`: gestao de templates, anuncios, usuarios, settings

### View

Responsavel por apresentacao.

- `catalog/view/layout/main.php`
- `admin/view/layout/admin.php`
- `install/view/layout/install.php`

Views seguem escape de saida com helper `e()`.

### Language

Estrutura de pasta existe (`/language/pt-br`) para evolucao de internacionalizacao.

## Bibliotecas de infraestrutura

- `Database`: wrapper PDO com prepared statements
- `Auth`: autenticacao por papel (`user` ou `admin`)
- `Csrf`: token por sessao e validacao com `hash_equals`
- `Session`: sessao em `system/storage/sessions`
- `Logger`: arquivo + persistencia em tabela `logs` (quando DB disponivel)
- `View`: renderizacao de template/layout

## Convencoes

- namespaces por area (`AureaVertex\\Catalog\\...`, etc)
- autoload por prefixo e nomes de arquivo em minusculo (via `system/bootstrap.php`)
- controllers em `controller/<modulo>/...controller.php`
- models em `model/...model.php`
- views em `view/<modulo>/...php`

## Fluxo funcional de curriculo

1. usuario abre `resume/create`
2. `ResumeController` valida auth e CSRF
3. `ResumeModel::save()` persiste cabecalho do curriculo
4. `persistSections()` normaliza e grava secoes + tabelas auxiliares
5. `resume_versions` recebe snapshot JSON da versao
6. visualizacao em `resume/view` renderiza apenas campos preenchidos
7. exportacao PDF/JSON reutiliza payload detalhado do curriculo

## Fluxo de doacoes

1. rota publica `doacoes` chama `DonationController@index`
2. configuracoes sao lidas de `settings` via `SettingModel`
3. view `common/donate` exibe apenas metodos configurados
4. admin configura campos em `admin -> Configuracoes`

## Decisoes tecnicas relevantes

- fallback sem DB em alguns models (ex.: templates) para manter resiliencia
- PDF com fallback HTML quando Dompdf nao estiver instalado
- configuracao em banco (`settings`) para ajustes sem deploy
- sessao e storage local para ambiente simples de hospedagem compartilhada
