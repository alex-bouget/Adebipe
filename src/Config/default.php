<?php

/**
 * Default configuration
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */

return [
    "env_var" => [
        "DB_CONNECTION" => "mysql://webapp:root@database:3306/webapp",
    ],
    "config" => [
        "DIR" => "src/",
        "CORE" => [ // The core configuration
            "LOGGER" => [
                "LOG_IN_FILE" => true,
                "LOG_LEVEL" => 1,
                //"ERROR_CLASS" => "Adebipe\Services\DiscordErrorSender",
            ],
            "ORM" => [
                "TABLE_MODELS" => "App\Model\TableModel",
            ],
            "RENDERER" => [
                "VIEW_DIR" => "Views/",
            ]
        ],
        "ERROR" => [ // The error configuration
            // TODO
        ],
        "APP" => [ // Settings of your app
            "SENTRY" => [
                "DISCORD" => [
                    "USERNAME" => "Adebipe",
                ]
            ],
            "CORS" => [
                "*"
            ]
        ]
    ],
];
