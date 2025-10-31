<?php
session_start();
  // Si no hay sesión activa, redirigir al login
  if (!isset($_SESSION['usuario'])) {
      header("Location: ../../index.php");
      exit;
  }
include '../../bd/conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID inválido');window.location.href='list.php';</script>";
    exit;
}

$id = intval($_GET['id']);

// Consultar datos del indicador
$sqlIndicador = "SELECT * FROM dbo.indicadores WHERE id_indicador = ?";
$stmtIndicador = sqlsrv_query($conexion, $sqlIndicador, [$id]);

if ($stmtIndicador === false || !($rowIndicador = sqlsrv_fetch_array($stmtIndicador, SQLSRV_FETCH_ASSOC))) {
    echo "<script>alert('Registro no encontrado');window.location.href='list.php';</script>";
    exit;
}

// Consultar resultados asociados
$sqlResultados = "SELECT * FROM dbo.indicadores_resultado WHERE id_idicador = ?";
$stmtResultados = sqlsrv_query($conexion, $sqlResultados, [$id]);

if ($stmtResultados === false) {
    echo "<script>alert('Error al consultar resultados');window.location.href='list.php';</script>";
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
    <title>Editar Indicador</title>
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
          Editar indicador
        </span>
      </nav>
    </header>
    <!------------------------------------------------------------------------>


    <div class="container">
      <div class="card mt-4 border-info">
        <br><h2 class="card-title background-color-b"> <b>Editar Indicador</b></h2><br>
        <div class="card-body">
        <form action="proceso_edicion.php" method="POST">
          <!-- ID oculto -->
          <input type="hidden" name="id_indicador" value="<?= $rowIndicador['id_indicador'] ?>">



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

          <div class="form-group">
              <label for="coordinacion">Coordinación</label>
               <select class="form-select" id="idCoordinacion" name="idCoordinacion" aria-label="Default select example" required>
                    <option value="">Seleccione una coordinación</option>
                    <?php
                    $query = "SELECT idCoordinacion, Coordinacion FROM dbo.Coordinacion";
                    $result = sqlsrv_query($conexion, $query);

                    // Guardamos la coordinación actual del indicador
                    $idCoordinacionActual = $rowIndicador['idCoordinacion'];

                    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                        // Si el id actual coincide con el del indicador, marcamos 'selected'
                        $selected = ($row['idCoordinacion'] == $idCoordinacionActual) ? 'selected' : '';
                        echo "<option value='{$row['idCoordinacion']}' $selected>{$row['Coordinacion']}</option>";
                    }
                    ?>
               </select>
            
            </div>

          <div class="mb-3">
              <label class="form-label">Código</label>
              <input type="text" name="codigo" class="form-control" 
                    value="<?= htmlspecialchars($rowIndicador['codigo']) ?>" required>
          </div>

          <div class="mb-3">
              <label class="form-label">Nombre Indicador</label>
              <input type="text" name="nombre_indicador" class="form-control" 
                    value="<?= htmlspecialchars($rowIndicador['nombre_indicador']) ?>" required>
          </div>

          <div class="mb-3">
              <label class="form-label">Objetivo</label>
              <textarea name="objetivo" class="form-control" rows="3"><?= htmlspecialchars($rowIndicador['objetivo']) ?></textarea>
          </div>

          <div class="mb-3">
              <label class="form-label">Periodicidad</label>
              <select class="form-select" id="periodicidad" name="periodicidad" aria-label="Default select example" required>
                  <option value="">Seleccione la periodicidad</option>
                  <?php
                  // Guardamos la periodicidad actual del indicador
                  $periodicidadActual = strtolower(trim($rowIndicador['periodicidad']));

                  // Lista de opciones posibles
                  $periodicidades = ["Anual", "Semestral", "Trimestral", "Mensual", "Dias"];

                  // Recorremos las opciones
                  foreach ($periodicidades as $p) {
                      $selected = (strtolower($p) == $periodicidadActual) ? 'selected' : '';
                      echo "<option value='$p' $selected>$p</option>";
                  }
                  ?>
              </select>

          </div>

          <div class="mb-3">
              <label class="form-label">Fuente Información</label>
              <input type="text" name="fuente_informacion" class="form-control" 
                    value="<?= htmlspecialchars($rowIndicador['fuente_informacion']) ?>">
          </div>

          <div class="mb-3">
              <label class="form-label">Meta</label>
              <input type="text" name="meta" class="form-control" 
                    value="<?= htmlspecialchars($rowIndicador['meta']) ?>">
          </div>

          <hr>
          <h4>Resultados del Indicador</h4>

            <?php while ($rowResultado = sqlsrv_fetch_array($stmtResultados, SQLSRV_FETCH_ASSOC)) : ?>
              <div class="border p-3 mb-3">
                  <!-- ID real de indicadores_resultado -->
                  <input type="hidden" name="id_result[]" 
                        value="<?= htmlspecialchars($rowResultado['id_result'], ENT_QUOTES, 'UTF-8') ?>">

                  <!-- Llave foránea hacia indicadores -->
                  <input type="hidden" name="id_idicador[]" 
                        value="<?= htmlspecialchars($rowResultado['id_idicador'], ENT_QUOTES, 'UTF-8') ?>">

                  <div class="row g-2">
                      <div class="col-md-3">
                          <label class="form-label">Mes</label>
                          <select class="form-select" id="mes" name="mes[]" aria-label="Default select example" required>
                              <option value="">Seleccione un mes</option>
                              <?php
                              // Guardamos el mes actual del resultado
                              $mesActual = strtolower(trim($rowResultado['mes'])); // por si viene con mayúsculas o espacios

                              // Array de meses en orden
                              $meses = [
                                  "enero", "febrero", "marzo", "abril", "mayo", "junio",
                                  "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
                              ];

                              // Recorremos los meses para crear las opciones
                              foreach ($meses as $mes) {
                                  $selected = ($mes == $mesActual) ? 'selected' : '';
                                  echo "<option value='$mes' $selected>" . ucfirst($mes) . "</option>";
                              }
                              ?>
                          </select>

                      </div>
                      <div class="col-md-2">
                          <label class="form-label">Num</label>
                          <input type="number" name="num[]" class="form-control" 
                                value="<?= htmlspecialchars($rowResultado['num'], ENT_QUOTES, 'UTF-8') ?>">
                      </div>
                      <div class="col-md-2">
                          <label class="form-label">Dem</label>
                          <input type="number" name="dem[]" class="form-control" 
                                value="<?= htmlspecialchars($rowResultado['dem'], ENT_QUOTES, 'UTF-8') ?>">
                      </div>
                      <div class="col-md-3">
                          <label class="form-label">Resultado</label>
                          <input type="text" name="resultado[]" class="form-control" 
                                value="<?= htmlspecialchars($rowResultado['resultado'], ENT_QUOTES, 'UTF-8') ?>">
                      </div>
                  </div>
              </div>
            <?php endwhile; ?>

          <button type="submit" class="btn btn-success">Guardar cambios</button>
          <a href="list.php" class="btn btn-secondary">Cancelar</a>
        </form>
        </div>
      </div>
    </div><br><br>

      
      

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
