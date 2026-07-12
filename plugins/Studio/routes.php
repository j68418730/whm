<?php

if (!isset($router)) {
    $router = \Core\Application::getInstance()->get('router');
}

// ── Phase 1: Read-only Studio Routes ──

$router->get('/admin/studio', 'Plugins\Studio\Controllers\StudioController@index');
$router->get('/admin/studio/dashboard/{stationId}', 'Plugins\Studio\Controllers\StudioController@dashboard');
$router->get('/admin/studio/stations', 'Plugins\Studio\Controllers\StudioController@stations');
$router->get('/admin/studio/station/{id}', 'Plugins\Studio\Controllers\StudioController@station');
$router->get('/admin/studio/station/{id}/queue', 'Plugins\Studio\Controllers\StudioController@queue');
$router->get('/admin/studio/station/{id}/history', 'Plugins\Studio\Controllers\StudioController@history');
$router->get('/admin/studio/station/{id}/library', 'Plugins\Studio\Controllers\StudioController@library');
$router->get('/admin/studio/station/{id}/playlists', 'Plugins\Studio\Controllers\StudioController@playlists');
$router->get('/admin/studio/station/{id}/playlist/{playlistId}', 'Plugins\Studio\Controllers\StudioController@playlistItems');
$router->get('/admin/studio/station/{id}/djs', 'Plugins\Studio\Controllers\StudioController@djs');
$router->get('/admin/studio/station/{id}/requests', 'Plugins\Studio\Controllers\StudioController@requests');
$router->get('/admin/studio/station/{id}/schedule', 'Plugins\Studio\Controllers\StudioController@schedule');
$router->get('/admin/studio/station/{id}/stats', 'Plugins\Studio\Controllers\StudioController@stats');
$router->get('/admin/studio/station/{id}/analytics', 'Plugins\Studio\Controllers\StudioController@analytics');
$router->get('/admin/studio/station/{id}/health', 'Plugins\Studio\Controllers\StudioController@health');
$router->get('/admin/studio/station/{id}/current-song', 'Plugins\Studio\Controllers\StudioController@currentSong');

// Public Studio Widget Embed
$router->get('/studio/widget/{stationId}', 'Plugins\Studio\Controllers\StudioController@widget');

// ── Phase 2: Queue Editing ──

$router->post('/admin/studio/station/{id}/queue/add', 'Plugins\Studio\Controllers\StudioController@queueAdd');
$router->post('/admin/studio/station/{id}/queue/remove', 'Plugins\Studio\Controllers\StudioController@queueRemove');
$router->post('/admin/studio/station/{id}/queue/reorder', 'Plugins\Studio\Controllers\StudioController@queueReorder');
$router->post('/admin/studio/station/{id}/queue/clear', 'Plugins\Studio\Controllers\StudioController@queueClear');
$router->get('/admin/studio/station/{id}/studio-queue', 'Plugins\Studio\Controllers\StudioController@studioQueue');

// ── Phase 2: Playback Control ──

$router->post('/admin/studio/station/{id}/play', 'Plugins\Studio\Controllers\StudioController@play');
$router->post('/admin/studio/station/{id}/stop', 'Plugins\Studio\Controllers\StudioController@stop');
$router->post('/admin/studio/station/{id}/cue', 'Plugins\Studio\Controllers\StudioController@cue');

// ── Phase 2: Playlist Editing ──

$router->post('/admin/studio/station/{id}/playlist/create', 'Plugins\Studio\Controllers\StudioController@playlistCreate');
$router->post('/admin/studio/station/{id}/playlist/{playlistId}/delete', 'Plugins\Studio\Controllers\StudioController@playlistDelete');
$router->post('/admin/studio/station/{id}/playlist/{playlistId}/add-song', 'Plugins\Studio\Controllers\StudioController@playlistAddSong');
$router->post('/admin/studio/station/{id}/playlist/{playlistId}/remove-song/{itemId}', 'Plugins\Studio\Controllers\StudioController@playlistRemoveSong');

// ── Phase 2: Request Management ──

$router->post('/admin/studio/station/{id}/request/{requestId}/approve', 'Plugins\Studio\Controllers\StudioController@requestApprove');
$router->post('/admin/studio/station/{id}/request/{requestId}/reject', 'Plugins\Studio\Controllers\StudioController@requestReject');

// ── Phase 2: Voice Tracking ──

$router->get('/admin/studio/station/{id}/voice-tracks', 'Plugins\Studio\Controllers\StudioController@voiceTracks');
$router->post('/admin/studio/station/{id}/voice/save', 'Plugins\Studio\Controllers\StudioController@voiceSave');
$router->post('/admin/studio/station/{id}/voice/{trackId}/delete', 'Plugins\Studio\Controllers\StudioController@voiceDelete');

// ── Phase 2: Desktop Connector API ──

$router->post('/connector/auth', 'Plugins\Studio\Controllers\StudioController@connectorAuth');
$router->get('/connector/station/{id}/library', 'Plugins\Studio\Controllers\StudioController@connectorLibrary');
$router->get('/connector/station/{id}/queue', 'Plugins\Studio\Controllers\StudioController@connectorQueue');
$router->get('/connector/station/{id}/status', 'Plugins\Studio\Controllers\StudioController@connectorStatus');
$router->get('/connector/station/{id}/history', 'Plugins\Studio\Controllers\StudioController@connectorHistory');
$router->post('/connector/station/{id}/upload', 'Plugins\Studio\Controllers\StudioController@connectorUpload');
$router->get('/connector/devices', 'Plugins\Studio\Controllers\StudioController@connectorDevices');
$router->get('/connector/download', 'Plugins\Studio\Controllers\StudioController@connectorDownload');

// ── Phase 3: Real-time Streaming (SSE) ──

$router->get('/admin/studio/station/{id}/sse', 'Plugins\Studio\Controllers\StudioController@sse');
$router->get('/admin/studio/station/{id}/connector-logs', 'Plugins\Studio\Controllers\StudioController@connectorLogs');

// ── Album Art, Voice FX, Audio Preview ──

$router->get('/admin/studio/station/{id}/album-art/{itemId}', 'Plugins\Studio\Controllers\StudioController@albumArt');
$router->get('/admin/studio/station/{id}/audio-preview/{itemId}', 'Plugins\Studio\Controllers\StudioController@audioPreview');
$router->get('/admin/studio/voice-fx-presets', 'Plugins\Studio\Controllers\StudioController@voiceFxPresets');
$router->post('/admin/studio/station/{id}/voice-fx/{itemId}', 'Plugins\Studio\Controllers\StudioController@voiceFxApply');