# Instalacao NosfirVertex

## Requisitos minimos

- PHP 8.1+
- extensoes: `pdo`, `pdo_mysql`, `json`, `mbstring`, `dom`
- MySQL/MariaDB
- permissao de escrita em:
  - `system/storage`
  - `system/storage/logs`
  - `system/storage/sessions`
  - `system/config`
  - `system/vendor`
  - `image/templates`

Requisitos opcionais por feature:

- PDF: Composer + dependencias em `system/vendor` (Dompdf)
- DOCX: extensao PHP `zip` (`ZipArchive`)
- recuperacao de senha por email: funcao `mail()` habilitada

## Preparacao

1. copie os arquivos para o servidor
2. aponte o host para a raiz do projeto
3. acesse `http://seu-host/install/index.php`

## Passos do instalador

### Passo 1 - ambiente

- valida requisitos de PHP/extensoes
- valida permissao de escrita nas pastas necessarias
- libera avancar apenas quando tudo estiver OK

### Passo 2 - banco e URL base

- define `base_url`
- informa host, porta, banco, usuario e senha
- opcao para criar banco automaticamente se nao existir
- botao de teste valida conexao antes de seguir
- exibicao de diagnostico da conexao (servidor, usuario autenticado e existencia do schema)

### Passo 3 - administrador inicial

- nome do administrador
- email valido
- senha (minimo 8 caracteres)
- confirmacao de senha

Ao concluir:

- cria schema (`install/sql/schema.sql`)
- cria usuario admin
- aplica seeds iniciais (templates, anuncios e settings)
- grava `system/config/installed.php`

## Acesso apos instalacao

- catalogo: `http://seu-host/catalog/index.php`
- admin: `http://seu-host/admin/index.php`

## Dependencias de exportacao

### PDF (Dompdf)

```bash
composer --working-dir=system install
```

Sem Dompdf, a exportacao PDF fica indisponivel.

### DOCX

- a exportacao DOCX usa `ZipArchive`
- se a extensao `zip` nao estiver habilitada, a exportacao DOCX fica indisponivel

## Troubleshooting rapido

### Instalador sempre reaparece

- verifique se `system/config/installed.php` foi criado
- confira permissao de escrita em `system/config`

### Erro de conexao com banco

- revise host/porta/credenciais
- valide se o usuario tem permissao para criar banco (quando opcao marcada)
- tente desmarcar criacao automatica e usar banco ja existente

### Sessao nao persiste

- confirme permissao em `system/storage/sessions`
- revise configuracao de sessao no PHP

### PDF nao gera arquivo

- execute `composer --working-dir=system install`
- confirme se `system/vendor/autoload.php` existe

### Recuperacao de senha nao envia email

- valide se `mail()` esta habilitado no ambiente
- confira logs em `system/storage/logs/app.log`
