<?php
// about.php - About Our Mission, Grassroots Identity Facilitation & Citizen Empowerment
require_once __DIR__ . '/config/config.php';

$pageTitle = 'About Us - Our Mission & Citizen Facilitation Services - ' . APP_NAME;
$metaDescription = 'Learn how Jan Seva Portal bridges the digital divide for Indian farmers, daily-wage workers, and rural families by providing accessible ID card generation and scheme assistance at nominal cost.';
$activeNav = 'about';

include __DIR__ . '/includes/header.php';
?>

    <!-- 1. Hero Banner Start -->
    <section class="detail-hero">
        <div class="container">
            <div aria-label="breadcrumb">
                <ol class="breadcrumb detail-breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">About Us (हमारे बारे में)</li>
                </ol>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="detail-kicker"><i class="fas fa-hand-holding-heart me-2"></i> नागरिक सेवा एवं सशक्तिकरण मिशन</span>
                    <h1>Desh Ke Har Kisan, Mazdoor Aur Gramin Parivar Tak Sarkari Suvidha</h1>
                    <p>Digital Bharat ka labh har aam nagrik tak pahunchane ka ek saral, imandar aur sasta madhyam. Bina kisi pareshani ya dalal ke chakkar ke, sidha aur surakshit samadhan.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="tel:<?= preg_replace('/[^0-9]/', '', HELPLINE_NUMBER) ?>" class="btn btn-portal-primary">
                        <i class="fas fa-phone-alt me-1"></i> Helpline: <?= htmlspecialchars(HELPLINE_NUMBER) ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- Hero Banner End -->

    <!-- 2. Grassroots Mission Story Start -->
    <div class="container-fluid py-5 bg-white">
        <div class="container py-3">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="pe-lg-3">
                        <span class="grassroots-badge"><i class="fas fa-compass text-warning me-1"></i> Kyun Shuru Hua Jan Seva Portal?</span>
                        <h2 class="display-6 fw-bold text-dark mb-4" style="line-height: 1.25;">
                            Digital Bharat Mein Koi Bhi Parivar Pichhe Na Chhoote
                        </h2>
                        <p class="text-secondary mb-3 fs-6" style="line-height: 1.8;">
                            Aaj hamare desh mein digital kranti aa chuki hai—har sarkari yojna, pension, swasthya suvidha aur ration card online portals par uplabdh hai. Lekin ground reality yeh hai ki hamare <strong>kisan bhai, dihadi mazdoor, uneducated citizens aur gaon ke parivar</strong> in jatil portals, English bhasha, OTP errors aur biometric validation ke chakkar mein ulajh jaate hain.
                        </p>
                        <p class="text-secondary mb-4 fs-6" style="line-height: 1.8;">
                            Uski wajah se gaon-dehat ke log cyber cafe ya dalalo ke pas jaane par majboor hote hain, jahan unhe ek chote se card ke liye <strong>₹200 se ₹500 tak</strong> kharch karne padte hain. 
                            <strong>Jan Seva Portal</strong> ka janm isi samasya ko khatam karne ke liye hua hai—ek aisi digital khidki jo har nagrik ko sidha, bina kisi jhanjhat ke, aur sirf <strong>₹50 ke nominal platform fee</strong> mein unka haq dilati hai.
                        </p>

                        <div class="row g-3 pt-2">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-3 p-3 rounded bg-light border">
                                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem; flex-shrink: 0;">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Zero Dalali System</h6>
                                        <small class="text-muted">No hidden middlemen charges</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center gap-3 p-3 rounded bg-light border">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem; flex-shrink: 0;">
                                        <i class="fas fa-rupee-sign"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Flat ₹50 Fee</h6>
                                        <small class="text-muted">Nominal maintenance cost</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="position-relative">
                        <div class="image-rounded-shadow">
                            <img class="img-fluid w-100" src="image/indian-farmer-citizen.jpg" alt="Proud Indian farmer with digital identity card" style="min-height: 380px; object-fit: cover;">
                        </div>
                        <div class="position-absolute bottom-0 start-0 m-4 p-3 rounded text-white shadow-lg d-none d-md-block" style="background: rgba(28, 27, 58, 0.92); backdrop-filter: blur(8px); max-width: 380px; border-left: 4px solid #ffc107;">
                            <div class="d-flex gap-2 align-items-center mb-1">
                                <i class="fas fa-check-circle text-warning fs-5"></i>
                                <span class="fw-bold fs-6">Sashakt Kisan, Aatmanirbhar Bharat</span>
                            </div>
                            <small class="text-white-50" style="font-size: 0.78rem;">ABHA, PM-KISAN aur e-Shram jaise jaruri digital identity cards bina cyber cafe ke chakkar kate sidha aapke hath mein.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Grassroots Mission Story End -->

    <!-- 3. Problem vs Solution Contrast Section Start -->
    <div class="container-fluid py-5" style="background: #f8fafc;">
        <div class="container py-3">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="grassroots-badge"><i class="fas fa-balance-scale text-warning me-1"></i> Ground Reality &amp; Resolution</span>
                <h2 class="display-6 fw-bold text-dark mb-3">Cyber Cafe Ki Pareshani vs Jan Seva Ka Asan Samadhan</h2>
                <p class="text-muted">Dekhiye kaise hamara platform gaon-dehat ke nagriko ka samay, paisa aur pareshani bachata hai.</p>
            </div>

            <div class="row g-4">
                <!-- Left: The Struggle -->
                <div class="col-lg-6">
                    <div class="problem-box-danger h-100">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-danger mb-0">Cyber Cafe &amp; Dalalo Ka Chakkar</h4>
                                <small class="text-muted">Pareshaniyan jo har aam parivar jhelta hai</small>
                            </div>
                        </div>

                        <ul class="list-unstyled mb-0">
                            <li class="d-flex gap-3 mb-3">
                                <i class="fas fa-times text-danger mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark">Bhari Kharcha (₹200 se ₹500):</strong>
                                    <p class="text-secondary small mb-0">Muft sarkari registration ke naam par anpadh nagriko se manmana charge vasoolna.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3 mb-3">
                                <i class="fas fa-times text-danger mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark">Kahin Din Ki Daud-Bhaag:</strong>
                                    <p class="text-secondary small mb-0">Gaon se tehsil ya block tak 20-30 kilometer aana-jana aur dihari ka nuksan.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3 mb-3">
                                <i class="fas fa-times text-danger mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark">English Bhasha Aur Technical Confusion:</strong>
                                    <p class="text-secondary small mb-0">Online form ke kathin shabd aur OTP expiry ki wajah se baar-baar application reject hona.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <i class="fas fa-times text-danger mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark">Privacy Aur Kagzat Ka Khatra:</strong>
                                    <p class="text-secondary small mb-0">Aadhaar card aur personal details anjan dukaandaro ke computer par bina suraksha ke chhut jana.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right: Our Solution -->
                <div class="col-lg-6">
                    <div class="solution-box-success h-100">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-success mb-0">Jan Seva Portal Ka Imandar Samadhan</h4>
                                <small class="text-muted">Surakshit, sasta aur sidha rasta</small>
                            </div>
                        </div>

                        <ul class="list-unstyled mb-0">
                            <li class="d-flex gap-3 mb-3">
                                <i class="fas fa-check-circle text-success mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark">Sirf ₹50 Ki Nominal Facilitation Fee:</strong>
                                    <p class="text-secondary small mb-0">Koyi chhipa hua kharcha nahi. Sidha online receipt aur card ki verified PVC format.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3 mb-3">
                                <i class="fas fa-check-circle text-success mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark">Mobile Par 2 Minute Mein Aavedan:</strong>
                                    <p class="text-secondary small mb-0">Saral Hindi aur English mein form, seedhe Aadhaar linked mobile number par OTP aakar verify.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3 mb-3">
                                <i class="fas fa-check-circle text-success mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark">Ghar Baithe Doorstep Executive:</strong>
                                    <p class="text-secondary small mb-0">Buzurg ya asahay nagriko ke ghar hamare pramanit executive aakar kagzat verify karte hain.</p>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <i class="fas fa-check-circle text-success mt-1 fs-5"></i>
                                <div>
                                    <strong class="text-dark">256-Bit Encrypted Data Suraksha:</strong>
                                    <p class="text-secondary small mb-0">Aapka data surakshit hai, kisi third party ko becha ya share nahi kiya jata.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Problem vs Solution Contrast End -->

    <!-- 4. Whom We Serve (Grassroots Personas) Start -->
    <div class="container-fluid py-5 bg-white">
        <div class="container py-3">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="grassroots-badge"><i class="fas fa-users text-warning me-1"></i> Kiske Liye Hai Jan Seva Portal?</span>
                <h2 class="display-6 fw-bold text-dark mb-3">Har Shreni Ke Nagrik Ke Liye Dedicated Facilitation</h2>
                <p class="text-muted">Chahe aap kisan hon, dihadi shramik hon ya buzurg nagrik—hamara platform har varg ki zarurato ko samajhta hai.</p>
            </div>

            <div class="row g-4">
                <!-- Persona 1: Farmers -->
                <div class="col-lg-3 col-md-6">
                    <div class="grassroots-card">
                        <div class="grassroots-icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Hamare Kisan Bhai</h5>
                        <p class="text-secondary small mb-3">
                            Kheti-kisani mein vyast rehte hue sarkari yojnao ka labh uthana ab aasan hai.
                        </p>
                        <div class="border-top pt-3">
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark border">PM-KISAN Samman</span>
                                <span class="badge bg-light text-dark border">Kisan Credit Card</span>
                                <span class="badge bg-light text-dark border">Fasal Bima</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Persona 2: Workers & Laborers -->
                <div class="col-lg-3 col-md-6">
                    <div class="grassroots-card">
                        <div class="grassroots-icon-box bg-warning bg-opacity-25 text-warning">
                            <i class="fas fa-hard-hat text-dark"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Asangathit Shramik &amp; Mazdoor</h5>
                        <p class="text-secondary small mb-3">
                            Nirman mazdoor, rickshaw chalak, dukan sahayak aur daily-wage kamgaro ki suraksha.
                        </p>
                        <div class="border-top pt-3">
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark border">e-Shram Worker Card</span>
                                <span class="badge bg-light text-dark border">₹2L Bima Pass</span>
                                <span class="badge bg-light text-dark border">Ayushman PM-JAY</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Persona 3: Rural Families & Women -->
                <div class="col-lg-3 col-md-6">
                    <div class="grassroots-card">
                        <div class="grassroots-icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-home"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Gramin Parivar &amp; Mahilayein</h5>
                        <p class="text-secondary small mb-3">
                            Parivar ke rashan card, janam-praman patra aur aay-jati praman patra ka aasan nidan.
                        </p>
                        <div class="border-top pt-3">
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark border">NFSA Ration Card</span>
                                <span class="badge bg-light text-dark border">e-District Certificates</span>
                                <span class="badge bg-light text-dark border">Mahila Yojna</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Persona 4: Elderly & Students -->
                <div class="col-lg-3 col-md-6">
                    <div class="grassroots-card">
                        <div class="grassroots-icon-box bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Chatra &amp; Buzurg Nagrik</h5>
                        <p class="text-secondary small mb-3">
                            Vidyarthiyon ke liye APAAR student ID aur buzurgon ke liye pension sahayata.
                        </p>
                        <div class="border-top pt-3">
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark border">APAAR / ABC ID</span>
                                <span class="badge bg-light text-dark border">Atal Pension (APY)</span>
                                <span class="badge bg-light text-dark border">Senior Health ID</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Whom We Serve End -->

    <!-- 5. Doorstep Rural Facilitation Showcase Start -->
    <div class="container-fluid py-5" style="background: #f1f5f9;">
        <div class="container py-3">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="position-relative">
                        <div class="image-rounded-shadow">
                            <img class="img-fluid w-100" src="image/doorstep-rural-facilitation.jpg" alt="Doorstep citizen executive helping rural Indian family" style="min-height: 380px; object-fit: cover;">
                        </div>
                        <div class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark px-3 py-2 fw-bold shadow">
                            <i class="fas fa-handshake me-1"></i> Doorstep Assistance
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 order-1 order-lg-2">
                    <div class="ps-lg-3">
                        <span class="grassroots-badge"><i class="fas fa-car text-warning me-1"></i> Vishishth Suvidha</span>
                        <h2 class="display-6 fw-bold text-dark mb-4">
                            Ghar Baithe Suvidha: Doorstep Executive Sahayata
                        </h2>
                        <p class="text-secondary mb-3 fs-6" style="line-height: 1.8;">
                            Agar aapko online form bharne mein kathinai hoti hai, aapke pas smartphone nahi hai, ya parivar mein koyi buzurg nagrik hain jo safar nahi kar sakte—toh aapko chinta karne ki bilkul zarurat nahi hai.
                        </p>
                        <p class="text-secondary mb-4 fs-6" style="line-height: 1.8;">
                            Hamara <strong>Doorstep Citizen Executive</strong> aapse sampark karke seedhe aapke ghar ya gaon aayega. Vahan aakar aapke kagzat verify kiye jayenge, Aadhaar linked mobile par OTP generate karwaya jayega, aur aapko bina kisi line mein khade hue verified card deliver karwaya jayega.
                        </p>

                        <div class="d-flex gap-3 flex-wrap">
                            <a href="<?= BASE_URL ?>/doorstep-booking.php" class="btn btn-portal-primary px-4 py-3 fw-bold">
                                <i class="fas fa-calendar-check me-2"></i> Book Doorstep Visit &rarr;
                            </a>
                            <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-portal px-4 py-3 fw-bold">
                                <i class="fas fa-headset me-2"></i> Help Desk Se Baat Karein
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Doorstep Rural Facilitation Showcase End -->

    <!-- 6. Our 4 Core Guarantees Start -->
    <div class="container-fluid py-5 bg-white">
        <div class="container py-3">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="grassroots-badge"><i class="fas fa-certificate text-warning me-1"></i> Hamari Pratibaddhta</span>
                <h2 class="display-6 fw-bold text-dark mb-3">Jan Seva Portal Ke 4 Mukhya Sankalp</h2>
                <p class="text-muted">Hamara har kadam aam nagrik ki suvidha, suraksha aur samman ke liye samarpit hai.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="grassroots-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="pillar-number">01</span>
                            <span class="srv-icon-badge bg-warning text-dark fs-5"><i class="fas fa-rupee-sign"></i></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Paradarshi Fee (₹50)</h5>
                        <p class="text-secondary small mb-0">
                            Har service ke liye bilkul nirdharit nominal platform charge. Koyi chhipa hua commission ya anchahe charges nahi.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="grassroots-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="pillar-number">02</span>
                            <span class="srv-icon-badge bg-primary text-white fs-5"><i class="fas fa-language"></i></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Saral Matribhasha</h5>
                        <p class="text-secondary small mb-0">
                            Koyee kathin technical bhasha nahi. Har aavedan aasan Hindi aur local context mein samjhaya jata hai.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="grassroots-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="pillar-number">03</span>
                            <span class="srv-icon-badge bg-success text-white fs-5"><i class="fas fa-shield-alt"></i></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">256-Bit Data Suraksha</h5>
                        <p class="text-secondary small mb-0">
                            Aapki niji jankari aur Aadhaar OTPs banking-grade encrypted server par verify hoti hain. Data privacy hamari pehli shart hai.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="grassroots-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="pillar-number">04</span>
                            <span class="srv-icon-badge bg-info text-white fs-5"><i class="fas fa-balance-scale"></i></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Imandar Compliance</h5>
                        <p class="text-secondary small mb-0">
                            Ham spasht roop se ghoshna karte hain ki ham private facilitator hain. Sarkari portal muft hain, ham sirf digital assistance pradan karte hain.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 4 Core Guarantees End -->

    <!-- 7. Grassroots Impact Metrics Start -->
    <div class="container-fluid py-5" style="background: #f8fafc;">
        <div class="container py-3">
            <div class="row g-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-item-box">
                        <div class="stat-number">1,50,000+</div>
                        <h6 class="fw-bold text-dark mb-1">Assisted Citizens</h6>
                        <small class="text-muted">Kisan, mazdoor aur parivar labh-anvit</small>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-item-box">
                        <div class="stat-number text-success">₹50</div>
                        <h6 class="fw-bold text-dark mb-1">Fixed Nominal Fee</h6>
                        <small class="text-muted">Zero hidden cyber cafe charges</small>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-item-box">
                        <div class="stat-number text-warning">08+</div>
                        <h6 class="fw-bold text-dark mb-1">Key Citizen Cards</h6>
                        <small class="text-muted">ABHA, e-Shram, APAAR, Voter &amp; more</small>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-item-box">
                        <div class="stat-number text-info">500+</div>
                        <h6 class="fw-bold text-dark mb-1">Pincodes Covered</h6>
                        <small class="text-muted">Doorstep executive reach in villages</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Grassroots Impact Metrics End -->

    <!-- 8. Final Call to Action Banner Start -->
    <div class="container-fluid py-5 bg-white">
        <div class="container py-2">
            <div class="doorstep-highlight-banner p-4 p-md-5">
                <div class="row align-items-center gy-4">
                    <div class="col-lg-8">
                        <span class="badge bg-warning text-dark px-3 py-2 text-uppercase fw-bold mb-3">
                            <i class="fas fa-check-circle me-1"></i> Aasan, Vishwasniya &amp; Sasta
                        </span>
                        <h2 class="display-6 fw-bold text-white mb-2">Aapko Kaunsa Sarkari Card Ya Sahayata Chahiye?</h2>
                        <p class="text-white-50 mb-0 fs-6">
                            Chahe aapko swasthya ke liye ABHA Card chahiye ho, rozgar ke liye e-Shram Card, ya vidyarthiyon ke liye APAAR ID—hamare sath sirf 2 minute mein aavedan karein.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="d-flex flex-column flex-sm-row flex-lg-column gap-2 justify-content-lg-end">
                            <a href="<?= BASE_URL ?>/service.php" class="btn btn-warning py-3 px-4 fw-bold text-dark">
                                <i class="fas fa-id-card me-2"></i> Apply For ID Card (₹50)
                            </a>
                            <a href="<?= BASE_URL ?>/doorstep-booking.php" class="btn btn-outline-light py-2 px-3 fw-bold">
                                <i class="fas fa-home me-2"></i> Book Doorstep Home Visit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Final Call to Action Banner End -->

<?php include __DIR__ . '/includes/footer.php'; ?>
