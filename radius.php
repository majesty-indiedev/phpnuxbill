<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill)
 *  by https://t.me/ibnux
 *
 * Authorize
 *    - Voucher activation
 * Authenticate
 *    - is it allow to login
 * Accounting
 *    - log
 **/

header("Content-Type: application/json");

include "init.php";

$action = $_SERVER['HTTP_X_FREERADIUS_SECTION'];
if (empty($action)) {
    $action = _get('action');
}

$code = 200;

//debug
// if (!empty($action)) {
//     file_put_contents("$action.json", json_encode([
//         'header' => $_SERVER,
//         'get' => $_GET,
//         'post' => $_POST,
//         'time' => time()
//     ]));
// }

try {
    switch ($action) {
        case 'authenticate':
            $username = _req('username');
            $password = _req('password');
            $CHAPassword = _req('CHAPassword');
            $CHAPchallenge = _req('CHAPchallenge');
            $isCHAP = false;
            if (!empty($CHAPassword)) {
                $c = ORM::for_table('tbl_customers')->select('password')->select('pppoe_password')->whereRaw("BINARY username = '$username' AND status = 'Active'")->find_one();
                if ($c) {
                    if (Password::chap_verify($c['password'], $CHAPassword, $CHAPchallenge)) {
                        $password = $c['password'];
                        $isVoucher = false;
                        $isCHAP = true;
                    } else if (!empty($c['pppoe_password']) && Password::chap_verify($c['pppoe_password'], $CHAPassword, $CHAPchallenge)) {
                        $password = $c['pppoe_password'];
                        $isVoucher = false;
                        $isCHAP = true;
                    } else {
                        // check if voucher
                        if (Password::chap_verify($username, $CHAPassword, $CHAPchallenge)) {
                            $isVoucher = true;
                            $password = $username;
                        } else {
                            // no password is voucher
                            if (Password::chap_verify('', $CHAPassword, $CHAPchallenge)) {
                                $isVoucher = true;
                                $password = $username;
                            } else {
                                show_radius_result(['Reply-Message' => 'Username or Password is wrong'], 401);
                            }
                        }
                    }
                } else {
                    $c = ORM::for_table('tbl_customers')->select('password')->select('pppoe_password')->whereRaw("BINARY pppoe_username = '$username' AND status = 'Active'")->find_one();
                    if ($c) {
                        if (Password::chap_verify($c['password'], $CHAPassword, $CHAPchallenge)) {
                            $password = $c['password'];
                            $isVoucher = false;
                            $isCHAP = true;
                        } else if (!empty($c['pppoe_password']) && Password::chap_verify($c['pppoe_password'], $CHAPassword, $CHAPchallenge)) {
                            $password = $c['pppoe_password'];
                            $isVoucher = false;
                            $isCHAP = true;
                        } else {
                            // check if voucher
                            if (Password::chap_verify($username, $CHAPassword, $CHAPchallenge)) {
                                $isVoucher = true;
                                $password = $username;
                            } else {
                                // no password is voucher
                                if (Password::chap_verify('', $CHAPassword, $CHAPchallenge)) {
                                    $isVoucher = true;
                                    $password = $username;
                                } else {
                                    show_radius_result(['Reply-Message' => 'Username or Password is wrong'], 401);
                                }
                            }
                        }
                    }
                }
            } else {
                if (!empty($username) && empty($password)) {
                    // Voucher with empty password
                    $isVoucher = true;
                    $password = $username;
                } else if (empty($username) || empty($password)) {
                    show_radius_result([
                        "control:Auth-Type" => "Reject",
                        "reply:Reply-Message" => 'Login invalid......'
                    ], 401);
                }
            }
            if ($username == $password) {
                $username = Text::alphanumeric($username, "-_.,");
                $d = ORM::for_table('tbl_voucher')->whereRaw("BINARY code = '$username'")->find_one();
            } else {
                $d = ORM::for_table('tbl_customers')->whereRaw("BINARY username = '$username' AND status = 'Active'")->find_one();
                if ($d['password'] != $password) {
                    if ($d['pppoe_password'] != $password) {
                        unset($d);
                    }
                }
            }
            if ($d) {
                header("HTTP/1.1 204 No Content");
                die();
            } else {
                show_radius_result([
                    "control:Auth-Type" => "Reject",
                    "reply:Reply-Message" => 'Login invalid......'
                ], 401);
            }
            break;
        case 'authorize':
            $username = _req('username');
            $password = _req('password');
            $isVoucher = ($username == $password);
            $CHAPassword = _req('CHAPassword');
            $CHAPchallenge = _req('CHAPchallenge');
            $isCHAP = false;
            if (!empty($CHAPassword)) {
                $c = ORM::for_table('tbl_customers')->select('password')->select('pppoe_password')->whereRaw("BINARY username = '$username' AND status = 'Active'")->find_one();
                if ($c) {
                    if (Password::chap_verify($c['password'], $CHAPassword, $CHAPchallenge)) {
                        $password = $c['password'];
                        $isVoucher = false;
                        $isCHAP = true;
                    } else if (!empty($c['pppoe_password']) && Password::chap_verify($c['pppoe_password'], $CHAPassword, $CHAPchallenge)) {
                        $password = $c['pppoe_password'];
                        $isVoucher = false;
                        $isCHAP = true;
                    } else {
                        // check if voucher
                        if (Password::chap_verify($username, $CHAPassword, $CHAPchallenge)) {
                            $isVoucher = true;
                            $password = $username;
                        } else {
                            // no password is voucher
                            if (Password::chap_verify('', $CHAPassword, $CHAPchallenge)) {
                                $isVoucher = true;
                                $password = $username;
                            } else {
                                show_radius_result(['Reply-Message' => 'Username or Password is wrong'], 401);
                            }
                        }
                    }
                } else {
                    $c = ORM::for_table('tbl_customers')->select('password')->select('username')->select('pppoe_password')->whereRaw("BINARY pppoe_username = '$username' AND status = 'Active'")->find_one();
                    if ($c) {
                        if (Password::chap_verify($c['password'], $CHAPassword, $CHAPchallenge)) {
                            $password = $c['password'];
                            $username = $c['username'];
                            $isVoucher = false;
                            $isCHAP = true;
                        } else if (!empty($c['pppoe_password']) && Password::chap_verify($c['pppoe_password'], $CHAPassword, $CHAPchallenge)) {
                            $password = $c['pppoe_password'];
                            $username = $c['username'];
                            $isVoucher = false;
                            $isCHAP = true;
                        } else {
                            // check if voucher
                            if (Password::chap_verify($username, $CHAPassword, $CHAPchallenge)) {
                                $isVoucher = true;
                                $password = $username;
                            } else {
                                // no password is voucher
                                if (Password::chap_verify('', $CHAPassword, $CHAPchallenge)) {
                                    $isVoucher = true;
                                    $password = $username;
                                } else {
                                    show_radius_result(['Reply-Message' => 'Username or Password is wrong'], 401);
                                }
                            }
                        }
                    }
                }
            } else {
                if (!empty($username) && empty($password)) {
                    // Voucher with empty password
                    $isVoucher = true;
                    $password = $username;
                } else if (empty($username) || empty($password)) {
                    show_radius_result([
                        "control:Auth-Type" => "Reject",
                        "reply:Reply-Message" => 'Login invalid......'
                    ], 401);
                }
            }
            $tur = ORM::for_table('tbl_user_recharges')->whereRaw("BINARY username = '$username'")->find_one();
            if (!$tur) {
                // if check if pppoe_username
                $c = ORM::for_table('tbl_customers')->select('username')->select('pppoe_password')->whereRaw("BINARY pppoe_username = '$username'")->find_one();
                if ($c) {
                    $username = $c['username'];
                    $tur = ORM::for_table('tbl_user_recharges')->whereRaw("BINARY username = '$username'")->find_one();
                }
            }
            if ($tur) {
                if (!$isVoucher && !$isCHAP) {
                    $d = ORM::for_table('tbl_customers')->select('password')->select('pppoe_password')->whereRaw("BINARY username = '$username' AND status = 'Active'")->find_one();
                    if ($d) {
                        if ($d['password'] != $password) {
                            if ($d['pppoe_password'] != $password) {
                                show_radius_result(['Reply-Message' => 'Username or Password is wrong'], 401);
                            }
                        }
                    } else {
                        $d = ORM::for_table('tbl_customers')->select('password')->select('pppoe_password')->whereRaw("BINARY pppoe_username = '$username' AND status = 'Active'")->find_one();
                        if ($d) {
                            if ($d['password'] != $password) {
                                if ($d['pppoe_password'] != $password) {
                                    show_radius_result(['Reply-Message' => 'Username or Password is wrong'], 401);
                                }
                            }
                        }
                    }
                }
                process_radiust_rest($tur, $code);
            } else {
                if ($isVoucher) {
                    $username = Text::alphanumeric($username, "-_.,");
                    $v = ORM::for_table('tbl_voucher')->whereRaw("BINARY code = '$username' AND routers = 'radius'")->find_one();
                    if ($v) {
                        if ($v['status'] == 0) {
                            if (Package::rechargeUser(0, $v['routers'], $v['id_plan'], "Voucher", $username)) {
                                $v->status = "1";
                                $v->used_date = date('Y-m-d H:i:s');
                                $v->save();
                                $tur = ORM::for_table('tbl_user_recharges')->whereRaw("BINARY username = '$username'")->find_one();
                                if ($tur) {
                                    process_radiust_rest($tur, $code);
                                } else {
                                    show_radius_result(['Reply-Message' => 'Voucher activation failed'], 401);
                                }
                            } else {
                                show_radius_result(['Reply-Message' => 'Voucher activation failed.'], 401);
                            }
                        } else {
                            show_radius_result(['Reply-Message' => 'Voucher Expired...'], 401);
                        }
                    } else {
                        show_radius_result(['Reply-Message' => 'Invalid Voucher..'], 401);
                    }
                } else {
                    show_radius_result(['Reply-Message' => 'Internet Plan Expired..'], 401);
                }
            }
            break;
        case 'accounting':
            $username = _req('username');
            if (empty($username)) {
                show_radius_result([
                    "control:Auth-Type" => "Reject",
                    "reply:Reply-Message" => 'Username empty'
                ], 200);
                die();
            }
            header("HTTP/1.1 200 ok");
            $acctSessionId = _post('acctSessionId');
            $nasid = _post('nasid');
            $macAddr = _post('macAddr');
            $d = ORM::for_table('rad_acct')
                // Use acctsessionid to preserve per-session history.
                // Without this, reconnects overwrite the same row and usage appears to "reset to 0".
                ->whereRaw("BINARY username = '$username' AND acctsessionid = '" . $acctSessionId . "' AND macaddr = '" . $macAddr . "' AND nasid = '" . $nasid . "'")
                ->findOne();
            if (!$d) {
                $d = ORM::for_table('rad_acct')->create();
            }
            // MikroTik/FreeRADIUS accounting octets are typically cumulative per session.
            // Store the latest counters (including Gigawords) instead of summing them.
            $acctOutputOctets = _post('acctOutputOctets', 0);
            $acctInputOctets = _post('acctInputOctets', 0);
            $acctOutputGigawords = _post('acctOutputGigawords', 0);
            $acctInputGigawords = _post('acctInputGigawords', 0);
            $out = (int) $acctOutputOctets + ((int) $acctOutputGigawords * 4294967296);
            $in = (int) $acctInputOctets + ((int) $acctInputGigawords * 4294967296);
            if ($out < 0) $out = 0;
            if ($in < 0) $in = 0;
            $d->acctOutputOctets = $out;
            $d->acctInputOctets = $in;

            $d->acctsessionid = $acctSessionId;
            $d->username = $username;
            $d->realm = _post('realm');
            $d->nasipaddress = _post('nasIpAddress');
            $d->acctsessiontime = intval(_post('acctSessionTime'));
            $d->nasid = $nasid;
            $d->nasportid = _post('nasPortId');
            $d->nasporttype = _post('nasPortType');
            $d->framedipaddress = _post('framedIPAddress');
            if (in_array(_post('acctStatusType'), ['Start', 'Stop', 'Interim-Update'], true)) {
                $d->acctstatustype = _post('acctStatusType');
            }
            $d->macaddr = $macAddr;
            $d->dateAdded = date('Y-m-d H:i:s');
            // pastikan data akunting yang disimpan memang customer aktif phpnuxbill
            $tur = ORM::for_table('tbl_user_recharges')->whereRaw("BINARY username = '$username' AND `status` = 'on' AND `routers` = 'radius'")->find_one();
            if (!$tur) {
                // check if pppoe_username
                $c = ORM::for_table('tbl_customers')->select('username')->whereRaw("BINARY pppoe_username = '$username'")->find_one();
                if ($c) {
                    $username = $c['username'];
                    $tur = ORM::for_table('tbl_user_recharges')->whereRaw("BINARY username = '$username'")->find_one();
                }
            }
            if ($tur) {
                $d->save();
                if (_post('acctStatusType') == 'Start') {
                    $plan = ORM::for_table('tbl_plans')->where('id', $tur['plan_id'])->find_one();
                    if ($plan['limit_type'] == "Data_Limit" || $plan['limit_type'] == "Both_Limit") {
                        $totalUsage = $d['acctOutputOctets'] + $d['acctInputOctets'];
                        $attrs['reply:Mikrotik-Total-Limit'] = Text::convertDataUnit($plan['data_limit'], $plan['data_unit']) - $totalUsage;
                        if ($attrs['reply:Mikrotik-Total-Limit'] < 0) {
                            $attrs['reply:Mikrotik-Total-Limit'] = 0;
                            show_radius_result(["control:Auth-Type" => "Accept", 'Reply-Message' => 'You have exceeded your data limit.'], 401);
                        }
                    }
                }
                process_radiust_rest($tur, 200);
            }
            show_radius_result([
                "control:Auth-Type" => "Accept",
                "reply:Reply-Message" => 'Saved'
            ], 200);
            break;
    }
    die();
} catch (Throwable $e) {
    Message::sendTelegram(
        "Sistem Error.\n" .
            $e->getMessage() . "\n" .
            $e->getTraceAsString()
    );
    show_radius_result(['Reply-Message' => 'Command Failed : ' . $action], 401);
} catch (Exception $e) {
    Message::sendTelegram(
        "Sistem Error.\n" .
            $e->getMessage() . "\n" .
            $e->getTraceAsString()
    );
    show_radius_result(['Reply-Message' => 'Command Failed : ' . $action], 401);
}
show_radius_result(['Reply-Message' => 'Invalid Command : ' . $action], 401);

/**
 * Calculate total data usage for current billing period
 * Uses same approach as radius_data_usage plugin - sums all accounting records in period
 */
function calculate_period_usage($username, $recharged_on, $expiration) {
    $startDate = date('Y-m-d H:i:s', strtotime($recharged_on));
    $endDate = date('Y-m-d H:i:s', strtotime($expiration . ' 23:59:59'));
    
    $uname = addslashes($username);
    $totalBytes = 0;
    
    try {
        // More accurate: Get max per session then sum (since accounting is cumulative per session)
        // This prevents double-counting if multiple records exist per session
        $query = "SELECT SUM(max_bytes) as total FROM (
                    SELECT MAX(acctOutputOctets + acctInputOctets) as max_bytes 
                    FROM rad_acct 
                    WHERE BINARY username = '$uname' 
                    AND dateAdded >= '$startDate' 
                    AND dateAdded <= '$endDate'
                    GROUP BY acctsessionid
                  ) as session_max";
        
        ORM::raw_execute($query);
        $statement = ORM::get_last_statement();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['total']) && $row['total'] > 0) {
            $totalBytes = intval($row['total']);
        } else {
            // Fallback: Simple sum (less accurate but works)
            $inSum = (int) ORM::for_table('rad_acct')
                ->where_raw("BINARY username = '$uname'")
                ->where_gte('dateAdded', $startDate)
                ->where_lte('dateAdded', $endDate)
                ->sum('acctInputOctets');
                
            $outSum = (int) ORM::for_table('rad_acct')
                ->where_raw("BINARY username = '$uname'")
                ->where_gte('dateAdded', $startDate)
                ->where_lte('dateAdded', $endDate)
                ->sum('acctOutputOctets');
                
            $totalBytes = max(0, $inSum + $outSum);
        }
    } catch (Exception $e) {
        // If query fails, use simple sum as fallback
        try {
            $inSum = (int) ORM::for_table('rad_acct')
                ->where_raw("BINARY username = '$uname'")
                ->where_gte('dateAdded', $startDate)
                ->where_lte('dateAdded', $endDate)
                ->sum('acctInputOctets');
                
            $outSum = (int) ORM::for_table('rad_acct')
                ->where_raw("BINARY username = '$uname'")
                ->where_gte('dateAdded', $startDate)
                ->where_lte('dateAdded', $endDate)
                ->sum('acctOutputOctets');
                
            $totalBytes = max(0, $inSum + $outSum);
        } catch (Exception $e2) {
            $totalBytes = 0;
        }
    }
    
    return $totalBytes;
}

function process_radiust_rest($tur, $code)
{
    global $config;
    $plan = ORM::for_table('tbl_plans')->where('id', $tur['plan_id'])->find_one();
    
    // Fair Usage Policy (FUP) - Check per plan settings
    // Only apply FUP for Unlimited plans that have FUP configured
    if ($plan['typebp'] == 'Unlimited' && !empty($plan['fup_threshold'])) {
        // Calculate usage for current billing period
        $totalUsageBytes = calculate_period_usage($tur['username'], $tur['recharged_on'], $tur['expiration']);
        
        // Convert threshold to bytes
        $thresholdBytes = 0;
        if ($plan['fup_threshold_unit'] == 'GB') {
            $thresholdBytes = $plan['fup_threshold'] * 1024 * 1024 * 1024;
        } else if ($plan['fup_threshold_unit'] == 'MB') {
            $thresholdBytes = $plan['fup_threshold'] * 1024 * 1024;
        }
        
        // Check if threshold exceeded
        if ($totalUsageBytes >= $thresholdBytes) {
            // NEW: Use direct bandwidth if specified (implicit FUP)
            if (!empty($plan['fup_rate_up']) && !empty($plan['fup_rate_down'])) {
                // Build rate limit string from direct bandwidth fields
                $unitdown = ($plan['fup_rate_down_unit'] == 'Kbps') ? 'K' : 'M';
                $unitup = ($plan['fup_rate_up_unit'] == 'Kbps') ? 'K' : 'M';
                $rate = $plan['fup_rate_up'] . $unitup . "/" . $plan['fup_rate_down'] . $unitdown;
                
                if (!empty(trim($plan['fup_burst']))) {
                    $rate .= ' ' . $plan['fup_burst'];
                }
                
                // Apply FUP bandwidth directly (no plan switch needed)
                // Update bandwidth attributes immediately
                if ($plan['is_radius'] || $plan['device'] == 'Radius' || $plan['device'] == 'RadiusRest') {
                    require_once "system/devices/Radius.php";
                    $radius = new Radius();
                    $radius->upsertCustomerAttr($tur['username'], 'Mikrotik-Rate-Limit', $rate, ':=');
                    try {
                        $radius->disconnectCustomer($tur['username']);
                    } catch (Exception $e) {
                        // If disconnect fails, attributes are still updated for next auth
                    }
                } else {
                    // For Mikrotik Hotspot/PPPoE: Update user profile directly
                    if (class_exists('Package')) {
                        $dvc = Package::getDevice($plan);
                        if (file_exists($dvc)) {
                            require_once $dvc;
                            $customer = ORM::for_table('tbl_customers')->where('username', $tur['username'])->find_one();
                            if ($customer && !empty($plan['device'])) {
                                // Update rate-limit directly on router using RouterOS API
                                $deviceObj = new $plan['device'];
                                $mikrotik = $deviceObj->info($plan['routers']);
                                $client = $deviceObj->getClient($mikrotik['ip_address'], $mikrotik['username'], $mikrotik['password']);
                                
                                if ($client) {
                                    // RouterOS classes are available via device class require_once
                                    // Use full namespace since we're in radius.php
                                    if ($plan['type'] == 'Hotspot') {
                                        // Update hotspot user rate-limit
                                        $printRequest = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/print');
                                        $printRequest->setQuery(\PEAR2\Net\RouterOS\Query::where('name', $customer['username']));
                                        $userInfo = $client->sendSync($printRequest);
                                        $id = $userInfo->getProperty('.id');
                                        if ($id) {
                                            $setRequest = new \PEAR2\Net\RouterOS\Request('/ip/hotspot/user/set');
                                            $setRequest->setArgument('numbers', $id);
                                            $setRequest->setArgument('rate-limit', $rate);
                                            $client->sendSync($setRequest);
                                        }
                                    } else if ($plan['type'] == 'PPPOE') {
                                        // Update PPPoE secret rate-limit
                                        $username = !empty($customer['pppoe_username']) ? $customer['pppoe_username'] : $customer['username'];
                                        $printRequest = new \PEAR2\Net\RouterOS\Request('/ppp/secret/print');
                                        $printRequest->setQuery(\PEAR2\Net\RouterOS\Query::where('name', $username));
                                        $userInfo = $client->sendSync($printRequest);
                                        $id = $userInfo->getProperty('.id');
                                        if ($id) {
                                            $setRequest = new \PEAR2\Net\RouterOS\Request('/ppp/secret/set');
                                            $setRequest->setArgument('numbers', $id);
                                            $setRequest->setArgument('rate-limit', $rate);
                                            $client->sendSync($setRequest);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                // Use FUP bandwidth for this authorization response
                // Create a bandwidth array that will be used below instead of fetching from database
                $fup_bw_used = true;
                $bw = [
                    'rate_up' => $plan['fup_rate_up'],
                    'rate_up_unit' => $plan['fup_rate_up_unit'],
                    'rate_down' => $plan['fup_rate_down'],
                    'rate_down_unit' => $plan['fup_rate_down_unit'],
                    'burst' => $plan['fup_burst'] ?? ''
                ];
            }
        }
    }
    
    // Use FUP bandwidth if it was set, otherwise fetch from database
    if (!isset($fup_bw_used) || !$fup_bw_used) {
        $bw = ORM::for_table("tbl_bandwidth")->find_one($plan['id_bw']);
    }
    // $bw is already set from FUP direct bandwidth above if fup_bw_used is true
    
    // Count User Onlines
    $USRon = ORM::for_table('rad_acct')
        ->whereRaw("BINARY username = '" . $tur['username'] . "' AND acctstatustype = 'Start'")
        ->find_array();
    // get all the IP
    $ips = array_column($USRon, 'framedipaddress');
    // check if user reach shared_users limit but IP is not in the list active
    if (count($USRon) >= $plan['shared_users'] && $plan['type'] == 'Hotspot' && !in_array(_post('framedIPAddress'), $ips)) {
        show_radius_result(["control:Auth-Type" => "Accept", 'Reply-Message' => 'You are already logged in - access denied (' . $USRon . ')'], 401);
    }
    if ($bw['rate_down_unit'] == 'Kbps') {
        $unitdown = 'K';
    } else {
        $unitdown = 'M';
    }
    if ($bw['rate_up_unit'] == 'Kbps') {
        $unitup = 'K';
    } else {
        $unitup = 'M';
    }
    $rate = $bw['rate_up'] . $unitup . "/" . $bw['rate_down'] . $unitdown;
    $rates = explode('/', $rate);

    if (!empty(trim($bw['burst']))) {
        $ratos = $rate . ' ' . $bw['burst'];
    } else {
        $ratos = $rates[0] . '/' . $rates[1];
    }

    $attrs = [];
    $timeexp = strtotime($tur['expiration'] . ' ' . $tur['time']);
    $attrs['reply:Reply-Message'] = 'success';
    $attrs['reply:Simultaneous-Use'] = $plan['shared_users'];
    $attrs['reply:Port-Limit'] = $plan['shared_users']; // Mikrotik-specific attribute for session limits
    $attrs['reply:Mikrotik-Wireless-Comment'] = $plan['name_plan'] . ' | ' . $tur['expiration'] . ' ' . $tur['time'];

    $attrs['reply:Ascend-Data-Rate'] = str_replace('M', '000000', str_replace('K', '000', $rates[1]));
    $attrs['reply:Ascend-Xmit-Rate'] = str_replace('M', '000000', str_replace('K', '000', $rates[0]));
    $attrs['reply:Mikrotik-Rate-Limit'] = $ratos;
    $attrs['reply:WISPr-Bandwidth-Max-Up'] = str_replace('M', '000000', str_replace('K', '000', $rates[0]));
    $attrs['reply:WISPr-Bandwidth-Max-Down'] = str_replace('M', '000000', str_replace('K', '000', $rates[1]));
    $attrs['reply:expiration'] = date('d M Y H:i:s', $timeexp);
    $attrs['reply:WISPr-Session-Terminate-Time'] = date('Y-m-d', $timeexp) . 'T' . date('H:i:sP', $timeexp);

    if ($plan['type'] == 'PPPOE') {
        $attrs['reply:Framed-Pool'] = $plan['pool'];
    }

    if ($plan['typebp'] == "Limited") {
        if ($plan['limit_type'] == "Data_Limit" || $plan['limit_type'] == "Both_Limit") {
            $raddact = ORM::for_table('rad_acct')->whereRaw("BINARY username = '$tur[username]'")->where('acctstatustype', 'Start')->find_one();
            $totalUsage = intval($raddact['acctOutputOctets']) + intval($raddact['acctInputOctets']);
            $attrs['reply:Mikrotik-Total-Limit'] = Text::convertDataUnit($plan['data_limit'], $plan['data_unit']) - $totalUsage;
            if ($attrs['reply:Mikrotik-Total-Limit'] < 0) {
                $attrs['reply:Mikrotik-Total-Limit'] = 0;
                show_radius_result(["control:Auth-Type" => "Accept", 'Reply-Message' => 'You have exceeded your data limit.'], 401);
            }
        }
        if ($plan['limit_type'] == "Time_Limit") {
            if ($plan['time_unit'] == 'Hrs')
                $timelimit = $plan['time_limit'] * 60 * 60;
            else
                $timelimit = $plan['time_limit'] * 60;
            $attrs['reply:Max-All-Session'] = $timelimit;
            $attrs['reply:Expire-After'] = $timelimit;
        } else if ($plan['limit_type'] == "Data_Limit") {
            if ($plan['data_unit'] == 'GB')
                $datalimit = $plan['data_limit'] . "000000000";
            else
                $datalimit = $plan['data_limit'] . "000000";
            $attrs['reply:Max-Data'] = $datalimit;
            $attrs['reply:Mikrotik-Recv-Limit-Gigawords'] = $datalimit;
            $attrs['reply:Mikrotik-Xmit-Limit-Gigawords'] = $datalimit;
        } else if ($plan['limit_type'] == "Both_Limit") {
            if ($plan['time_unit'] == 'Hrs')
                $timelimit = $plan['time_limit'] * 60 * 60;
            else
                $timelimit = $plan['time_limit'] * 60;
            if ($plan['data_unit'] == 'GB')
                $datalimit = $plan['data_limit'] . "000000000";
            else
                $datalimit = $plan['data_limit'] . "000000";
            $attrs['reply:Max-All-Session'] = $timelimit;
            $attrs['reply:Max-Data'] = $datalimit;
            $attrs['reply:Mikrotik-Recv-Limit-Gigawords'] = $datalimit;
            $attrs['reply:Mikrotik-Xmit-Limit-Gigawords'] = $datalimit;
        }
    }
    $result = array_merge([
        "control:Auth-Type" => "Accept",
        "reply" =>  ["Reply-Message" => ['value' => 'success']]
    ], $attrs);
    show_radius_result($result, $code);
}

function show_radius_result($array, $code = 200)
{
    if ($code == 401) {
        header("HTTP/1.1 401 Unauthorized");
    } else if ($code == 200) {
        header("HTTP/1.1 200 OK");
    } else if ($code == 204) {
        header("HTTP/1.1 204 No Content");
        die();
    }
    die(json_encode($array));
}
