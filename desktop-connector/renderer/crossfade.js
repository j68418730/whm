// ─── CROSSFADE ENGINE ───
const crossfade = {
    settings: {
        mode: 'cosine',
        fadeIn: 2,
        fadeOut: 3,
        threshold: 20,
        curve: 'cosine',
        beatMatch: true,
        detectSilence: true
    },
    
    init() {
        if (appConfig.crossfadeSettings) this.settings = appConfig.crossfadeSettings;
        this.applySettings();
    },
    
    applySettings() {
        // Set defaults from saved settings
        crossfadeDuration = this.settings.fadeOut || 3;
        const slider = document.getElementById('crossfadeSlider');
        const label = document.getElementById('crossfadeLabel');
        const mode = document.getElementById('xfMode');
        if (slider) { slider.value = crossfadeDuration; label.textContent = crossfadeDuration + 's'; }
        if (mode) mode.value = this.settings.mode || 'cosine';
        
        // Persist crossfadeDuration
        appConfig.crossfadeDuration = crossfadeDuration;
        api.saveConfig(appConfig);
    },
    
    showSettings() {
        document.getElementById('xfModeSetting').value = this.settings.mode || 'cosine';
        document.getElementById('xfFadeIn').value = this.settings.fadeIn || 2;
        document.getElementById('xfFadeOut').value = this.settings.fadeOut || 3;
        document.getElementById('xfThreshold').value = this.settings.threshold || 20;
        document.getElementById('xfCurve').value = this.settings.curve || 'cosine';
        document.getElementById('xfBeatMatch').checked = this.settings.beatMatch !== false;
        document.getElementById('xfDetectSilence').checked = this.settings.detectSilence !== false;
        document.getElementById('xfSettings').style.display = 'flex';
    },
    
    save() {
        this.settings = {
            mode: document.getElementById('xfModeSetting').value,
            fadeIn: parseFloat(document.getElementById('xfFadeIn').value) || 2,
            fadeOut: parseFloat(document.getElementById('xfFadeOut').value) || 3,
            threshold: parseInt(document.getElementById('xfThreshold').value) || 20,
            curve: document.getElementById('xfCurve').value,
            beatMatch: document.getElementById('xfBeatMatch').checked,
            detectSilence: document.getElementById('xfDetectSilence').checked
        };
        crossfadeDuration = this.settings.fadeOut;
        appConfig.crossfadeSettings = this.settings;
        appConfig.crossfadeDuration = crossfadeDuration;
        api.saveConfig(appConfig);
        
        // Update UI
        document.getElementById('crossfadeSlider').value = crossfadeDuration;
        document.getElementById('crossfadeLabel').textContent = crossfadeDuration + 's';
        document.getElementById('xfMode').value = this.settings.mode;
        document.getElementById('xfSettings').style.display = 'none';
        
        eventLog.log('🎚', `Crossfade: ${this.settings.mode} mode, ${crossfadeDuration}s fade`);
    },
    
    // Called when a track is nearing its end to calculate the crossfade
    getFadeParams(trackDuration, currentPosition, activeDeck) {
        const remaining = trackDuration - currentPosition;
        const mode = this.settings.mode;
        const fadeOut = this.settings.fadeOut;
        const threshold = this.settings.threshold / 100;
        let triggerAt = remaining - fadeOut; // seconds from end
        
        if (mode === 'threshold') {
            // Would normally detect volume level; simulate with position
            triggerAt = remaining - (trackDuration * threshold);
        } else if (mode === 'smart') {
            // Combine time-based + threshold
            triggerAt = Math.min(remaining - fadeOut, trackDuration * (1 - threshold));
        }
        // 'fixed' and 'cosine' use standard time-based trigger
        
        return {
            triggerAt: Math.max(0, triggerAt),
            fadeInDuration: this.settings.fadeIn,
            fadeOutDuration: fadeOut,
            curve: this.settings.curve
        };
    },
    
    // Calculate gain value for a given position in the crossfade
    getGain(progress, type) {
        // progress: 0 = start of fade, 1 = end of fade
        // type: 'out' for outgoing deck, 'in' for incoming deck
        const clamped = Math.max(0, Math.min(1, progress));
        const curve = this.settings.curve;
        
        let out, inGain;
        switch(curve) {
            case 'linear':
                out = 1 - clamped;
                inGain = clamped;
                break;
            case 'cosine':
                out = Math.cos(clamped * Math.PI / 2);
                inGain = Math.sin(clamped * Math.PI / 2);
                break;
            case 'log':
                out = 1 - Math.log(1 + clamped * 9) / Math.log(10);
                inGain = Math.log(1 + clamped * 9) / Math.log(10);
                break;
            case 'expo':
                out = Math.pow(1 - clamped, 2);
                inGain = Math.pow(clamped, 2);
                break;
            default:
                out = 1 - clamped;
                inGain = clamped;
        }
        return { out, in: inGain };
    }
};

// Override the scheduleNext to use crossfade engine params
const origScheduleNext = window.scheduleNext || function(){};
// The crossfade engine integrates with the existing scheduleNext function
// via crossfadeDuration and the getGain/getFadeParams methods

// Init on app start
setTimeout(() => crossfade.init(), 1000);

// Wire crossfade mode selector
document.addEventListener('DOMContentLoaded', () => {
    const mode = document.getElementById('xfMode');
    if (mode) {
        mode.addEventListener('change', function() {
            crossfade.settings.mode = this.value;
            appConfig.crossfadeSettings = crossfade.settings;
            api.saveConfig(appConfig);
        });
    }
});