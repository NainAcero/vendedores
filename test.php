<?php 
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Spipu\Html2Pdf\Html2Pdf;

require_once 'app/config.php';
require_once 'app/vendor/autoload.php';

// Mensaje al usuario / comprador
$cart = get_order_resume();
$mail = new PHPMailer(true);
try {

  $from = 'jslocal@localhost.com'; // La dirección del remitente
  $mail->setFrom($from, 'Carritow');

  // Dirección del destinatario
  $mail->addAddress($cart['client']['email'], $cart['client']['name']);

  // Attachments
  $filename = "hola.txt";
  $mail->addAttachment($filename);         // Add attachments

  // Content
  $mail->isHTML(true);
  $mail->CharSet  = 'UTF-8';
  $mail->Encoding = 'base64';
  $mail->Subject  = '[Carritow] Tu resumen de compra';
  $mail->Body     = get_module('test', $cart);
  $mail->AltBody  = 'Resumen de tu compra realizada en Carritow.';
  $mail->send();
  
} catch (Exception $e) {
  echo "<br><br>Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}