<?php

/* ---------- HMAC AUTH ----------
if (
    !array_key_exists( 'HTTP_X_HASH', $_SERVER ) ||
    !array_key_exists( 'HTTP_X_TIMESTAMP', $_SERVER) ||
    !array_key_exists( 'HTTP_X_UID', $_SERVER )
) {
    header( 'Status-Code: 403' );
    echo json_encode(
        [
            'error' => "No autorizado",
        ]
    );
    die;
}

list( $hash, $uid, $timestamp ) = [
    $_SERVER['HTTP_X_HASH'],
    $_SERVER['HTTP_X_UID'],
    $_SERVER['HTTP_X_TIMESTAMP'],
];
$secret = 'Sh!! No se lo cuentes a nadie!';
$newHash = sha1($uid.$timestamp.$secret);

if ( $newHash !== $hash ) {
    echo "\n";
	header( 'Status-Code: 403' );
    echo json_encode(
        [
            'error' => "No autorizado. Hash esperado: $newHash, hash recibido: $hash",
        ]
    );
    die;
}
// FIN AUTENTIFICACION */

// ----------AUTH WITH ACCESS TOKENS---------- //
if ( !array_key_exists( 'HTTP_X_TOKEN', $_SERVER ) ) {
    die;
}
$url = 'http://localhost:8001';
$ch = curl_init( $url );
curl_setopt(
    $ch,
    'CURLOP_HTTPHEADER',
    [
        "X-TOKEN: {$_SERVER['HTTP_X_TOKEN']}"
    ]
    );
curl_setopt(
    $ch,
    'CURLOP_RETURNTRANSFER',
    true
);
$ret = curl_exec( $ch );

if ( $ret !== 'true' ) {
    die;
}
// TIPO DE RECURSOS PERMITIDOS
$allowedResourceTypes = [
    'books',
    'authors',
    'genres',
];

// VALIDACION: TIPO DE RECURSO DISPONIBLE EN EL ARREGLO
$resourceType = $_GET['resource_type'];

if(!in_array($resourceType, $allowedResourceTypes)){
    header( 'Status-Code: 400' );
	echo json_encode(
		[
			'error' => "Resource type '$resourceType' is un unkown",
		]
	);
    die; // SI EL RECURSO NO ESTA PERMITIDO, ENTONCES MUERE
}

// DEFINICION DE RECURSOS
$books = [
    1 => [
        'titulo' => 'Lo que el viento se llevo',
        'id_autor' => 2,
        'id_genero' => 2,
    ],
    2 => [
        'titulo' => 'La Illiada',
        'id_autor' => 1,
        'id_genero' => 1,
    ],
    3 => [
        'titulo' => 'La Odisea',
        'id_autor' => 1,
        'id_genero' => 1,
    ],
];
// SE INDICA AL CLIENTE QUE RECIBIRA UN JSON
header('Content-Type: application/json');
// SACAMOS ID DEL RECURSO BUSCADO, VACIO EN CASO DE NO EXISTENCIA
$resourceId = array_key_exists('resource_id', $_GET) 
    ? $_GET['resource_id'] 
    : '';
// GENERAR RESPUESTA ASUMIENDO UN PEDIDO CORRECTO
switch( strtoupper( $_SERVER['REQUEST_METHOD'] )) {
    case 'GET':
        if ( empty( $resourceId ) ) {
            echo json_encode($books);
        } else {
            if ( array_key_exists( $resourceId, $books ) ) {
                echo json_encode( $books[ $resourceId ] );
            }
        }
        break;

    case "POST":
        $json = file_get_contents('php://input');
        $books[] = json_decode($json, true);
        // BUENA PRACTICA
        // OBTENER LA LLAVE DEL ULTIMO RECURSO CREADO
        //echo array_keys( $books )[ count($books) - 1];
        // IMPRIMIR TODO EL ARREGLO DE LIBROS
        echo json_encode( $books );   
        break;

    case "PUT":
        // VALIDAR LA EXISTENCIA DEL RECURSO BUSCADO
        if ( !empty($resourceId) && array_key_exists( $resourceId, $books ) ) {
            // TOMAR LA ENTRADA CRUDA
            $json = file_get_contents('php://input');
            // LA CLAVE RECIBIDA, ES EL INDICE PARA CAMBIAR DATOS
            $books[ $resourceId ] = json_decode( $json, true );
            // RETORNAR LA COLECCION MODIFICADA EN FORMATO JSON
            echo json_encode( $books );
        }
        break;

    case "DELETE":
        // VALIDAMOS QUE EL RECURSO EXISTA
        if ( !empty($resourceId) && array_key_exists( $resourceId, $books ) ) {
            // QUITARLO DEL ARREGLO
            unset( $books[ $resourceId ] );
            echo json_encode( $books );
        }
        break;
}