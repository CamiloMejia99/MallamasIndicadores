<?php
  include '../../bd/conexion.php';
  session_start();
  

  // Si no hay sesión activa, redirigir al login
  if (!isset($_SESSION['usuario'])) {
      header("Location: ../../index.php");
      exit;
  }

  // --- Obtener procesos asignados ---
  $procesosUsuario = $_SESSION['procesos'] ?? [];

  // 🔹 Verificar si el usuario tiene acceso a Power BI
  $tieneAccesoPBi = false;
  // --- Verificar si el usuario es superusuario ---
  $esSuperUsuario = isset($_SESSION['usuario']['super_usuario']) && $_SESSION['usuario']['super_usuario'] == 1;


  if (!empty($procesosUsuario)) {
      // Crear placeholders (?, ?, ?, ...)
      $placeholders = implode(',', array_fill(0, count($procesosUsuario), '?'));

      $sqlPBi = "SELECT COUNT(*) AS total
                FROM Proceso
                WHERE idProceso IN ($placeholders)
                AND accesoPBi = 1";

      $stmtPBi = sqlsrv_query($conexion, $sqlPBi, $procesosUsuario);

      if ($stmtPBi && $rowPBi = sqlsrv_fetch_array($stmtPBi, SQLSRV_FETCH_ASSOC)) {
          $tieneAccesoPBi = $rowPBi['total'] > 0;
      }
  }

  // Si el arreglo viene con subarreglos, extraer solo los idProceso
  if (isset($procesosUsuario[0]) && is_array($procesosUsuario[0]) && isset($procesosUsuario[0]['idProceso'])) {
      $procesosUsuario = array_column($procesosUsuario, 'idProceso');
  }

  // Verificar que tenga procesos
  if (empty($procesosUsuario)) {
      echo "<script>alert('No tiene procesos asignados. Contacte al administrador.'); window.location='../../index.php';</script>";
      exit;
  }

  // --- Crear placeholders dinámicos para los procesos ---
  $placeholders = implode(',', array_fill(0, count($procesosUsuario), '?'));

  // --- Configuración de paginación ---
  $registrosPorPagina = 13;
  $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  if ($pagina < 1) $pagina = 1;
  $offset = ($pagina - 1) * $registrosPorPagina;

  // --- Configuración de orden ---
  $columnas = [
      "id_indicador", "proceso", "coordinacion", "codigo", "nombre_indicador",
      "objetivo", "periodicidad", "fuente_informacion", "meta", "mes", "num", "dem", "resultado"
  ];

  $orden = (isset($_GET['order']) && in_array($_GET['order'], $columnas)) ? $_GET['order'] : "id_indicador";
  $direccion = (isset($_GET['dir']) && strtoupper($_GET['dir']) === "DESC") ? "DESC" : "ASC";

  // --- Consulta SQL principal ---
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
    FROM dbo.indicadores AS a
    INNER JOIN dbo.indicadores_resultado AS b ON a.id_indicador = b.id_idicador
    INNER JOIN dbo.Proceso AS c ON a.idProceso = c.idProceso
    INNER JOIN dbo.Coordinacion AS d ON a.idCoordinacion = d.idCoordinacion
    WHERE a.idProceso IN ($placeholders)
    ORDER BY $orden $direccion 
    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

  // Parámetros: primero los procesos, luego paginación
  $params = array_merge($procesosUsuario, [$offset, $registrosPorPagina]);

  $stmt = sqlsrv_query($conexion, $sql, $params);

  if ($stmt === false) {
      echo "<p>Error en la consulta.</p>";
      die(print_r(sqlsrv_errors(), true));
  }

  // --- Contar total de registros del usuario ---
  $sqlCount = "SELECT COUNT(*) as total
              FROM dbo.indicadores AS a
              INNER JOIN dbo.indicadores_resultado AS b ON a.id_indicador = b.id_idicador
              INNER JOIN dbo.Proceso AS c ON a.idProceso = c.idProceso
              WHERE a.idProceso IN ($placeholders)";

  $countStmt = sqlsrv_query($conexion, $sqlCount, $procesosUsuario);
  $totalRegistros = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC)['total'] ?? 0;
  $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Listado</title>

  <link rel="icon" type="image/x-icon" href="../../static/img/icon_pag.png">
  <link rel="stylesheet" type="text/css" href="../../static/css/style.css">
  <link rel="stylesheet" href="../../static/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../static/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="../../static/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="../../static/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="../../static/plugins/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="../../static/plugins/summernote/summernote-bs4.min.css">
  <link rel="stylesheet" href="../../static/css/adminlte.min.css">
  <link rel="stylesheet" type="text/css" href="../../static/css/bootstrap.min.css">
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
      border-top: 1px solid #ddd;
      text-align: center;
      padding: 10px;
      z-index: 1030;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed"
  style="background-image: url('../../static/img/fondo5.png'); background-size: cover; background-repeat: no-repeat; background-position: center; background-attachment: fixed;">

  <!-- CABECERA -->
  <header>
    <nav id="navbar-example2" class="navbar px-3 mb-3" style="background-color: #038f03ff;">
      <a href="../../index.php" class="brand-link">
        <img src="../../static/img/CycLogo.png" alt="MLLS LOGO" class="brand-image">
      </a>

      <a>
       <?php
          if (isset($_SESSION['usuario'])) {
              $nombre = htmlspecialchars($_SESSION['usuario']['Nombres'] ?? '');
              $apellido = htmlspecialchars($_SESSION['usuario']['Apellidos'] ?? '');
              $direccion = htmlspecialchars($_SESSION['usuario']['Direccion'] ?? '');
              $cargo = htmlspecialchars($_SESSION['usuario']['Cargo'] ?? '');
              echo "<span style='color:white; margin-left:15px;'> <b>USUARIO:</b>  $nombre $apellido - $direccion - $cargo</span>";
          } else {
              echo "<span style='color:white; font-weight:bold; margin-left:15px;'>USUARIO: </span>";
          }
       ?>

      </a>
      <a href="cerrar_sesion.php" style="color:white; font-weight:bold; margin-left:auto;">Cerrar sesión</a>

    </nav>
  </header>

  <!-- CONTENIDO -->
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div id="contenedor2" class="col-12">
          <div class="card border-info">
            <div class="card-body">
              
              <!-- desaparecer o aparecer segun usuario superusuario -->
              <?php if ($esSuperUsuario): ?>
                <a href="register_indicadores.php" class="btn btn-warning">Registrar Indicadores</a>
                <a href="register_indicadores_resultado.php" class="btn btn-warning">Registrar Cálculo Indicadores</a>
              <?php endif; ?>
             
              <!-- <a data-toggle="modal" data-target="#modalPowerBI" class="btn btn-warning">📈 Tablero de Control</a> -->
              <?php if ($tieneAccesoPBi): ?>
                <a data-toggle="modal" data-target="#modalPowerBI" class="btn btn-warning">📈 Tablero de Control</a>
              <?php endif; ?>
              <a href="vista_exportar_excel.php" class="btn btn-success"><i class="fas fa-file-excel"></i> Exportar a Excel</a>

              <section class="content mt-5">
                <div class="card card-primary">
                  <div class="card-header">
                    <h3 class="card-title">Indicadores</h3>
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
                                  <a href="edit.php?id=<?= $row['id_indicador'] ?>" class="btn btn-sm btn-primary">
                                    <i class='fas fa-edit'></i>
                                  </a>
                                  <a href="proceso_eliminar.php?id=<?= $row['id_indicador']; ?>"
                                    class="btn btn-danger btn-sm" onclick="return confirmarEliminacion(event);">
                                    <i class='fas fa-trash'></i>
                                  </a>
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
  <!------------------------------------------ Modal Power bi MATRIZ DE INDICADORES --------------------------------->
  <div class="modal fade" id="modalPowerBI" tabindex="-1" aria-labelledby="modalPowerBILabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" style="max-width: 100%;">
                <div class="modal-content">
                    <div class="modal-header" style="background-color:#038f03ff; color:white;">
                        <h5 class="modal-title" id="modalPowerBILabel">Tablero MATRIZ DE INDICADORES</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="iframe-container" style="position:relative; width:100%; padding-bottom:70%; height:0; overflow:hidden;">
                        <iframe src="https://app.powerbi.com/view?r=eyJrIjoiN2RlZmIzMTktMTQ2Yy00ZDVmLWE3NzgtNzc3MTc3ZGMyMzFlIiwidCI6ImIxMmQwMGE5LWZjYjMtNDgzNi1iMDk0LWU3ZWNmZDAyM2U5OSIsImMiOjR9"
                                allowFullScreen="true"
                                style="position:absolute; top:0; left:0; width:100%; height:100%; border:none;">
                        </iframe>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
    </div>

  <!-- FOOTER -->
  <footer class="footer-fixed">
    <strong>Copyright &copy; 2025 <a target="_blank">COORDINACIÓN ESTADÍSTICA</a></strong>
    Todos los derechos reservados.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.1.0
    </div>
  </footer>

  <aside class="control-sidebar control-sidebar-dark"></aside>

  <!-- SCRIPTS -->
  <script src="../../static/plugins/jquery/jquery.min.js"></script>
  <script src="../../static/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../static/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <script src="../../static/js/adminlte.min.js"></script>
  <script>
    function confirmarEliminacion(event) {
        event.preventDefault();
        const url = event.currentTarget.getAttribute('href');
        if (confirm("¿Está seguro que desea eliminar este registro?")) {
            window.location.href = url;
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
