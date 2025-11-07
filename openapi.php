<?php

// to be executed as a tinker script

$apiRoutes = collect(Route::getRoutes()->getIterator())
    ->filter(fn ($route) => Str::contains($route->uri, 'api/v'))
    ->map(fn ($route) => Str::replaceFirst('/v{ver}/', '/v0/', $route->uri))
    ->unique()
    ->sort(SORT_NATURAL | SORT_FLAG_CASE);

$base = 'https://localhost:8080';
$user = 'root@localhost.com';
$pass = 'secret';
$opts = [
    'http' => [
        'method' => 'GET',
        'header' => 'Authorization: Basic '.base64_encode($user.':'.$pass),
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
];

$types = [
    'bigint' => 'integer',
    'integer' => 'integer',
    'string' => 'string',
    'boolean' => 'boolean',
    'date' => 'string',
    'text' => 'string',
    'decimal' => 'number',
    'float' => 'number',
    'smallint' => 'integer',
    'bool' => 'boolean',
    'float8' => 'number',
    'int2' => 'integer',
    'int4' => 'integer',
    'int8' => 'integer',
    'numeric' => 'number',
    'timestamptz' => 'string',
    'varchar' => 'string',
];

// integer: int32, int64
// number: float, double
// string: date, date-time, password, byte, binary
$formats = [
    'bigint' => 'int64',
    'date' => 'date',
    'decimal' => 'float',
    'float' => 'float',
    'float8' => 'float',
    'int4' => 'int32',
    'int8' => 'int64',
];

$colRename = [
    'User' => 'users',
    'Role' => 'roles',
];

$ignore = [
    'configfile.firmware_upload',
    'contract.related_phonenrs',
    'ticket.tickettypes_ids[]',
    'ticket.users_ids[]',
    'ticketsystem.noreply_mail',
    'ticketsystem.noreply_name',
    'ticketsystem.open_tickets',
    'netelement.infrastructure_file_upload',
    'netelement.enable_agc',
    'mibfile.mibfile_upload',
    'parameter.name',
    'sepaaccount.template_invoice_upload',
    'sepaaccount.template_cdr_upload',
    'company.logo_upload',
    'company.conn_info_template_fn_upload',
    'settlementrun.voucher_nr',
    'settlementrun.banking_file_upload',
    'ccc.image_upload',
    'global_config.password_reset_interval',
    'global_config.login_img_upload',
    'global_config.is_all_nets_sidebar_enabled',
    'users.password_confirmation',
    'users.roles_ids[]',
    'users.users_ids[]',
    'roles.users_ids[]',
    'debtimport.file_upload',
    'debtimport.voucher_nr',
    'enviaorder.phonenumber_id',
    'enviaorderdocument.document_upload',
    'firmware_upgrade.fromconfigfile_ids[]',
];

$ret = [
    'openapi' => '3.0.3',
    'info' => [
        'title' => 'NMS Prime API',
        'description' => 'Description of the NMS Prime API',
        'contact' => [
            'email' => 'ole.ernst@nmsprime.com',
        ],
        'license' => [
            'name' => 'Apache 2.0',
            'url' => 'http://www.apache.org/licenses/LICENSE-2.0.html',
        ],
        'version' => '0.0.1',
    ],
    'components' => [
        'schemas' => [
            'DeleteResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                    ],
                ],
            ],
            'GenericResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => [
                        'type' => 'boolean',
                    ],
                    'id' => [
                        'type' => 'integer',
                        'format' => 'int64',
                    ],
                ],
            ],
        ],
    ],
];

foreach ($apiRoutes as $route) {
    if (! Str::contains($route, '/create')) {
        continue;
    }

    $response = json_decode(file_get_contents("$base/$route", false, stream_context_create($opts)), true);
    if (! $response) {
        continue;
    }

    $fields = $response['models'];
    $route = dirname($route);
    $entity = basename($route);

    $ret['components']['schemas']["{$entity}Response"]['type'] = 'object';
    $ret['components']['schemas']["{$entity}Response"]['properties']['success']['type'] = 'boolean';
    $ret['components']['schemas']["{$entity}Response"]['properties']['models']['type'] = 'object';
    $ret['components']['schemas']["{$entity}Response"]['properties']['models']['additionalProperties']['$ref'] = "#/components/schemas/$entity";

    $ret['components']['schemas']["{$entity}SingleResponse"]['type'] = 'object';
    $ret['components']['schemas']["{$entity}SingleResponse"]['properties']['id']['type'] = 'integer';
    $ret['components']['schemas']["{$entity}SingleResponse"]['properties']['id']['format'] = 'int64';
    $ret['components']['schemas']["{$entity}SingleResponse"]['properties']['success']['type'] = 'boolean';
    $ret['components']['schemas']["{$entity}SingleResponse"]['properties']['models']['type'] = 'object';
    $ret['components']['schemas']["{$entity}SingleResponse"]['properties']['models']['additionalProperties']['$ref'] = "#/components/schemas/$entity";
    $ret['components']['schemas']["{$entity}SingleResponse"]['properties']['models']['maxProperties'] = 1;

    $required = [];
    $ret['components']['schemas'][$entity]['type'] = 'object';
    foreach ($fields as $column => $values) {
        $table = array_key_exists($entity, $colRename) ? $colRename[$entity] : strtolower($entity);
        if (in_array("$table.$column", $ignore)) {
            continue;
        }

        // convert CamelCase to underscore_case and try again
        try {
            $columnType = Schema::getColumnType($table, $column);
        } catch(InvalidArgumentException $e) {
            $table = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $entity));
        }
        if (in_array("$table.$column", $ignore)) {
            continue;
        }

        try {
            $columnType = Schema::getColumnType($table, $column);
        } catch(Doctrine\DBAL\Exception $e) {
            echo "can't retrieve column type for $entity, $column: ".$e->getMessage()."\n";
            $columnType = null;
        }

        if ($type = $types[$columnType] ?? null) {
            $ret['components']['schemas'][$entity]['properties'][$column]['type'] = $type;
        }

        if ($format = $formats[$columnType] ?? null) {
            $ret['components']['schemas'][$entity]['properties'][$column]['format'] = $format;
        }

        if ($description = $values['description'] ?? null) {
            $ret['components']['schemas'][$entity]['properties'][$column]['description'] = $description;
            if (Str::endsWith($description, '*')) {
                $required[] = $column;
            }
        }

        $ret['paths']["/$route"]['get'] = [
            'summary' => "Get all existing {$entity}s",
            'description' => "Get all existing {$entity}s",
            'operationId' => "get{$entity}s",
            'responses' => [
                '200' => [
                    'description' => 'Successful operation',
                    'content' => ['application/json' => ['schema' => ['$ref' => "#/components/schemas/{$entity}Response"]]],
                ],
                '400' => [
                    'description' => "$entity not found",
                ],
            ],
        ];

        $ret['paths']["/$route/{{$entity}Id}"]['get'] = [
            'summary' => "Get existing $entity",
            'description' => "Get existing $entity by Id",
            'operationId' => "get{$entity}ById",
            'parameters' => [
                [
                    'name' => "{$entity}Id",
                    'in' => 'path',
                    'description' => "ID of $entity to return",
                    'required' => true,
                    'schema' => ['type' => 'integer', 'format' => 'int64'],
                ],
            ],
            'responses' => [
                '200' => [
                    'description' => 'Successful operation',
                    'content' => ['application/json' => ['schema' => ['$ref' => "#/components/schemas/{$entity}SingleResponse"]]],
                ],
                '400' => [
                    'description' => "$entity not found",
                ],
            ],
        ];

        $ret['paths']["/$route"]['post'] = [
            'summary' => "Create $entity",
            'description' => "Create $entity",
            'operationId' => "create{$entity}",
            'requestBody' => [
                'description' => "Create $entity",
                'content' => ['application/json' => ['schema' => ['$ref' => "#/components/schemas/$entity"]]],
                'required' => true,
            ],
            'responses' => [
                '200' => [
                    'description' => 'Successful operation',
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/GenericResponse']]],
                ],
                '400' => [
                    'description' => "$entity not found",
                ],
            ],
        ];

        $ret['paths']["/$route/{{$entity}Id}"]['patch'] = [
            'summary' => "Update existing $entity",
            'description' => "Update existing $entity by Id",
            'operationId' => "update{$entity}ById",
            'parameters' => [
                [
                    'name' => "{$entity}Id",
                    'in' => 'path',
                    'description' => "ID of $entity to return",
                    'required' => true,
                    'schema' => ['type' => 'integer', 'format' => 'int64'],
                ],
            ],
            'requestBody' => [
                'description' => "Update existing $entity by Id",
                'content' => ['application/json' => ['schema' => ['$ref' => "#/components/schemas/$entity"]]],
                'required' => true,
            ],
            'responses' => [
                '200' => [
                    'description' => 'Successful operation',
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/GenericResponse']]],
                ],
                '400' => [
                    'description' => "$entity not found",
                ],
            ],
        ];

        $ret['paths']["/$route/{{$entity}Id}"]['delete'] = [
            'summary' => "Delete existing $entity",
            'description' => "Delete existing $entity by Id",
            'operationId' => "delete{$entity}ById",
            'parameters' => [
                [
                    'name' => "{$entity}Id",
                    'in' => 'path',
                    'description' => "ID of $entity to delete",
                    'required' => true,
                    'schema' => ['type' => 'integer', 'format' => 'int64'],
                ],
            ],
            'responses' => [
                '200' => [
                    'description' => 'Successful operation',
                    'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/DeleteResponse']]],
                ],
                '400' => [
                    'description' => "$entity not found",
                ],
            ],
        ];
    }

    if ($required) {
        $ret['components']['schemas'][$entity]['required'] = $required;
    }
}

yaml_emit_file('openapi.yaml', $ret);
exit;
