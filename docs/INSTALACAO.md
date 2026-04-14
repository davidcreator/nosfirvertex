# Instalacao NosfirVertex

## Requisitos minimos

- PHP 8.1+
- extensoes: PDO, PDO MySQL, JSON, MBString
- MySQL/MariaDB
- permissao de escrita em:
  - `system/storage`
  - `system/storage/logs`
  - `system/storage/sessions`
  - `system/config`
  - `system/vendor`
  - `image/templates`

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

### Passo 3 - administrador inicial

- nome do administrador
- email valido
- senha (minimo 8 caracteres)
- confirmacao de senha

Ao concluir:
- cria schema (`install/sql/schema.sql`)
- cria usuario admin
- aplica seeds iniciais (templates, anuncios, settings)
- grava `system/config/installed.php`

## Acesso apos instalacao

- catalogo: `http://seu-host/catalog/index.php`
- admin: `http://seu-host/admin/index.php`

## Dompdf (opcional)

Para exportacao PDF real:

```bash
composer --working-dir=system install
```

Sem Dompdf, o endpoint de PDF retorna HTML de fallback.

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
- revise configuracao do PHP para uso de sessoes

### PDF nao gera arquivo

- execute `composer --working-dir=system install`
- confirme se `system/vendor/autoload.php` existe
