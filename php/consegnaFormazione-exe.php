<?php

	session_start();
	include_once("connessione.php");
	$nick=$_SESSION['nick'];

	$campionato=$_POST['campionato'];
	$formazione=$_POST['formazione'];

	// Seleziono il numero dela prossima giornata
	$sql="SELECT NumGior FROM giornata WHERE Stato='NGA'";
	$giornata=$cid->query($sql);
	$gior=$giornata->fetch_row();

	// Salvo in una variabile il nome della squadra loggata
	$sql="SELECT nomeSq FROM squadra JOIN utente ON Mail=Utente WHERE Nickname='".$nick."'";
	$squadra=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																 ."<p>codice di errore ".$cid->errno
																 .":".$cid->error."</p>");
	$nomeSq=$squadra->fetch_row();

	// Salvo i campionati ai quali sono già iscritto per la prossima giornata.
	$sql="SELECT Campionato
				FROM iscritta JOIN formazione ON IdForm=Formazione
				WHERE Campionato='$campionato' AND Giornata='$gior[0]' AND Squadra='$nomeSq[0]'";
	$campGiaIsc=$cid->query($sql);

	if($campGiaIsc->num_rows==0) {
		$cons=false;
	}
	else {
		$cons=true;
	}

	// Se è già consegnata una formazione per quel campionato per quela giornata modifico
  if($cons) {
		$query="UPDATE iscritta SET Formazione='$formazione'
		        WHERE Giornata='$gior[0]' AND Campionato='$campionato'
						AND Formazione IN (SELECT IdForm FROM formazione
															 WHERE Squadra='$nomeSq[0]')";
		$cid->query($query) or die("<p>Impossibile eseguire query.</p>"
																	 ."<p>codice di errore ".$cid->errno
																	 .":".$cid->error."</p>");
	  $cid->close();
	}
	// altrimenti inserisco
	else {
		$query="INSERT INTO iscritta (Formazione, Campionato, Giornata)
	        	VALUES ('$formazione','$campionato','$gior[0]')";
		$cid->query($query) or die("<p>Impossibile eseguire query.</p>"
																	 ."<p>codice di errore ".$cid->errno
																	 .":".$cid->error."</p>");
		$cid->close();
	}

	header("Location:../index.php?op=consegnaFormazione");

 ?>
