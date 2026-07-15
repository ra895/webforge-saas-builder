<?php
/**
 * Website Model
 * Manages SaaS website instances, pages, dynamic blocks, templates, and backups.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Website {
    /**
     * Fetch list of websites owned by a user
     */
    public static function getByUser(int $userId): array {
        return Database::query(
            "SELECT w.*, d.domain_name, d.ssl_status FROM websites w 
             LEFT JOIN domains d ON d.website_id = w.id 
             WHERE w.user_id = ? ORDER BY w.created_at DESC",
            [$userId]
        )->fetchAll();
    }

    /**
     * Fetch single website details
     */
    public static function find(int $id, int $userId): ?array {
        return Database::query(
            "SELECT * FROM websites WHERE id = ? AND user_id = ? LIMIT 1",
            [$id, $userId]
        )->fetch() ?: null;
    }

    /**
     * Create website with default page and layout sections
     */
    public static function create(int $userId, string $name, string $subdomain, ?string $templateSlug = null): int {
        Database::beginTransaction();
        try {
            // Check subdomain availability
            $exists = Database::query("SELECT id FROM websites WHERE subdomain = ?", [$subdomain])->fetch();
            if ($exists) {
                throw new Exception("Subdomain is already taken.");
            }

            // Create website record
            Database::query(
                "INSERT INTO websites (user_id, name, subdomain, category, status) VALUES (?, ?, ?, ?, 'draft')",
                [$userId, $name, $subdomain, $templateSlug ? 'Template' : 'Blank']
            );
            $websiteId = (int)Database::lastInsertId();

            // Create homepage
            Database::query(
                "INSERT INTO pages (website_id, title, slug, is_homepage) VALUES (?, 'Home', 'home', 1)",
                [$websiteId]
            );
            $pageId = (int)Database::lastInsertId();

            // Populate page with blocks (either from template or default blocks)
            $layout = [];
            if ($templateSlug) {
                $template = Database::query("SELECT layout_json FROM templates WHERE slug = ?", [$templateSlug])->fetch();
                if ($template && !empty($template['layout_json'])) {
                    $layout = json_decode($template['layout_json'], true) ?: [];
                }
            }

            // If empty, generate standard blank placeholder sections
            if (empty($layout)) {
                $layout = [
                    [
                        'type' => 'navbar',
                        'content' => [
                            'brand' => $name,
                            'links' => [
                                ['text' => 'Home', 'url' => '#home'],
                                ['text' => 'About', 'url' => '#about'],
                                ['text' => 'Contact', 'url' => '#contact']
                            ],
                            'btn_text' => 'Get in Touch',
                            'btn_url' => '#contact'
                        ]
                    ],
                    [
                        'type' => 'hero',
                        'content' => [
                            'title' => "Welcome to " . $name,
                            'subtitle' => "We build professional, stunning digital experiences that grow your business.",
                            'btn_primary' => "Learn More",
                            'btn_secondary' => "Contact Us",
                            'bg_color' => "#0d6efd"
                        ]
                    ],
                    [
                        'type' => 'about',
                        'content' => [
                            'title' => "About Our Business",
                            'desc' => "We are dedicated to providing outstanding value and exceptional services to our customers. Our team is passionate about excellence."
                        ]
                    ],
                    [
                        'type' => 'contact',
                        'content' => [
                            'title' => "Connect with Us",
                            'email' => "info@" . $subdomain . ".com",
                            'phone' => "+1 (555) 123-4567",
                            'address' => "123 Main Street, City, Country"
                        ]
                    ],
                    [
                        'type' => 'footer',
                        'content' => [
                            'copyright' => "© " . date('Y') . " " . $name . ". All rights reserved."
                        ]
                    ]
                ];
            }

            // Insert sections in order
            $order = 0;
            foreach ($layout as $section) {
                Database::query(
                    "INSERT INTO sections (page_id, type, sort_order, content_json) VALUES (?, ?, ?, ?)",
                    [$pageId, $section['type'], $order++, json_encode($section['content'])]
                );
            }

            // Create a default contact form in DB for this website
            Database::query(
                "INSERT INTO forms (website_id, name, email_recipient) VALUES (?, 'Contact Form', ?)",
                [$websiteId, 'leads@' . $subdomain . '.com']
            );

            Database::commit();
            log_activity($userId, 'create_website', "Created website '$name' with subdomain '$subdomain'.");
            return $websiteId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a website completely
     */
    public static function delete(int $id, int $userId): bool {
        $website = self::find($id, $userId);
        if ($website) {
            Database::query("DELETE FROM websites WHERE id = ?", [$id]);
            log_activity($userId, 'delete_website', "Deleted website ID $id.");
            return true;
        }
        return false;
    }

    /**
     * Duplicate a website, pages, and sections
     */
    public static function duplicate(int $id, int $userId, string $newName, string $newSubdomain): int {
        $website = self::find($id, $userId);
        if (!$website) {
            throw new Exception("Source website not found.");
        }

        // Check subdomain availability
        $exists = Database::query("SELECT id FROM websites WHERE subdomain = ?", [$newSubdomain])->fetch();
        if ($exists) {
            throw new Exception("Subdomain already in use.");
        }

        Database::beginTransaction();
        try {
            // Duplicate website record
            Database::query(
                "INSERT INTO websites (user_id, name, subdomain, category, logo_url, primary_color, secondary_color, font_family, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft')",
                [
                    $userId, $newName, $newSubdomain, $website['category'], 
                    $website['logo_url'], $website['primary_color'], 
                    $website['secondary_color'], $website['font_family']
                ]
            );
            $newWebsiteId = (int)Database::lastInsertId();

            // Duplicate pages
            $pages = Database::query("SELECT * FROM pages WHERE website_id = ?", [$id])->fetchAll();
            foreach ($pages as $page) {
                Database::query(
                    "INSERT INTO pages (website_id, title, slug, meta_title, meta_description, meta_keywords, is_homepage) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $newWebsiteId, $page['title'], $page['slug'], 
                        $page['meta_title'], $page['meta_description'], 
                        $page['meta_keywords'], $page['is_homepage']
                    ]
                );
                $newPageId = Database::lastInsertId();

                // Duplicate sections for this page
                $sections = Database::query("SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order", [$page['id']])->fetchAll();
                foreach ($sections as $section) {
                    Database::query(
                        "INSERT INTO sections (page_id, type, sort_order, content_json) VALUES (?, ?, ?, ?)",
                        [$newPageId, $section['type'], $section['sort_order'], $section['content_json']]
                    );
                }
            }

            Database::commit();
            log_activity($userId, 'duplicate_website', "Duplicated website ID $id as new ID $newWebsiteId.");
            return $newWebsiteId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
