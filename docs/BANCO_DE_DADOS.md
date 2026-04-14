# Banco de Dados

Arquivo de referencia do schema: `install/sql/schema.sql`.

## Tabelas principais

### Identidade e acesso

- `users`: contas de usuario/admin
- `profiles`: dados complementares de perfil
- `user_sessions`: sessao/token de usuario
- `password_resets`: tokens de recuperacao de senha

### Curriculos

- `resumes`: cabecalho do curriculo
- `resume_sections`: secoes em formato textual e metadados
- `resume_experiences`
- `resume_educations`
- `resume_courses`
- `resume_skills`
- `resume_languages`
- `resume_certifications`
- `resume_projects`
- `resume_links`
- `resume_versions`: snapshots historicos por atualizacao

### Conteudo e configuracao

- `templates`: modelos de curriculo
- `settings`: configuracoes globais
- `ad_blocks`: blocos de anuncios configuraveis
- `logs`: eventos registrados no sistema

## Relacoes relevantes

- `profiles.user_id` -> `users.user_id` (cascade delete)
- `resumes.user_id` -> `users.user_id` (cascade delete)
- `resumes.template_id` -> `templates.template_id` (set null)
- tabelas `resume_*` -> `resumes.resume_id` (cascade delete)
- `resume_versions.resume_id` -> `resumes.resume_id`
- `user_sessions.user_id` -> `users.user_id` (set null)
- `password_resets.user_id` -> `users.user_id` (cascade delete)

## Observacoes de modelagem

- `settings` usa chave unica (`key`) para feature flags e configuracoes de UI
- personalizacao visual do curriculo e armazenada em `resume_sections` com `section_key = design_options` (JSON)
- `resume_versions` guarda payload completo para trilha de alteracoes
- anuncios usam indice por `position_code + is_active + display_order`

## Seeds iniciais (instalador)

No primeiro install, o sistema cria dados iniciais em:
- templates padrao
- anuncios padrao da home
- settings basicas (incluindo configuracoes de doacao)

## Chaves de configuracao de doacao

Configuradas em `settings`:
- `donation_enabled`
- `donation_title`
- `donation_message`
- `donation_goal_text`
- `donation_pix_key`
- `donation_pix_beneficiary`
- `donation_paypal_url`
- `donation_bank_transfer`
- `donation_qr_image`
- `donation_thanks_message`
