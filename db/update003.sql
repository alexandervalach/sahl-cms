-- =====================================
-- USERS
-- =====================================
INSERT INTO users (id, username, password, role, is_present)
VALUES (
    1,
    'admin',
    '$2y$15$TvrUBr503tDKiWnus21GZOI9gjp7KT/8F/Hd336BtzI/4MuQjvk2W',
    'admin',
    true
);

-- =====================================
-- SEASONS (ARCHIVE ONLY)
-- =====================================
INSERT INTO seasons (id, label, is_present)
VALUES (1, '2024/2025', true);

-- =====================================
-- GROUPS
-- =====================================
INSERT INTO groups (id, label, is_present)
VALUES (1, 'A', true);

-- =====================================
-- SEASONS ↔ GROUPS
-- =====================================
INSERT INTO seasons_groups (id, season_id, group_id, is_present)
VALUES
    (1, 1, 1, true),      -- archived
    (2, NULL, 1, true);   -- current

-- =====================================
-- TEAMS
-- =====================================
INSERT INTO teams (id, name, is_present)
VALUES
    (1, 'HC Docker', true),
    (2, 'HC Localhost', true);

-- =====================================
-- SEASONS ↔ GROUPS ↔ TEAMS
-- =====================================
INSERT INTO seasons_groups_teams (id, season_group_id, team_id, is_present)
VALUES
    (1, 1, 1, true),
    (2, 1, 2, true),
    (3, 2, 1, true),
    (4, 2, 2, true);

-- =====================================
-- PLAYER TYPES (PREDEFINED)
-- =====================================
INSERT INTO player_types (id, label, abbr, priority, is_present) VALUES
    (1, 'Hráč', '', 30, true),
    (2, 'Brankár', 'B', 40, true),
    (5, 'Kapitán', 'C', 10, true),
    (7, 'Asistent kapitána', 'A', 20, true);

-- =====================================
-- TABLE TYPES (PREDEFINED)
-- =====================================
INSERT INTO table_types (id, label, is_present) VALUES
    (1, 'Základná časť', true),
    (2, 'Play Off', true);

-- =====================================
-- TABLES
-- =====================================
INSERT INTO tables (id, table_type_id, season_group_id, is_present, is_visible)
VALUES
    (1, 1, 1, true, true), -- archive
    (2, 1, 2, true, true); -- current

-- =====================================
-- TABLE ENTRIES
-- =====================================
INSERT INTO table_entries (id, team_id, table_id, is_present)
VALUES
    (1, 1, 1, true),
    (2, 2, 1, true),
    (3, 1, 2, true),
    (4, 2, 2, true);

-- =====================================
-- ROUNDS
-- =====================================
INSERT INTO rounds (id, season_id, label, is_present)
VALUES
    (1, 1, 'Round 1', true),     -- archive
    (2, NULL, 'Round 1', true);  -- current

-- =====================================
-- FIGHTS
-- =====================================
INSERT INTO fights (id, round_id, team1_id, team2_id, table_id, score1, score2, is_present)
VALUES
    (1, 1, 1, 2, 1, 3, 2, true), -- archive
    (2, 2, 1, 2, 2, 0, 0, true); -- current

-- =====================================
-- PLAYERS
-- =====================================
INSERT INTO players (id, name, number, born, is_present)
VALUES
    (1, 'John Doe', 9, '1995', true),
    (2, 'Max Power', 88, '1992', true);

-- =====================================
-- PLAYERS ↔ SEASONS ↔ GROUPS ↔ TEAMS
-- =====================================
INSERT INTO players_seasons_groups_teams
    (id, season_group_team_id, player_id, player_type_id, goals, assistances, is_present)
VALUES
    (1, 1, 1, 5, 1, 0, true), -- archive captain
    (2, 1, 2, 1, 0, 1, true),
    (3, 3, 1, 5, 0, 0, true), -- current captain
    (4, 3, 2, 1, 0, 0, true);

-- =====================================
-- GOALS
-- =====================================
INSERT INTO goals (id, fight_id, player_season_group_team_id, number, is_home_player, is_present)
VALUES
    (1, 1, 1, 1, true, true);

-- =====================================
-- ASSISTANCES
-- =====================================
INSERT INTO assistances (id, fight_id, player_season_group_team_id, number, is_home_player, is_present)
VALUES
    (1, 1, 2, 1, true, true);

-- =====================================
-- PUNISHMENTS
-- =====================================
INSERT INTO punishments (id, player_season_group_team_id, content, round, condition, is_present)
VALUES
    (1, 2, 'Minor penalty', 'Round 1', false, true);

-- =====================================
-- EVENTS
-- =====================================
INSERT INTO events (id, season_id, content, is_present)
VALUES
    (1, 1, 'Season 2024/2025 finished', true),
    (2, NULL, 'Season 2025/2026 started', true);

-- =====================================
-- RULES
-- =====================================
INSERT INTO rules (id, season_id, content, is_present)
VALUES
    (1, NULL, 'Standard SAHL rules apply.', true);

-- =====================================
-- POSTS
-- =====================================
INSERT INTO posts (id, title, content, author, is_present)
VALUES (
    1,
    'Welcome to SAHL',
    'Local development environment is running successfully.',
    'System',
    true
);

-- =====================================
-- POST IMAGES
-- =====================================
INSERT INTO post_images (id, post_id, name, is_present)
VALUES
    (1, 1, 'welcome.jpg', true);

-- =====================================
-- ALBUMS & IMAGES
-- =====================================
INSERT INTO albums (id, name, is_present)
VALUES (1, 'Season Gallery', true);

INSERT INTO images (id, album_id, name, is_present)
VALUES
    (1, 1, 'photo1.jpg', true);

-- =====================================
-- LINKS
-- =====================================
INSERT INTO links (id, label, url, is_present)
VALUES
    (1, 'SAHL', 'https://sahl.sk', true),
    (2, 'GitHub', 'https://github.com/alexandervalach/sahl-cms', true);

-- =====================================
-- SPONSORS
-- =====================================
INSERT INTO sponsors (id, label, url, is_present)
VALUES
    (1, 'Local Sponsor', 'https://example.com', true);
