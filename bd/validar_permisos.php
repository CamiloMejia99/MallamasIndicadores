<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

$cedula = $_SESSION['usuario']['Cedula'] ?? null;

// Valores por defecto (usuario común)
$_SESSION['es_super'] = false;
$_SESSION['permisos_su'] = [];

if (!$cedula) {
    return;
}

/* ======================================================
    Validar Super Usuario PERMANENTE
====================================================== */
$sqlSU = "
    SELECT super_usuario
    FROM persona
    WHERE Cedula = ?
";

$stmt = sqlsrv_query($conexion, $sqlSU, [$cedula]);
$row  = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if ($row && $row['super_usuario'] == 1) {
    $_SESSION['es_super'] = true;
    $_SESSION['permisos_su'] = [
        'p_registrar_indicador' => 1,
        'p_registrar_resultado' => 1,
        'p_editar_indicador'    => 1,
        'p_eliminar_indicador'  => 1
    ];

    return;
}

/* ======================================================
    Validar Super Usuario TEMPORAL
====================================================== */
$sqlTemp = "
    SELECT 
        p_registrar_indicador,
        p_registrar_resultado,
        p_editar_indicador,
        p_eliminar_indicador
    FROM super_usuario_temporal
    WHERE Cedula = ?
      AND GETDATE() BETWEEN fecha_inicio AND fecha_fin
";

$stmtTemp = sqlsrv_query($conexion, $sqlTemp, [$cedula]);
$temp = sqlsrv_fetch_array($stmtTemp, SQLSRV_FETCH_ASSOC);

if ($temp) {
    $_SESSION['es_super'] = true;
    $_SESSION['permisos_su'] = $temp;
}
