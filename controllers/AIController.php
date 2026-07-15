<?php
/**
 * AIController Class
 * Heuristics-based local AI page generation engine.
 * Assembles entire copy, services lists, FAQs, and layout blocks based on business profile prompts.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../config/database.php';

class AIController extends Controller {

    /**
     * Local Heuristics generator mapping categories to custom structural layouts and industry copy.
     */
    public static function generateWebsite(int $userId, array $params): int {
        $businessName = trim($params['business_name'] ?? 'My Business');
        $category = trim($params['category'] ?? 'Business');
        $phone = trim($params['phone'] ?? '+1 (500) 123-4567');
        $email = trim($params['email'] ?? 'hello@mycompany.com');
        $address = trim($params['address'] ?? '123 Enterprise Way, NY');
        $websiteName = trim($params['website_name'] ?? 'My Website');
        $description = trim($params['description'] ?? 'High quality professional services.');
        $primaryColor = trim($params['primary_color'] ?? '#0d6efd');
        $secondaryColor = trim($params['secondary_color'] ?? '#6c757d');
        $logoUrl = trim($params['logo_url'] ?? '');
        
        // Formulate safe subdomain slug
        $subdomain = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $websiteName)));
        if (empty($subdomain)) {
            $subdomain = 'site-' . rand(1000, 9999);
        }
        
        // Ensure subdomain is unique
        $exists = Database::query("SELECT id FROM websites WHERE subdomain = ?", [$subdomain])->fetch();
        if ($exists) {
            $subdomain .= '-' . rand(100, 999);
        }

        // Generate tailored SEO meta records
        $seoTitle = "$businessName - Premium $category Services";
        $seoDesc = "$businessName is a premier provider of $category. " . substr($description, 0, 120);
        $keywords = "$category, $businessName, local $category, professional services";

        // Category-specific variations
        $services = [];
        $features = [];
        $testimonials = [];
        $faqs = [];

        switch (strtolower($category)) {
            case 'restaurant':
            case 'food':
                $services = [
                    ['icon' => 'bi-egg-fried', 'title' => 'Gourmet Dining', 'desc' => 'Exquisite recipes prepared by multi-award winning culinary chefs.'],
                    ['icon' => 'bi-truck', 'title' => 'Express Delivery', 'desc' => 'Warm, fresh meals delivered straight to your home or office in minutes.'],
                    ['icon' => 'bi-award', 'title' => 'Private Catering', 'desc' => 'Make your personal events and weddings memorable with tailored menus.']
                ];
                $features = [
                    ['icon' => 'bi-heart', 'title' => 'Fresh Ingredients', 'desc' => 'We source directly from local organic farms every morning.'],
                    ['icon' => 'bi-shield-check', 'title' => 'Hygiene First', 'desc' => 'Strict quality and temperature controls across all cooking processes.']
                ];
                $testimonials = [
                    ['client' => 'Sarah Connor', 'quote' => 'The absolute best taste in town! The steak is incredibly tender, and customer service is outstanding.'],
                    ['client' => 'Michael Scott', 'quote' => 'Loved the cozy atmosphere and the fresh warm rolls. Our group had a wonderful time. Highly recommend!']
                ];
                $faqs = [
                    ['q' => 'Do you support vegan or gluten-free diets?', 'a' => 'Yes! We have dedicated menus for both vegan and gluten-sensitive customers.'],
                    ['q' => 'What are your working hours?', 'a' => 'We are open Monday to Sunday, from 11:00 AM to 11:00 PM.']
                ];
                break;

            case 'gym':
            case 'fitness':
                $services = [
                    ['icon' => 'bi-activity', 'title' => 'Personal Coaching', 'desc' => 'Customized nutrition plans and high-performance physical workouts.'],
                    ['icon' => 'bi-people', 'title' => 'Group Classes', 'desc' => 'Fun and high energy spinning, yoga, and crossfit community sessions.'],
                    ['icon' => 'bi-droplet', 'title' => 'Sauna & Recovery', 'desc' => 'Complete relaxation chambers, ice baths, and massage therapist appointments.']
                ];
                $features = [
                    ['icon' => 'bi-trophy', 'title' => 'Pro Trainers', 'desc' => 'Certified trainers with decades of collective sports guidance.'],
                    ['icon' => 'bi-clock', 'title' => '24/7 Access', 'desc' => 'Enter the facility anytime with your secure smart keycard.']
                ];
                $testimonials = [
                    ['client' => 'David Goggins', 'quote' => 'Excellent equipments, clean space, and super supportive environment. Changed my workout routine for the better!'],
                    ['client' => 'Emily Watson', 'quote' => 'The group yoga classes are amazing. Instructors are friendly and very helpful with postures.']
                ];
                $faqs = [
                    ['q' => 'Do you provide gym trial sessions?', 'a' => 'Absolutely. Sign up today and get your first 3 sessions completely free of cost.'],
                    ['q' => 'Is parking space available?', 'a' => 'Yes, we have free secure parking slots for members directly in front of the building.']
                ];
                break;

            case 'agency':
            case 'consultant':
            case 'business':
            default:
                $services = [
                    ['icon' => 'bi-graph-up-arrow', 'title' => 'Growth Strategy', 'desc' => 'We analyze market positions and scale your operational profits.'],
                    ['icon' => 'bi-laptop', 'title' => 'Digital Architecture', 'desc' => 'High quality websites, mobile apps, and data infrastructure solutions.'],
                    ['icon' => 'bi-shield-check', 'title' => 'Risk Operations', 'desc' => 'Secure reviews of legal, compliance, and corporate transaction metrics.']
                ];
                $features = [
                    ['icon' => 'bi-lightbulb', 'title' => 'Tailored Analytics', 'desc' => 'Actionable insights based on your exact customer profile data.'],
                    ['icon' => 'bi-chat-dots', 'title' => 'Constant Support', 'desc' => 'A dedicated account manager to assist your business progress.']
                ];
                $testimonials = [
                    ['client' => 'Bruce Wayne', 'quote' => 'They restructured our operational workflow completely. The ROI was clear within the first fiscal quarter.'],
                    ['client' => 'Tony Stark', 'quote' => 'Smart, fast, and fully transparent. The consulting team delivered exactly what they promised. A+ rank.']
                ];
                $faqs = [
                    ['q' => 'How do we kick off a consultation?', 'a' => 'Fill in the contact form or schedule a call, and our managers will contact you within 24 hours.'],
                    ['q' => 'What sizes of businesses do you support?', 'a' => 'We support rising early-stage startups as well as established Fortune-500 giants.']
                ];
                break;
        }

        // DB Transaction
        Database::beginTransaction();
        try {
            // Insert website details
            Database::query(
                "INSERT INTO websites (user_id, name, subdomain, category, logo_url, primary_color, secondary_color, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'published')",
                [$userId, $websiteName, $subdomain, $category, $logoUrl, $primaryColor, $secondaryColor]
            );
            $websiteId = (int)Database::lastInsertId();

            // Insert Homepage page
            Database::query(
                "INSERT INTO pages (website_id, title, slug, meta_title, meta_description, meta_keywords, is_homepage) 
                 VALUES (?, 'Home', 'home', ?, ?, ?, 1)",
                [$websiteId, $seoTitle, $seoDesc, $keywords]
            );
            $pageId = (int)Database::lastInsertId();

            // Structure Dynamic Sections
            $layout = [
                [
                    'type' => 'navbar',
                    'content' => [
                        'brand' => $businessName,
                        'links' => [
                            ['text' => 'Home', 'url' => '#home'],
                            ['text' => 'About', 'url' => '#about'],
                            ['text' => 'Services', 'url' => '#services'],
                            ['text' => 'Testimonials', 'url' => '#testimonials'],
                            ['text' => 'Contact', 'url' => '#contact']
                        ],
                        'btn_text' => 'Call Now',
                        'btn_url' => "tel:$phone"
                    ]
                ],
                [
                    'type' => 'hero',
                    'content' => [
                        'title' => "Empowering " . $businessName . " Forward",
                        'subtitle' => $description,
                        'btn_primary' => "Our Services",
                        'btn_secondary' => "Book Consultation",
                        'bg_color' => "linear-gradient(135deg, $primaryColor 0%, $secondaryColor 100%)"
                    ]
                ],
                [
                    'type' => 'about',
                    'content' => [
                        'title' => "Who We Are",
                        'desc' => "At " . $businessName . ", we lead the industry in " . $category . " operations. Based out of " . $address . ", our goal is to deliver top value to all our clients. " . $description
                    ]
                ],
                [
                    'type' => 'services',
                    'content' => [
                        'title' => "Our Professional Services",
                        'items' => $services
                    ]
                ],
                [
                    'type' => 'features',
                    'content' => [
                        'title' => "What Sets Us Apart",
                        'items' => $features
                    ]
                ],
                [
                    'type' => 'testimonials',
                    'content' => [
                        'title' => "Client Reviews",
                        'items' => $testimonials
                    ]
                ],
                [
                    'type' => 'faq',
                    'content' => [
                        'title' => "Frequently Asked Questions",
                        'items' => $faqs
                    ]
                ],
                [
                    'type' => 'contact',
                    'content' => [
                        'title' => "Connect with Us Today",
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $address
                    ]
                ],
                [
                    'type' => 'footer',
                    'content' => [
                        'copyright' => "© " . date('Y') . " " . $businessName . ". All rights reserved."
                    ]
                ]
            ];

            // Insert each section in sequential order
            $order = 0;
            foreach ($layout as $sect) {
                Database::query(
                    "INSERT INTO sections (page_id, type, sort_order, content_json) VALUES (?, ?, ?, ?)",
                    [$pageId, $sect['type'], $order++, json_encode($sect['content'])]
                );
            }

            // Create Contact Form link
            Database::query(
                "INSERT INTO forms (website_id, name, email_recipient) VALUES (?, 'Contact Form', ?)",
                [$websiteId, $email]
            );

            Database::commit();
            log_activity($userId, 'ai_generate', "Generated AI website ID $websiteId for $businessName.");
            return $websiteId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
