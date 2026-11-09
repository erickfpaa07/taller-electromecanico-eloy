<?php
require __DIR__.'/config/database.php'; require __DIR__.'/config/helpers.php';
$servicios = ['Alternador'=>2.0,'Marcha / motor de arranque'=>2.0,'Cableado'=>3.0,'Diagnóstico eléctrico'=>1.0,'Otro'=>1.0];
$factores = ['Sencilla'=>0.75,'Normal'=>1.0,'Complicada'=>1.5];
$err=''; $ok=false;
$vehiculos=$pdo->query('SELECT v.id,v.marca,v.modelo,c.nombre FROM vehiculos v JOIN clientes c ON c.id=v.cliente_id ORDER BY v.id DESC')->fetchAll();
$datos=['servicio'=>'Alternador','dificultad'=>'Normal','vehiculo_id'=>'','costo_refacciones'=>'0','otros_costos'=>'0','tarifa_hora'=>'175','margen_refacciones'=>'0','precio_final'=>'','notas'=>'','garantia_dias'=>'0','garantia_condiciones'=>''];
if ($_SERVER['REQUEST_METHOD']==='POST') {
 check_csrf(); foreach($datos as $k=>$v) $datos[$k]=trim((string)($_POST[$k]??$v));
 if(!isset($servicios[$datos['servicio']]) || !isset($factores[$datos['dificultad']])) $err='Selecciona un servicio y dificultad válidos.';
 foreach(['costo_refacciones','otros_costos','tarifa_hora','margen_refacciones'] as $k) if(!is_numeric($datos[$k]) || (float)$datos[$k]<0 || (float)$datos[$k]>999999) $err='Revisa los importes.';
 if((float)$datos['margen_refacciones']>=100) $err='El margen debe ser menor a 100%.';
 if(!ctype_digit($datos['garantia_dias']) || (int)$datos['garantia_dias']>3650) $err='Garantía inválida.';
 if(mb_strlen($datos['garantia_condiciones'])>500) $err='Condiciones de garantía demasiado largas.';
 if(strlen($datos['notas'])>5000) $err='Las notas son demasiado largas.';
 $vehiculo=$datos['vehiculo_id']===''?null:filter_var($datos['vehiculo_id'],FILTER_VALIDATE_INT);
 if($datos['vehiculo_id']!=='' && !$vehiculo) $err='Vehículo no válido.';
 if($vehiculo) { $q=$pdo->prepare('SELECT COUNT(*) FROM vehiculos WHERE id=?');$q->execute([$vehiculo]);if(!$q->fetchColumn())$err='Vehículo no encontrado.'; }
 if(!$err) {
  $horas=$servicios[$datos['servicio']]*$factores[$datos['dificultad']];
  $sugerido=round($horas*(float)$datos['tarifa_hora']+(float)$datos['costo_refacciones']/(1-(float)$datos['margen_refacciones']/100)+(float)$datos['otros_costos'],2);
  $final=$datos['precio_final']===''?$sugerido:(is_numeric($datos['precio_final'])?(float)$datos['precio_final']:-1);
  if($final<0 || $final>99999999) $err='Precio final inválido.';
  else { $pdo->beginTransaction(); $q=$pdo->prepare('INSERT INTO cotizaciones (vehiculo_id,servicio,dificultad,horas_estimadas,costo_refacciones,otros_costos,tarifa_hora,margen_refacciones,precio_sugerido,precio_final,notas) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
   $q->execute([$vehiculo,$datos['servicio'],$datos['dificultad'],$horas,$datos['costo_refacciones'],$datos['otros_costos'],$datos['tarifa_hora'],$datos['margen_refacciones'],$sugerido,$final,$datos['notas']]);  $nuevoId=(int)$pdo->lastInsertId(); $g=$pdo->prepare('INSERT INTO garantias_cotizacion (cotizacion_id,dias,condiciones) VALUES (?,?,?)'); $g->execute([$nuevoId,(int)$datos['garantia_dias'],$datos['garantia_condiciones']]); $pdo->commit(); header('Location: '.(($_POST['accion']??'')==='imprimir'?'ticket.php?id='.$nuevoId:'cotizador.php?guardado=1')); exit; }
 }
}
try { $historial=$pdo->query('SELECT q.*,v.marca,v.modelo,c.nombre cliente,c.telefono telefono FROM cotizaciones q LEFT JOIN vehiculos v ON v.id=q.vehiculo_id LEFT JOIN clientes c ON c.id=v.cliente_id ORDER BY q.id DESC LIMIT 30')->fetchAll(); }
catch(PDOException $e) { http_response_code(500);exit('Primero importa migracion_v3.sql en phpMyAdmin.'); }
page_top('Cotizador de reparaciones'); ?>
<p class="muted">El cliente recibe un solo precio. Los tiempos iniciales son supuestos editables en el código, no tarifas oficiales. Importes antes de impuestos.</p>
<section class="columns"><article class="panel"><h2>Nueva cotización</h2><?php if($err):?><p class="error"><?=h($err)?></p><?php endif;?>
<form method="post" id="cotizar"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>">
<label>Vehículo (opcional)<select name="vehiculo_id"><option value="">Sin vehículo asociado</option><?php foreach($vehiculos as $v):?><option value="<?=$v['id']?>" <?=$datos['vehiculo_id']==$v['id']?'selected':''?>><?=h($v['marca'].' '.$v['modelo'].' — '.$v['nombre'])?></option><?php endforeach;?></select></label>
<label>Tipo de trabajo<select name="servicio" id="servicio"><?php foreach($servicios as $s=>$h):?><option value="<?=h($s)?>" <?=$datos['servicio']===$s?'selected':''?>><?=h($s)?></option><?php endforeach;?></select></label>
<label>Dificultad<select name="dificultad" id="dificultad"><?php foreach($factores as $s=>$f):?><option value="<?=h($s)?>" <?=$datos['dificultad']===$s?'selected':''?>><?=h($s)?></option><?php endforeach;?></select></label>
<label>Costo real de refacciones ($)<input id="ref" type="number" name="costo_refacciones" min="0" step="0.01" required value="<?=h($datos['costo_refacciones'])?>"></label>
<label>Otros costos específicos de este trabajo ($)<input id="otros" type="number" name="otros_costos" min="0" step="0.01" required value="<?=h($datos['otros_costos'])?>"></label>
<label>Tarifa por hora ($)<input id="tarifa" type="number" name="tarifa_hora" min="0" step="0.01" required value="<?=h($datos['tarifa_hora'])?>"></label>
<label>Margen sobre precio de venta de refacciones (%)<input id="margen" type="number" name="margen_refacciones" min="0" max="99" step="0.01" required value="<?=h($datos['margen_refacciones'])?>"></label>
<div class="quote-result"><p>Horas estimadas: <b id="horas">—</b></p><p>Mano de obra estimada: <b id="mano">—</b></p><p>Precio sugerido: <strong id="sugerido">—</strong></p><p class="muted">Incluye refacciones, mano de obra y costos específicos. La tarifa por hora ya contempla gastos generales estimados.</p><p id="alerta" class="error" style="display:none">Atención: el precio final está por debajo del sugerido.</p></div>
<label>Precio final único al cliente ($) — opcional, vacío usa sugerido<input id="final" type="number" name="precio_final" min="0" step="0.01" placeholder="Usar precio sugerido" value="<?=h($datos['precio_final'])?>"></label>
<label>Garantía (días, 0 = sin garantía)<input type="number" name="garantia_dias" min="0" max="3650" step="1" required value="<?=h($datos['garantia_dias'])?>"></label>
<label>Condiciones de garantía (visibles en ticket)<textarea name="garantia_condiciones" maxlength="500" placeholder="Ej. Aplica únicamente a la reparación realizada"><?=h($datos['garantia_condiciones'])?></textarea></label>
<label>Notas internas<textarea name="notas" maxlength="5000" placeholder="Ej. Se cambiaron carbones y regulador"><?=h($datos['notas'])?></textarea></label>
<button type="submit" name="accion" value="imprimir">Guardar e imprimir ticket</button> <button type="submit" name="accion" value="guardar" class="secondary">Solo guardar</button></form></article>
<article class="panel"><h2>Últimas cotizaciones</h2><?php if(!$historial):?><p class="muted">Todavía no hay cotizaciones.</p><?php endif;?><?php foreach($historial as $q):?><div class="order"><b>COT-<?=str_pad($q['id'],4,'0',STR_PAD_LEFT)?> · <?=h($q['servicio'])?></b><p><?=h(($q['cliente']??'Sin cliente').' · '.($q['marca']??'').' '.($q['modelo']??''))?></p><p><strong>Precio al cliente:</strong> <?=money($q['precio_final'])?></p><?php if(!empty($q['telefono'])): $telefono=preg_replace('/\D+/', '', $q['telefono']); if(strlen($telefono)===10)$telefono='52'.$telefono; $mensaje='Hola '.$q['cliente'].', le informamos que su cotización COT-'.str_pad($q['id'],4,'0',STR_PAD_LEFT).' del Taller Electromecánico Parra es de '.money($q['precio_final']).' MXN. Servicio: '.$q['servicio'].'.'; ?><p><a target="_blank" rel="noopener noreferrer" href="https://wa.me/<?=h($telefono)?>?text=<?=rawurlencode($mensaje)?>">Enviar aviso por WhatsApp</a></p><?php endif; ?>
<p><a href="ticket.php?id=<?=(int)$q['id']?>" target="_blank" rel="noopener">🖨 Imprimir / reimprimir ticket</a></p><p><a href="finanzas.php?cotizacion=<?=(int)$q['id']?>">Registrar cobro</a></p><p class="muted">Estimado: <?=money($q['precio_sugerido'])?> · <?=h($q['dificultad'])?> · <?=h($q['creado_en'])?></p></div><?php endforeach;?></article></section>
<script>
const horasBase=<?=json_encode($servicios,JSON_UNESCAPED_UNICODE)?>, factores=<?=json_encode($factores,JSON_UNESCAPED_UNICODE)?>;
const $=id=>document.getElementById(id), number=id=>Math.max(0,Number($(id).value)||0), mx=n=>n.toLocaleString('es-MX',{style:'currency',currency:'MXN'});
function calc(){const h=horasBase[$('servicio').value]*factores[$('dificultad').value], mano=h*number('tarifa'), margen=Math.min(99,number('margen'))/100, total=mano+number('ref')/(1-margen)+number('otros');$('horas').textContent=h.toFixed(2)+' h';$('mano').textContent=mx(mano);$('sugerido').textContent=mx(total);$('alerta').style.display=$('final').value!==''&&Number($('final').value)<total-.005?'block':'none';}
['servicio','dificultad','tarifa','ref','otros','margen','final'].forEach(id=>$(id).addEventListener('input',calc));calc();
</script><?php page_bottom(); ?>