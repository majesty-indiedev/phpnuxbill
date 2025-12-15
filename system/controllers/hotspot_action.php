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

        $jobId = HotspotAction::create([
            'action' => $op,
            'customer_id' => (int) $user['id'],
            'recharge_id' => $rechargeId,
            'routers' => $bill['routers'],
            'nux_ip' => $_SESSION['nux-ip'] ?? '',
            'nux_mac' => $_SESSION['nux-mac'] ?? '',
        ]);

        $ui->assign('_title', ($op === 'login') ? Lang::T('Connecting') : Lang::T('Disconnecting'));
        $ui->assign('job', HotspotAction::get($jobId));
        $ui->assign('refresh_url', getUrl("hotspot_action/status/$jobId"));
        $ui->assign('home_url', getUrl('home'));
        $ui->display('customer/hotspot_wait.tpl');

        // Send response now, then continue in background if the server supports it.
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            // Best effort flush for non-FPM environments.
            @ob_end_flush();
            @flush();
        }

        HotspotAction::process($jobId);
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
