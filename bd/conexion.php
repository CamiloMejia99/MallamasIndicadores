<?php


$serverName = "172.17.1.120";
$connectionOptions = [
    "Database" => "DB_GESTION_INDICADORES",  
    "UID" => "Estadistica",            
    "PWD" => 'E$tadi$tica*2025',     
    "TrustServerCertificate" => true
];

$conexion = sqlsrv_connect($serverName, $connectionOptions);

if ($conexion === false) {
    error_log(print_r(sqlsrv_errors(), true));
    die(" Error en la conexión a la base de datos.");
} 



// $serverName = "ESTADISTICA01"; 
// $connectionOptions = [
//     "Database" => "DB_GESTION_INDICADORES",
//     "TrustServerCertificate" => true
// ];

// $conexion = sqlsrv_connect($serverName, $connectionOptions);

// if ($conexion === false) {
//     error_log(print_r(sqlsrv_errors(), true));
//     die("Error en la conexión a la base de datos.");
// }



// $serverName = "CAMILO\\SQLEXPRESS"; 
// $connectionOptions = [
//     "Database" => "DB_GESTION_INDICADORES",
//     "TrustServerCertificate" => true
// ];

// $conexion = sqlsrv_connect($serverName, $connectionOptions);

// if ($conexion === false) {

//     error_log(print_r(sqlsrv_errors(), true));
//     die("Error en la conexión a la base de datos.");
// }


?>