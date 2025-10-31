<?php
  session_start();

    // Bloquea acceso si no es super usuario
  if (!isset($_SESSION['usuario'])) {
      header("Location: ../../index.php");
      exit;
  }

  $esSuperUsuario = $_SESSION['usuario']['super_usuario'] ?? 0;

  // Ejemplo de validación
  if (!$esSuperUsuario) {
      echo "<script>alert('No tienes permisos para registrar indicadores.'); window.location='list.php';</script>";
      exit;
  }
  include '../../bd/conexion.php';
  if (!isset($_SESSION['usuario'])) {
      header("Location: ../../index.php");
      exit;
  }


// Obtener procesos del usuario desde la sesión
$procesosSesion = $_SESSION['procesos'] ?? [];

// Normalizar a lista de IDs (acepta dos formatos: [1,2,3] o [['idProceso'=>1,'Proceso'=>'X'], ...])
$procesosAUsar = [];
if (!empty($procesosSesion)) {
    // si es array de arrays (con idProceso), extraemos ids
    if (is_array($procesosSesion) && isset($procesosSesion[0]) && is_array($procesosSesion[0]) && isset($procesosSesion[0]['idProceso'])) {
        foreach ($procesosSesion as $p) {
            $procesosAUsar[] = (int)$p['idProceso'];
        }
    } else {
        // si viene ya como array simple de ids
        foreach ($procesosSesion as $p) {
            $procesosAUsar[] = (int)$p;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restristrar</title>
    <link rel="stylesheet" href="{{ url_for('../../static', filename='css/style.css')}}">
    <link rel="icon" type="image/x-icon" href="../../static/img/icon_pag.png">
    <link rel="stylesheet" type="text/css" href="../../static/css/style.css">
    <link rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../static/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="../../static/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="../../static/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="../../static/plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="{{ url_for('../../static', filename='css/adminlte.min.css')}}">
    <link rel="stylesheet" href="../../static/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="../../static/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="../../static/plugins/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="../../static/css/adminlte.min.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/bootstrap.min.css">
    <link
      href="https://fonts.googleapis.com/css2?family=Fuzzy+Bubbles:wght@700&family=Source+Serif+Pro:ital,wght@1,600&display=swap"
      rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.5/dist/umd/popper.min.js"
      integrity="sha384-Xe+8cL9oJa6tN/veChSP7q+mnSPaj5Bcu9mPX5F5xIGE0DVittaqT5lorf0EI7Vk"
      crossorigin="anonymous"></script>
    <script type="text/javascript" src="../../static/js/bootstrap.min.js"></script>
    <style>
      .card-img-top {
        transition: transform 0.4s ease, box-shadow 0.4s ease;
      }


      .card-img-top:hover {
        transform: scale(1.15);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
      }

      .footer-fixed {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #fff;
        /* puedes cambiar el color */
        border-top: 1px solid #ddd;
        text-align: center;
        padding: 10px;
        z-index: 1030;
        /* asegura que quede encima de otros elementos */
      }
    </style>
  </head>

  <body class="hold-transition sidebar-mini layout-fixed" style="background-image: url('../../static/img/fondo5.png');background-size: cover;background-repeat: no-repeat; background-position: center;background-attachment: fixed;">


    <!------------cabezera-------------------------->
    <header>
      <nav id="navbar-example2" class="navbar px-3 mb-3" style="background-color: #038f03ff;">
        <!--INICIO BOTON BACK-->    
        <a href="list.php" class="btn"   style="width: 70px; height: 50px; border-color: transparent;">
            <img style="width:70px; height:50px;" class="card-img-top" src="../../static/img/flechaIzquierda.png" alt="Regresar Menu" >
        </a>
        <!--FIN BOTON BACK--> 
        <a href="../../index.php" class="brand-link">
          <img src="../../static/img/CycLogo.png" alt="MLLS LOGO" class="brand-image ">
        </a>
        <span style="color:white; font-weight:bold; margin-left:15px;">
          NUEVO REGISTRO
        </span>
      </nav>
    </header>
    <!------------------------------------------------------------------------>

    
    <div class="container">
      <div class="card mt-4 border-info">
        <br><h2 class="card-title background-color-b"> <b>Registro de indicadores</b></h2><br>
        <div class="card-body">
          <form action="proceso_registro_indicadores.php" method="POST">
            <div class="form-group">
              <label for="idProceso">PROCESO</label>
              <select class="form-select"  id="idProceso" name="idProceso" required>
                <option>Seleccione un proceso</option>
                 <?php
                  if (!empty($procesosAUsar)) {
                      // construir placeholders y ejecutar consulta sólo con los procesos del usuario
                      $placeholders = implode(',', array_fill(0, count($procesosAUsar), '?'));
                      $sql = "SELECT idProceso, Proceso FROM dbo.Proceso WHERE idProceso IN ($placeholders) ORDER BY Proceso";
                      $stmt = sqlsrv_query($conexion, $sql, $procesosAUsar);

                      if ($stmt === false) {
                          echo '<option value="">Error al cargar procesos</option>';
                      } else {
                          while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                              $id = (int)$r['idProceso'];
                              $nombre = htmlspecialchars($r['Proceso'], ENT_QUOTES, 'UTF-8');
                              echo "<option value='{$id}'>{$nombre}</option>";
                          }
                          sqlsrv_free_stmt($stmt);
                      }
                  } else {
                      echo "<option value=''>⚠️ No tiene procesos asignados</option>";
                  }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label for="coordinacion">COORDINACIÓN</label>
              <select class="form-select"  id="idCoordinacion" name="idCoordinacion" required>
                <option>Seleccione una coordinacion</option>
                <?php
                include '../../bd/conexion.php';
                $query = "SELECT idCoordinacion, Coordinacion FROM dbo.Coordinacion ";
                $result = sqlsrv_query($conexion, $query);
                while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                    echo "<option value='{$row['idCoordinacion']}'>{$row['Coordinacion']}</option>";
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label for="codigo">CÓDIGO</label>
              <input type="text" class="form-control" id="codigo" name="codigo" required>
            </div>

            <div class="form-group">
              <label for="nombre_indicador">NOMBRE INDICADOR</label>
              <input type="text" class="form-control" id="nombre_indicador" name="nombre_indicador" required>
            </div>

            <div class="form-group">
              <label for="objetivo">OBJETIVO</label>
              <input type="text" class="form-control" id="objetivo" name="objetivo" required>
            </div>

            <div class="form-group">
              <label for="periodicidad">PERIODICIDAD</label>
              <select class="form-select"  id="periodicidad" name="periodicidad" aria-label="Default select example"  required>
                <option value="" >Seleccione la periodicidad</option>
                <option value="Anual">Anual</option>
                <option value="Semestral">Semestral</option>
                <option value="Trimestral">Trimestral</option>
                <option value="Mensual">Mensual</option>
                <option value="Dias">Dias</option>
              </select>
              
            </div>

            <div class="form-group">
              <label for="fuente_informacion">FUENTE INFORMACIÓN</label>
              <input type="text" class="form-control" id="fuente_informacion" name="fuente_informacion" required>
            </div>

            <div class="form-group">
              <label for="meta">META</label>
              <input type="text" class="form-control" id="meta" name="meta" required>
            </div>

            <div class="mt-3">
              <button type="submit" class="btn btn-warning">Registrar</button>
              <a href="list.php" class="btn btn-info">Regresar al listado</a>
            </div>
          </form>
        </div>
      </div>
    </div> <br><br>


    <!-------------------------------------------BARRA FINAL COPYRIGHT-------------------------------------------------->
    <footer class="footer-fixed">
      <strong>Copyright &copy; 2025 <a target="_blank">COORDINACIÓN ESTADÍSTICA</a></strong> Todos los derechos
      reservados.
      <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 1.1.0
      </div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
    <!--------------------------------------------------------------------------------------------->

    <script src="../../static/plugins/jquery/jquery.min.js"></script>
    <script src="../../static/plugins/jquery-ui/jquery-ui.min.js"></script>
    <script>
      $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="../../static/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../static/plugins/chart.js/Chart.min.js"></script>
    <script src="../../static/plugins/sparklines/sparkline.js"></script>
    <script src="../../static/plugins/jqvmap/jquery.vmap.min.js"></script>
    <script src="../../static/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
    <script src="../../static/plugins/jquery-knob/jquery.knob.min.js"></script>
    <script src="../../static/plugins/moment/moment.min.js"></script>
    <script src="../../static/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="../../static/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="../../static/plugins/summernote/summernote-bs4.min.js"></script>
    <script src="../../static/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="../../static/js/adminlte.js"></script>
    <script src="../../static/js/pages/dashboard.js"></script>
    <script src="../../static/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
    <script src="../../static/js/adminlte.min.js"></script>
    <script src="../../static/js/confirmacion.js"></script>
  </body>
</html>
