# Operacao Administrativa

## Acesso

- URL: `admin/index.php`
- autenticacao obrigatoria com conta `role = admin`
- logout protegido por `POST + CSRF`

## Modulos do painel

### Dashboard

- mostra totais de usuarios, curriculos, templates, anuncios e logs
- oferece atalhos rapidos para os principais modulos

### Usuarios

- lista contas cadastradas
- filtros por texto, papel, status e periodo
- paginacao configuravel (20, 50, 100)

### Curriculos

- lista curriculos criados na plataforma
- filtros por texto, status e periodo de atualizacao
- paginacao configuravel (20, 50, 100)

### Templates

- cadastro de novos templates
- listagem de templates existentes
- campos principais:
  - nome
  - categoria
  - caminho da imagem (`image_path`)
  - descricao
  - ativo/inativo

### Anuncios

- cadastro de novos blocos de anuncio
- listagem de blocos existentes
- campos principais:
  - nome
  - `position_code` (ex.: `home_top`, `home_mid`, `home_footer`)
  - HTML do conteudo
  - ativo/inativo
  - ordem de exibicao

### Configuracoes

- persistencia de `settings` com whitelist de chaves permitidas
- secao dedicada para doacoes

Campos de doacao no admin:

- ativar/desativar doacoes
- titulo e mensagem
- chave PIX e nome do beneficiario
- link de pagamento online
- dados de transferencia bancaria
- caminho/URL da imagem de QR code
- mensagem de agradecimento

### Logs

- visualiza logs persistidos em banco
- mostra cauda do arquivo `system/storage/logs/app.log`
- filtros por texto, nivel, contexto, `request_id` e periodo
- paginacao configuravel (20, 50, 100)

## Limitacoes atuais da interface

- templates e anuncios ainda nao possuem acoes de editar/excluir dedicadas na UI
- o modulo de anuncios esta operacional no admin e no banco, mas a home publica ainda nao consome esses blocos

## Checklist operacional recomendado

Diario:

1. validar login admin e status geral do dashboard
2. revisar erros recentes em logs
3. conferir novos cadastros de usuarios e curriculos

Semanal:

1. revisar templates ativos e caminhos de imagem
2. validar fluxo de exportacao PDF, DOCX e JSON
3. revisar configuracoes de doacao e pagina publica `catalog/index.php?route=doacoes`

## Boas praticas

- manter HTML de anuncios limpo e nao intrusivo
- validar consistencia de categorias de template
- registrar alteracoes operacionais criticas
- executar backup do banco antes de alteracoes estruturais
