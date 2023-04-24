<?php

const DS = DIRECTORY_SEPARATOR;

return [
    'path' => [
        'users' => public_path(implode(DS, ['uploads', 'images', 'users'])),
    ],
];
