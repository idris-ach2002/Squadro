CREATE TABLE IF NOT EXISTS JoueurSquadro (
    id serial PRIMARY KEY,
    joueurNom VARCHAR(255) UNIQUE NOT NULL,
    createdAt TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS PartieSquadro (
    partieId serial PRIMARY KEY,
    playerOne int NOT NULL REFERENCES JoueurSquadro(id),
    playerTwo int NULL REFERENCES JoueurSquadro(id),
    gameStatus VARCHAR(100) NOT NULL DEFAULT 'initialized',
    json text NOT NULL,
    currentTurn VARCHAR(10) NOT NULL DEFAULT 'blanc',
    winner VARCHAR(10) NULL,
    lastMove text NULL,
    moveCount int NOT NULL DEFAULT 0,
    createdAt TIMESTAMP NOT NULL DEFAULT NOW(),
    updatedAt TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT players CHECK (playerOne <> playerTwo)
);

ALTER TABLE PartieSquadro ADD COLUMN IF NOT EXISTS currentTurn VARCHAR(10) NOT NULL DEFAULT 'blanc';
ALTER TABLE PartieSquadro ADD COLUMN IF NOT EXISTS winner VARCHAR(10) NULL;
ALTER TABLE PartieSquadro ADD COLUMN IF NOT EXISTS lastMove text NULL;
ALTER TABLE PartieSquadro ADD COLUMN IF NOT EXISTS moveCount int NOT NULL DEFAULT 0;
ALTER TABLE PartieSquadro ADD COLUMN IF NOT EXISTS createdAt TIMESTAMP NOT NULL DEFAULT NOW();
ALTER TABLE PartieSquadro ADD COLUMN IF NOT EXISTS updatedAt TIMESTAMP NOT NULL DEFAULT NOW();
ALTER TABLE JoueurSquadro ADD COLUMN IF NOT EXISTS createdAt TIMESTAMP NOT NULL DEFAULT NOW();

ALTER TABLE PartieSquadro DROP CONSTRAINT IF EXISTS partiesquadro_gamestatus_check;
ALTER TABLE PartieSquadro DROP CONSTRAINT IF EXISTS partiesquadro_currentturn_check;
ALTER TABLE PartieSquadro DROP CONSTRAINT IF EXISTS partiesquadro_winner_check;

ALTER TABLE PartieSquadro
    ADD CONSTRAINT partiesquadro_gamestatus_check
    CHECK (gameStatus IN ('initialized', 'waitingForPlayer', 'active', 'finished'));

ALTER TABLE PartieSquadro
    ADD CONSTRAINT partiesquadro_currentturn_check
    CHECK (currentTurn IN ('blanc', 'noir'));

ALTER TABLE PartieSquadro
    ADD CONSTRAINT partiesquadro_winner_check
    CHECK (winner IS NULL OR winner IN ('blanc', 'noir'));

CREATE INDEX IF NOT EXISTS idx_parties_status ON PartieSquadro(gameStatus);
CREATE INDEX IF NOT EXISTS idx_parties_player_one ON PartieSquadro(playerOne);
CREATE INDEX IF NOT EXISTS idx_parties_player_two ON PartieSquadro(playerTwo);
CREATE INDEX IF NOT EXISTS idx_parties_updated ON PartieSquadro(updatedAt DESC);
