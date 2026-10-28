<?php
// admin/contact-settings.php - Manage Global Contact, Company & Social Details
require_once __DIR__ . '/auth.php';

$page_title = 'Contact & Site Settings';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_contact_settings') {
    $settings_to_save = [
        'company_name'     => trim($_POST['company_name'] ?? ''),
        'app_name'         => trim($_POST['app_name'] ?? ''),
        'app_name_hi'      => trim($_POST['app_name_hi'] ?? ''),
        'app_tagline'      => trim($_POST['app_tagline'] ?? ''),
        'helpline_number'  => trim($_POST['helpline_number'] ?? ''),
        'phone_secondary'  => trim($_POST['phone_secondary'] ?? ''),
        'whatsapp_number'  => preg_replace('/[^0-9]/', '', $_POST['whatsapp_number'] ?? ''),
        'support_email'    => trim($_POST['support_email'] ?? ''),
        'office_address'   => trim($_POST['office_address'] ?? ''),
        'operating_hours'  => trim($_POST['operating_hours'] ?? ''),
        'social_facebook'  => trim($_POST['social_facebook'] ?? ''),
        'social_twitter'   => trim($_POST['social_twitter'] ?? ''),
        'social_youtube'   => trim($_POST['social_youtube'] ?? ''),
        'social_instagram' => trim($_POST['social_instagram'] ?? ''),
        'social_linkedin'  => trim($_POST['social_linkedin'] ?? ''),
        'legal_disclaimer' => trim($_POST['legal_disclaimer'] ?? ''),
        'google_map_embed' => trim($_POST['google_map_embed'] ?? '')
    ];

    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value)
                           VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

    foreach ($settings_to_save as $key => $val) {
        $stmt->execute([$key, $val]);
        $site_settings[$key] = $val; // Update in-memory
    }

    set_flash('success', 'All contact details, social links, and portal branding have been updated successfully and are now live across the website!');
    header('Location: ' . BASE_URL . '/admin/contact-settings.php');
    exit;
}

// Fetch current values
$current_settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
if ($stmt) {
    $current_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function field_val($key, $default = '') {
    global $current_settings;
    return htmlspecialchars($current_settings[$key] ?? $default);
}

include __DIR__ . '/header.php';
?>

<style>
  /* Local polish for this settings page only */
  .cs-section-title {
    font-weight: 800;
    color: var(--adm-blue-dark);
    font-size: 1rem;
    margin: 0;
  }
  .cs-section-badge {
    background: #eef2f7;
    color: #475569;
    font-weight: 600;
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 20px;
    white-space: nowrap;
  }
  .cs-label {
    font-weight: 700;
    color: #1e293b;
    font-size: 0.86rem;
    margin-bottom: 6px;
    display: block;
  }
  .cs-hint {
    color: #64748b;
    font-size: 0.78rem;
    display: block;
    margin-top: 4px;
  }
  .cs-required { color: #dc2626; }

  .cs-preview-item {
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 12px;
    border: 1px solid var(--adm-border);
  }
  .cs-preview-item:last-child { margin-bottom: 0; }
  .cs-preview-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #64748b;
    font-weight: 700;
    display: block;
    margin-top: 4px;
  }

  .cs-quicklinks { list-style: none; padding: 0; margin: 0; }
  .cs-quicklinks li { border-bottom: 1px solid var(--adm-border); }
  .cs-quicklinks li:last-child { border-bottom: none; }
  .cs-quicklinks a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 2px;
    color: #334155;
    font-weight: 600;
    font-size: 0.86rem;
    text-decoration: none;
  }
  .cs-quicklinks a:hover { color: var(--adm-saffron-dark); }

  /* Sticky save bar: always reachable, especially on long forms / mobile */
  .cs-save-bar {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: #ffffff;
    border: 1px solid var(--adm-border);
    border-radius: 10px;
    padding: 14px 16px;
    margin-top: 4px;
    margin-bottom: 24px;
    box-shadow: 0 -6px 16px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
  }
  .cs-save-bar .cs-save-hint {
    margin-right: auto;
    color: #64748b;
    font-size: 0.82rem;
  }

  @media (max-width: 575.98px) {
    .cs-save-bar { flex-direction: column-reverse; align-items: stretch; }
    .cs-save-bar .cs-save-hint { margin-right: 0; text-align: center; }
    .cs-save-bar .btn { width: 100%; }
  }
</style>

<!-- Page Title & Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div>
    <h2 class="fw-bold mb-1" style="color: var(--adm-blue-dark); font-size: 1.5rem;">
      <i class="fas fa-address-book text-warning"></i> Contact &amp; Site Settings
    </h2>
    <p class="text-muted mb-0" style="font-size: 0.9rem; max-width: 640px;">
      Manage every public contact channel, helpline, WhatsApp number, address, social links, and legal disclaimer shown across the portal.
    </p>
  </div>
  <a href="<?php echo BASE_URL; ?>/contact.php" target="_blank" class="btn btn-primary fw-bold flex-shrink-0">
    <i class="fas fa-eye"></i> View Public Page
  </a>
</div>

<form method="POST" action="<?php echo BASE_URL; ?>/admin/contact-settings.php" autocomplete="off">
  <input type="hidden" name="action" value="save_contact_settings">

  <div class="row g-4">
    <!-- Left Column: Settings Form Cards -->
    <div class="col-lg-8">

      <!-- 1. Direct Contact Channels & Helpline -->
      <div class="adm-card">
        <div class="adm-card-header" style="border-left: 4px solid var(--adm-saffron);">
          <h4 class="cs-section-title"><i class="fas fa-phone-volume text-warning"></i> Primary Contact Channels</h4>
          <span class="cs-section-badge">Header &amp; Footer</span>
        </div>
        <div class="adm-card-body">
          <div class="row g-3">
            <!-- Helpline Phone -->
            <div class="col-md-6">
              <label for="helpline_number" class="cs-label">
                <i class="fas fa-phone-alt text-danger"></i> Toll-Free / Primary Helpline <span class="cs-required">*</span>
              </label>
              <input type="text" id="helpline_number" name="helpline_number" class="form-control"
                     value="<?php echo field_val('helpline_number', '1800-11-2026'); ?>" placeholder="e.g. 1800-11-2026" required>
              <small class="cs-hint">Shown in the accessibility bar, header callout, and emergency ticker.</small>
            </div>

            <!-- Secondary Mobile Phone -->
            <div class="col-md-6">
              <label for="phone_secondary" class="cs-label">
                <i class="fas fa-mobile-alt text-primary"></i> Direct Support Phone / Mobile
              </label>
              <input type="text" id="phone_secondary" name="phone_secondary" class="form-control"
                     value="<?php echo field_val('phone_secondary', '+91 98765 43210'); ?>" placeholder="e.g. +91 98765 43210">
              <small class="cs-hint">Additional support line for calls or mobile inquiries.</small>
            </div>

            <!-- WhatsApp Number -->
            <div class="col-md-6">
              <label for="whatsapp_number" class="cs-label">
                <i class="fab fa-whatsapp text-success"></i> WhatsApp Business Number
              </label>
              <div class="input-group">
                <span class="input-group-text" style="background: #25d366; color: #ffffff; border-color: #25d366;">
                  <i class="fab fa-whatsapp"></i>
                </span>
                <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control"
                       value="<?php echo field_val('whatsapp_number', '919876543210'); ?>" placeholder="919876543210 (country code + digits only)">
              </div>
              <small class="cs-hint">Powers one-click "Chat on WhatsApp" buttons across the portal.</small>
            </div>

            <!-- Support Email -->
            <div class="col-md-6">
              <label for="support_email" class="cs-label">
                <i class="fas fa-envelope text-info"></i> Support &amp; Grievance Email <span class="cs-required">*</span>
              </label>
              <input type="email" id="support_email" name="support_email" class="form-control"
                     value="<?php echo field_val('support_email', 'support@citizenportal.facilitator.in'); ?>" placeholder="e.g. support@domain.in" required>
              <small class="cs-hint">All contact-form inquiries and citizen escalations route here.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Physical Location & Working Hours -->
      <div class="adm-card">
        <div class="adm-card-header" style="border-left: 4px solid var(--adm-blue);">
          <h4 class="cs-section-title"><i class="fas fa-map-marker-alt text-danger"></i> Office Address &amp; Hours</h4>
          <span class="cs-section-badge">Office Location</span>
        </div>
        <div class="adm-card-body">
          <div class="mb-3">
            <label for="office_address" class="cs-label">
              <i class="fas fa-building text-warning"></i> Physical Office / Facilitation Center Address
            </label>
            <textarea id="office_address" name="office_address" class="form-control" rows="2" placeholder="Building, Street, Landmark, City, State, Pincode"><?php echo field_val('office_address', 'Plot No. 42, Citizen Facilitation Complex, Barakhamba Road, Connaught Place, New Delhi - 110001, India'); ?></textarea>
            <small class="cs-hint">Displayed in the footer and the public Contact Us page.</small>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="operating_hours" class="cs-label">
                <i class="fas fa-clock text-info"></i> Working Days &amp; Helpdesk Hours
              </label>
              <input type="text" id="operating_hours" name="operating_hours" class="form-control"
                     value="<?php echo field_val('operating_hours', 'Mon - Sat: 9:00 AM - 7:00 PM IST (Sunday Closed)'); ?>" placeholder="e.g. Mon - Sat: 9:00 AM - 7:00 PM IST">
            </div>

            <div class="col-md-6">
              <label for="google_map_embed" class="cs-label">
                <i class="fas fa-map-marked-alt text-success"></i> Google Maps Embed URL
              </label>
              <input type="url" id="google_map_embed" name="google_map_embed" class="form-control"
                     value="<?php echo field_val('google_map_embed'); ?>" placeholder="https://www.google.com/maps/embed?...">
              <small class="cs-hint">Renders the interactive map on the Contact Us page.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Portal Branding & Organization Titles -->
      <div class="adm-card">
        <div class="adm-card-header" style="border-left: 4px solid var(--adm-green);">
          <h4 class="cs-section-title"><i class="fas fa-id-card text-success"></i> Portal Branding &amp; Org Names</h4>
          <span class="cs-section-badge">Branding</span>
        </div>
        <div class="adm-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="app_name" class="cs-label">Portal Display Name (English) <span class="cs-required">*</span></label>
              <input type="text" id="app_name" name="app_name" class="form-control"
                     value="<?php echo field_val('app_name', 'Jan Seva Facilitation Portal'); ?>" required>
            </div>

            <div class="col-md-6">
              <label for="app_name_hi" class="cs-label">Portal Hindi Name (हिंदी शीर्षक)</label>
              <input type="text" id="app_name_hi" name="app_name_hi" class="form-control"
                     value="<?php echo field_val('app_name_hi', 'राष्ट्रीय नागरिक सेवा एवं पहचान पत्र सुविधा केंद्र'); ?>">
            </div>

            <div class="col-md-6">
              <label for="company_name" class="cs-label">Legal Operating Entity / Company Name <span class="cs-required">*</span></label>
              <input type="text" id="company_name" name="company_name" class="form-control"
                     value="<?php echo field_val('company_name', 'Jan Seva Digital Services Facilitator LLP'); ?>" required>
              <small class="cs-hint">Appears in legal copyright, citizen receipts, and terms notices.</small>
            </div>

            <div class="col-md-6">
              <label for="app_tagline" class="cs-label">Header Sub-Tagline</label>
              <input type="text" id="app_tagline" name="app_tagline" class="form-control"
                     value="<?php echo field_val('app_tagline', 'Citizen Identity Facilitation & Digital Document Assistance Gateway'); ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Social Media Links -->
      <div class="adm-card">
        <div class="adm-card-header" style="border-left: 4px solid #8b5cf6;">
          <h4 class="cs-section-title"><i class="fas fa-share-alt text-primary"></i> Social Media &amp; Community Channels</h4>
          <span class="cs-section-badge">Social Links</span>
        </div>
        <div class="adm-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="social_facebook" class="cs-label"><i class="fab fa-facebook text-primary"></i> Facebook Page URL</label>
              <input type="url" id="social_facebook" name="social_facebook" class="form-control"
                     value="<?php echo field_val('social_facebook', 'https://facebook.com/'); ?>" placeholder="https://facebook.com/yourpage">
            </div>

            <div class="col-md-6">
              <label for="social_twitter" class="cs-label"><i class="fab fa-twitter text-info"></i> Twitter / X Profile URL</label>
              <input type="url" id="social_twitter" name="social_twitter" class="form-control"
                     value="<?php echo field_val('social_twitter', 'https://twitter.com/'); ?>" placeholder="https://twitter.com/yourhandle">
            </div>

            <div class="col-md-4">
              <label for="social_youtube" class="cs-label"><i class="fab fa-youtube text-danger"></i> YouTube Channel</label>
              <input type="url" id="social_youtube" name="social_youtube" class="form-control"
                     value="<?php echo field_val('social_youtube', 'https://youtube.com/'); ?>" placeholder="https://youtube.com/@channel">
            </div>

            <div class="col-md-4">
              <label for="social_instagram" class="cs-label"><i class="fab fa-instagram text-danger"></i> Instagram URL</label>
              <input type="url" id="social_instagram" name="social_instagram" class="form-control"
                     value="<?php echo field_val('social_instagram', 'https://instagram.com/'); ?>" placeholder="https://instagram.com/profile">
            </div>

            <div class="col-md-4">
              <label for="social_linkedin" class="cs-label"><i class="fab fa-linkedin text-primary"></i> LinkedIn Company Page</label>
              <input type="url" id="social_linkedin" name="social_linkedin" class="form-control"
                     value="<?php echo field_val('social_linkedin', 'https://linkedin.com/'); ?>" placeholder="https://linkedin.com/company/profile">
            </div>
          </div>
        </div>
      </div>

      <!-- 5. Legal Compliance & Facilitation Notice -->
      <div class="adm-card">
        <div class="adm-card-header" style="border-left: 4px solid #f59e0b;">
          <h4 class="cs-section-title"><i class="fas fa-balance-scale text-warning"></i> Mandatory Legal Facilitation Notice</h4>
          <span class="cs-section-badge" style="background: #fff8e1; color: #b45309; border: 1px solid #fcd34d;">Compliance Requirement</span>
        </div>
        <div class="adm-card-body">
          <label for="legal_disclaimer" class="cs-label">Disclaimer Text (shown in header banner &amp; footer alert)</label>
          <textarea id="legal_disclaimer" name="legal_disclaimer" class="form-control" rows="3" required><?php echo field_val('legal_disclaimer'); ?></textarea>
          <small class="cs-hint">
            <strong>Statutory requirement:</strong> must clearly state that this is a private facilitation entity, not affiliated with official government bodies, charging nominal digital assistance fees.
          </small>
        </div>
      </div>

      <!-- Sticky Submit Action Bar -->
      <div class="cs-save-bar">
        <span class="cs-save-hint"><i class="fas fa-info-circle"></i> Changes go live on the public website immediately after saving.</span>
        <a href="<?php echo BASE_URL; ?>/admin/index.php" class="btn btn-default fw-bold">Cancel</a>
        <button type="submit" class="btn btn-success fw-bold px-4">
          <i class="fas fa-save"></i> Save &amp; Publish All Settings
        </button>
      </div>

    </div>

    <!-- Right Column: Live Summary & Quick Actions -->
    <div class="col-lg-4">
      <!-- Live Preview Box -->
      <div class="adm-card">
        <div class="adm-card-header" style="background: var(--adm-blue-dark); color: #ffffff; border-left: none;">
          <h4 class="mb-0" style="color: #ffffff; font-weight: 800; font-size: 1rem;">
            <i class="fas fa-desktop text-warning"></i> Live Citizen Contact Preview
          </h4>
        </div>
        <div class="adm-card-body" style="font-size: 0.88rem;">
          <p class="text-muted mb-3">This is how citizens see your contact touchpoints on the live website:</p>

          <div class="cs-preview-item" style="background: #f8fafc;">
            <div style="font-weight: 700; color: var(--adm-blue-dark); font-size: 1rem;">
              <i class="fas fa-phone-alt text-danger"></i> <?php echo field_val('helpline_number', '1800-11-2026'); ?>
            </div>
            <span class="cs-preview-label">24x7 Citizen Helpline</span>
          </div>

          <div class="cs-preview-item" style="background: #ecfdf5; border-color: #a7f3d0;">
            <div style="font-weight: 700; color: #065f46; font-size: 0.95rem;">
              <i class="fab fa-whatsapp text-success fa-lg"></i> +<?php echo field_val('whatsapp_number', '919876543210'); ?>
            </div>
            <a href="https://wa.me/<?php echo field_val('whatsapp_number', '919876543210'); ?>" target="_blank" class="btn btn-success btn-xs mt-2">
              <i class="fas fa-paper-plane"></i> Test WhatsApp Chat
            </a>
          </div>

          <div class="cs-preview-item" style="background: #f0f9ff; border-color: #bae6fd;">
            <div style="font-weight: 700; color: #0369a1; font-size: 0.92rem; word-break: break-all;">
              <i class="fas fa-envelope text-info"></i> <?php echo field_val('support_email', 'support@citizenportal.facilitator.in'); ?>
            </div>
            <span class="cs-preview-label">Email Support Desk</span>
          </div>

          <div class="cs-preview-item" style="margin-bottom: 0;">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">
              <i class="fas fa-map-marker-alt text-danger"></i> Office
            </div>
            <p style="font-size: 0.82rem; color: #64748b; line-height: 1.4; margin-bottom: 8px;">
              <?php echo field_val('office_address'); ?>
            </p>
            <div style="font-size: 0.78rem; color: #475569;">
              <i class="fas fa-clock text-warning"></i> <?php echo field_val('operating_hours'); ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Test & External Links -->
      <div class="adm-card">
        <div class="adm-card-header">
          <h4 class="cs-section-title"><i class="fas fa-external-link-alt text-primary"></i> Verification Links</h4>
        </div>
        <div class="adm-card-body">
          <ul class="cs-quicklinks">
            <li><a href="<?php echo BASE_URL; ?>/index.php" target="_blank"><i class="fas fa-chevron-right text-warning"></i> View Portal Homepage</a></li>
            <li><a href="<?php echo BASE_URL; ?>/contact.php" target="_blank"><i class="fas fa-chevron-right text-warning"></i> View Contact Us Page</a></li>
            <li><a href="<?php echo BASE_URL; ?>/doorstep-booking.php" target="_blank"><i class="fas fa-chevron-right text-warning"></i> Doorstep Booking Page</a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/messages.php"><i class="fas fa-chevron-right text-warning"></i> Citizen Messages Inbox</a></li>
          </ul>
        </div>
      </div>

    </div>
  </div>
</form>

<?php include __DIR__ . '/footer.php'; ?>
