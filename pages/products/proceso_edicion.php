<?php
include '../../bd/conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // =======================
    // VALIDACIÓN INICIAL
    // =======================
    if (empty($_POST['id_indicador'])) {
        echo "<script>alert('ID del indicador no recibido');window.history.back();</script>";
        exit;
    }

    $id_indicador       = $_POST['id_indicador'];
    $idProceso          = $_POST['idProceso'] ?? null;
    $idCoordinacion     = $_POST['idCoordinacion'] ?? null;
    $codigo             = $_POST['codigo'] ?? '';
    $nombre_indicador   = $_POST['nombre_indicador'] ?? '';
    $objetivo           = $_POST['objetivo'] ?? '';
    $periodicidad       = $_POST['periodicidad'] ?? '';
    $fuente_informacion = $_POST['fuente_informacion'] ?? '';
    $meta               = $_POST['meta'] ?? '';

    // =======================
    // INICIAR TRANSACCIÓN
    // =======================
    sqlsrv_begin_transaction($conexion);

    try {
        // =======================
        // ACTUALIZAR INDICADOR
        // =======================
        $sqlIndicador = "UPDATE dbo.indicadores 
                         SET idProceso = ?, 
                             idCoordinacion = ?, 
                             codigo = ?, 
                             nombre_indicador = ?, 
                             objetivo = ?, 
                             periodicidad = ?, 
                             fuente_informacion = ?, 
                             meta = ?
                         WHERE id_indicador = ?";
        $paramsIndicador = [
            $idProceso, 
            $idCoordinacion, 
            $codigo, 
            $nombre_indicador, 
            $objetivo, 
            $periodicidad, 
            $fuente_informacion, 
            $meta, 
            $id_indicador
        ];

        $stmtIndicador = sqlsrv_query($conexion, $sqlIndicador, $paramsIndicador);
        if ($stmtIndicador === false) {
            throw new Exception("Error al actualizar indicador: " . print_r(sqlsrv_errors(), true));
        }

        // =======================
        // ACTUALIZAR RESULTADOS
        // =======================
        if (!empty($_POST['id_result'])) {
            foreach ($_POST['id_result'] as $index => $id_result) {
                $mes        = $_POST['mes'][$index] ?? '';
                $num        = $_POST['num'][$index] ?? 0;
                $dem        = $_POST['dem'][$index] ?? 0;
                $resultado  = $_POST['resultado'][$index] ?? '';
                $analisis = $_POST['analisis'][$index] ?? '';

                $sqlResultado = "UPDATE dbo.indicadores_resultado
                                 SET mes = ?, 
                                     num = ?, 
                                     dem = ?, 
                                     resultado = ?,
                                     analisis = ?
                                 WHERE id_result = ? AND id_idicador = ?";
                $paramsResultado = [
                    $mes, 
                    $num, 
                    $dem, 
                    $resultado, 
                    $analisis,
                    $id_result, 
                    $id_indicador
                    
                ];

                $stmtResultado = sqlsrv_query($conexion, $sqlResultado, $paramsResultado);
                if ($stmtResultado === false) {
                    throw new Exception("Error al actualizar resultados: " . print_r(sqlsrv_errors(), true));
                }
            }
        }

        // =======================
        // CONFIRMAR CAMBIOS
        // =======================
        sqlsrv_commit($conexion);
        echo "<script>alert('✅ Registro actualizado correctamente');window.location.href='list.php';</script>";

    } catch (Exception $e) {
        // =======================
        // 🚫 ERROR Y ROLLBACK
        // =======================
        sqlsrv_rollback($conexion);
        error_log($e->getMessage());
        echo "<script>alert('❌ Error al actualizar el registro. Revise los datos.');window.history.back();</script>";
    }
}
?>
