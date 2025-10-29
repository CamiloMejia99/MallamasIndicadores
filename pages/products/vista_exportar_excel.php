<?php
  include '../../bd/conexion.php';

  // --- Configuración de paginación ---
  $registrosPorPagina = 12;
  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  if ($pagina < 1) $pagina = 1;
  $offset = ($pagina - 1) * $registrosPorPagina;

  // --- Configuración de orden ---
  $columnas = ["id_indicador","proceso","coordinacion","codigo","nombre_indicador",
              "objetivo","periodicidad","fuente_informacion","meta","num","dem","resultado"];
  $orden = isset($_GET['order']) && in_array($_GET['order'], $columnas) ? $_GET['order'] : "id_indicador";
  $direccion = (isset($_GET['dir']) && strtoupper($_GET['dir']) === "DESC") ? "DESC" : "ASC";

  // --- Filtros por mes (texto) ---
  $mesInicio = isset($_GET['mes_inicio']) ? strtolower($_GET['mes_inicio']) : "";
  $mesFin = isset($_GET['mes_fin']) ? strtolower($_GET['mes_fin']) : "";

  // --- Mapeo de meses ---
  $mesesOrden = [
      "enero" => 1, "febrero" => 2, "marzo" => 3, "abril" => 4,
      "mayo" => 5, "junio" => 6, "julio" => 7, "agosto" => 8,
      "septiembre" => 9, "octubre" => 10, "noviembre" => 11, "diciembre" => 12
  ];

  $filtroMes = "";
  $params = [$offset, $registrosPorPagina];

  if ($mesInicio && $mesFin && isset($mesesOrden[$mesInicio]) && isset($mesesOrden[$mesFin])) {
      $filtroMes = "WHERE 
          CASE LOWER(b.mes)
              WHEN 'enero' THEN 1 WHEN 'febrero' THEN 2 WHEN 'marzo' THEN 3 WHEN 'abril' THEN 4
              WHEN 'mayo' THEN 5 WHEN 'junio' THEN 6 WHEN 'julio' THEN 7 WHEN 'agosto' THEN 8
              WHEN 'septiembre' THEN 9 WHEN 'octubre' THEN 10 WHEN 'noviembre' THEN 11 WHEN 'diciembre' THEN 12
          END BETWEEN ? AND ?";
      $params = [$mesesOrden[$mesInicio], $mesesOrden[$mesFin], $offset, $registrosPorPagina];
  }

  // --- Consulta SQL ---
  $sql = "SELECT * 
          FROM dbo.indicadores AS a
          INNER JOIN dbo.indicadores_resultado AS b ON a.id_indicador = b.id_idicador
          $filtroMes
          ORDER BY $orden $direccion 
          OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

  $stmt = sqlsrv_query($conexion, $sql, $params);

  if ($stmt === false) {
      echo "<p>Error en la consulta.</p>";
      die(print_r(sqlsrv_errors(), true));
  }

  // --- Contar total de registros ---
  $sqlCount = "SELECT COUNT(*) as total 
              FROM dbo.indicadores AS a  
              INNER JOIN dbo.indicadores_resultado AS b ON a.id_indicador = b.id_idicador
              $filtroMes";
  $countParams = $mesInicio && $mesFin ? [$mesesOrden[$mesInicio], $mesesOrden[$mesFin]] : [];
  $countStmt = sqlsrv_query($conexion, $sqlCount, $countParams);
  $totalRegistros = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC)['total'];
  $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exportar Excel</title>
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
          EXPORTAR REPORTE
        </span>
      </nav>
    </header>
    <!------------------------------------------------------------------------>




      
    <div class="container mt-4">
      <div class="card border-info shadow">
        <div class="card-body">
          <form method="GET" action="vista_exportar_excel.php" class="row g-3">
            <div class="col-md-3">
              <label for="mes_inicio" class="form-label"><b>Mes inicio</b></label>
              <select class="form-control" name="mes_inicio" id="mes_inicio" required>
                <option value="">--Seleccione--</option>
                <?php foreach ($mesesOrden as $mes => $num): ?>
                  <option value="<?= $mes ?>" <?= ($mes == $mesInicio ? 'selected' : '') ?>>
                    <?= ucfirst($mes) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label for="mes_fin" class="form-label"><b>Mes fin</b></label>
              <select class="form-control" name="mes_fin" id="mes_fin" required>
                <option value="">--Seleccione--</option>
                <?php foreach ($mesesOrden as $mes => $num): ?>
                  <option value="<?= $mes ?>" <?= ($mes == $mesFin ? 'selected' : '') ?>>
                    <?= ucfirst($mes) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 align-self-end">
              <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
            </div>
            <div class="col-md-3 align-self-end">
              <a href="export_excel.php?mes_inicio=<?= $mesInicio ?>&mes_fin=<?= $mesFin ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar a Excel
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <br><br>

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