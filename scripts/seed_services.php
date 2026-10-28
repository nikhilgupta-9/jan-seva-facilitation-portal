<?php
// scripts/seed_services.php - Seed rich data for 10 flagship citizen services
require_once __DIR__ . '/../config/db.php';

$services = [
    [
        'slug' => 'abha-card',
        'service_name' => 'ABHA Health ID Card',
        'service_name_hi' => 'आभा हेल्थ आईडी कार्ड (आयुष्मान भारत स्वास्थ्य खाता)',
        'service_type' => 'api',
        'category' => 'Health',
        'department_en' => 'National Health Authority (NHA) & Ministry of Health and Family Welfare',
        'department_hi' => 'राष्ट्रीय स्वास्थ्य प्राधिकरण एवं स्वास्थ्य मंत्रालय',
        'fee' => 50.00,
        'turnaround_time' => 'Instant Digital Pass (2-5 Mins)',
        'icon' => 'fas fa-heartbeat',
        'image_url' => 'image/services/abha-card.jpg',
        'badge_text' => 'Instant Digital PVC',
        'short_desc' => 'Ayushman Bharat Health Account (14-digit Health ID) instant generation & digital PVC card download.',
        'full_desc' => 'ABHA (Ayushman Bharat Health Account) is a unique 14-digit identification number under the Ayushman Bharat Digital Mission (ABDM). It digitally unifies all your medical history, diagnostic reports, prescriptions, and health insurance across empanelled hospitals, diagnostic centres, and clinics across India with end-to-end citizen consent.',
        'benefits_json' => json_encode([
            'Digital Access to Medical Records: Seamless access to prescriptions, lab reports, and discharge summaries anywhere in India.',
            'Paperless Hospital Registrations: Skip long OPD queues with token scan at ABDM-enabled hospitals.',
            'Complete Data Privacy & Consent: You decide which doctor or hospital can access your health data.',
            'Universal Digital Healthcare Pass: Download durable CR80 PVC-format digital pass with QR code.'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'Citizen of India of any age (newborns to senior citizens).',
            'Possession of Aadhaar number or active Indian mobile number for OTP authentication.',
            'No income, employment, or state restrictions.'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            'Aadhaar Card or Active Indian Mobile Number',
            'Basic demographic details (Name, DOB, Gender, District)',
            'Active mobile device to receive 6-digit official UIDAI/ABDM OTP'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Fill Citizen Details', 'desc' => 'Enter your name, date of birth, mobile number, and state.'],
            ['title' => 'Authenticate with Mobile OTP', 'desc' => 'Confirm the 6-digit verification code sent to your registered mobile.'],
            ['title' => 'Nominal Platform Verification', 'desc' => 'Pay the nominal ₹50 facilitation fee via UPI, Card, or Netbanking.'],
            ['title' => 'Instant PVC Card Generation', 'desc' => 'Immediately download your printable high-definition ABHA PVC Card with secure QR code.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'Is ABHA mandatory for treatment?', 'a' => 'No, ABHA is voluntary. However, having an ABHA number significantly speeds up hospital admissions and eliminates physical record-keeping.'],
            ['q' => 'What is the difference between ABHA and Ayushman Card (PM-JAY)?', 'a' => 'ABHA is a universal 14-digit digital health account for all citizens. PM-JAY is an insurance card providing up to ₹5 Lakh free hospitalization for eligible low-income families.'],
            ['q' => 'Can I add family members under my account?', 'a' => 'Yes, family members can link their individual ABHA IDs through the ABHA mobile app or PHR applications.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://healthid.ndhm.gov.in',
        'meta_title' => 'ABHA Health ID Card Online Apply & Instant PVC Card Download',
        'meta_desc' => 'Apply for 14-digit ABHA Health ID Card under Ayushman Bharat Digital Mission. Instant online generation, mobile OTP verification, and secure QR-coded PVC pass.',
        'meta_keywords' => 'ABHA card apply, Ayushman Bharat health ID, ABHA card download, digital health account India, NDHM ABDM card',
        'sort_order' => 1,
        'is_active' => 1
    ],
    [
        'slug' => 'apaar-card',
        'service_name' => 'APAAR / ABC Student ID Card',
        'service_name_hi' => 'अपार / एबीसी छात्र पहचान पत्र (वन नेशन वन स्टूडेंट आईडी)',
        'service_type' => 'api',
        'category' => 'Education',
        'department_en' => 'Ministry of Education & National Educational Technology Forum (NETF)',
        'department_hi' => 'शिक्षा मंत्रालय, भारत सरकार',
        'fee' => 50.00,
        'turnaround_time' => 'Instant Digital Pass (2-5 Mins)',
        'icon' => 'fas fa-graduation-cap',
        'image_url' => 'image/services/apaar-card.jpg',
        'badge_text' => 'National Student ID',
        'short_desc' => 'Automated Permanent Academic Account Registry (12-digit One Nation One Student ID) digital generation.',
        'full_desc' => 'APAAR (Automated Permanent Academic Account Registry) is a lifetime 12-digit student identity mandated under the National Education Policy (NEP 2020). Connected to the Academic Bank of Credits (ABC) and DigiLocker, it permanently records school transfers, college admissions, degrees, scholarships, and co-curricular achievements in a single verified digital repository.',
        'benefits_json' => json_encode([
            'Lifetime Academic Tracking: Tracks learning progression from pre-primary through doctoral research.',
            'Frictionless School & College Transfers: Instant credit transfer between educational institutions across India.',
            'Automated Scholarship & Fee Waiver Verification: Direct integration with National Scholarship Portal (NSP).',
            'Durable CR80 Student Identity Pass: Digital photo card with verifiable QR code for student transport concessions.'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'Enrolled student in any recognized Indian school, college, polytechnic, or university.',
            'Valid U-DISE+ or college enrolment register ID.',
            'Active mobile number linked to student or parent/guardian.'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            'Student Enrolment ID / Roll Number or School U-DISE Code',
            'Aadhaar details of student (or parent for minors below 18)',
            'Active mobile number for receiving OTP'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Enter Academic Profile', 'desc' => 'Provide your school or university name, degree/class, and roll number.'],
            ['title' => 'Aadhaar e-KYC Verification', 'desc' => 'Verify academic credentials using mobile OTP.'],
            ['title' => 'Facilitation Fee Payment', 'desc' => 'Pay the nominal ₹50 digital pass generation charge.'],
            ['title' => 'Instant Download', 'desc' => 'Receive and print your official APAAR Student Identity Card.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'Is APAAR ID compulsory for students?', 'a' => 'Yes, as per Ministry of Education guidelines, APAAR is required for board exams, university admissions, and scholarship disbursals.'],
            ['q' => 'Can parents generate APAAR for minor children?', 'a' => 'Yes, parents or legal guardians can complete consent and OTP verification for school children under 18 years.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://apaar.education.gov.in',
        'meta_title' => 'APAAR Student ID Card Online - One Nation One Student ID Download',
        'meta_desc' => 'Generate and download your official 12-digit APAAR Student ID Card & Academic Bank of Credits (ABC) pass under NEP 2020. Instant digital student verification.',
        'meta_keywords' => 'APAAR card online, ABC ID card download, One Nation One Student ID, student credit card India, NEP 2020 APAAR',
        'sort_order' => 2,
        'is_active' => 1
    ],
    [
        'slug' => 'eshram-card',
        'service_name' => 'e-Shram Worker ID Card',
        'service_name_hi' => 'ई-श्रम कार्ड (असंगठित कर्मकार राष्ट्रीय पहचान पत्र)',
        'service_type' => 'api',
        'category' => 'Welfare',
        'department_en' => 'Ministry of Labour & Employment, Government of India',
        'department_hi' => 'श्रम एवं रोजगार मंत्रालय, भारत सरकार',
        'fee' => 50.00,
        'turnaround_time' => 'Instant Digital Pass (2-5 Mins)',
        'icon' => 'fas fa-hard-hat',
        'image_url' => 'image/services/eshram-card.jpg',
        'badge_text' => '12-Digit UAN',
        'short_desc' => 'National Database of Unorganized Workers (NDUW) 12-digit Universal Account Number (UAN) digital card.',
        'full_desc' => 'e-Shram is the Government of India’s centralized national database for unorganized sector workers (construction workers, migrant laborers, gig and platform workers, domestic helpers, street vendors, and agricultural laborers). Registered workers receive a 12-digit UAN Card providing direct access to social security pensions, accidental death insurance (PMSBY), and emergency financial relief during national crises.',
        'benefits_json' => json_encode([
            '12-Digit Universal Account Number (UAN) recognized in all 28 states and 8 union territories.',
            'Accidental Insurance Cover of ₹2 Lakh in case of accidental death or permanent disability.',
            'Direct Benefit Transfer (DBT) eligibility for central and state labor welfare assistance.',
            'Access to National Career Service (NCS) and free skilling programs under PMKVY.'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'Age between 16 and 59 years.',
            'Working in the unorganized sector (not paying income tax).',
            'Not an active member of EPFO or ESIC.'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            'Aadhaar Number linked with active mobile',
            'Bank Account Number with IFSC Code',
            'Primary Occupation detail (e.g. Mason, Driver, Cook, Farmer)'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Worker Profile Intake', 'desc' => 'Enter occupation category, father name, and demographic address.'],
            ['title' => 'Mobile OTP Validation', 'desc' => 'Authenticate worker demographic records securely.'],
            ['title' => 'Nominal Platform Fee', 'desc' => 'Pay the standard ₹50 card processing charge.'],
            ['title' => 'Download High-Res e-Shram Card', 'desc' => 'Download your official e-Shram PVC card with UAN and photo ready for printing.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'Can a small farmer or agricultural worker apply for e-Shram?', 'a' => 'Yes, small and marginal farmers, agricultural laborers, and allied rural workers are fully eligible.'],
            ['q' => 'Is there any renewal needed for e-Shram Card?', 'a' => 'No, the 12-digit e-Shram UAN is valid for lifetime with no annual renewal fees.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://eshram.gov.in',
        'meta_title' => 'e-Shram Card Apply Online - 12-Digit UAN Card Download',
        'meta_desc' => 'Apply and download your official 12-digit e-Shram UAN Card. Instant worker verification, ₹2 Lakh accidental insurance cover, and nationwide labor welfare benefits.',
        'meta_keywords' => 'eshram card online apply, e shramik card download, UAN card registration, unorganized worker card India',
        'sort_order' => 3,
        'is_active' => 1
    ],
    [
        'slug' => 'voter-id',
        'service_name' => 'Voter ID (EPIC) Verification Card',
        'service_name_hi' => 'मतदाता पहचान पत्र (ईपीआईसी कार्ड सत्यापन एवं डिजिटल पास)',
        'service_type' => 'api',
        'category' => 'Identity',
        'department_en' => 'Election Commission of India (ECI)',
        'department_hi' => 'भारत निर्वाचन आयोग',
        'fee' => 50.00,
        'turnaround_time' => 'Instant Digital Pass (2-5 Mins)',
        'icon' => 'fas fa-vote-yea',
        'image_url' => 'image/services/voter-id.jpg',
        'badge_text' => 'ECI Verified Pass',
        'short_desc' => 'Electoral Photo Identity Card (EPIC) verification, polling booth locator & high-res PVC pass.',
        'full_desc' => 'The Electoral Photo Identity Card (EPIC) issued by the Election Commission of India is statutory proof of Indian citizenship and voting franchise. This service allows citizens to authenticate their 10-character EPIC number against the official National Voters Service Portal (NVSP), locate parliamentary and assembly polling stations, and generate a verified digital pass with polling station locator.',
        'benefits_json' => json_encode([
            'Official Proof of Indian Citizenship and Electoral Rights.',
            'Instant Verification of Assembly Constituency and Polling Booth Number.',
            'QR-Coded High Definition PVC Card design ready for plastic card lamination.',
            'Assistance with Form 6 (New Voter), Form 7 (Deletion), and Form 8 (Correction).'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'Citizen of India aged 18 years or above on qualifying date.',
            'Enrolled in the official electoral roll of any assembly constituency.',
            'Possession of a 10-character EPIC number or voter registration acknowledgement.'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            '10-Character EPIC Voter ID Number',
            'Active mobile number for receiving status updates',
            'Basic residential details for constituency confirmation'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Enter EPIC & Constituency', 'desc' => 'Provide your voter ID number and assembly constituency.'],
            ['title' => 'Authenticate Record', 'desc' => 'System checks electoral roll and confirms polling station details.'],
            ['title' => 'Pay ₹50 Processing Fee', 'desc' => 'Secure nominal facilitation fee payment.'],
            ['title' => 'Download Digital Voter Card', 'desc' => 'Download print-ready voter identity pass immediately.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'Can I vote using the digital voter pass?', 'a' => 'Yes, if your name is in the electoral roll, you can present this card along with any valid photo ID at your assigned polling booth.'],
            ['q' => 'How to correct address on Voter ID?', 'a' => 'Address correction is processed via ECI Form 8. We provide assisted checklist and direct portal link.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://voters.eci.gov.in',
        'meta_title' => 'Voter ID Card Online Verification & EPIC Card Download',
        'meta_desc' => 'Verify your Voter ID (EPIC) number, check electoral roll inclusion, and download your digital voter pass. Direct Election Commission of India facilitation.',
        'meta_keywords' => 'voter id download, EPIC card online, nvsp voter search, check voter status, electoral photo identity card',
        'sort_order' => 4,
        'is_active' => 1
    ],
    [
        'slug' => 'aadhaar-services',
        'service_name' => 'Aadhaar Services & Update Guidance',
        'service_name_hi' => 'आधार सेवाएं एवं बायोमेट्रिक अपडेट मार्गदर्शन',
        'service_type' => 'redirect',
        'category' => 'Identity',
        'department_en' => 'Unique Identification Authority of India (UIDAI)',
        'department_hi' => 'भारतीय विशिष्ट पहचान प्राधिकरण (यूआईडीएआई)',
        'fee' => 0.00,
        'turnaround_time' => 'Official Turnaround: 5-15 Days',
        'icon' => 'fas fa-fingerprint',
        'image_url' => 'image/services/aadhaar-services.jpg',
        'badge_text' => 'UIDAI Official Guide',
        'short_desc' => 'Step-by-step guidance for mandatory 10-year document update, address change, and Aadhaar Seva Kendra appointment.',
        'full_desc' => 'Aadhaar is a 12-digit unique identity number issued by UIDAI to all Indian residents based on biometric and demographic records. Our portal provides step-by-step assisted guidance for mandatory 10-year document updates (PoI/PoA), online address updates, mobile number linking, Baal Aadhaar transition (mandatory biometric update at age 5 and 15), and instant online appointment booking at Aadhaar Seva Kendras (ASK).',
        'benefits_json' => json_encode([
            'Step-by-step pre-verified checklist preventing rejection at Aadhaar Seva Kendras.',
            'Direct official link for Free Online Document Update (PoI & PoA) on myAadhaar.',
            'Appointment booking guidance to skip physical waiting lines at bank/post office ASKs.',
            'Guidance for Masked Aadhaar download to protect biometric privacy.'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'All Indian residents holding a 12-digit Aadhaar number.',
            'Citizens whose Aadhaar was issued over 10 years ago and has not been updated.',
            'Children attaining 5 or 15 years of age (Mandatory Biometric Update).'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            'Proof of Identity (PoI): Passport, PAN Card, Voter ID, Ration Card, or Driving License',
            'Proof of Address (PoA): Bank Passbook, Electricity Bill, Water Bill, Rent Agreement',
            'Active mobile number linked with Aadhaar for OTP verification'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Review Prerequisite Checklist', 'desc' => 'Confirm you have valid PoI and PoA documents in PDF/JPEG format.'],
            ['title' => 'Open Official UIDAI Portal', 'desc' => 'Navigate directly to myAadhaar (myaadhaar.uidai.gov.in) with our verified link.'],
            ['title' => 'Login via Aadhaar OTP', 'desc' => 'Enter 12-digit Aadhaar and complete captcha & mobile OTP verification.'],
            ['title' => 'Submit Documents or Book Slot', 'desc' => 'Upload documents for address update or reserve appointment slot for biometric changes.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'Is the 10-year Aadhaar document update mandatory?', 'a' => 'Yes, UIDAI strongly recommends updating Proof of Identity and Proof of Address every 10 years to maintain active e-KYC status.'],
            ['q' => 'Can mobile number be changed online?', 'a' => 'No, mobile number and biometric updates require in-person verification at an authorized Aadhaar Seva Kendra (ASK). Address can be changed online.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://myaadhaar.uidai.gov.in',
        'meta_title' => 'Aadhaar Card Update Online - Address Change & Appointment Guide',
        'meta_desc' => 'Complete official guide for Aadhaar 10-year document update, address change, and Aadhaar Seva Kendra appointment booking. Free official UIDAI links.',
        'meta_keywords' => 'aadhaar update online, myaadhaar uidai, aadhaar address change, book aadhaar appointment, aadhaar document update free',
        'sort_order' => 5,
        'is_active' => 1
    ],
    [
        'slug' => 'ration-card',
        'service_name' => 'State Ration Card (NFSA) Guidance',
        'service_name_hi' => 'राशन कार्ड (राष्ट्रीय खाद्य सुरक्षा अधिनियम) मार्गदर्शन',
        'service_type' => 'redirect',
        'category' => 'Welfare',
        'department_en' => 'Department of Food & Public Distribution & State Civil Supplies',
        'department_hi' => 'खाद्य एवं सार्वजनिक वितरण विभाग, भारत सरकार',
        'fee' => 0.00,
        'turnaround_time' => 'Official Turnaround: 15-30 Days',
        'icon' => 'fas fa-utensils',
        'image_url' => 'image/services/ration-card.jpg',
        'badge_text' => 'NFSA Food Security',
        'short_desc' => 'State Ration Card application checklist, e-Ration Card download, and One Nation One Ration Card (ONORC) transfer.',
        'full_desc' => 'Ration Cards issued under the National Food Security Act (NFSA) by State Food & Civil Supplies Departments entitle eligible households to subsidized food grains (rice, wheat, coarse grains) through Fair Price Shops (FPS). Under the One Nation One Ration Card (ONORC) initiative, migrant workers can lift foodgrains from any ration shop in the country using biometric authentication.',
        'benefits_json' => json_encode([
            'Subsidized and free food grains under Pradhan Mantri Garib Kalyan Anna Yojana (PMGKAY).',
            'One Nation One Ration Card (ONORC) portability across all Fair Price Shops in India.',
            'Official documentary proof of family composition and state residency.',
            'Prerequisite qualification document for PMAY rural housing and state social security pensions.'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'Resident family of the respective state/UT not already holding another ration card.',
            'Antyodaya Anna Yojana (AAY): Poorest of poor households.',
            'Priority Household (PHH): Income within state-mandated poverty thresholds.'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            'Aadhaar cards of all family members to be included',
            'Passport-size photograph of head of family (female head preferred)',
            'Income certificate issued by Tehsildar or Sub-Divisional Magistrate',
            'Valid electricity/gas bill or bank passbook copy'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Check State Portal', 'desc' => 'Select your state civil supplies portal link from our directory.'],
            ['title' => 'Prepare Family Documents', 'desc' => 'Gather Aadhaar cards of all members and female head of household.'],
            ['title' => 'Submit NFSA Application', 'desc' => 'Fill online e-district or food portal form and upload scanned proofs.'],
            ['title' => 'Track Ration Card Status', 'desc' => 'Use your acknowledgement number to track field verification by the Food Inspector.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'Can I get ration in another state with my existing card?', 'a' => 'Yes, under ONORC (One Nation One Ration Card), any family member with linked Aadhaar can collect their entitlement anywhere in India.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://nfsa.gov.in',
        'meta_title' => 'Ration Card Apply Online - NFSA Food Security & ONORC Guide',
        'meta_desc' => 'Apply for new State Ration Card under NFSA, check beneficiary list, and download e-Ration card. Step-by-step guidance for all Indian states.',
        'meta_keywords' => 'ration card apply online, NFSA beneficiary list, download ration card, ONORC food security, rashan card form',
        'sort_order' => 6,
        'is_active' => 1
    ],
    [
        'slug' => 'pan-card',
        'service_name' => 'PAN Card Correction / Verification',
        'service_name_hi' => 'पैन कार्ड सुधार, सत्यापन एवं ई-पैन डाउनलोड',
        'service_type' => 'redirect',
        'category' => 'Identity',
        'department_en' => 'Income Tax Department & Protean (NSDL) / UTIITSL',
        'department_hi' => 'आयकर विभाग, वित्त मंत्रालय, भारत सरकार',
        'fee' => 0.00,
        'turnaround_time' => 'Official Turnaround: Instant e-PAN / 10-15 Days PVC',
        'icon' => 'fas fa-id-badge',
        'image_url' => 'image/services/pan-card.jpg',
        'badge_text' => 'Income Tax Dept',
        'short_desc' => 'Permanent Account Number (PAN) correction, reprint, instant e-PAN generation, and Aadhaar-PAN linking.',
        'full_desc' => 'A Permanent Account Number (PAN) is a ten-digit alphanumeric identifier issued by the Income Tax Department through Protean (formerly NSDL) and UTIITSL. A valid PAN is mandatory for opening bank accounts, financial transactions above ₹50,000, business registration, and filing income tax returns. We provide verified procedures for instant paperless e-PAN, correction in name/DOB, and mandatory PAN-Aadhaar linking.',
        'benefits_json' => json_encode([
            'Instant paperless e-PAN generation in 10 minutes via Aadhaar e-KYC.',
            'Official financial identity mandatory for banking, demat accounts, and tax filing.',
            'Online reprint request for lost, damaged, or faded physical plastic PAN cards.',
            'Guidance to resolve name/DOB mismatch between Aadhaar and PAN records.'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'Any Indian citizen, minor (via parent), NRI, or business entity.',
            'For Instant e-PAN: Active Aadhaar linked with mobile number and no prior PAN allocated.',
            'For PAN Correction: Existing valid 10-digit PAN number.'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            'Aadhaar Card (serves as Proof of Identity, Address, and Date of Birth)',
            'Existing PAN Card copy (for correction or reprint applications)',
            'Supporting certificate (e.g. Gazetted officer certificate for name changes)'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Identify Requirement', 'desc' => 'Determine whether you need an Instant e-PAN, Correction, or Physical Reprint.'],
            ['title' => 'Visit Official Portal', 'desc' => 'Access Income Tax e-Filing (Instant e-PAN) or Protean/UTIITSL with our deep link.'],
            ['title' => 'Complete e-KYC', 'desc' => 'Authenticate with Aadhaar OTP to automatically verify demographics.'],
            ['title' => 'Download e-PAN or Track Delivery', 'desc' => 'Download PDF e-PAN immediately or track PVC card dispatch via India Post Speed Post.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'Is an Instant e-PAN equally valid as a physical card?', 'a' => 'Yes, digital e-PAN carries equal statutory validity under Rule 114 of the Income Tax Rules.'],
            ['q' => 'Is linking PAN with Aadhaar mandatory?', 'a' => 'Yes, as per Section 139AA of the Income Tax Act, non-linked PAN cards become inoperative for financial transactions.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://www.incometax.gov.in/iec/fposervices/#/pre-login/instant-e-pan',
        'meta_title' => 'PAN Card Apply Online - Instant e-PAN & Correction Guide',
        'meta_desc' => 'Apply for Instant e-PAN in 10 minutes via Aadhaar OTP, correct PAN details, or request physical reprint. Official Income Tax Department procedures.',
        'meta_keywords' => 'instant e pan online, pan card correction form, download pan card, pan aadhaar link status, reprint pan card',
        'sort_order' => 7,
        'is_active' => 1
    ],
    [
        'slug' => 'edistrict-certificates',
        'service_name' => 'e-District State Certificates',
        'service_name_hi' => 'ई-डिस्ट्रिक्ट प्रमाण पत्र (आय, जाति, निवास एवं जन्म प्रमाण पत्र)',
        'service_type' => 'redirect',
        'category' => 'Welfare',
        'department_en' => 'State Revenue Departments & District Administration',
        'department_hi' => 'राजस्व विभाग एवं जिला प्रशासन',
        'fee' => 0.00,
        'turnaround_time' => 'Official Turnaround: 7-15 Working Days',
        'icon' => 'fas fa-file-contract',
        'image_url' => 'image/services/edistrict-certificates.jpg',
        'badge_text' => 'State Revenue',
        'short_desc' => 'Official assistance for Income (Aay), Caste (Jati), Domicile (Nivas), and Birth/Death certificates.',
        'full_desc' => 'State e-District portals enable citizens to apply for statutory certificates issued by Tehsildars, Revenue Officers, and District Magistrates. These include Income Certificates (Aay Praman Patra), Caste Certificates (Jati Praman Patra for SC/ST/OBC), Domicile/Residence Certificates (Niwas Praman Patra), and non-creamy layer certificates needed for school/college admissions, government jobs, and government welfare schemes.',
        'benefits_json' => json_encode([
            'QR-Coded Digitally Signed Certificates accepted nationwide without physical gazetted attestation.',
            'Essential prerequisite for OBC/SC/ST reservations in UPSC, SSC, NEET, and JEE examinations.',
            'Income certificate mandatory for scholarships and fee exemptions.',
            'Online tracking of Lekhpal / Revenue Inspector verification reports.'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'Permanent or long-term resident of the respective state/district.',
            'Income Certificate: Based on total verified annual family income.',
            'Caste Certificate: Lineage documents showing community status.'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            'Aadhaar Card and Passport-sized photograph',
            'Self-declaration certificate form',
            'Land revenue record (Khatauni) or salary slip / ITR for income proof',
            'School transfer certificate (TC) or family member existing caste certificate'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Select Your State e-District Portal', 'desc' => 'Choose UP eDistrict, Bihar RTPS, MP e-District, Rajasthan e-Mitra, etc.'],
            ['title' => 'Fill Citizen Application', 'desc' => 'Enter applicant particulars, parentage, and residential address.'],
            ['title' => 'Upload Scanned Documents', 'desc' => 'Upload photo, self-declaration, and identity proofs.'],
            ['title' => 'Download Digitally Signed Certificate', 'desc' => 'Download certificate bearing unique verification code once approved by Tehsildar.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'What is the validity period of an Income Certificate?', 'a' => 'In most states, an income certificate is valid for 3 financial years from the date of issue.'],
            ['q' => 'Do I need to visit the Tehsil office in person?', 'a' => 'No, for applications filed via official state e-District citizen portals, physical visit is rarely required unless requested during spot verification.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://services.india.gov.in',
        'meta_title' => 'e-District Certificates Online - Income, Caste & Domicile Guide',
        'meta_desc' => 'Apply for Income Certificate (Aay), Caste Certificate (Jati), and Domicile (Niwas) across state e-District portals. Direct state citizen portal links and checklist.',
        'meta_keywords' => 'e district certificate, aay praman patra online, jati praman patra apply, niwas praman patra download, revenue certificate',
        'sort_order' => 8,
        'is_active' => 1
    ],
    [
        'slug' => 'driving-license',
        'service_name' => 'Driving Licence & RC Parivahan',
        'service_name_hi' => 'ड्राइविंग लाइसेंस एवं आरसी परिवहन सेवा',
        'service_type' => 'redirect',
        'category' => 'Transport',
        'department_en' => 'Ministry of Road Transport and Highways (MoRTH) & State RTOs',
        'department_hi' => 'सड़क परिवहन एवं राजमार्ग मंत्रालय, भारत सरकार',
        'fee' => 0.00,
        'turnaround_time' => 'Official Turnaround: Instant Learner / 15-30 Days DL',
        'icon' => 'fas fa-id-card-alt',
        'image_url' => 'image/services/driving-license.jpg',
        'badge_text' => 'Parivahan Sewa',
        'short_desc' => 'Learner licence contactless online test, permanent DL appointment, licence renewal, and mParivahan digital RC.',
        'full_desc' => 'Sarathi and Vahan portals operated by MoRTH provide centralized transport facilitation across all 36 States and UTs. Citizens can take the online computer test from home using Aadhaar facial authentication for Learner Licence (LL), apply for Permanent Driving Licence (DL), schedule automated driving test tracks, renew expired licenses, or link digital driving credentials to DigiLocker and mParivahan.',
        'benefits_json' => json_encode([
            'Contactless Learner Licence (LL) exam from home without visiting RTO.',
            'Official QR-coded Smart Card DL recognized across India.',
            'Paperless renewal and address change within 5 years of license expiration.',
            'Legally accepted digital driving license on DigiLocker / mParivahan under IT Act 2000.'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'Learner Licence for Motorcycle without Gear (<50cc): Minimum 16 years with parental consent.',
            'Light Motor Vehicle (Car/Bike): Minimum 18 years of age.',
            'Commercial Vehicle: Minimum 20 years with 1 year prior LMV experience.'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            'Aadhaar Card (serves for e-KYC and facial authentication)',
            'Proof of Age (Class 10 Certificate, Birth Certificate, or Passport)',
            'Medical Certificate Form 1-A (for commercial licenses or applicants aged 40+)'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Aadhaar e-KYC on Sarathi', 'desc' => 'Enter Aadhaar number on parivahan.gov.in to auto-populate residential details.'],
            ['title' => 'Online Road Safety Tutorial', 'desc' => 'Watch the mandatory road safety video and take the online MCQ test.'],
            ['title' => 'Instant Learner Licence Download', 'desc' => 'Download your official 6-month Learner Licence PDF immediately.'],
            ['title' => 'Book Permanent DL Slot', 'desc' => 'After 30 days, book a practical driving test track appointment at your local RTO.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'Can I give the learner license test from my mobile phone?', 'a' => 'Yes, states supporting Aadhaar authentication allow online facial recognition tests on mobile or desktop with webcam.'],
            ['q' => 'What is the validity of a permanent driving license?', 'a' => 'A non-transport driving license is valid for 20 years or until the holder reaches 40 years of age, whichever comes earlier.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://sarathi.parivahan.gov.in',
        'meta_title' => 'Driving Licence Online Apply - Learner License & Parivahan Guide',
        'meta_desc' => 'Apply for Learner Licence online from home with Aadhaar test, book driving test slots, and renew DL on official Sarathi Parivahan portal.',
        'meta_keywords' => 'driving licence online apply, sarathi parivahan learner licence, driving license test online, DL renewal, mparivahan RC',
        'sort_order' => 9,
        'is_active' => 1
    ],
    [
        'slug' => 'pm-kisan-ekyc',
        'service_name' => 'PM Kisan e-KYC & Beneficiary Status',
        'service_name_hi' => 'पीएम किसान ई-केवाईसी एवं किसान पासबुक सत्यापन',
        'service_type' => 'redirect',
        'category' => 'Agriculture',
        'department_en' => 'Ministry of Agriculture & Farmers Welfare',
        'department_hi' => 'कृषि एवं किसान कल्याण मंत्रालय, भारत सरकार',
        'fee' => 0.00,
        'turnaround_time' => 'Instant Verification (2 Mins)',
        'icon' => 'fas fa-tractor',
        'image_url' => 'image/services/pm-kisan-ekyc.jpg',
        'badge_text' => 'PM-KISAN DBT',
        'short_desc' => 'Mandatory Aadhaar OTP e-KYC verification, land seeding check, installment payment status, and Kisan Credit Card linking.',
        'full_desc' => 'Pradhan Mantri Kisan Samman Nidhi (PM-KISAN) provides ₹6,000 per year direct income support in three 4-monthly installments of ₹2,000 each to eligible farmer families. To continue receiving installments without interruption, farmers must complete mandatory Aadhaar OTP or facial e-KYC, ensure Land Seeding in the state revenue records, and link their active bank account with NPCI / Aadhaar DBT.',
        'benefits_json' => json_encode([
            'Uninterrupted direct credit of ₹2,000 quarterly installments into farmer bank account.',
            'Instant verification of Land Seeding, e-KYC status, and Aadhaar Bank Account Linking.',
            'Resolution of pending FTO (Fund Transfer Order) generation issues.',
            'Fast-track eligibility for Kisan Credit Card (KCC) with subsidized 4% interest loans.'
        ], JSON_UNESCAPED_UNICODE),
        'eligibility_json' => json_encode([
            'Small and marginal landholder farmer families with cultivable land in their names.',
            'Citizen not holding constitutional posts, government employment, or paying income tax.',
            'Aadhaar-seeded bank account with active DBT mandate.'
        ], JSON_UNESCAPED_UNICODE),
        'documents_json' => json_encode([
            '12-Digit Aadhaar Number of the farmer',
            'Registered mobile number linked with Aadhaar',
            'Land ownership record (Khatauni / Khasra number)'
        ], JSON_UNESCAPED_UNICODE),
        'steps_json' => json_encode([
            ['title' => 'Open PM-KISAN Portal', 'desc' => 'Navigate directly to pmkisan.gov.in e-KYC section.'],
            ['title' => 'Enter Aadhaar Number', 'desc' => 'Input 12-digit Aadhaar and registered mobile number.'],
            ['title' => 'Submit OTP', 'desc' => 'Confirm 4-digit PM-Kisan mobile OTP followed by 6-digit Aadhaar OTP.'],
            ['title' => 'Verify Beneficiary Status', 'desc' => 'Check that e-KYC Status shows Success and Land Seeding shows YES.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs_json' => json_encode([
            ['q' => 'What should I do if my installment is stopped due to Land Seeding?', 'a' => 'Visit your local Tehsil or Block Agriculture Officer (BAO) with your Khatauni land record to update land seeding in the PM-Kisan portal.'],
            ['q' => 'Is face authentication available for PM-Kisan e-KYC?', 'a' => 'Yes, farmers can download the PM-KISAN Mobile App and complete e-KYC using Face Authentication without OTP or fingerprint.']
        ], JSON_UNESCAPED_UNICODE),
        'official_url' => 'https://pmkisan.gov.in',
        'meta_title' => 'PM Kisan e-KYC Online Verification - Check Beneficiary Status',
        'meta_desc' => 'Complete mandatory PM-KISAN e-KYC with Aadhaar OTP, check installment payment status, land seeding, and beneficiary list online on official portal.',
        'meta_keywords' => 'pm kisan ekyc online, check pm kisan status, pmkisan gov in beneficiary, pm kisan installment, kisan samman nidhi ekyc',
        'sort_order' => 10,
        'is_active' => 1
    ]
];

$countUpdated = 0;
$countInserted = 0;

foreach ($services as $s) {
    $check = $pdo->prepare("SELECT id FROM services WHERE slug = ?");
    $check->execute([$s['slug']]);
    $existingId = $check->fetchColumn();

    if ($existingId) {
        $sql = "UPDATE services SET
            slug = :slug,
            service_name = :service_name,
            service_name_hi = :service_name_hi,
            service_type = :service_type,
            category = :category,
            department_en = :department_en,
            department_hi = :department_hi,
            fee = :fee,
            turnaround_time = :turnaround_time,
            icon = :icon,
            image_url = :image_url,
            badge_text = :badge_text,
            short_desc = :short_desc,
            full_desc = :full_desc,
            benefits_json = :benefits_json,
            eligibility_json = :eligibility_json,
            documents_json = :documents_json,
            steps_json = :steps_json,
            faqs_json = :faqs_json,
            official_url = :official_url,
            meta_title = :meta_title,
            meta_desc = :meta_desc,
            meta_keywords = :meta_keywords,
            sort_order = :sort_order,
            is_active = :is_active
        WHERE id = :id";
        $s['id'] = $existingId;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($s);
        $countUpdated++;
    } else {
        $sql = "INSERT INTO services (
            slug, service_name, service_name_hi, service_type, category,
            department_en, department_hi, fee, turnaround_time, icon,
            image_url, badge_text, short_desc, full_desc, benefits_json,
            eligibility_json, documents_json, steps_json, faqs_json,
            official_url, meta_title, meta_desc, meta_keywords, sort_order, is_active
        ) VALUES (
            :slug, :service_name, :service_name_hi, :service_type, :category,
            :department_en, :department_hi, :fee, :turnaround_time, :icon,
            :image_url, :badge_text, :short_desc, :full_desc, :benefits_json,
            :eligibility_json, :documents_json, :steps_json, :faqs_json,
            :official_url, :meta_title, :meta_desc, :meta_keywords, :sort_order, :is_active
        )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($s);
        $countInserted++;
    }
}

echo "Seeding completed: Updated $countUpdated services, Inserted $countInserted new services. Total in DB: " . ($countUpdated + $countInserted) . "\n";
