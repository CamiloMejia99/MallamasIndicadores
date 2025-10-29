<?php
  include '../../bd/conexion.php';

  // --- Configuración de paginación ---
  $registrosPorPagina = 13;
  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  if ($pagina < 1) $pagina = 1;
  $offset = ($pagina - 1) * $registrosPorPagina;

  // --- Configuración de orden ---
  $columnas = ["id_indicador","proceso","coordinacion","codigo","nombre_indicador",
              "objetivo","periodicidad","fuente_informacion","meta","mes","num","dem","resultado"];
  $orden = isset($_GET['order']) && in_array($_GET['order'], $columnas) ? $_GET['order'] : "id_indicador";
  $direccion = (isset($_GET['dir']) && strtoupper($_GET['dir']) === "DESC") ? "DESC" : "ASC";

  // --- Consulta SQL con orden y paginación ---
  $sql = "SELECT 
            a.[id_indicador],
            c.[proceso],
            d.[coordinacion],
            a.[codigo],
            a.[nombre_indicador],
            a.[objetivo],
            a.[periodicidad],
            a.[fuente_informacion],
            a.[meta],
            b.[id_idicador],
            b.[mes],
            b.[num],
            b.[dem],
            b.[resultado],
            b.[id_result]
            FROM dbo.indicadores as a  
              INNER JOIN dbo.indicadores_resultado as b ON a.id_indicador = b.id_idicador
              INNER JOIN dbo.Proceso AS c ON a.idProceso = c.idProceso
              INNER JOIN dbo.Coordinacion as d on a.idCoordinacion = d.idCoordinacion
          ORDER BY $orden $direccion 
          OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

  $params = [$offset, $registrosPorPagina];
  $stmt = sqlsrv_query($conexion, $sql, $params);

  if ($stmt === false) {
      echo "<p>Error en la consulta.</p>";
      die(print_r(sqlsrv_errors(), true));
  }

  // --- Contar total de registros ---
  $sqlCount = "SELECT COUNT(*) as total 
              FROM dbo.indicadores as a  
              INNER JOIN dbo.indicadores_resultado as b ON a.id_indicador = b.id_idicador
              INNER JOIN dbo.Proceso AS c ON a.idProceso = c.idProceso";
  $countStmt = sqlsrv_query($conexion, $sqlCount);
  $totalRegistros = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC)['total'];
  $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Listado</title>
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
        <a href="../../index.php" class="brand-link">
          <img src="../../static/img/CycLogo.png" alt="MLLS LOGO" class="brand-image ">
        </a>
        <span style="color:white; font-weight:bold; margin-left:15px;">
          LISTADO
        </span>
      </nav>
    </header>
    <!------------------------------------------------------------------------>




      
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div id="contenedor2" class="col-12">
            <div class="card border-info">
              <div class="card-body">
                <a href="register_indicadores.php" class="btn btn-warning">Registrar Indicadores</a>
                <a href="register_indicadores_resultado.php" class="btn btn-warning">Registrar Calculo Indicadores</a>
                <a href="vista_exportar_excel.php" class="btn btn-success "><i class="fas fa-file-excel"></i> Exportar a Excel</a>
                <!-- <a href="export_csv.php" class="btn btn-success"><i class="fas fa-file-excel"></i> Exportar a Excel (CSV)</a> -->
                <section class="content mt-5">
                  <!-- <form action="proceso_buscar.php" method="post" class="d-flex align-items-center mt-3">
                    <label for="idBuscar" class="me-2">Buscar ID</label>
                    <input type="number" class="form-control me-2" id="idBuscar" name="idBuscar" placeholder="ID" style="width:150px;">
                    <button type="submit" class="btn btn-info">Consultar</button>
                  </form> -->
                  <div class="card card-primary">
                    <div class="card-header">
                      <h3 class="card-title">Registros</h3>
                    </div>
                    <div class="container-fluid">
                      <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                          <thead class="table-light">
                            <tr>
                              <?php foreach ($columnas as $col): ?>
                                <th>
                                  <a href="?order=<?= $col ?>&dir=<?= ($orden === $col && $direccion === 'ASC') ? 'DESC' : 'ASC' ?>">
                                    <?= ucfirst($col) ?>
                                    <?php if ($orden === $col): ?>
                                      <?= $direccion === "ASC" ? "(↓)" : "(↑)" ?>
                                    <?php endif; ?>
                                  </a>
                                </th>
                              <?php endforeach; ?>
                              <th>Opciones</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (sqlsrv_has_rows($stmt)): ?>
                              <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                                <tr>
                                  <?php foreach ($columnas as $col): ?>
                                    <td><?= htmlspecialchars($row[$col]) ?></td>
                                  <?php endforeach; ?>
                                  <td>
                                    <a href="edit.php?id=<?= $row['id_indicador'] ?>" class="btn btn-sm btn-primary"> <i class='fas fa-edit'></i></a>
                                    <a href="proceso_eliminar.php?id=<?php echo $row['id_indicador']; ?>" class="btn btn-danger btn-sm" onclick="return confirmarEliminacion(event);"><i class='fas fa-trash'></i></a>
                                  </td>
                                </tr>
                              <?php endwhile; ?>
                            <?php else: ?>
                              <tr><td colspan="<?= count($columnas)+1 ?>">No hay registros</td></tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>

                      <!-- Paginación -->
                      <nav>
                        <ul class="pagination">
                          <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                              <a class="page-link" href="?page=<?= $i ?>&order=<?= $orden ?>&dir=<?= $direccion ?>"><?= $i ?></a>
                            </li>
                          <?php endfor; ?>
                        </ul>
                      </nav>
                    </div>
                    
                  </div>
                </section>
              </div>
            </div>
          </div>
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
    <script>
      function confirmarEliminacion(event) {
          event.preventDefault(); // evita ir al enlace de inmediato
          const url = event.currentTarget.getAttribute('href');

          if (confirm("¿Está seguro que desea eliminar este registro?")) {
              window.location.href = url; // si confirma, redirige a eliminar.php
          } else {
              return false; // si cancela, no hace nada
          }
      }
   </script>
  </body>

</html>

<?php
  sqlsrv_free_stmt($stmt);
  sqlsrv_free_stmt($countStmt);
  sqlsrv_close($conexion);
?>