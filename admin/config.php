<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'NosfirVertex Admin',
        'layout' => 'layout/admin',
        'area' => 'admin',
    ],
    'routes' => [
        '' => 'NosfirVertex\\Admin\\Controller\\Common\\DashboardController@index',
        'login' => 'NosfirVertex\\Admin\\Controller\\Common\\LoginController@index',
        'logout' => 'NosfirVertex\\Admin\\Controller\\Common\\LoginController@logout',
        'dashboard' => 'NosfirVertex\\Admin\\Controller\\Common\\DashboardController@index',
        'users' => 'NosfirVertex\\Admin\\Controller\\User\\UserController@index',
        'resumes' => 'NosfirVertex\\Admin\\Controller\\Resume\\ResumeController@index',
        'templates' => 'NosfirVertex\\Admin\\Controller\\Template\\TemplateController@index',
        'ads' => 'NosfirVertex\\Admin\\Controller\\Ad\\AdController@index',
        'settings' => 'NosfirVertex\\Admin\\Controller\\Setting\\SettingController@index',
        'logs' => 'NosfirVertex\\Admin\\Controller\\Log\\LogController@index',
        '404' => 'NosfirVertex\\Admin\\Controller\\Common\\DashboardController@notFound',
    ],
];
