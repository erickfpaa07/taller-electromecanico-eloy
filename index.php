<?php
session_start();
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
require __DIR__ . '/config/database.php';
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(403); exit('Solicitud no válida'); }
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    if ($nombre === '' || mb_strlen($nombre) > 150 || mb_strlen($telefono) > 25) {
        $mensaje = 'Revisa el nombre y teléfono.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO clientes (nombre, telefono) VALUES (?, ?)');
        $stmt->execute([$nombre, $telefono]);
        header('Location: index.php?guardado=1'); exit;
    }
}
$clientes = $pdo->query('SELECT id, nombre, telefono FROM clientes ORDER BY id DESC LIMIT 10')->fetchAll();
$stats = $pdo->query("SELECT COUNT(*) AS total, COALESCE(SUM(estado='Pendiente'),0) AS pendientes, COALESCE(SUM(estado='En proceso'),0) AS proceso, COALESCE(SUM(estado='Terminado'),0) AS terminados FROM ordenes")->fetch();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Taller Electromecánico Parra</title><link rel="stylesheet" href="assets/css/style.css"></head><body>
<header><div class="brand">⚙ TALLER <span>PARRA</span></div><div class="subtitle">Sistema de administración</div></header>
<nav class="nav"><a href="index.php">Inicio</a><a href="vehiculos.php">Vehículos</a><a href="ordenes.php">Órdenes de trabajo</a><a href="cotizador.php">Cotizador</a><a href="finanzas.php">Ganancias y gastos</a></nav><main><h1>Panel principal</h1><p class="muted">Control de servicios y clientes del taller</p>
<section class="stats"><article><small>Órdenes totales</small><strong><?= (int)$stats['total'] ?></strong></article><article><small>Pendientes</small><strong><?= (int)$stats['pendientes'] ?></strong></article><article><small>En proceso</small><strong><?= (int)$stats['proceso'] ?></strong></article><article><small>Terminadas</small><strong><?= (int)$stats['terminados'] ?></strong></article></section>
<section class="columns"><article class="panel"><h2>Registrar cliente</h2><?php if ($mensaje): ?><p class="error"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?><?php if (isset($_GET['guardado'])): ?><p class="ok">Cliente guardado correctamente.</p><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>"><label>Nombre completo<input name="nombre" maxlength="150" required placeholder="Nombre del cliente"></label><label>Teléfono<input name="telefono" type="tel" maxlength="25" placeholder="Número de contacto"></label><button type="submit">Guardar cliente</button></form></article>
<article class="panel"><h2>Clientes recientes</h2><?php if (!$clientes): ?><p class="muted">Todavía no hay clientes registrados.</p><?php else: ?><div class="clients"><?php foreach ($clientes as $c): ?><div class="client"><b><?= htmlspecialchars($c['nombre']) ?></b><span><?= htmlspecialchars($c['telefono'] ?: 'Sin teléfono') ?></span></div><?php endforeach; ?></div><?php endif; ?></article></section>
<p class="footnote">Clientes, vehículos, órdenes, cotizaciones, cobros, gastos y garantías.</p></main></body></html>
