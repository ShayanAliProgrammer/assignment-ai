<?php

require_once __DIR__ . '/vendor/autoload.php';

function cache()
{
    // Cache for 7 days
    header('Cache-Control: max-age=604800', true);
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

header_remove('server');
header_remove('X-Powered-By');

header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if ($path == '/') {
    cache();

    function minify($content)
    {
        return preg_replace(
            [
                '/ {2,}/',
                '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s',
            ],
            [
                ' ',
                ''
            ],
            $content
        );
    }

    ob_start();
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>AI Assignment Generator</title>
        <meta name="description" content="An AI assignment generator that helps you create assignments by typing questions and generating answers using AI." />
        <link rel="canonical" href="http://localhost:5000<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>" />

        <!-- Prefetch Images -->
        <link rel="prefetch" as="style" href="/images/background.png">
        <link rel="prefetch" as="style" href="/images/primary-texture.png">


        <link rel="prefetch" as="style" href="/css/styles.css">
        <link rel="stylesheet" href="/css/styles.css">

        <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" rel="prefetch" as="script" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

        <link rel="prefetch" as="style" href="/fonts/Literata_Complete/css/literata.css">
        <link rel="prefetch" as="style" href="/fonts/GeneralSans_Complete/css/general-sans.css">

        <link rel="stylesheet" href="/fonts/Literata_Complete/css/literata.css">
        <link rel="stylesheet" href="/fonts/GeneralSans_Complete/css/general-sans.css">

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
    </head>

    <body class="min-h-screen font-sans overflow-hidden max-w-screen w-full p-6" style="background: url('/images/background.png') repeat center center; background-size: contain;">

        <div class="loader fixed inset-0 bg-white text-green-700 grid place-items-center" id="screen-loader">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-14 md:size-20 animate-spin">
                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </svg>
        </div>


        <div class="hidden p-10 max-w-4xl container mx-auto relative !z-30 bg-white rounded-2xl" id="main-content">
            <div class="max-w-4xl">
                <div class="mb-8">
                    <h1 class="text-4xl leading-relaxed font-bold text-green-700 mb-2">Assignment Questions</h1>
                    <p class="text-gray-500">Type your questions, delete mistakes, and generate your assignment.</p>
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

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                $('body').removeClass('overflow-hidden');
                $('#main-content').removeClass('hidden');
                $('#screen-loader').remove();
            })
        </script>


        <script>
            let questions = [];

            function renderQuestions() {
                $('#question-list').empty();
                questions.forEach((q, i) => {
                    $('#question-list').append(`
                        <li id="q-${i}" class="flex items-center py-0.5 gap-3 text-black">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6 shrink-0 dot"><circle cx="12.1" cy="12.1" r="1"/></svg>
                            ${$('<div>').text(q).html()}
                            <button class="cursor-pointer delete-btn ml-auto p-2 rounded-lg bg-red-700 text-white text-red-500" data-index="${i}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </li>`);
                });
            }

            $('#add-form').on('submit', e => {
                e.preventDefault();
                const q = $('#new-question').val().trim();
                if (!q) return;
                questions.push(q);
                $('#new-question').val('');
                renderQuestions();
            });

            $('#new-question').on('paste', e => {
                e.preventDefault();
                const clipboardData = e.originalEvent.clipboardData || window.clipboardData;
                const pastedText = clipboardData.getData('text');
                if (!pastedText) return;
                const newQuestions = pastedText.split(/\r?\n/).map(line => line.trim()).filter(line => line.length > 0);
                if (newQuestions.length === 0) return;
                questions = questions.concat(newQuestions);
                $('#new-question').val('');
                renderQuestions();
            });

            $(document).on('click', '.delete-btn', function() {
                const index = $(this).data('index');
                if (confirm('Are you sure?')) {
                    questions.splice(index, 1);
                    renderQuestions();
                }
            });

            $('#generate-form').on('submit', e => {
                e.preventDefault();
                if (!questions.length) return alert('Add some questions first!');

                const minWords = parseInt($('#min-words').val(), 10);
                const maxWords = parseInt($('#max-words').val(), 10);

                if (isNaN(minWords) || isNaN(maxWords)) return alert('Enter both min and max word counts!');
                if (minWords >= maxWords) return alert('Minimum words must be less than maximum words!');

                $('#progress-list').empty().removeClass('hidden');
                $('#question-list').empty().addClass('hidden');

                questions.forEach((q, i) => {
                    $('#progress-list').append(`
                        <li id="q-${i}" class="flex items-center py-0.5 gap-3 text-black">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6 shrink-0 dot"><circle cx="12.1" cy="12.1" r="1"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="loader size-6 shrink-0 animate-spin hidden"><path d="M10.1 2.18a9.93 9.93 0 0 1 3.8 0"/><path d="M17.6 3.71a9.95 9.95 0 0 1 2.69 2.7"/><path d="M21.82 10.1a9.93 9.93 0 0 1 0 3.8"/><path d="M20.29 17.6a9.95 9.95 0 0 1-2.7 2.69"/><path d="M13.9 21.82a9.94 9.94 0 0 1-3.8 0"/><path d="M6.4 20.29a9.95 9.95 0 0 1-2.69-2.7"/><path d="M2.18 13.9a9.93 9.93 0 0 1 0-3.8"/><path d="M3.71 6.4a9.95 9.95 0 0 1 2.7-2.69"/><circle cx="12" cy="12" r="1"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="checkmark size-6 shrink-0 hidden"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            ${$('<div>').text(q).html()}
                        </li>`);
                });

                const query = new URLSearchParams({
                    questions: JSON.stringify(questions),
                    min_words: minWords,
                    max_words: maxWords
                }).toString();

                const evt = new EventSource(`/assignment/progress?${query}`);

                evt.addEventListener('start', e => {
                    const i = parseInt(e.data);
                    $(`#q-${i} .dot`).addClass('hidden');
                    $(`#q-${i} .checkmark`).addClass('hidden');
                    $(`#q-${i} .loader`).removeClass('hidden');
                });

                evt.addEventListener('done', e => {
                    const i = parseInt(e.data);
                    $(`#q-${i} .dot`).addClass('hidden');
                    $(`#q-${i} .loader`).addClass('hidden');
                    $(`#q-${i} .checkmark`).removeClass('hidden');
                });

                evt.addEventListener('complete', e => {
                    evt.close();
                    const data = JSON.parse(e.data);
                    const markdown = data?.markdown;

                    const form = $('<form>', {
                        method: 'POST',
                        action: '/assignment/docx',
                        style: 'display: none;',
                    });

                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'markdown',
                        value: markdown,
                    }));

                    $('body').append(form);
                    form.trigger('submit');
                });
            });
        </script>

    </body>

    </html>
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    echo minify($content);
} elseif ($path == '/assignment/progress') {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');

    require_once __DIR__ . '/load-env.php';
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
            ob_start();

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

        // no temp file here!
        echo "event: complete\ndata: " . json_encode(['markdown' => base64_encode($markdown)]) . "\n\n";
        ob_flush();
        flush();
    } catch (\Throwable $th) {
        echo "event: error\ndata: " . $th->getMessage();
    }
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
    header('content-type: text/plain');
    echo file_get_contents(__DIR__ . '/robots.txt');
} elseif ($path == '/sitemap.xml') {
    header('content-type: text/xml');
    echo file_get_contents(__DIR__ . '/sitemap.xml');
} else {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "404 Not Found\n";
    echo "The requested URL " . htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') . " was not found on this server.\n";
}
