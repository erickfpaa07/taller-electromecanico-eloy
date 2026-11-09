<?php
require __DIR__.'/config/database.php'; require __DIR__.'/config/helpers.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 check_csrf();
 $cliente=filter_var($_POST['cliente_id']??'', FILTER_VALIDATE_INT);
 $marca=trim($_POST['marca']??''); $modelo=trim($_POST['modelo']??'');
 if (!$cliente || $marca==='' || $modelo==='' || mb_strlen($marca)>80 || mb_strlen($modelo)>80) $error='Revisa los datos del vehículo.';
 else {
  $exists=$pdo->prepare('SELECT COUNT(*) FROM clientes WHERE id=?'); $exists->execute([$cliente]);
  if (!$exists->fetchColumn()) $error='Selecciona un cliente válido.';
  else { $stmt=$pdo->prepare('INSERT INTO vehiculos (cliente_id,marca,modelo) VALUES (?,?,?)'); $stmt->execute([$cliente,$marca,$modelo]); redirect_saved('vehiculos.php'); }
 }
}
$clientes=$pdo->query('SELECT id,nombre FROM clientes ORDER BY nombre')->fetchAll();
$vehiculos=$pdo->query('SELECT v.*,c.nombre AS cliente FROM vehiculos v JOIN clientes c ON c.id=v.cliente_id ORDER BY v.id DESC')->fetchAll();
page_top('Vehículos'); ?>
<p class="muted">Registra automóviles y camionetas de los clientes.</p><section class="columns"><article class="panel"><h2>Registrar vehículo</h2>
<?php if($error): ?><p class="error"><?=h($error)?></p><?php endif; ?>
<?php if(!$clientes): ?><p>Primero registra un cliente desde <a href="index.php">Inicio</a>.</p><?php else: ?>
<form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><label>Propietario<select name="cliente_id" required><option value="">Seleccionar cliente</option><?php foreach($clientes as $c): ?><option value="<?= (int)$c['id'] ?>"><?=h($c['nombre'])?></option><?php endforeach; ?></select></label><label>Marca<input name="marca" maxlength="80" required placeholder="Ej. Nissan"></label><label>Modelo<input name="modelo" maxlength="80" required placeholder="Ej. Tsuru"></label><button>Guardar vehículo</button></form><?php endif; ?></article>
<article class="panel"><h2>Vehículos registrados</h2><?php if(!$vehiculos): ?><p class="muted">Aún no hay vehículos.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Vehículo</th><th>Cliente</th><th>Acción</th></tr></thead><tbody><?php foreach($vehiculos as $v): ?><tr><td><?=h($v['marca'].' '.$v['modelo'])?></td><td><?=h($v['cliente'])?></td><td><a href="ordenes.php?vehiculo_id=<?=(int)$v['id']?>">Crear orden</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></article></section><?php page_bottom(); ?>
