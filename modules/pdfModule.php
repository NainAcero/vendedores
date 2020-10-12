 <?php 
 $fecha = date('d/m/Y');
 
 ?>
 <style>
  table {
    font-size: 12px;
  }
 </style>
 <page> 
  <page_header> 
    Carritow - <?php echo 'Compra #'.$d->order_number; ?>
  </page_header> 
  <page_footer> 
    www.carritow.com
  </page_footer> 

  <!-- Información del comprador -->
  <br><br>
  <table>
    <tr>
      <td width="50%">
        <table width="100%">
          <tr><td style="font-size: 10px; color: red;">Facturado a</td></tr>
          <tr><td><?php echo $d->client->name; ?></td></tr>
          <tr><td><?php echo $d->client->email; ?></td></tr>
          <tr><td><?php echo '#'.$d->order_number; ?></td></tr>
          <tr><td><?php echo $fecha; ?></td></tr>
        </table>
      </td>
      <td width="50%">
        <table width="100%">
          <tr><td style="font-size: 10px; color: red;">Empresa</td></tr>
          <tr><td>Carritow SA. de CV.</td></tr>
          <tr><td>ventas@carritow.com</td></tr>
          <tr><td>+(55) 22 5878 9695</td></tr>
          <tr><td>CAXX004519479</td></tr>
        </table>
      </td>
    </tr>    
  </table>

  <!-- Resumen de compra -->
  <br>
  <table style="width: 100%;">
    <thead>
      <tr>
        <th>Producto</th>
        <th align="center">Cantidad</th>
        <th align="right">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($d->products as $p): ?>
      <tr style="border: 1px solid grey !important;">
        <td class="align-middle" width="50%">
          <?php echo $p->nombre; ?><br>
          <small class="d-block text-muted"><?php echo 'SKU '.$p->sku ?></small>
        </td>
        <td class="align-middle text-center" align="center" width="25%"><?php echo $p->cantidad; ?></td>
        <td class="align-middle" align="right" width="25%"><?php echo format_currency(floatval($p->cantidad * $p->precio)) ?></td>
      </tr>
      <?php endforeach; ?>
      <!-- Totales de compra -->
      <tr>
        <td class="align-middle" align="left" colspan="1">Subtotal</td>
        <td class="align-middle" align="right" colspan="2"><?php echo  format_currency($d->cart_totals->subtotal)?></td>
      </tr>
      <tr>
        <td class="align-middle" align="left" colspan="1">Envío</td>
        <td class="align-middle" align="right" colspan="2"><?php echo  format_currency($d->cart_totals->shipment)?></td>
      </tr>
      <tr>
        <td class="align-middle" align="left" colspan="1">Total</td>
        <td class="align-middle" align="right" colspan="2"><b><?php echo  format_currency($d->cart_totals->total)?></b></td>
      </tr>
      <tr>
        <td class="align-middle" align="left" colspan="1">Forma de pago</td>
        <td class="align-middle" align="right" colspan="2"><?php echo 'Tarjeta de débito '.substr($d->client->number,-4) ?></td>
      </tr>
      <tr>
        <td class="align-middle" align="left" colspan="1">Estado del pago</td>
        <td class="align-middle" align="right" colspan="2">Aprobado</td>
      </tr>
    </tbody>
  </table>
  <h4>¡Gracias por tu compra!</h4>
 </page> 