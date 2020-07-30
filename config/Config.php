<?php

use Controller\FrontController;

return [
    "displayError" => boolval(getenv('DISPLAY_ERROR')),
    "app" => [
        "defaultController" => "front",
        "defaultMethod" => "index",
    ],
    "site" => [
        "name" => getenv('SITE_NAME'),
        "subname" => getenv('SITE_SUBNAME'),
        "title" => getenv('SITE_TITLE'),
        "footer" => getenv('SITE_FOOOTER'),
    ],
    "dir" => [
        "site_subdir" => "",
        "upload" => __DIR__ . "/files",
    ],
    "database" => [
        "driver" => getenv('DB_DRIVER'),
        "host" => getenv('DB_HOST'),
        "database" => getenv('DB_NAME'),
        "username" => getenv('DB_USERNAME'),
        "password" => getenv('DB_PASSWORD'),
        "port" => getenv('DB_PORT'),
    ],
    "template" => [
        "view" => __DIR__ . "/../views",
        "cache" => __DIR__ . "/../cache",
    ],
];
