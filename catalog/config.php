<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'NosfirVertex',
        'layout' => 'layout/main',
        'area' => 'catalog',
    ],
    'routes' => [
        '' => 'NosfirVertex\\Catalog\\Controller\\Common\\HomeController@index',
        'login' => 'NosfirVertex\\Catalog\\Controller\\Account\\AuthController@login',
        'register' => 'NosfirVertex\\Catalog\\Controller\\Account\\AuthController@register',
        'logout' => 'NosfirVertex\\Catalog\\Controller\\Account\\AuthController@logout',
        'password/forgot' => 'NosfirVertex\\Catalog\\Controller\\Account\\AuthController@forgot',
        'dashboard' => 'NosfirVertex\\Catalog\\Controller\\Account\\DashboardController@index',
        'account/settings' => 'NosfirVertex\\Catalog\\Controller\\Account\\SettingsController@index',
        'resume/create' => 'NosfirVertex\\Catalog\\Controller\\Resume\\ResumeController@create',
        'resume/edit/{id}' => 'NosfirVertex\\Catalog\\Controller\\Resume\\ResumeController@edit',
        'resume/view/{id}' => 'NosfirVertex\\Catalog\\Controller\\Resume\\ResumeController@view',
        'resume/delete/{id}' => 'NosfirVertex\\Catalog\\Controller\\Resume\\ResumeController@delete',
        'resume/export/pdf/{id}' => 'NosfirVertex\\Catalog\\Controller\\Export\\ExportController@pdf',
        'resume/export/browser/{id}' => 'NosfirVertex\\Catalog\\Controller\\Export\\ExportController@browser',
        'resume/export/json/{id}' => 'NosfirVertex\\Catalog\\Controller\\Export\\ExportController@json',
        'templates' => 'NosfirVertex\\Catalog\\Controller\\Template\\TemplateController@index',
        'doacoes' => 'NosfirVertex\\Catalog\\Controller\\Common\\DonationController@index',
        'theme/toggle' => 'NosfirVertex\\Catalog\\Controller\\Common\\ThemeController@toggle',
        '404' => 'NosfirVertex\\Catalog\\Controller\\Common\\ErrorController@notFound',
    ],
];
