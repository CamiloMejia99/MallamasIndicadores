<?php
include '../../bd/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // =======================
    // 1. EDITAR INDICADORES
    // =======================
    $id_indicador = $_POST['id_indicador'];
    $idCoordinacion = $_POST['idCoordinacion'];
    $codigo = $_POST['codigo'];
    $nombre_indicador = $_POST['nombre_indicador'];
    $objetivo = $_POST['objetivo'];
    $periodicidad = $_POST['periodicidad'];
    $fuente_informacion = $_POST['fuente_informacion'];
    $meta = $_POST['meta'];

    $sqlIndicador = "UPDATE dbo.indicadores 
                     SET  idCoordinacion=?, codigo=?, nombre_indicador=?, objetivo=?, periodicidad=?, fuente_informacion=?, meta=?
                     WHERE id_indicador=?";
    $paramsIndicador = [$idCoordinacion, $codigo, $nombre_indicador, $objetivo, $periodicidad, $fuente_informacion, $meta, $id_indicador];
    $stmtIndicador = sqlsrv_query($conexion, $sqlIndicador, $paramsIndicador);

    if ($stmtIndicador === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // ==============================
    // 2. EDITAR INDICADORES_RESULTADO
    // ==============================
    if (!empty($_POST['id_result'])) {
        foreach ($_POST['id_result'] as $index => $id_result) {
            $mes = $_POST['mes'][$index];
            $num = $_POST['num'][$index];
            $dem = $_POST['dem'][$index];
            $resultado = $_POST['resultado'][$index];

            $sqlResultado = "UPDATE dbo.indicadores_resultado
                             SET mes=?, num=?, dem=?, resultado=?
                             WHERE id_result=? AND id_idicador=?";
            $paramsResultado = [$mes, $num, $dem, $resultado, $id_result, $id_indicador];
            $stmtResultado = sqlsrv_query($conexion, $sqlResultado, $paramsResultado);

            if ($stmtResultado === false) {
                die(print_r(sqlsrv_errors(), true));
            }
        }
    }

    echo "<script>alert('Registro actualizado correctamente');window.location.href='list.php';</script>";
}
?>
