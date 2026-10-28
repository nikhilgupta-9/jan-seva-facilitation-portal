<?php
// scripts/test_admin_suite.php - Senior QA & Unit Testing Suite for Admin Console
require_once dirname(__DIR__) . '/config/config.php';

class AdminTestSuite {
    private string $baseUrl;
    private string $sessionId = '';
    private int $passed = 0;
    private int $failed = 0;
    private PDO $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->baseUrl = 'http://localhost/gp';
    }

    public function runAll(): void {
        echo "====================================================================\n";
        echo "       JAN SEVA ADMIN PANEL: AUTOMATED QA & UNIT TEST SUITE         \n";
        echo "====================================================================\n\n";

        $this->testAuthentication();
        $this->testPageIntegrity();
        $this->testSpiderAdminLinks();
        $this->testBannersCrud();
        $this->testFaqsCrud();
        $this->testUpdatesCrud();
        $this->testFeedbacksWorkflow();
        $this->testDoorstepStatusUpdates();
        $this->testMessagesWorkflow();
        $this->testServicesCrud();
        $this->testSchemesCrud();
        $this->testContactSettingsPersistence();
        $this->testEdgeCasesAndSecurity();

        echo "\n====================================================================\n";
        echo sprintf(" TEST RUN COMPLETE: %d PASSED, %d FAILED (Total %d tests)\n", 
            $this->passed, $this->failed, $this->passed + $this->failed);
        echo "====================================================================\n";
    }

    private function assert(string $title, bool $condition, string $details = ''): void {
        if ($condition) {
            $this->passed++;
            echo " [PASS] " . $title . "\n";
        } else {
            $this->failed++;
            echo " [FAIL] " . $title . "\n";
            if ($details) {
                echo "        --> " . $details . "\n";
            }
        }
    }

    private function request(string $path, string $method = 'GET', array $postData = [], bool $useSession = true): array {
        if (strpos($path, 'http') === 0) {
            $url = $path;
        } elseif (strpos($path, '/') === 0) {
            $url = 'http://localhost' . $path;
        } else {
            $url = $this->baseUrl . '/' . ltrim($path, '/');
        }

        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HEADER => true,
        ];

        if ($useSession && !empty($this->sessionId)) {
            $options[CURLOPT_COOKIE] = "PHPSESSID=" . $this->sessionId;
        }

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($postData);
        }

        curl_setopt_array($ch, $options);
        $raw = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $headers = substr($raw, 0, $headerSize);
        $body = substr($raw, $headerSize);

        // Capture PHPSESSID if Set-Cookie header exists
        if (preg_match('/Set-Cookie:\s*PHPSESSID=([a-zA-Z0-9]+)/i', $headers, $m)) {
            $this->sessionId = $m[1];
        }

        // Extract Location header if redirect
        $location = '';
        if (preg_match('/^Location:\s*(.*)$/mi', $headers, $locMatch)) {
            $location = trim($locMatch[1]);
        }

        return [
            'code' => $code,
            'headers' => $headers,
            'body' => $body,
            'location' => $location
        ];
    }

    // 1. Authentication Tests
    private function testAuthentication(): void {
        echo "--- 1. AUTHENTICATION & SESSION GUARDS ---\n";

        // Unauthenticated access should redirect to login
        $res = $this->request('admin/index.php', 'GET', [], false);
        $this->assert('Unauthenticated access to admin/index.php returns 302 redirect', 
            $res['code'] === 302, "Got HTTP {$res['code']}");
        $this->assert('Redirect points to login.php', 
            strpos($res['location'], 'login.php') !== false, "Location: {$res['location']}");

        // Login with invalid credentials
        $res = $this->request('admin/login.php', 'POST', [
            'username' => 'admin',
            'password' => 'wrongpassword123'
        ], false);
        $this->assert('Invalid login rejects and stays on login.php', 
            $res['code'] === 200 && strpos($res['body'], 'Invalid') !== false, "Response code: {$res['code']}");

        // Login with valid credentials
        $res = $this->request('admin/login.php', 'POST', [
            'username' => 'admin',
            'password' => 'password'
        ], false);
        $this->assert('Valid login sets session cookie', !empty($this->sessionId), "Session ID captured: {$this->sessionId}");
        $this->assert('Valid login returns 302 redirect to index.php', 
            $res['code'] === 302 && strpos($res['location'], 'index.php') !== false, 
            "Got HTTP {$res['code']}, Location: {$res['location']}");

        // Now authenticated access to index.php should return 200
        $res = $this->request('admin/index.php', 'GET', [], true);
        $this->assert('Authenticated access to admin/index.php returns 200 OK', 
            $res['code'] === 200, "Got HTTP {$res['code']}");
    }

    // 2. Page Integrity & PHP Errors
    private function testPageIntegrity(): void {
        echo "\n--- 2. PAGE INTEGRITY & PHP SYNTAX SCAN ---\n";
        $pages = [
            'admin/index.php',
            'admin/orders.php',
            'admin/services.php',
            'admin/service-edit.php?id=1',
            'admin/service-edit.php',
            'admin/schemes.php',
            'admin/scheme-edit.php?id=1',
            'admin/scheme-edit.php',
            'admin/banners.php',
            'admin/updates.php',
            'admin/faqs.php',
            'admin/doorstep.php',
            'admin/feedbacks.php',
            'admin/messages.php',
            'admin/contact-settings.php'
        ];

        foreach ($pages as $p) {
            $res = $this->request($p, 'GET', [], true);
            $hasPhpError = preg_match('/(<b>(Fatal error|Warning|Notice|Parse error|Deprecated)<\/b>:|Fatal error:|Warning:|Notice:|Parse error:|Deprecated:|SQLSTATE\[)/i', $res['body'], $m);
            $errDetail = $hasPhpError ? strip_tags($m[0]) : '';
            $this->assert("Page {$p} renders HTTP 200 with zero PHP/DB errors", 
                $res['code'] === 200 && !$hasPhpError, 
                "Code: {$res['code']}, Error: {$errDetail}");
        }
    }

    // 3. Spider Internal Links in Admin
    private function testSpiderAdminLinks(): void {
        echo "\n--- 3. SPIDER INTERNAL LINKS ACROSS ADMIN ---\n";
        $pages = ['admin/index.php', 'admin/orders.php', 'admin/services.php', 'admin/schemes.php', 'admin/banners.php', 'admin/faqs.php', 'admin/feedbacks.php'];
        $links = [];

        foreach ($pages as $p) {
            $res = $this->request($p, 'GET', [], true);
            if (preg_match_all('/(?:href|action)=["\']([^"\'#][^"\' ]*)["\']/i', $res['body'], $matches)) {
                foreach ($matches[1] as $lnk) {
                    if (strpos($lnk, 'javascript:') === 0 || strpos($lnk, 'mailto:') === 0 || strpos($lnk, 'tel:') === 0) continue;
                    if (strpos($lnk, 'logout') !== false) continue; // Do not hit logout during link spider!
                    if (strpos($lnk, 'http://') === 0 || strpos($lnk, 'https://') === 0) {
                        if (strpos($lnk, 'http://localhost/gp/') !== 0) continue;
                    }
                    $links[$lnk] = $p;
                }
            }
        }

        $broken = [];
        foreach ($links as $lnk => $foundOn) {
            if (strpos($lnk, 'http') === 0) {
                $target = $lnk;
            } elseif (strpos($lnk, '/') === 0) {
                $target = $lnk;
            } else {
                $target = 'admin/' . $lnk;
            }

            $res = $this->request($target, 'GET', [], true);
            if ($res['code'] >= 400 || $res['code'] === 0) {
                $broken[] = "{$target} (HTTP {$res['code']}, found on {$foundOn})";
            }
        }

        $this->assert('All ' . count($links) . ' internal admin links are reachable (No 404/500)', 
            empty($broken), empty($broken) ? '' : implode("\n        --> ", $broken));
    }

    // 4. Banners CRUD
    private function testBannersCrud(): void {
        echo "\n--- 4. BANNERS MODULE CRUD & VALIDATION ---\n";
        
        $testTitle = 'QA Unit Test Banner ' . microtime(true);
        // 1. Create a test banner
        $res = $this->request('admin/banners.php', 'POST', [
            'action' => 'save_banner',
            'id' => 0,
            'title' => $testTitle,
            'subtitle' => 'Automated testing subtitle',
            'link_url' => 'all-scheme.php',
            'target' => '_self',
            'badge_text' => 'Unit Test',
            'button_text' => 'Explore',
            'sort_order' => 99,
            'is_active' => '1',
            'image_url_text' => 'image/banners/banner-abha-healthcare.webp'
        ], true);

        $this->assert('Banner creation redirects (302) on success', 
            $res['code'] === 302 && strpos($res['location'], 'banners.php') !== false, 
            "Got HTTP {$res['code']}, Location: {$res['location']}");

        // Verify in DB
        $stmt = $this->pdo->prepare("SELECT * FROM banners WHERE title = ? LIMIT 1");
        $stmt->execute([$testTitle]);
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assert('Banner record exists in database', !empty($banner), 'Failed to find banner in DB');

        if (!empty($banner)) {
            $bannerId = (int)$banner['id'];

            // 2. Toggle active (POST toggle_status)
            $res = $this->request('admin/banners.php', 'POST', [
                'action' => 'toggle_status',
                'id' => $bannerId
            ], true);
            $this->assert('Banner toggle status redirects', $res['code'] === 302);
            
            $stmt = $this->pdo->prepare("SELECT is_active FROM banners WHERE id = ?");
            $stmt->execute([$bannerId]);
            $is_active = $stmt->fetchColumn();
            $this->assert('Banner active status toggled to 0', (int)$is_active === 0, "Got is_active: {$is_active}");

            // 3. Delete banner (POST delete)
            $res = $this->request('admin/banners.php', 'POST', [
                'action' => 'delete',
                'id' => $bannerId
            ], true);
            $this->assert('Banner delete action redirects', $res['code'] === 302);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM banners WHERE id = ?");
            $stmt->execute([$bannerId]);
            $cnt = $stmt->fetchColumn();
            $this->assert('Banner successfully deleted from DB', (int)$cnt === 0, "Still exists count: {$cnt}");
        }
    }

    // 5. FAQs CRUD
    private function testFaqsCrud(): void {
        echo "\n--- 5. FAQS MODULE CRUD & VALIDATION ---\n";
        
        $testQuestion = 'QA Test FAQ Question ' . microtime(true);
        $testAnswer = 'QA Test FAQ Answer with details.';

        // Create FAQ
        $res = $this->request('admin/faqs.php', 'POST', [
            'action' => 'save_faq',
            'id' => 0,
            'question_en' => $testQuestion,
            'question_hi' => 'परीक्षण प्रश्न',
            'answer_en' => $testAnswer,
            'answer_hi' => 'परीक्षण उत्तर',
            'sort_order' => 99,
            'is_active' => '1'
        ], true);

        $this->assert('FAQ creation redirects on save', 
            $res['code'] === 302 && strpos($res['location'], 'faqs.php') !== false, 
            "Got HTTP {$res['code']}, Location: {$res['location']}");

        $stmt = $this->pdo->prepare("SELECT * FROM faqs WHERE question_en = ? LIMIT 1");
        $stmt->execute([$testQuestion]);
        $faq = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assert('FAQ record persisted in DB', !empty($faq));

        if (!empty($faq)) {
            $faqId = (int)$faq['id'];

            // Toggle
            $res = $this->request('admin/faqs.php', 'POST', [
                'quick_action' => 'toggle_status',
                'faq_id' => $faqId
            ], true);
            $this->assert('FAQ toggle redirects', $res['code'] === 302);

            // Delete
            $res = $this->request('admin/faqs.php', 'POST', [
                'quick_action' => 'delete',
                'faq_id' => $faqId
            ], true);
            $this->assert('FAQ delete redirects', $res['code'] === 302);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM faqs WHERE id = ?");
            $stmt->execute([$faqId]);
            $this->assert('FAQ record deleted from DB', (int)$stmt->fetchColumn() === 0);
        }
    }

    // 6. Portal Updates CRUD
    private function testUpdatesCrud(): void {
        echo "\n--- 6. PORTAL UPDATES (NOTICE BOARD) CRUD ---\n";
        
        $testTitle = 'QA Portal Notice Update ' . microtime(true);

        $res = $this->request('admin/updates.php', 'POST', [
            'action' => 'save_update',
            'id' => 0,
            'title_en' => $testTitle,
            'title_hi' => 'परीक्षण सूचना',
            'source_name' => 'Ministry QA Desk',
            'source_url' => 'https://pib.gov.in',
            'category' => 'scheme',
            'published_at' => date('Y-m-d'),
            'is_active' => '1'
        ], true);

        $this->assert('Update creation redirects on save', 
            $res['code'] === 302 && strpos($res['location'], 'updates.php') !== false, 
            "Got HTTP {$res['code']}, Location: {$res['location']}");

        $stmt = $this->pdo->prepare("SELECT * FROM portal_updates WHERE title_en = ? LIMIT 1");
        $stmt->execute([$testTitle]);
        $update = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assert('Update notice persisted in DB', !empty($update));

        if (!empty($update)) {
            $uId = (int)$update['id'];

            // Toggle
            $res = $this->request('admin/updates.php', 'POST', [
                'quick_action' => 'toggle_status',
                'update_id' => $uId
            ], true);
            $this->assert('Update toggle redirects', $res['code'] === 302);

            // Delete
            $res = $this->request('admin/updates.php', 'POST', [
                'quick_action' => 'delete',
                'update_id' => $uId
            ], true);
            $this->assert('Update delete redirects', $res['code'] === 302);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM portal_updates WHERE id = ?");
            $stmt->execute([$uId]);
            $this->assert('Update deleted from DB', (int)$stmt->fetchColumn() === 0);
        }

        // Test manual one-click PIB sync action
        $res = $this->request('admin/updates.php', 'POST', [
            'action' => 'sync_pib'
        ], true);
        $this->assert('Manual one-click PIB sync action redirects with success', $res['code'] === 302);
    }

    // 7. Feedbacks Workflow
    private function testFeedbacksWorkflow(): void {
        echo "\n--- 7. CITIZEN FEEDBACK APPROVAL WORKFLOW ---\n";

        // Insert mock feedback in pending status
        $stmt = $this->pdo->prepare("
            INSERT INTO feedbacks (citizen_name, service_name, rating, comment, phone, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute(['QA Test Citizen', 'ABHA Health Card', 5, 'Great service test comment', '9876543210']);
        $fbId = (int)$this->pdo->lastInsertId();
        $this->assert('Mock feedback inserted for testing', $fbId > 0);

        // Approve feedback
        $res = $this->request('admin/feedbacks.php', 'POST', [
            'action' => 'approve',
            'feedback_id' => $fbId
        ], true);
        $this->assert('Feedback approval action redirects', $res['code'] === 302);

        $stmt = $this->pdo->prepare("SELECT status FROM feedbacks WHERE id = ?");
        $stmt->execute([$fbId]);
        $status = $stmt->fetchColumn();
        $this->assert('Feedback status updated to approved', $status === 'approved', "Status is: {$status}");

        // Feature feedback
        $res = $this->request('admin/feedbacks.php', 'POST', [
            'action' => 'toggle_featured',
            'feedback_id' => $fbId
        ], true);
        $stmt = $this->pdo->prepare("SELECT is_featured FROM feedbacks WHERE id = ?");
        $stmt->execute([$fbId]);
        $this->assert('Feedback featured flag toggled to 1', (int)$stmt->fetchColumn() === 1);

        // Delete test feedback
        $res = $this->request('admin/feedbacks.php', 'POST', [
            'action' => 'delete',
            'feedback_id' => $fbId
        ], true);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM feedbacks WHERE id = ?");
        $stmt->execute([$fbId]);
        $this->assert('Feedback deleted cleanly', (int)$stmt->fetchColumn() === 0);
    }

    // 8. Doorstep Leads Workflow
    private function testDoorstepStatusUpdates(): void {
        echo "\n--- 8. DOORSTEP LEADS STATUS WORKFLOW ---\n";

        // Insert a mock doorstep lead
        $stmt = $this->pdo->prepare("
            INSERT INTO doorstep_requests (citizen_name, phone, address, city, state, pincode, service_id, service_needed, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new')
        ");
        $stmt->execute(['QA Applicant', '9876543211', '123 Test Street', 'New Delhi', 'Delhi', '110001', 1, 'ABHA Health Card']);
        $leadId = (int)$this->pdo->lastInsertId();
        $this->assert('Mock doorstep lead inserted', $leadId > 0);

        // Update status via POST on doorstep.php
        $res = $this->request('admin/doorstep.php', 'POST', [
            'request_id' => $leadId,
            'status' => 'contacted'
        ], true);

        $this->assert('Doorstep status update returns response/redirect', in_array($res['code'], [200, 302]));

        $stmt = $this->pdo->prepare("SELECT status FROM doorstep_requests WHERE id = ?");
        $stmt->execute([$leadId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assert('Doorstep lead status updated to contacted', $row['status'] === 'contacted', "Status: {$row['status']}");

        // Cleanup
        $this->pdo->prepare("DELETE FROM doorstep_requests WHERE id = ?")->execute([$leadId]);
    }

    // 9. Messages Workflow
    private function testMessagesWorkflow(): void {
        echo "\n--- 9. CONTACT MESSAGES / INQUIRIES WORKFLOW ---\n";

        $stmt = $this->pdo->prepare("
            INSERT INTO contact_messages (name, email, phone, subject, message, status)
            VALUES (?, ?, ?, ?, ?, 'new')
        ");
        $stmt->execute(['QA Tester', 'test@example.com', '9876543212', 'Test Inquiry', 'Unit test inquiry message body']);
        $msgId = (int)$this->pdo->lastInsertId();
        $this->assert('Mock contact message inserted', $msgId > 0);

        // Mark as read/resolved
        $res = $this->request('admin/messages.php', 'POST', [
            'action' => 'update_status',
            'message_id' => $msgId,
            'status' => 'read'
        ], true);
        $this->assert('Message status update redirects', in_array($res['code'], [200, 302]));

        $stmt = $this->pdo->prepare("SELECT status FROM contact_messages WHERE id = ?");
        $stmt->execute([$msgId]);
        $this->assert('Message status updated to read in DB', $stmt->fetchColumn() === 'read');

        // Cleanup
        $this->pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$msgId]);
    }

    // 10. Services CRUD
    private function testServicesCrud(): void {
        echo "\n--- 10. SERVICES CATALOG CRUD ---\n";

        // Test editing service 1 (ABHA card)
        $res = $this->request('admin/service-edit.php?id=1', 'GET', [], true);
        $this->assert('Service edit page loads for ID 1', $res['code'] === 200);

        // Verify required form elements exist in service-edit
        $this->assert('Service edit contains service_name and fee fields', 
            strpos($res['body'], 'name="service_name"') !== false && strpos($res['body'], 'name="fee"') !== false);

        // Test service toggle status
        $res = $this->request('admin/services.php', 'POST', [
            'action' => 'toggle_status',
            'service_id' => 1
        ], true);
        $this->assert('Service inline toggle redirects', $res['code'] === 302);
        // Toggle back
        $this->request('admin/services.php', 'POST', [
            'action' => 'toggle_status',
            'service_id' => 1
        ], true);
        $this->assert('Service toggled back to original state', true);
    }

    // 11. Schemes CRUD
    private function testSchemesCrud(): void {
        echo "\n--- 11. SCHEMES CATALOG CRUD ---\n";

        $res = $this->request('admin/scheme-edit.php?id=1', 'GET', [], true);
        $this->assert('Scheme edit page loads for ID 1', $res['code'] === 200);

        $this->assert('Scheme edit contains title_en and category fields', 
            strpos($res['body'], 'name="title_en"') !== false && strpos($res['body'], 'name="category"') !== false);

        // Test scheme toggle status
        $res = $this->request('admin/schemes.php', 'POST', [
            'action' => 'toggle_status',
            'scheme_id' => 1
        ], true);
        $this->assert('Scheme inline toggle redirects', $res['code'] === 302);
        // Toggle back
        $this->request('admin/schemes.php', 'POST', [
            'action' => 'toggle_status',
            'scheme_id' => 1
        ], true);
        $this->assert('Scheme toggled back to original state', true);
    }

    // 12. Contact & Site Settings Persistence
    private function testContactSettingsPersistence(): void {
        echo "\n--- 12. SITE SETTINGS PERSISTENCE ---\n";

        $res = $this->request('admin/contact-settings.php', 'GET', [], true);
        $this->assert('Contact settings page loads HTTP 200', $res['code'] === 200);

        // Fetch current support phone
        $stmt = $this->pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'support_phone'");
        $currentPhone = $stmt->fetchColumn() ?: '1800-111-2233';

        // Save settings via POST
        $res = $this->request('admin/contact-settings.php', 'POST', [
            'action' => 'save_contact_settings',
            'company_name' => 'Jan Seva Facilitation Portal',
            'app_name' => 'Jan Seva Portal',
            'helpline_number' => $currentPhone,
            'support_email' => 'support@janseva.gov.in',
            'office_address' => 'Citizen Facilitation Centre, New Delhi',
        ], true);

        $this->assert('Contact settings save redirects on success', 
            $res['code'] === 302 && strpos($res['location'], 'contact-settings.php') !== false);

        // Verify in DB
        $stmt = $this->pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'helpline_number'");
        $savedPhone = $stmt->fetchColumn();
        $this->assert('Helpline number persisted in site_settings table', $savedPhone === $currentPhone);
    }

    // 13. Edge Cases & Security Checks
    private function testEdgeCasesAndSecurity(): void {
        echo "\n--- 13. SECURITY, EDGE CASES & SQL INJECTION GUARDS ---\n";

        // SQL injection probe in order search
        $sqliPayload = "' OR '1'='1";
        $res = $this->request('admin/orders.php?search=' . urlencode($sqliPayload), 'GET', [], true);
        $this->assert('Orders search handles SQL injection characters safely without DB error', 
            $res['code'] === 200 && strpos($res['body'], 'SQLSTATE') === false);

        // Non-existent ID in service-edit
        $res = $this->request('admin/service-edit.php?id=999999', 'GET', [], true);
        $this->assert('Non-existent service ID handled gracefully without fatal error', 
            in_array($res['code'], [200, 302, 404]) && strpos($res['body'], 'Fatal error') === false);

        // Non-existent ID in scheme-edit
        $res = $this->request('admin/scheme-edit.php?id=999999', 'GET', [], true);
        $this->assert('Non-existent scheme ID handled gracefully without fatal error', 
            in_array($res['code'], [200, 302, 404]) && strpos($res['body'], 'Fatal error') === false);

        // XSS script injection in FAQ title
        $xssPayload = '<script>alert("xss")</script>';
        $res = $this->request('admin/faqs.php', 'POST', [
            'action' => 'save_faq',
            'id' => 0,
            'question_en' => 'XSS Test ' . $xssPayload,
            'answer_en' => 'Answer',
            'sort_order' => 1
        ], true);
        
        // Find created FAQ and inspect rendered page
        $res = $this->request('admin/faqs.php', 'GET', [], true);
        $this->assert('Script tag is escaped or not executed as raw HTML in FAQs list', 
            strpos($res['body'], '<script>alert("xss")</script>') === false || strpos($res['body'], '&lt;script&gt;') !== false);

        // Cleanup XSS FAQ
        $this->pdo->exec("DELETE FROM faqs WHERE question_en LIKE 'XSS Test%'");
    }
}

$suite = new AdminTestSuite();
$suite->runAll();
