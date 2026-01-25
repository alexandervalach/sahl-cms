-- =====================================
-- ASSISTANCES
-- =====================================
ALTER TABLE assistances
ADD CONSTRAINT fk_assistances_fight
FOREIGN KEY (fight_id) REFERENCES fights (id);

ALTER TABLE assistances
ADD CONSTRAINT fk_assistances_psgt
FOREIGN KEY (player_season_group_team_id)
REFERENCES players_seasons_groups_teams (id);

-- =====================================
-- EVENTS
-- =====================================
ALTER TABLE events
ADD CONSTRAINT fk_events_season
FOREIGN KEY (season_id) REFERENCES seasons (id);

-- =====================================
-- FIGHTS
-- =====================================
ALTER TABLE fights
ADD CONSTRAINT fk_fights_round
FOREIGN KEY (round_id) REFERENCES rounds (id);

ALTER TABLE fights
ADD CONSTRAINT fk_fights_team1
FOREIGN KEY (team1_id) REFERENCES teams (id);

ALTER TABLE fights
ADD CONSTRAINT fk_fights_team2
FOREIGN KEY (team2_id) REFERENCES teams (id);

ALTER TABLE fights
ADD CONSTRAINT fk_fights_table
FOREIGN KEY (table_id) REFERENCES tables (id);

-- =====================================
-- GOALS
-- =====================================
ALTER TABLE goals
ADD CONSTRAINT fk_goals_fight
FOREIGN KEY (fight_id) REFERENCES fights (id);

ALTER TABLE goals
ADD CONSTRAINT fk_goals_psgt
FOREIGN KEY (player_season_group_team_id)
REFERENCES players_seasons_groups_teams (id);

-- =====================================
-- IMAGES
-- =====================================
ALTER TABLE images
ADD CONSTRAINT fk_images_album
FOREIGN KEY (album_id) REFERENCES albums (id);

-- =====================================
-- POST IMAGES
-- =====================================
ALTER TABLE post_images
ADD CONSTRAINT fk_post_images_post
FOREIGN KEY (post_id) REFERENCES posts (id)
ON DELETE CASCADE;

-- =====================================
-- PUNISHMENTS
-- =====================================
ALTER TABLE punishments
ADD CONSTRAINT fk_punishments_psgt
FOREIGN KEY (player_season_group_team_id)
REFERENCES players_seasons_groups_teams (id);

-- =====================================
-- ROUNDS
-- =====================================
ALTER TABLE rounds
ADD CONSTRAINT fk_rounds_season
FOREIGN KEY (season_id) REFERENCES seasons (id);

-- =====================================
-- RULES
-- =====================================
ALTER TABLE rules
ADD CONSTRAINT fk_rules_season
FOREIGN KEY (season_id) REFERENCES seasons (id)
ON DELETE SET NULL;

-- =====================================
-- SEASONS_GROUPS
-- =====================================
ALTER TABLE seasons_groups
ADD CONSTRAINT fk_seasons_groups_season
FOREIGN KEY (season_id) REFERENCES seasons (id);

ALTER TABLE seasons_groups
ADD CONSTRAINT fk_seasons_groups_group
FOREIGN KEY (group_id) REFERENCES groups (id);

-- =====================================
-- SEASONS_GROUPS_TEAMS
-- =====================================
ALTER TABLE seasons_groups_teams
ADD CONSTRAINT fk_sgt_season_group
FOREIGN KEY (season_group_id) REFERENCES seasons_groups (id);

ALTER TABLE seasons_groups_teams
ADD CONSTRAINT fk_sgt_team
FOREIGN KEY (team_id) REFERENCES teams (id);

-- =====================================
-- TABLES
-- =====================================
ALTER TABLE tables
ADD CONSTRAINT fk_tables_type
FOREIGN KEY (table_type_id) REFERENCES table_types (id);

ALTER TABLE tables
ADD CONSTRAINT fk_tables_season_group
FOREIGN KEY (season_group_id) REFERENCES seasons_groups (id);

-- =====================================
-- TABLE ENTRIES
-- =====================================
ALTER TABLE table_entries
ADD CONSTRAINT fk_table_entries_team
FOREIGN KEY (team_id) REFERENCES teams (id);

ALTER TABLE table_entries
ADD CONSTRAINT fk_table_entries_table
FOREIGN KEY (table_id) REFERENCES tables (id);

-- =====================================
-- PLAYERS_SEASONS_GROUPS_TEAMS
-- =====================================
ALTER TABLE players_seasons_groups_teams
ADD CONSTRAINT fk_psgt_season_group_team
FOREIGN KEY (season_group_team_id)
REFERENCES seasons_groups_teams (id);

ALTER TABLE players_seasons_groups_teams
ADD CONSTRAINT fk_psgt_player
FOREIGN KEY (player_id) REFERENCES players (id);

ALTER TABLE players_seasons_groups_teams
ADD CONSTRAINT fk_psgt_player_type
FOREIGN KEY (player_type_id) REFERENCES player_types (id);
