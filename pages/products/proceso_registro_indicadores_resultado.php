<?php
session_start();
include '../../bd/conexion.php';

// Validar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}

// Asegurar que venga por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register_indicadores_resultado.php');
    exit;
}

// Recibir y sanear datos
$id_indicador = isset($_POST['id_indicador']) ? intval($_POST['id_indicador']) : 0;
$mes          = trim($_POST['mes'] ?? '');
$num_raw      = trim($_POST['num'] ?? '');
$dem_raw      = trim($_POST['dem'] ?? '');
$resultado    = (strval($num_raw  /$dem_raw) . "%");
$analisis     = trim($_POST['analisis'] ?? '');

// Validaciones básicas
if ($id_indicador <= 0) {
    echo "<script>alert('Seleccione un indicador válido.');window.history.back();</script>";
    exit;
}
if ($mes === '') {
    echo "<script>alert('Ingrese el mes.');window.history.back();</script>";
    exit;
}
// VALIDACIÓN CLAVE: mínimo 200 caracteres
if (strlen($analisis) < 200) {
    echo "<script>
        alert('El análisis debe contener mínimo 200 caracteres.');
        window.history.back();
    </script>";
    exit;
}


// Convertir num y dem a númericos si es posible, sino NULL
$num = is_numeric($num_raw) ? (float)$num_raw : null;
$dem = is_numeric($dem_raw) ? (float)$dem_raw : null;

// Verificar que el indicador existe (evita errores FK y da mensaje más claro)
$sqlCheck = "SELECT 1 FROM dbo.indicadores WHERE id_indicador = ?";
$paramsCheck = [$id_indicador];
$stmtCheck = sqlsrv_query($conexion, $sqlCheck, $paramsCheck);

if ($stmtCheck === false) {
    error_log(print_r(sqlsrv_errors(), true));
    echo "<script>alert('Error al verificar el indicador.');window.history.back();</script>";
    exit;
}

$exists = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmtCheck);

if (!$exists) {
    echo "<script>alert('El indicador seleccionado no existe.');window.history.back();</script>";
    exit;
}

// Insertar en indicadores_resultado (columna FK es id_idicador)
sqlsrv_begin_transaction($conexion);

try {
    $sqlInsert = "INSERT INTO dbo.indicadores_resultado (id_idicador, mes, num, dem, resultado, analisis)
                  VALUES (?, ?, ?, ?, ?, ?)";
    $paramsInsert = [$id_indicador, $mes, $num, $dem, $resultado, $analisis];

    $stmtInsert = sqlsrv_query($conexion, $sqlInsert, $paramsInsert);

    if ($stmtInsert === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_commit($conexion);
    sqlsrv_free_stmt($stmtInsert);
    sqlsrv_close($conexion);

    echo "<script>alert('✅ Registro agregado correctamente.');window.location.href='list.php';</script>";
    exit;

} catch (Exception $e) {
    sqlsrv_rollback($conexion);
    error_log("❌ Error insertando indicadores_resultado: " . $e->getMessage());
    // Mensaje amigable al usuario
    echo "<script>alert('Ocurrió un error al guardar. Revise los datos e intente nuevamente.');window.history.back();</script>";
    exit;
}
?>
