<?php

class RadiusRest {

    // show Description
    function description()
    {
        return [
            'title' => 'Radius Rest API',
            'description' => 'This devices will handle Radius Connection using Rest API',
            'author' => 'ibnu maksum',
            'url' => [
                'Wiki Tutorial' => 'https://github.com/hotspotbilling/phpnuxbill/wiki/FreeRadius-Rest',
                'Telegram' => 'https://t.me/phpnuxbill',
                'Donate' => 'https://paypal.me/ibnux'
            ]
        ];
    }

    // Add Customer to Mikrotik/Device
    function add_customer($customer, $plan)
    {
    }

	function sync_customer($customer, $plan)
    {
        $this->add_customer($customer, $plan);
    }

    // Remove Customer to Mikrotik/Device
    function remove_customer($customer, $plan)
    {
        // set zero data usage
        if ($plan['typebp'] == "Limited" && ($plan['limit_type'] == "Data_Limit" || $plan['limit_type'] == "Both_Limit")) {
            $cs = ORM::for_table("rad_acct")->where('username', $customer['username'])->findMany();
            foreach ($cs as $c) {
                $c->acctOutputOctets = 0;
                $c->acctInputOctets = 0;
                $c->save();
            }
        }
    }

    // customer change username
    public function change_username($plan, $from, $to)
    {
    }

    // Add Plan to Mikrotik/Device
    function add_plan($plan)
    {
    }

    // Update Plan to Mikrotik/Device
    function update_plan($old_name, $plan)
    {
    }

    // Remove Plan from Mikrotik/Device
    function remove_plan($plan)
    {
    }

    // check if customer is online
    function online_customer($customer, $router_name)
    {
        global $config;

        $username = is_array($customer) ? ($customer['username'] ?? '') : '';
        if (empty($username)) {
            return false;
        }

        // Determine an "online" window based on interim updates (minutes).
        // We consider a user online if the latest accounting record is Start/Interim-Update
        // and the last update is recent.
        // Default window: 10 minutes.
        $windowSeconds = 600;
        if (isset($config['frrest_interim_update'])) {
            $m = (int) $config['frrest_interim_update'];
            if ($m > 0) {
                // ~3 intervals + buffer, but never less than 2 minutes.
                $windowSeconds = max(120, ($m * 60 * 3) + 30);
            }
        }

        // Fetch latest accounting row for this username.
        // Avoid filtering by dateAdded in SQL because timezone / type coercion differences
        // can cause false negatives on some installs; we apply the recency window in PHP.
        $u = addslashes($username);
        $row = ORM::for_table('rad_acct')
            ->where_raw("BINARY username = '$u'")
            ->order_by_desc('id')
            ->find_one();

        // Fallback (non-binary) in case the NAS sends a different casing for username.
        if (!$row) {
            $row = ORM::for_table('rad_acct')
                ->where('username', $username)
                ->order_by_desc('id')
                ->find_one();
        }

        if (!$row) {
            return false;
        }

        $status = $row['acctstatustype'] ?? $row['acctStatusType'] ?? $row['acctatustype'] ?? null;
        $status = trim((string) $status);
        $statusNorm = strtolower($status);
        if (!in_array($statusNorm, ['start', 'interim-update'], true)) {
            return false;
        }

        // If dateAdded is parseable, enforce recency window; otherwise trust status.
        $dateAdded = $row['dateAdded'] ?? $row['dateadded'] ?? null;
        if (is_string($dateAdded) && $dateAdded !== '') {
            $ts = strtotime($dateAdded);
            if ($ts !== false) {
                // Use abs() to tolerate DB/PHP timezone offsets.
                return abs(time() - $ts) <= $windowSeconds;
            }
        }
        return true;
    }

    // make customer online
    function connect_customer($customer, $ip, $mac_address, $router_name)
    {
    }

    // make customer disconnect
    function disconnect_customer($customer, $router_name)
    {
    }

}
