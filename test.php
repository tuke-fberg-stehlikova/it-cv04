<?php
// Autograder cv04: 10 kontrol × 10 bodov. Spúšťa sa v GitHub Actions (.github/workflows/classroom.yml) alebo lokálne: php tests/test.php
declare(strict_types=1);

$root = dirname(__DIR__);
$prefix = '/student/test.user/it/cv04';
$port = 8765;
$score = 0;
$results = [];

function check(string $name, bool $ok, string $hint = ''): void
{
    global $score, $results;
    $score += $ok ? 10 : 0;
    $results[] = sprintf('%s %s%s', $ok ? '✔' : '✘', $name, $ok || $hint === '' ? '' : "  → $hint");
}

function get(string $path): array
{
    global $prefix, $port;
    $ctx = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 10]]);
    $body = @file_get_contents("http://127.0.0.1:$port$prefix$path", false, $ctx);
    $status = 0;
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('#^HTTP/\S+ (\d{3})#', $h, $m)) {
            $status = (int) $m[1];
        }
    }
    return [$status, (string) $body];
}

// --- statické kontroly súborov -------------------------------------------------
$rq = @file_get_contents("$root/src/Http/Request.php") ?: '';
check('src/Http/Request.php: trieda App\\Http\\Request s fromGlobals(), path() a query()',
    preg_match('/namespace\s+App\\\\Http;/', $rq) && preg_match('/class\s+Request\b/', $rq)
    && str_contains($rq, 'function fromGlobals(') && str_contains($rq, 'function path(') && str_contains($rq, 'function query('),
    'súbor src/Http/Request.php z mvc.zip');

$rs = @file_get_contents("$root/src/Http/Response.php") ?: '';
check('src/Http/Response.php: trieda App\\Http\\Response s metódou send()',
    preg_match('/namespace\s+App\\\\Http;/', $rs) && preg_match('/class\s+Response\b/', $rs) && str_contains($rs, 'function send('),
    'súbor src/Http/Response.php z mvc.zip');

$bc = @file_get_contents("$root/src/Controller/BaseController.php") ?: '';
check('src/Controller/BaseController.php: render() a notFound()',
    preg_match('/namespace\s+App\\\\Controller;/', $bc) && str_contains($bc, 'function render(') && str_contains($bc, 'function notFound('),
    'súbor src/Controller/BaseController.php z mvc.zip');

$ix = @file_get_contents("$root/index.php") ?: '';
check('index.php: Request::fromGlobals(), dispatch($request) a send(); žiadna anonymná funkcia',
    str_contains($ix, 'Request::fromGlobals()') && preg_match('/->dispatch\(\s*\$request\s*\)/', $ix) && str_contains($ix, '->send()')
    && !preg_match('/function\s*\(/', $ix),
    'index.php z mvc.zip; route sú dvojice [Controller::class, \'metóda\'], nie function ()');

preg_match_all('/->get\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\[\s*\\\\?[A-Za-z0-9_\\\\]+::class\s*,\s*[\'"][a-zA-Z_][a-zA-Z0-9_]*[\'"]\s*\]/', $ix, $mm);
$routes = $mm[1];
$custom = array_values(array_diff($routes, ['/', '/kontrola', '/debug']));
$idRoutes = array_values(array_filter($routes, fn($r) => str_contains($r, '{')));
check('index.php: aspoň 3 vlastné route ako [Controller::class, \'metóda\'] a aspoň jedna s {id}',
    count($custom) >= 3 && count($idRoutes) >= 1,
    'route pre každý číselník, hlavnú entitu a detail /nazov/{id} (úlohy 5.2, 5.3)');

$ctrls = [];
foreach (glob("$root/src/Controller/*Controller.php") ?: [] as $f) {
    $b = basename($f, '.php');
    if (in_array($b, ['BaseController', 'HomeController'], true)) {
        continue;
    }
    $c = file_get_contents($f);
    if (preg_match('/class\s+' . preg_quote($b, '/') . '\s+extends\s+BaseController/', $c) && preg_match('/namespace\s+App\\\\Controller;/', $c)) {
        $ctrls[] = $b;
    }
}
check('src/Controller/: aspoň 2 vlastné controllery (extends BaseController, namespace App\\Controller)',
    count($ctrls) >= 2,
    'jeden controller pre každý číselník a jeden pre hlavnú entitu (úlohy 5.2, 5.3)');

// --- HTTP kontroly cez php -S ----------------------------------------------------
$cmd = sprintf('php -S 127.0.0.1:%d %s > /dev/null 2>&1 & echo $!', $port, escapeshellarg("$root/tests/router.php"));
$pid = (int) trim((string) shell_exec($cmd));
usleep(700000);

[$s, $b] = get('/');
check('GET / → 200 a stránka s menu (<nav)', $s === 200 && str_contains($b, '<nav'), "stavový kód $s");

[$s, $b] = get('/debug?q=tofu');
check('GET /debug?q=tofu → 200, v tabuľke relatívna adresa /debug a hodnota parametra tofu',
    $s === 200 && preg_match('#<td>/debug</td>#', $b) && preg_match('#<td>tofu</td>#', $b),
    "stavový kód $s; views/debug.php z mvc.zip vypisuje \$request->path() a \$request->query('q')");

[$s, $b] = get('/toto-neexistuje-' . random_int(1000, 9999));
$ok = $s === 404 && str_contains($b, '<nav');
$why = "neexistujúca adresa → stavový kód $s";
if ($ok && $idRoutes) {
    $r = preg_replace('/\{[a-z_]+\}/', '999999', $idRoutes[0]);
    [$s2, $b2] = get($r);
    $ok = $s2 === 404;
    $why = "$r → stavový kód $s2 (controller má vrátiť \$this->notFound())";
}
check('neexistujúca adresa → 404 (Router); detail s neexistujúcim id → 404 (notFound v controlleri)', $ok, $ok ? '' : $why);

$ok = false; $why = 'chýba route s {id}';
if ($idRoutes) {
    $list = preg_replace('#/\{[a-z_]+\}$#', '', $idRoutes[0]);
    [$s, $b] = get($list . '?q=xyzq-nic-' . random_int(1000, 9999));
    $ok = $s === 200 && str_contains($b, '<form') && preg_match('/name=["\']q["\']/', $b) && preg_match('/method=["\']get["\']/i', $b);
    $why = "$list?q=… → stavový kód $s; zoznam hlavnej entity má mať formulár method=\"get\" s poľom name=\"q\" (úloha 5.4)";
}
check('zoznam hlavnej entity s ?q= → 200 a formulár na vyhľadávanie (method="get", name="q")', $ok, $ok ? '' : $why);

if ($pid) {
    exec("kill $pid 2>/dev/null");
}

echo implode("\n", $results), "\n\n", "Body: $score / 100\n";
exit($score === 100 ? 0 : 1);
