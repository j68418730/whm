-- Node agent v2.1: track multiple install locations/drives reported by the
-- agent plus host metadata (OS/arch/agent version).

ALTER TABLE game_nodes
  ADD COLUMN locations TEXT NULL,
  ADD COLUMN os VARCHAR(50) NULL,
  ADD COLUMN arch VARCHAR(20) NULL,
  ADD COLUMN agent_version VARCHAR(30) NULL,
  ADD COLUMN disks TEXT NULL;

-- Note: if this column set was already applied by a previous run the ALTER
-- above can be skipped. The following sanity update is harmless:
UPDATE game_nodes SET locations = NULL WHERE locations = '';