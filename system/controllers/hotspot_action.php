<?php
/**
 * Customer hotspot login/logout actions that must work inside captive portals.
 *
 * Flow:
 * - enqueue/login/<rechargeId> or enqueue/logout/<rechargeId>
 *   - instantly returns a "Connecting..." page (no JS popups required)
 *   - then processes the action in the background (when possible)
 * - status/<jobId>
 *   - polls job state and redirects to home when done
 */

_auth();

$action = $routes[1] ?? 'status';
$user = User::_info();
$ui->assign('_user', $user);

// Prevent hotspot action queue files from growing indefinitely.
// Keep 24 hours of jobs by default.
HotspotAction::cleanup(86400);

switch ($action) {
    case 'enqueue':
        $op = $routes[2] ?? '';
        $rechargeId = (int) ($routes[3] ?? 0);

        if (!in_array($op, ['login', 'logout'], true) || $rechargeId <= 0) {
            r2(getUrl('home'), 'e', Lang::T('Invalid request'));
        }

        // Validate the recharge belongs to the user
        $bill = ORM::for_table('tbl_user_recharges')->find_one($rechargeId);
        if (!$bill || (int) $bill['customer_id'] !== (int) $user['id']) {
            r2(getUrl('home'), 'e', Lang::T('Not Found'));
        }

        // For login we need hotspot params
        if ($op === 'login') {
            if (empty($_SESSION['nux-ip']) || empty($_SESSION['nux-mac'])) {
                r2(getUrl('home'), 'e', Lang::T('Missing hotspot client data (IP/MAC). Please reopen the hotspot login page.'));
            }
        }

        try {
            $jobId = HotspotAction::create([
                'action' => $op,
                'customer_id' => (int) $user['id'],
                'recharge_id' => $rechargeId,
                'routers' => $bill['routers'],
                'nux_ip' => $_SESSION['nux-ip'] ?? '',
                'nux_mac' => $_SESSION['nux-mac'] ?? '',
            ]);
        } catch (Throwable $e) {
            error_log('HotspotAction enqueue failed: ' . $e->getMessage());
            r2(getUrl('home'), 'e', Lang::T('System error. Please contact support.'));
        } catch (Exception $e) {
            error_log('HotspotAction enqueue failed: ' . $e->getMessage());
            r2(getUrl('home'), 'e', Lang::T('System error. Please contact support.'));
        }

        // Option 1 UX: always respond instantly for iOS captive portals.
        // The actual RouterOS work is handled asynchronously by a background worker (cron/systemd).
        $ui->assign('_title', ($op === 'login') ? Lang::T('Connecting') : Lang::T('Disconnecting'));
        $ui->assign('job', HotspotAction::get($jobId));
        $ui->assign('status_url', getUrl("hotspot_action/status/$jobId"));
        $ui->assign('home_url', getUrl('home'));
        // Fail-safe: if Smarty/template fails on some servers, return a minimal HTML response
        // (better than the generic internal error page inside captive portals).
        try {
            $ui->display('customer/hotspot_request.tpl');
        } catch (Throwable $e) {
            error_log('HotspotAction render failed: ' . $e->getMessage());
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
            echo '<title>Connecting…</title></head><body style="font-family: Arial, sans-serif; padding:16px; background:#f5f5f5;">';
            echo '<div style="max-width:520px;margin:0 auto;background:#fff;border:1px solid #ddd;border-radius:8px;overflow:hidden">';
            echo '<div style="background:#337ab7;color:#fff;padding:12px 14px;font-weight:600">Granting internet access…</div>';
            echo '<div style="padding:14px">';
            echo '<p style="margin:0 0 8px;color:#555">You can close this page now.</p>';
            echo '<p style="margin:0 0 8px;color:#555">As soon as the connection is established, your Wi‑Fi icon will show internet access.</p>';
            echo '<p style="margin:0;color:#555">This can take up to 30 seconds. Then you can use TikTok, Instagram, Facebook, and more.</p>';
            echo '<hr style="border:none;border-top:1px solid #eee;margin:12px 0">';
            echo '<p style="margin:0;color:#777">On iPhone, tap Done (top right) to close this window.</p>';
            echo '</div></div></body></html>';
        } catch (Exception $e) {
            error_log('HotspotAction render failed: ' . $e->getMessage());
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
            echo '<title>Connecting…</title></head><body style="font-family: Arial, sans-serif; padding:16px; background:#f5f5f5;">';
            echo '<div style="max-width:520px;margin:0 auto;background:#fff;border:1px solid #ddd;border-radius:8px;overflow:hidden">';
            echo '<div style="background:#337ab7;color:#fff;padding:12px 14px;font-weight:600">Granting internet access…</div>';
            echo '<div style="padding:14px">';
            echo '<p style="margin:0 0 8px;color:#555">You can close this page now.</p>';
            echo '<p style="margin:0 0 8px;color:#555">As soon as the connection is established, your Wi‑Fi icon will show internet access.</p>';
            echo '<p style="margin:0;color:#555">This can take up to 30 seconds. Then you can use TikTok, Instagram, Facebook, and more.</p>';
            echo '<hr style="border:none;border-top:1px solid #eee;margin:12px 0">';
            echo '<p style="margin:0;color:#777">On iPhone, tap Done (top right) to close this window.</p>';
            echo '</div></div></body></html>';
        }
        die();

    case 'enqueue_json':
        // Dashboard AJAX flow: enqueue without navigating away.
        $op = $routes[2] ?? '';
        $rechargeId = (int) ($routes[3] ?? 0);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        if (!in_array($op, ['login', 'logout'], true) || $rechargeId <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'invalid_request']);
            die();
        }

        // Validate the recharge belongs to the user
        $bill = ORM::for_table('tbl_user_recharges')->find_one($rechargeId);
        if (!$bill || (int) $bill['customer_id'] !== (int) $user['id']) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            die();
        }

        // For login we need hotspot params
        if ($op === 'login') {
            if (empty($_SESSION['nux-ip']) || empty($_SESSION['nux-mac'])) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'missing_nux']);
                die();
            }
        }

        try {
            $jobId = HotspotAction::create([
                'action' => $op,
                'customer_id' => (int) $user['id'],
                'recharge_id' => $rechargeId,
                'routers' => $bill['routers'],
                'nux_ip' => $_SESSION['nux-ip'] ?? '',
                'nux_mac' => $_SESSION['nux-mac'] ?? '',
            ]);
        } catch (Throwable $e) {
            error_log('HotspotAction enqueue_json failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'server_error']);
            die();
        } catch (Exception $e) {
            error_log('HotspotAction enqueue_json failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'server_error']);
            die();
        }

        echo json_encode([
            'ok' => true,
            'job_id' => $jobId,
            'status_url' => getUrl("hotspot_action/status_json/$jobId"),
        ]);
        die();

    case 'status_json':
        // Lightweight polling endpoint for dashboard (and optional for captive portals).
        $jobId = $routes[2] ?? '';
        $job = $jobId ? HotspotAction::get($jobId) : null;

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        if (!$job) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            die();
        }

        echo json_encode([
            'ok' => true,
            'id' => $job['id'] ?? '',
            'status' => $job['status'] ?? '',
            'action' => $job['action'] ?? '',
            'message' => $job['message'] ?? '',
            'updated_at' => $job['updated_at'] ?? '',
        ]);
        die();

    case 'status':
    default:
        $jobId = $routes[2] ?? '';
        $job = $jobId ? HotspotAction::get($jobId) : null;
        if (!$job) {
            r2(getUrl('home'), 'e', Lang::T('Not Found'));
        }

        if ($job['status'] === 'success') {
            // In iOS captive portal (CNA), long-running pages / repeated refreshes can lead to
            // "server stopped responding" even if the login already succeeded.
            // Stop polling and show a success page with clear next actions.
            $ui->assign('_title', Lang::T('Connected'));
            $ui->assign('job', $job);
            $ui->assign('home_url', getUrl('home'));
            // iOS captive portal often closes itself when it can fetch this URL successfully.
            $ui->assign('apple_cna_url', 'http://captive.apple.com/hotspot-detect.html');
            $ui->display('customer/hotspot_done.tpl');
            die();
        }

        // Still pending/running or failed -> show wait page (with retry button on failure)
        $ui->assign('_title', Lang::T('Please wait'));
        $ui->assign('job', $job);
        $ui->assign('refresh_url', getUrl("hotspot_action/status/$jobId"));
        $ui->assign('home_url', getUrl('home'));
        $ui->display('customer/hotspot_wait.tpl');
        die();
}
