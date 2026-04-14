<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'Instalador NosfirVertex',
        'layout' => 'layout/install',
        'area' => 'install',
    ],
    'routes' => [
        '' => 'NosfirVertex\\Install\\Controller\\InstallerController@index',
        'step/1' => 'NosfirVertex\\Install\\Controller\\InstallerController@step1',
        'step/1/next' => 'NosfirVertex\\Install\\Controller\\InstallerController@step1Next',
        'step/2' => 'NosfirVertex\\Install\\Controller\\InstallerController@step2',
        'step/2/test-db' => 'NosfirVertex\\Install\\Controller\\InstallerController@testDb',
        'step/2/next' => 'NosfirVertex\\Install\\Controller\\InstallerController@step2Next',
        'step/3' => 'NosfirVertex\\Install\\Controller\\InstallerController@step3',
        'run' => 'NosfirVertex\\Install\\Controller\\InstallerController@run',
        'restart' => 'NosfirVertex\\Install\\Controller\\InstallerController@restart',
        '404' => 'NosfirVertex\\Install\\Controller\\InstallerController@notFound',
    ],
];
