# Qualidade e CI

Este documento descreve as validacoes automatizadas minimas do projeto.

## Pipeline

Workflow: `.github/workflows/ci.yml`

Executa em `push` (`main`, `master`, `develop`) e `pull_request`:

- lint de PHP (`php -l`) em arquivos versionados, excluindo `system/vendor` e `system/storage`
- analise estatica com PHPStan (`phpstan analyse -c phpstan.neon --no-progress`)
- smoke test HTTP de rotas principais e fluxos `POST/CSRF` via `tests/smoke/routes_smoke.php`

No CI, um `system/config/installed.php` temporario e gerado para exercitar fluxos de `catalog` e `admin` sem depender de banco.

## Execucao local

### 1) Instalar dependencias

```bash
composer --working-dir=system install
```

### 2) Lint de PHP

Linux/macOS:

```bash
git ls-files '*.php' ':!:system/vendor/**' ':!:system/storage/**' | while IFS= read -r file; do php -l "$file"; done
```

Windows PowerShell:

```powershell
git ls-files '*.php' ':!:system/vendor/**' ':!:system/storage/**' | ForEach-Object { php -l $_ }
```

### 3) PHPStan

```bash
phpstan analyse -c phpstan.neon --no-progress
```

Observacao: no CI o binario `phpstan` e provisionado como ferramenta global. Em ambiente local, instale-o globalmente ou ajuste para o binario disponivel na sua maquina.

### 4) Smoke de rotas

Terminal 1:

```bash
php -S 127.0.0.1:8080 -t .
```

Terminal 2:

```bash
php tests/smoke/routes_smoke.php http://127.0.0.1:8080
```

O smoke valida:

- GET basico das rotas principais
- ausencia de erros fatais na resposta
- fluxos CSRF de login/logout/toggle em `catalog` e `admin`

## Riscos e lacunas atuais

- ainda nao existem testes unitarios/integracao para models e controllers
- o quality gate atual prioriza regressao estrutural (lint + smoke + analise estatica)
