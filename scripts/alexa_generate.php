<?php
/**
 * Alexa Skill Generator
 * Builds an ASK-CLI-ready amzn1.ask.skill.zip for a streaming station.
 *
 * Usage:
 *   php alexa_generate.php <station_id> [out_dir]
 *
 * Reads alexa_* settings from automation_settings for stream URL base, price,
 * AWS region/account, privacy/terms URLs. Outputs:
 *   <out_dir>/alexa-<station_id>/amzn1.ask.skill.zip
 */

define('BASE_PATH', dirname(__DIR__));
$envFile = BASE_PATH . '/.env';
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (str_contains($line, '=')) { [$k, $v] = explode('=', $line, 2); putenv(trim($k) . '=' . trim($v)); }
}

$pdo = new PDO(
    'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=' . getenv('DB_DATABASE') . ';charset=utf8mb4',
    getenv('DB_USERNAME'), getenv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$setting = function ($key, $default = '') use ($pdo) {
    try {
        $st = $pdo->prepare("SELECT setting_value FROM automation_settings WHERE setting_key = ?");
        $st->execute([$key]);
        $v = trim((string)$st->fetchColumn());
        return $v !== '' ? $v : $default;
    } catch (Exception $e) { return $default; }
};

$stationId = (int)($argv[1] ?? 0);
$outBase = $argv[2] ?? (BASE_PATH . '/storage/alexa');
if (!$stationId) { fwrite(STDERR, "Usage: php alexa_generate.php <station_id> [out_dir]\n"); exit(1); }

$st = $pdo->prepare("SELECT ss.*, hu.username AS owner, hu.email AS owner_email FROM streaming_stations ss LEFT JOIN hosting_users hu ON hu.id = ss.user_id WHERE ss.id = ?");
$st->execute([$stationId]);
$station = $st->fetch(PDO::FETCH_OBJ);
if (!$station) { fwrite(STDERR, "Station $stationId not found\n"); exit(1); }

// ---- Configuration from settings ----
$urlBase = rtrim($setting('alexa_skill_url_base', 'https://planet-hosts.com'), '/');
$awsRegion = $setting('alexa_aws_region', 'us-east-1');
$awsAccount = $setting('alexa_aws_account_id', '');
$sharedArn = $setting('alexa_lambda_arn', '');
$privacy = $setting('alexa_privacy_url', $urlBase . '/privacy');
$terms = $setting('alexa_terms_url', $urlBase . '/terms');
$price = $setting('alexa_skill_price', '99.00');

$stationName = trim($station->name ?? '');
$owner = trim($station->owner ?? '');
$port = (int)($station->port ?? 8000);
$streamUrl = $urlBase . ':' . $port . '/';
$domain = parse_url($urlBase, PHP_URL_HOST) ?: 'planet-hosts.com';

// Skill name / invocation derived from station name
$skillKey = strtolower(preg_replace('/[^A-Za-z0-9]+/', '', $stationName));
$invocation = strtolower(trim(preg_replace('/\s+/', ' ', $stationName)));

// ---- Skill folder + zip ----
$dir = rtrim($outBase, '/') . '/alexa-' . $stationId;
@mkdir($dir . '/lambda/languages', 0777, true);
@mkdir($dir . '/interactionModels/custom', 0777, true);
@mkdir($dir . '/assets/images', 0777, true);

// ---- skill.json ----
if ($sharedArn !== '') {
    $endpointArr = ['uri' => $sharedArn];
} else {
    $fn = 'station-' . $stationId . '-Release_1';
    $arn = 'arn:aws:lambda:' . $awsRegion . ':' . ($awsAccount ?: 'ACCOUNT_ID') . ':function:' . $fn;
    $endpointArr = ['uri' => $arn];
}

$skillJson = [
    'manifest' => [
        'publishingInformation' => [
            'locales' => [
                'en-US' => [
                    'summary' => "Listen to {$stationName}",
                    'examplePhrases' => ["Alexa Open " . ucwords($invocation), "Alexa Open " . $skillKey, "Alexa open " . $invocation],
                    'keywords' => [$skillKey, 'livestream', 'radio', 'shoutcast', 'icecast', $domain],
                    'name' => ucfirst($skillKey) ?: 'RadioStation',
                    'description' => "Online Radio Station App For {$domain}\n24/7 Live music. Stream: {$streamUrl}",
                    'smallIconUri' => 'file://assets/images/en-US_smallIconUri.png',
                    'largeIconUri' => 'file://assets/images/en-US_largeIconUri.png',
                ],
            ],
            'automaticDistribution' => ['isActive' => false],
            'isAvailableWorldwide' => true,
            'testingInstructions' => 'No special instructions.',
            'category' => 'STREAMING_SERVICE',
            'distributionMode' => 'PUBLIC',
            'distributionCountries' => [],
        ],
        'apis' => [
            'custom' => [
                'endpoint' => $endpointArr,
                'interfaces' => [['type' => 'AUDIO_PLAYER']],
                'regions' => [
                    'EU' => ['endpoint' => $endpointArr],
                    'NA' => ['endpoint' => $endpointArr],
                    'FE' => ['endpoint' => $endpointArr],
                ],
            ],
        ],
        'manifestVersion' => '1.0',
        'privacyAndCompliance' => [
            'allowsPurchases' => false,
            'locales' => ['en-US' => ['termsOfUseUrl' => $terms, 'privacyPolicyUrl' => $privacy]],
            'containsAds' => false,
            'isExportCompliant' => true,
            'isChildDirected' => false,
            'usesPersonalInfo' => false,
        ],
    ],
];
file_put_contents($dir . '/skill.json', json_encode($skillJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// ---- interactionModels/custom/en-US.json ----
$intentNames = ['AMAZON.NavigateHomeIntent','AMAZON.PauseIntent','AMAZON.ResumeIntent','AMAZON.StopIntent','AMAZON.CancelIntent','AMAZON.LoopOnIntent','AMAZON.NextIntent','AMAZON.PreviousIntent','AMAZON.RepeatIntent','AMAZON.ShuffleOnIntent','AMAZON.StartOverIntent','AMAZON.LoopOffIntent','AMAZON.ShuffleOffIntent','AMAZON.FallbackIntent'];
$intents = [];
foreach ($intentNames as $n) { $intents[] = ['name' => $n, 'samples' => []]; }
$intents[] = ['name' => 'AMAZON.HelpIntent', 'samples' => ['help me','how does this work','what should i do']];
$intents[] = ['name' => 'AboutIntent', 'slots' => [], 'samples' => ['about','what is this','tell me about this']];

$model = [
    'interactionModel' => [
        'languageModel' => [
            'invocationName' => $invocation,
            'intents' => $intents,
            'types' => [],
            'modelConfiguration' => ['fallbackIntentSensitivity' => ['level' => 'LOW']],
        ],
    ],
    'version' => '3',
];
file_put_contents($dir . '/interactionModels/custom/en-US.json', json_encode($model, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// ---- lambda/languages/en.json + en-US.json ----
$prompts = [
    'SKILL_NAME' => $stationName,
    'DEVICE_NOT_SUPPORTED' => 'Sorry, this device does not support audio playback.',
    'ABOUT' => ["This is the {$stationName} skill. Play music 24/7 from {$domain}."],
    'ABOUT_REPROMPT' => ['You can say play, or stop.'],
    'HELP' => ["Say play to start listening to {$stationName}."],
    'HELP_REPROMPT' => ['Say play to start.'],
    'FALLBACK' => ['I did not understand that. Say play or stop.'],
    'FALLBACK_REPROMPT' => ['Say play or stop.'],
    'ERROR' => ['Sorry, something went wrong. Try again.'],
    'ERROR_REPROMPT' => ['Say play to try again.'],
    'UNHANDLED' => ['That feature is not available. Say play or stop.'],
];
$lang = ['language' => 'en', 'responses' => $prompts];
file_put_contents($dir . '/lambda/languages/en.json', json_encode($lang, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
$lang['language'] = 'en-US';
file_put_contents($dir . '/lambda/languages/en-US.json', json_encode($lang, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// ---- lambda/lambda_function.py ----
$py = <<<'PY'
import logging
import json
import random

from ask_sdk_core.utils import is_request_type, is_intent_name
from ask_sdk_core.dispatch_components import (AbstractRequestHandler, AbstractExceptionHandler, AbstractRequestInterceptor, AbstractResponseInterceptor)
from ask_sdk_core.skill_builder import SkillBuilder
from ask_sdk_model.interfaces.audioplayer import (
    PlayDirective, PlayBehavior, AudioItem, Stream, AudioItemMetadata,
    StopDirective, ClearQueueDirective, ClearBehavior)

logger = logging.getLogger(__name__)
logger.setLevel(logging.INFO)

# Audio stream metadata (generated per station)
STREAMS = [
  {
    "token": '1',
    "url": '__STREAM_URL__',
    "metadata": {
      "title": '__STATION_NAME__',
      "subtitle": '__STATION_NAME__',
      "art": {"sources": [{"contentDescription": 'station art', "url": '__ART_URL__', "widthPixels": 512, "heightPixels": 512}]},
      "backgroundImage": {"sources": [{"contentDescription": 'background', "url": '__BG_URL__', "widthPixels": 1200, "heightPixels": 800}]}
    }
  }
]

class CheckAudioInterfaceHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        if handler_input.request_envelope.context.system.device:
            return handler_input.request_envelope.context.system.device.supported_interfaces.audio_player is None
        return False
    def handle(self, handler_input):
        language_prompts = handler_input.attributes_manager.request_attributes["_"]
        return (handler_input.response_builder.speak(language_prompts["DEVICE_NOT_SUPPORTED"]).set_should_end_session(True).response)

class LaunchRequestHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return is_request_type("LaunchRequest")(handler_input)
    def handle(self, handler_input):
        stream = STREAMS[0]
        return (handler_input.response_builder
            .speak("Starting {}".format(stream["metadata"]["title"]))
            .add_directive(PlayDirective(play_behavior=PlayBehavior.REPLACE_ALL,
                audio_item=AudioItem(stream=Stream(token=stream["token"], url=stream["url"], offset_in_milliseconds=0, expected_previous_token=None), metadata=stream["metadata"])))
            .set_should_end_session(True).response)

class ResumeStreamIntentHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return (is_request_type("PlaybackController.PlayCommandIssued")(handler_input) or is_intent_name("AMAZON.ResumeIntent")(handler_input))
    def handle(self, handler_input):
        stream = STREAMS[0]
        return (handler_input.response_builder
            .add_directive(PlayDirective(play_behavior=PlayBehavior.REPLACE_ALL,
                audio_item=AudioItem(stream=Stream(token=stream["token"], url=stream["url"], offset_in_milliseconds=0, expected_previous_token=None), metadata=stream["metadata"])))
            .set_should_end_session(True).response)

class UnhandledFeaturesIntentHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return (is_intent_name("AMAZON.LoopOnIntent")(handler_input) or is_intent_name("AMAZON.NextIntent")(handler_input)
            or is_intent_name("AMAZON.PreviousIntent")(handler_input) or is_intent_name("AMAZON.RepeatIntent")(handler_input)
            or is_intent_name("AMAZON.ShuffleOnIntent")(handler_input) or is_intent_name("AMAZON.StartOverIntent")(handler_input)
            or is_intent_name("AMAZON.ShuffleOffIntent")(handler_input) or is_intent_name("AMAZON.LoopOffIntent")(handler_input))
    def handle(self, handler_input):
        language_prompts = handler_input.attributes_manager.request_attributes["_"]
        return (handler_input.response_builder.speak(random.choice(language_prompts["UNHANDLED"])).set_should_end_session(True).response)

class AboutIntentHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return is_intent_name("AboutIntent")(handler_input)
    def handle(self, handler_input):
        language_prompts = handler_input.attributes_manager.request_attributes["_"]
        return (handler_input.response_builder.speak(random.choice(language_prompts["ABOUT"])).ask(random.choice(language_prompts["ABOUT_REPROMPT"])).response)

class HelpIntentHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return is_intent_name("AMAZON.HelpIntent")(handler_input)
    def handle(self, handler_input):
        language_prompts = handler_input.attributes_manager.request_attributes["_"]
        return (handler_input.response_builder.speak(random.choice(language_prompts["HELP"])).ask(random.choice(language_prompts["HELP_REPROMPT"])).response)

class CancelOrStopIntentHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return (is_intent_name("AMAZON.CancelIntent")(handler_input) or is_intent_name("AMAZON.StopIntent")(handler_input) or is_intent_name("AMAZON.PauseIntent")(handler_input))
    def handle(self, handler_input):
        return (handler_input.response_builder
            .add_directive(ClearQueueDirective(clear_behavior=ClearBehavior.CLEAR_ALL))
            .add_directive(StopDirective()).set_should_end_session(True).response)

class PlaybackStartedIntentHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return is_request_type("AudioPlayer.PlaybackStarted")(handler_input)
    def handle(self, handler_input):
        return (handler_input.response_builder.add_directive(ClearQueueDirective(clear_behavior=ClearBehavior.CLEAR_ENQUEUED)).response)

class PlaybackStoppedIntentHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return (is_request_type("PlaybackController.PauseCommandIssued")(handler_input) or is_request_type("AudioPlayer.PlaybackStopped")(handler_input))
    def handle(self, handler_input):
        return (handler_input.response_builder
            .add_directive(ClearQueueDirective(clear_behavior=ClearBehavior.CLEAR_ALL))
            .add_directive(StopDirective()).set_should_end_session(True).response)

class PlaybackFailedIntentHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return is_request_type("AudioPlayer.PlaybackFailed")(handler_input)
    def handle(self, handler_input):
        stream = STREAMS[0]
        return (handler_input.response_builder
            .add_directive(PlayDirective(play_behavior=PlayBehavior.REPLACE_ALL,
                audio_item=AudioItem(stream=Stream(token=stream["token"], url=stream["url"], offset_in_milliseconds=0, expected_previous_token=None), metadata=stream["metadata"])))
            .set_should_end_session(True).response)

class FallbackIntentHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return is_intent_name("AMAZON.FallbackIntent")(handler_input)
    def handle(self, handler_input):
        language_prompts = handler_input.attributes_manager.request_attributes["_"]
        return (handler_input.response_builder.speak(random.choice(language_prompts["FALLBACK"])).ask(random.choice(language_prompts["FALLBACK_REPROMPT"])).response)

class SessionEndedRequestHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return is_request_type("SessionEndedRequest")(handler_input)
    def handle(self, handler_input):
        return handler_input.response_builder.response

class ExceptionEncounteredRequestHandler(AbstractRequestHandler):
    def can_handle(self, handler_input):
        return is_request_type("System.ExceptionEncountered")(handler_input)
    def handle(self, handler_input):
        return handler_input.response_builder.response

class LocalizationInterceptor(AbstractRequestInterceptor):
    def process(self, handler_input):
        locale = handler_input.request_envelope.request.locale
        try:
            with open("languages/"+str(locale)+".json") as language_data:
                language_prompts = json.load(language_data)
        except:
            with open("languages/"+str(locale[:2])+".json") as language_data:
                language_prompts = json.load(language_data)
        handler_input.attributes_manager.request_attributes["_"] = language_prompts

class CatchAllExceptionHandler(AbstractExceptionHandler):
    def can_handle(self, handler_input, exception):
        return True
    def handle(self, handler_input, exception):
        logger.error(exception, exc_info=True)
        language_prompts = handler_input.attributes_manager.request_attributes["_"]
        return (handler_input.response_builder.speak(language_prompts["ERROR"]).ask(language_prompts["ERROR_REPROMPT"]).response)

sb = SkillBuilder()
sb.add_request_handler(CheckAudioInterfaceHandler())
sb.add_request_handler(LaunchRequestHandler())
sb.add_request_handler(ResumeStreamIntentHandler())
sb.add_request_handler(UnhandledFeaturesIntentHandler())
sb.add_request_handler(CancelOrStopIntentHandler())
sb.add_request_handler(HelpIntentHandler())
sb.add_request_handler(AboutIntentHandler())
sb.add_request_handler(FallbackIntentHandler())
sb.add_request_handler(PlaybackStartedIntentHandler())
sb.add_request_handler(PlaybackStoppedIntentHandler())
sb.add_request_handler(PlaybackFailedIntentHandler())
sb.add_request_handler(SessionEndedRequestHandler())
sb.add_exception_handler(CatchAllExceptionHandler())
sb.add_global_request_interceptor(LocalizationInterceptor())
lambda_handler = sb.lambda_handler()
PY;

$artUrl = $urlBase . '/images/' . $skillKey . '.jpg';
$bgUrl = $urlBase . '/background/' . $skillKey . '.jpg';
$py = str_replace('__STREAM_URL__', $streamUrl, $py);
$py = str_replace('__STATION_NAME__', str_replace("'", '', $stationName), $py);
$py = str_replace('__ART_URL__', $artUrl, $py);
$py = str_replace('__BG_URL__', $bgUrl, $py);
file_put_contents($dir . '/lambda/lambda_function.py', $py);

// ---- requirements.txt ----
file_put_contents($dir . '/lambda/requirements.txt', "ask-sdk-core\nask-sdk-model\n");

// ---- assets (placeholder icons) ----
// Generate simple placeholder PNG icons (1x1). Real skill icons can be replaced by the user.
$px = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
file_put_contents($dir . '/assets/images/en-US_smallIconUri.png', $px);
file_put_contents($dir . '/assets/images/en-US_largeIconUri.png', $px);

// ---- Zip it (skill.json must be at the zip root) ----
$zipPath = $dir . '/amzn1.ask.skill.zip';
@unlink($zipPath);
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) { fwrite(STDERR, "Cannot create zip\n"); exit(1); }
$addDir = function ($base, $prefix = '') use (&$addDir, $zip) {
    $files = scandir($base);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $p = $base . '/' . $f;
        if (is_dir($p)) { $addDir($p, $prefix . $f . '/'); }
        else { $zip->addFile($p, $prefix . $f); }
    }
};
// Add skill.json at root, then subdirs
$zip->addFile($dir . '/skill.json', 'skill.json');
$addDir($dir . '/interactionModels', 'interactionModels/');
$addDir($dir . '/lambda', 'lambda/');
$addDir($dir . '/assets', 'assets/');
$zip->close();

echo json_encode([
    'ok' => true,
    'station_id' => $stationId,
    'station_name' => $stationName,
    'stream_url' => $streamUrl,
    'invocation_name' => $invocation,
    'skill_key' => $skillKey,
    'zip' => $zipPath,
    'size' => filesize($zipPath),
], JSON_PRETTY_PRINT);
exit(0);