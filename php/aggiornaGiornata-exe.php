<?php
	// Stabilisco la connessione col DB
	include_once("connessione.php");

	// Salvo la giornata per la quale vengono generati i risultati
	$sql="SELECT NumGior FROM giornata WHERE Stato='NGA'";
	$giornRisultati=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																 ."<p>codice di errore ".$cid->errno
																 .":".$cid->error."</p>");
	$gior=$giornRisultati->fetch_row();

	// Salvo tutti i giocatori
	$sql="SELECT Cognome FROM giocatore";
	$gente=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																 ."<p>codice di errore ".$cid->errno
																 .":".$cid->error."</p>");

	// Inserisco la valutazione di tutti i giocatori per quella giornata
	while($cognomi=$gente->fetch_row()) {
		$sql="INSERT INTO gioca (Giocatore, Giornata, Punteggio)
					VALUES ('$cognomi[0]', '$gior[0]', '".(rand(-1,10))."')";
		$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																	 ."<p>codice di errore ".$cid->errno
																	 .":".$cid->error."</p>");
	}

	// Seleziono tutte le formazioni iscritte alla giornata
	$sql="SELECT DISTINCT Formazione
				FROM iscritta WHERE Giornata='$gior[0]'";
	$formDaAgg=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																 ."<p>codice di errore ".$cid->errno
																 .":".$cid->error."</p>");

	// Ciclo per ogni formazione iscritta alla giornata per qualunque campionato
	while($form=$formDaAgg->fetch_row()) {
		// Inizializzo variabile per il conto dei punti giornalieri per ogni formazione
		$puntiGiornalieri=0;
		// Seleziono i giocatori titolari che fanno parte della formazione selezionata
		$sql="SELECT Giocatore FROM sta
					WHERE Formazione='$form[0]' AND NumIngresso BETWEEN 1 AND 5
					ORDER BY NumIngresso";
		$giocatoriInForm=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																	 ."<p>codice di errore ".$cid->errno
																	 .":".$cid->error."</p>");

		// Per ogni punteggio del giocatore sommo ai punti giornalieri
		while($titolare=$giocatoriInForm->fetch_row()) {
			// Prendo il voto del giocatore considerato
			$sql="SELECT Punteggio FROM gioca WHERE Giocatore='$titolare[0]' AND Giornata='$gior[0]'";
			$votiGioc=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");
			$voto=$votiGioc->fetch_row();

			// Seleziono il ruolo del giocatore considerato
			$sql="SELECT Ruolo FROM giocatore WHERE Cognome='$titolare[0]'";
			$ruoloGiocPerSost=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");
			$ruolo=$ruoloGiocPerSost->fetch_row();

			// verifico se ci sono sostituzioni da fare, se il voto è '-1' devo considerare il voto del sostituto
			if($voto[0]!=-1) {
				// Sommo ai punti i punti del giocatore
				$puntiGiornalieri=$puntiGiornalieri+$voto[0];
			}
			else {
				// Creo un array che conterrà le riserve per quel ruolo
				$riserve=array();
				// Seleziono i giocatori che possono sostuire il giocatore in quella formazione
				$sql="SELECT Giocatore FROM sta JOIN giocatore ON Cognome=sta.Giocatore
							WHERE Formazione='$form[0]' AND Ruolo='$ruolo[0]' AND NumIngresso BETWEEN 6 AND 11
							ORDER BY NumIngresso";
				$ris=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																			 ."<p>codice di errore ".$cid->errno
																			 .":".$cid->error."</p>");
				// Salvo questi giocatori in un array, il primo sostituto sarà in pos [0]
				while($nomeRis=$ris->fetch_row()) {
					$riserve[]=$nomeRis[0];
				}
				// Nel caso ho un solo sostituto per quel ruolo, se il voto è diverso da "-1" lo sommo
				if(count($riserve)==1) {
					// Salvo il punteggio dell'unico sostituto
					$sql="SELECT Punteggio FROM gioca WHERE Giocatore='$riserve[0]' AND Giornata='$gior[0]'";
					$ptSost=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																				 ."<p>codice di errore ".$cid->errno
																				 .":".$cid->error."</p>");
					$ptSost=$ptSost->fetch_row();
					// Controllo il punteggio del sostituto, se è uguale a "-1" non faccio nulla
					if($ptSost[0]!=-1) {
						$puntiGiornalieri=$puntiGiornalieri+$ptSost[0];
					}
				}
				else {
					// Controllo se la prima riserva per quel ruolo ha voto diverso da "-1",
					// Se si sommo, altrimenti prendo il voto della seconda e lo controllo a sua volta
					for($i=0;$i<2;$i++) {
						$sql="SELECT Punteggio FROM gioca WHERE Giocatore='$riserve[$i]' AND Giornata='$gior[0]'";
						$ptSost=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																					 ."<p>codice di errore ".$cid->errno
																					 .":".$cid->error."</p>");
						$ptSost=$ptSost->fetch_row();
						if($ptSost[0]!="" && $ptSost[0]!='-1') {
							$puntiGiornalieri=$puntiGiornalieri+$ptSost[0];
							break;
						}
					}
				}
			}
		}

		/* PROTEGGO IL CASO IN CUI I TITOLARI CON STESSO RUOLO PRENDONO -1 */
		// Seleziono il voto e il ruolo del giocatore in posizione 5 (seocndo gioc. di un ruolo)
		$sql="SELECT Punteggio, Ruolo
					FROM gioca JOIN giocatore ON gioca.Giocatore=Cognome
					JOIN sta ON Cognome=sta.Giocatore
					WHERE sta.Formazione='$form[0]' AND gioca.Giornata='$gior[0]' AND NumIngresso='5'";
		$votoDopp=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
														 ."<p>codice di errore ".$cid->errno
														 .":".$cid->error."</p>");
		$votoDop=$votoDopp->fetch_row();

		// Controllo se ha preso "-1"
		if($votoDop[0]==-1) {
			// vedo se il giocatore titolare dello stesso ruolo ha preso "-1"
			$sql="SELECT Punteggio
						FROM gioca JOIN giocatore ON gioca.Giocatore=Cognome
						JOIN sta ON Cognome=sta.Giocatore
						WHERE sta.Formazione='$form[0]' AND gioca.Giornata='$gior[0]'
						AND Ruolo='$votoDop[1]' AND NumIngresso BETWEEN 2 AND 4";
			$votoTitCorr=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
															 ."<p>codice di errore ".$cid->errno
															 .":".$cid->error."</p>");
			$votoCorr=$votoTitCorr->fetch_row();

			if($votoCorr[0]==-1) {
				/* Seleziono il voto dell'unico sostituto che sarà stato aggiunto due volte (una per sostituzione),
				ed essendo le posizioni dei sostituti fisse, il mio sarà o in poiszione 9, o 10 o 11 */
				$sql="SELECT Punteggio
							FROM gioca JOIN giocatore ON gioca.Giocatore=Cognome
							JOIN sta ON Cognome=sta.Giocatore
							WHERE sta.Formazione='$form[0]' AND gioca.Giornata='$gior[0]'
							AND Ruolo='$votoDop[1]' AND NumIngresso BETWEEN 9 AND 11";
			 	$punteggioSostituto=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																 ."<p>codice di errore ".$cid->errno
																 .":".$cid->error."</p>");
				$pDaTogliere=$punteggioSostituto->fetch_row();
				// Controllo che il voto non sia "-1"
				if($pDaTogliere[0]!=-1) {
					$puntiGiornalieri=$puntiGiornaliri-$pDaTogliere[0];
				}
			}
		}

		// Aggiorno i punteggi giornalieri per ogni formazione iscritta
		$sql="UPDATE iscritta SET PuntiGiornata='$puntiGiornalieri' WHERE Formazione='$form[0]'";
		$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
														 ."<p>codice di errore ".$cid->errno
														 .":".$cid->error."</p>");

		// Salvo nome squadra formazione considerata
		$sql="SELECT Squadra FROM Formazione WHERE IdForm='$form[0]'";
		$nomeSquadraForm=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");
		$nomeSqForm=$nomeSquadraForm->fetch_row();
		$nomeSqForm=$nomeSqForm[0];

		// Controllo e il punteggio giornaliero è maggiore o uguale a 35 per l'attribuzione delle stelle
		if($puntiGiornalieri>=35) {
		  // Salvo il numero corrente di Stelle
			$sql="SELECT Stelle FROM squadra WHERE NomeSq='$nomeSqForm'";
			$numeroStelle=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																			 ."<p>codice di errore ".$cid->errno
																			 .":".$cid->error."</p>");
			$numStelle=$numeroStelle->fetch_row();
			$newStelle=$numStelle[0]+1;

			// Inserisco una stella alla squadra della formazione considerata
			$sql="UPDATE squadra SET Stelle='$newStelle' WHERE NomeSq='$nomeSqForm'";
			$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																			 ."<p>codice di errore ".$cid->errno
																			 .":".$cid->error."</p>");

			// Controllo se l'utente passa di grado (con tre o più stelle da "Allenatore" diventerà "CT")
			if($newStelle==3) {

				// Salvo la mail dell'utente che passa di grado
				$sql="SELECT Utente FROM squadra WHERE NomeSq='$nomeSqForm'";
				$mailUt=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																				 ."<p>codice di errore ".$cid->errno
																				 .":".$cid->error."</p>");
				$mail=$mailUt->fetch_row();

				// Salvo il tipo dell'utente che dovrebbe passare di grado (nel caso fosse l'admin)
				$sql="SELECT Tipo FROM utente WHERE Mail='$mail[0]'";
				$tipoUt=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																				 ."<p>codice di errore ".$cid->errno
																				 .":".$cid->error."</p>");
				$tipo=$tipoUt->fetch_row();
				// Controllo se l'utente non è amministratore
				if($tipo[0]!='Amministratore') {
					// Se non è un amministratore aggiorno il Tipo dell'utente a "CT"
					$sql="UPDATE utente SET Tipo='CT' WHERE Mail='$mail[0]'";
					$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																					 ."<p>codice di errore ".$cid->errno
																					 .":".$cid->error."</p>");
				}
			}
		}

		/*          ----- CONTROLLI PER TOP COACH ----            */
		// Salvo i voti dei 5 titolari della formazione considerata
		$sql="SELECT Punteggio
					FROM gioca
					WHERE gioca.Giocatore IN (SELECT sta.Giocatore
																		FROM sta JOIN formazione ON sta.Formazione=IdForm
																		JOIN iscritta ON IdForm=iscritta.Formazione
																		WHERE iscritta.Formazione='$form[0]'
																		AND NumIngresso BETWEEN 1 AND 5)";
		$votiTitolari=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");

		// Calcolo la media dei punteggi dei titolari di quella Giornata
		$sql="SELECT AVG(Punteggio)
					FROM gioca JOIN giocatore ON gioca.Giocatore=Cognome JOIN sta ON Cognome=sta.Giocatore
					JOIN Formazione ON sta.Formazione=IdForm JOIN iscritta ON IdForm=iscritta.Formazione
					WHERE NumIngresso BETWEEN 1 AND 5 AND iscritta.Giornata='$gior[0]'";

		$mediaGiocatori=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");
		$mediaVoti=$mediaGiocatori->fetch_row();
		// Variabile che conteggia i giocatori che superano la media
		$topCoach=0;
		// Valuto se tutti e cinque i giocatori hanno punteggio maggiore della media
		while($votiTit=$votiTitolari->fetch_row()) {
			if($votiTit[0]>$mediaVoti[0]) {
				$topCoach=$topCoach+1;
			}
			else {
				break;
			}
		}
		// Se la variabile vale 5 significa che l'utente è TopCoach con la formazione considerata per la giornata
		if($topCoach==5) {
			$sql="UPDATE iscritta SET TopCoach='1' WHERE Formazione='$form[0]'";
			$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																			 ."<p>codice di errore ".$cid->errno
																			 .":".$cid->error."</p>");
		}

		/* Seleziono i campionati a cui si partecipa per i quali si è giocata la giornata
			 con la formazione considerata per aggiornarne le classifiche generali.
			 Non vengono aggiornati i campionati conclusi ne iscritte le formazioni. */
		$sql="SELECT iscritta.Campionato FROM iscritta
		      WHERE Formazione='$form[0]' AND Giornata='$gior[0]'
					AND iscritta.Campionato NOT IN (SELECT DISTINCT vince.Campionato FROM vince)";
		$campionatiDaAggiornare=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																	 ."<p>codice di errore ".$cid->errno
																	 .":".$cid->error."</p>");

		while($campAgg=$campionatiDaAggiornare->fetch_row()) {
			// Iscrivo automaticamente le formazioni nei campionati per la prossima giornata
			$nextGior=$gior[0]+1;
			$sql="INSERT INTO iscritta (Formazione,Campionato,Giornata)
						VALUES ('$form[0]','$campAgg[0]','$nextGior')";
			$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");
			// Salvo i vecchi puntiTotali
			$sql="SELECT PuntiTot FROM partecipa WHERE Squadra='$nomeSqForm' AND Campionato='$campAgg[0]'";
			$puntiVecchi=$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");
			$oldPt=$puntiVecchi->fetch_row();
			// Salvo i nuovi Punti Totali
			$newPt=$oldPt[0]+$puntiGiornalieri;
			// Aggiorno il punteggio della classifica totale
			$sql="UPDATE partecipa SET PuntiTot='$newPt'
						WHERE Squadra='$nomeSqForm' AND Campionato='$campAgg[0]'";
			$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
																		 ."<p>codice di errore ".$cid->errno
																		 .":".$cid->error."</p>");
		}
  }

	// Rinnovo le giornate nel DB
	$oldGior=$gior[0]-1;
	$nextGior=$gior[0]+1;
	$newGior=$nextGior+1;
	$sql="UPDATE giornata SET Stato='GC' WHERE NumGior='$gior[0]'";
	$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
											  	 ."<p>codice di errore ".$cid->errno
													 .":".$cid->error."</p>");
	$sql="UPDATE giornata SET Stato='NGA' WHERE NumGior='$nextGior'";
	$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
										  		 ."<p>codice di errore ".$cid->errno
													 .":".$cid->error."</p>");
	$sql="INSERT INTO giornata(NumGior,Stato) VALUES ('$newGior','NGC')";
	$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
													 ."<p>codice di errore ".$cid->errno
													 .":".$cid->error."</p>");
	// Elimino le vecchie valutazioni dei giocatori per non appesantire il DB
	$sql="DELETE FROM gioca WHERE Giornata='$oldGior'";
	$cid->query($sql) or die("<p>Impossibile eseguire query.</p>"
													 ."<p>codice di errore ".$cid->errno
													 .":".$cid->error."</p>");

	header("Location:../index.php?op=classificaCampionati");


?>
