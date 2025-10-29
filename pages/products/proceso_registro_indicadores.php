    <?php
    include '../../bd/conexion.php'; // conexión a la BD

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idProceso          = $_POST['idProceso'] ?? '';
        $idCoordinacion     = $_POST['idCoordinacion'] ?? '';
        $codigo             = $_POST['codigo'] ?? '';
        $nombre_indicador   = $_POST['nombre_indicador'] ?? '';
        $objetivo           = $_POST['objetivo'] ?? '';
        $periodicidad       = $_POST['periodicidad'] ?? '';
        $fuente_informacion = $_POST['fuente_informacion'] ?? '';
        $meta               = $_POST['meta'] ?? '';

        // Validación
        if (empty($idProceso) || empty($idCoordinacion) || empty($codigo) || empty($nombre_indicador)) {
            echo "<script>alert('⚠️ Debes llenar todos los campos obligatorios');window.history.back();</script>";
            exit;
        }

        sqlsrv_begin_transaction($conexion);

        try {
            $sqlIndicadores = "INSERT INTO dbo.indicadores 
                (idProceso, idCoordinacion, codigo, nombre_indicador, objetivo, periodicidad, fuente_informacion, meta) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $paramsIndicadores = [
                $idProceso,
                $idCoordinacion,
                $codigo,
                $nombre_indicador,
                $objetivo,
                $periodicidad,
                $fuente_informacion,
                $meta
            ];

            $stmtIndicadores = sqlsrv_query($conexion, $sqlIndicadores, $paramsIndicadores);

            if ($stmtIndicadores === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            sqlsrv_commit($conexion);
            echo "<script>alert('✅ Indicador registrado correctamente');window.location.href='list.php';</script>";

        } catch (Exception $e) {
            sqlsrv_rollback($conexion);
            error_log($e->getMessage());
            echo "<script>alert('❌ Error al registrar el indicador');window.history.back();</script>";
        }

        } else {
        header("Location: register_indicadores.php");
        exit;
    }
