<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Check Your Eligibility - ' . APP_NAME;
$metaDescription = 'Answer a few simple questions to discover government schemes that may match your profile.';
$activeNav = 'eligibility';

include __DIR__ . '/includes/header.php';
?>

    <section class="detail-hero">
        <div class="container">
            <div aria-label="breadcrumb">
                <ol class="breadcrumb detail-breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Check Your Eligibility</li>
                </ol>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="detail-kicker"><i class="fas fa-landmark mr-2"></i> SCHEME</span>
                    <h1> Check Your Eligibility</h1>
                    <p>Understand scheme benefits, eligibility conditions, required documents and how to find the
                        official application process.</p>
                </div>
            </div>
        </div>
    </section>

<section class="eligibility-section">
  <div class="eligibility-container">

    <!-- Left Content -->
    <div class="eligibility-info">
      <span class="eligibility-tag">
        <i class="fa-solid fa-clipboard-check"></i>
        CHECK YOUR ELIGIBILITY
      </span>

      <h2>Find Government Schemes You <span>Qualify For</span></h2>

      <p>
        Answer a few simple questions to discover government schemes
        that may match your profile and requirements.
      </p>

      <div class="eligibility-benefits">
        <div class="eligibility-benefit">
          <span class="eligibility-icon">
            <i class="fa-solid fa-user-check"></i>
          </span>
          <div>
            <h4>Personalized Results</h4>
            <p>Explore schemes based on your profile.</p>
          </div>
        </div>

        <div class="eligibility-benefit">
          <span class="eligibility-icon">
            <i class="fa-solid fa-list-check"></i>
          </span>
          <div>
            <h4>Simple Process</h4>
            <p>Complete a few details in minutes.</p>
          </div>
        </div>

        <div class="eligibility-benefit">
          <span class="eligibility-icon">
            <i class="fa-solid fa-shield-halved"></i>
          </span>
          <div>
            <h4>Informed Decisions</h4>
            <p>Understand the next steps for applying.</p>
          </div>
        </div>
      </div>

      <div class="eligibility-note">
        <i class="fa-solid fa-circle-info"></i>
        This checker provides preliminary guidance, not official approval.
      </div>
    </div>

    <!-- Right Form -->
    <div class="eligibility-card">
      <div class="eligibility-card-header">
        <div>
          <span class="eligibility-step">PERSONAL DETAILS</span>
          <h3>Check Your Eligibility</h3>
          <p>Enter your details below to get started.</p>
        </div>
        <div class="eligibility-header-icon">
          <i class="fa-solid fa-file-circle-check"></i>
        </div>
      </div>

      <form id="eligibilityForm">

        <div class="eligibility-field">
          <label for="citizenAge">Your Age <span>*</span></label>
          <div class="eligibility-input-wrap">
            <i class="fa-solid fa-calendar-days"></i>
            <input type="number" id="citizenAge"
              placeholder="Enter your age" min="1" max="120" required>
          </div>
        </div>

        <div class="eligibility-field">
          <label for="citizenState">State / Union Territory <span>*</span></label>
          <div class="eligibility-input-wrap">
            <i class="fa-solid fa-location-dot"></i>
            <select id="citizenState" required>
              <option value="">Select your state</option>
              <option>Andhra Pradesh</option>
              <option>Arunachal Pradesh</option>
              <option>Assam</option>
              <option>Bihar</option>
              <option>Chhattisgarh</option>
              <option>Goa</option>
              <option>Gujarat</option>
              <option>Haryana</option>
              <option>Himachal Pradesh</option>
              <option>Jharkhand</option>
              <option>Karnataka</option>
              <option>Kerala</option>
              <option>Madhya Pradesh</option>
              <option>Maharashtra</option>
              <option>Manipur</option>
              <option>Meghalaya</option>
              <option>Mizoram</option>
              <option>Nagaland</option>
              <option>Odisha</option>
              <option>Punjab</option>
              <option>Rajasthan</option>
              <option>Sikkim</option>
              <option>Tamil Nadu</option>
              <option>Telangana</option>
              <option>Tripura</option>
              <option>Uttar Pradesh</option>
              <option>Uttarakhand</option>
              <option>West Bengal</option>
              <option>Andaman and Nicobar Islands</option>
              <option>Chandigarh</option>
              <option>Dadra and Nagar Haveli and Daman and Diu</option>
              <option>Delhi</option>
              <option>Jammu and Kashmir</option>
              <option>Ladakh</option>
              <option>Lakshadweep</option>
              <option>Puducherry</option>
            </select>
          </div>
        </div>

        <div class="eligibility-field">
          <label for="familyIncome">Annual Family Income <span>*</span></label>
          <div class="eligibility-input-wrap">
            <i class="fa-solid fa-indian-rupee-sign"></i>
            <select id="familyIncome" required>
              <option value="">Select annual income</option>
              <option value="100000">Below ₹1 lakh</option>
              <option value="300000">₹1 lakh – ₹3 lakh</option>
              <option value="500000">₹3 lakh – ₹5 lakh</option>
              <option value="800000">₹5 lakh – ₹8 lakh</option>
              <option value="1000000">Above ₹8 lakh</option>
            </select>
          </div>
        </div>

        <div class="eligibility-field">
          <label for="citizenCategory">Social Category <span>*</span></label>
          <div class="eligibility-input-wrap">
            <i class="fa-solid fa-users"></i>
            <select id="citizenCategory" required>
              <option value="">Select your category</option>
              <option value="General">General</option>
              <option value="OBC">OBC</option>
              <option value="SC">SC</option>
              <option value="ST">ST</option>
              <option value="Other">Other / Prefer not to specify</option>
            </select>
          </div>
        </div>

        <button type="submit" class="eligibility-submit">
          Check Eligibility
          <i class="fa-solid fa-arrow-right"></i>
        </button>

        <p class="eligibility-privacy">
          <i class="fa-solid fa-lock"></i>
          No Aadhaar number or bank details required.
        </p>
      </form>

      <!-- Results -->
      <div id="eligibilityResult" class="eligibility-result"
        role="status" aria-live="polite" hidden>
      </div>
    </div>

  </div>
</section>

<!-- Font Awesome -->
<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<?php include __DIR__ . '/includes/footer.php'; ?>
