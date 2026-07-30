<?php 

ob_start();

function load_env($path) {
    if (!file_exists($path)) {
        return;   // no .env file — assume env vars are set by the platform (e.g. Render)
    }

    foreach (file($path) as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] == '#') {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"');

        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

load_env(__DIR__ . '/../.env');

define("SUPABASE_URL", $_ENV["SUPABASE_URL"] ?? "");
define("SUPABASE_ANON_KEY", $_ENV["SUPABASE_ANON_KEY"] ?? "");
define("GROQ_API_KEY", $_ENV["GROQ_API_KEY"] ?? "");
define("APP_ENV", $_ENV["APP_ENV"] ?? "local");

define("APP_TIME_LIMIT_SECONDS", 300);
define("APP_QUESTIONS_PER_QUIZ", 10);

$required = [
    "SUPABASE_URL",
    "SUPABASE_ANON_KEY",
    "GROQ_API_KEY"
];
foreach ($required as $key){
    if(empty(constant($key))){
        die("Missing required environment variable: $key - check your .env file");
    }
}

if(session_status() != PHP_SESSION_ACTIVE){
    session_start();
}

if(APP_ENV == "local"){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
else{
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set('UTC');
?>
