<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function h($value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
function check_csrf(): void { if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) { http_response_code(403); exit('Solicitud no válida.'); } }
function redirect_saved(string $page): void { header('Location: '.$page.'?guardado=1'); exit; }
function money($value): string { return '$'.number_format((float)$value,2); }
function page_top(string $title): void { ?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= h($title) ?> | Taller Parra</title><link rel="stylesheet" href="assets/css/style.css"></head><body>
<header><div class="brand">⚙ TALLER <span>PARRA</span></div><div class="subtitle">Sistema de administración</div></header>
<nav class="nav"><a href="index.php">Inicio</a><a href="vehiculos.php">Vehículos</a><a href="ordenes.php">Órdenes de trabajo</a><a href="cotizador.php">Cotizador</a><a href="finanzas.php">Ganancias y gastos</a></nav><main><h1><?= h($title) ?></h1><?php if (isset($_GET['guardado'])): ?><p class="ok">Información guardada correctamente.</p><?php endif; ?>
<?php }
function page_bottom(): void { ?></main></body></html><?php }
