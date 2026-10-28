<?php
// scripts/migrate_schemes.php - Create schemes table and populate all 20 schemes into MySQL database
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../data/schemes.php';

try {
    echo "Creating table 'schemes' if not exists...\n";

    $createSql = "
    CREATE TABLE IF NOT EXISTS `schemes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slug` VARCHAR(100) NOT NULL UNIQUE,
        `title_en` VARCHAR(255) NOT NULL,
        `title_hi` VARCHAR(255) NOT NULL,
        `tagline_en` TEXT,
        `tagline_hi` TEXT,
        `icon` VARCHAR(50) DEFAULT 'fa-landmark',
        `image` VARCHAR(255) DEFAULT '',
        `category` VARCHAR(50) NOT NULL,
        `category_name_en` VARCHAR(100),
        `category_name_hi` VARCHAR(100),
        `funding_type` ENUM('central_sector', 'centrally_sponsored') NOT NULL DEFAULT 'central_sector',
        `funding_type_label_en` VARCHAR(150),
        `funding_type_label_hi` VARCHAR(150),
        `ministry_en` VARCHAR(200),
        `ministry_hi` VARCHAR(200),
        `launch_year` VARCHAR(20),
        `budget` VARCHAR(100),
        `applicable_scope` VARCHAR(100) DEFAULT 'all_india',
        `applicable_states` TEXT,
        `overview_en` TEXT,
        `overview_hi` TEXT,
        `benefits_en` LONGTEXT,
        `benefits_hi` LONGTEXT,
        `eligibility_en` LONGTEXT,
        `eligibility_hi` LONGTEXT,
        `documents_en` LONGTEXT,
        `documents_hi` LONGTEXT,
        `how_to_apply_en` LONGTEXT,
        `how_to_apply_hi` LONGTEXT,
        `official_url` VARCHAR(255),
        `helpline` VARCHAR(100),
        `audio_text_en` TEXT,
        `audio_text_hi` TEXT,
        `eligibility_checker` LONGTEXT,
        `min_age` INT DEFAULT 0,
        `max_age` INT DEFAULT 120,
        `max_income` BIGINT DEFAULT 99999999,
        `target_occupations` TEXT,
        `target_gender` VARCHAR(20) DEFAULT 'all',
        `is_active` TINYINT(1) DEFAULT 1,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($createSql);
    echo "Table 'schemes' is ready.\n";

    // Criteria mapping for smart eligibility matching
    $criteriaMap = [
        'pm-kisan' => [
            'min_age' => 18, 'max_age' => 100, 'max_income' => 99999999,
            'target_occupations' => 'farmer,agriculture,all', 'target_gender' => 'all',
            'image' => 'image/schemes/pm-kisan.jpg'
        ],
        'pm-surya-ghar' => [
            'min_age' => 18, 'max_age' => 100, 'max_income' => 99999999,
            'target_occupations' => 'all,business,farmer,unorganized,resident', 'target_gender' => 'all',
            'image' => 'image/schemes/pm-surya-ghar.jpg'
        ],
        'pm-vishwakarma' => [
            'min_age' => 18, 'max_age' => 75, 'max_income' => 99999999,
            'target_occupations' => 'artisan,carpenter,tailor,blacksmith,potter,cobbler,mason,sculptor', 'target_gender' => 'all',
            'image' => 'image/schemes/pm-vishwakarma.jpg'
        ],
        'ayushman-bharat' => [
            'min_age' => 0, 'max_age' => 120, 'max_income' => 500000,
            'target_occupations' => 'all,farmer,artisan,street_vendor,unorganized,laborer,senior', 'target_gender' => 'all',
            'image' => 'image/schemes/ayushman-bharat.jpg'
        ],
        'pmay-gramin' => [
            'min_age' => 18, 'max_age' => 100, 'max_income' => 300000,
            'target_occupations' => 'all,farmer,artisan,unorganized,laborer,homemaker', 'target_gender' => 'all',
            'image' => 'image/schemes/pmay-gramin.jpg'
        ],
        'pm-mudra-yojana' => [
            'min_age' => 18, 'max_age' => 75, 'max_income' => 99999999,
            'target_occupations' => 'business,shopkeeper,artisan,street_vendor,entrepreneur', 'target_gender' => 'all',
            'image' => 'image/schemes/pm-mudra-yojana.jpg'
        ],
        'mgnrega' => [
            'min_age' => 18, 'max_age' => 75, 'max_income' => 300000,
            'target_occupations' => 'unorganized,laborer,farmer,homemaker,rural', 'target_gender' => 'all',
            'image' => 'image/schemes/mgnrega.jpg'
        ],
        'pm-kaushal-vikas' => [
            'min_age' => 15, 'max_age' => 45, 'max_income' => 99999999,
            'target_occupations' => 'student,youth,unemployed,all', 'target_gender' => 'all',
            'image' => 'image/schemes/pm-kaushal-vikas.jpg'
        ],
        'nsap-pension' => [
            'min_age' => 40, 'max_age' => 120, 'max_income' => 200000,
            'target_occupations' => 'senior,widow,disabled,all', 'target_gender' => 'all',
            'image' => 'image/schemes/nsap-pension.jpg'
        ],
        'day-nrlm-aajivika' => [
            'min_age' => 18, 'max_age' => 70, 'max_income' => 500000,
            'target_occupations' => 'homemaker,women,shg,rural,artisan', 'target_gender' => 'female',
            'image' => 'image/schemes/day-nrlm-aajivika.jpg'
        ],
        'pm-janjatiya-gram' => [
            'min_age' => 0, 'max_age' => 120, 'max_income' => 500000,
            'target_occupations' => 'tribal,all,farmer,rural', 'target_gender' => 'all',
            'image' => 'image/schemes/pm-janjatiya-gram.jpg'
        ],
        'mission-indradhanush' => [
            'min_age' => 0, 'max_age' => 50, 'max_income' => 99999999,
            'target_occupations' => 'all,mother,child,family', 'target_gender' => 'female',
            'image' => 'image/schemes/mission-indradhanush.jpg'
        ],
        'pmgsy-roads' => [
            'min_age' => 0, 'max_age' => 120, 'max_income' => 99999999,
            'target_occupations' => 'all,rural,farmer', 'target_gender' => 'all',
            'image' => 'image/schemes/pmgsy-roads.jpg'
        ],
        'atal-pension-yojana' => [
            'min_age' => 18, 'max_age' => 40, 'max_income' => 800000,
            'target_occupations' => 'all,farmer,artisan,street_vendor,unorganized,business', 'target_gender' => 'all',
            'image' => 'image/schemes/atal-pension-yojana.jpg'
        ],
        'pm-svanidhi' => [
            'min_age' => 18, 'max_age' => 75, 'max_income' => 500000,
            'target_occupations' => 'street_vendor,hawker,vendor,small_shop', 'target_gender' => 'all',
            'image' => 'image/schemes/pm-svanidhi.jpg'
        ],
        'pm-matru-vandana' => [
            'min_age' => 18, 'max_age' => 50, 'max_income' => 800000,
            'target_occupations' => 'pregnant,mother,women,homemaker', 'target_gender' => 'female',
            'image' => 'image/schemes/pm-matru-vandana.jpg'
        ],
        'pm-fasal-bima' => [
            'min_age' => 18, 'max_age' => 100, 'max_income' => 99999999,
            'target_occupations' => 'farmer,agriculture', 'target_gender' => 'all',
            'image' => 'image/schemes/pm-fasal-bima.jpg'
        ],
        'jal-jeevan-mission' => [
            'min_age' => 0, 'max_age' => 120, 'max_income' => 99999999,
            'target_occupations' => 'all,rural,family', 'target_gender' => 'all',
            'image' => 'image/schemes/jal-jeevan-mission.jpg'
        ],
        'pm-shram-yogi' => [
            'min_age' => 18, 'max_age' => 40, 'max_income' => 180000,
            'target_occupations' => 'unorganized,laborer,domestic,rickshaw,artisan', 'target_gender' => 'all',
            'image' => 'image/schemes/pm-shram-yogi.jpg'
        ],
        'kisan-credit-card' => [
            'min_age' => 18, 'max_age' => 75, 'max_income' => 99999999,
            'target_occupations' => 'farmer,dairy,fishery,agriculture', 'target_gender' => 'all',
            'image' => 'image/schemes/kisan-credit-card.jpg'
        ]
    ];

    $schemes = get_all_schemes();
    $insertSql = "
    INSERT INTO `schemes` (
        `slug`, `title_en`, `title_hi`, `tagline_en`, `tagline_hi`,
        `icon`, `image`, `category`, `category_name_en`, `category_name_hi`,
        `funding_type`, `funding_type_label_en`, `funding_type_label_hi`,
        `ministry_en`, `ministry_hi`, `launch_year`, `budget`,
        `applicable_scope`, `applicable_states`, `overview_en`, `overview_hi`,
        `benefits_en`, `benefits_hi`, `eligibility_en`, `eligibility_hi`,
        `documents_en`, `documents_hi`, `how_to_apply_en`, `how_to_apply_hi`,
        `official_url`, `helpline`, `audio_text_en`, `audio_text_hi`,
        `eligibility_checker`, `min_age`, `max_age`, `max_income`,
        `target_occupations`, `target_gender`, `is_active`, `sort_order`
    ) VALUES (
        :slug, :title_en, :title_hi, :tagline_en, :tagline_hi,
        :icon, :image, :category, :category_name_en, :category_name_hi,
        :funding_type, :funding_type_label_en, :funding_type_label_hi,
        :ministry_en, :ministry_hi, :launch_year, :budget,
        :applicable_scope, :applicable_states, :overview_en, :overview_hi,
        :benefits_en, :benefits_hi, :eligibility_en, :eligibility_hi,
        :documents_en, :documents_hi, :how_to_apply_en, :how_to_apply_hi,
        :official_url, :helpline, :audio_text_en, :audio_text_hi,
        :eligibility_checker, :min_age, :max_age, :max_income,
        :target_occupations, :target_gender, :is_active, :sort_order
    )
    ON DUPLICATE KEY UPDATE
        `title_en` = VALUES(`title_en`),
        `title_hi` = VALUES(`title_hi`),
        `tagline_en` = VALUES(`tagline_en`),
        `tagline_hi` = VALUES(`tagline_hi`),
        `icon` = VALUES(`icon`),
        `image` = VALUES(`image`),
        `category` = VALUES(`category`),
        `category_name_en` = VALUES(`category_name_en`),
        `category_name_hi` = VALUES(`category_name_hi`),
        `funding_type` = VALUES(`funding_type`),
        `funding_type_label_en` = VALUES(`funding_type_label_en`),
        `funding_type_label_hi` = VALUES(`funding_type_label_hi`),
        `ministry_en` = VALUES(`ministry_en`),
        `ministry_hi` = VALUES(`ministry_hi`),
        `launch_year` = VALUES(`launch_year`),
        `budget` = VALUES(`budget`),
        `applicable_scope` = VALUES(`applicable_scope`),
        `applicable_states` = VALUES(`applicable_states`),
        `overview_en` = VALUES(`overview_en`),
        `overview_hi` = VALUES(`overview_hi`),
        `benefits_en` = VALUES(`benefits_en`),
        `benefits_hi` = VALUES(`benefits_hi`),
        `eligibility_en` = VALUES(`eligibility_en`),
        `eligibility_hi` = VALUES(`eligibility_hi`),
        `documents_en` = VALUES(`documents_en`),
        `documents_hi` = VALUES(`documents_hi`),
        `how_to_apply_en` = VALUES(`how_to_apply_en`),
        `how_to_apply_hi` = VALUES(`how_to_apply_hi`),
        `official_url` = VALUES(`official_url`),
        `helpline` = VALUES(`helpline`),
        `audio_text_en` = VALUES(`audio_text_en`),
        `audio_text_hi` = VALUES(`audio_text_hi`),
        `eligibility_checker` = VALUES(`eligibility_checker`),
        `min_age` = VALUES(`min_age`),
        `max_age` = VALUES(`max_age`),
        `max_income` = VALUES(`max_income`),
        `target_occupations` = VALUES(`target_occupations`),
        `target_gender` = VALUES(`target_gender`),
        `is_active` = VALUES(`is_active`),
        `sort_order` = VALUES(`sort_order`);
    ";

    $stmt = $pdo->prepare($insertSql);
    $order = 1;

    foreach ($schemes as $slug => $s) {
        $meta = $criteriaMap[$slug] ?? [
            'min_age' => 18, 'max_age' => 100, 'max_income' => 99999999,
            'target_occupations' => 'all', 'target_gender' => 'all',
            'image' => 'image/schemes/default-scheme-banner.jpg'
        ];

        // Fallback image if custom image doesn't exist on disk yet
        $imagePath = $meta['image'];
        if (!file_exists(__DIR__ . '/../' . $imagePath)) {
            // Check if generic or banner exists
            $imagePath = 'image/schemes/default-scheme-banner.jpg';
        }

        $stmt->execute([
            ':slug' => $slug,
            ':title_en' => $s['title_en'],
            ':title_hi' => $s['title_hi'],
            ':tagline_en' => $s['tagline_en'],
            ':tagline_hi' => $s['tagline_hi'],
            ':icon' => $s['icon'] ?? 'fa-landmark',
            ':image' => $imagePath,
            ':category' => $s['category'],
            ':category_name_en' => $s['category_name_en'],
            ':category_name_hi' => $s['category_name_hi'],
            ':funding_type' => $s['funding_type'],
            ':funding_type_label_en' => $s['funding_type_label_en'],
            ':funding_type_label_hi' => $s['funding_type_label_hi'],
            ':ministry_en' => $s['ministry_en'],
            ':ministry_hi' => $s['ministry_hi'],
            ':launch_year' => $s['launch_year'] ?? '2020',
            ':budget' => $s['budget'] ?? '',
            ':applicable_scope' => $s['applicable_scope'] ?? 'all_india',
            ':applicable_states' => json_encode($s['applicable_states'], JSON_UNESCAPED_UNICODE),
            ':overview_en' => $s['overview_en'],
            ':overview_hi' => $s['overview_hi'],
            ':benefits_en' => json_encode($s['benefits_en'], JSON_UNESCAPED_UNICODE),
            ':benefits_hi' => json_encode($s['benefits_hi'], JSON_UNESCAPED_UNICODE),
            ':eligibility_en' => json_encode($s['eligibility_en'], JSON_UNESCAPED_UNICODE),
            ':eligibility_hi' => json_encode($s['eligibility_hi'], JSON_UNESCAPED_UNICODE),
            ':documents_en' => json_encode($s['documents_en'], JSON_UNESCAPED_UNICODE),
            ':documents_hi' => json_encode($s['documents_hi'], JSON_UNESCAPED_UNICODE),
            ':how_to_apply_en' => json_encode($s['how_to_apply_en'], JSON_UNESCAPED_UNICODE),
            ':how_to_apply_hi' => json_encode($s['how_to_apply_hi'], JSON_UNESCAPED_UNICODE),
            ':official_url' => $s['official_url'],
            ':helpline' => $s['helpline'],
            ':audio_text_en' => $s['audio_text_en'],
            ':audio_text_hi' => $s['audio_text_hi'],
            ':eligibility_checker' => json_encode($s['eligibility_checker'], JSON_UNESCAPED_UNICODE),
            ':min_age' => $meta['min_age'],
            ':max_age' => $meta['max_age'],
            ':max_income' => $meta['max_income'],
            ':target_occupations' => $meta['target_occupations'],
            ':target_gender' => $meta['target_gender'],
            ':is_active' => 1,
            ':sort_order' => $order++
        ]);
        echo "Imported scheme: {$slug}\n";
    }

    echo "Migration completed successfully! Total 20 schemes populated into database.\n";
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
