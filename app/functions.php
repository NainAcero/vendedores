<?php 

function get_products() {
  $products = require APP.'products.php';
  return $products;
}

function get_product_by_id($id) {
  $products = get_products();
  foreach ($products as $i => $v) {
    if(intval($v['id']) === (int) $id) {
      return $products[$i];
    }
  }
  return false;
}

// render_view(carrito_view)
function render_view($view , $data = []) {
  $d = json_decode(json_encode($data));

  if(!is_file(VIEWS.$view.'.php')) {
    //si no existe la vista, yo quiero que hagas esto:
    echo 'No existe la vista '.$view;
    die;
  }
  
  require_once VIEWS.$view.'.php';
}

function format_currency($number, $symbol = '$') {
  if(!is_float($number) && !is_integer($number)) {
    $number = 0;
  }

  return $symbol.number_format($number,2,'.',',');
}


// --------------------------------------
//
// FUNCIONES DEL CARRITO
//
// --------------------------------------
function get_cart() {
  // Products
  // - ID
  // - SKU
  // - IMAGEN
  // - NOMBRE
  // - PRECIO
  // - CANTIDAD
  // Total products
  // Subtotal
  // Shipment
  // Total
  // Payment url
  if(isset($_SESSION['cart'])) {
    $_SESSION['cart']['cart_totals'] = calculate_cart_totals();
    return $_SESSION['cart'];
  }

  $cart =
  [
    'products'       => [],
    'cart_totals'    => calculate_cart_totals(),
    'payment_url'    => NULL
  ];

  $_SESSION['cart'] = $cart;
  return $_SESSION['cart'];
}

function calculate_cart_totals() {

  // El carro no existe, se inicializa
  // Si no hay productos aun pero el carrito si existe ya
  if(!isset($_SESSION['cart']) || empty($_SESSION['cart']['products'])) {
    $cart_totals =
    [
      'subtotal'       => 0,
      'shipment'       => 0,
      'total'          => 0
    ];
    return $cart_totals;
  }
  
  // Calcular los totales según los products en carrito
  $subtotal = 0;
  $shipment = SHIPPING_COST;
  $total    = 0;

  // Si ya hay productos hay que sumar las cantidades
  foreach ($_SESSION['cart']['products'] as $p) {
    $_total = floatval($p['cantidad'] * $p['precio']);
    $subtotal = floatval($subtotal + $_total);
  }

  $total = floatval($subtotal + $shipment);  
  $cart_totals =
  [
    'subtotal'       => $subtotal,
    'shipment'       => $shipment,
    'total'          => $total
  ];
  return $cart_totals;
}

function add_to_cart($id_producto , $cantidad = 1) {
  $new_product =
  [
    'id'       => NULL,
    'sku'      => NULL,
    'nombre'   => NULL,
    'cantidad' => NULL,
    'precio'   => NULL,
    'imagen'   => NULL
  ];

  $product = get_product_by_id($id_producto);

  // Algo paso, o no existe el producto
  if(!$product) {
    return false;
  }

  $new_product =
  [
    'id'       => $product['id'],
    'sku'      => $product['sku'],
    'nombre'   => $product['nombre'],
    'cantidad' => $cantidad,
    'precio'   => $product['precio'],
    'imagen'   => $product['imagen']
  ];

  // Si no existe el carro, es obvio que no existe el producto
  // entonces lo agregamos directamente
  if(!isset($_SESSION['cart']) || empty($_SESSION['cart']['products'])) {
    $_SESSION['cart']['products'][] = $new_product;
    return true;
  }

  // Si se agrega pero vamos primero a loopear en el array de todos los productos
  // para buscar uno con el mismo id sí existe
  foreach ($_SESSION['cart']['products'] as $i => $p) {
    if($id_producto === $p['id']) {
      $_cantidad = $p['cantidad'] + $cantidad;
      $p['cantidad'] = $_cantidad;
      $_SESSION['cart']['products'][$i] = $p;
      return true;
    }
  }
  
  $_SESSION['cart']['products'][] = $new_product;
  return true;
}

function update_cart_product($id_producto , $cantidad = 1) {
  // Si no existe el carro, es obvio que no existe el producto
  // entonces lo agregamos directamente
  if(!isset($_SESSION['cart']) || empty($_SESSION['cart']['products'])) {
    return false;
  }

  // Si se agrega pero vamos primero a loopear en el array de todos los productos
  // para buscar uno con el mismo id sí existe
  foreach ($_SESSION['cart']['products'] as $i => $p) {
    if($id_producto === $p['id']) {
      $p['cantidad'] = (int) $cantidad;
      $_SESSION['cart']['products'][$i] = $p;
      return true;
    }
  }
  
  return false;
}

function delete_from_cart($id_producto) {
  if(!isset($_SESSION['cart']) || empty($_SESSION['cart']['products'])) {
    return false;
  }

  foreach ($_SESSION['cart']['products'] as $index => $p) {
    if($id_producto === $p['id']) {
      unset($_SESSION['cart']['products'][$index]);
      return true;
    }
  }
  return false;
}

function destroy_cart() {
  unset($_SESSION['cart']);
  //session_destroy();
  return true;
}

function json_output($status = 200, $msg = '' , $data = []) {
  //http_response_code($status);
  $r =
  [
    'status' => $status,
    'msg'    => $msg,
    'data'   => $data
  ];
  echo json_encode($r);
  die;
}

function clean_string($string) {
  $string = trim($string);
  $string = rtrim($string);
  $string = ltrim($string);
  return $string;
}

function get_order_resume() {
  if(!isset($_SESSION['order_resume'])) {
    return false;
  }

  return $_SESSION['order_resume'];
}

function send_email($to , $subject = 'Nuevo mensaje' , $msg = NULL) {

  if(!filter_var($to , FILTER_VALIDATE_EMAIL)) {
    return false;
  }

  if($msg == NULL) {
    $msg = "
    <html>
    <head>
    <title>HTML email</title>
    </head>
    <body>
    <p>This email contains HTML Tags!</p>
    <table>
    <tr>
    <th>Firstname</th>
    <th>Lastname</th>
    </tr>
    <tr>
    <td>John</td>
    <td>Doe</td>
    </tr>
    </table>
    </body>
    </html>
    ";
  }

  // Always set content-type when sending HTML email
  $headers  = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
  $headers .= 'From: '.COMPANY_NAME.' <'.COMPANY_EMAIL.'>' . "\r\n";
  // More headers

  mail($to,$subject,$msg,$headers);
  return true;
}

// --------------------------------------
//
// FUNCIONES DEL PROYECTO03
//
// --------------------------------------
function get_module($module , $d = []) {
  $d = json_decode(json_encode($d));

  if(!is_file(MODULES.$module.'Module.php')) {
    echo 'No existe el archivo '.$module;
    die;
  }

  $module = MODULES.$module.'Module.php';

  ob_start();
  require_once $module;
  $output = ob_get_clean();

  return $output;
}