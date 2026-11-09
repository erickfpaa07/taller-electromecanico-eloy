<?php
require __DIR__.'/config/database.php';
require __DIR__.'/config/helpers.php';
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if(!$id || $id<1){http_response_code(400);exit('Folio inválido.');}
$stmt=$pdo->prepare('SELECT q.id,q.servicio,q.precio_final,q.creado_en,v.marca,v.modelo,c.nombre AS cliente,g.dias AS garantia_dias,g.condiciones AS garantia_condiciones FROM cotizaciones q LEFT JOIN vehiculos v ON v.id=q.vehiculo_id LEFT JOIN clientes c ON c.id=v.cliente_id LEFT JOIN garantias_cotizacion g ON g.cotizacion_id=q.id WHERE q.id=?');
$stmt->execute([$id]);$q=$stmt->fetch();
if(!$q){http_response_code(404);exit('Cotización no encontrada.');}
$fecha=date('d/m/Y H:i',strtotime($q['creado_en']));
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Ticket COT-<?=str_pad($q['id'],4,'0',STR_PAD_LEFT)?> | Taller Parra</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#e9edf1;font-family:Arial,sans-serif;color:#111}.toolbar{display:flex;gap:10px;justify-content:center;align-items:center;flex-wrap:wrap;padding:15px}.toolbar button,.toolbar a{border:0;border-radius:7px;padding:11px 17px;background:#152f4b;color:white;text-decoration:none;cursor:pointer;font-size:14px}.toolbar label{font-size:14px}.paper{background:#fff;width:80mm;margin:10px auto 25px;padding:5mm 4mm;box-shadow:0 3px 16px #0002;font-family:Arial,sans-serif;font-size:12px;overflow-wrap:anywhere}.paper h1{font-size:16px;line-height:1.3;text-align:center;margin:0 0 5px}.center{text-align:center}.small{font-size:11px}.line{border-top:1px dashed #222;margin:10px 0}.detail{margin:6px 0;line-height:1.5}.total{display:flex;justify-content:space-between;gap:6px;font-weight:bold;font-size:17px;margin:12px 0}.foot{text-align:center;font-size:11px;line-height:1.5}.paper[data-size="58"]{width:58mm;padding:4mm 3mm;font-size:11px}.paper[data-size="58"] h1{font-size:13px}.paper[data-size="58"] .total{font-size:14px}
@media print{@page{size:80mm auto;margin:0}html,body{width:auto;margin:0;padding:0;background:white}.toolbar{display:none!important}.paper,.paper[data-size="58"]{box-shadow:none;margin:0;width:100%;padding:3mm;break-inside:avoid}body{print-color-adjust:exact;-webkit-print-color-adjust:exact}}
</style></head><body>
<div class="toolbar"><a href="cotizador.php">Volver al cotizador</a><label for="tam">Ancho:</label><select id="tam"><option value="80">80 mm</option><option value="58">58 mm</option></select><button type="button" onclick="window.print()">Imprimir ticket</button></div>
<section class="paper" id="ticket" data-size="80">
<h1>TALLER<br>ELECTROMECÁNICO PARRA</h1><div class="center small">COTIZACIÓN DE SERVICIO</div><div class="line"></div>
<div class="detail"><b>Folio:</b> COT-<?=str_pad($q['id'],4,'0',STR_PAD_LEFT)?><br><b>Fecha:</b> <?=h($fecha)?></div>
<?php if(!empty($q['cliente'])): ?><div class="detail"><b>Cliente:</b> <?=h($q['cliente'])?></div><?php endif; ?>
<?php if(!empty($q['marca']) || !empty($q['modelo'])): ?><div class="detail"><b>Vehículo:</b> <?=h(trim(($q['marca']??'').' '.($q['modelo']??'')))?></div><?php endif; ?>
<div class="line"></div><div class="detail"><b>Servicio:</b><br><?=h($q['servicio'])?></div><div class="small">Precio total de reparación (incluye mano de obra y refacciones aplicables).</div><div class="line"></div>
<div class="total"><span>TOTAL</span><span><?=h(money($q['precio_final']))?> MXN</span></div><div class="line"></div>
<?php if((int)($q['garantia_dias']??0)>0): ?><div class="detail"><b>Garantía:</b> <?= (int)$q['garantia_dias'] ?> días a partir de la entrega del trabajo.<br><?=h($q['garantia_condiciones'] ?: 'Aplica únicamente al trabajo realizado.')?></div><div class="line"></div><?php else: ?><div class="detail small">Garantía: no especificada.</div><?php endif; ?>
<div class="foot">Cotización sujeta a revisión y aceptación.<br>¡Gracias por su preferencia!</div>
</section>
<script>
const select=document.getElementById('tam'),paper=document.getElementById('ticket');
select.addEventListener('change',()=>{paper.dataset.size=select.value;});
window.addEventListener('beforeprint',()=>{let old=document.getElementById('printSize');if(old)old.remove();const s=document.createElement('style');s.id='printSize';s.textContent='@media print{@page{size:'+select.value+'mm auto;margin:0}html,body{width:'+select.value+'mm}.paper{width:'+select.value+'mm!important}}';document.head.appendChild(s);});
</script></body></html>
