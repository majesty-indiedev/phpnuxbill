<?php
/**
 * Hotspot action queue/runner for captive portal flows.
 *
 * Goal: avoid blocking the HTTP request while RouterOS API login/logout runs.
 * Storage: filesystem under $CACHE_PATH/hotspot_actions (no DB migration needed).
 */

class HotspotAction
{
    private static function dir(): string
    {
        global $CACHE_PATH;
        return $CACHE_PATH . DIRECTORY_SEPARATOR . 'hotspot_actions';
    }

    private static function path(string $id): string
    {
        return self::dir() . DIRECTORY_SEPARATOR . $id . '.json';
    }

    private static function ensureDir(): void
    {
        $dir = self::dir();
        if (!file_exists($dir)) {
            @mkdir($dir, 0775, true);
            @touch($dir . DIRECTORY_SEPARATOR . 'index.html');
        }
    }

    public static function create(array $data): string
    {
        self::ensureDir();
        $id = bin2hex(random_bytes(8));

        $now = date('Y-m-d H:i:s');
        $job = array_merge([
            'id' => $id,
            'status' => 'pending', // pending|running|success|failed
            'message' => '',
            'attempts' => 0,
            'created_at' => $now,
            'updated_at' => $now,
            'started_at' => null,
            'finished_at' => null,
        ], $data);

        file_put_contents(self::path($id), json_encode($job, JSON_PRETTY_PRINT));
        return $id;
    }

    public static function get(string $id): ?array
    {
        $path = self::path($id);
        if (!file_exists($path)) {
            return null;
        }
        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            return null;
        }
        return $data;
    }

    public static function update(string $id, array $fields): bool
    {
        $job = self::get($id);
        if (!$job) {
            return false;
        }
        $job = array_merge($job, $fields);
        $job['updated_at'] = date('Y-m-d H:i:s');
        return (bool) file_put_contents(self::path($id), json_encode($job, JSON_PRETTY_PRINT));
    }

    /**
     * Execute the job. Intended to be called after sending a response.
     */
    public static function process(string $id): void
    {
        $job = self::get($id);
        if (!$job) {
            return;
        }
        if (in_array($job['status'], ['running', 'success', 'failed'], true)) {
            return;
        }

        @set_time_limit(120);
        @ignore_user_abort(true);

        self::update($id, [
            'status' => 'running',
            'attempts' => (int) ($job['attempts'] ?? 0) + 1,
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        try {
            $action = $job['action'] ?? '';
            $rechargeId = (int) ($job['recharge_id'] ?? 0);
            $customerId = (int) ($job['customer_id'] ?? 0);

            if (empty($action) || $rechargeId <= 0 || $customerId <= 0) {
                throw new Exception("Invalid job payload");
            }

            $user = ORM::for_table('tbl_customers')->find_one($customerId);
            if (!$user) {
                throw new Exception("Customer not found");
            }

            $bill = ORM::for_table('tbl_user_recharges')->find_one($rechargeId);
            if (!$bill || $bill['customer_id'] != $customerId) {
                throw new Exception("Active plan not found");
            }

            $plan = ORM::for_table('tbl_plans')->find_one($bill['plan_id']);
            if (!$plan) {
                throw new Exception("Plan not found");
            }

            $dvc = Package::getDevice($plan);
            if (!file_exists($dvc)) {
                throw new Exception("Device not found");
            }
            require_once $dvc;

            if ($action === 'login') {
                $nuxIp = (string) ($job['nux_ip'] ?? '');
                $nuxMac = (string) ($job['nux_mac'] ?? '');
                if (empty($nuxIp) || empty($nuxMac)) {
                    throw new Exception("Missing hotspot client IP/MAC (nux-ip/nux-mac)");
                }
                (new $plan['device'])->connect_customer($user, $nuxIp, $nuxMac, $bill['routers']);
            } else if ($action === 'logout') {
                (new $plan['device'])->disconnect_customer($user, $bill['routers']);
            } else {
                throw new Exception("Unknown action");
            }

            self::update($id, [
                'status' => 'success',
                'message' => 'OK',
                'finished_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            self::update($id, [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'finished_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            self::update($id, [
                'status' => 'failed',
                'message' => $e->getMessage(),
                'finished_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Delete old job files to prevent unbounded growth.
     *
     * @param int $maxAgeSeconds Delete jobs older than this many seconds (based on filemtime).
     */
    public static function cleanup(int $maxAgeSeconds = 86400): void
    {
        self::ensureDir();
        $dir = self::dir();
        $now = time();

        foreach (glob($dir . DIRECTORY_SEPARATOR . '*.json') as $file) {
            if (!is_file($file)) {
                continue;
            }
            $age = $now - filemtime($file);
            if ($age > $maxAgeSeconds) {
                @unlink($file);
            }
        }
    }
}
