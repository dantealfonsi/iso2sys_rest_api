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

	$resultado = mysqli_query($GLOBALS['conn'], "SELECT * FROM lessons WHERE lesson_order = $order and unit_id = $unit_id AND isDeleted=0");
    if (mysqli_num_rows($resultado) > 0) {
       return true;
    } 	   
	return false;
}


function ifMarkExists($user_id,$exam_id) {

	$resultado = mysqli_query($GLOBALS['conn'], "SELECT * FROM exam_scores WHERE user_id = $user_id and exam_id = $exam_id");
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

function ifPersonExist($cedula){
  $result = array('existe'=>false,'idPerson' =>null);  
  $resultado = mysqli_query($GLOBALS['conn'], "SELECT count(*) as existe, id FROM person WHERE cedula = '$cedula'");
  $row = mysqli_fetch_assoc($resultado);
  if($row['existe'] > 0){
    $result['existe'] = true;
    $result['idPerson'] = $row['id'];
  }
  return $result;	
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


function returnPersonName($person_id) {
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT name FROM person WHERE id = $person_id");
    $row = mysqli_fetch_assoc($resultado);
    
    return $row['name']; // Devuelve solo el valor del nombre
}




function returnUnitName($id) {
    global $conn; // Asegúrate de que $conn está declarado como global
    $resultado = mysqli_query($conn, "SELECT name FROM units WHERE id = $id");
    if ($resultado) {
        $row = mysqli_fetch_assoc($resultado);
        return $row['name']; // Devuelve solo el nombre de la unidad
    } else {
        return null; // O manejar el error adecuadamente
    }
}



function returnUnitOrder($id) {
    global $conn; // Asegúrate de que $conn está declarado como global
    $resultado = mysqli_query($conn, "SELECT unit_order FROM units WHERE id = $id");
    if ($resultado) {
        $row = mysqli_fetch_assoc($resultado);
        return $row['unit_order']; // Devuelve solo el valor de unit_order
    } else {
        return null; // O manejar el error adecuadamente
    }
}

function returnLessons($unit_id)
{
    $obj = array();
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT title,id,lesson_order FROM lessons WHERE unit_id = '$unit_id' AND isDeleted=0");
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

function returnExams($unit_id)
{
    $obj = array();
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT title,id,exam_order FROM exams WHERE unit_id = '$unit_id' ORDER BY exam_order ASC");
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $obj[] = array(
                'title' => $row['title'],
                'exam_order' => $row['exam_order'],
                'id' => $row['id'],
            );
        }
    }
    return $obj;
}

function returnSingleExam($exam_id)
{
    $obj = array();
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT title,exam_order FROM exams WHERE id =$exam_id ORDER BY exam_order ASC");
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $obj = array(
                'title' => $row['title'],
                'exam_order' => $row['exam_order']
            );
        }
    }
    return $obj;
}

function returnExamUnit($exam_id) {
    global $conn; // Asegúrate de que $conn está declarado como global
    $resultado = mysqli_query($conn, "SELECT unit_id FROM exams WHERE id = $exam_id");
    if ($resultado) {
        $row = mysqli_fetch_assoc($resultado);
        return $row['unit_id']; // Devuelve el unit_id
    } else {
        return null; // O manejar el error adecuadamente
    }
}



function returnExamQuestions($exam_id)
{
    $obj = array('totalQuestionMark'=>0,'count'=>0,'question'=>array(),'data_exam'=>array());
    $resultado = mysqli_query($GLOBALS['conn'], "SELECT id,exam_id,text,question_order,question_mark FROM questions WHERE exam_id = '$exam_id'");
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
                'question_order' => $row['question_order'],
                'question_mark' => $row['question_mark'],
                'question_data' => $question_data                
            );
            $obj['question'] = $question;
            $obj['data_exam'] = $dataExam;
            $obj['count'] = row_sqlconector("SELECT COUNT(*) as suma FROM questions WHERE exam_id={$row['exam_id']}")['suma'];
            $obj['totalQuestionMark'] = row_sqlconector("SELECT SUM(question_mark) as suma FROM questions WHERE exam_id={$row['exam_id']}")['suma'];
        }
    } else {
        // Si no hay preguntas, aún debemos obtener los datos del examen
        $dataExam = row_sqlconector("SELECT * FROM exams WHERE id='$exam_id'");
        $obj['data_exam'] = $dataExam ? $dataExam : array();
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

function addToHistory($user_id, $action) {
    global $conn; // Necesario para acceder a la variable $conn
    $query = "INSERT INTO user_history (user_id,action) VALUES ($user_id,'$action')"; // Usa comillas simples para los valores de texto
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
    }

    return array("message" => "ok"); // Devuelve el array en lugar de hacer echo
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

        if(isset($data['updateSingleFieldUnit'])){ /* Actualiza segun un campo con su valor y  la tabla requerida*/

			$campo = mysqli_real_escape_string($conn, $data['campo']);
			$valor = mysqli_real_escape_string($conn, strtolower($data['valor']));
			$tabla = mysqli_real_escape_string($conn, $data['tabla']);
			$whereCondition =	 mysqli_real_escape_string($conn, $data['whereCondition']);

			
            $query = "UPDATE $tabla SET $campo = $valor WHERE $whereCondition";
            $result = mysqli_query($conn, $query);

                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha desabilitado una unidad";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial
            
            
            if (!$result) {
                // Error en la consulta
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
            }
			
			$response = array("message" => "ok");
			echo json_encode($response);
		}

        if(isset($data['updateSingleFieldLesson'])){ /* Actualiza segun un campo con su valor y  la tabla requerida*/

			$campo = mysqli_real_escape_string($conn, $data['campo']);
			$valor = mysqli_real_escape_string($conn, strtolower($data['valor']));
			$tabla = mysqli_real_escape_string($conn, $data['tabla']);
			$whereCondition =	 mysqli_real_escape_string($conn, $data['whereCondition']);

			
            $query = "UPDATE $tabla SET $campo = $valor WHERE $whereCondition";
            $result = mysqli_query($conn, $query);

                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha desabilitado una Lección";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial
            
            
            if (!$result) {
                // Error en la consulta
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
            }
			
			$response = array("message" => "ok");
			echo json_encode($response);
		}

        if(isset($data['updateSingleFieldExam'])){ /* Actualiza segun un campo con su valor y  la tabla requerida*/

			$campo = mysqli_real_escape_string($conn, $data['campo']);
			$valor = mysqli_real_escape_string($conn, strtolower($data['valor']));
			$tabla = mysqli_real_escape_string($conn, $data['tabla']);
			$whereCondition =	 mysqli_real_escape_string($conn, $data['whereCondition']);

			
            $query = "UPDATE $tabla SET $campo = $valor WHERE $whereCondition";
            $result = mysqli_query($conn, $query);

                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha desabilitado un Examen";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial
            
            
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



        if(isset($data['updateBlockUser'])) {
            // Limpia el buffer de salida antes de cualquier echo
            ob_clean();
        
            $campo = mysqli_real_escape_string($conn, $data['campo']);
            $valor = mysqli_real_escape_string($conn, strtolower($data['valor']));
            $tabla = mysqli_real_escape_string($conn, $data['tabla']);
            $query = "UPDATE $tabla SET $campo = $valor WHERE user_id=".$data['updateBlockUser'];
            $result = mysqli_query($conn, $query);
        
            if (!$result) {
                // Error en la consulta
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
            }

                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha bloqueado a un usuario";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial
        
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


        if(isset($data['updateEraseUser'])) {
            // Limpia el buffer de salida antes de cualquier echo
            ob_clean();
        
            $campo = mysqli_real_escape_string($conn, $data['campo']);
            $valor = mysqli_real_escape_string($conn, strtolower($data['valor']));
            $tabla = mysqli_real_escape_string($conn, $data['tabla']);
            $query = "UPDATE $tabla SET $campo = $valor WHERE user_id=".$data['updateEraseUser'];
            $result = mysqli_query($conn, $query);
        
            if (!$result) {
                // Error en la consulta
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
            }

                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha borrado a un usuario";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial
        
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
            $date = date('Y-m-d');  // Obtener solo la fecha en formato 'YYYY-MM-DD'
            $hashContrasena = password_hash($data['userData']['password'], PASSWORD_BCRYPT);
            $QinsertUser = "INSERT INTO user (person_id, email, password,date) VALUES ($endIdPerson, '".$data['userData']['email']."', '$hashContrasena','$date')";
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
                            'person_id' => $row['person_id'],
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

                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha añadido una unidad";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial

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
        $lesson_id = null;
    
        $title = mysqli_real_escape_string($conn, strtolower($data['lesson']['title']));
        $unit_id = mysqli_real_escape_string($conn, strtolower($data['lesson']['unitId']));
        $lesson_order = mysqli_real_escape_string($conn, strtolower($data['lesson']['lesson_order']));
        $summary = mysqli_real_escape_string($conn, strtolower($data['lesson']['summary']));
        $url = mysqli_real_escape_string($conn, strtolower($data['lesson']['url']));
    
        if (ifLessonExists($lesson_order, $unit_id) == true) {
            $message = 'Cuidado: Esta lección ya tiene un numero asignado';
            $icon = 'warning';
        } else {
            $query = "INSERT INTO lessons (title, unit_id, lesson_order, summary, url) VALUES ('$title', '$unit_id', $lesson_order, '$summary', '$url')";
            $result = mysqli_query($conn, $query);

                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha creado una Lección";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial
    
            if (!$result) {
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                $message = 'Error';
            } else {
                $lesson_id = mysqli_insert_id($conn); // Obtener el ID de la última lección añadida
                $message = 'Lección Añadida con Éxito';
                $icon = 'success';
            }
        }
        $response = array('message' => $message, 'icon' => $icon, 'lesson_id' => $lesson_id);
        echo json_encode($response);
    }
    

        if (isset($data['editUnit'])) {
            $subjectExist = false;
            $message = '';
            $icon = '';
        
            // Escapa los valores para evitar inyección de SQL
            $id = mysqli_real_escape_string($conn, $data['unit']['id']);
            $name = mysqli_real_escape_string($conn, strtolower($data['unit']['name']));
            $order = mysqli_real_escape_string($conn, strtolower($data['unit']['order']));
        
            // Otros campos...
        
            // Actualizar la unidad
            $query = "UPDATE units SET name='$name', unit_order='$order' WHERE id='$id'";
            $result = mysqli_query($conn, $query);
        
            // Historial
            $historyName= returnPersonName($data['history']['person_id']);
            $texto = returnPersonName($data['history']['person_id'])." ha editado una unidad";
            $historyResponse = addToHistory($data['history']['user'], $texto);
            //Fin Historial


            if (!$result) {
                throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                $message = 'Error';
                $icon = 'error';
            } else {
                $message = 'Unidad editada con éxito';
                $icon = 'success';
            }
        
            // Respuesta
            $response = array('message' => $message, 'icon' => $icon, 'history' => $historyResponse);
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

                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha editado una Lección";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial

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
        $fileExtension = pathinfo($name, PATHINFO_EXTENSION);
        $allowedExtensions = ['pdf', 'docx'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['status' => 'error', 'file' => $name, 'message' => 'Es un tipo invalido. Los unicos tipos validos son PDF Y WORD']);
            continue; // Saltar al siguiente archivo si la extensión no es válida
        }

        if (ifFileExists($name, $lesson_id)) {
            echo json_encode(['status' => 'exists', 'file' => $name, 'message' => 'El archivo ya existe.']);
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


if (isset($_POST['addVideo']) && $_POST['addVideo'] === 'true') {
    $lesson_id = $_POST['lesson_id'];
    $uploadDirectory = 'videos/';
    $response = [];
    $key = 0; // Esperamos solo un archivo

    $name = $_FILES['files']['name'][$key];
    $fileExtension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($fileExtension !== 'mp4') {
        $response[] = ['status' => 'error', 'file' => $name, 'message' => 'Only .mp4 files are allowed'];
        echo json_encode($response);
        exit;
    }

    if (ifFileExists($name, $lesson_id)) {
        $response[] = ['status' => 'exists', 'file' => $name, 'message' => 'File already exists'];
        echo json_encode($response);
        exit;
    }

    $tmpName = $_FILES['files']['tmp_name'][$key];
    $filePath = $uploadDirectory . basename($name);
    error_log("Intentando mover archivo: $tmpName a $filePath");

    if (move_uploaded_file($tmpName, $filePath)) {
        chmod($filePath, 0777);
        $query = "INSERT INTO videos (lesson_id, file, name) VALUES ('$lesson_id', '$filePath', '$name')";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $response[] = ['status' => 'success', 'file' => $name, 'message' => 'File uploaded successfully'];
        } else {
            error_log("Error al insertar en la base de datos: " . mysqli_error($conn));
            $response[] = ['status' => 'error', 'message' => 'DB insert failed'];
        }
    } else {
        error_log("Error al mover el archivo.");
        $response[] = ['status' => 'error', 'message' => 'File move failed'];
    }
    echo json_encode($response);
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

                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha editado un usuario";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial

				if (!$result) {
					throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
					$message = 'Error';
				}

			$response = array('message' => $message);
			echo json_encode($response);
		}  


        if (isset($data['editProfile'])) {
            
            ini_set('display_errors', 1); 
            ini_set('display_startup_errors', 1); 
            error_reporting(E_ALL);        
            
            $message = 'Datos actualizados correctamente';
            $icon= 'success';
            // Editar información del usuario
        
            // Editar información de la persona
                $personId = $data['person']['id'];
                $cedula = mysqli_real_escape_string($conn, $data['person']['cedula']);
        
                // Verifica si la cédula ya existe antes de continuar
               if (ifPersonExist($cedula)['existe'] && ifPersonExist($cedula)['idPerson'] != $personId) {
                    $icon= 'error';
                    $message = "La cédula ya está registrada a otro Usuario.";
                } 
                else {
                    $nationality = mysqli_real_escape_string($conn, $data['person']['nationality']);
                    $name = mysqli_real_escape_string($conn, strtolower($data['person']['name']));
                    $second_name = mysqli_real_escape_string($conn, strtolower($data['person']['second_name']));
                    $last_name = mysqli_real_escape_string($conn, strtolower($data['person']['last_name']));
                    $second_last_name = mysqli_real_escape_string($conn, strtolower($data['person']['second_last_name']));
                    $phone = mysqli_real_escape_string($conn, $data['person']['phone']);
                    $address = mysqli_real_escape_string($conn, strtolower($data['person']['address']));
                    $gender = mysqli_real_escape_string($conn, $data['person']['gender']);
                    $birthday = mysqli_real_escape_string($conn, $data['person']['birthday']);
        
                    $personQuery = "UPDATE person SET 
                                    cedula='$cedula',
                                    nationality='$nationality',
                                    name='$name',
                                    second_name='$second_name',
                                    last_name='$last_name',
                                    second_last_name='$second_last_name',
                                    phone='$phone',
                                    address='$address',
                                    gender='$gender',
                                    birthday='$birthday' 
                                  WHERE id=$personId";
                    $personResult = mysqli_query($conn, $personQuery);
        
                    if (!$personResult) {
                        $message = 'Error en la consulta SQL de persona: ' . mysqli_error($conn);
                    }
                    else{
                        $icon= 'success';
                        $message = "Datos Actualizados con Exito.!";
                    }
                }            
        
            // Envía la respuesta
            $response = array('message' => $message,'icon' => $icon); 
            $jsonResponse = json_encode($response); 
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo 'Error en la codificación JSON: ' . json_last_error_msg();
            }else {
                echo $jsonResponse;
            }

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
                $date = date('Y-m-d');  // Obtener solo la fecha en formato 'YYYY-MM-DD'
                $hashContrasena = password_hash($data['userData']['password'], PASSWORD_BCRYPT);
                $QinsertUser = "INSERT INTO user (person_id, email, password,isAdmin,date) VALUES ($endIdPerson, '".$data['userData']['email']."', '$hashContrasena',".$data['userData']['isAdmin'].",'$date')";
                $result = mysqli_query($conn, $QinsertUser);
                if (!$result) {
                    throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                }
                $message = 'Usuario añadido con éxito...';
                $icon = 'success';
                // Historial
                $historyName= returnPersonName($data['history']['person_id']);
                $texto = returnPersonName($data['history']['person_id'])." ha creado un usuario";
                $historyResponse = addToHistory($data['history']['user'], $texto);
                //Fin Historial
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
                    
                    
                    // Historial
                    $historyName= returnPersonName($data['history']['person_id']);
                    $texto = returnPersonName($data['history']['person_id'])." ha creado un examen";
                    $historyResponse = addToHistory($data['history']['user'], $texto);
                    //Fin Historial
    
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

                    
                    // Historial
                    $historyName= returnPersonName($data['history']['person_id']);
                    $texto = returnPersonName($data['history']['person_id'])." ha editado un examen";
                    $historyResponse = addToHistory($data['history']['user'], $texto);
                    //Fin Historial
    
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
                // ...otros campos    

                    $query = "INSERT INTO questions (exam_id,question_order,question_mark,text) VALUES ($exam_id,$question_order,$question_mark,'$text')";
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



        if (isset($data['addMark'])) {

            $message = '';
            $icon = '';
    
                // Escapa los valores para evitar inyección de SQL
                $score = mysqli_real_escape_string($conn, strtolower($data['score']));
                $exam_id =  mysqli_real_escape_string($conn, strtolower($data['exam_id']));
                $user_id =  mysqli_real_escape_string($conn, strtolower($data['user_id']));
    
                if (ifMarkExists($user_id,$exam_id) == true) {

                    $message ='Cuidado: Esta lección ya tiene un numero asignado ';
                    $icon = 'warning';	

                } else{
                    $query = "INSERT INTO exam_scores (score,exam_id,user_id) VALUES ($score,$exam_id,$user_id)";
                    $result = mysqli_query($conn, $query);
    
                    if (!$result) {
                        throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
                        $message = 'Error';
                    }
                }

                $response = array('message' => $message,'icon'=>$icon);
                echo json_encode($response);
            }




}
catch (Exception $e) {		
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
    $consulta = "SELECT * FROM lessons WHERE unit_id = $unit_id AND isDeleted=0 ORDER BY lesson_order ASC";
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

if(isset($_GET['evaluation_exams_data'])){
    $exam_id = $_GET['id'];
        $obj = array('question'=>array(),'data_exam'=>array());
        $resultado = mysqli_query($GLOBALS['conn'], "SELECT id,exam_id,text,question_order,question_mark FROM questions WHERE exam_id = '$exam_id'");
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $question_data = array();
                $dataExam = row_sqlconector("SELECT * FROM exams WHERE id={$row['exam_id']}");
                $checkbox_true = row_sqlconector("SELECT COUNT(*) as suma FROM questions_data WHERE question_id={$row['id']} AND type='checkbox' AND true_response='true'")['suma'];

                $data = mysqli_query($GLOBALS['conn'], "SELECT * FROM questions_data WHERE question_id = '{$row['id']}'");
                while ($row_data = mysqli_fetch_assoc($data)) {
                    $question_data[]=array(
                        'checkbox_true' => $checkbox_true,
                        "id" => $row_data['id'],
                        "question_id" => $row_data['question_id'],
                        "exam_id" => $row_data['exam_id'],
                        "answer" => $row_data['answer'],
                        "type" => $row_data['type'],
                        "true_response" => $row_data['true_response']
                    );

                }
                $question[] = array(
                    'question_order' => $row['question_order'],
                    'id' => $row['id'],                
                    'exam_id' => $row['exam_id'],                
                    'text' => $row['text'],
                    'question_mark' => $row['question_mark'],
                    'question_data' => $question_data                
                );
                $obj['question'] = $question;
                $obj['data_exam'] = $dataExam;
            }
        } else {
            // Si no hay preguntas, aún debemos obtener los datos del examen
            $dataExam = row_sqlconector("SELECT * FROM exams WHERE id='$exam_id'");
            $obj['data_exam'] = $dataExam ? $dataExam : array();
        }
    
        echo json_encode($obj);
}


if (isset($_GET['this_specific_lesson_list'])) {
    $unit_id = $_GET['id'];
    $lesson_order = $_GET['lesson_order'];
    $obj = array();
    $consulta = "SELECT * FROM lessons WHERE unit_id = $unit_id AND lesson_order = $lesson_order AND isDeleted=0";
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


if (isset($_GET['this_lessons_videos'])) {
    $lesson_id = $_GET['id'];
    $obj = array();
    $consulta = "SELECT * FROM videos WHERE lesson_id = $lesson_id";
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


if(isset($_GET['this_user_list'])){
    $obj = array();
    $id = $_GET['id'];
    $consulta = "SELECT * FROM user where user_id=$id";
    $resultado = mysqli_query($conn, $consulta);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while($row = mysqli_fetch_assoc($resultado)) {      
            $obj=array('user_id'=>$row['user_id'],'person_id'=>returnDatPerson($row['person_id']),'password'=>$row['password'],'isAdmin'=>$row['isAdmin'],'email'=>$row['email'],'isBlocked'=>$row['isBlocked']);
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
                'lessons' => array(),
                'exams' => returnExams($rowUnidad['id'])
            );

            $unit_id = $rowUnidad['id'];
            $consultaLecciones = "SELECT * FROM lessons WHERE unit_id = $unit_id AND isDeleted=0 ORDER BY lesson_order ASC";
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



    if (isset($_GET['dashboard_cards'])) {
        // Consultas SQL
        $consulta_user = "SELECT COUNT(*) AS total_entries FROM user";
        $consulta_lessons = "SELECT COUNT(*) AS total_entries FROM lessons WHERE isDeleted=0";
        $consulta_units = "SELECT COUNT(*) AS total_entries FROM units";
        $consulta_exams = "SELECT COUNT(*) AS total_entries FROM exams";
    
        // Objeto de resultados
        $obj = array(
            'total_user' => row_sqlconector($consulta_user)['total_entries'],
            'total_lessons' => row_sqlconector($consulta_lessons)['total_entries'],
            'total_units' => row_sqlconector($consulta_units)['total_entries'],
            'total_exams' => row_sqlconector($consulta_exams)['total_entries']
        );
    
        echo json_encode($obj);
    }

    if (isset($_GET['mark_list'])) {
        $user_id = $_GET['user_id'];
        $obj = array();
        $consulta = "SELECT * FROM exam_scores WHERE user_id=$user_id";
        $resultado = mysqli_query($conn, $consulta);
        
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $obj = array(); // Inicializar $obj como un array vacío
    
            while ($row = mysqli_fetch_assoc($resultado)) {
                $exam_unit = returnExamUnit($row['exam_id']);
                $unit_name = returnUnitName($exam_unit); // Obtener el nombre de la unidad
                $unit_order = returnUnitOrder($exam_unit);
    
                $obj[] = array( // Agregar los datos al array $obj
                    'unit_name' => $unit_name,
                    'unit_order' => $unit_order,  // Concatenar el nombre y el orden de la unidad
                    'exam' => returnSingleExam($row['exam_id']), // Quitar la coma adicional
                    'score' => $row['score']
                );
            }
        }
    
        echo json_encode($obj);
    }



    

if(isset($_GET['history_data'])){
    $obj = array();
    $consulta = "SELECT * FROM user_history";
    $resultado = mysqli_query($conn, $consulta);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while($row = mysqli_fetch_assoc($resultado)) {      
            $obj[]=array('action'=>$row['action'],'date'=>$row['date']);
        }   
    }
    echo json_encode($obj); 
}
    
    
if(isset($_GET['getUsersByMonth'])){
    $obj = array();
    $consulta = "SELECT DATE_FORMAT(date, '%Y-%m') AS month, DATE_FORMAT(date, '%M') AS month_name, COUNT(*) AS user_count FROM user GROUP BY month ORDER BY month";
    $resultado = mysqli_query($conn, $consulta);

    if (!$resultado) {
        die("Error en la consulta SQL: " . mysqli_error($conn));
    }

    $englishToSpanishMonths = array(
        'January' => 'Enero',
        'February' => 'Febrero',
        'March' => 'Marzo',
        'April' => 'Abril',
        'May' => 'Mayo',
        'June' => 'Junio',
        'July' => 'Julio',
        'August' => 'Agosto',
        'September' => 'Septiembre',
        'October' => 'Octubre',
        'November' => 'Noviembre',
        'December' => 'Diciembre'
    );

    while ($row = mysqli_fetch_assoc($resultado)) {
        $month_name_es = $englishToSpanishMonths[$row['month_name']]; // Traduce el nombre del mes al español

        $obj[] = array(
            'month' => $row['month'],
            'month_name' => $month_name_es,
            'user_count' => $row['user_count']
        );
    }

    echo json_encode($obj); 
}




}









