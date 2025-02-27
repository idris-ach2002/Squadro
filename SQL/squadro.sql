DROP TABLE IF exists PartieSquadro;
DROP TABLE IF exists JoueurSquadro;

CREATE TABLE JoueurSquadro (
                        id serial PRIMARY KEY,
                        joueurNom VARCHAR(255) UNIQUE NOT NULL
);

--INSERT INTO JoueurSquadro(joueurNom) VALUES ('ToTo'),('Titi'), ('Lulu');

CREATE TABLE PartieSquadro(
                            partieId serial PRIMARY KEY,
                            playerOne int NOT NULL REFERENCES JoueurSquadro(id),
                            playerTwo int NULL REFERENCES JoueurSquadro(id),
                            gameStatus VARCHAR(100) NOT NULL DEFAULT 'initialized' CHECK ( gameStatus IN ('initialized', 'waitingForPlayer', 'finished')),
                            json text NOT NULL,
                            CONSTRAINT players CHECK ( playerOne<>playerTwo)
);


