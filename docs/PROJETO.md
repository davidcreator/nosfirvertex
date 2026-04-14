# Projeto NosfirVertex

## Objetivo

NosfirVertex e uma plataforma gratuita para criacao e exportacao de curriculos profissionais, com foco em simplicidade, experiencia mobile e resultado pratico para recolocacao profissional.

## Publico alvo

- usuarios que precisam criar curriculo de forma rapida
- usuarios com uso predominante em celular
- perfis iniciantes e intermediarios que precisam de orientacao no preenchimento

## Funcionalidades implementadas

### Area publica (catalog)

- home com proposta de valor, templates demonstrativos e blocos de anuncios
- cadastro, login, logout e fluxo de esqueci senha
- painel do usuario com listagem de curriculos
- criacao e edicao de curriculo por secoes
- assistente de preenchimento com:
  - barra de progresso
  - checklist de blocos essenciais
  - dicas contextuais por campo
  - botao de insercao de exemplos
  - contador de caracteres
- personalizacao visual do curriculo:
  - tamanho de fonte
  - cor de destaque
  - cor de fundo do cabecalho
  - cor do texto do corpo
- preset visual padrao inspirado no LinkedIn
- visualizacao do curriculo final sem secoes vazias
- exportacao:
  - PDF (Dompdf quando instalado)
  - JSON estruturado para integracoes futuras
- pagina publica de doacoes (`route=doacoes`)

### Area administrativa (admin)

- login administrativo
- dashboard com totais principais
- gestao de usuarios
- gestao de curriculos
- gestao de templates
- gestao de anuncios
- gestao de configuracoes globais
- secao dedicada para configuracao da area de doacoes
- visualizacao de logs (banco + arquivo)

### Instalador (install)

- passo 1: requisitos e permissoes
- passo 2: configuracao/teste de banco
- passo 3: criacao do administrador
- geracao de `system/config/installed.php`
- carga inicial de schema e seeds basicos

## Diferenciais atuais do produto

- experiencia de preenchimento guiada dentro do formulario
- visual final com padrao de curriculo moderno e leitura profissional
- ocultacao de campos nao preenchidos no resultado final
- configuracao de doacoes sem necessidade de ajuste manual no banco

## Fluxo principal do usuario

1. criar conta
2. selecionar template
3. preencher formulario assistido
4. revisar visualizacao final
5. exportar em PDF ou JSON
6. manter versoes historicas por atualizacao

## Estado atual

O sistema esta funcional para uso ponta a ponta (instalacao, operacao do catalogo, administracao e exportacao), com base arquitetural preparada para evolucao incremental.
