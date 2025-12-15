<?php
/**
 * HotspotAction background worker.
 *
 * Runs RouterOS login/logout jobs OUTSIDE of the captive portal HTTP request.
 *
 * Usage:
 *   php system/cron/hotspot_worker.php                # run once (process up to --max-jobs)
 *   php system/cron/hotspot_worker.php --daemon      # run forever
 *
 * Options:
 *   --daemon            Run continuously
 *   --interval=2        Sleep seconds between loops (daemon mode)
 *   --max-jobs=10       Max jobs per loop/run
 *   --max-age=86400     Cleanup job files older than seconds
 */

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden', true, 403);
    die('Forbidden');
}

$root = dirname(__DIR__, 2); // project root
require_once $root . DIRECTORY_SEPARATOR . 'init.php';

// Basic CLI options
$opts = [
    'daemon' => in_array('--daemon', $argv, true),
    'interval' => 2,
    'max_jobs' => 10,
    'max_age' => 86400,
];

foreach ($argv as $arg) {
    if (strpos($arg, '--interval=') === 0) {
        $opts['interval'] = max(1, (int) substr($arg, strlen('--interval=')));
    } elseif (strpos($arg, '--max-jobs=') === 0) {
        $opts['max_jobs'] = max(1, (int) substr($arg, strlen('--max-jobs=')));
    } elseif (strpos($arg, '--max-age=') === 0) {
        $opts['max_age'] = max(3600, (int) substr($arg, strlen('--max-age=')));
    }
}

/**
 * Acquire a global worker lock to avoid concurrent processing.
 */
function acquire_worker_lock(string $lockPath)
{
    $fh = @fopen($lockPath, 'c+');
    if (!$fh) {
        return null;
    }
    if (!flock($fh, LOCK_EX | LOCK_NB)) {
        fclose($fh);
        return null;
    }
    ftruncate($fh, 0);
    fwrite($fh, (string) getmypid());
    fflush($fh);
    return $fh;
}

function list_pending_job_ids(string $dir): array
{
    $ids = [];
    foreach (glob($dir . DIRECTORY_SEPARATOR . '*.json') as $file) {
        if (!is_file($file)) {
            continue;
        }
        $raw = @file_get_contents($file);
        if ($raw === false) {
            continue;
        }
        $job = json_decode($raw, true);
        if (!is_array($job)) {
            continue;
        }
        if (($job['status'] ?? '') === 'pending') {
            $ids[] = $job['id'] ?? pathinfo($file, PATHINFO_FILENAME);
        }
    }
    return $ids;
}

function run_once(array $opts): int
{
    global $CACHE_PATH;

    $dir = $CACHE_PATH . DIRECTORY_SEPARATOR . 'hotspot_actions';
    if (!is_dir($dir)) {
        // nothing to do
        return 0;
    }

    $lockPath = $CACHE_PATH . DIRECTORY_SEPARATOR . 'hotspot_worker.lock';
    $lock = acquire_worker_lock($lockPath);
    if (!$lock) {
        // Another worker is running.
        return 0;
    }

    // cleanup old jobs
    try {
        HotspotAction::cleanup((int) $opts['max_age']);
    } catch (Throwable $e) {
        // don't crash worker
    } catch (Exception $e) {
        // don't crash worker
    }

    $pending = list_pending_job_ids($dir);
    if (empty($pending)) {
        flock($lock, LOCK_UN);
        fclose($lock);
        return 0;
    }

    $processed = 0;
    foreach ($pending as $jobId) {
        if ($processed >= (int) $opts['max_jobs']) {
            break;
        }
        try {
            HotspotAction::process($jobId);
        } catch (Throwable $e) {
            // process() already marks failed when possible; just continue
        } catch (Exception $e) {
            // continue
        }
        $processed++;
    }

    flock($lock, LOCK_UN);
    fclose($lock);

    return $processed;
}

if ($opts['daemon']) {
    while (true) {
        run_once($opts);
        sleep((int) $opts['interval']);
    }
} else {
    run_once($opts);
}
