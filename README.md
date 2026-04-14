# AureaVertex

Plataforma MVCL em PHP para criacao, edicao e exportacao de curriculos profissionais.

## Visao geral

AureaVertex foi construida com arquitetura inspirada no ReamurCMS, separando claramente:
- `catalog` (area do usuario final)
- `admin` (painel administrativo)
- `install` (instalador guiado)
- `system` (engine, libs e infraestrutura)

Principais pontos do produto atual:
- criacao de curriculo com formulario assistido
- preset visual estilo LinkedIn como padrao
- ocultacao automatica de campos/secoes vazias no curriculo final
- personalizacao visual (fonte e cores)
- exportacao em PDF (com Dompdf) e JSON estruturado
- blocos de anuncios discretos na home
- area publica de doacoes com configuracao via admin

## Requisitos

- PHP 8.1+
- extensoes: PDO, PDO MySQL, JSON, MBString
- MySQL 5.7+ ou MariaDB equivalente
- servidor web (Apache/Nginx) apontando para a raiz do projeto

Opcional para PDF real:
- Composer instalado
- dependencias de `system/composer.json`

## Instalacao rapida

1. Aponte o host para a raiz do projeto.
2. Acesse `http://seu-host/install/index.php`.
3. Conclua os 3 passos do instalador:
   - validacao de requisitos/permissoes
   - configuracao e teste de banco
   - criacao do usuario administrador
4. Ao finalizar, o arquivo `system/config/installed.php` sera gerado.

Acessos:
- Catalogo: `http://seu-host/catalog/index.php`
- Admin: `http://seu-host/admin/index.php`

## Dependencias de PDF

Para habilitar geracao PDF com Dompdf:

```bash
composer --working-dir=system install
```

Se Dompdf nao estiver instalado, o sistema exibe HTML de fallback no endpoint de exportacao PDF.

## Estrutura do projeto

```text
/admin      painel administrativo
/catalog    area publica e painel do usuario
/docs       documentacao tecnica e funcional
/image      assets e previews de templates
/install    assistente de instalacao
/system     bootstrap, engine, libs e storage
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

## Observacoes

- O arquivo `index.php` da raiz redireciona para `catalog/index.php`.
- Em `catalog` e `admin`, se `system/config/installed.php` nao existir, o fluxo redireciona para instalacao.
- As configuracoes da area de doacoes ficam no admin em `Configuracoes`.
