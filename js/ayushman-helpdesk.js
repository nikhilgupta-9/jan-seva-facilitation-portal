/**
 * js/ayushman-helpdesk.js
 * Ayushman Bharat Help Desk & Digital Health Smart ID
 * Modular JavaScript: Smooth AJAX Submission, Client Validation, Live Previews & Language Switcher
 */

(function () {
    'use strict';

    // 1. DOM Ready Initialization
    document.addEventListener('DOMContentLoaded', function () {
        initLanguageSwitcher();
        initAadhaarFormatter();
        initMobileSanitizer();
        initDocumentTiles();
        initAjaxFormSubmission();
    });

    // 2. Bilingual Language Switcher (EN <-> HI)
    function initLanguageSwitcher() {
        const buttons = document.querySelectorAll('.ayush-lang-switch .lang-toggle-btn');
        if (!buttons.length) return;

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const lang = this.getAttribute('data-lang-val');
                buttons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                setLanguage(lang);
            });
        });
    }

    window.setLanguage = function (lang) {
        document.documentElement.setAttribute('lang', lang);

        // Update text nodes with data-hi and data-en
        const translatable = document.querySelectorAll('[data-en][data-hi]');
        translatable.forEach(function (el) {
            const val = el.getAttribute('data-' + lang);
            if (val) {
                el.innerHTML = val;
            }
        });

        // Update inputs with data-hi-placeholder and data-en-placeholder
        const placeholders = document.querySelectorAll('[data-en-placeholder][data-hi-placeholder]');
        placeholders.forEach(function (input) {
            const ph = input.getAttribute('data-' + lang + '-placeholder');
            if (ph) {
                input.setAttribute('placeholder', ph);
            }
        });
    };

    // 3. Collapsible Quick Guide Toggle
    window.toggleAyushGuide = function () {
        const body = document.getElementById('ayush-guide-body');
        const icon = document.getElementById('guide-toggle-icon');
        const txt = document.getElementById('guide-toggle-txt');
        if (!body) return;

        const isHidden = (body.style.display === 'none' || !body.style.display);
        body.style.display = isHidden ? 'block' : 'none';

        if (icon) {
            icon.className = isHidden ? 'fas fa-chevron-up ms-1' : 'fas fa-chevron-down ms-1';
        }
        if (txt) {
            const isHi = (document.documentElement.getAttribute('lang') === 'hi');
            txt.innerText = isHidden ? (isHi ? 'विवरण छिपाएं' : 'Hide Details') : (isHi ? 'विवरण देखें' : 'View Details');
        }
    };

    // 4. Aadhaar Auto-Spacing Formatter (XXXX XXXX XXXX)
    function initAadhaarFormatter() {
        const aadhaarInput = document.getElementById('aadhaar_input');
        if (!aadhaarInput) return;

        aadhaarInput.addEventListener('input', function () {
            let val = this.value.replace(/\D/g, '');
            if (val.length > 12) {
                val = val.substring(0, 12);
            }
            const parts = [];
            for (let i = 0; i < val.length; i += 4) {
                parts.push(val.substring(i, i + 4));
            }
            this.value = parts.join(' ');
        });
    }

    // 5. Mobile Number Sanitizer (10 Digits)
    function initMobileSanitizer() {
        const mobileInput = document.getElementById('mobile');
        if (!mobileInput) return;

        mobileInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').substring(0, 10);
        });
    }

    // 6. Live Photo Preview
    window.previewCitizenPhoto = function (input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        if (file.size > 10 * 1024 * 1024) {
            alert('फोटो का आकार 10MB से अधिक नहीं होना चाहिए (Photo must be under 10MB)!');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById('photo-img-preview');
            const wrap = document.getElementById('photo-preview-wrap');
            const nameEl = document.getElementById('photo-file-name');
            const tile = document.getElementById('photo-tile');

            if (img) {
                img.src = e.target.result;
                img.style.display = 'block';
            }
            if (wrap) wrap.style.display = 'none';
            if (nameEl) {
                nameEl.innerText = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                nameEl.style.display = 'inline-block';
            }
            if (tile) tile.classList.add('has-file');
        };
        reader.readAsDataURL(file);
    };

    // 7. Live Camera Capture Integration
    window.captureCitizenPhotoFromCamera = function (cameraInput) {
        if (!cameraInput.files || !cameraInput.files[0]) return;
        const file = cameraInput.files[0];
        const mainInput = document.getElementById('photo_input');

        if (mainInput && window.DataTransfer) {
            const dt = new DataTransfer();
            dt.items.add(file);
            mainInput.files = dt.files;
            window.previewCitizenPhoto(mainInput);
        } else {
            window.previewCitizenPhoto(cameraInput);
        }
    };

    // 8. Document Upload Tiles Preview
    function initDocumentTiles() {
        const tiles = [
            { inputId: 'abha_input', tileId: 'abha-tile', nameId: 'abha-file-name' },
            { inputId: 'ayush_card_input', tileId: 'ayush-tile', nameId: 'ayush-file-name' },
            { inputId: 'ration_input', tileId: 'ration-tile', nameId: 'ration-file-name' },
            { inputId: 'bocw_input', tileId: 'bocw-tile', nameId: 'bocw-file-name' },
            { inputId: 'pmjay_input', tileId: 'pmjay-tile', nameId: 'pmjay-file-name' }
        ];

        tiles.forEach(function (item) {
            const input = document.getElementById(item.inputId);
            const tile = document.getElementById(item.tileId);
            const nameEl = document.getElementById(item.nameId);
            if (!input) return;

            input.addEventListener('change', function () {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    if (file.size > 10 * 1024 * 1024) {
                        alert('दस्तावेज का आकार 10MB से अधिक नहीं होना चाहिए (File must be under 10MB)!');
                        input.value = '';
                        if (tile) tile.classList.remove('has-file');
                        if (nameEl) nameEl.style.display = 'none';
                        return;
                    }
                    if (tile) tile.classList.add('has-file');
                    if (nameEl) {
                        const sizeKb = (file.size / 1024).toFixed(1);
                        nameEl.innerText = '✓ ' + file.name + ' (' + sizeKb + ' KB)';
                        nameEl.style.display = 'inline-block';
                    }
                }
            });
        });
    }

    // 9. Smooth Non-Blocking AJAX Form Submission
    function initAjaxFormSubmission() {
        const form = document.getElementById('ayushman-form');
        const overlay = document.getElementById('ayushman-submit-overlay');
        const submitBtn = document.getElementById('btn-submit-main');
        const progressBar = document.getElementById('ayushman-submit-progress-bar');
        const statusText = document.getElementById('ayushman-submit-status');
        const elapsedText = document.getElementById('ayushman-submit-elapsed');
        const alertBox = document.getElementById('ayush-alert-container');

        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Client-Side Validation
            const fullName = (document.getElementById('full_name') ? document.getElementById('full_name').value.trim() : '');
            const email = (document.getElementById('email') ? document.getElementById('email').value.trim() : '');
            const rawAadhaar = (document.getElementById('aadhaar_input') ? document.getElementById('aadhaar_input').value.replace(/\s+/g, '') : '');
            const mobile = (document.getElementById('mobile') ? document.getElementById('mobile').value.replace(/\D/g, '') : '');
            const consent = (document.getElementById('consent_accepted') ? document.getElementById('consent_accepted').checked : false);

            const validationErrors = [];
            if (!fullName) {
                validationErrors.push('कृपया मरीज / नागरिक का पूरा नाम दर्ज करें (Full Name is required).');
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                validationErrors.push('कृपया एक मान्य ईमेल आईडी दर्ज करें (Valid Email ID is required).');
            }
            if (!/^[2-9][0-9]{11}$/.test(rawAadhaar)) {
                validationErrors.push('आधार नंबर 12 अंकों का होना चाहिए और 2-9 से शुरू होना चाहिए (Valid 12-digit Aadhaar required).');
            }
            if (!/^[6-9][0-9]{9}$/.test(mobile)) {
                validationErrors.push('मोबाइल नंबर 10 अंकों का होना चाहिए और 6-9 से शुरू होना चाहिए (Valid 10-digit Mobile required).');
            }
            if (!consent) {
                validationErrors.push('नियम, शर्तें एवं स्व-घोषणा सहमति स्वीकार करना अनिवार्य है (Terms & Consent must be accepted).');
            }

            if (validationErrors.length > 0) {
                displayFormErrors(validationErrors);
                return;
            }

            // Clear previous errors
            if (alertBox) {
                alertBox.innerHTML = '';
                alertBox.style.display = 'none';
            }

            // Lock submit button & show smooth progress overlay
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Submitting & Generating...';
            }

            const isHi = (document.documentElement.getAttribute('lang') === 'hi');
            if (overlay) overlay.style.display = 'flex';
            if (progressBar) progressBar.style.width = '20%';
            if (statusText) statusText.innerText = isHi ? 'विवरण अपलोड किया जा रहा है...' : 'Uploading details...';

            let seconds = 0;
            const timer = setInterval(function () {
                seconds++;
                if (elapsedText) elapsedText.innerText = seconds + 's';
            }, 1000);

            // Construct FormData for multipart upload
            const formData = new FormData(form);

            const xhr = new XMLHttpRequest();
            const apiUrl = form.getAttribute('action') || (window.BASE_URL ? window.BASE_URL + '/api/submit_ayushman.php' : 'api/submit_ayushman.php');

            xhr.open('POST', apiUrl, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            // Track real upload progress
            xhr.upload.onprogress = function (event) {
                if (event.lengthComputable) {
                    const percent = Math.min(85, Math.round((event.loaded / event.total) * 85));
                    if (progressBar) progressBar.style.width = Math.max(25, percent) + '%';
                    if (statusText) {
                        statusText.innerText = isHi ? 'अपलोडिंग: ' + percent + '% पूर्ण...' : 'Uploading: ' + percent + '%...';
                    }
                }
            };

            xhr.onload = function () {
                clearInterval(timer);

                let res = null;
                try {
                    res = JSON.parse(xhr.responseText);
                } catch (err) {
                    // Non-JSON response (e.g. fatal PHP error)
                    res = {
                        success: false,
                        message: 'सर्वर प्रतिक्रिया अमान्य है। कृपया पुनः प्रयास करें। (Server returned unexpected response)'
                    };
                }

                if (xhr.status >= 200 && xhr.status < 300 && res && res.success) {
                    if (progressBar) progressBar.style.width = '100%';
                    if (statusText) {
                        statusText.innerText = isHi ? 'सफल! स्मार्ट आईडी कार्ड खोला जा रहा है...' : 'Success! Opening your Smart ID Card...';
                    }

                    // Seamless instant redirect
                    setTimeout(function () {
                        window.location.href = res.redirect_url;
                    }, 350);

                } else {
                    // Handle failure or validation errors smoothly without reload
                    if (overlay) overlay.style.display = 'none';
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check-double me-2"></i> <span data-hi="सबमिट करें एवं डिजिटल हेल्थ स्मार्ट आईडी कार्ड जनरेट करें" data-en="Submit &amp; Generate Digital Health Smart ID Card">' +
                            (isHi ? 'सबमिट करें एवं डिजिटल हेल्थ स्मार्ट आईडी कार्ड जनरेट करें' : 'Submit & Generate Digital Health Smart ID Card') + '</span>';
                    }

                    const errorList = (res && res.errors) ? res.errors : [(res && res.message ? res.message : 'सबमिशन विफल रहा। कृपया नेटवर्क जांचें और पुनः प्रयास करें।')];
                    displayFormErrors(errorList);
                }
            };

            xhr.onerror = function () {
                clearInterval(timer);
                if (overlay) overlay.style.display = 'none';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check-double me-2"></i> Submit & Generate Card';
                }
                displayFormErrors(['नेटवर्क त्रुटि: सर्वर से संपर्क नहीं हो सका। कृपया अपना इंटरनेट कनेक्शन जांचें। (Network connection failed)']);
            };

            xhr.send(formData);
        });

        function displayFormErrors(errors) {
            if (!alertBox) {
                alert(errors.join('\n'));
                return;
            }

            let html = '<div class="alert alert-danger ayush-alert-errors">';
            html += '<h5 style="margin: 0 0 8px; font-weight: 800;"><i class="fas fa-exclamation-triangle me-2"></i> कृपया निम्नलिखित त्रुटियों को सुधारें:</h5>';
            html += '<ul style="margin: 0; padding-left: 20px;">';
            errors.forEach(function (err) {
                html += '<li style="font-size: 0.9rem; margin-bottom: 4px;">' + escapeHtml(err) + '</li>';
            });
            html += '</ul></div>';

            alertBox.innerHTML = html;
            alertBox.style.display = 'block';
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.innerText = str;
            return div.innerHTML;
        }
    }

})();
