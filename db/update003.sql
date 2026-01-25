-- =====================================
-- USERS
-- =====================================
INSERT INTO users (username, password, role, is_present)
VALUES (
    'admin',
    '$2y$15$TvrUBr503tDKiWnus21GZOI9gjp7KT/8F/Hd336BtzI/4MuQjvk2W',
    'admin',
    true
);

-- =====================================
-- SEASONS (ARCHIVE ONLY)
-- =====================================
INSERT INTO seasons (label, is_present)
VALUES ('2024/2025', true);

-- =====================================
-- GROUPS
-- =====================================
INSERT INTO groups (label, is_present)
VALUES ('A', true);

-- =====================================
-- SEASONS ↔ GROUPS
-- =====================================
-- archived
INSERT INTO seasons_groups (season_id, group_id, is_present)
VALUES (1, 1, true);

-- current
INSERT INTO seasons_groups (season_id, group_id, is_present)
VALUES (NULL, 1, true);

-- =====================================
-- TEAMS
-- =====================================
INSERT INTO teams (name, is_present)
VALUES
    ('HC Docker', true),
    ('HC Localhost', true);

-- =====================================
-- SEASONS ↔ GROUPS ↔ TEAMS
-- =====================================
-- archive
INSERT INTO seasons_groups_teams (season_group_id, team_id, is_present)
VALUES
    (1, 1, true),
    (1, 2, true);

-- current
INSERT INTO seasons_groups_teams (season_group_id, team_id, is_present)
VALUES
    (2, 1, true),
    (2, 2, true);

-- =====================================
-- PLAYER TYPES
-- =====================================
INSERT INTO player_types (label, abbr, priority, is_present) VALUES
    ('Hráč', '', 30, true),
    ('Brankár', 'B', 40, true),
    ('Kapitán', 'C', 10, true),
    ('Asistent kapitána', 'A', 20, true);

-- =====================================
-- TABLE TYPES
-- =====================================
INSERT INTO table_types (label, is_present) VALUES
    ('Základná časť', true),
    ('Play Off', true);

-- =====================================
-- TABLES
-- =====================================
-- archive
INSERT INTO tables (table_type_id, season_group_id, is_present, is_visible)
VALUES (1, 1, true, true);

-- current
INSERT INTO tables (table_type_id, season_group_id, is_present, is_visible)
VALUES (1, 2, true, true);

-- =====================================
-- TABLE ENTRIES
-- =====================================
INSERT INTO table_entries (team_id, table_id, is_present)
VALUES
    (1, 1, true),
    (2, 1, true),
    (1, 2, true),
    (2, 2, true);

-- =====================================
-- ROUNDS
-- =====================================
INSERT INTO rounds (season_id, label, is_present)
VALUES
    (1, 'Round 1', true),
    (NULL, 'Round 1', true);

-- =====================================
-- FIGHTS
-- =====================================
INSERT INTO fights (round_id, team1_id, team2_id, table_id, score1, score2, is_present)
VALUES
    (1, 1, 2, 1, 3, 2, true),
    (2, 1, 2, 2, 0, 0, true);

-- =====================================
-- PLAYERS
-- =====================================
INSERT INTO players (name, number, born, is_present)
VALUES
    ('John Doe', 9, '1995', true),
    ('Max Power', 88, '1992', true);

-- =====================================
-- PLAYERS ↔ SEASONS ↔ GROUPS ↔ TEAMS
-- =====================================
INSERT INTO players_seasons_groups_teams
    (season_group_team_id, player_id, player_type_id, goals, assistances, is_present)
VALUES
    (1, 1, 3, 1, 0, true), -- archive captain
    (1, 2, 1, 0, 1, true),
    (3, 1, 3, 0, 0, true), -- current captain
    (3, 2, 1, 0, 0, true);

-- =====================================
-- GOALS
-- =====================================
INSERT INTO goals (fight_id, player_season_group_team_id, number, is_home_player, is_present)
VALUES
    (1, 1, 1, true, true);

-- =====================================
-- ASSISTANCES
-- =====================================
INSERT INTO assistances (fight_id, player_season_group_team_id, number, is_home_player, is_present)
VALUES
    (1, 2, 1, true, true);

-- =====================================
-- PUNISHMENTS
-- =====================================
INSERT INTO punishments (player_season_group_team_id, content, round, condition, is_present)
VALUES
    (2, 'Minor penalty', 'Round 1', false, true);

-- =====================================
-- EVENTS
-- =====================================
INSERT INTO events (season_id, content, is_present)
VALUES
    (1, 'Season 2024/2025 finished', true),
    (NULL, 'Season 2025/2026 started', true);

-- =====================================
-- RULES
-- =====================================
INSERT INTO rules (season_id, content, is_present)
VALUES
    (NULL, 'Standard SAHL rules apply.', true);

-- =====================================
-- POSTS
-- =====================================
INSERT INTO posts (title, content, author, is_present)
VALUES (
    'Welcome to SAHL',
    'Local development environment is running successfully.',
    'System',
    true
);

-- =====================================
-- POST IMAGES
-- =====================================
INSERT INTO post_images (post_id, name, is_present)
VALUES
    (1, 'welcome.jpg', true);

-- =====================================
-- ALBUMS & IMAGES
-- =====================================
INSERT INTO albums (name, is_present)
VALUES ('Season Gallery', true);

INSERT INTO images (album_id, name, is_present)
VALUES
    (1, 'photo1.jpg', true);

-- =====================================
-- LINKS
-- =====================================
INSERT INTO links (label, url, is_present)
VALUES
    ('SAHL', 'https://sahl.sk', true),
    ('GitHub', 'https://github.com/alexandervalach/sahl-cms', true);

-- =====================================
-- SPONSORS
-- =====================================
INSERT INTO sponsors (label, url, is_present)
VALUES
    ('Local Sponsor', 'https://example.com', true);
