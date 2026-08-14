-- =====================================
-- OVERTIME RESULTS + 3/2/1/0 SCORING
-- =====================================

-- A fight needs to remember whether the winner was decided after overtime.
ALTER TABLE fights
ADD COLUMN IF NOT EXISTS is_overtime boolean NOT NULL DEFAULT false;

-- Standings keep regulation and overtime results separately.
ALTER TABLE table_entries
ADD COLUMN IF NOT EXISTS overtime_win integer NOT NULL DEFAULT 0;

ALTER TABLE table_entries
ADD COLUMN IF NOT EXISTS overtime_loss integer NOT NULL DEFAULT 0;

-- Rebuild only the CURRENT season standings from fights.
-- Archived seasons are intentionally left untouched because they may have used
-- a different historical points system. Existing fights are treated as
-- regulation results until an administrator explicitly marks them as overtime.
WITH current_entries AS (
    SELECT te.id, te.team_id, te.table_id
    FROM table_entries AS te
    INNER JOIN tables AS t
        ON t.id = te.table_id
    INNER JOIN seasons_groups AS sg
        ON sg.id = t.season_group_id
    WHERE sg.season_id IS NULL
      AND te.is_present = true
      AND t.is_present = true
      AND sg.is_present = true
),
stats AS (
    SELECT
        ce.id,
        COUNT(f.id)::integer AS counter,
        COUNT(f.id) FILTER (
            WHERE f.is_overtime = false
              AND (
                    (f.team1_id = ce.team_id AND f.score1 > f.score2)
                 OR (f.team2_id = ce.team_id AND f.score2 > f.score1)
              )
        )::integer AS win,
        COUNT(f.id) FILTER (
            WHERE f.is_overtime = true
              AND (
                    (f.team1_id = ce.team_id AND f.score1 > f.score2)
                 OR (f.team2_id = ce.team_id AND f.score2 > f.score1)
              )
        )::integer AS overtime_win,
        COUNT(f.id) FILTER (
            WHERE f.score1 = f.score2
        )::integer AS tram,
        COUNT(f.id) FILTER (
            WHERE f.is_overtime = true
              AND (
                    (f.team1_id = ce.team_id AND f.score1 < f.score2)
                 OR (f.team2_id = ce.team_id AND f.score2 < f.score1)
              )
        )::integer AS overtime_loss,
        COUNT(f.id) FILTER (
            WHERE f.is_overtime = false
              AND (
                    (f.team1_id = ce.team_id AND f.score1 < f.score2)
                 OR (f.team2_id = ce.team_id AND f.score2 < f.score1)
              )
        )::integer AS lost,
        COALESCE(SUM(
            CASE
                WHEN f.team1_id = ce.team_id THEN f.score1
                WHEN f.team2_id = ce.team_id THEN f.score2
                ELSE 0
            END
        ), 0)::integer AS score1,
        COALESCE(SUM(
            CASE
                WHEN f.team1_id = ce.team_id THEN f.score2
                WHEN f.team2_id = ce.team_id THEN f.score1
                ELSE 0
            END
        ), 0)::integer AS score2,
        COALESCE(SUM(
            CASE
                WHEN f.id IS NULL THEN 0
                WHEN f.score1 = f.score2 THEN 1
                WHEN (f.team1_id = ce.team_id AND f.score1 > f.score2)
                  OR (f.team2_id = ce.team_id AND f.score2 > f.score1)
                    THEN CASE WHEN f.is_overtime THEN 2 ELSE 3 END
                WHEN f.is_overtime THEN 1
                ELSE 0
            END
        ), 0)::integer AS points
    FROM current_entries AS ce
    LEFT JOIN fights AS f
        ON f.table_id = ce.table_id
       AND f.is_present = true
       AND (f.team1_id = ce.team_id OR f.team2_id = ce.team_id)
    GROUP BY ce.id
)
UPDATE table_entries AS te
SET
    counter = stats.counter,
    win = stats.win,
    overtime_win = stats.overtime_win,
    tram = stats.tram,
    overtime_loss = stats.overtime_loss,
    lost = stats.lost,
    score1 = stats.score1,
    score2 = stats.score2,
    points = stats.points
FROM stats
WHERE te.id = stats.id;
