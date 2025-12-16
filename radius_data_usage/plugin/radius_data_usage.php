<?php
/**
 * Radius Data Usage (Customer)
 *
 * - Shows a customer "Data Usage" page (menu item).
 * - Shows a toast warning on dashboard when data is low/exhausted.
 *
 * Install location (after ZIP install): system/plugin/
 * UI templates (after ZIP install): system/plugin/ui/
 */

register_menu("Data Usage", false, "radius_data_usage", 'AFTER_DASHBOARD', 'ion ion-pie-graph', '', 'info');
register_hook('view_customer_dashboard', 'radius_data_usage_dashboard_notice');

function radius_data_usage_debug_enabled()
{
    // Enable by adding ?radius_usage_debug=1 to URL.
    return isset($_GET['radius_usage_debug']) && $_GET['radius_usage_debug'] == '1';
}

function radius_data_usage_log_error($e, $context)
{
    $msg = '[RadiusDataUsage] ' . $context . ': ' . $e->getMessage();
    error_log($msg);
}

/**
 * Customer menu page: summary of data usage for the active RADIUS plan(s).
 */
function radius_data_usage()
{
    global $ui, $user;
    _auth();

    try {
        $user = User::_info();
        $ui->assign('_user', $user);
        $ui->assign('_title', Lang::T('Data Usage'));

        $items = radius_data_usage_get_items($user);
        $ui->assign('items', $items);
        $ui->assign('plugin_error', '');

        $ui->display('radius_data_usage.tpl');
    } catch (Exception $e) {
        radius_data_usage_log_error($e, 'radius_data_usage page');
        if (radius_data_usage_debug_enabled()) {
            // Show error only when debug flag is enabled; never break login/dashboard.
            $ui->assign('items', []);
            $ui->assign('plugin_error', $e->getMessage());
            $ui->display('radius_data_usage.tpl');
            return;
        }
    }
}

/**
 * Hook: show a toast warning on customer dashboard if data is low/exhausted.
 */
function radius_data_usage_dashboard_notice()
{
    try {
        $user = User::_info();
        $items = radius_data_usage_get_items($user);
        if (!$items) {
            return;
        }

        // Avoid spamming toast: at most once every 10 minutes.
        $now = time();
        $last = isset($_SESSION['radius_usage_notice_ts']) ? (int) $_SESSION['radius_usage_notice_ts'] : 0;
        if ($last && ($now - $last) < 600) {
            return;
        }

        // Find worst status across plans.
        $worst = null; // ['level' => 'exhausted'|'low', 'msg' => ...]
        foreach ($items as $it) {
            if (!$it['has_limit']) {
                continue;
            }
            if ($it['remaining_bytes'] <= 0) {
                $worst = [
                    'level' => 'exhausted',
                    'msg' => Lang::T('Your data is exhausted. Please recharge to continue.')
                ];
                break;
            }
            if ($it['remaining_ratio'] !== null && $it['remaining_ratio'] <= 0.10) {
                $worst = [
                    'level' => 'low',
                    'msg' => Lang::T('Your data is running low. Please recharge soon.')
                ];
            }
        }

        if (!$worst) {
            return;
        }

        $_SESSION['radius_usage_notice_ts'] = $now;
        $_SESSION['notify'] = $worst['msg'];
        $_SESSION['ntype'] = 'w';
    } catch (Exception $e) {
        radius_data_usage_log_error($e, 'dashboard hook');
        if (radius_data_usage_debug_enabled()) {
            $_SESSION['notify'] = '[Radius Usage Plugin] ' . $e->getMessage();
            $_SESSION['ntype'] = 'w';
        }
    }
}

/**
 * Compute per-plan usage using RADIUS accounting rows (rad_acct) across the active recharge period.
 *
 * Returns array of items:
 * - plan_name, username, start_dt, end_dt
 * - used_bytes, remaining_bytes, limit_bytes
 * - has_limit, remaining_ratio
 */
function radius_data_usage_get_items($user)
{
    $items = [];
    if (empty($user['id']) || empty($user['username'])) {
        return $items;
    }

    // Active recharges only.
    $bills = ORM::for_table('tbl_user_recharges')
        ->where('customer_id', (int) $user['id'])
        ->where('status', 'on')
        ->order_by_desc('id')
        ->find_many();

    foreach ($bills as $bill) {
        $routers = strtolower(trim((string) (isset($bill['routers']) ? $bill['routers'] : '')));

        // Only RADIUS-based plans.
        if ($routers !== 'radius') {
            continue;
        }

        $plan = ORM::for_table('tbl_plans')->find_one((int) (isset($bill['plan_id']) ? $bill['plan_id'] : 0));
        if (!$plan) {
            continue;
        }
        $planArr = $plan->as_array();

        // Usage window: recharge start -> expiration end
        $startDt = trim((string) (isset($bill['recharged_on']) ? $bill['recharged_on'] : '')) . ' ' . trim((string) (isset($bill['recharged_time']) ? $bill['recharged_time'] : '00:00:00'));
        $endDt = trim((string) (isset($bill['expiration']) ? $bill['expiration'] : '')) . ' ' . trim((string) (isset($bill['time']) ? $bill['time'] : '23:59:59'));
        $startTs = strtotime($startDt) ?: 0;
        $endTs = strtotime($endDt) ?: 0;
        if ($startTs <= 0 || $endTs <= 0) {
            continue;
        }

        // Sum counters across the period.
        $uname = addslashes($user['username']);
        $startFilter = date('Y-m-d H:i:s', $startTs);
        $endFilter = date('Y-m-d H:i:s', $endTs);
        try {
            $inSum = (int) ORM::for_table('rad_acct')
                ->where_raw("BINARY username = '$uname'")
                ->where_gte('dateAdded', $startFilter)
                ->where_lte('dateAdded', $endFilter)
                ->sum('acctinputoctets');
            $outSum = (int) ORM::for_table('rad_acct')
                ->where_raw("BINARY username = '$uname'")
                ->where_gte('dateAdded', $startFilter)
                ->where_lte('dateAdded', $endFilter)
                ->sum('acctoutputoctets');
            $used = max(0, $inSum + $outSum);
        } catch (Exception $e) {
            $used = 0;
        }

        $hasLimit = false;
        $limitBytes = null;
        $remaining = null;
        $remainingRatio = null;

        $planTypeBp = isset($planArr['typebp']) ? $planArr['typebp'] : '';
        $planLimitType = isset($planArr['limit_type']) ? $planArr['limit_type'] : '';
        if ($planTypeBp === 'Limited' && in_array($planLimitType, ['Data_Limit', 'Both_Limit'], true)) {
            $hasLimit = true;
            $limitBytes = (int) Text::convertDataUnit($planArr['data_limit'], $planArr['data_unit']);
            if ($limitBytes < 0) $limitBytes = 0;
            $remaining = max(0, $limitBytes - $used);
            if ($limitBytes > 0) {
                $remainingRatio = $remaining / $limitBytes;
            } else {
                $remainingRatio = null;
            }
        }

        $items[] = [
            'plan_name' => (string) (isset($bill['namebp']) ? $bill['namebp'] : (isset($planArr['name_plan']) ? $planArr['name_plan'] : '')),
            'username' => (string) $user['username'],
            'start_dt' => date('Y-m-d H:i:s', $startTs),
            'end_dt' => date('Y-m-d H:i:s', $endTs),
            'used_bytes' => $used,
            'used_human' => radius_data_usage_format_bytes($used),
            'has_limit' => $hasLimit,
            'limit_bytes' => $limitBytes,
            'limit_human' => ($hasLimit && $limitBytes !== null) ? radius_data_usage_format_bytes($limitBytes) : null,
            'remaining_bytes' => $remaining,
            'remaining_human' => ($hasLimit && $remaining !== null) ? radius_data_usage_format_bytes($remaining) : null,
            'remaining_ratio' => $remainingRatio,
        ];
    }

    return $items;
}

/**
 * Format bytes as MB/GB.
 * Rule: if >= 1 GB show in GB, otherwise show in MB.
 */
function radius_data_usage_format_bytes($bytes)
{
    $b = (float) max(0, (int) $bytes);
    $gb = 1024 * 1024 * 1024;
    $mb = 1024 * 1024;
    if ($b >= $gb) {
        return number_format($b / $gb, 2) . ' GB';
    }
    return number_format($b / $mb, 2) . ' MB';
}
