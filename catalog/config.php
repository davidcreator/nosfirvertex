<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'AureaVertex',
        'layout' => 'layout/main',
        'area' => 'catalog',
    ],
    'routes' => [
        '' => 'AureaVertex\\Catalog\\Controller\\Common\\HomeController@index',
        'login' => 'AureaVertex\\Catalog\\Controller\\Account\\AuthController@login',
        'register' => 'AureaVertex\\Catalog\\Controller\\Account\\AuthController@register',
        'logout' => 'AureaVertex\\Catalog\\Controller\\Account\\AuthController@logout',
        'password/forgot' => 'AureaVertex\\Catalog\\Controller\\Account\\AuthController@forgot',
        'dashboard' => 'AureaVertex\\Catalog\\Controller\\Account\\DashboardController@index',
        'account/settings' => 'AureaVertex\\Catalog\\Controller\\Account\\SettingsController@index',
        'resume/create' => 'AureaVertex\\Catalog\\Controller\\Resume\\ResumeController@create',
        'resume/edit/{id}' => 'AureaVertex\\Catalog\\Controller\\Resume\\ResumeController@edit',
        'resume/view/{id}' => 'AureaVertex\\Catalog\\Controller\\Resume\\ResumeController@view',
        'resume/delete/{id}' => 'AureaVertex\\Catalog\\Controller\\Resume\\ResumeController@delete',
        'resume/export/pdf/{id}' => 'AureaVertex\\Catalog\\Controller\\Export\\ExportController@pdf',
        'resume/export/browser/{id}' => 'AureaVertex\\Catalog\\Controller\\Export\\ExportController@browser',
        'resume/export/json/{id}' => 'AureaVertex\\Catalog\\Controller\\Export\\ExportController@json',
        'templates' => 'AureaVertex\\Catalog\\Controller\\Template\\TemplateController@index',
        'doacoes' => 'AureaVertex\\Catalog\\Controller\\Common\\DonationController@index',
        'theme/toggle' => 'AureaVertex\\Catalog\\Controller\\Common\\ThemeController@toggle',
        '404' => 'AureaVertex\\Catalog\\Controller\\Common\\ErrorController@notFound',
    ],
];
