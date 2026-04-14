# Changelog

Todas as mudancas relevantes deste projeto serao documentadas neste arquivo.

Formato inspirado em Keep a Changelog.

## [Unreleased]

### Added
- Estrutura inicial de changelog para rastrear evolucao por versao e data.

## [0.1.0] - 2026-04-13

### Added
- Estrutura MVCL completa com separacao por areas `catalog`, `admin`, `install` e `system`.
- Instalador guiado em 3 passos com validacao de requisitos, conexao com banco e criacao de administrador.
- CRUD de curriculos com versionamento em `resume_versions`.
- Exportacao de curriculo em JSON estruturado para integracoes futuras.
- Exportacao de curriculo em PDF com suporte a Dompdf (fallback HTML quando indisponivel).
- Galeria de templates com seeds iniciais e gerenciamento no admin.
- Blocos de anuncios configuraveis por posicao na home.
- Modo de tema claro/escuro para navegacao no catalogo.
- Area publica de doacoes (`route=doacoes`) com metodos condicionais (PIX, link online, transferencia).
- Configuracao de doacoes no admin em `Configuracoes`.
- Assistente de preenchimento no formulario de curriculo (progresso, checklist, dicas e exemplos).

### Changed
- Preview e exportacao do curriculo ajustados para padrao visual profissional com preset LinkedIn como base.
- Renderizacao final alterada para ocultar secoes e campos vazios.
- Personalizacao visual do curriculo ampliada com fonte e cores configuraveis.

### Security
- Validacao de token CSRF em fluxos de escrita (catalog, admin e install).
- Sanitizacao/escape de saida nas views com helper `e()`.
- Validacao de URL externa na area de doacoes para aceitar apenas `http/https`.

### Docs
- Documentacao tecnica e funcional consolidada em `docs/`.
- README da raiz atualizado com instalacao, estrutura e mapa de documentacao.
