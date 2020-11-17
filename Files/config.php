<?php

/* 
	Definisco le variabili necessarie all'accesso, dunque nome utente, password e nome del database
*/

$db_user = "user";
$db_pass = "password";
$db_name = "name_database";

/* 
	Tento la connessione creando una nuova istanza PDO, con puntamento all'host locale, set di caratteri e credenziali. Inserisco inoltre gli 
	attributi necessari per la segnalazione degli errori. Nel momento in cui viene generata un'eccezione effettuo un catching e passo tale 
	valore alla variabile $e che raccolgo col metodo getMessage().
*/

	try { 
		
		$db = new PDO('mysql:host=hostIP;dbname=' . $db_name . ';charset=utf8', $db_user, $db_pass);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	} catch (PDOException $e){
		
		exit($e->getMessage());
		
	}

?>
