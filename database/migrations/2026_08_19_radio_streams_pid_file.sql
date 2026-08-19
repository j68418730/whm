-- RadioProvision writes pid_file to radio_streams (streaming_stations already has it)
ALTER TABLE radio_streams ADD COLUMN pid_file VARCHAR(255) NULL AFTER status;