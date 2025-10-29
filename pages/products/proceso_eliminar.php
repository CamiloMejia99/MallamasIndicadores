<?php
include '../../bd/conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID inválido');window.location.href='list.php';</script>";
    exit;
}

$id = intval($_GET['id']);

// Primero eliminar registros hijos en indicadores_resultado (si existen)
// por la restricción de clave foránea
sqlsrv_begin_transaction($conexion);

try {
    $sqlDeleteResultados = "DELETE FROM dbo.indicadores_resultado WHERE id_idicador = ?";
    $stmt1 = sqlsrv_query($conexion, $sqlDeleteResultados, [$id]);
    if ($stmt1 === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    $sqlDeleteIndicador = "DELETE FROM dbo.indicadores WHERE id_indicador = ?";
    $stmt2 = sqlsrv_query($conexion, $sqlDeleteIndicador, [$id]);
    if ($stmt2 === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_commit($conexion);

    echo "<script>alert('Registro eliminado correctamente');window.location.href='list.php';</script>";
    exit;

} catch (Exception $e) {
    sqlsrv_rollback($conexion);
    error_log("Error al eliminar: " . $e->getMessage());
    echo "<script>alert('No se pudo eliminar el registro.');window.location.href='list.php';</script>";
    exit;
}
?>
