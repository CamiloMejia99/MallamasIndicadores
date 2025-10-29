<?php
include '../../bd/conexion.php';

// Recibir datos
$PROCESO            = $_POST['PROCESO'] ?? '';
$COORDINACION       = $_POST['COORDINACION'] ?? '';
$CODIGO             = $_POST['CODIGO'] ?? '';
$NOMBRE_INDICADOR   = $_POST['NOMBRE_INDICADOR'] ?? '';
$OBJETIVO           = $_POST['OBJETIVO'] ?? '';
$PERIODICIDAD       = $_POST['PERIODICIDAD'] ?? '';
$FUENTE_INFORMACION = $_POST['FUENTE_INFORMACION'] ?? '';
$META               = $_POST['META'] ?? '';
$MES                = $_POST['MES'] ?? '';
$NUM                = $_POST['NUM'] ?? '';
$DEM                = $_POST['DEM'] ?? '';
$RESULTADO          = $_POST['RESULTADO'] ?? '';

$ban = 0;

// Validaciones
if ($PROCESO == "" || $COORDINACION == "" || $CODIGO == "" || $NOMBRE_INDICADOR == "" || 
    $OBJETIVO == "" || $PERIODICIDAD == "" || $FUENTE_INFORMACION == "" || $META == "" || 
    $MES == "" || $NUM == "" || $DEM == "" || $RESULTADO == "") {
    $ban = 1;
} else {
    // Validar si ya existe el código
    $validar = "SELECT * FROM indicadores WHERE codigo = ?";
    $params  = [$CODIGO];
    $validando = sqlsrv_query($conn, $validar, $params);

    if ($validando && sqlsrv_has_rows($validando)) {
        $ban = 2;
    }
}

if ($ban == 0) {
    // Insertar en indicadores
    $sqlIndicadores = "INSERT INTO indicadores 
        (proceso, coordinacion, codigo, nombre_indicador, objetivo, periodicidad, fuente_informacion, meta) 
        VALUES (?,?,?,?,?,?,?,?)";

    $paramsIndicadores = [$PROCESO, $COORDINACION, $CODIGO, $NOMBRE_INDICADOR, $OBJETIVO, $PERIODICIDAD, $FUENTE_INFORMACION, $META];

    $stmtIndicadores = sqlsrv_query($conn, $sqlIndicadores, $paramsIndicadores);

    if ($stmtIndicadores) {
        // Obtener el último ID insertado
        $lastIdResult = sqlsrv_query($conn, "SELECT SCOPE_IDENTITY() AS id");
        $row = sqlsrv_fetch_array($lastIdResult, SQLSRV_FETCH_ASSOC);
        $idIndicador = $row['id'];

        // Insertar en indicadores_resultado
        $sqlResultado = "INSERT INTO indicadores_resultado (id_indicador, mes, num, dem, resultado) 
                         VALUES (?,?,?,?,?)";
        $paramsResultado = [$idIndicador, $MES, $NUM, $DEM, $RESULTADO];
        $stmtResultado = sqlsrv_query($conn, $sqlResultado, $paramsResultado);

        if ($stmtResultado) {
            echo "<h3 style='color:green'>✅ Registro exitoso</h3>";
        } else {
            echo "<h3 style='color:red'>❌ Error al insertar en indicadores_resultado</h3>";
            print_r(sqlsrv_errors());
        }
    } else {
        echo "<h3 style='color:red'>❌ Error al insertar en indicadores</h3>";
        print_r(sqlsrv_errors());
    }
} else {
    if ($ban == 1) {
        echo "<h3 style='color:red'>⚠️ Existen campos vacíos</h3>";
    }
    if ($ban == 2) {
        echo "<h3 style='color:red'>⚠️ Ya existe un indicador con código: $CODIGO</h3>";
    }
}

// Cerrar conexión
sqlsrv_close($conn);
?>
