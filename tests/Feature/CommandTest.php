<?php

test('example', function () {

    $fieldTypes = [
        'bigIncrements', 'bigInteger', 'binary', 'boolean', 'char', 'date', 'dateTime',
        'dateTimeTz', 'decimal', 'double', 'enum', 'float', 'geometry', 'geometryCollection',
        'increments', 'integer', 'ipAddress', 'json', 'jsonb', 'lineString', 'longText',
        'macAddress', 'mediumIncrements', 'mediumInteger', 'mediumText', 'morphs',
        'multiLineString', 'multiPoint', 'multiPolygon', 'nullableMorphs', 'nullableTimestamps',
        'nullableUuidMorphs', 'point', 'polygon', 'rememberToken', 'set', 'smallIncrements',
        'smallInteger', 'softDeletes', 'softDeletesTz', 'string', 'text', 'time', 'timeTz',
        'timestamp', 'timestampTz', 'timestamps', 'tinyIncrements', 'tinyInteger', 'tinyText',
        'unsignedBigInteger', 'unsignedDecimal', 'unsignedInteger', 'unsignedMediumInteger',
        'unsignedSmallInteger', 'unsignedTinyInteger', 'uuid', 'uuidMorphs', 'year',
    ];
    

    $this->artisan('kjos:make:api client')
    ->expectsQuestion('Do you want to create database fields?', 'yes')
    ->expectsChoice(
        'use arrow to select your database field type. Ex: string', // The question being asked
        'string', // The answer you want to simulate (the user's selection)
        $fieldTypes
    )
    ->expectsQuestion('Enter the field length. Ex: 255', 20)
    ->expectsQuestion('Enter your database field name. Ex: name', 'name')
    ->expectsQuestion('Field is nullable?', 'yes')
    ->expectsQuestion('Field is unique?', 'no')
    ->expectsQuestion('Field is can be indexed?', 'no')
    ->expectsQuestion('Does the field have a default value?', 'yes')
    ->expectsQuestion('Enter the default value:', 'Koffi')
    ->expectsQuestion('Should the table have timestamps (created_at and updated_at)?', 'yes')
    ->expectsQuestion('Would you like to add a comment to the field?', 'yes')
    ->expectsQuestion('Enter the field comment:', 'test tes test ')
    ->expectsQuestion('Is the field a foreign key?', 'yes')
    ->expectsQuestion('Enter the related table name:', 'address')
    ->expectsQuestion("Enter the related table field name, typically 'id':", 'address_id')    
    ->expectsQuestion('Do you want to create database fields?', 'yes')

    // ->expectsQuestion('Do you want to create database fields?', 'yes')
    ->expectsChoice(
        'use arrow to select your database field type. Ex: string', // The question being asked
        'integer', // The answer you want to simulate (the user's selection)
        $fieldTypes
    )
    ->expectsQuestion('Enter your database field name. Ex: name', 'price')
    ->expectsQuestion('Field is nullable?', 'no')
    ->expectsQuestion('Field is unique?', 'yes')
    ->expectsQuestion('Field is can be indexed?', 'yes')
    ->expectsQuestion('Does the field have a default value?', 'no')
    ->expectsQuestion('Should the table have timestamps (created_at and updated_at)?', 'yes')
    ->expectsQuestion('Would you like to add a comment to the field?', 'no')
    ->expectsQuestion('Is the field a foreign key?', 'no')
    ->expectsQuestion('Do you want to create database fields?', 'yes')

    ->expectsChoice(
        'use arrow to select your database field type. Ex: string', // The question being asked
        'string', // The answer you want to simulate (the user's selection)
        $fieldTypes
    )
    ->expectsQuestion('Enter the field length. Ex: 255', 20)
    ->expectsQuestion('Enter your database field name. Ex: name', 'partner_id')
    ->expectsQuestion('Field is nullable?', 'yes')
    ->expectsQuestion('Field is unique?', 'no')
    ->expectsQuestion('Field is can be indexed?', 'no')
    ->expectsQuestion('Does the field have a default value?', 'no')
    ->expectsQuestion('Should the table have timestamps (created_at and updated_at)?', 'yes')
    ->expectsQuestion('Would you like to add a comment to the field?', 'yes')
    ->expectsQuestion('Enter the field comment:', 'test tes test ')
    ->expectsQuestion('Is the field a foreign key?', 'yes')
    ->expectsQuestion('Enter the related table name:', 'partner')
    ->expectsQuestion("Enter the related table field name, typically 'id':", 'id')    
    ->expectsQuestion('Do you want to create database fields?', 'no')

    // ->expectsQuestion('Do you want to create database fields?', 'yes')
    ;
});
