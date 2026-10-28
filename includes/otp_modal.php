<!-- includes/otp_modal.php - Citizen Phone OTP Verification Modal (Bootstrap 5) -->
<div class="modal fade" id="otpVerificationModal" tabindex="-1" aria-labelledby="otpModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header text-white" style="background: #1C1B3A; padding: 18px 24px;">
        <h5 class="modal-title d-flex align-items-center gap-2 mb-0" id="otpModalTitle" style="font-weight: 700; font-size: 1.15rem;">
          <i class="fas fa-shield-alt text-warning"></i> Citizen Mobile Verification (OTP)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- Step 1: Send OTP -->
        <div id="otp-step-1">
          <p class="text-muted small mb-3">
            As per citizen safety guidelines, a 6-digit OTP will be dispatched to your 10-digit mobile number before digital card generation.
          </p>
          <div class="mb-3">
            <label for="otp_citizen_name" class="form-label fw-bold small">Applicant Full Name <span class="text-danger">*</span></label>
            <input type="text" id="otp_citizen_name" class="form-control" placeholder="Enter Full Name" required>
          </div>
          <div class="mb-3">
            <label for="otp_phone_input" class="form-label fw-bold small">10-Digit Mobile Number <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light fw-bold text-secondary">+91</span>
              <input type="tel" id="otp_phone_input" class="form-control" placeholder="98XXXXXXXX" maxlength="10" required>
            </div>
            <small class="text-muted">A 6-digit verification code will be sent via SMS.</small>
          </div>

          <div id="otp-send-alert" class="alert d-none small py-2 mb-3"></div>

          <button type="button" id="btn-request-otp" class="btn btn-warning w-100 py-2 fw-bold text-dark shadow-sm">
            <i class="fas fa-paper-plane me-1"></i> Send Verification OTP
          </button>
        </div>

        <!-- Step 2: Enter & Verify OTP -->
        <div id="otp-step-2" style="display: none;">
          <div class="alert alert-info py-2 px-3 small mb-3">
            <i class="fas fa-info-circle me-1"></i> OTP dispatched to <strong id="otp-target-display">+91-XXXXXXXXXX</strong>.
            <div id="otp-demo-hint" class="mt-1 fw-bold text-primary"></div>
          </div>
          <div class="mb-3 text-center">
            <label for="otp_code_input" class="form-label fw-bold d-block">Enter 6-Digit Verification Code</label>
            <input type="text" id="otp_code_input" class="form-control text-center mx-auto" placeholder="• • • • • •" maxlength="6" style="font-size: 1.6rem; letter-spacing: 8px; font-weight: 800; max-width: 240px;">
            <small class="text-muted d-block mt-2">Valid for 10 minutes. Test OTP: <strong class="text-success">123456</strong> is always accepted in sandbox.</small>
          </div>

          <div id="otp-verify-alert" class="alert d-none small py-2 mb-3"></div>

          <button type="button" id="btn-verify-otp" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
            <i class="fas fa-check-circle me-1"></i> Verify & Proceed to Payment
          </button>

          <div class="text-center mt-3">
            <button type="button" id="btn-resend-otp" class="btn btn-link btn-sm text-decoration-none fw-bold" style="color: #1C1B3A;">
              <i class="fas fa-redo me-1"></i> Resend OTP
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
