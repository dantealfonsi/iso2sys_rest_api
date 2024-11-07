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

function sqlconector($consulta) {
    $resultado = mysqli_query($GLOBALS['conn'], $consulta);
    
    if (!$resultado) {
        die("Error in query: " . mysqli_error($GLOBALS['conn']));
    }
    
    return $resultado;
  }

function row_sqlconector($consulta) {
    $row = array();
    $resultado = mysqli_query($GLOBALS['conn'], $consulta);
    if($resultado){
        $row = mysqli_fetch_assoc($resultado);
    }

    return $row;
  }
  
  function array_sqlconector($consulta){
    $obj= array();
    $resultado = sqlconector($consulta);
    if($resultado){
      while($row = mysqli_fetch_assoc($resultado)){
        $obj[]=$row;
      }
    }
    return $obj;
  }


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

function ifLessonExists($order,$unit_id) {

	$resultado = mysqli_query($GLOBALS['conn'], "SELECT * FROM lessons WHERE lesson_order = $order and unit_id = $unit_id");
    if (mysqli_num_rows($resultado) > 0) {
       return true;
    } 	   
	return false;
}

function ifFileExists($name,$lesson_id) {

	$resultado = mysqli_query($GLOBALS['conn'], "SELECT * FROM guides WHERE name='$name' AND lesson_id = $lesson_id");
    if (mysqli_num_rows($resultado) > 0) {
       return true;
    } 	   
	return false;
}

function returnDatPerson($id) {
	$obj = array();
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT * FROM person WHERE id = $id");
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $row = mysqli_fetch_assoc($resultado);
		$obj = array(
			'id'=>$row['id'],
			'cedula' => $row['cedula'],
			'name' => $row['name'],
			'second_name' => $row['second_name'],
			'last_name'	=> $row['last_name'],
			'second_last_name' => $row['second_last_name'],
			'phone'	=> $row['phone'],
			'birthday' =>$row['birthday'],
			'gender' => $row['gender'],
			'address' => $row['address']
		);
    }
	return $obj;	
}



function returnUnitName($id) {
	$obj = array();
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT name FROM units WHERE id = $id");
    $row = mysqli_fetch_assoc($resultado);
    
	return $row;	
}

function returnUnitOrder($id) {
	$obj = array();
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT unit_order FROM units WHERE id = $id");
    $row = mysqli_fetch_assoc($resultado);
    
	return $row;	
}

function returnLessons($unit_id)
{
    $obj = array();
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT title,id,lesson_order FROM lessons WHERE unit_id = '$unit_id'");
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $obj[] = array(
                'title' => $row['title'],
                'lesson_order' => $row['lesson_order'],
                'id' => $row['id'],
            );
        }
    }
    return $obj;
}


function returnExamQuestions($exam_id)
{
    $obj = array('totalQuestionMark'=>0,'count'=>0,'question'=>'','data_exam'=>'');
    $question = array();
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT id,exam_id,text,type,question_order,question_mark FROM questions WHERE exam_id = '$exam_id'");
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $dataExam = row_sqlconector("SELECT * FROM exams WHERE id={$row['exam_id']}");            
            $question_data = array_sqlconector("SELECT * FROM questions_data WHERE question_id = '{$row['id']}'");
            $block_radius = row_sqlconector("SELECT COUNT(*) as suma FROM questions_data WHERE question_id={$row['id']} AND type='radius' AND true_response='true'")['suma'];
            $block_select = row_sqlconector("SELECT COUNT(*) as suma FROM questions_data WHERE question_id={$row['id']}")['suma'];
            $question[] = array(
                'block_radius' => $block_radius,
                'block_select' => $block_select,
                'id' => $row['id'],                
                'exam_id' => $row['exam_id'],                
                'text' => $row['text'],
                'type' => $row['type'],
                'question_order' => $row['question_order'],
                'question_mark' => $row['question_mark'],
                'question_data' => $question_data                
            );
            $obj['question'] = $question;
            $obj['data_exam'] = $dataExam;
            $obj['count'] = row_sqlconector("SELECT COUNT(*) as suma FROM questions WHERE exam_id={$row['exam_id']}")['suma'];
            $obj['totalQuestionMark'] = row_sqlconector("SELECT SUM(question_mark) as suma FROM questions WHERE exam_id={$row['exam_id']}")['suma'];
        }
    }

    return $obj;
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
                'file' => $row['file'],
                'name' => $row['name']
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

        if(isset($data['update'])) {
            // Limpia el buffer de salida antes de cualquier echo
            ob_clean();
        
            $campo = mysqli_real_escape_string($conn, $data['campo']);
            $valor = mysqli_real_escape_string($conn, strtolower($data['valor']));
            $tabla = mysqli_real_escape_string($conn, $data['tabla']);
            $query = "UPDATE $tabla SET $campo = $valor WHERE user_id=".$data['update'];
            $result = mysqli_query($conn, $query);
        
            if (!$result) {
                // Error en la consulta
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
            }
        
            // Solo enviamos esta respuesta al final
            $response = array("message" => "ok");
            echo json_encode($response);
        
            // Asegúrate de terminar el script correctamente
            exit();
        }
       
        if(isset($data['delete'])) {
            // Limpia el buffer de salida antes de cualquier echo
            ob_clean();
        
            $tabla = mysqli_real_escape_string($conn, $data['tabla']);
            $query = "DELETE FROM $tabla  WHERE id=".$data['delete'];
            $result = mysqli_query($conn, $query);
        
            if (!$result) {
                // Error en la consulta
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
            }
        
            // Solo enviamos esta respuesta al final
            $response = array("message" => "ok");
            echo json_encode($response);
        
            // Asegúrate de terminar el script correctamente
            exit();
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

            if (ifLessonExists($lesson_order,$unit_id) == true) {
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




    if (isset($data['editQuestion'])) {

        $message = '';
        $icon = '';

            // Escapa los valores para evitar inyección de SQL
            $id = mysqli_real_escape_string($conn, strtolower($data['question']['id']));
            $question_order = mysqli_real_escape_string($conn, strtolower($data['question']['question_order']));
            $question_mark =  mysqli_real_escape_string($conn, strtolower($data['question']['question_mark']));
            $text =  mysqli_real_escape_string($conn, strtolower($data['question']['text']));
            //$type = mysqli_real_escape_string($conn, strtolower($data['question']['type']));


                $query = "UPDATE questions SET question_order=$question_order,question_mark=$question_mark, text='$text' WHERE id=$id";
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

// Manejar subida de archivos
/*
if (isset($_POST['addFile']) && $_POST['addFile'] === 'true') {
    $lesson_id = $_POST['lesson_id'];
    $uploadDirectory = 'guides/';
    foreach ($_FILES['files']['name'] as $key => $name) {
      $tmpName = $_FILES['files']['tmp_name'][$key];
      $filePath = $uploadDirectory . basename($name);
      if (move_uploaded_file($tmpName, $filePath)) {
        chmod($filePath, 0777); // Cambia los permisos del archivo a 777
        $query = "INSERT INTO guides (lesson_id, file, name) VALUES ('$lesson_id', '$filePath', '$name')";
        $result = mysqli_query($conn, $query);
        echo json_encode(['file' => $name]);
      } else {
        echo json_encode(['file' => 'error']);
      }
    }
  }
  */

// Manejar subida de archivos
if (isset($_POST['addFile']) && $_POST['addFile'] === 'true') {
    $lesson_id = $_POST['lesson_id'];
    $uploadDirectory = 'guides/';
    foreach ($_FILES['files']['name'] as $key => $name) {
        if (ifFileExists($name, $lesson_id)) {
            echo json_encode(['status' => 'exists', 'file' => $name, 'message' => 'File already exists']);
            exit; // Detenemos el script si el archivo existe
        }
        $tmpName = $_FILES['files']['tmp_name'][$key];
        $filePath = $uploadDirectory . basename($name);

        // Añadir mensaje de depuración
        error_log("Intentando mover archivo: $tmpName a $filePath");

        if (move_uploaded_file($tmpName, $filePath)) {
            chmod($filePath, 0777); // Cambia los permisos del archivo a 777
            $query = "INSERT INTO guides (lesson_id, file, name) VALUES ('$lesson_id', '$filePath', '$name')";
            $result = mysqli_query($conn, $query);
            if ($result) {
                echo json_encode(['status' => 'success', 'file' => $name, 'message' => 'File uploaded successfully']);
            } else {
                error_log("Error al insertar en la base de datos: " . mysqli_error($conn));
                echo json_encode(['status' => 'error', 'message' => 'DB insert failed']);
            }
        } else {
            error_log("Error al mover el archivo.");
            echo json_encode(['status' => 'error', 'message' => 'File move failed']);
        }
    }
}


  // Manejar eliminación de archivos
  if (isset($_POST['fileName']) && isset($_POST['lesson_id'])) {
    $fileName = $_POST['fileName'];
    $lessonId = $_POST['lesson_id'];
    $filePath = 'guides/' . $fileName;
  
    // Eliminar entrada de la base de datos
    $query = "DELETE FROM guides WHERE name = '$fileName' AND lesson_id = '$lessonId'";
    $result = mysqli_query($conn, $query);
  
    // Verificar si el archivo todavía está asociado a otra lesson_id
    $query = "SELECT COUNT(*) AS file_count FROM guides WHERE name = '$fileName'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $fileCount = $row['file_count'];
  
    if ($fileCount == 0) {
      // Eliminar el archivo del sistema de archivos si no tiene más asociaciones
      if (file_exists($filePath)) {
        unlink($filePath); // Elimina el archivo
      }
      echo json_encode(['message' => 'Archivo eliminado del sistema de archivos y de la base de datos']);
    } else {
      echo json_encode(['message' => 'Archivo eliminado de la base de datos, pero no del sistema de archivos']);
    }
  }

  if (isset($data['editUser'])) {

			$message = 'Editado';

				// Escapa los valores para evitar inyección de SQL
				$id = mysqli_real_escape_string($conn, $data['user']['id']);
				$email = mysqli_real_escape_string($conn, $data['user']['email']);
				$password = mysqli_real_escape_string($conn, $data['user']['password']);
				$isAdmin = mysqli_real_escape_string($conn, $data['user']['isAdmin']);
				
				
				if (empty($password)) {
					$hashContrasena =  returnDatUser($id)['password'];
				}
				else{
					$hashContrasena = password_hash($password, PASSWORD_BCRYPT);
				}
								
				// ...otros campos    
				$query = "UPDATE user SET 
					email='$email',
					password='$hashContrasena',
					isAdmin=$isAdmin where user_id=$id";

				$result = mysqli_query($conn, $query);

				if (!$result) {
					throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
					$message = 'Error';
				}

			$response = array('message' => $message);
			echo json_encode($response);
		}  
    
            


        if (isset($data['addAdmin'])) {
            try {
            function insertPerson($conn, $data) {
                $nationality = mysqli_real_escape_string($conn, $data['nationality']);
				$cedula = mysqli_real_escape_string($conn, $data['cedula']);
				$name = mysqli_real_escape_string($conn, strtolower($data['name']));
				$second_name = mysqli_real_escape_string($conn, strtolower($data['second_name']));
				$last_name = mysqli_real_escape_string($conn, strtolower($data['last_name']));
				$second_last_name = mysqli_real_escape_string($conn, strtolower($data['second_last_name']));
				$phone = mysqli_real_escape_string($conn, $data['phone']);
				$birthday = mysqli_real_escape_string($conn, $data['birthday']);
				$gender = mysqli_real_escape_string($conn, $data['gender']);
				$address = mysqli_real_escape_string($conn, strtolower($data['address']));
                // ...otros campos
                $query = "INSERT INTO person (nationality,cedula, name, second_name,last_name,second_last_name,phone,birthday,gender,address) VALUES ('$nationality','$cedula', '$name','$second_name','$last_name','$second_last_name','$phone','$birthday','$gender','$address')";
				$result = mysqli_query($conn, $query);
                if (!$result) {
                    throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                }
                return mysqli_insert_id($conn);
            }
    
            $endIdPerson = insertPerson($conn, $data['person']);
    
            if (!ifUserExist($data['userData']['email'])) {
                $hashContrasena = password_hash($data['userData']['password'], PASSWORD_BCRYPT);
                $QinsertUser = "INSERT INTO user (person_id, email, password,isAdmin) VALUES ($endIdPerson, '".$data['userData']['email']."', '$hashContrasena',".$data['userData']['isAdmin'].")";
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


        /*
            Añade una Question data de tipo complete 
            input type text
        */
        if(isset($data['addQuestionDataComplete'])){
            $obj = array('result' => 'true');
            $question_id = mysqli_real_escape_string($conn, strtolower($data['questionData']['question_id']));
            $exam_id = mysqli_real_escape_string($conn, strtolower($data['questionData']['exam_id']));
            $answer = mysqli_real_escape_string($conn, strtolower($data['questionData']['answer']));
            $type = mysqli_real_escape_string($conn, strtolower($data['questionData']['type']));
            $true_response = mysqli_real_escape_string($conn, strtolower($data['questionData']['true_response']));

            $result = sqlconector("insert into questions_data(question_id,exam_id,answer,type,true_response) values($question_id,$exam_id,'$answer','$type','$true_response')");
            if(!$result){
                $obj['result'] = 'false';
            }

            echo json_encode($obj);
        }
 


        if (isset($data['addExam'])) {

            $message = '';
            $icon = '';

                // Escapa los valores para evitar inyección de SQL
                $unit_id =  mysqli_real_escape_string($conn, strtolower($data['exam']['unit_id']));
                $title = mysqli_real_escape_string($conn, strtolower($data['exam']['title']));
                $description =  mysqli_real_escape_string($conn, strtolower($data['exam']['description']));
                $total_score = mysqli_real_escape_string($conn, strtolower($data['exam']['total_score']));
                // ...otros campos    

                    $query = "INSERT INTO exams (unit_id,title,description,total_score) VALUES ('$unit_id','$title','$description',$total_score)";
                    $result = mysqli_query($conn, $query);
    
                    if (!$result) {
                        throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                        $message = 'Error';
                    }
                    $message ='Examen Añadido con Exito';
                    $icon = 'success';

                $response = array('message' => $message,'icon'=>$icon);
                echo json_encode($response);
        }



        if (isset($data['editExam'])) {

            $subjectExist = false;
            $message = '';
            $icon = '';
    
                // Escapa los valores para evitar inyección de SQL
                $id =  mysqli_real_escape_string($conn, strtolower($data['exam']['id']));
                $unit_id =  mysqli_real_escape_string($conn, strtolower($data['exam']['unit_id']));
                $title = mysqli_real_escape_string($conn, strtolower($data['exam']['title']));
                $description =  mysqli_real_escape_string($conn, strtolower($data['exam']['description']));
                $total_score = mysqli_real_escape_string($conn, strtolower($data['exam']['total_score']));
    
                // ...otros campos    
    
                    $query = "UPDATE exams SET unit_id=$unit_id,title='$title',description='$description',total_score=$total_score WHERE id=$id";
                    $result = mysqli_query($conn, $query);
    
                    if (!$result) {
                        throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                        $message = 'Error';
                    }
                    $message ='Examen editado con exito';
                    $icon = 'success';
                
                $response = array('message' => $message,'icon'=>$icon);
                echo json_encode($response);
        }


        if (isset($data['addQuestion'])) {

            $message = '';
            $icon = '';

                // Escapa los valores para evitar inyección de SQL
                $exam_id =  mysqli_real_escape_string($conn, strtolower($data['question']['exam_id']));
                $question_order =  mysqli_real_escape_string($conn, strtolower($data['question']['question_order']));
                $question_mark = mysqli_real_escape_string($conn, strtolower($data['question']['question_mark']));
                $text =  mysqli_real_escape_string($conn, strtolower($data['question']['text']));
                $type = mysqli_real_escape_string($conn, strtolower($data['question']['type']));
                // ...otros campos    

                    $query = "INSERT INTO questions (exam_id,question_order,question_mark,text,type) VALUES ($exam_id,$question_order,$question_mark,'$text','$type')";
                    $result = mysqli_query($conn, $query);
    
                    if (!$result) {
                        throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                        $message = 'Error';
                    }
                    $message ='Examen Añadido con Exito';
                    $icon = 'success';

                $response = array('message' => $message,'icon'=>$icon);
                echo json_encode($response);
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

    if(isset($_GET['exam_list'])){
		$obj = array('data' => array(),'unit_list' => array());
        $data = array();
		$consulta = "SELECT * FROM exams where isDeleted = 0";
		$resultado = mysqli_query($conn, $consulta);
		if ($resultado && mysqli_num_rows($resultado) > 0) {
			while($row = mysqli_fetch_assoc($resultado)) {     
                $unit_name = row_sqlconector("SELECT name FROM units WHERE id={$row['unit_id']}")['name'];
				$data[]=array('id'=>$row['id'],
                'unit_id'=>$row['unit_id'],
                'unit_name' => $unit_name,
                'title'=>$row['title'],
                'description'=>$row['description'],
                'total_score'=>$row['total_score'],
                'exam_order'=>$row['exam_order']);

			} 
		}
        $obj['data'] = $data;
        $obj['unit_list'] = array_sqlconector("SELECT id,name,unit_order FROM units where isDeleted = 0 order by unit_order ASC");
		echo json_encode($obj);   
	}



    if(isset($_GET['this_exam_list'])){
		$obj = array('data' => array(),'unit_list' => array());
        $data = array();
		$consulta = "SELECT * FROM exams where isDeleted = 0";
		$resultado = mysqli_query($conn, $consulta);
		if ($resultado && mysqli_num_rows($resultado) > 0) {
			while($row = mysqli_fetch_assoc($resultado)) {     
                $unit_name = row_sqlconector("SELECT name FROM units WHERE id={$row['unit_id']}")['name'];
                
				$data[]=array('id'=>$row['id'],
                'unit_id'=>$row['unit_id'],
                'unit_name' => $unit_name,
                'title'=>$row['title'],
                'description'=>$row['description'],
                'total_score'=>$row['total_score'],
                'exam_order'=>$row['exam_order']);

			} 
		}
        $obj['data'] = $data;
        $obj['unit_list'] = array_sqlconector("SELECT id,name,unit_order FROM units where isDeleted = 0 order by unit_order ASC");
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

if (isset($_GET['this_exams_data'])) {
    //datos de los examenes
    echo json_encode(returnExamQuestions($_GET['id']));
}


if (isset($_GET['this_specific_lesson_list'])) {
    $unit_id = $_GET['id'];
    $lesson_order = $_GET['lesson_order'];
    $obj = array();
    $consulta = "SELECT * FROM lessons WHERE unit_id = $unit_id AND lesson_order = $lesson_order";
    $resultado = mysqli_query($conn, $consulta);
    if (!$resultado) {
        die('Error en la consulta: ' . mysqli_error($conn));
    }
    while ($row = mysqli_fetch_assoc($resultado)) {
        $obj = array(
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

if(isset($_GET['user_list'])){
    $obj = array();
    $consulta = "SELECT * FROM user where isDeleted=0 and isAdmin=0";
    $resultado = mysqli_query($conn, $consulta);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while($row = mysqli_fetch_assoc($resultado)) {      
            $obj[]=array('user_id'=>$row['user_id'],'person_id'=>returnDatPerson($row['person_id']),'password'=>$row['password'],'isAdmin'=>$row['isAdmin'],'email'=>$row['email'],'isBlocked'=>$row['isBlocked']);
        }   
    }
    echo json_encode($obj); 
}

    
if(isset($_GET['admin_list'])){
    $obj = array();
    $consulta = "SELECT * FROM user where isDeleted=0 and isAdmin=1";
    $resultado = mysqli_query($conn, $consulta);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while($row = mysqli_fetch_assoc($resultado)) {      
            $obj[]=array('user_id'=>$row['user_id'],'person_id'=>returnDatPerson($row['person_id']),'password'=>$row['password'],'isAdmin'=>$row['isAdmin'],'email'=>$row['email'],'isBlocked'=>$row['isBlocked']);
        }   
    }
    echo json_encode($obj); 
}



if(isset($_GET['person_list'])){
    $obj = array();
    $consulta = "SELECT
        person.id,
        person.nationality,
        person.cedula,
        person.name,
        person.phone,
        person.second_name,
        person.last_name,
        person.second_last_name,
        person.birthday,
        person.gender,
        person.address
        FROM
        person
        INNER JOIN
        teacher ON teacher.person_id = person.id
        INNER JOIN
        parent ON parent.person_id = person.id";
    $resultado = mysqli_query($conn, $consulta);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while($row = mysqli_fetch_assoc($resultado)) {      
            $obj[]=array('id'=>$row['id'],'phone'=>$row['phone'],'nationality'=>$row['nationality'],'cedula'=>$row['cedula'],'name'=>$row['name'],'second_name'=>$row['second_name'],'last_name'=>$row['last_name'],'second_last_name'=>$row['second_last_name'],'birthday'=>$row['birthday'],'gender'=>$row['gender'],'address'=>$row['address']);
        }   
    }
    echo json_encode($obj); 
}		


if (isset($_GET['units_and_lessons_list'])) {
    $obj = array();
    $consultaUnidades = "SELECT * FROM units WHERE isDeleted = 0 ORDER BY unit_order ASC";
    $resultadoUnidades = mysqli_query($conn, $consultaUnidades);
    if ($resultadoUnidades && mysqli_num_rows($resultadoUnidades) > 0) {
        while ($rowUnidad = mysqli_fetch_assoc($resultadoUnidades)) {
            $unidad = array(
                'id' => $rowUnidad['id'],
                'name' => $rowUnidad['name'],
                'order' => $rowUnidad['unit_order'],
                'lessons' => array()
            );

            $unit_id = $rowUnidad['id'];
            $consultaLecciones = "SELECT * FROM lessons WHERE unit_id = $unit_id ORDER BY lesson_order ASC";
            $resultadoLecciones = mysqli_query($conn, $consultaLecciones);
            if ($resultadoLecciones && mysqli_num_rows($resultadoLecciones) > 0) {
                while ($rowLeccion = mysqli_fetch_assoc($resultadoLecciones)) {
                    $unidad['lessons'][] = array(
                        'id' => $rowLeccion['id'],
                        'title' => $rowLeccion['title'],
                        'content' => $rowLeccion['content'],
                        'lesson_order' => $rowLeccion['lesson_order'],
                        'summary' => $rowLeccion['summary'],
                        'url' => $rowLeccion['url'],
                        'files' => returnExistingFiles($rowLeccion['id'])
                    );
                }
            }
            $obj[] = $unidad;
        }
    }
    echo json_encode($obj);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'Error en la codificación JSON: ' . json_last_error_msg();
    }


    }





}









