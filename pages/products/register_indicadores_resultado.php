<?php
include '../../bd/conexion.php';
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
  // Si no hay sesión activa, redirigir al login
  if (!isset($_SESSION['usuario'])) {
      header("Location: ../../index.php");
      exit;
  }
?>
<!DOCTYPE html>
<html lang="es">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restristrar calculo de indicadores </title>
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
        <br>
        <h2 class="card-title background-color-b"><b>Registro de calculo indicadores</b></h2><br>
        <div class="card-body">

          <form action="proceso_registro_indicadores_resultado.php" method="POST">
            <div class="form-group">
              <label for="id_indicador">Indicador</label>
              <select class="form-control" id="id_indicador" name="id_indicador" required>
                <option value="">Seleccione el codigo del indicador</option>
                <?php
                $sql = "SELECT id_indicador, codigo FROM indicadores ORDER BY codigo ASC";
                $stmt = sqlsrv_query($conexion, $sql);

                if ($stmt !== false) {
                  while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    echo "<option value='{$row['id_indicador']}'>{$row['codigo']}</option>";
                  }
                  sqlsrv_free_stmt($stmt);
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label for="mes">MES</label>
             
              <select class="form-select"  id="mes" name="mes" aria-label="Default select example"  required>
                <option value="" >Seleccione un mes</option>
                <option value="enero">Enero</option>
                <option value="febrero">Febrero</option>
                <option value="marzo">Marzo</option>
                <option value="abril">Abril</option>
                <option value="mayo">Mayo</option>
                <option value="junio">Junio</option>
                <option value="julio">Julio</option>
                <option value="agosto">Agosto</option>
                <option value="septiembre">Septiembre</option>
                <option value="octubre">Octubre</option>
                <option value="noviembre">Noviembre</option>
                <option value="diciembre">Diciembre</option>
              </select>

            </div>

            <div class="form-group">
              <label for="num">NUMERADOR</label>
              <input type="number" class="form-control" id="num" name="num" required>
            </div>

            <div class="form-group">
              <label for="dem">DENOMINADOR</label>
              <input type="number" class="form-control" id="dem" name="dem" required>
            </div>

            <div class="form-group">
              <label for="resultado">RESULTADO</label>
              <input type="text" class="form-control" id="resultado" name="resultado" required>
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
