<?php
session_start();
require '../../vendor/autoload.php';
require '../../bd/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// -------------------------------------------------------------
// 1. Verificar sesión y obtener procesos del usuario
// -------------------------------------------------------------
if (!isset($_SESSION['usuario']) || !isset($_SESSION['procesos'])) {
    echo "<script>alert('Sesión no válida. Inicie sesión nuevamente.'); window.location='../../index.php';</script>";
    exit;
}

$procesosUsuario = $_SESSION['procesos'];
if (empty($procesosUsuario)) {
    echo "<script>alert('No tiene procesos asignados.'); window.location='../../index.php';</script>";
    exit;
}

// Generar placeholders dinámicos para los procesos
$placeholdersProcesos = implode(',', array_fill(0, count($procesosUsuario), '?'));

// -------------------------------------------------------------
// 2. Recibir filtros de mes (texto: enero, febrero, etc.)
// -------------------------------------------------------------
$mesInicio = strtolower(trim($_GET['mes_inicio'] ?? ''));
$mesFin    = strtolower(trim($_GET['mes_fin'] ?? ''));

// -------------------------------------------------------------
// 3. Filtro dinámico según rango de meses
// -------------------------------------------------------------
$filtroMes = '';
$params = $procesosUsuario; // siempre primero van los procesos

if ($mesInicio !== '' && $mesFin !== '') {
    $ordenMeses = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];

    $idxInicio = array_search($mesInicio, $ordenMeses);
    $idxFin = array_search($mesFin, $ordenMeses);

    if ($idxInicio !== false && $idxFin !== false) {
        if ($idxInicio > $idxFin) {
            [$idxInicio, $idxFin] = [$idxFin, $idxInicio];
        }

        $rangoMeses = array_slice($ordenMeses, $idxInicio, $idxFin - $idxInicio + 1);
        $placeholdersMeses = implode(',', array_fill(0, count($rangoMeses), '?'));
        $filtroMes = "AND LOWER(r.mes) IN ($placeholdersMeses)";
        $params = array_merge($params, $rangoMeses);
    }
}

// -------------------------------------------------------------
// 4. Consulta SQL: solo procesos asignados al usuario
// -------------------------------------------------------------
$sql = "
SELECT 
    i.id_indicador,
    j.Proceso AS proceso,
    k.Coordinacion AS coordinacion,
    i.codigo,
    i.nombre_indicador,
    i.objetivo,
    i.periodicidad,
    i.fuente_informacion,
    i.meta,
    r.id_result,
    r.mes,
    r.num,
    r.dem,
    r.resultado
FROM dbo.indicadores i
LEFT JOIN dbo.indicadores_resultado r ON i.id_indicador = r.id_idicador
INNER JOIN dbo.Proceso j ON i.idProceso = j.idProceso
INNER JOIN dbo.Coordinacion k ON i.idCoordinacion = k.idCoordinacion
WHERE i.idProceso IN ($placeholdersProcesos)
$filtroMes
ORDER BY i.id_indicador;
";

$stmt = sqlsrv_query($conexion, $sql, $params);
if ($stmt === false) {
    echo "<h3>Error en la consulta.</h3>";
    echo "<pre>" . print_r(sqlsrv_errors(), true) . "</pre>";
    exit;
}

// -------------------------------------------------------------
// 5. Recolectar filas
// -------------------------------------------------------------
$rows = [];
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $rows[] = $r;
}

// -------------------------------------------------------------
// 6. Si no hay registros
// -------------------------------------------------------------
if (count($rows) === 0) {
    ?>
    <!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>No hay datos</title>
      <link rel="stylesheet" href="../../static/css/bootstrap.min.css">
    </head>
    <body class="p-4">
      <div class="container">
        <h3>No se encontraron registros para el rango de meses seleccionado.</h3>
        <a class="btn btn-primary" href="vista_exportar_excel.php">Volver al filtro</a>
      </div>
    </body>
    </html>
    <?php
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conexion);
    exit;
}

// -------------------------------------------------------------
// 7. Crear y rellenar el Excel
// -------------------------------------------------------------
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Indicadores");

// Encabezados
$headers = [
    "ID Indicador", "Proceso", "Coordinación", "Código",
    "Nombre Indicador", "Objetivo", "Periodicidad",
    "Fuente Información", "Meta", "ID Resultado", "Mes", "Num", "Dem", "Resultado"
];

$colLetter = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($colLetter . "1", $header);
    $colLetter++;
}

// Rellenar filas
$rowNum = 2;
foreach ($rows as $row) {
    $colLetter = 'A';
    $values = [
        $row['id_indicador'] ?? '',
        $row['proceso'] ?? '',
        $row['coordinacion'] ?? '',
        $row['codigo'] ?? '',
        $row['nombre_indicador'] ?? '',
        $row['objetivo'] ?? '',
        $row['periodicidad'] ?? '',
        $row['fuente_informacion'] ?? '',
        $row['meta'] ?? '',
        $row['id_result'] ?? '',
        $row['mes'] ?? '',
        $row['num'] ?? '',
        $row['dem'] ?? '',
        $row['resultado'] ?? ''
    ];

    foreach ($values as $v) {
        $sheet->setCellValue($colLetter . $rowNum, $v);
        $colLetter++;
    }
    $rowNum++;
}

// -------------------------------------------------------------
// 8. Estilos del encabezado
// -------------------------------------------------------------
$sheet->getStyle("A1:N1")->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4CAF50']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

foreach (range('A', 'N') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$sheet->setAutoFilter("A1:N1");
$sheet->freezePane("A2");

// -------------------------------------------------------------
// 9. Descargar Excel
// -------------------------------------------------------------
$writer = new Xlsx($spreadsheet);
$filename = "indicadores_" . ($mesInicio ?: 'todos') . "_a_" . ($mesFin ?: 'todos') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"{$filename}\"");
header('Cache-Control: max-age=0');
$writer->save('php://output');

sqlsrv_free_stmt($stmt);
sqlsrv_close($conexion);
exit;
?>
