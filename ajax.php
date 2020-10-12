<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Spipu\Html2Pdf\Html2Pdf;

require_once 'app/config.php';
require_once 'app/vendor/autoload.php';
// Función para sacar un json en pantalla
//echo json_encode($response);

// Qué tipo de petición está solicitando ajax
if(!isset($_POST['action'])) {
  json_output(403);
}

$action = $_POST['action'];

// GET
switch ($action) {
  case 'get':
    $cart = get_cart();
    $output = '';
      if(!empty($cart['products'])) {
        $output .= '
        <div class="table-responsive">
          <table class="table table-hover table-striped table-sm">
            <thead>
              <tr>
                <th>Producto</th>
                <th class="text-center">Precio</th>
                <th class="text-center">Cantidad</th>
                <th class="text-right">Total</th>
                <th class="text-right"></th>
              </tr>
            </thead>
            <tbody>';
            foreach ($cart['products'] as $p) {
              $output .= 
              '<tr>
                <td class="align-middle" width="25%">
                  <span class="d-block text-truncate">'.$p['nombre'].'</span>
                  <small class="d-block text-muted">SKU '.$p['sku'].'</small>
                </td>
                <td class="align-middle text-center">'.format_currency($p['precio']).'</td>
                <td class="align-middle text-center" width="5%">
                  <input data-id="'.$p['id'].'" data-cantidad="'.$p['cantidad'].'" type="text" class="form-control form-control-sm text-center do_update_cart" value="'.$p['cantidad'].'">
                </td>
                <td class="align-middle text-right">'.format_currency(floatval($p['cantidad'] * $p['precio'])).'</td>
                <td class="text-right align-middle">
                  <button class="btn btn-sm btn-danger do_delete_from_cart" data-id="'.$p['id'].'">
                  <i class="fas fa-times"></i>
                  </button>
                </td>
              </tr>';
            }
            $output .= '</tbody>
          </table>
        </div>
        <button class="btn btn-sm btn-danger do_destroy_cart">Vaciar carrito</button>';
      } else {
        $output .= '
        <div class="text-center py-5">
          <img src="'.IMAGES.'empty-cart.png'.'" title="No hay productos" class="img-fluid mb-3" style="width: 80px;">
          <p class="text-muted">No hay productos en el carrito</p>
        </div>';
      }
      $output .= 
      '<br><br>
      <!-- END Cart content -->
      
      <!-- Cart totals -->
      <table class="table">
        <tr>
          <th class="border-0">Subtotal</th>
          <td class="text-success text-right border-0">'.format_currency($cart['cart_totals']['subtotal']).'</td>
        </tr>
        <tr>
          <th>Envío</th>
          <td class="text-success text-right">'.format_currency($cart['cart_totals']['shipment']).'</td>
        </tr>
        <tr>
          <th>Total</th>
          <td class="text-success text-right"><h3 class="font-weight-bold">'.format_currency($cart['cart_totals']['total']).'</h3></td>
        </tr>
      </table>
      <!-- END Cart totals -->

      <!-- Payment form -->
      <form id="do_pay">
        <h4>Completa el formulario</h4>
        <div class="form-group">
          <label for="card_name">Nombre del titular</label>
          <input type="text" id="card_name" class="form-control" name="card_name" placeholder="John Doe">
        </div>
        <div class="form-group row">
          <div class="col-xl-6">
            <label for="card_number">Número en la tarjeta</label>
            <input type="text" id="card_number" class="form-control" name="card_number" placeholder="5755 6258 4875 6895">
          </div>
          <div class="col-xl-3">
            <label for="card_date">MM/AA</label>
            <input type="text" id="card_date" class="form-control" name="card_date" placeholder="12/24">
          </div>
          <div class="col-xl-3">
            <label for="card_cvc">CVC</label>
            <input type="text" id="card_cvc" class="form-control" name="card_cvc" placeholder="568">
          </div>
        </div>
        <div class="form-group">
          <label for="card_email">E-mail</label>
          <input type="email" id="card_email" class="form-control" name="card_email" placeholder="jslocal@localhost.com">
        </div>
        <button type="submit" class="mt-4 btn btn-info btn-lg btn-block"><b>Pagar ahora</b></button>
      </form>
      <!-- END Payment form -->';

    json_output(200, 'OK' , $output);
    break;

  // Agregar al carrito
  case 'post':
    if(!isset($_POST['id'],$_POST['cantidad'])) {
      json_output(403);
    }

    if(!add_to_cart((int)$_POST['id'] , (int)$_POST['cantidad'])) {
      json_output(400,'No se pudo agregar al carrito, intenta de nuevo');
    }

    json_output(201);
    break;
  
  case 'put':
    if(!isset($_POST['id'],$_POST['cantidad'])) {
      json_output(403);
    }

    if(!update_cart_product((int) $_POST['id'] , (int) $_POST['cantidad'])) {
      json_output(400,'No se pudo actualizar el producto, intenta de nuevo');
    }

    json_output(200);
    break;
  
  case 'destroy':
    if(!destroy_cart()) {
      json_output(400,'No se pudo destruir el carrito, intenta de nuevo');
    }

    json_output(200);
    break;

  case 'delete':
    if(!isset($_POST['id'])) {
      json_output(403);
    }

    if(!delete_from_cart((int)$_POST['id'])) {
      json_output(400,'No se pudo borrar el producto del carrito, intenta de nuevo');
    }

    json_output(200);
    break;
  
  case 'pay':
    // Verificar que haya un carrito existente
    $cart = get_cart();
    if(empty($cart['products'])) {
      json_output(400,'Tu carrito no tiene productos');
    }

    parse_str($_POST['data'],$_POST);
    if(!isset(
      $_POST['card_name'],
      $_POST['card_number'],
      $_POST['card_date'],
      $_POST['card_cvc'],
      $_POST['card_email']
    )) {
      json_output(400,'Completa todos los campos por favor e intenta de nuevo');
    }

    // Tarjeta falsa, debe coincidir la información que mande el usuario
    // con esta, para decir que es un pago aprobado
    $card =
    [
      'number' => '5755625848756895',
      'month'  => '12',
      'year'   => '24',
      'cvc'    => '568'
    ];

    // Validación del correo electrónico
    if(!filter_var($_POST['card_email'],FILTER_VALIDATE_EMAIL)) {
      json_output(400,'Ingresa una dirección de correo válida por favor e intenta de nuevo');
    }

    $card['email'] = $_POST['card_email'];
    $card['name']  = clean_string($_POST['card_name']);

    $errors = 0;
    // Validación del número de tarjeta
    if(clean_string(str_replace(' ','',$_POST['card_number'])) !== $card['number']) {
      $errors++;
    }

    // Validación de la fecha
    // 12/24
    if(!empty($_POST['card_date'])) {
      $date = explode('/',$_POST['card_date']);
      if(count($date) < 2) {
        $errors++;
      }
      // array[12 , 24];
      if(clean_string($date[0]) !== $card['month']) {
        $errors++;
      }
      if(clean_string($date[1]) !== $card['year']) {
        $errors++;
      }
    } else {
      $errors++;
    }

    // Validación de el cvc
    if(clean_string($_POST['card_cvc']) !== $card['cvc']) {
      $errors++;
    }

    // Verificar si hay algún error
    if($errors > 0) {
      json_output(400,'Verifica la información de tu tarjeta por favor e intenta de nuevo');
    }

    // Guardamos el carro o la información del carro en otra variable para poder utilizarla como resumen
    // de compra
    // Número de compra
    $cart['order_number'] = rand(11111111,99999999);

    // Cliente de la compra
    $cart['client'] = $card;

    // Guardar resumen de compra
    $_SESSION['order_resume'] = $cart;

    destroy_cart();
    json_output(200);
    break;
  
  case 'order_resume':
    $cart = get_order_resume();

    // Generación de la factura de compra PDF
    $html2pdf = new Html2Pdf('P', 'A6', 'es', true, 'UTF-8',[10,10,10,10]);
    $html2pdf->writeHTML(get_module('pdf', $cart));

    // Nombre del pdf
    // order_xxxxxx.pdf;
    $filename = 'order_'.$cart['order_number'].'.pdf';
    $html2pdf->output(FILES.$filename, 'F');

    // Modal
    $output = 
    '<!-- Modal -->
    <div class="modal fade" id="order_resume" tabindex="-1" role="dialog" aria-labelledby="order_resume" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Resumen de compra</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h2>¡Gracias por tu compra!</h2>
            <p>Número de compra <b>#'.$cart['order_number'].'</b></p>
            <p><strong>'.$cart['client']['name'].'</strong>, tu pago ha sido realizado con éxito y hemos enviado el resumen de la misma al correo <b>'.$cart['client']['email'].'</b></p>
            <a href="'.URL.'files/'.$filename.'" target="_blank" class="btn btn-success" download><i class="fas fa-download"></i> Descargar factura</a>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>';

    // Mensaje al usuario / comprador
    $mail = new PHPMailer(true);
    try {

      $from = 'nain.acero25@gmail.com'; // La dirección del remitente
      $mail->setFrom($from, 'Carritow');

      // Dirección del destinatario
      $mail->addAddress($cart['client']['email'], $cart['client']['name']);

      // Attachments
      $mail->addAttachment(FILES.$filename);         // Add attachments

      // Content
      $mail->isHTML(true);
      $mail->CharSet  = 'UTF-8';
      $mail->Encoding = 'base64';
      $mail->Subject  = '[Carritow] Tu resumen de compra';
      $mail->Body     = get_module('test', $cart);
      $mail->AltBody  = 'Resumen de tu compra realizada en Carritow.';
      $mail->send();
      
    } catch (Exception $e) {
      $output .= "<br><br>Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    //send_email('jslocal@localhost.com','[Carritow] Tu resumen de compra', get_module('test', $cart));

    json_output(200,'',$output);
    break;
  
  default:
    json_output(403);
    break;
}

