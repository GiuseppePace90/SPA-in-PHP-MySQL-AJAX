<?php

/*
	Richiedo la connessione al database.
*/
require('config.php');

/*
	Mi assicuro che le variabili obbligatorie e non obbligatorie sia settate, tranne la search_keyword, di cui mi occupo più avanti.
*/
if(isset($_GET['idNode']) && isset($_GET['language']) || isset($_GET['page_num']) || isset($_GET['page_size'])) {

/*
	Definisco le variabili così settate come nuove variabili PHP. Per quelle provenienti da campi di testo faccio l'escape di tutti i valori non numerici. Mi assicuro che il replace dia numeri in formato stringa diversi da 0 e non interi in modo da indurre in errore un input non conforme.
*/
	$idNode_input = $_GET['idNode'];

	$language_input = $_GET['language'];

	$page_num_input = str_replace(',', '.', $_GET['page_num']);
	preg_match_all('/[a-z]/i',$page_num_input,$matches,PREG_PATTERN_ORDER);

        foreach ($matches[0] as $match) {
            $page_num_input = str_replace($match,'1.1',$page_num_input);
        } 
        
	$page_size_input = str_replace(',', '.', $_GET['page_size']);
	preg_match_all('/[a-z]/i',$page_size_input,$matche,PREG_PATTERN_ORDER);

        foreach ($matche[0] as $match) {
            $page_size_input = str_replace($match,'1.1',$page_size_input);
        } 

/*
    Impongo i vincoli di progetto. Per le varibili $page_num_input e $page_size_input ho pilotato la conversione da stringa a numero con una banale divisione per 1.
*/
	if($idNode_input < 0 || $idNode_input > 12) {
	    
	    $errore = 'ID nodo non valido';
	    
	} else if($idNode_input == 0 && $language_input !== 'empty') {
	    
	    $errore = 'ID nodo non valido';
        
	} else if($idNode_input == 0 || $language_input == 'empty') {
	    
	    $errore = 'Parametri obbligatori mancanti';
        
	} else if(is_int($page_num_input/1) == false) {
	    
	    $errore = 'Numero di pagina richiesto non valido';
	    
	} else if(is_int($page_size_input/1) == false || $page_size_input < 0 || $page_size_input > 1000) {
	    
	    $errore = 'Richiesto formato pagina non valido';
	    
	}

/*
    Definisco gli arrays dove andrò a inserire gli elementi da richiamare in JSON.
*/
    $json_nodes = array();   
    $json_pages = array();
	
/*
    Interrogo il DB con puntamento alla tabella node_tree-Names, sia nel caso in cui la variabile search_keywork esista e non sia vuota, sia nel caso opposto, ricavando in entrambi i casi -tramite array associativo- l'ID del o dei nodi in questione, nella lingua selezionata, con cui andrò a filtrare i risultati finali.
*/	
    if(!isset($_GET['search_keyword']) || empty($_GET['search_keyword'])) {
    
	    $query_slt_idNode = "SELECT idNode 
	                         FROM node_tree_names 
	                         WHERE idNode='$idNode_input' AND language='$language_input'";
		$query_stmt_idNode = $db->prepare($query_slt_idNode);
		$query_stmt_idNode->execute();
		    
		$result_idNode = $query_stmt_idNode->fetchAll(PDO::FETCH_ASSOC);
	
	
    } elseif(isset($_GET['search_keyword']) || !empty($_GET['search_keyword'])) {
    
	    $search_keyword = $_GET['search_keyword'];
		$query_slt_idNode = "SELECT idNode 
	                         FROM node_tree_names 
	                         WHERE language='$language_input' AND NodeName LIKE '%$search_keyword%'";
		$query_stmt_idNode = $db->prepare($query_slt_idNode);
		$query_stmt_idNode->execute();
		    
		$result_idNode = $query_stmt_idNode->fetchAll(PDO::FETCH_ASSOC);
	
    }

/*
    Per ogni elemento dell'array $result_idNode, contenente l'ID di tutti i nodi selezionati, interrogo il DB alla ricerca degli eventuali nodi figli, degli estremi e del livello di profondità, usando l'ID importato tramite AJAX e quelli contenuti nell'array di cui sopra per filtrare i risultati dei nodi genitori e dei nodi figli.
*/    
	foreach($result_idNode as $array_idNode_keys) {
	
	    $idNode = $array_idNode_keys['idNode'];
    
    	$query_slt_node = "SELECT Child.idNode, Child.iLeft, Child.iRight, Child.level, (COUNT(Parent.idNode) - 1), (COUNT(Child.idNode) - 1) AS count
                           FROM node_tree AS Child, node_tree AS Parent
                           WHERE Child.iLeft BETWEEN Parent.iLeft AND Parent.iRight AND IF (Parent.idNode!='$idNode', Parent.idNode='$idNode_input' AND Child.idNode='$idNode', Parent.idNode='$idNode')  
                           GROUP BY Child.idNode
                           ORDER BY Child.iLeft";
    	$query_stmt_node = $db->prepare($query_slt_node);
    	$query_stmt_node->execute();
    	
/*
    Formalizzo i risultati in un array associativo e ad ogni elemento al suo interno associo una variabile PHP. Uso quindi gli estremi per ricavare il numero di nodi figli di ogni nodo genitore.
*/     	
    	while ($result_node = $query_stmt_node->fetch(PDO::FETCH_ASSOC)) {
    	    
    	    $idNode_output = $result_node['idNode'];
    	    $iLeft = $result_node['iLeft'];
            $iRight = $result_node['iRight'];
            $level = $result_node['level'];
            $countChild = ($iRight - ($iLeft + 1))/2;

/*
    Impongo che se un nodo genitore non ha figli restituisca 0 oppure il numero di nodi figli.
*/         
            if($countChild == 0) {
                $new_countChild = 0;
            } elseif($countChild > 0) {
                $new_countChild = $countChild;
            }

/*
    Interrogo il DB con puntamento alla tabella node_tree_names per selezionare stavolta tutti i nomi dei nodi precedentemente selezionati, nella lingua impostata.
*/          
            $query_slt_nodeName = "SELECT NodeName 
                                   FROM node_tree_names 
                                   WHERE idNode='$idNode_output' AND language='$language_input'";
        	$query_stmt_nodeName = $db->prepare($query_slt_nodeName);
        	$query_stmt_nodeName->execute();
        	$result_nodeName = $query_stmt_nodeName->fetchAll(PDO::FETCH_ASSOC);

/*
    Per ogni elemento trovato mi assicuro che il valore sia diverso da null, quindi inizio a popolare un array di comodo che uso per veicolare i parametri fuori dal while-loop, all'interno dell'array $json_nodes. Rispetto a quanto richiesto ho estrapolato anche il parametro level per una migliore formattazione finale dei risultati.
*/     	
        	foreach($result_nodeName as $array_nodeName_keys) {
        	    
            	$NodeName = $array_nodeName_keys['NodeName'];
        
        	    if($NodeName !== null) {
        	        
                    $nodes = array();
                    
                    $nodes['node_id'] = $idNode_output;
                    $nodes['name'] = $NodeName;
                    $nodes['children_count'] = $new_countChild;
                    $nodes['level'] = $level;
                    
                    $json_nodes[] = $nodes;
                
        	    }
        	}
        }
    }

/*
    Verifico che gli elementi dell'array superiano il valore di record per pagina, mi assicuro che in caso di valore decimale venga resituito l'intero corretto che garantisca una pagina ai record residui, quindi in modo analogo a prima inizio a popolare l'array $json_pages che contiene il numero delle pagine ottenute stando al numero di records e di records per pagina da visualizzare.
*/      
    if(count($json_nodes)>$page_size_input) {
        
        $page_count = count($json_nodes)/$page_size_input;
                
        $page_num = (round($page_count)<$page_count) ? (round($page_count) + 1) : round($page_count);
        
        $pages = array();
                
            for ($i=0; $i<=$page_num; $i++) {
            
                array_push($pages,$i);
                
            }
            
        $json_pages[] = $pages;

/*
    Verifico che il numero della pagina selezionato sia compatibile con le pagine create e, se risulta compreso fra due pagine esistenti, identifico il valore di entrambe. Tali valori verranno esportati in JSON per creare i pulsanti "Pagina precedente" e "Pagina successiva" lato Front-End.
*/
        if($page_num_input < (count($json_pages[0]) - 2)) {
            
            $next_page = $json_pages[0][$page_num_input] + 1;
            
        }
        
        if($page_num_input > 0 && $page_num_input <= (count($json_pages[0]) - 1)){
            
            $prev_page = $json_pages[0][$page_num_input] - 1;
            
        }

    }

/*
    Creo la paginazione dei risultati, sezionando l'array che contiene tutti i nodi, in funzione dei parametri di input.
*/
    if($page_num_input == 0) {
        
        $json_nodes = array_slice($json_nodes, 0, $page_size_input);
        
    } else if ($page_num_input > 0) {
        
        $json_nodes = array_slice($json_nodes, ($page_num_input*$page_size_input), $page_size_input);

    }

/*
    A questo punto codifico le variabili di interesse come elementi di un array JSON che poi vado a recuperare tramite promise di AJAX lato Front-End.
*/
echo json_encode(array('nodes'=>$json_nodes,'next_page'=>$next_page,'prev_page'=>$prev_page,'error'=>$errore)); 

}

?>