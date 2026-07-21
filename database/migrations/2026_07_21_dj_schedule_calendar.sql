-- Add missing columns to radio_dj_schedule for calendar view
ALTER TABLE radio_dj_schedule 
  ADD COLUMN IF NOT EXISTS show_name VARCHAR(255) DEFAULT '' AFTER time_slot,
  ADD COLUMN IF NOT EXISTS day_of_week TINYINT DEFAULT NULL AFTER show_name,
  ADD COLUMN IF NOT EXISTS start_time TIME DEFAULT NULL AFTER day_of_week,
  ADD COLUMN IF NOT EXISTS end_time TIME DEFAULT NULL AFTER start_time,
  ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER status,
  ADD COLUMN IF NOT EXISTS created_by VARCHAR(50) DEFAULT 'dj' AFTER is_active;

SELECT 'SCHEDULE_CALENDAR_OK' AS result;
