<?php
// ====================================
//   LÓGICA PHP: leer POST y calcular
// ====================================

// Arreglo de transacciones
$transacciones = [];

// Variables de resultados
$totalContado    = 0;
$totalConInteres = 0;
$cashback        = 0;
$montoFinal      = 0;
$hayResultados   = false;

function registrarTransaccion($id, $descripcion, $monto)
{
    global $transacciones;

    $transacciones[] = [
        "id" => $id,
        "descripcion" => $descripcion,
        "monto" => $monto
    ];
}

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ids           = isset($_POST["id"]) ? $_POST["id"] : [];
    $descripciones = isset($_POST["descripcion"]) ? $_POST["descripcion"] : [];
    $montos        = isset($_POST["monto"]) ? $_POST["monto"] : [];

    for ($i = 0; $i < count($descripciones); $i++) {

        $id          = isset($ids[$i]) ? intval($ids[$i]) : 0;
        $descripcion = trim($descripciones[$i] ?? "");
        $montoTexto  = trim($montos[$i] ?? "");

        // Si la fila está vacía, la ignoramos
        if ($descripcion === "" || $montoTexto === "") {
            continue;
        }

        $monto = floatval($montoTexto);

        if ($id <= 0) {
            $id = $i + 1;
        }

        registrarTransaccion($id, $descripcion, $monto);
    }

    if (!empty($transacciones)) {
        // Calcular totales
        foreach ($transacciones as $t) {
            $totalContado += $t["monto"];
        }

        $interes            = 0.026; // 2.6%
        $cashbackPorcentaje = 0.001; // 0.1%

        $totalConInteres = $totalContado * (1 + $interes);
        $cashback        = $totalContado * $cashbackPorcentaje;
        $montoFinal      = $totalConInteres - $cashback;

        $hayResultados = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Estado de Cuenta | Tarjeta de Crédito</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          crossorigin="anonymous">

    <!-- Tu CSS -->
    <link rel="stylesheet" href="ASSETS/CSS/estilo.css" />
</head>

<body>
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Estado de Cuenta - Ingreso de Transacciones</h4>
        </div>

        <div class="card-body">
            <p class="mb-3">
                Ingrese las transacciones realizadas con la tarjeta de crédito.
                Puede dejar filas en blanco si no las necesita.
                Al presionar <strong>Imprimir resultados</strong>, el sistema calculará:
                <strong>monto de contado, monto con interés (2.6%), cashback (0.1%) y monto final a pagar</strong>
                y mostrará los resultados en la columna <strong>RESULTADOS</strong>.
            </p>

            <div class="row">
                <!-- ========================= -->
                <!--  COLUMNA IZQUIERDA: FORM  -->
                <!-- ========================= -->
                <div class="col-md-6 mb-3">
                    <h5 class="mb-3">Transacciones</h5>

                    <!-- Importante: action vacío -> se envía a la MISMA página -->
                    <form action="" method="post">

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th style="width: 15%;">ID</th>
                                    <th style="width: 50%;">Descripción</th>
                                    <th style="width: 35%;">Monto (₡)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                // Pintamos 5 filas siempre
                                for ($i = 0; $i < 5; $i++): ?>
                                    <tr>
                                        <td>
                                            <input type="number" name="id[]" class="form-control" min="1" />
                                        </td>
                                        <td>
                                            <input type="text" name="descripcion[]" class="form-control" />
                                        </td>
                                        <td>
                                            <input type="number" name="monto[]" class="form-control" step="0.01" min="0" />
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                Imprimir resultados
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========================= -->
                <!--  COLUMNA DERECHA: RESULTADOS -->
                <!-- ========================= -->
                <div class="col-md-6 mb-3">
                    <h5 class="text-center mb-3">RESULTADOS</h5>

                    <?php if (!$hayResultados): ?>
                        <div class="alert alert-info">
                            No hay resultados todavía. Ingrese transacciones y presione
                            <strong>Imprimir resultados</strong>.
                        </div>
                    <?php else: ?>

                        <!-- Tabla de transacciones -->
                        <h6 class="mb-2">Transacciones ingresadas</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-striped table-hover align-middle">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Descripción</th>
                                    <th>Monto (₡)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($transacciones as $t): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($t["id"]); ?></td>
                                        <td><?php echo htmlspecialchars($t["descripcion"]); ?></td>
                                        <td><?php echo number_format($t["monto"], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Resumen de resultados -->
                        <h6 class="mb-2">Resumen de cálculos</h6>
                        <div class="border rounded p-3 bg-light">
                            <p class="mb-1">Monto total de contado:</p>
                            <p><strong>₡<?php echo number_format($totalContado, 2); ?></strong></p>

                            <hr class="my-2">

                            <p class="mb-1">Monto total con interés (2.6%):</p>
                            <p><strong>₡<?php echo number_format($totalConInteres, 2); ?></strong></p>

                            <p class="mt-3 mb-1">Cashback (0.1% del monto de contado):</p>
                            <p><strong>₡<?php echo number_format($cashback, 2); ?></strong></p>

                            <hr class="my-2">

                            <p class="fw-bold mb-1">Monto final a pagar:</p>
                            <p class="fs-5 fw-bold text-success">
                                ₡<?php echo number_format($montoFinal, 2); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

<!-- Tu JS -->
<script src="ASSETS/JS/main.js"></script>
</body>

</html>
