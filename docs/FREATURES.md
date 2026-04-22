# FREATURES

Sugestoes de melhorias aplicaveis ao projeto NosfirVertex com foco em seguranca, confiabilidade, operacao e evolucao de produto.

## Escopo da analise

Analise realizada em 2026-04-15, considerando principalmente:

- `system/engine/*`
- `system/library/*`
- `catalog/controller/*` e `catalog/model/*`
- `admin/controller/*` e `admin/model/*`
- `install/controller/*` e `install/model/*`
- documentacao em `docs/*`

## Achados rapidos

- Nao existe `.gitignore` no repositorio.
- `system/config/installed.php` esta versionado (inclui credenciais locais).
- Existem arquivos de runtime dentro de `system/storage` (sessoes e arquivos de teste).
- O fluxo de recuperacao de senha gera token, mas ainda nao conclui reset por link.
- O token de recuperacao esta sendo gravado em log.
- Salvar curriculo envolve multiplas operacoes sem transacao unica.
- A `base_url` configurada no instalador nao esta sendo usada pelo helper global.

## Backlog priorizado

## P0 - Aplicar primeiro (alto impacto)

### 1) Higiene de repositorio e segredos

O que fazer:

- Criar `.gitignore` para ignorar `system/storage/*`, `system/config/installed.php` e artefatos locais.
- Remover do versionamento arquivos de sessao, logs e arquivos temporarios.
- Manter apenas `system/config/installed.php.example` no Git.

Beneficio:

- Reduz risco de vazamento de credenciais e dados sensiveis.

Onde atuar:

- `.gitignore` (novo arquivo)
- `system/config/installed.php`
- `system/storage/*`

### 2) Hardening de sessao e autenticacao

O que fazer:

- Regenerar ID de sessao no login e logout.
- Configurar cookie de sessao com `httponly`, `secure` (quando HTTPS), `samesite`.
- Trocar `logout` e `theme/toggle` para POST com CSRF.
- Validar `HTTP_REFERER` como same-origin antes de redirecionar.

Beneficio:

- Reduz risco de session fixation, CSRF e open redirect.

Onde atuar:

- `system/library/session.php`
- `system/library/auth.php`
- `catalog/controller/account/authcontroller.php`
- `catalog/controller/common/themecontroller.php`
- views com links de logout/toggle

### 3) Recuperacao de senha completa e segura

O que fazer:

- Implementar fluxo completo: solicitar token -> enviar email -> redefinir senha -> invalidar token.
- Remover token dos logs imediatamente.
- Adicionar expiracao/uso unico validado por banco.

Beneficio:

- Fecha uma funcionalidade critica que hoje esta incompleta.

Onde atuar:

- `catalog/controller/account/authcontroller.php`
- `catalog/model/usermodel.php`
- novas rotas/view para reset
- tabela `password_resets`

### 4) Transacao unica ao salvar curriculo

O que fazer:

- Envolver `save()` de curriculo em `Database::transaction()`.
- Garantir rollback em qualquer falha entre update principal, secoes e versao.

Beneficio:

- Evita inconsistencias e perda parcial de dados.

Onde atuar:

- `catalog/model/resumemodel.php`
- `system/library/database.php`

### 5) Corrigir estrategia de URL base

O que fazer:

- Fazer `base_url()` priorizar `app.base_url` de configuracao.
- Manter fallback automatico apenas quando configuracao estiver ausente.

Beneficio:

- Evita links quebrados em proxy reverso/subdiretorios e torna instalador coerente.

Onde atuar:

- `system/helper/common.php`
- `system/config/installed.php`
- `install/model/installermodel.php`

## P1 - Curto prazo (operacao e qualidade)

### 6) Melhorar tratamento global de erro

O que fazer:

- Retornar status HTTP 500 em excecoes nao tratadas.
- Exibir pagina de erro padronizada em vez de HTML inline.
- Adicionar `request_id` no log para rastreabilidade.

Onde atuar:

- `system/engine/application.php`
- `system/library/logger.php`

### 7) Sanitizacao de HTML de anuncios

O que fazer:

- Substituir `strip_tags` simples por sanitizacao de atributos e protocolos permitidos.
- Bloquear `javascript:` e atributos inline perigosos.

Onde atuar:

- `catalog/view/common/home.php`
- `admin/model/admodel.php`

### 8) Validacao mais forte nas configuracoes

O que fazer:

- Trocar persistencia generica de `allPost()` por whitelist de chaves editaveis.
- Validar URL de pagamento, imagem QR e limites de tamanho de texto.

Onde atuar:

- `admin/controller/setting/settingcontroller.php`
- `admin/model/settingmodel.php`

### 9) Paginacao e filtros no admin

O que fazer:

- Adicionar filtros por data/status e paginacao em usuarios, curriculos e logs.

Onde atuar:

- `admin/model/usermodel.php`
- `admin/model/resumemodel.php`
- `admin/model/logmodel.php`
- views admin

### 10) Pipeline de qualidade (CI)

O que fazer:

- Adicionar testes de smoke para rotas principais.
- Incluir analise estatica e lint de PHP.
- Rodar tudo em CI a cada pull request.

Onde atuar:

- criar pasta `tests/`
- `system/composer.json` (dev dependencies)
- workflow CI (ex.: GitHub Actions)

## P2 - Evolucao funcional

### 11) CRUD administrativo completo

O que fazer:

- Permitir editar/excluir itens ja cadastrados em templates e anuncios.
- Adicionar acoes de ativar/desativar sem reenvio manual de IDs.

Onde atuar:

- `admin/view/template/index.php`
- `admin/view/ad/index.php`
- controllers e models admin correspondentes

### 12) UX de conta e seguranca do usuario

O que fazer:

- Exigir senha atual para trocar senha.
- Adicionar confirmacao de nova senha.
- Validar formato de URL em website/linkedin/github.

Onde atuar:

- `catalog/view/account/settings.php`
- `catalog/controller/account/settingscontroller.php`
- `catalog/model/usermodel.php`

### 13) Historico de versoes de curriculo para o usuario

O que fazer:

- Exibir `resume_versions` no painel.
- Permitir comparacao e restauracao de versoes.

Onde atuar:

- `catalog/model/resumemodel.php`
- novos controllers/views de versao

### 14) Limpeza de assets nao usados

O que fazer:

- Revisar bibliotecas front-end grandes atualmente nao referenciadas diretamente.
- Remover ou mover para pacote opcional o que nao for usado em runtime.

Onde atuar:

- `catalog/view/js/*`
- `admin/js/*`

### 15) Estrategia de migracoes de banco

O que fazer:

- Introduzir migracoes versionadas em vez de depender apenas de `schema.sql`.
- Criar rotina de upgrade para ambientes ja instalados.

Onde atuar:

- novo modulo de migracoes
- `install/sql/schema.sql`
- documentacao em `docs/BANCO_DE_DADOS.md`

## Sugestao de execucao por sprint

Sprint 1:

- Itens 1, 2, 3 e 4

Sprint 2:

- Itens 5, 6, 7, 8 e 9

Sprint 3:

- Itens 10, 11, 12, 13, 14 e 15

## Criterio de pronto (geral)

Cada item deve ser considerado concluido somente quando:

- possuir validacao automatizada minima (teste ou checklist manual documentado)
- possuir atualizacao de documentacao em `docs/`
- possuir registro no `CHANGELOG.md`
