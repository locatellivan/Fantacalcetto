CREATE DATABASE FantaCalcettoStatale;
USE FantaCalcettoStatale;

CREATE TABLE Utente (
	Mail varchar(40) PRIMARY KEY,
	Nickname varchar(30) UNIQUE NOT NULL,
	Password varchar(30) NOT NULL,
	SquadraTifata varchar(20) DEFAULT NULL,
	Nome varchar(20) DEFAULT NULL,
	CognomeU varchar(20) DEFAULT NULL,
	DataN DATE DEFAULT NULL,
	LuogoN varchar(20) DEFAULT NULL,
	Sesso ENUM('M','F') DEFAULT NULL,
	CittaAtt varchar(20) DEFAULT NULL,
	Tipo ENUM('Allenatore','CT','Amministratore') DEFAULT 'Allenatore'
);

CREATE TABLE Squadra (
	NomeSq varchar(20) PRIMARY KEY,
	FantaMilioni int(3) UNSIGNED DEFAULT '300',
	Motto varchar(30) DEFAULT NULL,
	Stelle int(4) UNSIGNED DEFAULT '0',
	Utente varchar(40),
	INDEX (Utente),
	FOREIGN KEY (Utente) REFERENCES Utente(Mail) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Campionato (
	NomeCamp varchar(20) PRIMARY KEY,
	DataInizio DATE,
	DataFine DATE,
	Creatore varchar(40),
	INDEX (Creatore),
	FOREIGN KEY (Creatore) REFERENCES Utente(mail) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Vince (
	Campionato varchar(20) NOT NULL,
	Campione varchar(40) NOT NULL,
	INDEX (Campionato),
	INDEX (Campione),
	PRIMARY KEY (Campione, Campionato),
	FOREIGN KEY (Campionato) REFERENCES Campionato(NomeCamp) ON DELETE CASCADE ON UPDATE NO ACTION,
	FOREIGN KEY (Campione) REFERENCES Utente(Mail) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Giornata (
	NumGior int(4) UNSIGNED PRIMARY KEY,
	Stato ENUM('GC','NGC','NGA')
);

CREATE TABLE Giocatore (
	Cognome varchar(20) PRIMARY KEY,
	Ruolo varchar(1) NOT NULL,
	Prezzo int(3) UNSIGNED NOT NULL,
	Squadra varchar(15),
	Citta varchar(20)
);

CREATE TABLE Possiede (
	Giocatore varchar(20),
	SquadraGioc varchar(20),
	INDEX (Giocatore),
	INDEX (SquadraGioc),
	PRIMARY KEY (Giocatore,SquadraGioc),
	FOREIGN KEY (Giocatore) REFERENCES Giocatore(Cognome) ON DELETE CASCADE ON UPDATE NO ACTION,
	FOREIGN KEY (SquadraGioc) REFERENCES Squadra(NomeSq) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE TABLE Partecipa (
	Squadra varchar(20),
	Campionato varchar(20),
	PuntiTot int(5) UNSIGNED,
	INDEX (Squadra),
	INDEX (Campionato),
	PRIMARY KEY (Squadra, Campionato),
	FOREIGN KEY (Squadra) REFERENCES Squadra(NomeSq) ON DELETE CASCADE ON UPDATE NO ACTION,
	FOREIGN KEY (Campionato) REFERENCES Campionato(NomeCamp) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE TABLE Formazione (
	IdForm varchar(41) PRIMARY KEY,
	Squadra varchar(20) NOT NULL,
	INDEX (Squadra),
	FOREIGN KEY (Squadra) REFERENCES Squadra(NomeSq) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE TABLE Iscritta (
	Formazione varchar(41),
	Campionato varchar(20),
	Giornata int(4) UNSIGNED,
	PuntiGiornata int(2) UNSIGNED,
	TopCoach ENUM('0','1'),
	INDEX (Formazione),
	INDEX (Campionato),
	INDEX (Giornata),
	PRIMARY KEY (Formazione, Giornata, Campionato),
	FOREIGN KEY (Formazione) REFERENCES Formazione(IdForm) ON DELETE CASCADE ON UPDATE NO ACTION,
	FOREIGN KEY (Campionato) REFERENCES Campionato(NomeCamp) ON DELETE CASCADE ON UPDATE NO ACTION,
	FOREIGN KEY (Giornata) REFERENCES Giornata(NumGior) ON DELETE CASCADE ON UPDATE NO ACTION
);


CREATE TABLE Sta (
	Giocatore varchar(20),
	Formazione varchar(41),
	NumIngresso int(2) UNSIGNED,
	INDEX (Giocatore),
	INDEX (Formazione),
	PRIMARY KEY (Giocatore, Formazione),
	FOREIGN KEY (Giocatore) REFERENCES Giocatore(Cognome) ON DELETE CASCADE ON UPDATE NO ACTION,
	FOREIGN KEY (Formazione) REFERENCES Formazione(IdForm) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE TABLE Gioca (
	Giocatore varchar(20),
	Giornata int(4) UNSIGNED,
	Punteggio int(2),
	INDEX (Giocatore),
	INDEX (Giornata),
	PRIMARY KEY (Giocatore, Giornata),
	FOREIGN KEY (Giocatore) REFERENCES Giocatore(Cognome) ON DELETE CASCADE ON UPDATE NO ACTION,
	FOREIGN KEY (Giornata) REFERENCES Giornata(NumGior) ON DELETE CASCADE ON UPDATE NO ACTION
);

INSERT INTO utente (Mail, Nickname, Password, Tipo)
VALUES ('admin@admin', 'Admin', 'admin', 'Amministratore') ;
INSERT INTO squadra (NomeSq, Utente)
VALUES ('AdminTeam', 'admin@admin');
INSERT INTO campionato (NomeCamp, DataInizio, DataFine, Creatore)
VALUES ('CAMPIONATO GENERALE', CURDATE(), '2018-12-31', 'admin@admin');
INSERT INTO giornata (NumGior, Stato)
VALUES ('1','NGA');
INSERT INTO giornata (NumGior, Stato)
VALUES ('2','NGC');
