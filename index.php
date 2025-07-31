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

if ($path == '/site.webmanifest') {
    header("content-type: application/json");
    echo minify(file_get_contents(__DIR__ . '/public/site.webmanifest'));
    exit;
}

if (preg_match('#^/fonts/.+/fonts/[^/]+\.(woff2?|ttf|otf|eot|svg)$#', $path)) {
    serve_file(__DIR__ . '/public' . $path);
    exit;
} else if (preg_match('#^/images/[^/]+\.(jpe?g|png|gif|svg|webp|ico)$#i', $path)) {
    serve_file(__DIR__ . '/public' . $path);
    exit;
}


if ($path == '/' || $path == '/generate') {
    cache();
    require_once __DIR__ . '/load-env.php';

    ob_start();

    $app_name = 'Assign AI';
    $og_image = $_ENV['APP_BASE_URL'] . '/images/og-image.png';
    $title = 'Assign AI - ' . ($path == '/' ? 'Generate Fully Ready Assignments' : 'Create Full Assignments in Seconds');
    $description = 'Instantly generate high-quality answers for your assignments. Simply paste your questions, adjust the settings, and let AI take care of the rest. It\'s fast, flexible, and designed to enhance your academic workflow.';
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= $title ?></title>
        <meta name="description" content="<?= $description ?>" />
        <link rel="canonical" href="<?= $_ENV['APP_BASE_URL'] . $path ?>" />

        <!-- Favicon -->
        <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">



        <meta property="twitter:title" content="<?= $title ?>">
        <meta property="twitter:description" content="<?= $description ?>">
        <meta property="twitter:image" content="<?= $og_image ?>">
        <meta property="twitter:card" content="summary_large_image">


        <meta property="og:image" content="<?= $og_image ?>">
        <meta property="og:site_name" content="<?= $app_name ?>">
        <meta property="og:title" content="<?= $title ?>">
        <meta property="og:description" content="<?= $description ?>" />
        <meta property="og:url" content="<?= $_ENV['APP_BASE_URL'] . $path ?>">

        <!-- Google Ads -->
        <meta name="google-adsense-account" content="<?= $_ENV['GOOGLE_ADSENSE_ACCOUNT'] ?>">


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

        <script>
            <?= minify(file_get_contents(__DIR__ . '/public/js/jquery.min.js')) ?>
        </script>

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

    <body class="min-h-screen font-sans max-w-screen w-full <?= $path == '/generate' ? 'md:p-6' : '' ?>" <?= $path == '/generate' ? 'style="background: url(\'/images/background.png\') repeat center center; background-size: contain;"' : '' ?>>


        <?php if ($path == '/'): ?>
            <div class="min-h-screen bg-white text-gray-800">

                <!-- Hero Section -->
                <section class="text-center py-20 px-6 bg-gradient-to-b from-green-50 via-white to-white">
                    <h1 class="text-5xl sm:text-6xl font-extrabold leading-tight mb-6 tracking-tight">
                        ✨ Instantly Generate <br class="hidden sm:block" />Assignments with AI
                    </h1>
                    <p class="text-base sm:text-lg leading-loose text-gray-700 mb-6 max-w-2xl mx-auto">
                        Assign AI is your personal homework sidekick. Upload any question, set a word count, and get high-quality, structured answers — instantly downloadable as a DOC file. No logins. No extra fluff. Just smart help, when you need it.
                    </p>
                    <a href="/generate" class="inline-block bg-green-600 text-white font-semibold px-8 py-4 rounded-xl shadow-md hover:bg-green-700 hover:shadow-lg transition-all text-lg">
                        Try Assign AI – It’s Free 🚀
                    </a>
                </section>

                <!-- SEO/Content Boost Section -->
                <section class="px-6 py-16 max-w-4xl mx-auto text-left">
                    <h2 class="text-3xl font-bold mb-6 text-green-700">🎯 What Is Assign AI?</h2>
                    <p class="text-gray-700 text-lg leading-relaxed mb-6">
                        Assign AI is a free online tool designed to help students complete assignments faster and smarter using artificial intelligence. Whether you’re stuck on a tricky theory question, need help summarizing textbook content, or just want to polish up your homework before submission — Assign AI has you covered.
                    </p>
                    <p class="text-base sm:text-lg text-gray-700 mb-6">
                        Whether you're tackling a last-minute college paper or preparing structured notes for study sessions, Assign AI makes it effortless. Built with students in mind, it handles the formatting while you focus on the thinking.
                    </p>
                    <p class="text-gray-700 text-lg leading-relaxed mb-6">
                        Unlike generic writing tools, Assign AI is fine-tuned for education. You control the word count, paste as many questions as you like, and get a downloadable answer that’s already formatted for Microsoft Word. No more messy layouts, no more last-minute stress. Our goal is to help learners save time and improve writing quality without requiring technical skills or creating an account.
                    </p>
                    <p class="text-gray-700 text-lg leading-relaxed">
                        Best of all, it's completely free — no paywalls, no nonsense.
                    </p>
                </section>

                <!-- How It Works -->
                <section class="py-20 px-6 bg-white max-w-6xl mx-auto">
                    <h2 class="text-4xl font-bold mb-14 text-center">🛠️ How It Works</h2>
                    <div class="grid md:grid-cols-3 gap-10 text-center">
                        <div class="transition hover:-translate-y-1 hover:shadow-lg p-6 rounded-xl">
                            <div class="text-5xl mb-4">📝</div>
                            <h3 class="font-semibold text-2xl mb-2">Paste Your Question</h3>
                            <p class="text-gray-600">Drop in anything—from a single prompt to a list of assignment questions.</p>
                        </div>
                        <div class="transition hover:-translate-y-1 hover:shadow-lg p-6 rounded-xl">
                            <div class="text-5xl mb-4">🎚️</div>
                            <h3 class="font-semibold text-2xl mb-2">Set Word Limit</h3>
                            <p class="text-gray-600">Control how long or short your answer is. Great for summaries or essays.</p>
                        </div>
                        <div class="transition hover:-translate-y-1 hover:shadow-lg p-6 rounded-xl">
                            <div class="text-5xl mb-4">⚡</div>
                            <h3 class="font-semibold text-2xl mb-2">Generate &amp; Download</h3>
                            <p class="text-gray-600">Click a button. Boom — ready-to-use .doc download, perfectly formatted.</p>
                        </div>
                    </div>
                </section>

                <!-- Features Section -->
                <section class="bg-gray-50 py-20 px-6 border-t">
                    <div class="max-w-5xl mx-auto">
                        <h2 class="text-4xl font-bold mb-10 text-center">💡 Why Use Assign AI?</h2>
                        <ul class="space-y-6 text-lg text-gray-700 max-w-2xl mx-auto">
                            <li class="flex items-start gap-3">
                                <span class="text-green-600 text-2xl">✔️</span> No signups or accounts required — instant access.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-green-600 text-2xl">✔️</span> Downloadable Word documents that are clean and submission-ready.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-green-600 text-2xl">✔️</span> Smart, structured, and AI-generated answers — no filler or BS.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-green-600 text-2xl">✔️</span> Works beautifully on both mobile and desktop.
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- Testimonials -->
                <section class="bg-white py-20 px-6 border-t">
                    <div class="max-w-3xl mx-auto text-center">
                        <h2 class="text-3xl font-bold mb-10">💬 What Students Say</h2>
                        <blockquote class="italic text-gray-700 text-xl leading-relaxed">"Assign AI saved me the night before my deadline. It’s fast, clean, and ridiculously easy to use."</blockquote>
                        <p class="text-sm text-gray-500 mt-3">— Probably Someone Just Like You</p>
                    </div>
                </section>

                <!-- Call to Action Footer -->
                <footer class="bg-green-600 text-white py-16 px-6 text-center border-t">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-6">🎓 Ready to Ace Your Assignments?</h2>
                    <a href="/generate" class="inline-block bg-white text-green-600 font-semibold px-8 py-4 rounded-xl hover:bg-gray-100 transition text-lg shadow-lg">
                        Open Assignment Generator Now →
                    </a>
                    <p class="mt-6 text-sm opacity-80">No login • No fluff • Always free</p>
                </footer>
            </div>

        <?php elseif ($path == '/generate'): ?>

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
                            <input type="number" id="min-words" min="10" class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-transparent" placeholder="e.g. 300" value="10" required />
                        </div>
                        <div>
                            <label for="max-words" class="block text-sm text-gray-700 mb-1">Maximum Words</label>
                            <input type="number" id="max-words" max="10000" min="10" class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-transparent" placeholder="e.g. 500" value="20" required />
                        </div>

                        <button type="submit" class="cursor-pointer bg-green-700 w-max text-white px-6 py-3 rounded-xl">Generate Assignment 🚀</button>
                    </form>
                </div>

                <div class="pt-8 border-t-2 text-gray-700 text-base space-y-4">
                    <h2 class="text-2xl font-bold text-green-700">How to Use This Assignment Generator</h2>
                    <p>
                        This tool helps you quickly generate well-structured assignment answers. Simply type or paste your questions into the input field at the top. You can also paste multiple questions at once—each line will be treated as a separate entry.
                    </p>
                    <p>
                        After entering your questions, adjust the word count sliders to match your assignment requirements. Use the <span class="font-semibold">"Minimum Words"</span> and <span class="font-semibold">"Maximum Words"</span> fields to control how detailed each answer should be.
                    </p>
                    <p>
                        Click the <span class="font-semibold">"Generate Assignment 🚀"</span> button to begin. You’ll see each question progress in real-time as answers are created. The spinner indicates loading; once completed, a checkmark confirms the response is ready.
                    </p>
                    <p>
                        After processing, the entire assignment is automatically compiled into a downloadable <span class="font-semibold">.doc</span> file. You can easily edit or customize the file further in Microsoft Word or Google Docs.
                    </p>
                    <p>
                        This tool requires no sign-up, no installations, and is 100% free. Whether you're working late on a deadline or just need a helping hand, Assign AI is here to support your academic productivity.
                    </p>

                    <p class='font-semibold'>Overall:</p>

                    <ul class='list-decimal pl-6'>
                        <li>Paste assignment questions, either on by one, or all at once but each question must be seperated with a new line</li>
                        <li>Adjust some settings like <span class="font-semibold">minimum words</span>, and <span class="font-semibold">maximum words</span> if needed.</li>
                        <li>Click on <span class="font-medium">Generate Assignment</span> button to start generating your assignment</li>
                        <li>Check the generated assignment, before submiting</li>
                    </ul>

                    <p class="text-sm text-yellow-600 italic">
                        Note: Always review generated content for accuracy before submission. This tool is designed to assist, not replace, your own understanding.
                        Our AI does not design the whole assignment document, it just acts like a ghost writer for you.
                    </p>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        let questions = [];

                        function renderQuestions() {
                            $("#question-list").empty();
                            questions.forEach((q, i) => {
                                $("#question-list").append(`
                            <li id="q-${i}" class="flex items-center py-0.5 gap-3 text-black">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6 shrink-0 dot"><circle cx="12.1" cy="12.1" r="1"/></svg>
                                ${$("<div>").text(q).html()}
                                <button class="cursor-pointer size-max focus:!ring-red-700 delete-btn ml-auto p-2 rounded-lg bg-red-700 text-white text-red-500" data-index="${i}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </li>`);
                            });
                        }

                        $("#add-form").on("submit", (e) => {
                            e.preventDefault();
                            const q = $("#new-question").val().trim();
                            if (!q) return;
                            questions.push(q);
                            $("#new-question").val("");
                            renderQuestions();
                        });

                        $("#new-question").on("paste", (e) => {
                            e.preventDefault();
                            const clipboardData = e.originalEvent.clipboardData || window.clipboardData;
                            const pastedText = clipboardData.getData("text");
                            if (!pastedText) return;
                            const newQuestions = pastedText
                                .split(/\r?\n/)
                                .map((line) => line.trim())
                                .filter((line) => line.length > 0);
                            if (newQuestions.length === 0) return;
                            questions = questions.concat(newQuestions);
                            $("#new-question").val("");
                            renderQuestions();
                        });

                        $(document).on("click", ".delete-btn", function() {
                            const index = $(this).data("index");
                            if (confirm("Are you sure?")) {
                                questions.splice(index, 1);
                                renderQuestions();
                            }
                        });

                        $("#generate-form").on("submit", (e) => {
                            e.preventDefault();
                            if (!questions.length) return alert("Add some questions first!");

                            const minWords = parseInt($("#min-words").val(), 10);
                            const maxWords = parseInt($("#max-words").val(), 10);

                            if (isNaN(minWords) || isNaN(maxWords))
                                return alert("Enter both min and max word counts!");
                            if (minWords >= maxWords)
                                return alert("Minimum words must be less than maximum words!");

                            $("#progress-list").empty().removeClass("hidden");
                            $("#question-list").empty().addClass("hidden");

                            questions.forEach((q, i) => {
                                $("#progress-list").append(`
                            <li id="q-${i}" class="flex items-center py-0.5 gap-3 text-black">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6 shrink-0 dot"><circle cx="12.1" cy="12.1" r="1"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="loader size-6 shrink-0 animate-spin hidden"><path d="M10.1 2.18a9.93 9.93 0 0 1 3.8 0"/><path d="M17.6 3.71a9.95 9.95 0 0 1 2.69 2.7"/><path d="M21.82 10.1a9.93 9.93 0 0 1 0 3.8"/><path d="M20.29 17.6a9.95 9.95 0 0 1-2.7 2.69"/><path d="M13.9 21.82a9.94 9.94 0 0 1-3.8 0"/><path d="M6.4 20.29a9.95 9.95 0 0 1-2.69-2.7"/><path d="M2.18 13.9a9.93 9.93 0 0 1 0-3.8"/><path d="M3.71 6.4a9.95 9.95 0 0 1 2.7-2.69"/><circle cx="12" cy="12" r="1"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="checkmark size-6 shrink-0 hidden"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                ${$("<div>").text(q).html()}
                            </li>`);
                            });

                            const query = new URLSearchParams({
                                questions: JSON.stringify(questions),
                                min_words: minWords,
                                max_words: maxWords,
                            }).toString();

                            const evt = new EventSource(`/assignment/progress?${query}`);

                            evt.addEventListener("start", (e) => {
                                const i = parseInt(e.data);
                                $(`#q-${i} .dot`).addClass("hidden");
                                $(`#q-${i} .checkmark`).addClass("hidden");
                                $(`#q-${i} .loader`).removeClass("hidden");
                            });

                            evt.addEventListener("done", (e) => {
                                const i = parseInt(e.data);
                                $(`#q-${i} .dot`).addClass("hidden");
                                $(`#q-${i} .loader`).addClass("hidden");
                                $(`#q-${i} .checkmark`).removeClass("hidden");
                            });

                            evt.addEventListener("complete", (e) => {
                                evt.close();
                                const data = JSON.parse(e.data);
                                const markdown = data?.markdown;

                                const form = $("<form>", {
                                    method: "POST",
                                    action: "/assignment/DOC",
                                    style: "display: none;",
                                });

                                form.append(
                                    $("<input>", {
                                        type: "hidden",
                                        name: "markdown",
                                        value: markdown,
                                    })
                                );

                                $("body").append(form);
                                form.trigger("submit");
                            });
                        });
                    });
                </script>

                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        $("#main-content").removeClass("hidden");
                        $("#screen-loader").remove();
                    });
                </script>
            </div>
        <?php endif; ?>

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
    ob_flush();
    flush();

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
        ob_flush();
        flush();
    }
    ob_end_flush();
    exit;
} elseif ($path === '/assignment/DOC' && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
