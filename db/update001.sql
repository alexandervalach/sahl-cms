-- PostgreSQL 14 schema

DROP TABLE IF EXISTS albums CASCADE;
CREATE TABLE albums (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name text NOT NULL,
    thumbnail text,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE albums ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS assistances CASCADE;
CREATE TABLE assistances (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    fight_id integer NOT NULL,
    player_season_group_team_id integer NOT NULL,
    number integer DEFAULT 0,
    is_home_player boolean NOT NULL DEFAULT false,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE assistances ADD PRIMARY KEY (id);

CREATE INDEX idx_assistances_fight_id ON assistances(fight_id);
CREATE INDEX idx_assistances_psgt_id ON assistances(player_season_group_team_id);

DROP TABLE IF EXISTS events CASCADE;
CREATE TABLE events (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    season_id integer,
    content text NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE events ADD PRIMARY KEY (id);

CREATE INDEX idx_events_season_id ON events(season_id);

DROP TABLE IF EXISTS fights CASCADE;
CREATE TABLE fights (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    round_id integer NOT NULL,
    team1_id integer NOT NULL,
    team2_id integer NOT NULL,
    table_id integer NOT NULL DEFAULT 1,
    score1 integer NOT NULL DEFAULT 0,
    score2 integer NOT NULL DEFAULT 0,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE fights ADD PRIMARY KEY (id);

CREATE INDEX idx_fights_round_id ON fights(round_id);
CREATE INDEX idx_fights_team1_id ON fights(team1_id);
CREATE INDEX idx_fights_team2_id ON fights(team2_id);
CREATE INDEX idx_fights_table_id ON fights(table_id);

DROP TABLE IF EXISTS goals CASCADE;
CREATE TABLE goals (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    fight_id integer NOT NULL,
    player_season_group_team_id integer NOT NULL,
    number integer,
    is_home_player boolean NOT NULL DEFAULT false,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE goals ADD PRIMARY KEY (id);

CREATE INDEX idx_goals_fight_id ON goals(fight_id);
CREATE INDEX idx_goals_psgt_id ON goals(player_season_group_team_id);

DROP TABLE IF EXISTS groups CASCADE;
CREATE TABLE groups (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    label text NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE groups ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS images CASCADE;
CREATE TABLE images (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    album_id integer NOT NULL,
    name text NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE images ADD PRIMARY KEY (id);

CREATE INDEX idx_images_album_id ON images(album_id);

DROP TABLE IF EXISTS links CASCADE;
CREATE TABLE links (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    label text NOT NULL,
    url text NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE links ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS players CASCADE;
CREATE TABLE players (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name varchar(255) NOT NULL,
    number integer NOT NULL,
    born varchar(10),
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE players ADD PRIMARY KEY (id);

CREATE INDEX idx_players_name ON players(name);
CREATE INDEX idx_players_number ON players(number);

DROP TABLE IF EXISTS player_types CASCADE;
CREATE TABLE player_types (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    label text NOT NULL,
    abbr text,
    priority smallint NOT NULL DEFAULT 100,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE player_types ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS posts CASCADE;
CREATE TABLE posts (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title text NOT NULL,
    content text NOT NULL,
    author text,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    thumbnail text,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE posts ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS post_images CASCADE;
CREATE TABLE post_images (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    post_id integer NOT NULL,
    name text NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE post_images ADD PRIMARY KEY (id);

CREATE INDEX idx_post_images_post_id ON post_images(post_id);

DROP TABLE IF EXISTS punishments CASCADE;
CREATE TABLE punishments (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    player_season_group_team_id integer NOT NULL,
    content text,
    round text NOT NULL,
    condition boolean NOT NULL DEFAULT false,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE punishments ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS rounds CASCADE;
CREATE TABLE rounds (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    season_id integer,
    label text NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE rounds ADD PRIMARY KEY (id);

CREATE INDEX idx_rounds_season_id ON rounds(season_id);

DROP TABLE IF EXISTS rules CASCADE;
CREATE TABLE rules (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    season_id integer,
    content text NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE rules ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS seasons CASCADE;
CREATE TABLE seasons (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    label text NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE seasons ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS seasons_groups CASCADE;
CREATE TABLE seasons_groups (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    season_id integer,
    group_id integer NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE seasons_groups ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS seasons_groups_teams CASCADE;
CREATE TABLE seasons_groups_teams (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    season_group_id integer NOT NULL,
    team_id integer NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE seasons_groups_teams ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS sponsors CASCADE;
CREATE TABLE sponsors (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    label text NOT NULL,
    url text,
    image text,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE sponsors ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS table_types CASCADE;
CREATE TABLE table_types (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    label text NOT NULL,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE table_types ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS tables CASCADE;
CREATE TABLE tables (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    table_type_id integer NOT NULL,
    season_group_id integer NOT NULL,
    is_present boolean NOT NULL DEFAULT true,
    is_visible boolean NOT NULL DEFAULT true
);

ALTER TABLE tables ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS table_entries CASCADE;
CREATE TABLE table_entries (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    team_id integer NOT NULL,
    table_id integer NOT NULL,
    counter integer NOT NULL DEFAULT 0,
    win integer NOT NULL DEFAULT 0,
    tram integer NOT NULL DEFAULT 0,
    lost integer NOT NULL DEFAULT 0,
    score1 integer NOT NULL DEFAULT 0,
    score2 integer NOT NULL DEFAULT 0,
    points integer NOT NULL DEFAULT 0,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE table_entries ADD PRIMARY KEY (id);

DROP TABLE IF EXISTS teams CASCADE;
CREATE TABLE teams (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name varchar(255) NOT NULL,
    photo varchar(255),
    logo varchar(255),
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE teams ADD PRIMARY KEY (id);

CREATE INDEX idx_teams_name ON teams(name);

DROP TABLE IF EXISTS users CASCADE;
CREATE TABLE users (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username text NOT NULL,
    password text NOT NULL,
    role text NOT NULL DEFAULT 'admin' CHECK (role = 'admin'),
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE users ADD PRIMARY KEY (id);

-- =====================================
-- PLAYERS ↔ SEASONS ↔ GROUPS ↔ TEAMS
-- =====================================
DROP TABLE IF EXISTS players_seasons_groups_teams CASCADE;
CREATE TABLE players_seasons_groups_teams (
    id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    season_group_team_id integer NOT NULL,
    player_id integer NOT NULL,
    player_type_id integer NOT NULL,
    goals integer NOT NULL DEFAULT 0,
    assistances integer NOT NULL DEFAULT 0,
    is_transfer boolean NOT NULL DEFAULT false,
    is_present boolean NOT NULL DEFAULT true
);

ALTER TABLE players_seasons_groups_teams ADD PRIMARY KEY (id);

CREATE INDEX idx_psgt_season_group_team
    ON players_seasons_groups_teams (season_group_team_id);

CREATE INDEX idx_psgt_player
    ON players_seasons_groups_teams (player_id);

CREATE INDEX idx_psgt_player_type
    ON players_seasons_groups_teams (player_type_id);
