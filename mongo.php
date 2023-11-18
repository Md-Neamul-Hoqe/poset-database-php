<?php 
// Mongo DB connection [Best for file processing]

// Define MongoDB connection string and options
$dsn = "mongodb://localhost:27017";
$options = [
    'username' => '',
    'password' => '',
    'ssl' => false,
    'authSource' => '',
    'authMechanism' => 'SCRAM-SHA-1',
];


// =======================================
// Establish MongoDB connection
$mongo = new MongoDB\Client("mongodb://localhost:27017");

// Select database and collection
$db_name = "Posets";
$collection_name = "allposets";
$collection = $mongo->$db_name->$collection_name;

// Create indexes
$collection->createIndex(["MatrixOrder" => 1]);
$collection->createIndex(["Height" => 1]);
$collection->createIndex(["Width" => 1]);

// Create collection with schema
$schema = [
    'validator' => [
        '$jsonSchema' => [
            'bsonType' => 'object',
            'required' => ['MatrixOrder', 'Matrix', 'Height', 'Width', 'Date'],
            'properties' => [
                'idx' => ['bsonType' => 'objectId'],
                'MatrixOrder' => ['bsonType' => 'int', 'minimum' => 0],
                'Matrix' => ['bsonType' => 'string'],
                'Height' => ['bsonType' => 'int', 'minimum' => 0],
                'Width' => ['bsonType' => 'int', 'minimum' => 0],
                'Date' => ['bsonType' => 'date']
            ]
        ]
    ]
];
$options = [
    'validationAction' => 'error', // Will throw an exception if validation fails
    'validationLevel' => 'strict' // Will enforce validation rules strictly
];
$collection->createCollection($schema, $options);



// "C:\Program Files\MongoDB\Server\<version>\bin\mongod.exe" --dbpath "C:\data\db"
