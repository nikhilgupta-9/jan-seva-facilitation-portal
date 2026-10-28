<?php
/**
 * scripts/fetch_pib_updates.php
 *
 * Daily cron job: pulls the latest press releases from the official
 * Press Information Bureau (PIB) RSS feed and inserts new ones into
 * `portal_updates` (created_via = 'cron') so they show up on the
 * homepage "Schemes & Updates" notice board alongside admin-posted
 * entries.
 *
 * Run manually to test:
 *   php scripts/fetch_pib_updates.php
 *
 * Schedule it (crontab -e), once a day at 7am server time:
 *   0 7 * * * /usr/bin/php /path/to/gp/scripts/fetch_pib_updates.php >> /path/to/gp/storage/pib_fetch.log 2>&1
 *
 * Design notes:
 * - Uses PIB's own public RSS endpoint (no HTML scraping, no login,
 *   no bypassing of any access control) - this is the intended way
 *   to consume their releases programmatically.
 * - Identifies itself honestly via User-Agent; does not impersonate
 *   a browser. If PIB's WAF blocks server-to-server requests from a
 *   given host, this will fail loudly in the log rather than retry
 *   with a spoofed identity - swap PIB_RSS_URL below for a different
 *   official feed if that happens consistently.
 * - Idempotent: `external_guid` has a UNIQUE key, so re-running never
 *   creates duplicates; already-seen items are silently skipped.
 */

require_once __DIR__ . '/../config/config.php';

const PIB_RSS_URL = 'https://pib.gov.in/RssMain.aspx?ModId=6&Lang=1&Regid=3'; // All Ministries, English
const MAX_ITEMS_PER_RUN = 30;
const FETCH_TIMEOUT_SECONDS = 20;

function log_line(string $msg): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    echo $line . PHP_EOL;
}

function fetch_rss(string $url): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => FETCH_TIMEOUT_SECONDS,
        CURLOPT_USERAGENT => 'JanSevaFacilitationPortal-UpdateFetcher/1.0 (+https://jan-seva-facilitation-portal; contact: ' . (defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'admin') . ')',
        CURLOPT_HTTPHEADER => ['Accept: application/rss+xml, application/xml, text/xml'],
    ]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($body === false || $error) {
        log_line("ERROR: cURL failed - {$error}");
        return null;
    }
    if ($httpCode !== 200) {
        log_line("ERROR: PIB returned HTTP {$httpCode} (feed may be blocking this server's IP, or the URL needs updating).");
        return null;
    }
    return $body;
}

function parse_pib_rss(string $xml): array
{
    libxml_use_internal_errors(true);
    $rss = simplexml_load_string($xml);
    if ($rss === false) {
        log_line('ERROR: Could not parse RSS XML - ' . implode('; ', array_map(fn($e) => $e->message, libxml_get_errors())));
        return [];
    }

    $items = [];
    foreach ($rss->channel->item ?? [] as $item) {
        $title = trim((string) $item->title);
        $link = trim((string) $item->link);
        $guid = trim((string) ($item->guid ?? $link));
        $pubDateRaw = trim((string) $item->pubDate);
        $pubDate = $pubDateRaw ? date('Y-m-d', strtotime($pubDateRaw)) : date('Y-m-d');

        if ($title === '' || $guid === '') {
            continue;
        }

        $items[] = [
            'title' => $title,
            'link' => $link,
            'guid' => 'pib:' . $guid,
            'published_at' => $pubDate,
        ];
    }
    return array_slice($items, 0, MAX_ITEMS_PER_RUN);
}

function categorize(string $title): string
{
    $t = mb_strtolower($title);
    if (str_contains($t, 'scheme') || str_contains($t, 'yojana')) return 'scheme';
    if (str_contains($t, 'deadline') || str_contains($t, 'last date')) return 'deadline';
    return 'notice';
}

// ---------------------------------------------------------------------
log_line('PIB update fetch started.');

$xml = fetch_rss(PIB_RSS_URL);
if ($xml === null) {
    log_line('Aborting: no feed data retrieved.');
    exit(1);
}

$items = parse_pib_rss($xml);
if (empty($items)) {
    log_line('No items parsed from feed. Nothing to insert.');
    exit(0);
}

$stmt = $pdo->prepare(
    "INSERT IGNORE INTO portal_updates (title_en, source_name, source_url, category, published_at, is_active, created_via, external_guid)
     VALUES (?, 'PIB', ?, ?, ?, 1, 'cron', ?)"
);

$inserted = 0;
$skipped = 0;
foreach ($items as $item) {
    $ok = $stmt->execute([
        $item['title'],
        $item['link'],
        categorize($item['title']),
        $item['published_at'],
        $item['guid'],
    ]);
    if ($ok && $stmt->rowCount() > 0) {
        $inserted++;
    } else {
        $skipped++;
    }
}

log_line("Done. Fetched " . count($items) . " items -> inserted {$inserted} new, skipped {$skipped} already-known.");
