-- =====================================
-- ATTENDANCE
-- =====================================

ALTER TABLE fights
ADD COLUMN IF NOT EXISTS attendance_recorded boolean NOT NULL DEFAULT false;

CREATE TABLE IF NOT EXISTS attendances (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    fight_id integer NOT NULL,
    player_season_group_team_id integer NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_attendances_fight_player
ON attendances (fight_id, player_season_group_team_id);

CREATE INDEX IF NOT EXISTS idx_attendances_fight_id
ON attendances (fight_id);

CREATE INDEX IF NOT EXISTS idx_attendances_psgt_id
ON attendances (player_season_group_team_id);

ALTER TABLE attendances
DROP CONSTRAINT IF EXISTS fk_attendances_fight;

ALTER TABLE attendances
ADD CONSTRAINT fk_attendances_fight
FOREIGN KEY (fight_id)
REFERENCES fights (id);


ALTER TABLE attendances
DROP CONSTRAINT IF EXISTS fk_attendances_psgt;

ALTER TABLE attendances
ADD CONSTRAINT fk_attendances_psgt
FOREIGN KEY (player_season_group_team_id)
REFERENCES players_seasons_groups_teams (id);