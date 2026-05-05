# FREATURES

Roadmap tecnico e funcional do NosfirVertex.

## Escopo da analise

Analise atualizada em 2026-05-05, considerando principalmente:

- `system/engine/*`
- `system/library/*`
- `catalog/controller/*` e `catalog/model/*`
- `admin/controller/*` e `admin/model/*`
- `install/controller/*` e `install/model/*`
- documentacao em `README.md` e `docs/*`

## Estado atual (resumo)

Concluido no codigo atual:

- `.gitignore` presente e removendo segredos/runtime
- `base_url()` prioriza config de `installed.php`
- sessao com hardening basico (`httponly`, `secure`, `samesite`, regeneracao de ID)
- logout/troca de tema com `POST + CSRF`
- recuperacao de senha com token de expiracao e invalidacao
- `save()` de curriculo com transacao
- logger com `request_id` em arquivo e metadata
- sanitizacao de HTML de anuncios com whitelist
- whitelist de settings no admin
- filtros e paginacao para usuarios/curriculos/logs
- quality gate com lint + phpstan + smoke

## Backlog priorizado

## P0 - Alta prioridade

### 1) Integrar exibicao real de anuncios no catalog

O que fazer:

- consumir `ad_blocks` ativos nas views publicas (ex.: home)
- respeitar flag `ads_enabled` em runtime
- manter fallback limpo quando nao houver blocos ativos

Beneficio:

- conecta o modulo administrativo de anuncios ao frontend real

Onde atuar:

- `catalog/controller/common/homecontroller.php`
- `catalog/model/admodel.php`
- `catalog/model/settingmodel.php`
- `catalog/view/common/home.php`

### 2) Editar/excluir templates e anuncios pela interface admin

O que fazer:

- adicionar acoes de editar/excluir na listagem
- pre-preencher formulario para edicao
- validar impacto de exclusao em registros referenciados

Beneficio:

- fecha ciclo de CRUD no backoffice

Onde atuar:

- `admin/view/template/index.php`
- `admin/view/ad/index.php`
- `admin/controller/template/templatecontroller.php`
- `admin/controller/ad/adcontroller.php`

### 3) Endurecer recuperacao de senha (entrega de email)

O que fazer:

- criar adaptador SMTP configuravel (sem depender apenas de `mail()`)
- registrar falhas de entrega com contexto padronizado
- opcional: fila simples para reenvio

Beneficio:

- reduz falhas operacionais no reset de senha

Onde atuar:

- `catalog/controller/account/authcontroller.php`
- nova camada de servico de email em `system/library`
- settings de transporte no admin

### 4) Expor historico de versoes para o usuario

O que fazer:

- listar `resume_versions` no dashboard
- permitir abrir snapshot historico
- opcional: restaurar snapshot como versao atual

Beneficio:

- agrega valor real ao versionamento ja persistido

Onde atuar:

- `catalog/model/resumemodel.php`
- novos controllers/views de historico

## P1 - Curto prazo

### 5) Fortalecer seguranca de conta

O que fazer:

- exigir senha atual para alterar senha na conta
- validar complexidade minima de nova senha
- registrar evento de troca de senha

Onde atuar:

- `catalog/controller/account/settingscontroller.php`
- `catalog/model/usermodel.php`
- `catalog/view/account/settings.php`

### 6) Rate limit de autenticacao

O que fazer:

- limitar tentativas de login/forgot por IP + email
- aplicar janela temporaria e mensagens genericas

Onde atuar:

- `catalog/controller/account/authcontroller.php`
- `admin/controller/common/logincontroller.php`
- tabela auxiliar de throttling ou uso de cache

### 7) Cobertura de testes de dominio

O que fazer:

- adicionar testes de regressao para `ResumeModel` e `UserModel`
- validar parser de secoes e regras de data

Onde atuar:

- criar `tests/unit/*`
- bootstrap de testes com DB isolado

## P2 - Medio prazo

### 8) Migracoes versionadas de banco

O que fazer:

- introduzir mecanismo de migracao incremental
- separar instalacao nova de upgrades em ambiente existente

Onde atuar:

- novo modulo de migracao
- `install/sql/schema.sql`
- documentacao de upgrade em `docs/BANCO_DE_DADOS.md`

### 9) API publica para integracoes

O que fazer:

- expor endpoint autenticado para exportacao estruturada
- versionamento de contrato e limite de taxa

Onde atuar:

- novos controllers/routes API
- camada de autenticacao por token

## Criterio de pronto

Cada item deve ser concluido com:

- validacao automatizada minima (teste ou smoke dedicado)
- atualizacao da documentacao relevante (`README.md` e/ou `docs/*`)
- registro no `CHANGELOG.md`
