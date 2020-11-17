<!DOCTYPE html>
<html>
<head>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<style>
    input{
        width:80px;
        height:16px;
    }
    
    select, button {
        width:80px;
        height:22px;
    }
</style>

<body>

<!-- Creo gli elementi HTML e HTML5 necessari -->
<form id="input_parameters" onsubmit="return enter();">
  <input type="number" id="idNode" placeholder="ID Node">
  <select id="language">
    <option value="empty">Lingua</option>
    <option value="italian">Italiano</option>
    <option value="english">English</option>
  </select>
  <input type="text" id="search_keyword" placeholder="Search Value">
  <input type="text" id="page_num" placeholder="Numero Pagina" value="0">
  <input type="text" id="page_size" placeholder="Numero risultati" value="100">
  <button type="submit">Invia</button>
</form>
<br>
<!-- Creo un elemento DIV dove visualizzare i risultati -->
<div class="results"></div>

<!-- Costruisco lo script che tramite metodo HTTP di tipo GET chiama in modo asincrono l'API di Back-End. -->
<script>
function enter() {

/* 
    Recupero i valori dai campi precedentemente creati e li passo come variabili Javascript 
*/
  var idNode = document.getElementById('idNode').value;
  var language = document.getElementById('language').value;
  var search_keyword = document.getElementById('search_keyword').value;
  var page_num = document.getElementById('page_num').value;
  var page_size = document.getElementById('page_size').value;

/* 
    Passo tali variabili all'API tramite chiamata AJAX 
*/
        $.ajax ({
          type: 'get',
          url: './api.php',
          data: {
            idNode:idNode,
            language:language,
            search_keyword:search_keyword,
            page_num:page_num,
            page_size:page_size
            },
          success: function(data) {

/* 
    Se la chiamata ha successo impongo che venga anzitutto ripulito il contenitore dei risultati, quindi converto la stringa JSON in oggetto Javascript 
*/           
            $('.results').empty();
            
            var data = JSON.parse(data);
            console.log(data);

/* 
    Se nella stringa non ho alcun messaggio di errore procedo con la formattazione dei risultati. 
*/               
            if(data.error == null) {
            
/* 
    Recupero la lunghezza della proprietà nodes con accesso condizionale mediante ? per byassare eventuali errori undefined. Per ogni elemento aggancio le 
    proprietà dell'oggetto richieste dal progetto (node_id, name, childre_count) al contenitore dei risultati. 
*/ 
                for(i=0; i<data.nodes?.length; i++) {
                        
                    $('.results').append(data.nodes[i].node_id + ', ' + data.nodes[i].name + ', ' + data.nodes[i].children_count + '<br><br>');
                        
                    var space = ('&nbsp;').repeat([8]);
                    var j = data.nodes[0].level;
                    var x = data.nodes[i+1]?.level-j;

/* 
    Analizzo i risultati per migliorare la formattazione tramite indentatura visibile 
*/                        
                    if (x == 2) {
                            
                        for(j=2; j<data.nodes[i+1]?.level; j++){ 
                     
                            $('.results').append(space + space + '|==>');
                            
                        }
                        
                    } else if (x == 1) {
                            
                        for(j; j<data.nodes[i+1]?.level; j++){ 
                            
                            $('.results').append(space + '|==>');
                            
                        }
                    }
                }
            
/* 
    Passo a verificare che tutti i risultati stiano nella stessa pagina. In caso contrario recupero le proprietà necessarie a creare i pulsanti di navigazione. 
*/
            if (data.prev_page !== null && data.next_page == null) {
                    
                $('.results').append('<span id="prev">< Pagina Precedente</span>');
                
            } else if(data.next_page !== null && data.prev_page == null) {
                
                $('.results').append('<span id="next">Pagina Successiva ></span>');
                
            } else if(data.next_page !== null && data.prev_page !== null) {
                
                $('.results').append('<span id="prev">< Pagina Precedente</span> | <span id="next">Pagina Successiva ></span>');
            }
            
/* 
    Passo a due funzioni il compito di modificare l'indicatore della pagina e rilanciare la funzione principale per restituire i nuovi risultati. 
*/
                $('#prev').css('cursor','pointer').on('click', function() {
                    document.getElementById('page_num').value= page_num - 1;
                    return enter();
                });
                
                $('#next').css('cursor','pointer').on('click', function() {
                    document.getElementById('page_num').value= Number(page_num)+1;
                    return enter();
                });
        } else {
/* 
    Nel caso in cui il JSON abbia errore, lo mando a schermo al posto dei risultati. 
*/                   
            $('.results').append(data.error);
                
        }
    }
            
});
/* 
    Passaggio obbligato per settare l'asincronicità della chiamata. 
*/      
    return false;
}

 
</script>
</body>
</html>
