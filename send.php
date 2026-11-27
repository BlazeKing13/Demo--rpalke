<?php
/**
 * STIMER – pošiljanje povpraševanja na e-pošto.
 * Zahteve: PHP 7.4+ z delujočo funkcijo mail() (običajno gostovanje v EU jo ima).
 * V php.ini naj bo upload_max_filesize in post_max_size vsaj 12M.
 */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

const TO        = 'stimer.doo@gmail.com';
const FROM      = 'povprasevanje@stimer.si';   // naslov na vaši domeni (da pošta ne konča med vsiljeno pošto)
const MAX_BYTES = 10 * 1024 * 1024;
const ALLOWED   = ['pdf', 'jpg', 'jpeg', 'png', 'dwg', 'zip'];

const LABELS = [
    'kraj' => 'Kraj', 'povrsina' => 'Ogrevana površina (m²)', 'objekt' => 'Objekt',
    'tip_objekta' => 'Tip objekta', 'visina' => 'Višina prostorov', 'okna' => 'Stavbno pohištvo – okna',
    'izolacija' => 'Tip izolacije', 'tip_tc' => 'Želeni tip TČ', 'namen' => 'Namen',
    'ogrevala' => 'Ogrevala', 'osebe' => 'Število oseb', 'obstojece' => 'Obstoječi vir ogrevanja',
    'izvedba' => 'Želena izvedba', 'projekt' => 'Projekt za izvedbo del',
    'ime' => 'Ime in priimek', 'email' => 'E-pošta', 'telefon' => 'Telefon',
    'opombe' => 'Opombe', 'soglasje' => 'Soglasje za obdelavo podatkov', 'jezik' => 'Jezik obiskovalca',
];
const REQUIRED = ['kraj', 'povrsina', 'objekt', 'tip_objekta', 'visina', 'okna', 'izolacija',
                  'tip_tc', 'namen', 'izvedba', 'projekt', 'email'];

function out(int $code, string $msg): void {
    http_response_code($code);
    echo json_encode(['ok' => $code === 200, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}
function clean(string $v, int $max = 300): string {
    return mb_substr(trim(str_replace(["\r", "\0"], '', $v)), 0, $max);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(405, 'Dovoljen je samo POST.');
if (!empty($_POST['website'])) out(200, 'ok');            // past za robote

$data = [];
foreach (LABELS as $key => $label) {
    $data[$key] = isset($_POST[$key]) && is_string($_POST[$key]) ? clean($_POST[$key]) : '';
}
foreach (REQUIRED as $key) {
    if ($data[$key] === '') out(422, 'Manjka polje: ' . LABELS[$key]);
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) out(422, 'Neveljaven e-naslov.');
if ($data['soglasje'] !== 'da') out(422, 'Manjka soglasje.');

$lines = [];
foreach (LABELS as $key => $label) {
    if ($data[$key] !== '') $lines[] = $label . ': ' . $data[$key];
}
$body = "Novo povpraševanje s spletne strani stimer.si\n\n" . implode("\n", $lines) . "\n";

$attachment = null;
if (!empty($_FILES['priloga']) && $_FILES['priloga']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['priloga'];
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > MAX_BYTES) out(413, 'Priloga presega 10 MB.');
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED, true) || !is_uploaded_file($f['tmp_name'])) out(415, 'Nedovoljena vrsta priloge.');
    $attachment = [
        'name' => preg_replace('/[^A-Za-z0-9._-]/', '_', basename($f['name'])),
        'data' => chunk_split(base64_encode(file_get_contents($f['tmp_name']))),
    ];
}

$subject  = '=?UTF-8?B?' . base64_encode('Povpraševanje TČ – ' . $data['kraj']) . '?=';
$boundary = 'stimer-' . bin2hex(random_bytes(12));
$headers  = [
    'From: STIMER splet <' . FROM . '>',
    'Reply-To: ' . str_replace(["\n", "\r"], '', $data['email']),
    'MIME-Version: 1.0',
    'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
];
$msg  = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n$body\r\n";
if ($attachment) {
    $msg .= "--$boundary\r\nContent-Type: application/octet-stream; name=\"{$attachment['name']}\"\r\n"
          . "Content-Transfer-Encoding: base64\r\n"
          . "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n\r\n{$attachment['data']}\r\n";
}
$msg .= "--$boundary--";

if (!mail(TO, $subject, $msg, implode("\r\n", $headers))) out(500, 'Pošiljanje ni uspelo.');
out(200, 'ok');
