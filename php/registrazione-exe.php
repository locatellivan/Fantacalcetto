<?php

	include_once("connessione.php");

	$nickname=addslashes(htmlspecialchars($_POST['nickname']));
	$nomeSq=addslashes(htmlspecialchars($_POST['nomeSq']));
	$psw1=trim(addslashes(htmlspecialchars($_POST['psw1'])));
	$psw2=trim(addslashes(htmlspecialchars($_POST['psw2'])));
	$email=trim(addslashes(htmlspecialchars($_POST['email'])));

	if($psw1==$psw2 && (!empty($nickname)) && (!empty($nomeSq)) && (!empty($psw1))
	   && (!empty($psw2)) && $nickname.length<30 && $nomeSq.lenght<20 && $email.length<40
	   && $psw1.lenght<30) {

		// Creo l'utente
		$sql="INSERT INTO utente(Nickname,Mail,Password) VALUES ('$nickname','$email','$psw1')";

		$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																 ."<p>codice di errore ".$cid->errno
																 .":".$cid->error."</p>");
		// Creo la squadra
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

		header("Location:../main.php?=statusOK");
	}
	else {
		header("Location:../main.php?op=registrazione");
	}

?>
