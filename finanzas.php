<?php
require __DIR__.'/config/database.php';require __DIR__.'/config/helpers.php';
$error='';
// Verificar que la migración de finanzas esté instalada antes de consultar o registrar cobros.
$tablasRequeridas = ['cotizaciones','gastos_taller','cobros_cotizacion','garantias_cotizacion'];
$tablasFaltantes = [];
foreach ($tablasRequeridas as $tabla) {
    $verificacion = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $verificacion->execute([$tabla]);
    if (!(int)$verificacion->fetchColumn()) $tablasFaltantes[] = $tabla;
}
if ($tablasFaltantes) {
    http_response_code(503);
    page_top('Ganancias y gastos');
    echo '<article class="panel"><h2>Falta actualizar la base de datos</h2>';
    echo '<p>Este apartado necesita las tablas: <strong>'.h(implode(', ', $tablasFaltantes)).'</strong>.</p>';
    echo '<p>Haz una copia de seguridad de la base <strong>taller_parra</strong> desde phpMyAdmin y después importa <strong>migracion_v5.sql</strong>. Si falta cotizaciones, primero importa migracion_v3.sql.</p>';
    echo '<p>No vuelvas a importar database.sql ni borres tus datos.</p></article>';
    page_bottom(); exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf();$tipo=(string)($_POST['tipo']??'');$importe=filter_var($_POST['importe']??null,FILTER_VALIDATE_FLOAT);$fecha=(string)($_POST['fecha']??'');
 $dt=DateTimeImmutable::createFromFormat('!Y-m-d',$fecha);
 if($importe===false || $importe<=0 || $importe>99999999 || !$dt || $dt->format('Y-m-d')!==$fecha){$error='Revisa el importe y la fecha.';}
 elseif($tipo==='gasto'){
  $concepto=trim((string)($_POST['concepto']??''));$cat=(string)($_POST['categoria']??'Otros');
  if($concepto==='' || mb_strlen($concepto)>160 || !in_array($cat,['Luz','Consumibles','Herramientas','Otros'],true))$error='Revisa los datos del gasto.';
  else{$stmt=$pdo->prepare('INSERT INTO gastos_taller(concepto,categoria,importe,fecha) VALUES (?,?,?,?)');$stmt->execute([$concepto,$cat,$importe,$fecha]);redirect_saved('finanzas.php');}
 }elseif($tipo==='cobro'){
  $id=filter_var($_POST['cotizacion_id']??null,FILTER_VALIDATE_INT);$metodo=(string)($_POST['metodo']??'Efectivo');
  $st=$pdo->prepare('SELECT precio_final FROM cotizaciones WHERE id=?');$st->execute([$id]);$precio=$st->fetchColumn();
  $st=$pdo->prepare('SELECT COALESCE(SUM(importe),0) FROM cobros_cotizacion WHERE cotizacion_id=?');$st->execute([$id]);$abonado=(float)$st->fetchColumn();
  if($precio===false || !in_array($metodo,['Efectivo','Transferencia','Tarjeta','Otro'],true) || $importe>(float)$precio-$abonado+0.001)$error='Cobro inválido o mayor que el saldo pendiente.';
  else{$stmt=$pdo->prepare('INSERT INTO cobros_cotizacion(cotizacion_id,importe,metodo,fecha) VALUES (?,?,?,?)');$stmt->execute([$id,$importe,$metodo,$fecha]);redirect_saved('finanzas.php');}
 }else{$error='Operación inválida.';}
}
$mes=(string)($_GET['mes']??date('Y-m'));if(!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$mes))$mes=date('Y-m');
$desde=$mes.'-01';$hasta=(new DateTimeImmutable($desde))->modify('+1 month')->format('Y-m-d');
$sum=function($sql)use($pdo,$desde,$hasta){$st=$pdo->prepare($sql);$st->execute([$desde,$hasta]);return (float)$st->fetchColumn();};
$ingresos=$sum('SELECT COALESCE(SUM(importe),0) FROM cobros_cotizacion WHERE fecha>=? AND fecha<?');
$gastos=$sum('SELECT COALESCE(SUM(importe),0) FROM gastos_taller WHERE fecha>=? AND fecha<?');
// Costos de refacciones y otros se imputan al mes de la cotización; no incluyen gastos registrados aparte.
$costos=$sum('SELECT COALESCE(SUM(costo_refacciones+otros_costos),0) FROM cotizaciones WHERE creado_en>=? AND creado_en<?');
$cotizaciones=$pdo->query('SELECT q.id,q.servicio,q.precio_final,c.nombre cliente,COALESCE(cb.cobrado,0) cobrado FROM cotizaciones q LEFT JOIN vehiculos v ON v.id=q.vehiculo_id LEFT JOIN clientes c ON c.id=v.cliente_id LEFT JOIN (SELECT cotizacion_id,SUM(importe) cobrado FROM cobros_cotizacion GROUP BY cotizacion_id) cb ON cb.cotizacion_id=q.id ORDER BY q.id DESC LIMIT 80')->fetchAll();
$mov=$pdo->prepare('SELECT fecha,concepto,categoria,importe FROM gastos_taller WHERE fecha>=? AND fecha<? ORDER BY fecha DESC,id DESC LIMIT 50');$mov->execute([$desde,$hasta]);$mov=$mov->fetchAll();
page_top('Ganancias y gastos'); ?>
<p class="muted">Los ingresos son cobros registrados, no cotizaciones. El resultado es estimado: resta gastos y costos de refacciones registrados en cotizaciones del mes; los periodos de cobro y reparación pueden ser distintos. Evita registrar la misma compra como gasto y como costo de refacción.</p>
<?php if($error):?><p class="error"><?=h($error)?></p><?php endif;?>
<form method="get" class="inline-form"><label>Mes<input type="month" name="mes" value="<?=h($mes)?>"></label><button>Consultar</button></form>
<section class="stats"><article><small>Cobros recibidos</small><strong><?=money($ingresos)?></strong></article><article><small>Gastos registrados</small><strong><?=money($gastos)?></strong></article><article><small>Costos de trabajos cotizados</small><strong><?=money($costos)?></strong></article><article><small>Resultado estimado</small><strong><?=money($ingresos-$gastos-$costos)?></strong></article></section>
<section class="columns"><article class="panel"><h2>Registrar gasto</h2><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="tipo" value="gasto"><label>Concepto<input name="concepto" required maxlength="160" placeholder="Ej. Pago de luz"></label><label>Categoría<select name="categoria"><option>Luz</option><option>Consumibles</option><option>Herramientas</option><option>Otros</option></select></label><label>Importe ($)<input type="number" name="importe" min="0.01" max="99999999" step="0.01" required></label><label>Fecha<input type="date" name="fecha" value="<?=date('Y-m-d')?>" required></label><button>Guardar gasto</button></form></article>
<article class="panel"><h2>Registrar cobro de cotización</h2><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="tipo" value="cobro"><label>Cotización<select name="cotizacion_id" required><option value="">Seleccionar</option><?php foreach($cotizaciones as $q):$saldo=round((float)$q['precio_final']-(float)$q['cobrado'],2);if($saldo<=0)continue;?><option value="<?=(int)$q['id']?>" <?=((int)($_GET['cotizacion']??0)===(int)$q['id'])?'selected':''?>>COT-<?=str_pad($q['id'],4,'0',STR_PAD_LEFT)?> · <?=h($q['cliente']??'Sin cliente')?> · Pendiente <?=money($saldo)?></option><?php endforeach;?></select></label><label>Importe recibido ($)<input type="number" name="importe" min="0.01" step="0.01" required></label><label>Método<select name="metodo"><option>Efectivo</option><option>Transferencia</option><option>Tarjeta</option><option>Otro</option></select></label><label>Fecha<input type="date" name="fecha" value="<?=date('Y-m-d')?>" required></label><button>Guardar cobro</button></form></article></section>
<section class="columns" style="margin-top:18px"><article class="panel"><h2>Gastos del mes</h2><?php if(!$mov):?><p class="muted">Sin gastos registrados.</p><?php endif;?><?php foreach($mov as $g):?><div class="order"><b><?=h($g['concepto'])?></b><p><?=h($g['fecha'])?> · <?=h($g['categoria'])?> · <?=money($g['importe'])?></p></div><?php endforeach;?></article><article class="panel"><h2>Estado de cobros</h2><?php foreach($cotizaciones as $q):?><div class="order"><b>COT-<?=str_pad($q['id'],4,'0',STR_PAD_LEFT)?> · <?=h($q['servicio'])?></b><p><?=h($q['cliente']??'Sin cliente')?> · Total <?=money($q['precio_final'])?></p><p>Cobrado <?=money($q['cobrado'])?> · Pendiente <?=money(max(0,(float)$q['precio_final']-(float)$q['cobrado']))?></p></div><?php endforeach;?></article></section>
<?php page_bottom(); ?>
