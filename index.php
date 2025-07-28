<?php

require_once __DIR__ . '/vendor/autoload.php';

function cache()
{
    // Cache for 7 days
    header('Cache-Control: max-age=604800', true);
}
function minify($content)
{
    return trim(preg_replace(
        [
            '/ {2,}/',
            '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s',
        ],
        [
            ' ',
            ''
        ],
        $content
    ));
}

function serve_file($file, $mime = null, $minify = false)
{
    if (!file_exists($file)) {
        http_response_code(404);
        echo 'Not Found';
        exit;
    }

    cache();

    // Basic MIME type detection if not passed
    if (!$mime) {
        $mime = mime_content_type($file);
    }

    header("Content-Type: $mime");
    header('Content-Length: ' . filesize($file));

    $output = file_get_contents($file);
    $output = $minify ? minify($output) : $output;

    echo $output;
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

header_remove('Server');
header_remove('X-Powered-By');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (preg_match('#^/js/[^/]+\.js$#', $path)) {
    serve_file(__DIR__ . '/public' . $path, 'application/javascript', minify: true);
} else if (preg_match('#^/fonts/.+/fonts/[^/]+\.(woff2?|ttf|otf|eot|svg)$#', $path)) {
    serve_file(__DIR__ . '/public' . $path);
    exit;
} else if (preg_match('#^/images/[^/]+\.(jpe?g|png|gif|svg|webp|ico)$#i', $path)) {
    serve_file(__DIR__ . '/public' . $path);
    exit;
}


if ($path == '/') {
    cache();
    require_once __DIR__ . '/load-env.php';

    ob_start();

    $app_name = 'AI Assignment Generator';
    $og_image = $_ENV['APP_BASE_URL'] . '/images/og-image.png';
    $title = 'AI Assignment Generator - Not Replacing You Indeed Doubling Your Productivity';
    $description = 'An AI assignment generator that helps you create assignments by typing questions and generating answers using AI.';
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>AI Assignment Generator</title>
        <meta name="description" content="<?= $description ?>" />
        <link rel="canonical" href="<?= $_ENV['APP_BASE_URL'] . $path ?>" />

        <meta property="twitter:title" content="<?= $title ?>">
        <meta property="twitter:description" content="<?= $description ?>">
        <meta property="twitter:image" content="<?= $og_image ?>">
        <meta property="twitter:card" content="summary_large_image">


        <meta property="og:image" content="<?= $og_image ?>">
        <meta property="og:site_name" content="<?= $app_name ?>">
        <meta property="og:title" content="<?= $title ?>">
        <meta property="og:description" content="<?= $description ?>" />
        <meta property="og:url" content="<?= $_ENV['APP_BASE_URL'] . $path ?>">


        <!-- Prefetch Images -->
        <link rel="prefetch" as="image" href="/images/background.png">
        <link rel="prefetch" as="image" href="/images/primary-texture.png">

        <style>
            <?= minify(file_get_contents(__DIR__ . '/public/css/styles.css')) ?>
        </style>

        <style>
            <?= minify(str_replace('../', '/fonts/GeneralSans_Complete/', file_get_contents(__DIR__ . '/public/fonts/GeneralSans_Complete/css/general-sans.css'))) ?>
        </style>

        <style>
            <?= minify(str_replace('../', '/fonts/Literata_Complete/', file_get_contents(__DIR__ . '/public/fonts/Literata_Complete/css/literata.css'))) ?>
        </style>

        <script src="/js/jquery.min.js" defer></script>
        <script src="/js/app.js" defer></script>

        <style>
            * {
                font-family: 'GeneralSans-Medium', sans-serif;
            }

            h1,
            h2,
            h3,
            h4,
            h5,
            h6 {
                font-family: 'Literata-Medium', serif;
            }
        </style>
        <script async>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/js/sw.js');
            }
        </script>
    </head>

    <body class="min-h-screen font-sans overflow-hidden max-w-screen w-full md:p-6" style="background: url('/images/background.png') repeat center center; background-size: contain;">

        <div class="loader fixed inset-0 bg-white text-green-700 grid place-items-center" id="screen-loader">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-14 md:size-20 animate-spin">
                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </svg>
        </div>


        <div class="hidden p-10 max-w-4xl container mx-auto relative !z-30 bg-white md:h-max min-h-screen md:rounded-2xl" id="main-content">
            <div class="max-w-4xl">
                <div class="mb-8">
                    <h1 class="text-4xl leading-relaxed font-bold text-green-700 mb-2">Assignment Questions</h1>
                    <p class="text-gray-500">Type your questions, adjust settings, and generate your assignment.</p>
                </div>

                <form id="add-form" class="flex gap-2 mb-8">
                    <input type="text" id="new-question" class="flex-1 w-full px-4 py-2 border border-gray-300 rounded-xl bg-transparent" placeholder="Type your new question here..." required autofocus />
                    <button class="cursor-pointer bg-green-700 text-white px-5 py-3 rounded-xl flex gap-2 items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Add
                    </button>
                </form>

                <ul id="question-list" class="leading-loose mb-12 space-y-0.5"></ul>


                <ul id="progress-list" class="mb-12 leading-loose hidden space-y-0.5"></ul>


                <form class="grid grid-cols-2 gap-4 mb-6" id="generate-form">
                    <div>
                        <label for="min-words" class="block text-sm text-gray-700 mb-1">Minimum Words</label>
                        <input type="number" id="min-words" min="10" class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-transparent" placeholder="e.g. 300" value="5000" required />
                    </div>
                    <div>
                        <label for="max-words" class="block text-sm text-gray-700 mb-1">Maximum Words</label>
                        <input type="number" id="max-words" max="10000" min="10" class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-transparent" placeholder="e.g. 500" value="6000" required />
                    </div>

                    <button type="submit" class="cursor-pointer bg-green-700 w-max text-white px-6 py-3 rounded-xl">Generate Assignment 🚀</button>
                </form>
            </div>
        </div>

    </body>

    </html>
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    echo minify($content);
} elseif ($path == '/assignment/progress') {
    header('Content-Type: text/event-stream', replace: true);
    header('Cache-Control: no-cache', replace: true);
    header('Connection: keep-alive', replace: true);

    require_once __DIR__ . '/load-env.php';
    if (ob_get_level() == 0) ob_start();
    try {

        $client = new \GeminiAPI\Client($_ENV['GEMINI_API_KEY']);
        $questions = json_decode($_GET['questions'] ?? '[]', true);
        $min_words = $_GET['min_words'] ?? '8000';
        $max_words = $_GET['max_words'] ?? '9000';

        $system_prompt = file_get_contents(__DIR__ . '/prompts/system.md');
        $generation_config = (new \GeminiAPI\GenerationConfig())->withMaxOutputTokens(10_00_000);

        $markdown = '';
        foreach ($questions as $index => $question) {
            $safeQuestion = htmlspecialchars($question, ENT_QUOTES, 'UTF-8');
            $question_number = 1 + $index;

            echo "event: start\ndata: {$index}\n\n";
            ob_flush();
            flush();

            $response = $client->withV1BetaVersion()
                ->generativeModel('gemini-2.0-flash-lite')
                ->withSystemInstruction(str_replace('{min_words}', $min_words, str_replace('{max_words}', $max_words, $system_prompt)))
                ->withGenerationConfig($generation_config)
                ->generateContent(
                    new \GeminiAPI\Resources\Parts\TextPart("Now provide me the answer for this question, without any talk/conversation (eg; here are the answers, etc...):\nQ{$question_number}. {$safeQuestion}"),
                );

            $markdown .= $response->text() . "\n\n";

            echo "event: done\ndata: {$index}\n\n";
            ob_flush();
            flush();
        }

        echo "event: complete\ndata: " . json_encode(['markdown' => base64_encode($markdown)]) . "\n\n";
        ob_flush();
        flush();
    } catch (\Throwable $th) {
        echo "event: error\ndata: " . $th->getMessage();
    }
    ob_end_flush();
    exit;
} elseif ($path === '/assignment/docx' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $markdown = base64_decode($_POST['markdown'] ?? '');
    if (!$markdown) {
        http_response_code(400);
        echo "Missing or invalid markdown.";
        exit;
    }

    try {
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment;Filename=assignment.doc");
        header("Pragma: no-cache");
        header("Expires: 0");

        $converter = new \League\CommonMark\CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);
        $html = $converter->convert($markdown);

    ?>

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>

            <style>
                * {
                    font-family: 'Poppins';
                    text-align: justify;
                    line-height: 1.5;
                }

                body {
                    margin: 10px;
                }
            </style>
        </head>

        <body>
            <?= $html ?>
        </body>

        </html>

<?php
    } catch (\Throwable $th) {
        echo "Something went wrong!\n\n" . $th->getMessage();
    }
    exit;
} elseif ($path == '/robots.txt') {
    require_once __DIR__ . '/load-env.php';

    header('content-type: text/plain');
    echo str_replace('{base_url}', $_ENV['APP_BASE_URL'], file_get_contents(__DIR__ . '/robots.txt'));
} elseif ($path == '/sitemap.xml') {
    require_once __DIR__ . '/load-env.php';

    header('content-type: text/xml');
    echo str_replace('{base_url}', $_ENV['APP_BASE_URL'], file_get_contents(__DIR__ . '/sitemap.xml'));
} else {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "404 Not Found\n";
    echo "The requested URL " . htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') . " was not found on this server.\n";
}
