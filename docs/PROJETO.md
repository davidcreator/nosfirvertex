# Projeto NosfirVertex

## Objetivo

NosfirVertex e uma plataforma gratuita para criacao e exportacao de curriculos profissionais, com foco em simplicidade, experiencia mobile e resultado pratico para recolocacao profissional.

## Publico alvo

- usuarios que precisam criar curriculo de forma rapida
- usuarios com uso predominante em celular
- perfis iniciantes e intermediarios que precisam de orientacao no preenchimento

## Funcionalidades implementadas

### Area publica (catalog)

- home institucional com proposta de valor e entrada de autenticacao
- cadastro, login, logout (POST + CSRF), fluxo de esqueci senha e redefinicao por token
- painel do usuario com listagem de curriculos
- criacao e edicao de curriculo por secoes
- assistente de preenchimento com:
  - barra de progresso
  - checklist de blocos essenciais
  - dicas contextuais por campo
  - botao de insercao de exemplos
  - contador de caracteres
- validacao de datas em secoes criticas (`MM/AAAA` para experiencias e formacoes)
- personalizacao visual do curriculo:
  - tamanho de fonte
  - cor de destaque
  - cor de fundo do cabecalho
  - cor do texto do cabecalho
  - cor do texto do corpo
- preset visual padrao inspirado no LinkedIn
- visualizacao do curriculo final sem secoes vazias
- exportacao:
  - PDF (Dompdf quando instalado)
  - DOCX (ZipArchive habilitado)
  - JSON estruturado
  - preview HTML para navegacao
- pagina publica de doacoes (`route=doacoes`)
- suporte de idiomas `pt-br` e `en-us`

### Area administrativa (admin)

- login administrativo
- dashboard com totais principais
- gestao de usuarios com filtros e paginacao
- gestao de curriculos com filtros e paginacao
- cadastro e listagem de templates
- cadastro e listagem de anuncios
- gestao de configuracoes globais
- secao dedicada para configuracao da area de doacoes
- visualizacao de logs (banco + arquivo) com filtros e paginacao

Observacao operacional:

- a interface atual nao expoe acoes de editar/excluir para templates e anuncios ja cadastrados
- o modulo de anuncios esta preparado no admin e no banco, mas ainda nao foi integrado na renderizacao da home publica

### Instalador (install)

- passo 1: requisitos e permissoes
- passo 2: configuracao/teste de banco e base URL
- passo 3: criacao do administrador
- geracao de `system/config/installed.php`
- carga inicial de schema e seeds basicos

## Diferenciais atuais do produto

- experiencia de preenchimento guiada dentro do formulario
- validacao orientada para formato de curriculo (datas e organizacao por campos)
- visual final com padrao profissional e leitura objetiva
- ocultacao de campos nao preenchidos no resultado final
- configuracao de doacoes sem necessidade de ajuste manual no banco
- fluxo de erro padronizado com `request_id` para rastreabilidade

## Fluxo principal do usuario

1. criar conta
2. selecionar template
3. preencher formulario assistido
4. revisar visualizacao final
5. exportar em PDF, DOCX, JSON ou preview web
6. atualizar o curriculo e gerar snapshots em `resume_versions`

## Estado atual

O sistema esta funcional ponta a ponta (instalacao, operacao do catalogo, administracao e exportacao) e possui base arquitetural pronta para evolucao incremental.
