# Rotas do Sistema

## Catalog (`catalog/config.php`)

- `""` -> HomeController@index
- `login` -> AuthController@login
- `register` -> AuthController@register
- `logout` -> AuthController@logout
- `password/forgot` -> AuthController@forgot
- `dashboard` -> DashboardController@index
- `account/settings` -> SettingsController@index
- `resume/create` -> ResumeController@create
- `resume/edit/{id}` -> ResumeController@edit
- `resume/view/{id}` -> ResumeController@view
- `resume/delete/{id}` -> ResumeController@delete
- `resume/export/pdf/{id}` -> ExportController@pdf
- `resume/export/json/{id}` -> ExportController@json
- `templates` -> TemplateController@index
- `doacoes` -> DonationController@index
- `theme/toggle` -> ThemeController@toggle
- `404` -> ErrorController@notFound

## Admin (`admin/config.php`)

- `""` -> DashboardController@index
- `login` -> LoginController@index
- `logout` -> LoginController@logout
- `dashboard` -> DashboardController@index
- `users` -> UserController@index
- `resumes` -> ResumeController@index
- `templates` -> TemplateController@index
- `ads` -> AdController@index
- `settings` -> SettingController@index
- `logs` -> LogController@index
- `404` -> DashboardController@notFound

## Install (`install/config.php`)

- `""` -> InstallerController@index
- `step/1` -> InstallerController@step1
- `step/1/next` -> InstallerController@step1Next
- `step/2` -> InstallerController@step2
- `step/2/test-db` -> InstallerController@testDb
- `step/2/next` -> InstallerController@step2Next
- `step/3` -> InstallerController@step3
- `run` -> InstallerController@run
- `restart` -> InstallerController@restart
- `404` -> InstallerController@notFound

## Observacoes

- parametros dinamicos usam formato `{id}`
- rotas podem ser chamadas por `?route=...`
- quando rota nao existe, o fallback `404` da area e acionado
