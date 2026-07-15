<?php
/**
 * Subscription Model
 * Manages SaaS plans, subscription states, billing logs, and checking resource limits.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Subscription {
    /**
     * Get the active subscription details of a user
     */
    public static function getActive(int $userId): array {
        $sub = Database::query(
            "SELECT s.*, p.name as plan_name, p.price_monthly, p.price_yearly, 
                    p.website_limit, p.storage_limit_mb, p.has_custom_domain, 
                    p.has_analytics, p.has_github, p.features_json 
             FROM subscriptions s 
             JOIN plans p ON s.plan_id = p.id 
             WHERE s.user_id = ? AND s.status IN ('active', 'trialing') 
             LIMIT 1",
            [$userId]
        )->fetch();

        if ($sub) {
            // Unpack features
            $sub['features'] = json_decode($sub['features_json'] ?? '{}', true) ?: [];
            return $sub;
        }

        // Fallback to absolute bare free limits if subscription record is missing
        return [
            'plan_id' => 1,
            'plan_name' => 'Free',
            'status' => 'active',
            'website_limit' => 1,
            'storage_limit_mb' => 50,
            'has_custom_domain' => 0,
            'has_analytics' => 0,
            'has_github' => 0,
            'features' => ["builder" => true, "ecom" => false, "blog" => true, "custom_domain" => false]
        ];
    }

    /**
     * Verify if user is allowed to create a new website under their plan
     */
    public static function canCreateWebsite(int $userId): bool {
        $sub = self::getActive($userId);
        $count = (int)Database::query("SELECT COUNT(*) as cnt FROM websites WHERE user_id = ?", [$userId])->fetch()['cnt'];
        return $count < (int)$sub['website_limit'];
    }

    /**
     * Verify if user has storage left (takes incoming filesize in bytes)
     */
    public static function canUpload(int $userId, int $incomingBytes): bool {
        $sub = self::getActive($userId);
        $maxBytes = $sub['storage_limit_mb'] * 1024 * 1024;
        
        $used = (int)Database::query("SELECT SUM(filesize) as total FROM media WHERE user_id = ?", [$userId])->fetch()['total'];
        
        return ($used + $incomingBytes) <= $maxBytes;
    }

    /**
     * Subscribe or upgrade user plan
     */
    public static function upgrade(int $userId, int $planId, string $cycle, string $gateway, string $txId, float $amount): bool {
        Database::beginTransaction();
        try {
            $plan = Database::query("SELECT * FROM plans WHERE id = ?", [$planId])->fetch();
            if (!$plan) {
                throw new Exception("Target plan does not exist.");
            }

            // Expiry date calculation
            $interval = ($cycle === 'yearly') ? '+1 year' : '+1 month';
            $endsAt = date('Y-m-d H:i:s', strtotime($interval));

            // Cancel any active subscriptions
            Database::query(
                "UPDATE subscriptions SET status = 'expired', ends_at = NOW() WHERE user_id = ? AND status = 'active'",
                [$userId]
            );

            // Create new subscription record
            Database::query(
                "INSERT INTO subscriptions (user_id, plan_id, status, billing_cycle, starts_at, ends_at) 
                 VALUES (?, ?, 'active', ?, NOW(), ?)",
                [$userId, $planId, $cycle, $endsAt]
            );
            $subId = (int)Database::lastInsertId();

            // Record invoice details / payment logs
            $invoice = "INV-" . strtoupper(bin2hex(random_bytes(4)));
            Database::query(
                "INSERT INTO payments (user_id, subscription_id, amount, gateway, transaction_id, status, invoice_number) 
                 VALUES (?, ?, ?, ?, ?, 'completed', ?)",
                [$userId, $subId, $amount, $gateway, $txId, $invoice]
            );

            Database::commit();
            log_activity($userId, 'upgrade_subscription', "Upgraded to plan ID $planId ($cycle) via $gateway.");
            return true;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
