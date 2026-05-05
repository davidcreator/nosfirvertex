# NosfirVertex

Plataforma MVCL em PHP para criacao, edicao e exportacao de curriculos profissionais.

## Visao geral

NosfirVertex e organizado em quatro areas principais:

- `catalog`: area publica e area autenticada do usuario final
- `admin`: painel administrativo
- `install`: instalador guiado
- `system`: engine, bibliotecas e infraestrutura compartilhada

O produto hoje entrega:

- criacao e edicao de curriculos com formulario assistido
- validacao de datas em blocos criticos (formato `MM/AAAA`)
- ocultacao automatica de campos e secoes vazias na visualizacao final
- personalizacao visual (fonte e paleta de cores)
- exportacao em PDF, DOCX, JSON e preview em navegador
- recuperacao de senha com token de expiracao
- suporte de idioma `pt-br` e `en-us`
- pagina publica de doacoes com configuracao via admin

## Escopo funcional atual

### Catalog

- home institucional com entrada para login/cadastro
- cadastro, login, logout (POST + CSRF), esqueci senha e redefinicao por token
- painel do usuario com listagem de curriculos
- criacao/edicao por secoes (dados, resumo, experiencia, formacao, skills e complementares)
- galeria de templates (`route=templates`)
- visualizacao final do curriculo e atalhos para plataformas de emprego

### Admin

- login administrativo e dashboard com metricas
- filtros e paginacao para usuarios, curriculos e logs
- cadastro/listagem de templates
- cadastro/listagem de blocos de anuncio
- configuracoes globais com whitelist de chaves e secao de doacoes

Observacao: a interface atual do admin ainda nao expoe acoes de editar/excluir para templates e anuncios ja cadastrados.

### Install

- passo 1: requisitos/permissoes
- passo 2: base URL + teste de conexao com banco
- passo 3: criacao de administrador
- aplicacao de schema e seeds iniciais
- geracao de `system/config/installed.php`

## Requisitos

- PHP 8.1+
- extensoes PHP: `pdo`, `pdo_mysql`, `json`, `mbstring`, `dom`
- MySQL 5.7+ ou MariaDB compativel
- servidor web apontando para a raiz do projeto

Para exportacao DOCX:

- extensao PHP `zip` (`ZipArchive`)

Para exportacao PDF com Dompdf:

- Composer
- dependencias instaladas em `system/vendor`

## Instalacao rapida

1. Aponte o host para a raiz do projeto.
2. Acesse `http://seu-host/install/index.php`.
3. Conclua os 3 passos do instalador.
4. Ao final, valide a criacao de `system/config/installed.php`.

Acessos principais:

- Catalogo: `http://seu-host/catalog/index.php`
- Admin: `http://seu-host/admin/index.php`

## Dependencias de PDF

```bash
composer --working-dir=system install
```

Se o Dompdf nao estiver instalado, o endpoint de exportacao PDF fica indisponivel e o usuario recebe mensagem de orientacao.

## Qualidade e validacao local

Lint de PHP:

```bash
git ls-files '*.php' ':!:system/vendor/**' ':!:system/storage/**' | while IFS= read -r file; do php -l "$file"; done
```

PHPStan:

```bash
phpstan analyse -c phpstan.neon --no-progress
```

Smoke de rotas (GET + fluxos POST/CSRF):

```bash
php -S 127.0.0.1:8080 -t .
php tests/smoke/routes_smoke.php http://127.0.0.1:8080
```

## Estrutura do projeto

```text
/admin      painel administrativo
/catalog    area publica e painel do usuario
/docs       documentacao tecnica e funcional
/image      assets e previews de templates
/install    assistente de instalacao
/system     bootstrap, engine, libs e storage
/tests      smoke tests de rotas e CSRF
```

## Documentacao

- [Changelog](CHANGELOG.md)
- [Mapa da documentacao](docs/README.md)
- [Resumo do projeto](docs/PROJETO.md)
- [Arquitetura](docs/ARQUITETURA.md)
- [Instalacao detalhada](docs/INSTALACAO.md)
- [Rotas](docs/ROTAS.md)
- [Banco de dados](docs/BANCO_DE_DADOS.md)
- [Operacao administrativa](docs/OPERACAO_ADMIN.md)
- [Qualidade e CI](docs/QUALIDADE.md)
- [Roadmap tecnico (FREATURES)](docs/FREATURES.md)

## Observacoes

- `index.php` na raiz redireciona para `catalog/index.php`.
- Se `system/config/installed.php` nao existir, `catalog` e `admin` redirecionam para o instalador.
- A configuracao de idioma e persistida em sessao e pode ser alterada por `?lang=pt-br` / `?lang=en-us`.
