<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Allow: GET, POST, OPTIONS, PUT, DELETE');
header('Content-Type: application/json');
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$servidor = "localhost";
$user = "root";
$password = "";
$database = "iso-sys";

$method = $_SERVER['REQUEST_METHOD'];

$conexion = mysqli_connect($GLOBALS["servidor"], $GLOBALS["user"], $GLOBALS["password"], $GLOBALS["database"]);

// Verificar conexión
//check connection
if(!$conexion){
  echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
  exit;
}

if ($method == "OPTIONS") {
  die();
}

function sqlconector($consulta) { //Esta Funcion ejecuta una consulta update, insert, alter,... y devuelve un resultado.
  mysqli_set_charset($conexion, "utf8mb4");
  $resultado = mysqli_query($conexion, $consulta);
  
  if (!$resultado) {
      die("Error in query: " . mysqli_error($conexion));
  }
  
  return $resultado;
}

function generaCode(){
  $bytes = random_bytes(4);
  $referencia = bin2hex($bytes);
  return $referencia;
}


function sendEmailSoporte($to, $subject, $body) {
  // Configuración de PHPMailer
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'server121.web-hosting.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'soporteadministrativo@criptosignalgroup.online';
    $mail->Password = 'F_M4Cth#YNEw';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('soporteadministrativo@criptosignalgroup.online', 'Soporte Cryptosignal');
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
  } catch (Exception $e) {
    echo "Message could not be sent. Soporte Error: {$mail->ErrorInfo}";
  }
}

function sendEmail($to, $subject, $body) {
  // Configuración de PHPMailer
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'server121.web-hosting.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'criptosignalgroup@criptosignalgroup.online';
    $mail->Password = 'JRnc^YaDj@la';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('criptosignalgroup@criptosignalgroup.online', 'Cryptosignal');
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
  } catch (Exception $e) {
    echo "Message could not be sent. Cryptosignal Error: {$mail->ErrorInfo}";
  }
}

if ($method == "POST") {
  try {
      $jsonData = file_get_contents('php://input');
      $data = json_decode($jsonData, true);

      if (isset($data['example'])) {
          $response = array('exists' => false, 'pass' => false);
          echo json_encode($response);
      }


      if (isset($data['getcodemail'])) {
        $codigo = generaCode();
        $email = $data['email'];
        
        // Inserta el código en la base de datos (descomentar la línea si es necesario)
        // sqlconector("INSERT INTO LINKS (LINK, CORREO) VALUES('$codigo', '$email')");
      
        // Envia el email
        sendEmail($email, "Codigo Iran", "Copie este codigo: $codigo\nGenerado por Cryptosignal para su seguridad no conteste este email.");
      
        // Respuesta JSON solo con el código
        $obj = array('code' => $codigo);
        echo json_encode($obj);
      }
      
      if(isset($data['sendmailtecno'])){ //envia un correo a servicio tecnico con el asunto y el mensaje tratado
        $cliente = $data['cliente'];
        $asunto = $data['asunto'];
        $mensaje = $data['mensaje'];
        $servicioTecnico = "crptsgnlgrpspprt@gmail.com";
        sendEmail($servicioTecnico, "Asistencia Crytosignal", "Tienes una Nueva asistencia para el Cliente:<br>$cliente<br>Asunto Tratado: $asunto<br> <u><b>Problema Presentado:</b></u> $mensaje <hr>Recuerda contestar desde el correo de soporte este mensaje es solo un recordatorio.");  
      }

  } catch (Exception $e) {
      $response = array('Error' => $e->getMessage());
      echo json_encode($response);
  }
}

if ($method == "GET") {

  if (isset($_GET['Example'])) {
      $obj = array('current_period' => true, 'time_period' => false);
      echo json_encode($obj);
  }  

}

mysqli_close($conexion);