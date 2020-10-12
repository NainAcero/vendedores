<?php 
// PHP Y SUS FUNCIONES PREDEFINIDAS ESTÁN TODAS ATRAS DE ESTO
require_once 'app/config.php';

$data =
[
  'title' => 'Tienda de Carritow',
  'products' => get_products()
];

//session_destroy();
// Renderizado de la vista
render_view('carrito_view' , $data);
