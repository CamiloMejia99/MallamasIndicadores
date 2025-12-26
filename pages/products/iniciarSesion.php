<?php
session_start();
include '../../bd/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $codigo = trim($_POST['codigo']);
    $contrasena = trim($_POST['contraseña']);

    // Buscar usuario y su estado de super_usuario
    $sql = "SELECT Cedula, Nombres, Apellidos, codigoUsuario, Password, super_usuario, Cargo 
            FROM persona 
            WHERE codigoUsuario = ?";
    $params = array($codigo);
    $stmt = sqlsrv_query($conexion, $sql, $params);

    if ($stmt === false) {
        die("Error en la consulta de usuario: " . print_r(sqlsrv_errors(), true));
    }

    $usuario = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($usuario) {
        //  Verificar contraseña encriptada
        if (password_verify($contrasena, $usuario['Password'])) {

            // Buscar los procesos asociados al usuario
            $sqlProcesos = "SELECT p.idProceso, pr.Proceso
                            FROM persona_proceso p
                            INNER JOIN Proceso pr ON p.idProceso = pr.idProceso
                            WHERE p.Cedula = ?";
            $stmtProcesos = sqlsrv_query($conexion, $sqlProcesos, array($usuario['Cedula']));

            if ($stmtProcesos === false) {
                die("Error consultando procesos: " . print_r(sqlsrv_errors(), true));
            }

            $procesos = [];
            while ($row = sqlsrv_fetch_array($stmtProcesos, SQLSRV_FETCH_ASSOC)) {
                $procesos[] = $row['idProceso'];
            }

            if (empty($procesos)) {
                echo "<script>alert('No tiene procesos asignados. Contacte al administrador.'); window.location='../../index.php';</script>";
                exit;
            }

            // Buscar las coordianciones asociadas al usuario
            $sqlCoordinaciones = "SELECT c.idCoordinacion, co.Coordinacion
                            FROM persona_coordinacion as c
                            INNER JOIN Coordinacion as co ON c.idCoordinacion = co.idCoordinacion
                            WHERE c.Cedula = ?";
            $stmtCoordinaciones = sqlsrv_query($conexion, $sqlCoordinaciones, array($usuario['Cedula']));

            if ($stmtCoordinaciones === false) {
                die("Error consultando coordinaciones: " . print_r(sqlsrv_errors(), true));
            }

            $coordinaciones = [];
            while ($row = sqlsrv_fetch_array($stmtCoordinaciones, SQLSRV_FETCH_ASSOC)) {
                $coordinaciones[] = $row['idCoordinacion'];
            }

            if (empty($coordinaciones)) {
                echo "<script>alert('No tiene coordinaciones asignadas. Contacte al administrador.'); window.location='../../index.php';</script>";
                exit;
            }

            // Regenerar ID de sesión (seguridad)
            session_regenerate_id(true);

            // Guardar todos los datos en sesión
            $_SESSION['usuario'] = [
                'Cedula' => $usuario['Cedula'] ?? '',
                'Nombres' => $usuario['Nombres'] ?? '',
                'Apellidos' => $usuario['Apellidos'] ?? '',
                'Cargo' => $usuario['Cargo'] ?? '',
                'codigoUsuario' => $usuario['codigoUsuario'] ?? '',
                'super_usuario' => $usuario['super_usuario'] ?? 0
            ];
            $_SESSION['procesos'] = $procesos;
            $_SESSION['coordinaciones'] = $coordinaciones;

            // Registrar última actividad
            $_SESSION['ultima_actividad'] = time();

            // Redirigir al listado principal
            header("Location: list.php");
            exit;

        } else {
            echo "<script>alert('Usuario o contraseña incorrectos'); window.location='../../index.php';</script>";
        }
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos'); window.location='../../index.php';</script>";
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conexion);
}
?>
