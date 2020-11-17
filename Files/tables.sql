/*  
	Versione phpMyAdmin 4.9.6
	Versione del server: 5.7.26
	Versione PHP: 7.3 
*/

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"; 	-- Evito la generazione di nuovi valori AUTO_INCREMENT quando viene inserito uno 0 in una colonna.
SET AUTOCOMMIT = 0;				-- Permetto di abilitare transazioni multi-statement senza dichiararle prima di ogni istruzione che causa commit implicito.
START TRANSACTION;				-- Inizio una nuova transazione.		
SET time_zone = "+00:00";			-- Imposto il tempo coordinato universale (UTC+0).
 
--
-- Struttura della tabella `node_tree`
--

CREATE TABLE IF NOT EXISTS `node_tree` (	-- Creo una nuova tabella `node_tree`.
  `idNode` int(10) NOT NULL AUTO_INCREMENT,	-- Definisco la colonna `idNode` con valori numerici interi, non null, autoincrementali. Il limite a 10 cifre è una scelta di comodo.
  `level` int(10) NOT NULL DEFAULT '0',		-- Definisco le colonne `level`, `iLeft` e `iRight` con valori numerici interi, non null, impostando '0' come valore di default.
  `iLeft` int(10) NOT NULL DEFAULT '0',			
  `iRight` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idNode`)			-- Assegno la chiave primaria alla colonna `idNode`. Infine definisco l'engine, il valore attuale di AUTO_INCREMENT e il set di caratteri.
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;

--
-- Struttura della tabella `node_tree_names`
--

CREATE TABLE IF NOT EXISTS `node_tree_names` (	-- Creo una nuova tabella `node_tree_names`.
  `idNode` int(10) NOT NULL DEFAULT '0',	-- Definisco la colonna `idNode` con valori numerici interi, non null. Il limite a 10 cifre è una scelta di comodo.
  `language` varchar(100) NOT NULL DEFAULT '0',	-- Definisco le colonne `language`, `NodeName` in caratteri alfanumerici, non null. Il limite a 100 caratteri è una scelta di comodo.
  `NodeName` varchar(100) NOT NULL DEFAULT '0'	-- Definisco l'engine e il set di caratteri.
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
