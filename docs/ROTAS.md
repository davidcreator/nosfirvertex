# Rotas do Sistema

## Catalog (`catalog/config.php`)

- `GET /` -> HomeController@index
- `GET|POST login` -> AuthController@login
- `GET|POST register` -> AuthController@register
- `POST logout` -> AuthController@logout
- `GET|POST password/forgot` -> AuthController@forgot
- `GET|POST password/reset/{token}` -> AuthController@reset
- `GET dashboard` -> DashboardController@index
- `GET|POST account/settings` -> SettingsController@index
- `GET|POST resume/create` -> ResumeController@create
- `GET|POST resume/edit/{id}` -> ResumeController@edit
- `GET resume/view/{id}` -> ResumeController@view
- `POST resume/delete/{id}` -> ResumeController@delete
- `GET resume/export/pdf/{id}` -> ExportController@pdf
- `GET resume/export/docx/{id}` -> ExportController@docx
- `GET resume/export/browser/{id}` -> ExportController@browser
- `GET resume/export/json/{id}` -> ExportController@json
- `GET templates` -> TemplateController@index
- `GET doacoes` -> DonationController@index
- `POST theme/toggle` -> ThemeController@toggle
- `GET 404` -> ErrorController@notFound

## Admin (`admin/config.php`)

- `GET /` -> DashboardController@index
- `GET|POST login` -> LoginController@index
- `POST logout` -> LoginController@logout
- `GET dashboard` -> DashboardController@index
- `GET users` -> UserController@index
- `GET resumes` -> ResumeController@index
- `GET|POST templates` -> TemplateController@index
- `GET|POST ads` -> AdController@index
- `GET|POST settings` -> SettingController@index
- `GET logs` -> LogController@index
- `GET 404` -> DashboardController@notFound

## Install (`install/config.php`)

- `GET /` -> InstallerController@index
- `GET step/1` -> InstallerController@step1
- `POST step/1/next` -> InstallerController@step1Next
- `GET step/2` -> InstallerController@step2
- `POST step/2/test-db` -> InstallerController@testDb
- `POST step/2/next` -> InstallerController@step2Next
- `GET step/3` -> InstallerController@step3
- `POST run` -> InstallerController@run
- `GET restart` -> InstallerController@restart
- `GET 404` -> InstallerController@notFound

## Observacoes

- parametros dinamicos usam formato `{id}` / `{token}`
- rotas podem ser chamadas por `?route=...`
- quando rota nao existe, o fallback `404` da area e acionado
- escrita sensivel (logout, toggle de tema, exclusao, instalacao) usa `POST + CSRF`
