<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Allow: GET, POST, OPTIONS, PUT, DELETE');
header('Content-Type: application/json');


//<!--========== PHP CONNECTION TO DATABASE ==========-->
$host = "localhost";
$username = "root";
$pass = "";
$dbname = "iso-sys";
$method = $_SERVER['REQUEST_METHOD'];
$conn = mysqli_connect($host, $username, $pass, $dbname); //create connection

//check connection
if(!$conn){
      echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
      die();
}

//funciones de utilidad para el server

function ifUserExist($email){
	$existe = false;
	$resultado = mysqli_query($GLOBALS['conn'], "SELECT 1 FROM user WHERE email = '$email'");
	if(mysqli_num_rows($resultado) > 0) $existe = true;
	return $existe;	
  }

  function ifUnitExists($order) {

	$resultado = mysqli_query($GLOBALS['conn'], "SELECT * FROM units WHERE unit_order = $order AND isDeleted=0");
    if (mysqli_num_rows($resultado) > 0) {
       return true;
    } 	   
	return false;
}

function ifLessonExists($order) {

	$resultado = mysqli_query($GLOBALS['conn'], "SELECT * FROM lessons WHERE lesson_order = $order");
    if (mysqli_num_rows($resultado) > 0) {
       return true;
    } 	   
	return false;
}

function returnExistingFiles($lesson_id)
{
    $obj = array();
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT * FROM guides WHERE lesson_id = '$lesson_id'");
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $obj[] = array(
                'id' => $row['id'],
                'lesson_id' => $row['lesson_id'],
                'file' => $row['file']
            );
        }
    }
    return $obj;
}


//-------------------------------------

//*******Metodos de Comunicacion con el Front *************

if ($method == "OPTIONS") {
    exit();
}

if ($method == "POST") {

    try {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);


        if(isset($data['updateSingleField'])){ /* Actualiza segun un campo con su valor y  la tabla requerida*/

			$campo = mysqli_real_escape_string($conn, $data['campo']);
			$valor = mysqli_real_escape_string($conn, strtolower($data['valor']));
			$tabla = mysqli_real_escape_string($conn, $data['tabla']);
			$whereCondition =	 mysqli_real_escape_string($conn, $data['whereCondition']);

			
            $query = "UPDATE $tabla SET $campo = $valor WHERE $whereCondition";
            $result = mysqli_query($conn, $query);
            
            if (!$result) {
                // Error en la consulta
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
            }
			
			$response = array("message" => "ok");
			echo json_encode($response);
		}



     if (isset($data['register'])) {
        try {
        
        function insertPerson($conn, $data) {
            $name = mysqli_real_escape_string($conn, strtolower($data['name']));
            $last_name = mysqli_real_escape_string($conn, strtolower($data['last_name']));
            $birthday = mysqli_real_escape_string($conn, $data['birthday']);
            // ...otros campos
            $query = "INSERT INTO person (name, last_name, birthday) VALUES ('$name', '$last_name', '$birthday')";
            $result = mysqli_query($conn, $query);
            if (!$result) {
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
            }
            return mysqli_insert_id($conn);
        }

        $endIdPerson = insertPerson($conn, $data['person']);

        if (!ifUserExist($data['userData']['email'])) {
            $hashContrasena = password_hash($data['userData']['password'], PASSWORD_BCRYPT);
            $QinsertUser = "INSERT INTO user (person_id, email, password) VALUES ($endIdPerson, '".$data['userData']['email']."', '$hashContrasena')";
            $result = mysqli_query($conn, $QinsertUser);
            if (!$result) {
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
            }
            $message = 'Usuario añadido con éxito...';
            $icon = 'success';
        } else {
            $message = 'Error: Este usuario ya existe';
            $icon = 'error';
        }

        $response = array('message' => $message, 'icon' => $icon);
        echo json_encode($response);

    } catch (Exception $e) {
        $response = array('message' => 'Error:' . $e->getMessage(), 'icon' => 'error');
        echo json_encode($response);
        }
    }

    //verifica el inicio de sesion
       
    if(isset($data['login'])){
                $query = "SELECT * FROM user WHERE email='".$data['email']."' AND isBlocked=0";
                $result = mysqli_query($conn, $query);
            
                if (!$result) {
                    // Error en la consulta
                    throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                }
            
                $row = mysqli_fetch_array($result);
                $userExist = false;
                $pass = false;
                $user_id = '';
                $isAdmin = 0;
            
                if(mysqli_num_rows($result) > 0){
                    $userExist = true;
                    if (password_verify($data['password'], $row['password'] )){
                        $pass = true;
                        $user_id = $row['user_id'];
                        $isAdmin = $row['isAdmin'];
            
                        // Creación del payload para JWT
                        $payload = [
                            'id' => $user_id,
                            'email' => $row['email'],
                            'isAdmin' => $isAdmin
                        ];
            
                        $token = $payload;
                        echo json_encode(['token' => $token, 'isAdmin' => $isAdmin]);
                        exit();
                    }
                }
            
                $response = array('exists' => $userExist, 'pass' => $pass, 'user_id' => $user_id, 'isAdmin' => $isAdmin);
                echo json_encode($response);
    }



    if (isset($data['addUnit'])) {

        $message = '';
        $icon = '';

            // Escapa los valores para evitar inyección de SQL
            $order =  mysqli_real_escape_string($conn, strtolower($data['unit']['order']));
            $name = mysqli_real_escape_string($conn, strtolower($data['unit']['name']));
            // ...otros campos    


            if (ifUnitExists($order) == true) {
                $message ='Cuidado: Esta unidad ya tiene un numero asignado ';
                $icon = 'warning';					
            } else{
                $query = "INSERT INTO units (name,unit_order,subject_id) VALUES ('$name',$order,1)";
                $result = mysqli_query($conn, $query);

                if (!$result) {
                    throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                    $message = 'Error';
                }
                $message ='Unidad Añadida con Exito';
                $icon = 'success';
            }
            $response = array('message' => $message,'icon'=>$icon);
            echo json_encode($response);
    }

        if (isset($data['addLesson'])) {

        $message = '';
        $icon = '';

            // Escapa los valores para evitar inyección de SQL
            $title = mysqli_real_escape_string($conn, strtolower($data['lesson']['title']));
            $unit_id =  mysqli_real_escape_string($conn, strtolower($data['lesson']['unitId']));
            $lesson_order =  mysqli_real_escape_string($conn, strtolower($data['lesson']['lesson_order']));
            $summary = mysqli_real_escape_string($conn, strtolower($data['lesson']['summary']));
            $url =  mysqli_real_escape_string($conn, strtolower($data['lesson']['url']));

            if (ifLessonExists($lesson_order) == true) {
                $message ='Cuidado: Esta lección ya tiene un numero asignado ';
                $icon = 'warning';					
            } else{
                $query = "INSERT INTO lessons (title,unit_id,lesson_order,summary,url) VALUES ('$title','$unit_id',$lesson_order,'$summary','$url')";
                $result = mysqli_query($conn, $query);

                if (!$result) {
                    throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                    $message = 'Error';
                }
                $message ='Leccion Añadida con Exito';
                $icon = 'success';
            }
            $response = array('message' => $message,'icon'=>$icon);
            echo json_encode($response);
        }

    if (isset($data['editUnit'])) {

        $subjectExist = false;
        $message = '';
        $icon = '';

            // Escapa los valores para evitar inyección de SQL
            
            $id = mysqli_real_escape_string($conn, $data['unit']['id']);
            $name = mysqli_real_escape_string($conn, strtolower($data['unit']['name']));
            $order =  mysqli_real_escape_string($conn, strtolower($data['unit']['order']));

            // ...otros campos    

                $query = "UPDATE units SET name='$name', unit_order=$order WHERE id=$id";
                $result = mysqli_query($conn, $query);

                if (!$result) {
                    throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                    $message = 'Error';
                }
                $message ='Unidad editada con exito';
                $icon = 'success';
            
            $response = array('message' => $message,'icon'=>$icon);
            echo json_encode($response);
    }


    if (isset($data['editLesson'])) {

        $message = '';
        $icon = '';

            // Escapa los valores para evitar inyección de SQL
            $id = mysqli_real_escape_string($conn, strtolower($data['lesson']['id']));
            $title = mysqli_real_escape_string($conn, strtolower($data['lesson']['title']));
            $unit_id =  mysqli_real_escape_string($conn, strtolower($data['lesson']['unitId']));
            $lesson_order =  mysqli_real_escape_string($conn, strtolower($data['lesson']['lesson_order']));
            $summary = mysqli_real_escape_string($conn, strtolower($data['lesson']['summary']));
            $url =  mysqli_real_escape_string($conn, strtolower($data['lesson']['url']));

            // ...otros campos    

                $query = "UPDATE lessons SET title='$title',unit_id=$unit_id, lesson_order=$lesson_order,summary='$summary',url='$url' WHERE id=$id";
                $result = mysqli_query($conn, $query);

                if (!$result) {
                    throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                    $message = 'Error';
                }
                $message ='Unidad editada con exito';
                $icon = 'success';
            
            $response = array('message' => $message,'icon'=>$icon);
            echo json_encode($response);
    }

    if (isset($_POST['addFile']) && $_POST['addFile'] === 'true') {
        
        $lesson_id  = $_POST['lesson_id'];

        $uploadDirectory = 'guides/';
        foreach ($_FILES['files']['name'] as $key => $name) {
          $tmpName = $_FILES['files']['tmp_name'][$key];
          $filePath = $uploadDirectory . basename($name);
          if (move_uploaded_file($tmpName, $filePath)) {
            chmod($filePath, 0777); // Cambia los permisos del archivo a 777
            
            $query = "INSERT INTO guides (lesson_id, file,name) VALUES ($lesson_id, '$filePath','$name')";
            $result = mysqli_query($conn, $query);

            echo json_encode(['file' => $name]);
            
          } else {
            echo json_encode(['file' => 'error']);
          }
        }
      } else {
        echo json_encode(['error' => 'addFile not set or not true']);
      }
      
            




}catch (Exception $e) {		
        //http_response_code(500);
		$response = array('Error: ' => $e->getMessage());
		echo json_encode($response);		
        //echo json_encode(new stdClass()); // Devuelve un objeto JSON vacío
    }

}

if ($method == "GET") {

    if (isset($_GET['pipe'])) {
        $query = "SELECT * FROM user WHERE email='daniel.alfonsi2011@gmail.com' AND isBlocked=0";
        $result = mysqli_query($conn, $query);
    
        if (!$result) {
            // Error en la consulta
            throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
        }
    
        $row = mysqli_fetch_array($result);
        $userExist = false;
        $pass = false;
        $user_id = 0;
        $isAdmin = false;
        $token = '';
    
        if (mysqli_num_rows($result) > 0) {
            $userExist = true;
            // Cambiar a la verificación correcta de la contraseña
            if (password_verify('a$10882990', $row['password'])) {
                $pass = true;
                $user_id = $row['user_id'];
                $isAdmin = $row['isAdmin'];
    
                // Creación del payload para JWT
                $payload = array(
                    'id' => $user_id,
                    'email' => $row['email'],
                    'isAdmin' => $isAdmin
                );
    
                //$token = JWT::encode($payload, 'tu_clave_secreta');
                $token = $payload;
            }
        }
    
        $response = array('token' => $token, 'exists' => $userExist, 'pass' => $pass, 'user_id' => $user_id, 'isAdmin' => $isAdmin);
        echo json_encode($response);
    }


    if(isset($_GET['unit_list'])){
		$obj = array();
		$consulta = "SELECT * FROM units where isDeleted = 0";
		$resultado = mysqli_query($conn, $consulta);
		if ($resultado && mysqli_num_rows($resultado) > 0) {
			while($row = mysqli_fetch_assoc($resultado)) {      
				$obj[]=array('id'=>$row['id'],'name'=>$row['name'],'order'=>$row['unit_order']);
			} 
		}
		echo json_encode($obj);   
	}

	if(isset($_GET['this_unit_list'])){
		$unit_id = $_GET['id'];
		$obj = array();
		$consulta = "SELECT * FROM units where id =$unit_id and isDeleted = 0 ";
		$resultado = mysqli_query($conn, $consulta);
		while($row = mysqli_fetch_assoc($resultado)) {
			$obj = array(
				'id' => $row['id'],
				'subject_id' => $row['subject_id'],
				'name' => $row['name'],
				'unit_order' => $row['unit_order'],
			);
		}
		echo json_encode($obj); 
		// Agrega esto para depurar
		if (json_last_error() !== JSON_ERROR_NONE) {
			echo 'Error en la codificación JSON: ' . json_last_error_msg();
		}
	}

if (isset($_GET['this_lessons_list'])) {
    $unit_id = $_GET['id'];
    $obj = array();
    $consulta = "SELECT * FROM lessons WHERE unit_id = $unit_id ORDER BY lesson_order ASC";
    $resultado = mysqli_query($conn, $consulta);
    if (!$resultado) {
        die('Error en la consulta: ' . mysqli_error($conn));
    }
    while ($row = mysqli_fetch_assoc($resultado)) {
        $obj[] = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'content' => $row['content'],
            'lesson_order' => $row['lesson_order'],
            'summary' => $row['summary'],
            'url' => $row['url'],
            'files' => returnExistingFiles($row['id'])
        );
    }
    echo json_encode($obj); // Devuelve los datos en formato JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'Error en la codificación JSON: ' . json_last_error_msg();
    }
}

if (isset($_GET['this_lessons_files'])) {
    $lesson_id = $_GET['id'];
    $obj = array();
    $consulta = "SELECT * FROM guides WHERE lesson_id = $lesson_id";
    $resultado = mysqli_query($conn, $consulta);
    if (!$resultado) {
        die('Error en la consulta: ' . mysqli_error($conn));
    }
    while ($row = mysqli_fetch_assoc($resultado)) {
        $obj[] = array(
            'id' => $row['id'],
            'name'=> $row['name'],
        );
    }
    echo json_encode($obj); // Devuelve los datos en formato JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'Error en la codificación JSON: ' . json_last_error_msg();
    }
}
    


}


