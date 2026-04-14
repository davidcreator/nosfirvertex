<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'AureaVertex Admin',
        'layout' => 'layout/admin',
        'area' => 'admin',
    ],
    'routes' => [
        '' => 'AureaVertex\\Admin\\Controller\\Common\\DashboardController@index',
        'login' => 'AureaVertex\\Admin\\Controller\\Common\\LoginController@index',
        'logout' => 'AureaVertex\\Admin\\Controller\\Common\\LoginController@logout',
        'dashboard' => 'AureaVertex\\Admin\\Controller\\Common\\DashboardController@index',
        'users' => 'AureaVertex\\Admin\\Controller\\User\\UserController@index',
        'resumes' => 'AureaVertex\\Admin\\Controller\\Resume\\ResumeController@index',
        'templates' => 'AureaVertex\\Admin\\Controller\\Template\\TemplateController@index',
        'ads' => 'AureaVertex\\Admin\\Controller\\Ad\\AdController@index',
        'settings' => 'AureaVertex\\Admin\\Controller\\Setting\\SettingController@index',
        'logs' => 'AureaVertex\\Admin\\Controller\\Log\\LogController@index',
        '404' => 'AureaVertex\\Admin\\Controller\\Common\\DashboardController@notFound',
    ],
];
