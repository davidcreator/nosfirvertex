<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'Instalador AureaVertex',
        'layout' => 'layout/install',
        'area' => 'install',
    ],
    'routes' => [
        '' => 'AureaVertex\\Install\\Controller\\InstallerController@index',
        'step/1' => 'AureaVertex\\Install\\Controller\\InstallerController@step1',
        'step/1/next' => 'AureaVertex\\Install\\Controller\\InstallerController@step1Next',
        'step/2' => 'AureaVertex\\Install\\Controller\\InstallerController@step2',
        'step/2/test-db' => 'AureaVertex\\Install\\Controller\\InstallerController@testDb',
        'step/2/next' => 'AureaVertex\\Install\\Controller\\InstallerController@step2Next',
        'step/3' => 'AureaVertex\\Install\\Controller\\InstallerController@step3',
        'run' => 'AureaVertex\\Install\\Controller\\InstallerController@run',
        'restart' => 'AureaVertex\\Install\\Controller\\InstallerController@restart',
        '404' => 'AureaVertex\\Install\\Controller\\InstallerController@notFound',
    ],
];
