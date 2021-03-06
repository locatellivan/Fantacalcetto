<?php

	include_once("connessione.php");

	$nickname=addslashes(htmlspecialchars($_POST['nickname']));
	$nomeSq=addslashes(htmlspecialchars($_POST['nomeSq']));
	$psw1=trim(addslashes(htmlspecialchars($_POST['psw1'])));
	$psw2=trim(addslashes(htmlspecialchars($_POST['psw2'])));
	$email=trim(addslashes(htmlspecialchars($_POST['email'])));
	$errDB=0;

	$sql="SELECT Mail, Nickname, NomeSq FROM Utente JOIN squadra ON Mail=utente";
	$elencoUtenti=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
													    					 ."<p>codice di errore ".$cid->errno
														    				 .":".$cid->error."</p>");

	// Conto gli errori sollevati dal DB in caso di duplicazione di chiave primaria attributi UNIQUE
	while($elenco=$elencoUtenti->fetch_row()) {
		if($elenco[0]==$email) {
			$errDB++;
		}
		if($elenco[1]==$nickname) {
			$errDB++;
		}
		if($elenco[2]==$nomeSq) {
			$errDB++;
		}
	}

	// Se il database non ha sollevato errori passo al controllo degli input
	if($errDB==0) {

		// Eseguo il controllo sugli input
		if( ($psw1==$psw2) && (!empty($nickname)) && (!empty($email)) && (!empty($nomeSq)) && (!empty($psw1))
			 && (!empty($psw2)) && strlen($nickname)<30 && strlen($nomeSq)<20 && strlen($mail)<40
			 && strlen($psw1)<30) {
			// Salvo le credenziali dell'utente
			$sql="INSERT INTO utente(Nickname,Mail,Password) VALUES ('$nickname','$email','$psw1')";
			$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");

			// Inserisco nel database il nome della squadra
			$sql2="INSERT INTO squadra(NomeSq, Utente) VALUES ('$nomeSq','$email')";
			$cid->query($sql2) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");


			// Inserisco la squadra nel Campionato Generale
			$sql3="INSERT INTO partecipa (Squadra, Campionato, PuntiTot)
			       VALUES ('$nomeSq','CAMPIONATO GENERALE','0')";
			$cid->query($sql3) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");

			$cid->close();
			header("Location:../index.php");
			}
			else {
				header("Location:../index.php?op=registrazione");
			}
		}
		else {
			header("Location:../index.php?op=registrazione&err=ALERT");
		}

?>
