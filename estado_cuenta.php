<?php

$transacciones = [];

function registrarTransaccion($id, $descripcion, $monto)
{
    global $transacciones;

    $transacciones[] = [
        "id" => $id,
        "descripcion" => $descripcion,
        "monto" => $monto
    ];
}

// Solo aceptamos datos por POST
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
} else {
    // Si entran sin POST, simplemente mostramos un mensaje sencillo
    echo "<p style='padding:10px'>No se han enviado datos. Vuelva al formulario.</p>";
    exit;
}

// Variables de resultados
$totalContado    = 0;
$totalConInteres = 0;
$cashback        = 0;
$montoFinal      = 0;

// Solo calculamos si hay transacciones
if (!empty($transacciones)) {

    foreach ($transacciones as $t) {
        $totalContado += $t["monto"];
    }

    $interes            = 0.026;  // 2.6%
    $cashbackPorcentaje = 0.001;  // 0.1%

    $totalConInteres = $totalContado * (1 + $interes);
    $cashback        = $totalContado * $cashbackPorcentaje;
    $montoFinal      = $totalConInteres - $cashback;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resultados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          crossorigin="anonymous">

    <link rel="stylesheet" href="assetes/css/estilo.css">
</head>

<body style="background-color: #ffffff;">
<div class="p-3">

    <?php if (empty($transacciones)) : ?>

        <div class="alert alert-warning">
            No se ingresaron transacciones válidas. Por favor, vuelva al formulario.
        </div>

    <?php else : ?>

        <div class="row">
            <!-- Columna izquierda: tabla de transacciones -->
            <div class="col-12 mb-3">
                <h6 class="mb-2">Transacciones ingresadas</h6>

                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Descripción</th>
                            <th>Monto (₡)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($transacciones as $t) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t["id"]); ?></td>
                                <td><?php echo htmlspecialchars($t["descripcion"]); ?></td>
                                <td><?php echo number_format($t["monto"], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Columna derecha: resumen de resultados (visual dentro del iframe) -->
            <div class="col-12">
                <h6 class="mb-2">Resumen de resultados</h6>
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
                    <p class="fs-5 fw-bold text-success">₡<?php echo number_format($montoFinal, 2); ?></p>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>
</body>

</html>
