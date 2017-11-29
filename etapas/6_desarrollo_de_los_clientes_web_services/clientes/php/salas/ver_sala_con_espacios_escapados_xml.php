<?php

	// variables 
	$get = 'http://localhost:8004/salas/ver';
	$format = 'xml';
	$vars = [
		// id_efector
		1 => '292',
		
		// nombre_sala
		2 => 'medicina general mujeres',
		
	];
	
	foreach ($vars as $var){
		
		$get .= '/'.$var;
		
	}
	$get.='.'.$format;
	
	// reemplaza espacios
	$get = str_replace ( ' ', '%20', $get);
	
	// inicializa cUrl
	$ch = curl_init($get);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// send request
	$response = curl_exec($ch);
	$code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	
	if (!$response){
		die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
	}
	
	// cierra conexión, libera recursos
	curl_close($ch);

	// response
	echo 
        'Codigo HTTP: '
        .$code
        .' - Contenido: '
        .htmlspecialchars($response);

?>
