<?php

session_start();
include_once("connessione.php");

$nick=$_SESSION['nick'];
$motto=addslashes(htmlspecialchars($_POST['motto']));

// Controllo input
if(!empty($motto) && strlen($motto)<=30) {
	$query="UPDATE squadra JOIN utente ON Utente=Mail SET Motto='$motto' WHERE Nickname='$nick'";
	$cid->query($query) or die("<p>Impossibile eseguire query.</p>"
															 ."<p>codice di errore ".$cid->errno
															 .":".$cid->error."</p>");

	$cid->close();
	header("Location:../index.php?op=squadraInfo");
}
else{
		header("Location:../index.php?op=squadraModifica");
}

?>
