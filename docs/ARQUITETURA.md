# Arquitetura NosfirVertex

## Visao geral

NosfirVertex usa arquitetura MVCL em PHP puro, com organizacao por areas:

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
   - `Request`, `Response`, `Session`, `Csrf`, `Language`
4. define `request_id` e injeta em header (`X-Request-Id`)
5. cria `Database` quando credenciais estao configuradas
6. instancia `Logger`, `View` e `Auth`
7. registra dependencias no `Registry`
8. resolve rota via `Router`
9. executa `Controller@metodo`
10. envia resposta final via `Response::send()`

Em caso de excecao nao tratada:

- registra erro com `request_id`
- responde com HTTP `500`
- renderiza pagina de erro padronizada com `request_id`

## Roteamento

- cada area define suas rotas em `<area>/config.php`
- rotas aceitam parametros dinamicos no padrao `{id}`
- fallback para rota `404` quando nao houver correspondencia
- `Request::getPath()` aceita:
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

- `catalog/model/resumemodel.php`: CRUD de curriculo, persistencia de secoes e snapshot em `resume_versions`
- `catalog/model/usermodel.php`: conta do usuario e fluxo de recuperacao de senha
- `admin/model/*`: usuarios, curriculos, templates, anuncios, settings e logs

### View

Responsavel por apresentacao.

- `catalog/view/layout/main.php`
- `admin/view/layout/admin.php`
- `install/view/layout/install.php`

Views usam escape de saida com helper `e()` e podem usar `lang()` para traducao.

### Language (i18n)

Internacionalizacao ativa via `system/library/language.php`:

- descoberta automatica de locales por pasta (`pt-br`, `en-us`)
- fallback configuravel (`app.locale`)
- persistencia de locale em sessao
- troca de idioma por `?lang=<locale>`

## Bibliotecas de infraestrutura

- `Database`: wrapper PDO com prepared statements e helper de transacao
- `Auth`: autenticacao por papel (`user` ou `admin`) + regeneracao de sessao no login/logout
- `Csrf`: token por sessao e validacao com `hash_equals`
- `Session`: cookies com `httponly`, `samesite=lax` e `secure` em HTTPS
- `Logger`: arquivo + persistencia em tabela `logs` (quando DB disponivel)
- `Language`: traducao por arquivos de locale e mapa de frases
- `View`: renderizacao de template/layout

## Fluxo funcional de curriculo

1. usuario abre `resume/create`
2. `ResumeController` valida auth e CSRF
3. `ResumeModel::save()` persiste cabecalho
4. `persistSections()` normaliza/grava secoes e tabelas auxiliares
5. `resume_versions` recebe snapshot JSON da versao
6. `resume/view` renderiza apenas campos preenchidos
7. exportacoes PDF/DOCX/JSON/browser reutilizam payload detalhado

## Fluxo de recuperacao de senha

1. usuario acessa `password/forgot`
2. `UserModel::createPasswordReset()` invalida tokens ativos e cria token com expiracao de 1 hora
3. `AuthController` tenta envio de email via `mail()` com link de reset
4. rota `password/reset/{token}` valida token e redefine senha
5. token e marcado como usado e nao pode ser reutilizado

## Fluxo de doacoes

1. rota publica `doacoes` chama `DonationController@index`
2. configuracoes sao lidas de `settings`
3. view exibe apenas metodos preenchidos
4. admin configura campos em `settings`

## Convencoes

- namespaces por area (`NosfirVertex\\Catalog\\...`, etc)
- autoload por prefixo e nomes de arquivo em minusculo (`system/bootstrap.php`)
- controllers em `controller/<modulo>/...controller.php`
- models em `model/...model.php`
- views em `view/<modulo>/...php`

## Limitacoes atuais

- exibicao de anuncios ainda nao esta integrada na home publica, apesar do modulo administrativo existir
- templates e anuncios possuem create/list no admin; edicao/exclusao ainda nao possuem fluxo de interface dedicado
- envio de email de recuperacao depende de `mail()` do ambiente (sem provedor SMTP dedicado)
