<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * used for ajax
 **/

_auth();

$action = $routes['1'];
$user = User::_info();

switch ($action) {
    case 'isLogin':
        $bill = ORM::for_table('tbl_user_recharges')->where('id', $routes['2'])->where('username', $user['username'])->findOne();
        if ($bill['type'] == 'Hotspot' && $bill['status'] == 'on') {
            $p = ORM::for_table('tbl_plans')->find_one($bill['plan_id']);
            $dvc = Package::getDevice($p);
            // RADIUS plans are authenticated by the NAS via FreeRADIUS (radius.php),
            // not by RouterOS "active login/logout". Avoid trying to "connect to device"
            // from the dashboard, which can cause confusing errors like "Failed to connect to device".
            $isRadiusPlan = ((int) ($p['is_radius'] ?? 0) === 1) ||
                in_array(($p['device'] ?? ''), ['Radius', 'RadiusRest'], true) ||
                (strtolower((string) ($bill['routers'] ?? '')) === 'radius');
            if ($isRadiusPlan) {
                $online = false;
                try {
                    if (file_exists($dvc)) {
                        require_once $dvc;
                        if (!empty($p['device'])) {
                            $online = (bool) (new $p['device'])->online_customer($user, $bill['routers']);
                        }
                    }
                } catch (Throwable $e) {
                    $online = false;
                } catch (Exception $e) {
                    $online = false;
                }

                $label = $online ? Lang::T('Online') : Lang::T('Offline');
                $cls = $online ? 'btn-success' : 'btn-default';
                $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                $safeTitle = htmlspecialchars(Lang::T('Radius Managed'), ENT_QUOTES, 'UTF-8');
                die('<span class="btn ' . $cls . ' btn-xs btn-block disabled" aria-disabled="true" title="' . $safeTitle . '">' . $safeLabel . '</span>');
            }
            if ($_app_stage != 'demo') {
                try {
                    if (file_exists($dvc)) {
                        require_once $dvc;
                        if ((new $p['device'])->online_customer($user, $bill['routers'])) {
                            // iOS captive portal webviews often block native JS confirm()/alert().
                            // Use a plain link so logout works in captive portals.
                            $href = getUrl('hotspot_action/enqueue/logout/' . $bill['id']);
                            $enqueueJson = getUrl('hotspot_action/enqueue_json/logout/' . $bill['id']);
                            $refreshUrl = getUrl('autoload_user/isLogin/' . $bill['id']);

                            $hrefOnline = getUrl('hotspot_action/enqueue/logout/' . $bill['id']);
                            $hrefOffline = getUrl('hotspot_action/enqueue/login/' . $bill['id']);
                            $enqueueOnline = getUrl('hotspot_action/enqueue_json/logout/' . $bill['id']);
                            $enqueueOffline = getUrl('hotspot_action/enqueue_json/login/' . $bill['id']);
                            $textOnline = Lang::T('You are Online, Logout?');
                            $textOffline = Lang::T('Not Online, Login now?');
                            die(
                                '<a href="' . $href . '" ' .
                                'data-enqueue-json="' . $enqueueJson . '" ' .
                                'data-refresh-url="' . $refreshUrl . '" ' .
                                'data-recharge-id="' . (int) $bill['id'] . '" ' .
                                'data-op="logout" ' .
                                'data-href-online="' . $hrefOnline . '" ' .
                                'data-href-offline="' . $hrefOffline . '" ' .
                                'data-enqueue-online="' . $enqueueOnline . '" ' .
                                'data-enqueue-offline="' . $enqueueOffline . '" ' .
                                'data-text-online="' . htmlspecialchars($textOnline, ENT_QUOTES, 'UTF-8') . '" ' .
                                'data-text-offline="' . htmlspecialchars($textOffline, ENT_QUOTES, 'UTF-8') . '" ' .
                                'class="btn btn-success btn-xs btn-block js-hotspot-action">' .
                                $textOnline .
                                '</a>'
                            );
                        } else {
                            if (!empty($_SESSION['nux-mac']) && !empty($_SESSION['nux-ip'])) {
                                // Use a plain link so login works in captive portals.
                                $href = getUrl('hotspot_action/enqueue/login/' . $bill['id']);
                                $enqueueJson = getUrl('hotspot_action/enqueue_json/login/' . $bill['id']);
                                $refreshUrl = getUrl('autoload_user/isLogin/' . $bill['id']);

                                $hrefOnline = getUrl('hotspot_action/enqueue/logout/' . $bill['id']);
                                $hrefOffline = getUrl('hotspot_action/enqueue/login/' . $bill['id']);
                                $enqueueOnline = getUrl('hotspot_action/enqueue_json/logout/' . $bill['id']);
                                $enqueueOffline = getUrl('hotspot_action/enqueue_json/login/' . $bill['id']);
                                $textOnline = Lang::T('You are Online, Logout?');
                                $textOffline = Lang::T('Not Online, Login now?');
                                die(
                                    '<a href="' . $href . '" ' .
                                    'data-enqueue-json="' . $enqueueJson . '" ' .
                                    'data-refresh-url="' . $refreshUrl . '" ' .
                                    'data-recharge-id="' . (int) $bill['id'] . '" ' .
                                    'data-op="login" ' .
                                    'data-href-online="' . $hrefOnline . '" ' .
                                    'data-href-offline="' . $hrefOffline . '" ' .
                                    'data-enqueue-online="' . $enqueueOnline . '" ' .
                                    'data-enqueue-offline="' . $enqueueOffline . '" ' .
                                    'data-text-online="' . htmlspecialchars($textOnline, ENT_QUOTES, 'UTF-8') . '" ' .
                                    'data-text-offline="' . htmlspecialchars($textOffline, ENT_QUOTES, 'UTF-8') . '" ' .
                                    'class="btn btn-danger btn-xs btn-block js-hotspot-action">' .
                                    $textOffline .
                                    '</a>'
                                );
                            } else {
                                die(Lang::T('-'));
                            }
                        }
                    } else {
                        die(Lang::T('-'));
                    }
                } catch (Exception $e) {
                    die(Lang::T('Failed to connect to device'));
                }
            }
            die(Lang::T('-'));
        } else {
            die('--');
        }
        // Always exits via die() above.
    case 'bw_name':
        $bw = ORM::for_table('tbl_bandwidth')->select("name_bw")->find_one($routes['2']);
        echo $bw['name_bw'];
        die();
    case 'inbox_unread':
        $count =  ORM::for_table('tbl_customers_inbox')->where('customer_id', $user['id'])->whereRaw('date_read is null')->count('id');
        if ($count > 0) {
            echo $count;
        }
        die();
    case 'inbox':
        $inboxs = ORM::for_table('tbl_customers_inbox')->selects(['id', 'subject', 'date_created'])->where('customer_id', $user['id'])->whereRaw('date_read is null')->order_by_desc('date_created')->limit(10)->find_many();
        foreach ($inboxs as $inbox) {
            echo '<li><a href="' . getUrl('mail/view/' . $inbox['id']) . '">' . $inbox['subject'] . '<br><sub class="text-muted">' . Lang::dateTimeFormat($inbox['date_created']) . '</sub></a></li>';
        }
        die();
    case 'language':
        $select = _get('select');
        $folders = [];
        $files = scandir('system/lan/');
        foreach ($files as $file) {
            if (is_file('system/lan/' . $file) && !in_array($file, ['index.html', 'country.json', '.DS_Store'])) {
                $file = str_replace(".json", "", $file);
                if(!empty($file)){
                    echo '<li><a href="' . getUrl('accounts/language-update-post&lang=' . $file) . '">';
                    if($select == $file){
                        echo '<span class="glyphicon glyphicon-ok"></span> ';
                    }
                    echo ucwords($file) . '</a></li>';
                }
            }
        }
        die();
    default:
        die();
}
