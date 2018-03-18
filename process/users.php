<?php
include('../assets/php/connect.php');


// Obtener información del archivo a subir...
if($_POST['accion'] == 'get_file_info'){
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){		
		$data = array(
			'file_name' => $_FILES['archivo']['name'],
			'file_type' => $_FILES['archivo']['type'],
			'file_temp' => $_FILES['archivo']['tmp_name'],
			'file_size' => $_FILES['archivo']['size'],
			'status' => 'SUCCESS'
		);
	}
	else{
		$data['status'] = 'ERROR';
		$data['error'] = 'Error al subir el archivo seleccionado.';
	}
$json_data = json_encode($data);
echo $json_data;
}



// Agregar nuevo usuario...
if($_POST['accion'] == 'add_user'){
$user_dir = sha1(trim($_POST['correo'] . strftime('%F %T')));
$user_path = '../uploads/' . $user_dir . '/';
$done = false;
	
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
		if(mkdir($user_path, 0755, true)){
			if($_FILES['archivo']['name'] && move_uploaded_file($_FILES['archivo']['tmp_name'], $user_path . $_FILES['archivo']['name'])){
				
				// Insertar registro en la BD...
				$sql = "INSERT INTO usuarios (nombre, apellido, correo, archivo, archivo_size, archivo_tipo, archivo_dir) VALUES ('";
				$sql .= utf8_decode($_POST['nombre']) . "', '" . utf8_decode($_POST['apellido']) . "', '" . utf8_decode($_POST['correo']);
				$sql .= "', '" . $_FILES['archivo']['name'] . "', " . $_FILES['archivo']['size'] . ", '" . $_FILES['archivo']['type'];
				$sql .= "', '" . $user_dir . "');";

				// Enviar array con la respuesta...
				if(mysqli_query($_conn, $sql)){
					$data = array(
						'nombre' => $_POST['nombre'],
						'apellido' => $_POST['apellido'],
						'correo' => $_POST['correo'],
						'archivo' => array(
							'nombre' => $_FILES['archivo']['name'],
							'tipo' => $_FILES['archivo']['type'],
							'temporal' => $_FILES['archivo']['tmp_name'],
							'peso' => $_FILES['archivo']['size'],
							'path_dir' => $user_dir
						),
						'status' => 'SUCCESS'
					);
					$done = true;
				}
			}
		}
	}
	if($done == false){
		$data['status'] = 'ERROR';
		$data['error'] = 'Error al crear usuario.';
	}
mysqli_close($_conn);
$json_data = json_encode($data);
echo $json_data;
}



// Editar usuario existente...
if($_POST['accion'] == 'edit_user'){
$sql = "UPDATE usuarios SET nombre='" . utf8_decode($_POST['nombre']) . "', apellido='" . utf8_decode($_POST['apellido']);
$sql .= "', correo='" . utf8_decode($_POST['correo']) . "' WHERE id=" . $_POST['id_usuario'] . " LIMIT 1;";
$done = false;
	if(mysqli_query($_conn, $sql)){
		$data['status'] = 'SUCCESS';
		$done = true;
	}
	if($done == false){
		$data['status'] = 'ERROR';
		$data['error'] = 'Error al actualizar datos de usuario.';
	}
mysqli_close($_conn);
$json_data = json_encode($data);
echo $json_data;
}



// Eliminar un usuario...
if($_POST['accion'] == 'delete_user'){
$sql = "DELETE FROM usuarios WHERE id=" . $_POST['id_usuario'] . " LIMIT 1;";
$done = false;
	if(mysqli_query($_conn, $sql)){
		$data['status'] = 'SUCCESS';
		$done = true;
		$file_dir = '../uploads/' . $_POST['archivo_dir'];
		$file_path = $file_dir . '/' . $_POST['archivo'];
			if(file_exists($file_path))
				unlink($file_path);
			if(is_dir($file_dir))
				rmdir($file_dir);
	}
	if($done == false){
		$data['status'] = 'ERROR';
		$data['error'] = 'Error al eliminar usuario.';
	}
mysqli_close($_conn);
$json_data = json_encode($data);
echo $json_data;
}





// Obtener datos de un usuario... 
if($_REQUEST['accion'] == 'get_user'){
$sql = "SELECT * FROM usuarios WHERE id=" . trim($_REQUEST['id_usuario']) . " LIMIT 1;";
$done = false;
	if($rs = mysqli_query($_conn, $sql)){
		while ($row = mysqli_fetch_assoc($rs)){
			$data = array(
				'nombre' => utf8_encode($row['nombre']),
				'apellido' => utf8_encode($row['apellido']),
				'correo' => utf8_encode($row['correo']),
				'archivo' => $row['archivo'],
				'archivo_dir' => $row['archivo_dir'],
				'status' => 'SUCCESS'
			);
			$done = true;
		}
	mysqli_free_result($rs);
	}
	if($done == false){
		$data = array(
			'status' => 'ERROR',
			'error' => 'No se he encontrado el usuario seleccionado.'
		);
	}
mysqli_close($_conn);
$json_data = json_encode($data);
echo $json_data;
}


?>