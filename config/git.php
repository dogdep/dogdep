<?php return [
    'path' => storage_path('repos'),

    'private_key' => storage_path('keys/dogdep'),
    'public_key' => storage_path('keys/dogdep.pub'),

    'options' => [
        'environment_variables' => [
            'GIT_SSH' => base_path("scripts/git_ssh.sh"),
            'GIT_SSH_KEY' => storage_path("keys/dogdep"),
        ],
    ]
];
