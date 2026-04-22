# Operacao Administrativa

## Acesso

- URL: `admin/index.php`
- autenticacao obrigatoria com conta `role = admin`

## Modulos do painel

### Dashboard

- mostra totais de usuarios, curriculos e templates
- usado para visao rapida da operacao

### Usuarios

- lista contas cadastradas
- apoia auditoria basica de crescimento e status
- filtros por texto, papel, status e periodo
- paginacao configuravel (20, 50, 100 itens por pagina)

### Curriculos

- lista curriculos criados na plataforma
- permite acompanhar volume e atividade
- filtros por texto, status e periodo de atualizacao
- paginacao configuravel (20, 50, 100 itens por pagina)

### Templates

- cria e edita templates
- campos principais:
  - nome
  - categoria
  - caminho da imagem (`image_path`)
  - descricao
  - ativo/inativo

### Anuncios

- cria e edita blocos de anuncio
- campos principais:
  - nome
  - `position_code` (ex.: `home_top`, `home_mid`, `home_footer`)
  - HTML do conteudo
  - ativo/inativo
  - ordem de exibicao

### Configuracoes

- gerencia chaves de `settings`
- possui secao dedicada para doacoes

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
- paginacao configuravel (20, 50, 100 itens por pagina)

## Checklist operacional recomendado

Diario:
1. validar login admin e status geral do dashboard
2. revisar erros recentes em logs
3. conferir anuncios ativos e ordem

Semanal:
1. revisar templates ativos e imagens
2. validar fluxo de exportacao PDF e JSON
3. revisar configuracoes de doacao e pagina publica `catalog/index.php?route=doacoes`

## Boas praticas

- manter HTML de anuncios limpo e nao intrusivo
- evitar remover templates em uso sem validar impacto
- registrar mudancas operacionais criticas
- executar backup do banco antes de alteracoes estruturais
