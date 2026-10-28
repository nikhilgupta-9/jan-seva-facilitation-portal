<?php
// data/schemes.php - Master Core Government Schemes Repository
// Sourced from Vajiram & Ravi Official 2026 Directory & VisionIAS PT 365
// Supports live database management via Admin panel with static fallback

require_once dirname(__DIR__) . '/config/db.php';

function get_all_schemes($onlyActive = true) {
    global $pdo;
    try {
        if ($pdo) {
            $sql = "SELECT * FROM `schemes`" . ($onlyActive ? " WHERE `is_active` = 1" : "") . " ORDER BY `sort_order` ASC, `id` ASC";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $schemes = [];
                foreach ($rows as $row) {
                    $row['applicable_states'] = is_string($row['applicable_states']) ? (json_decode($row['applicable_states'], true) ?? explode(',', $row['applicable_states'])) : ($row['applicable_states'] ?? []);
                    $row['benefits_en'] = is_string($row['benefits_en']) ? (json_decode($row['benefits_en'], true) ?? []) : ($row['benefits_en'] ?? []);
                    $row['benefits_hi'] = is_string($row['benefits_hi']) ? (json_decode($row['benefits_hi'], true) ?? []) : ($row['benefits_hi'] ?? []);
                    $row['eligibility_en'] = is_string($row['eligibility_en']) ? (json_decode($row['eligibility_en'], true) ?? []) : ($row['eligibility_en'] ?? []);
                    $row['eligibility_hi'] = is_string($row['eligibility_hi']) ? (json_decode($row['eligibility_hi'], true) ?? []) : ($row['eligibility_hi'] ?? []);
                    $row['documents_en'] = is_string($row['documents_en']) ? (json_decode($row['documents_en'], true) ?? []) : ($row['documents_en'] ?? []);
                    $row['documents_hi'] = is_string($row['documents_hi']) ? (json_decode($row['documents_hi'], true) ?? []) : ($row['documents_hi'] ?? []);
                    $row['how_to_apply_en'] = is_string($row['how_to_apply_en']) ? (json_decode($row['how_to_apply_en'], true) ?? []) : ($row['how_to_apply_en'] ?? []);
                    $row['how_to_apply_hi'] = is_string($row['how_to_apply_hi']) ? (json_decode($row['how_to_apply_hi'], true) ?? []) : ($row['how_to_apply_hi'] ?? []);
                    $row['eligibility_checker'] = is_string($row['eligibility_checker']) ? (json_decode($row['eligibility_checker'], true) ?? []) : ($row['eligibility_checker'] ?? []);
                    $schemes[$row['slug']] = $row;
                }
                return $schemes;
            }
        }
    } catch (Exception $e) {
        // Fallback to static
    }
    return get_static_schemes_fallback();
}

function get_scheme_by_slug($slug) {
    global $pdo;
    try {
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM `schemes` WHERE `slug` = ? AND `is_active` = 1 LIMIT 1");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $row['applicable_states'] = is_string($row['applicable_states']) ? (json_decode($row['applicable_states'], true) ?? explode(',', $row['applicable_states'])) : ($row['applicable_states'] ?? []);
                $row['benefits_en'] = is_string($row['benefits_en']) ? (json_decode($row['benefits_en'], true) ?? []) : ($row['benefits_en'] ?? []);
                $row['benefits_hi'] = is_string($row['benefits_hi']) ? (json_decode($row['benefits_hi'], true) ?? []) : ($row['benefits_hi'] ?? []);
                $row['eligibility_en'] = is_string($row['eligibility_en']) ? (json_decode($row['eligibility_en'], true) ?? []) : ($row['eligibility_en'] ?? []);
                $row['eligibility_hi'] = is_string($row['eligibility_hi']) ? (json_decode($row['eligibility_hi'], true) ?? []) : ($row['eligibility_hi'] ?? []);
                $row['documents_en'] = is_string($row['documents_en']) ? (json_decode($row['documents_en'], true) ?? []) : ($row['documents_en'] ?? []);
                $row['documents_hi'] = is_string($row['documents_hi']) ? (json_decode($row['documents_hi'], true) ?? []) : ($row['documents_hi'] ?? []);
                $row['how_to_apply_en'] = is_string($row['how_to_apply_en']) ? (json_decode($row['how_to_apply_en'], true) ?? []) : ($row['how_to_apply_en'] ?? []);
                $row['how_to_apply_hi'] = is_string($row['how_to_apply_hi']) ? (json_decode($row['how_to_apply_hi'], true) ?? []) : ($row['how_to_apply_hi'] ?? []);
                $row['eligibility_checker'] = is_string($row['eligibility_checker']) ? (json_decode($row['eligibility_checker'], true) ?? []) : ($row['eligibility_checker'] ?? []);
                return $row;
            }
        }
    } catch (Exception $e) {
        // Fallback
    }
    $all = get_all_schemes();
    return $all[$slug] ?? null;
}

function get_static_schemes_fallback() {
    return [
        // ---------------------------------------------------------------------
        // 1. PM-KISAN (Central Sector)
        // ---------------------------------------------------------------------
        'pm-kisan' => [
            'id' => 1,
            'slug' => 'pm-kisan',
            'icon' => 'fa-tractor',
            'category' => 'agriculture',
            'category_name_en' => 'Agriculture & Farmers Welfare',
            'category_name_hi' => 'कृषि एवं किसान कल्याण',
            'funding_type' => 'central_sector',
            'funding_type_label_en' => 'Central Sector Scheme (100% Central Funded)',
            'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (100% केंद्र सरकार द्वारा वित्तपोषित)',
            'ministry_en' => 'Ministry of Agriculture and Farmers Welfare',
            'ministry_hi' => 'कृषि एवं किसान कल्याण मंत्रालय',
            'launch_year' => '2019',
            'budget' => '₹60,000 Crore / year',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'PM-KISAN (Pradhan Mantri Kisan Samman Nidhi)',
            'title_hi' => 'प्रधानमंत्री किसान सम्मान निधि (PM-KISAN)',
            'tagline_en' => 'Direct income support of ₹6,000 per year to all landholding farmer families across India.',
            'tagline_hi' => 'सभी भूमिधारक किसान परिवारों को प्रति वर्ष ₹6,000 की सीधी बैंक सहायता।',
            
            'overview_en' => 'PM-KISAN is a flagship Central Sector scheme launched in 2019 to provide income support to all landholding eligible farmer families across the country. An amount of ₹6,000 per year is released directly into the bank accounts of beneficiary farmers through Direct Benefit Transfer (DBT) in three equal installments of ₹2,000 every four months.',
            'overview_hi' => 'प्रधानमंत्री किसान सम्मान निधि (PM-KISAN) भारत सरकार की 100% केंद्रीय वित्तपोषित प्रमुख योजना है। इसके तहत देश के सभी भूमिधारक किसानों को हर साल ₹6,000 की वित्तीय सहायता ₹2,000 की तीन किस्तों में सीधे उनके बैंक खातों में डीबीटी (DBT) के माध्यम से ट्रांसफर की जाती है।',
            
            'benefits_en' => [
                'Direct financial assistance of ₹6,000 per year transferred directly to bank account via DBT.',
                'Paid in 3 equal installments of ₹2,000 each (April-July, August-November, December-March).',
                '100% funded by the Central Government with zero middleman commission.',
                'Linked with Kisan Credit Card (KCC) for subsidized agriculture loans at 4% interest.',
                'Aadhaar-based eKYC and Facial Recognition authentication through the PM-KISAN mobile app.'
            ],
            'benefits_hi' => [
                'प्रति वर्ष ₹6,000 की सीधी वित्तीय सहायता बैंक खाते में डीबीटी (DBT) से ट्रांसफर।',
                '₹2,000 की तीन समान किस्तों में भुगतान (अप्रैल-जुलाई, अगस्त-नवंबर, दिसंबर-मार्च)।',
                '100% केंद्र सरकार द्वारा वित्तपोषित—शून्य कटौती और शून्य बिचौलिया प्रणाली।',
                '4% रियायती ब्याज दर पर कृषि ऋण के लिए किसान क्रेडिट कार्ड (KCC) से सीधा जुड़ाव।',
                'पीएम-किसान मोबाइल ऐप के जरिए आधार आधारित eKYC और फेस ऑथेंटिकेशन की सुविधा।'
            ],
            
            'eligibility_en' => [
                'All landholding farmer families having cultivable landholding in their names.',
                'Both small/marginal and other landholding farmers are eligible.',
                'Exclusion: Institutional landholders, former/present constitutional post holders.',
                'Exclusion: Former and present Ministers, MPs, MLAs, Mayors, and District Panchayat Chairpersons.',
                'Exclusion: All serving or retired government employees (except Group D/MTS).',
                'Exclusion: All persons who paid Income Tax in the last assessment year.'
            ],
            'eligibility_hi' => [
                'सभी भूमिधारक किसान परिवार जिनके नाम पर कृषि योग्य भूमि दर्ज है।',
                'छोटे, सीमांत और अन्य सभी श्रेणी के वास्तविक किसान परिवार पात्र हैं।',
                'अपात्रता: संस्थागत भूमिधारक, पूर्व या वर्तमान संवैधानिक पद धारक।',
                'अपात्रता: पूर्व और वर्तमान मंत्री, सांसद, विधायक, मेयर और जिला पंचायत अध्यक्ष।',
                'अपात्रता: सेवारत या सेवानिवृत्त सरकारी कर्मचारी (ग्रुप D / MTS को छोड़कर)।',
                'अपात्रता: वे सभी व्यक्ति जिन्होंने पिछले वित्तीय वर्ष में आयकर (Income Tax) का भुगतान किया है।'
            ],
            
            'documents_en' => [
                'Aadhaar Card linked with active mobile number.',
                'Landownership Documents (Khatauni / Jamabandi / Land Revenue Records).',
                'Active Savings Bank Account Passbook (seeded with Aadhaar for DBT).',
                'Valid Mobile Number for OTP verification.'
            ],
            'documents_hi' => [
                'सक्रिय मोबाइल नंबर से लिंक आधार कार्ड।',
                'जमीन के स्वामित्व के दस्तावेज (खतौनी / जमाबंदी / भू-अभिलेख)।',
                'सक्रिय बैंक खाता पासबुक (डीबीटी के लिए आधार से जुड़ा हुआ)।',
                'ओटीपी सत्यापन के लिए चालू मोबाइल नंबर।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Visit the official PM-KISAN portal (pmkisan.gov.in) or your local CSC / Jan Seva Kendra.',
                'Step 2: Click on "New Farmer Registration" and verify your Aadhaar Number via OTP.',
                'Step 3: Enter state, district, sub-district, block, and village details.',
                'Step 4: Upload land record details (Khasra/Khatauni number and area of land).',
                'Step 5: Submit the form; state nodal officers verify the land records and initiate payment.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: आधिकारिक पीएम-किसान पोर्टल (pmkisan.gov.in) पर जाएं या नजदीकी जन सेवा केंद्र पर संपर्क करें।',
                'चरण 2: "New Farmer Registration" पर क्लिक करके आधार नंबर और ओटीपी दर्ज करें।',
                'चरण 3: राज्य, जिला, ब्लॉक और गांव का विवरण चुनें।',
                'चरण 4: अपनी जमीन का खसरा/खतौनी नंबर और रकबा दर्ज करें।',
                'चरण 5: फॉर्म सबमिट करें; राज्य सरकार द्वारा सत्यापन के बाद खाते में किस्त आनी शुरू हो जाएगी।'
            ],
            
            'official_url' => 'https://pmkisan.gov.in/',
            'helpline' => '155261 / 011-24300606',
            
            'audio_text_en' => 'Welcome to PM-KISAN Samman Nidhi overview. Under this 100 percent Central Sector scheme, all landholding farmer families receive six thousand rupees per year directly into their bank accounts in three equal installments. Apply online at pmkisan.gov.in with your Aadhaar and land records.',
            'audio_text_hi' => 'प्रधानमंत्री किसान सम्मान निधि योजना में आपका स्वागत है। इस केंद्रीय योजना के तहत सभी भूमिधारक किसानों को प्रति वर्ष ₹6,000 की सीधी सहायता ₹2,000 की तीन किस्तों में बैंक खाते में मिलती है। आधार और खतौनी के साथ आवेदन करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you or your family own cultivable agricultural land registered in your name?',
                'q1_hi' => 'क्या आपके या आपके परिवार के नाम पर कृषि योग्य भूमि दर्ज है?',
                'q2_en' => 'Are you or anyone in your household an Income Tax payer or high-ranking government official?',
                'q2_hi' => 'क्या आप या आपके परिवार का कोई सदस्य इनकम टैक्स भरता है या उच्च सरकारी पद पर है?',
                'expected' => ['q1' => 'yes', 'q2' => 'no'],
                'eligible_msg_en' => 'Congratulations! You are eligible for PM-KISAN ₹6,000 annual income support.',
                'eligible_msg_hi' => 'बधाई हो! आप पीएम-किसान योजना के तहत ₹6,000 वार्षिक सहायता पाने के लिए पूर्ण रूप से पात्र हैं।',
                'ineligible_msg_en' => 'You may not qualify if you do not own land or if you pay Income Tax / hold constitutional posts.',
                'ineligible_msg_hi' => 'यदि आपके नाम जमीन नहीं है या आप आयकर दाता हैं, तो पीएम-किसान नियमों के तहत अपात्र माने जाते हैं।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 2. PM Surya Ghar Muft Bijli Yojana (Vajiram & Ravi 2024 List - Central Sector)
        // ---------------------------------------------------------------------
        'pm-surya-ghar' => [
            'id' => 2,
            'slug' => 'pm-surya-ghar',
            'icon' => 'fa-solar-panel',
            'category' => 'energy_utilities',
            'category_name_en' => 'Renewable Energy & Power',
            'category_name_hi' => 'ऊर्जा एवं नवीन संसाधन',
            'funding_type' => 'central_sector',
            'funding_type_label_en' => 'Central Sector Scheme (100% Central Funded)',
            'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (100% केंद्र सरकार द्वारा वित्तपोषित)',
            'ministry_en' => 'Ministry of New & Renewable Energy',
            'ministry_hi' => 'नवीन एवं नवीकरणीय ऊर्जा मंत्रालय',
            'launch_year' => '2024',
            'budget' => '₹75,021 Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'PM Surya Ghar: Muft Bijli Yojana',
            'title_hi' => 'प्रधानमंत्री सूर्य घर: मुफ्त बिजली योजना',
            'tagline_en' => 'Free electricity up to 300 units per month with direct central rooftop solar subsidy up to ₹78,000.',
            'tagline_hi' => 'हर महीने 300 यूनिट तक मुफ्त बिजली और ₹78,000 तक की सीधी केंद्रीय सोलर सब्सिडी।',
            
            'overview_en' => 'Launched in 2024 with an outlay of ₹75,021 Crore, PM Surya Ghar Muft Bijli Yojana aims to illuminate 1 crore households by providing up to 300 units of free electricity every month through rooftop solar systems. The central government provides direct DBT subsidy of ₹30,000 for 1 kW, ₹60,000 for 2 kW, and ₹78,000 for 3 kW and above systems.',
            'overview_hi' => 'प्रधानमंत्री सूर्य घर मुफ्त बिजली योजना 2024 में ₹75,021 करोड़ के बजट के साथ शुरू की गई। इसका लक्ष्य 1 करोड़ घरों की छतों पर सोलर पैनल लगाकर हर महीने 300 यूनिट तक मुफ्त बिजली उपलब्ध कराना है। केंद्र सरकार 1 किलोवाट पर ₹30,000, 2 किलोवाट पर ₹60,000 और 3 किलोवाट पर ₹78,000 की सीधी बैंक सब्सिडी देती है।',
            
            'benefits_en' => [
                'Up to 300 units of free clean electricity every single month.',
                'Direct bank DBT subsidy: ₹30,000 for 1kW, ₹60,000 for 2kW, and ₹78,000 for 3kW or higher.',
                'Collateral-free low-interest bank loans (around 7% interest) to cover balance installation costs.',
                'Net-metering facility: Sell excess solar power back to the grid and earn additional income.',
                'Estimated annual electricity bill savings of ₹15,000 to ₹18,000 per family.'
            ],
            'benefits_hi' => [
                'हर महीने 300 यूनिट तक बिल्कुल मुफ्त बिजली की सुविधा।',
                'बैंक खाते में सीधी सब्सिडी: 1kW पर ₹30,000, 2kW पर ₹60,000 और 3kW पर ₹78,000।',
                'शेष लागत के लिए बिना किसी गारंटी के मात्र 7% के सस्ते ब्याज दर पर बैंक लोन।',
                'नेट-मीटरिंग सुविधा: अतिरिक्त बिजली ग्रिड को बेचकर हर महीने कमाई का अवसर।',
                'हर परिवार को प्रति वर्ष बिजली बिल में ₹15,000 से ₹18,000 तक की भारी बचत।'
            ],
            
            'eligibility_en' => [
                'Must be an Indian residential household owning a suitable roof/terrace.',
                'Must have an active domestic electricity connection in the applicant\'s or family member\'s name.',
                'The household must not have availed solar rooftop subsidy under any other government scheme previously.',
                'Must possess valid electricity bill of the current connection.'
            ],
            'eligibility_hi' => [
                'भारतीय आवासीय नागरिक जिनके पास खुद की पक्की छत या उपयुक्त स्थान हो।',
                'आवेदक के नाम पर वैध घरेलू बिजली कनेक्शन और बिजली उपभोक्ता संख्या (CA Number) होना अनिवार्य।',
                'परिवार ने पहले किसी अन्य सरकारी योजना के तहत सोलर सब्सिडी का लाभ न लिया हो।',
                'नवीनतम बिजली बिल और वैध आधार कार्ड होना चाहिए।'
            ],
            
            'documents_en' => [
                'Electricity Bill (latest domestic consumer bill).',
                'Aadhaar Card of the electricity connection holder.',
                'Bank Passbook / Cancelled Cheque (for direct subsidy DBT credit).',
                'Proof of rooftop ownership or residence.'
            ],
            'documents_hi' => [
                'नवीनतम घरेलू बिजली बिल (उपभोक्ता संख्या सहित)।',
                'बिजली कनेक्शन धारक का आधार कार्ड।',
                'बैंक खाता पासबुक / कैंसिल चेक (सीधी सब्सिडी पाने के लिए)।',
                'छत या मकान के मालिकाना हक का प्रमाण।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Register on the National Portal (pmsuryaghar.gov.in) with your State, DISCOM name, and Electricity Consumer Account Number.',
                'Step 2: Apply for Rooftop Solar installation and choose a registered vendor.',
                'Step 3: Wait for DISCOM feasibility approval, then get the solar plant installed by vendor.',
                'Step 4: Vendor submits installation details; DISCOM inspects and installs the Net Meter.',
                'Step 5: Commissioning certificate is generated, and the Central subsidy is credited to your bank account within 30 days.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: राष्ट्रीय पोर्टल (pmsuryaghar.gov.in) पर जाकर अपने राज्य, बिजली कंपनी (DISCOM) और उपभोक्ता संख्या से रजिस्टर करें।',
                'चरण 2: रूफटॉप सोलर के लिए आवेदन करें और अपने क्षेत्र के अधिकृत वेंडर को चुनें।',
                'चरण 3: बिजली कंपनी से अप्रूवल मिलने के बाद वेंडर द्वारा छत पर सोलर पैनल लगाए जाएंगे।',
                'चरण 4: बिजली विभाग द्वारा नेट-मीटर लगाया जाएगा और कनेक्शन चालू किया जाएगा।',
                'चरण 5: कमीशनिंग सर्टिफिकेट बनने के 30 दिनों के भीतर केंद्र सरकार की सब्सिडी सीधे आपके बैंक खाते में आ जाएगी।'
            ],
            
            'official_url' => 'https://pmsuryaghar.gov.in/',
            'helpline' => '15555',
            
            'audio_text_en' => 'Welcome to PM Surya Ghar Muft Bijli Yojana overview. Residential homes can install rooftop solar panels and get up to three hundred units of free electricity per month, along with up to seventy-eight thousand rupees direct government subsidy. Apply online at pmsuryaghar.gov.in.',
            'audio_text_hi' => 'पीएम सूर्य घर मुफ्त बिजली योजना में आपका स्वागत है। अपने घर की छत पर सोलर पैनल लगवाकर हर महीने 300 यूनिट तक मुफ्त बिजली पाएं और ₹78,000 तक की सीधी सरकारी सब्सिडी हासिल करें। आधिकारिक पोर्टल pmsuryaghar.gov.in पर आज ही आवेदन करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you have an active domestic electricity connection and an unshaded roof or terrace?',
                'q1_hi' => 'क्या आपके घर में बिजली का मीटर लगा है और आपके पास सोलर पैनल लगाने के लिए पक्की छत है?',
                'q2_en' => 'Have you already claimed government subsidy for rooftop solar in the past?',
                'q2_hi' => 'क्या आपने पहले कभी सरकारी सोलर सब्सिडी का लाभ लिया है?',
                'expected' => ['q1' => 'yes', 'q2' => 'no'],
                'eligible_msg_en' => 'You are eligible for PM Surya Ghar subsidy of up to ₹78,000 and 300 units free power!',
                'eligible_msg_hi' => 'आप पीएम सूर्य घर योजना के तहत ₹78,000 की सब्सिडी और 300 यूनिट मुफ्त बिजली पाने के लिए पूरी तरह पात्र हैं!',
                'ineligible_msg_en' => 'Domestic connection and unused solar subsidy status are mandatory to qualify.',
                'ineligible_msg_hi' => 'योजना का लाभ केवल घरेलू बिजली उपभोक्ताओं और पहली बार सोलर लगवाने वालों के लिए उपलब्ध है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 3. PM Vishwakarma Scheme (Vajiram & Ravi 2023 List - Central Sector)
        // ---------------------------------------------------------------------
        'pm-vishwakarma' => [
            'id' => 3,
            'slug' => 'pm-vishwakarma',
            'icon' => 'fa-tools',
            'category' => 'employment',
            'category_name_en' => 'Artisans & Skill Development',
            'category_name_hi' => 'कारीगर एवं कौशल विकास',
            'funding_type' => 'central_sector',
            'funding_type_label_en' => 'Central Sector Scheme (100% Central Funded)',
            'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (100% केंद्र सरकार द्वारा वित्तपोषित)',
            'ministry_en' => 'Ministry of Micro, Small & Medium Enterprises (MSME)',
            'ministry_hi' => 'सूक्ष्म, लघु एवं मध्यम उद्यम मंत्रालय',
            'launch_year' => '2023',
            'budget' => '₹13,000 Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'PM Vishwakarma Kaushal Samman',
            'title_hi' => 'प्रधानमंत्री विश्वकर्मा कौशल सम्मान योजना',
            'tagline_en' => 'Holistic financial, training, toolkit (₹15,000) and low-interest loan support (5%) to 18 traditional artisan trades.',
            'tagline_hi' => '18 पारंपरिक कारीगरों को ₹15,000 का टूलकिट वाउचर, स्किल ट्रेनिंग और 5% ब्याज पर आसान लोन।',
            
            'overview_en' => 'PM Vishwakarma is a landmark Central Sector initiative launched in 2023 with an outlay of ₹13,000 Crore to preserve Guru-Shishya parampara and support traditional craftspeople working with hands and tools across 18 designated trades. Artisans receive digital recognition, ₹500/day training stipend, ₹15,000 toolkit e-voucher, and collateral-free enterprise loans up to ₹3 Lakh at an attractive concessional interest rate of 5%.',
            'overview_hi' => 'प्रधानमंत्री विश्वकर्मा योजना 2023 में ₹13,000 करोड़ के बजट से शुरू की गई। इसका उद्देश्य हाथ और औजारों से काम करने वाले 18 पारंपरिक कारीगरों (जैसे बढ़ई, लोहार, कुम्हार, दर्जी, मोची, मूर्तिकार आदि) को आर्थिक व सामाजिक सुरक्षा देना है। कारीगरों को विश्वकर्मा प्रमाण-पत्र, ₹15,000 का टूलकिट वाउचर, ₹500 प्रतिदिन का ट्रेनिंग भत्ता और 5% ब्याज पर ₹3 लाख तक का लोन मिलता है।',
            
            'benefits_en' => [
                'Official PM Vishwakarma Certificate and ID Card acknowledging traditional skill.',
                'Basic skill training (5-7 days) and advanced training (15 days) with ₹500 per day daily stipend.',
                'Free e-Voucher of ₹15,000 for purchasing modern toolkits.',
                'Collateral-free Enterprise Loans up to ₹3,00,000 at concessional 5% interest (Tranche 1: ₹1 Lakh, Tranche 2: ₹2 Lakh).',
                'Incentive for digital transactions (₹1 per transaction up to 100 transactions/month) and marketing support.'
            ],
            'benefits_hi' => [
                'आधिकारिक पीएम विश्वकर्मा प्रमाण-पत्र और डिजिटल पहचान पत्र।',
                '5 से 7 दिन की बेसिक ट्रेनिंग और ₹500 प्रतिदिन का भत्ता।',
                'आधुनिक औजार खरीदने के लिए ₹15,000 का मुफ्त टूलकिट ई-वाउचर।',
                'बिना गारंटी 5% की रियायती ब्याज दर पर ₹3 लाख तक का बिजनेस लोन (पहली किस्त ₹1 लाख, दूसरी किस्त ₹2 लाख)।',
                'डिजिटल लेन-देन पर प्रति माह ₹100 तक का कैशबैक प्रोत्साहन और सरकारी ब्रांडिंग।'
            ],
            
            'eligibility_en' => [
                'Artisan/craftsperson working in one of 18 traditional trades (Carpenter, Boat Maker, Blacksmith, Hammer/Tool Maker, Locksmith, Sculptor, Goldsmith, Potter, Cobbler, Mason, Basket/Mat/Broom Maker, Traditional Toy Maker, Barber, Garland Maker, Washerman, Tailor, Fishing Net Maker).',
                'Minimum age of 18 years on the date of application.',
                'Only one family member (husband/wife/unmarried children) can avail benefits.',
                'Must not have availed loans under PMEGP, PM SVANidhi, or MUDRA in the last 5 years.'
            ],
            'eligibility_hi' => [
                '18 पारंपरिक व्यवसायों में से किसी एक में हाथ से काम करने वाले कारीगर (जैसे बढ़ई, लोहार, कुम्हार, दर्जी, मोची, धोबी, नाई, राजमिस्त्री, मूर्तिकार आदि)।',
                'आवेदक की न्यूनतम आयु 18 वर्ष होनी चाहिए।',
                'परिवार में केवल एक सदस्य को योजना का लाभ मिलेगा।',
                'पिछले 5 वर्षों में PMEGP, पीएम स्वनिधि या मुद्रा योजना से लोन न लिया हो।'
            ],
            
            'documents_en' => [
                'Aadhaar Card linked to active mobile number.',
                'Bank Account Passbook (Aadhaar seeded).',
                'Trade Proof / Declaration of working in notified trade.',
                'Ration Card / Family Proof.'
            ],
            'documents_hi' => [
                'मोबाइल से लिंक आधार कार्ड।',
                'आधार से जुड़ा बैंक खाता पासबुक।',
                'संबंधित ट्रेड में काम करने का स्व-घोषणा पत्र।',
                'राशन कार्ड या परिवार पहचान पत्र।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Visit your nearest Common Service Centre (CSC) or Jan Seva Kendra with Aadhaar and bank details.',
                'Step 2: CSC executive performs biometric authentication on the PM Vishwakarma portal.',
                'Step 3: Choose your specific craft trade from the 18 notified artisan categories.',
                'Step 4: Gram Panchayat (rural) or Urban Local Body (urban) verifies your artisan identity in Level-1 screening.',
                'Step 5: District Committee clears Level-2; download your Vishwakarma Card and receive your ₹15,000 toolkit voucher.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: अपने आधार और बैंक पासबुक के साथ नजदीकी सीएससी (जन सेवा केंद्र) पर जाएं।',
                'चरण 2: बायोमेट्रिक फिंगरप्रिंट लगाकर पीएम विश्वकर्मा पोर्टल पर पंजीकरण कराएं।',
                'चरण 3: अपने पारंपरिक व्यवसाय (ट्रेड) का चयन करें।',
                'चरण 4: ग्राम प्रधान / वार्ड सदस्य और जिला समिति द्वारा कारीगर होने का सत्यापन किया जाता है।',
                'चरण 5: सत्यापन पूरा होते ही विश्वकर्मा आईडी कार्ड, ₹15,000 का टूलकिट वाउचर और 5% लोन की सुविधा मिल जाएगी।'
            ],
            
            'official_url' => 'https://pmvishwakarma.gov.in/',
            'helpline' => '1800-267-7777 / 17923',
            
            'audio_text_en' => 'Welcome to PM Vishwakarma Kaushal Samman scheme. Traditional artisans and craftspeople working in eighteen trades get a digital identity card, fifteen thousand rupees toolkit voucher, daily training stipend, and enterprise loans up to three lakh rupees at just five percent interest. Apply at your nearest CSC.',
            'audio_text_hi' => 'पीएम विश्वकर्मा कौशल सम्मान योजना में आपका स्वागत है। बढ़ई, लोहार, कुम्हार, दर्जी और अन्य 18 कारीगरों को ₹15,000 का टूलकिट वाउचर, ₹500 प्रतिदिन का ट्रेनिंग भत्ता और मात्र 5% ब्याज पर ₹3 लाख का लोन मिलता है। नजदीकी जन सेवा केंद्र से आवेदन करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you work in one of the 18 traditional artisan trades (such as carpenter, tailor, blacksmith, potter, mason, etc.)?',
                'q1_hi' => 'क्या आप 18 पारंपरिक कारीगरी व्यवसायों (जैसे बढ़ई, दर्जी, लोहार, कुम्हार, राजमिस्त्री आदि) में काम करते हैं?',
                'q2_en' => 'Are you at least 18 years old without active unpaid government micro-loans?',
                'q2_hi' => 'क्या आपकी आयु 18 वर्ष या अधिक है और कोई पुराना सरकारी लोन डिफॉल्ट नहीं है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible for PM Vishwakarma ID, ₹15,000 toolkit voucher, and 5% loans!',
                'eligible_msg_hi' => 'आप पीएम विश्वकर्मा योजना के तहत ₹15,000 का टूलकिट वाउचर और 5% पर लोन पाने के लिए पूरी तरह पात्र हैं!',
                'ineligible_msg_en' => 'Scheme is reserved exclusively for artisans belonging to the 18 notified traditional trades.',
                'ineligible_msg_hi' => 'यह योजना केवल 18 पारंपरिक शिल्पकला व कारीगरी व्यवसायों से जुड़े व्यक्तियों के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 4. Ayushman Bharat PM-JAY (Centrally Sponsored)
        // ---------------------------------------------------------------------
        'ayushman-bharat' => [
            'id' => 4,
            'slug' => 'ayushman-bharat',
            'icon' => 'fa-heartbeat',
            'category' => 'healthcare',
            'category_name_en' => 'Healthcare & Wellness',
            'category_name_hi' => 'स्वास्थ्य एवं परिवार कल्याण',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Shared 60:40 Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (60:40 केंद्र और राज्य सरकार द्वारा साझा)',
            'ministry_en' => 'Ministry of Health and Family Welfare',
            'ministry_hi' => 'स्वास्थ्य एवं परिवार कल्याण मंत्रालय',
            'launch_year' => '2018',
            'budget' => '₹64,180 Crore (National Health Outlay)',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'Haryana', 'Punjab', 'Gujarat', 'Jharkhand'],
            
            'title_en' => 'Ayushman Bharat PM-JAY',
            'title_hi' => 'आयुष्मान भारत - प्रधानमंत्री जन आरोग्य योजना (PM-JAY)',
            'tagline_en' => 'World’s largest health assurance scheme providing ₹5 Lakh free cashless treatment per family per year.',
            'tagline_hi' => 'विश्व की सबसे बड़ी स्वास्थ्य सुरक्षा योजना—प्रति वर्ष प्रति परिवार ₹5 लाख तक का मुफ्त इलाज।',
            
            'overview_en' => 'Ayushman Bharat Pradhan Mantri Jan Arogya Yojana (PM-JAY) provides health assurance cover of up to ₹5 Lakh per family per year for secondary and tertiary care hospitalization to over 12 Crore poor and vulnerable families (bottom 40% of population) based on SECC 2011 deprivation criteria. Treatment is completely cashless and paperless across all empanelled government and private hospitals across India.',
            'overview_hi' => 'आयुष्मान भारत प्रधानमंत्री जन आरोग्य योजना (PM-JAY) देश के 12 करोड़ से अधिक गरीब और कमजोर परिवारों को माध्यमिक और तृतीयक अस्पताल में भर्ती के लिए प्रति वर्ष प्रति परिवार ₹5 लाख तक का कैशलेस स्वास्थ्य बीमा प्रदान करती है। देश भर के 28,000 से अधिक सूचीबद्ध सरकारी और निजी अस्पतालों में इलाज पूरी तरह मुफ्त और कैशलेस है।',
            
            'benefits_en' => [
                'Cashless coverage of up to ₹5,00,000 per family per year for secondary and tertiary hospitalization.',
                'Over 1,949 medical and surgical procedures covered including cardiology, oncology, and neurosurgery.',
                'All pre-existing diseases covered from day one of enrolment.',
                'Includes 3 days of pre-hospitalization and 15 days of post-hospitalization diagnostic and medicine costs.',
                'Valid across 28,000+ empanelled public and private hospitals anywhere in India (National Portability).'
            ],
            'benefits_hi' => [
                'प्रति वर्ष प्रति परिवार ₹5,00,000 तक का पूर्ण कैशलेस अस्पताल इलाज।',
                'हार्ट सर्जरी, कैंसर, न्यूरोलॉजी सहित 1,949 से अधिक गंभीर बीमारियों और ऑपरेशनों का इलाज।',
                'पहले से मौजूद सभी बीमारियां पहले ही दिन से कवर (कोई वेटिंग पीरियड नहीं)।',
                'अस्पताल में भर्ती होने से 3 दिन पहले और छुट्टी मिलने के 15 दिन बाद तक की जांचें और दवाइयां मुफ्त।',
                'पूरे भारत में किसी भी सूचीबद्ध सरकारी या निजी अस्पताल में मान्य (राष्ट्रीय पोर्टेबिलिटी)।'
            ],
            
            'eligibility_en' => [
                'Households identified as deprived in rural areas or meeting occupational criteria in urban areas as per SECC 2011.',
                'All members of NFSA Priority Ration Card or Antyodaya Anna Yojana (AAY) in participating states.',
                'Extended to all citizens aged 70 years and above irrespective of family income.',
                'No cap on family size, gender, or age of family members.'
            ],
            'eligibility_hi' => [
                'SECC 2011 सामाजिक-आर्थिक जाति जनगणना के अनुसार ग्रामीण और शहरी वंचित परिवार।',
                'अंत्योदय (AAY) और प्राथमिकता राशन कार्ड धारक पात्र परिवार।',
                '70 वर्ष या उससे अधिक आयु के सभी वरिष्ठ नागरिकों को आय सीमा के बिना आयुष्मान कवर।',
                'परिवार के सदस्यों की संख्या या आयु पर कोई सीमा नहीं।'
            ],
            
            'documents_en' => [
                'Aadhaar Card (or Ration Card / Voter ID).',
                'Ration Card (NFSA / BPL / Antyodaya).',
                'PM-JAY Family ID / Ration Card number.',
                'Active mobile number for OTP authentication.'
            ],
            'documents_hi' => [
                'आधार कार्ड (परिवार के सभी सदस्यों का)।',
                'राशन कार्ड (पात्र गृहस्थी या अंत्योदय राशन कार्ड)।',
                'पीएम-जय परिवार पहचान संख्या (यदि उपलब्ध हो)।',
                'ओटीपी के लिए चालू मोबाइल नंबर।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Check your eligibility on beneficiary.nha.gov.in using your mobile number or Ration Card number.',
                'Step 2: Visit any Empanelled Health Care Provider (EHCP), Ayushman Mitra kiosk, or CSC Jan Seva Kendra.',
                'Step 3: Provide Aadhaar and complete biometric eKYC verification.',
                'Step 4: Ayushman Card approval is granted instantly or within 24 hours.',
                'Step 5: Download and print your official plastic PVC Ayushman Card with QR code.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: आधिकारिक पोर्टल beneficiary.nha.gov.in पर जाकर अपने मोबाइल या राशन कार्ड नंबर से नाम चेक करें।',
                'चरण 2: किसी भी सरकारी अस्पताल में आयुष्मान मित्र डेस्क या नजदीकी जन सेवा केंद्र पर जाएं।',
                'चरण 3: आधार कार्ड देकर फिंगरप्रिंट या ओटीपी से eKYC पूरा करें।',
                'चरण 4: आयुष्मान कार्ड का सत्यापन 24 घंटे के भीतर मंजूर हो जाता है।',
                'चरण 5: अपना डिजिटल या प्लास्टिक पीवीसी आयुष्मान कार्ड डाउनलोड और प्रिंट कराएं।'
            ],
            
            'official_url' => 'https://pmjay.gov.in/',
            'helpline' => '14555 / 1800-111-565',
            
            'audio_text_en' => 'Welcome to Ayushman Bharat PM-JAY. Eligible families receive five lakh rupees worth of free hospitalization and treatment every year across twenty-eight thousand empanelled hospitals. Check your name at beneficiary.nha.gov.in and download your golden card.',
            'audio_text_hi' => 'आयुष्मान भारत प्रधानमंत्री जन आरोग्य योजना में आपका स्वागत है। पात्र परिवारों को हर साल देश के 28 हजार से अधिक अस्पतालों में ₹5 लाख तक का मुफ्त इलाज मिलता है। beneficiary.nha.gov.in पर नाम चेक करें और अपना आयुष्मान कार्ड बनवाएं।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you possess a valid Ration Card (Antyodaya or Priority NFSA) or does your family appear on the SECC list?',
                'q1_hi' => 'क्या आपके पास अंत्योदय या पात्र गृहस्थी राशन कार्ड है या आपका नाम SECC सूची में दर्ज है?',
                'q2_en' => 'Is there any member in your family aged 70 years or above?',
                'q2_hi' => 'क्या आपके परिवार में कोई सदस्य 70 वर्ष या उससे अधिक आयु का है?',
                'expected' => ['q1' => 'yes', 'q2' => 'any'],
                'eligible_msg_en' => 'You are eligible for Ayushman Bharat ₹5 Lakh cashless health cover!',
                'eligible_msg_hi' => 'आप आयुष्मान भारत ₹5 लाख कैशलेस स्वास्थ्य कार्ड पाने के लिए पात्र हैं!',
                'ineligible_msg_en' => 'Eligibility requires inclusion in the SECC database or active NFSA ration card.',
                'ineligible_msg_hi' => 'पात्रता के लिए SECC डेटाबेस या राशन कार्ड में नाम होना आवश्यक है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 5. Pradhan Mantri Awas Yojana (PMAY-Gramin / Urban - Vajiram List)
        // ---------------------------------------------------------------------
        'pmay-gramin' => [
            'id' => 5,
            'slug' => 'pmay-gramin',
            'icon' => 'fa-home',
            'category' => 'housing',
            'category_name_en' => 'Housing & Rural Development',
            'category_name_hi' => 'आवास एवं ग्रामीण विकास',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (60:40 Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (60:40 केंद्र और राज्य सरकार द्वारा साझा)',
            'ministry_en' => 'Ministry of Rural Development & MoHUA',
            'ministry_hi' => 'ग्रामीण विकास मंत्रालय एवं आवासन कार्य मंत्रालय',
            'launch_year' => '2016',
            'budget' => '₹6.85 Lakh Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'Pradhan Mantri Awas Yojana (PMAY)',
            'title_hi' => 'प्रधानमंत्री आवास योजना (PMAY - ग्रामीण एवं शहरी)',
            'tagline_en' => 'Financial assistance of ₹1.20 Lakh to ₹1.30 Lakh for building a pucca house with basic amenities.',
            'tagline_hi' => 'पक्का मकान बनाने के लिए ₹1.20 लाख से ₹1.30 लाख की सीधी सरकारी सहायता।',
            
            'overview_en' => 'PMAY aims to achieve Housing for All by providing financial assistance to all houseless households and those households living in kutcha and dilapidated houses in rural and urban areas. Under PMAY-G, ₹1.20 Lakh is provided in plain areas and ₹1.30 Lakh in hilly/difficult areas, along with 90-95 days of unskilled labor wages under MGNREGA and ₹12,000 for toilet construction.',
            'overview_hi' => 'प्रधानमंत्री आवास योजना (PMAY) का उद्देश्य सभी बेघर और कच्चे मकानों में रहने वाले परिवारों को बुनियादी सुविधाओं से युक्त पक्का मकान उपलब्ध कराना है। ग्रामीण क्षेत्रों में मैदानी इलाकों के लिए ₹1.20 लाख और पहाड़ी/दुर्गम इलाकों के लिए ₹1.30 लाख की सहायता दी जाती है। साथ ही मनरेगा के तहत 90-95 दिनों की मजदूरी और शौचालय के लिए ₹12,000 अलग से मिलते हैं।',
            
            'benefits_en' => [
                'Direct cash assistance of ₹1,20,000 (Plains) or ₹1,30,000 (Hilly/Himalayan states) transferred in 3-4 installments.',
                'Additional 90 to 95 days of unskilled wage labor support under MGNREGA (worth approx. ₹25,000).',
                '₹12,000 additional assistance for building a toilet under Swachh Bharat Mission (Gramin).',
                'LPG gas connection under PM Ujjwala Yojana and electricity connection under Saubhagya.',
                'Tap water connection under Jal Jeevan Mission.'
            ],
            'benefits_hi' => [
                'मैदानी इलाकों में ₹1,20,000 और पहाड़ी राज्यों में ₹1,30,000 की सीधी नकद सहायता 3-4 किस्तों में।',
                'मनरेगा के तहत 90 से 95 दिनों की अकुशल मजदूरी (लगभग ₹25,000 की अतिरिक्त सहायता)।',
                'स्वच्छ भारत मिशन के तहत शौचालय निर्माण के लिए ₹12,000 की अलग से सहायता।',
                'उज्ज्वला योजना से मुफ्त रसोई गैस और सौभाग्य योजना से बिजली कनेक्शन।',
                'जल जीवन मिशन से घर पर नल से जल की सुविधा।'
            ],
            
            'eligibility_en' => [
                'Houseless households or households living in zero, one, or two rooms with kutcha wall and kutcha roof.',
                'Beneficiary or any member of the family must not own a pucca house anywhere in India.',
                'Exclusion: Households owning motorized vehicles, mechanized agricultural equipment, or Kisan Credit Card with limit ≥ ₹50,000.',
                'Exclusion: Any household member earning > ₹15,000 per month or paying income/professional tax.'
            ],
            'eligibility_hi' => [
                'बेघर परिवार या कच्चे मकान में रहने वाले ऐसे परिवार जिनके पास पक्की छत वाला घर नहीं है।',
                'परिवार के किसी भी सदस्य के नाम पूरे भारत में कोई पक्का मकान नहीं होना चाहिए।',
                'अपात्रता: 2/3/4 पहिया वाहन, 50,000 से अधिक की केसीसी लिमिट या ट्रैक्टर रखने वाले परिवार।',
                'अपात्रता: परिवार का कोई सदस्य सरकारी नौकरी या ₹15,000 से अधिक मासिक आय वाला न हो।'
            ],
            
            'documents_en' => [
                'Aadhaar Card of all family members.',
                'Bank Account Passbook (Aadhaar linked for DBT).',
                'MGNREGA Job Card Number.',
                'Swachh Bharat Mission (SBM) toilet registration number.',
                'Land ownership certificate or gram sabha allotment resolution.'
            ],
            'documents_hi' => [
                'परिवार के सभी सदस्यों का आधार कार्ड।',
                'आधार से लिंक बैंक खाता पासबुक।',
                'मनरेगा जॉब कार्ड नंबर।',
                'शौचालय पंजीकरण संख्या।',
                'जमीन का पट्टा या ग्राम सभा का आवंटन प्रस्ताव।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Check inclusion in the Awaas+ priority list with your Gram Panchayat Secretary or Gram Rozgar Sahayak.',
                'Step 2: Gram Rozgar Sahayak geotags your existing kutcha house using the AwaasApp.',
                'Step 3: Registration and Aadhaar verification completed on pmayg.nic.in portal.',
                'Step 4: Sanction order issued; 1st installment (approx. ₹40,000) credited to start foundation.',
                'Step 5: Subsequent installments credited after intermediate geotagging of plinth and lintel levels.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: अपनी ग्राम पंचायत के सचिव या ग्राम रोजगार सहायक से आवास प्लस सूची में नाम जांचें।',
                'चरण 2: ग्राम रोजगार सहायक द्वारा AwaasApp के माध्यम से आपके कच्चे घर की जियोटैगिंग की जाएगी।',
                'चरण 3: पोर्टल पर आधार और बैंक खाते का ऑनलाइन सत्यापन किया जाएगा।',
                'चरण 4: आवास स्वीकृति के बाद नींव भरने के लिए पहली किस्त (लगभग ₹40,000) बैंक में आएगी।',
                'चरण 5: काम की प्रगति के साथ जियोटैगिंग के बाद दूसरी और तीसरी किस्त खाते में जारी की जाएगी।'
            ],
            
            'official_url' => 'https://pmayg.nic.in/',
            'helpline' => '1800-11-6446',
            
            'audio_text_en' => 'Welcome to Pradhan Mantri Awas Yojana overview. Rural and urban families without a pucca house receive up to one lakh thirty thousand rupees along with MGNREGA wages and toilet assistance to construct a durable home. Verify your name with your local Gram Panchayat.',
            'audio_text_hi' => 'प्रधानमंत्री आवास योजना में आपका स्वागत है। जिन गरीब परिवारों के पास पक्का मकान नहीं है, उन्हें सरकार ₹1.20 लाख से ₹1.30 लाख की नकद सहायता, मनरेगा मजदूरी और शौचालय निर्माण के पैसे देती है। अपनी ग्राम पंचायत से पात्रता जांचें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you or your family currently live in a kutcha house or do not own any pucca house in India?',
                'q1_hi' => 'क्या आप कच्चे मकान में रहते हैं और आपके परिवार के पास पूरे भारत में कोई पक्का घर नहीं है?',
                'q2_en' => 'Does any member of your household own a car/tractor or pay Income Tax?',
                'q2_hi' => 'क्या आपके परिवार में किसी के पास कार, ट्रैक्टर है या कोई सरकारी नौकरी/टैक्सपेयर है?',
                'expected' => ['q1' => 'yes', 'q2' => 'no'],
                'eligible_msg_en' => 'You are eligible for PMAY ₹1.20L - ₹1.30L pucca house construction assistance!',
                'eligible_msg_hi' => 'आप पीएम आवास योजना के तहत ₹1.20 लाख से ₹1.30 लाख की आवास सहायता पाने के लिए पात्र हैं!',
                'ineligible_msg_en' => 'PMAY requires applicant to be houseless or living in kutcha dwellings with no tax liabilities.',
                'ineligible_msg_hi' => 'पीएम आवास योजना केवल कच्चे मकानों में रहने वाले जरूरतमंद परिवारों के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 6. PM MUDRA Yojana (Vajiram List - Central Sector)
        // ---------------------------------------------------------------------
        'pm-mudra-yojana' => [
            'id' => 6,
            'slug' => 'pm-mudra-yojana',
            'icon' => 'fa-coins',
            'category' => 'financial_services',
            'category_name_en' => 'Financial Inclusion & Micro-Credit',
            'category_name_hi' => 'वित्तीय समावेशन एवं ऋण',
            'funding_type' => 'central_sector',
            'funding_type_label_en' => 'Central Sector Scheme (100% Central Funded)',
            'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (100% केंद्र सरकार द्वारा वित्तपोषित)',
            'ministry_en' => 'Ministry of Finance (Department of Financial Services)',
            'ministry_hi' => 'वित्त मंत्रालय (वित्तीय सेवाएं विभाग)',
            'launch_year' => '2015',
            'budget' => '₹3 Lakh Crore+ Annually',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'Pradhan Mantri MUDRA Yojana (PMMY)',
            'title_hi' => 'प्रधानमंत्री मुद्रा योजना (PMMY)',
            'tagline_en' => 'Collateral-free business loans up to ₹20 Lakh across Shishu, Kishore, and Tarun categories.',
            'tagline_hi' => 'बिना किसी गारंटी के ₹20 लाख तक का बिजनेस लोन (शिशु, किशोर और तरुण श्रेणी)।',
            
            'overview_en' => 'PMMY enables micro and small enterprises to access collateral-free institutional credit up to ₹10 Lakh (extended up to ₹20 Lakh for entrepreneurs who have successfully repaid Tarun loans). Loans are classified under three tiers: Shishu (loans up to ₹50,000), Kishore (loans ₹50,001 to ₹5,00,000), and Tarun (loans ₹5,00,001 to ₹10,00,000 / ₹20,00,000).',
            'overview_hi' => 'प्रधानमंत्री मुद्रा योजना (PMMY) छोटे व्यापारियों, दुकानदारों और नए उद्यमियों को अपना व्यवसाय शुरू करने या बढ़ाने के लिए बिना किसी गारंटी (कोलैटरल) के ₹20 लाख तक का बैंक लोन उपलब्ध कराती है। इसमें तीन श्रेणियां हैं: शिशु लोन (₹50,000 तक), किशोर लोन (₹50,001 से ₹5 लाख तक) और तरुण लोन (₹5 लाख से ₹10/₹20 लाख तक)।',
            
            'benefits_en' => [
                '100% collateral-free credit: No property pledge or third-party guarantee required.',
                'Flexible credit tranches: Shishu (up to ₹50k), Kishore (up to ₹5L), Tarun (up to ₹20L).',
                'MUDRA RuPay Debit Card provided for working capital withdrawals at ATMs.',
                'Nominal processing fees and competitive commercial interest rates.',
                'Over 48 Crore loans sanctioned, with over 68% going to women entrepreneurs.'
            ],
            'benefits_hi' => [
                '100% बिना किसी गारंटी का लोन: किसी जमीन या मकान को गिरवी रखने की जरूरत नहीं।',
                'तीन श्रेणियों में लोन: शिशु (₹50,000 तक), किशोर (₹5 लाख तक), और तरुण (₹20 लाख तक)।',
                'कार्यशील पूंजी के लिए एटीएम से पैसे निकालने हेतु RuPay मुद्रा डेबिट कार्ड।',
                'शून्य या नाममात्र प्रोसेसिंग फीस और रियायती ब्याज दरें।',
                'अब तक 48 करोड़ से अधिक लोन स्वीकृत, जिनमें 68% से अधिक महिला उद्यमी लाभार्थी।'
            ],
            
            'eligibility_en' => [
                'Any Indian citizen with a viable business plan for non-farm income generating activity.',
                'Small manufacturing units, shopkeepers, fruit/vegetable vendors, transport operators, artisans, food processors.',
                'Must have an active savings/current bank account with no past bank loan default.',
                'Age of applicant should be 18 years or above.'
            ],
            'eligibility_hi' => [
                'कोई भी भारतीय नागरिक जिसके पास गैर-कृषि व्यवसाय या दुकान का व्यावहारिक विचार हो।',
                'दुकानदार, फल-सब्जी विक्रेता, छोटे कारीगर, रिपेयर शॉप, ब्यूटी पार्लर, ट्रांसपोर्ट ऑपरेटर आदि।',
                'किसी भी बैंक में पिछला कोई लोन डिफॉल्ट या एनपीए (NPA) रिकॉर्ड नहीं होना चाहिए।',
                'आवेदक की न्यूनतम आयु 18 वर्ष होनी चाहिए।'
            ],
            
            'documents_en' => [
                'Aadhaar Card and PAN Card.',
                'Passport-size photographs.',
                'Proof of Business Address / Trade License / Udyam Registration (if existing).',
                'Bank statement of the last 6 months.'
            ],
            'documents_hi' => [
                'आधार कार्ड और पैन कार्ड।',
                'पासपोर्ट साइज फोटो।',
                'दुकान या व्यवसाय के पते का प्रमाण / उद्यम आधार (यदि उपलब्ध हो)।',
                'पिछले 6 महीने का बैंक स्टेटमेंट।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Prepare your business proposal and choose the loan category (Shishu, Kishore, or Tarun).',
                'Step 2: Visit any commercial bank, Regional Rural Bank (RRB), Small Finance Bank, or apply online at udyamimitra.in.',
                'Step 3: Submit the standard MUDRA application form with KYC documents and quotation for machinery/tools.',
                'Step 4: Bank verifies business feasibility and sanctions the loan without asking for collateral.',
                'Step 5: Amount is disbursed into your account and MUDRA Card is issued.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: अपने व्यवसाय की योजना तैयार करें और लोन श्रेणी (शिशु, किशोर, या तरुण) का चयन करें।',
                'चरण 2: किसी भी बैंक शाखा में जाएं या udyamimitra.in पोर्टल पर ऑनलाइन आवेदन करें।',
                'चरण 3: मुद्रा फॉर्म के साथ आधार, पैन और सामान/मशीन का कोटेशन जमा करें।',
                'चरण 4: बैंक आपके आवेदन की जांच करेगा और बिना गारंटी लोन स्वीकृत करेगा।',
                'चरण 5: लोन की राशि सीधे बैंक खाते में ट्रांसफर कर दी जाएगी।'
            ],
            
            'official_url' => 'https://www.mudra.org.in/',
            'helpline' => '1800-180-1111 / 1800-11-0001',
            
            'audio_text_en' => 'Welcome to Pradhan Mantri MUDRA Yojana overview. Small entrepreneurs and shopkeepers can avail collateral-free loans up to twenty lakh rupees under Shishu, Kishore, and Tarun categories. Apply at any bank branch or online on the Udyami Mitra portal.',
            'audio_text_hi' => 'प्रधानमंत्री मुद्रा योजना में आपका स्वागत है। छोटे व्यवसायी, दुकानदार और नए उद्यमी बिना किसी गारंटी के ₹50,000 से लेकर ₹20 लाख तक का बिजनेस लोन ले सकते हैं। अपने नजदीकी बैंक में या udyamimitra.in पर आवेदन करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you run or plan to start a non-farming micro-business, shop, or manufacturing service?',
                'q1_hi' => 'क्या आप कोई दुकान, छोटा व्यवसाय, सर्विस सेंटर या मैन्युफैक्चरिंग काम करते हैं या शुरू करना चाहते हैं?',
                'q2_en' => 'Are you free of any past unresolved bank loan defaults?',
                'q2_hi' => 'क्या आपका पिछला कोई बैंक लोन डिफॉल्ट या एनपीए (NPA) बकाया नहीं है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible to apply for a collateral-free MUDRA loan up to ₹20 Lakh!',
                'eligible_msg_hi' => 'आप बिना किसी गारंटी के ₹20 लाख तक का मुद्रा बिजनेस लोन पाने के लिए पात्र हैं!',
                'ineligible_msg_en' => 'Clean past credit history and non-farm commercial proposal are required.',
                'ineligible_msg_hi' => 'मुद्रा लोन के लिए साफ क्रेडिट रिकॉर्ड और गैर-कृषि व्यावसायिक योजना होना आवश्यक है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 7. MGNREGA (Vajiram List - Centrally Sponsored)
        // ---------------------------------------------------------------------
        'mgnrega' => [
            'id' => 7,
            'slug' => 'mgnrega',
            'icon' => 'fa-hard-hat',
            'category' => 'employment',
            'category_name_en' => 'Employment & Livelihood',
            'category_name_hi' => 'रोजगार एवं आजीविका',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Shared Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (केंद्र एवं राज्य सरकार द्वारा साझा)',
            'ministry_en' => 'Ministry of Rural Development',
            'ministry_hi' => 'ग्रामीण विकास मंत्रालय',
            'launch_year' => '2006',
            'budget' => '₹86,000 Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'MGNREGA (Mahatma Gandhi NREGA)',
            'title_hi' => 'महात्मा गांधी राष्ट्रीय ग्रामीण रोजगार गारंटी अधिनियम (मनरेगा)',
            'tagline_en' => 'Legal guarantee of 100 days of unskilled wage employment per financial year to rural households.',
            'tagline_hi' => 'ग्रामीण परिवारों को प्रति वर्ष 100 दिनों के अकुशल मजदूरी रोजगार की कानूनी गारंटी।',
            
            'overview_en' => 'MGNREGA is one of the world\'s largest rights-based public employment programs enacted under statutory law to guarantee at least 100 days of wage employment in a financial year to every rural household whose adult members volunteer to do unskilled manual work. If work is not provided within 15 days of applying, an unemployment allowance is legally mandated.',
            'overview_hi' => 'महात्मा गांधी नरेगा (MGNREGA) एक कानूनी अधिकार आधारित योजना है जो प्रत्येक ग्रामीण परिवार को एक वित्तीय वर्ष में कम से कम 100 दिनों के गारंटीशुदा अकुशल रोजगार का कानूनी अधिकार देती है। यदि आवेदन करने के 15 दिनों के भीतर काम नहीं दिया जाता, तो सरकार द्वारा कानूनी तौर पर बेरोजगारी भत्ता दिया जाता है।',
            
            'benefits_en' => [
                'Statutory legal guarantee of 100 days of manual wage employment per household per year.',
                'Direct bank transfer of state-notified minimum wages directly into workers\' bank/post-office accounts via ABPS.',
                'Equal wages for men and women with statutory workplace facilities (creche, shade, first-aid, drinking water).',
                'Unemployment allowance payable by the State Government if work is not allotted within 15 days of application.',
                'Creation of durable community assets: farm ponds, check dams, plantations, rural roads, and individual wells.'
            ],
            'benefits_hi' => [
                'प्रति परिवार प्रति वर्ष 100 दिनों के शारीरिक काम की कानूनी गारंटी।',
                'मजदूरी का भुगतान बिना किसी बिचौलिये के सीधे बैंक या डाकघर खाते में आधार-बेस्ड पेमेंट सिस्टम (ABPS) से।',
                'महिला और पुरुष को समान मजदूरी, और कार्यस्थल पर पीने का पानी, छाया और प्राथमिक चिकित्सा की सुविधा।',
                'आवेदन के 15 दिनों के भीतर काम न मिलने पर राज्य सरकार द्वारा बेरोजगारी भत्ता पाने का कानूनी अधिकार।',
                'गांव में खेत-तालाब, मेड़बंदी, सड़क, कुएं और पौधारोपण जैसे उपयोगी विकास कार्य।'
            ],
            
            'eligibility_en' => [
                'Must be an adult resident (18 years or older) of a rural area.',
                'Willing to undertake unskilled manual labor.',
                'Household must apply for and hold a valid MGNREGA Job Card issued by the local Gram Panchayat.',
                'No discrimination based on gender, caste, or religion.'
            ],
            'eligibility_hi' => [
                'ग्रामीण क्षेत्र का स्थायी निवासी होना चाहिए और आयु 18 वर्ष से अधिक हो।',
                'शारीरिक व अकुशल मजदूरी करने का इच्छुक हो।',
                'परिवार के पास ग्राम पंचायत द्वारा जारी वैध मनरेगा जॉब कार्ड होना अनिवार्य है।'
            ],
            
            'documents_en' => [
                'Aadhaar Card (linked to bank account for ABPS payments).',
                'MGNREGA Job Card.',
                'Bank Account Passbook / Post Office Account Passbook.',
                'Passport-size photographs of adult family members.'
            ],
            'documents_hi' => [
                'बैंक खाते से लिंक आधार कार्ड।',
                'ग्राम पंचायत द्वारा जारी मनरेगा जॉब कार्ड।',
                'बैंक या पोस्ट ऑफिस खाता पासबुक।',
                'परिवार के बालिग सदस्यों की फोटो।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Submit a simple application on plain paper to the Gram Panchayat with photos and Aadhaar.',
                'Step 2: Gram Panchayat verifies local residence and issues a Job Card within 15 days free of cost.',
                'Step 3: Submit a dated written work application to the Panchayat Program Officer.',
                'Step 4: Receive a dated receipt; work must be provided within 5 km of the village within 15 days.',
                'Step 5: Attend daily muster roll attendance; weekly wages are transferred straight to your bank account.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: ग्राम पंचायत कार्यालय या ग्राम प्रधान को आधार कार्ड और फोटो के साथ जॉब कार्ड हेतु आवेदन दें।',
                'चरण 2: ग्राम पंचायत 15 दिनों के भीतर बिल्कुल मुफ्त जॉब कार्ड जारी करती है।',
                'चरण 3: काम मांगने के लिए पंचायत में तारीख सहित आवेदन पत्र जमा करें और रसीद लें।',
                'चरण 4: 15 दिनों के भीतर गांव के 5 किलोमीटर के दायरे में काम मिलना अनिवार्य है।',
                'चरण 5: मस्टरोल पर हाजिरी दर्ज होगी और हर हफ्ते मजदूरी सीधे आपके बैंक खाते में भेजी जाएगी।'
            ],
            
            'official_url' => 'https://nrega.nic.in/',
            'helpline' => '1800-111-555',
            
            'audio_text_en' => 'Welcome to MGNREGA overview. Rural households have a legal guarantee to receive one hundred days of wage employment every financial year. Apply for a free Job Card at your local Gram Panchayat office to demand work.',
            'audio_text_hi' => 'महात्मा गांधी नरेगा में आपका स्वागत है। ग्रामीण परिवारों को हर साल 100 दिनों के रोजगार की कानूनी गारंटी मिलती है। अपनी ग्राम पंचायत से मुफ्त जॉब कार्ड बनवाएं और काम की मांग करें। 15 दिन में काम न मिलने पर बेरोजगारी भत्ता पाएं।',
            
            'eligibility_checker' => [
                'q1_en' => 'Are you an adult resident of a rural village willing to do unskilled manual work?',
                'q1_hi' => 'क्या आप ग्रामीण क्षेत्र में रहते हैं और शारीरिक मजदूरी काम करने के इच्छुक हैं?',
                'q2_en' => 'Do you have an Aadhaar card linked with your active bank or post-office account?',
                'q2_hi' => 'क्या आपके पास आधार से लिंक बैंक या डाकघर खाता है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible to obtain a Job Card and demand 100 days of guaranteed work under MGNREGA!',
                'eligible_msg_hi' => 'आप मनरेगा के तहत जॉब कार्ड बनवाने और 100 दिनों के गारंटी रोजगार पाने के लिए पूरी तरह पात्र हैं!',
                'ineligible_msg_en' => 'MGNREGA is applicable exclusively for adult rural residents seeking manual work.',
                'ineligible_msg_hi' => 'मनरेगा योजना केवल ग्रामीण क्षेत्र के बालिग नागरिकों के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 8. PM Kaushal Vikas Yojana (PMKVY 4.0 - Vajiram List - Central Sector)
        // ---------------------------------------------------------------------
        'pm-kaushal-vikas' => [
            'id' => 8,
            'slug' => 'pm-kaushal-vikas',
            'icon' => 'fa-graduation-cap',
            'category' => 'employment',
            'category_name_en' => 'Skill Development & Youth',
            'category_name_hi' => 'कौशल विकास एवं युवा',
            'funding_type' => 'central_sector',
            'funding_type_label_en' => 'Central Sector Scheme (100% Central Funded)',
            'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (100% केंद्र सरकार द्वारा वित्तपोषित)',
            'ministry_en' => 'Ministry of Skill Development & Entrepreneurship',
            'ministry_hi' => 'कौशल विकास एवं उद्यमशीलता मंत्रालय',
            'launch_year' => '2015',
            'budget' => '₹12,000 Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'Pradhan Mantri Kaushal Vikas Yojana (PMKVY 4.0)',
            'title_hi' => 'प्रधानमंत्री कौशल विकास योजना (PMKVY)',
            'tagline_en' => '100% free industry-aligned skill certification, technical training, and placement support for Indian youth.',
            'tagline_hi' => 'भारतीय युवाओं के लिए 100% मुफ्त तकनीकी प्रशिक्षण, सरकारी सर्टिफिकेट और रोजगार सहायता।',
            
            'overview_en' => 'PMKVY is the flagship skill certification scheme launched to enable Indian youth to take up industry-relevant skill training that helps them secure a better livelihood. Under PMKVY 4.0, training covers on-job training, industry partnerships, and courses in cutting-edge 4.0 technologies like coding, AI, robotics, mechatronics, IoT, 3D printing, and drones, along with traditional technical trades.',
            'overview_hi' => 'प्रधानमंत्री कौशल विकास योजना (PMKVY) देश के युवाओं को उद्योग से जुड़े आधुनिक हुनर सिखाने की प्रमुख योजना है। इसके तहत युवाओं को बिल्कुल मुफ्त तकनीकी ट्रेनिंग, सरकारी मान्यता प्राप्त एनएसडीसी (NSDC) सर्टिफिकेट, ट्रेनिंग भत्ता और नौकरी के अवसर दिए जाते हैं। नए चरण (PMKVY 4.0) में ड्रोन तकनीक, कोडिंग, एआई और रोबोटिक्स के कोर्स भी शामिल हैं।',
            
            'benefits_en' => [
                '100% free training courses across 300+ NSQF-aligned job roles.',
                'Government-recognized NSDC Skill India Certificate and digital Skill Card.',
                'Direct monetary reward of approx. ₹8,000 upon successful assessment and certification.',
                'Accidental insurance cover (₹2 Lakh) for 3 years upon certification.',
                'Rozgar Mela placement assistance and apprenticeship linkage across top companies.'
            ],
            'benefits_hi' => [
                '300 से अधिक ट्रेडों में 100% बिल्कुल मुफ्त तकनीकी और वोकेशनल ट्रेनिंग।',
                'सरकार द्वारा मान्यता प्राप्त राष्ट्रीय स्किल इंडिया सर्टिफिकेट और डिजिटल कार्ड।',
                'सफलतापूर्वक परीक्षा पास करने पर खाते में लगभग ₹8,000 का नकद पुरस्कार।',
                'प्रमाणित युवाओं को 3 साल के लिए ₹2 लाख का मुफ्त दुर्घटना बीमा।',
                'रोजगार मेलों के जरिए देश की बड़ी कंपनियों में सीधी नौकरी और अप्रेंटिसशिप का मौका।'
            ],
            
            'eligibility_en' => [
                'Any Indian national aged between 15 and 45 years.',
                'School/college dropouts or unemployed youth seeking productive technical skills.',
                'Must possess valid Aadhaar and bank account.',
                'Willing to attend mandatory practical training classes at registered PMKVY training centers.'
            ],
            'eligibility_hi' => [
                '15 से 45 वर्ष की आयु के कोई भी भारतीय युवा।',
                'स्कूल या कॉलेज छोड़ चुके युवा या बेरोजगार युवक-युवतियां जो कोई हुनर सीखना चाहते हैं।',
                'वैध आधार कार्ड और बैंक खाता होना चाहिए।'
            ],
            
            'documents_en' => [
                'Aadhaar Card.',
                'Voter ID / School ID / Proof of Identity.',
                'Bank Account Passbook (Aadhaar linked).',
                'Educational marksheet (10th/12th/8th pass depending on job role).'
            ],
            'documents_hi' => [
                'आधार कार्ड।',
                'शैक्षणिक योग्यता का प्रमाण पत्र (8वीं/10वीं/12वीं अंकतालिका)।',
                'आधार से जुड़ा बैंक खाता पासबुक।',
                'पासपोर्ट साइज फोटो।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Visit the official portal (pmkvyofficial.org) or skillindiadigital.gov.in.',
                'Step 2: Search for registered PMKK/PMKVY training centers in your district.',
                'Step 3: Select your desired course (Automotive, Healthcare, IT, Electronics, Construction, etc.).',
                'Step 4: Enroll with your Aadhaar and attend the training sessions.',
                'Step 5: Pass the final practical assessment; receive your Skill India certificate and placement assistance.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: आधिकारिक पोर्टल skillindiadigital.gov.in पर जाएं या नजदीकी प्रधानमंत्री कौशल केंद्र पर जाएं।',
                'चरण 2: अपने जिले में उपलब्ध पसंदीदा कोर्स (जैसे इलेक्ट्रीशियन, वेल्डर, कंप्यूटर, सिलाई, ड्रोन आदि) का चयन करें।',
                'चरण 3: आधार कार्ड से मुफ्त दाखिला लें और क्लास अटेंड करें।',
                'चरण 4: कोर्स पूरा होने पर प्रैक्टिकल परीक्षा दें।',
                'चरण 5: सरकारी सर्टिफिकेट प्राप्त करें और रोजगार मेले में शामिल होकर नौकरी पाएं।'
            ],
            
            'official_url' => 'https://www.pmkvyofficial.org/',
            'helpline' => '088000-55555 / 1800-123-9626',
            
            'audio_text_en' => 'Welcome to PM Kaushal Vikas Yojana overview. Unemployed youth and students can access free technical training, official Skill India certification, and direct job placement support. Register at skillindiadigital.gov.in or visit your nearest Skill Center.',
            'audio_text_hi' => 'प्रधानमंत्री कौशल विकास योजना में आपका स्वागत है। देश के युवा बिल्कुल मुफ्त तकनीकी प्रशिक्षण, सरकारी स्किल इंडिया सर्टिफिकेट और सीधी नौकरी की सहायता पा सकते हैं। skillindiadigital.gov.in पर ऑनलाइन रजिस्टर करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Are you between 15 and 45 years of age and looking to acquire certified technical or vocational skills?',
                'q1_hi' => 'क्या आपकी उम्र 15 से 45 वर्ष के बीच है और आप कोई तकनीकी हुनर या काम सीखना चाहते हैं?',
                'q2_en' => 'Do you have an Aadhaar card and an active bank account?',
                'q2_hi' => 'क्या आपके पास आधार कार्ड और बैंक खाता उपलब्ध है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible to enroll for 100% free skill training under PMKVY!',
                'eligible_msg_hi' => 'आप पीएम कौशल विकास योजना के तहत 100% मुफ्त ट्रेनिंग और सर्टिफिकेट पाने के लिए पात्र हैं!',
                'ineligible_msg_en' => 'PMKVY training is available for Indian youth between 15-45 years of age.',
                'ineligible_msg_hi' => 'यह योजना 15 से 45 वर्ष के भारतीय युवाओं के कौशल विकास के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 9. National Social Assistance Programme (NSAP - Vajiram List - CSS)
        // ---------------------------------------------------------------------
        'nsap-pension' => [
            'id' => 9,
            'slug' => 'nsap-pension',
            'icon' => 'fa-blind',
            'category' => 'social_security',
            'category_name_en' => 'Social Security & Pension',
            'category_name_hi' => 'सामाजिक सुरक्षा एवं पेंशन',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Core of the Core - Shared)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (कोर ऑफ द कोर - केंद्र एवं राज्य साझा)',
            'ministry_en' => 'Ministry of Rural Development',
            'ministry_hi' => 'ग्रामीण विकास मंत्रालय',
            'launch_year' => '1995',
            'budget' => '₹9,652 Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'National Social Assistance Programme (NSAP)',
            'title_hi' => 'राष्ट्रीय सामाजिक सहायता कार्यक्रम (वृद्धा, विधवा एवं दिव्यांग पेंशन)',
            'tagline_en' => 'Monthly social security pensions for elderly, widows, and persons with severe disabilities living below poverty line.',
            'tagline_hi' => 'बीपीएल गरीब बुजुर्गों, विधवा महिलाओं और दिव्यांगजनों को हर महीने सीधी सामाजिक सुरक्षा पेंशन।',
            
            'overview_en' => 'NSAP is a constitutional directive principles flagship program that provides monthly social security pensions to poor households in the event of old age, death of the primary breadwinner, or severe disability. It encompasses Indira Gandhi National Old Age Pension Scheme (IGNOAPS), Indira Gandhi National Widow Pension Scheme (IGNWPS), and Indira Gandhi National Disability Pension Scheme (IGNDPS).',
            'overview_hi' => 'राष्ट्रीय सामाजिक सहायता कार्यक्रम (NSAP) भारत के संविधान के अनुच्छेद 41 के तहत गरीब परिवारों के बुजुर्गों, विधवा महिलाओं और गंभीर रूप से दिव्यांग व्यक्तियों को हर महीने आर्थिक सुरक्षा पेंशन प्रदान करता है। इसमें वृद्धावस्था पेंशन (IGNOAPS), विधवा पेंशन (IGNWPS) और दिव्यांगजन पेंशन (IGNDPS) शामिल हैं, जो सीधे बैंक खातों में भेजी जाती हैं।',
            
            'benefits_en' => [
                'Monthly pension of ₹1,000 to ₹3,000 (combined Central + State contribution depending on state policy).',
                'Old Age Pension for seniors aged 60+ living in BPL households.',
                'Widow Pension for destitute women aged 40-79 living in BPL households.',
                'Disability Pension for persons aged 18-79 with 80%+ severe or multiple disability.',
                'National Family Benefit Scheme (NFBS): One-time lump sum of ₹20,000 upon death of the primary family breadwinner.'
            ],
            'benefits_hi' => [
                'हर महीने ₹1,000 से ₹3,000 तक की मासिक पेंशन (केंद्र और राज्य सरकार के साझा अंशदान से)।',
                '60 वर्ष या अधिक आयु के बीपीएल बुजुर्गों को वृद्धावस्था पेंशन।',
                '40 से 79 वर्ष की बीपीएल विधवा महिलाओं को हर महीने विधवा पेंशन।',
                '80% या उससे अधिक दिव्यांगता वाले व्यक्तियों को मासिक दिव्यांग पेंशन।',
                'राष्ट्रीय पारिवारिक लाभ योजना: परिवार के मुख्य कमाने वाले की मृत्यु पर ₹20,000 की एकमुश्त सहायता।'
            ],
            
            'eligibility_en' => [
                'Household must belong to Below Poverty Line (BPL) or Antyodaya category.',
                'For Old Age Pension: Age 60 years and above.',
                'For Widow Pension: Widows aged 40 to 79 years.',
                'For Disability Pension: Persons aged 18+ with 80% or higher certified disability level.',
                'Must have active bank or post-office account seeded with Aadhaar.'
            ],
            'eligibility_hi' => [
                'परिवार गरीबी रेखा से नीचे (BPL) या अंत्योदय राशन कार्ड धारक होना चाहिए।',
                'वृद्धावस्था पेंशन के लिए: उम्र 60 वर्ष या उससे अधिक।',
                'विधवा पेंशन के लिए: उम्र 40 से 79 वर्ष और पति का मृत्यु प्रमाण पत्र।',
                'दिव्यांग पेंशन के लिए: मुख्य चिकित्सा अधिकारी (CMO) द्वारा 80% या अधिक दिव्यांगता प्रमाण पत्र।'
            ],
            
            'documents_en' => [
                'Aadhaar Card.',
                'BPL Ration Card / BPL Survey Certificate.',
                'Age Proof (Voter ID / School Certificate / Medical Age Certificate).',
                'Husband\'s Death Certificate (for Widow Pension).',
                'Disability Certificate from Chief Medical Officer (CMO) for Disability Pension.',
                'Bank Passbook (Aadhaar seeded).'
            ],
            'documents_hi' => [
                'आधार कार्ड।',
                'बीपीएल / अंत्योदय राशन कार्ड या बीपीएल सर्वे नंबर।',
                'आयु प्रमाण पत्र (वोटर आईडी या जन्म प्रमाण पत्र)।',
                'पति का मृत्यु प्रमाण पत्र (विधवा पेंशन के लिए)।',
                'सीएमओ द्वारा जारी दिव्यांगता प्रमाण पत्र (दिव्यांग पेंशन हेतु)।',
                'बैंक या डाकघर पासबुक।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Apply online through the national portal (nsap.nic.in) or state social welfare e-district portal / CSC.',
                'Step 2: Fill applicant details, upload BPL proof and required medical/death certificates.',
                'Step 3: Block Development Officer (BDO) or Sub-Divisional Magistrate (SDM) verifies field eligibility.',
                'Step 4: Sanction order issued by District Social Welfare Officer.',
                'Step 5: Monthly pension is deposited directly into your bank or post-office account via DBT.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: nsap.nic.in या राज्य के e-District पोर्टल / नजदीकी जन सेवा केंद्र से ऑनलाइन आवेदन करें।',
                'चरण 2: आधार, बीपीएल प्रमाण और बैंक खाता विवरण अपलोड करें।',
                'चरण 3: ब्लॉक विकास अधिकारी (BDO) या तहसील द्वारा पात्रता का सत्यापन किया जाता है।',
                'चरण 4: जिला समाज कल्याण अधिकारी द्वारा पेंशन स्वीकृत की जाती है।',
                'चरण 5: हर महीने पेंशन की राशि सीधे आपके बैंक या डाकघर खाते में आना शुरू हो जाती है।'
            ],
            
            'official_url' => 'https://nsap.nic.in/',
            'helpline' => '1800-180-1551 / 14567 (Elderly Helpline)',
            
            'audio_text_en' => 'Welcome to National Social Assistance Programme overview. Senior citizens aged 60 and above, widows, and persons with severe disabilities belonging to poor families receive monthly pensions directly in their bank accounts. Apply online on nsap.nic.in or at your local block office.',
            'audio_text_hi' => 'राष्ट्रीय सामाजिक सहायता कार्यक्रम में आपका स्वागत है। 60 वर्ष से अधिक के गरीब बुजुर्गों, विधवा माताओं और दिव्यांगजनों को हर महीने सरकारी पेंशन सीधे बैंक खाते में दी जाती है। nsap.nic.in या ब्लॉक समाज कल्याण कार्यालय से आवेदन करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Does your household possess a Below Poverty Line (BPL / Antyodaya) ration card or certificate?',
                'q1_hi' => 'क्या आपके परिवार के पास बीपीएल या अंत्योदय राशन कार्ड है?',
                'q2_en' => 'Are you aged 60 or older, or a widow aged 40+, or an individual with 80%+ certified disability?',
                'q2_hi' => 'क्या आपकी आयु 60 वर्ष या अधिक है, या आप विधवा हैं (40+), या 80% से अधिक दिव्यांग हैं?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible to apply for monthly pension under NSAP!',
                'eligible_msg_hi' => 'आप एनएसएपी योजना के तहत हर महीने सरकारी पेंशन पाने के लिए पूर्ण रूप से पात्र हैं!',
                'ineligible_msg_en' => 'NSAP pensions are reserved for BPL elderly, widows, and severely disabled persons.',
                'ineligible_msg_hi' => 'यह पेंशन योजना बीपीएल परिवारों के बुजुर्गों, विधवा महिलाओं और दिव्यांगजनों के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 10. DAY-NRLM Aajivika (Vajiram List - Centrally Sponsored)
        // ---------------------------------------------------------------------
        'day-nrlm-aajivika' => [
            'id' => 10,
            'slug' => 'day-nrlm-aajivika',
            'icon' => 'fa-female',
            'category' => 'employment',
            'category_name_en' => 'Women Empowerment & SHGs',
            'category_name_hi' => 'महिला सशक्तिकरण एवं स्वयं सहायता समूह',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Shared 60:40 Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (60:40 केंद्र और राज्य सरकार द्वारा साझा)',
            'ministry_en' => 'Ministry of Rural Development',
            'ministry_hi' => 'ग्रामीण विकास मंत्रालय',
            'launch_year' => '2011',
            'budget' => '₹14,000 Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'DAY-NRLM (Deendayal Antyodaya Yojana - Aajivika)',
            'title_hi' => 'दीनदयाल अंत्योदय योजना - राष्ट्रीय ग्रामीण आजीविका मिशन (लखपति दीदी)',
            'tagline_en' => 'Empowering rural women through Self-Help Groups (SHGs), revolving funds, and collateral-free bank loans.',
            'tagline_hi' => 'स्वयं सहायता समूहों के माध्यम से ग्रामीण महिलाओं को आजीविका, रिवॉल्विंग फंड और सस्ता बैंक ऋण।',
            
            'overview_en' => 'DAY-NRLM is the world\'s largest poverty alleviation program aimed at mobilizing rural poor women into Self-Help Groups (SHGs) and empowering them through sustainable livelihood opportunities. The government provides Revolving Funds (RF) of ₹15,000 to ₹30,000, Community Investment Funds (CIF) up to ₹1.5 Lakh, and collateral-free bank credit up to ₹20 Lakh at a 7% interest subvention to promote "Lakhpati Didis".',
            'overview_hi' => 'दीनदयाल अंत्योदय योजना - राष्ट्रीय ग्रामीण आजीविका मिशन (DAY-NRLM) ग्रामीण गरीब महिलाओं को स्वयं सहायता समूहों (SHG) में संगठित करके उन्हें आर्थिक रूप से आत्मनिर्भर बनाने का देश का सबसे बड़ा अभियान है। इसके तहत समूहों को ₹15,000 से ₹30,000 का रिवॉल्विंग फंड, ₹1.5 लाख का कम्युनिटी फंड और बिना गारंटी ₹20 लाख तक का बैंक लोन मात्र 7% ब्याज पर मिलता है ताकि हर महिला "लखपति दीदी" बन सके।',
            
            'benefits_en' => [
                'Revolving Fund (RF) of ₹15,000 - ₹30,000 per eligible SHG to establish financial discipline.',
                'Community Investment Fund (CIF) of up to ₹1,50,000 per SHG for livelihood activities.',
                'Collateral-free bank loans up to ₹20,00,000 with interest subvention at 7% per annum.',
                'Training as Bank Sakhi, Krishi Sakhi, Pashu Sakhi, and Lakhpati Didi micro-entrepreneurs.',
                'Market linkage via Saras Melas, Government e-Marketplace (GeM), and e-commerce platforms.'
            ],
            'benefits_hi' => [
                'स्वयं सहायता समूह को ₹15,000 से ₹30,000 का रिवॉल्विंग फंड (RF) सीधे खाते में।',
                'आजीविका शुरू करने के लिए प्रति समूह ₹1,50,000 तक का कम्युनिटी इन्वेस्टमेंट फंड (CIF)।',
                'बिना किसी गारंटी के ₹20 लाख तक का बैंक लोन, 7% की सस्ती ब्याज दर पर।',
                'बैंक सखी, कृषि सखी, पशु सखी के रूप में सम्मानजनक रोजगार और ट्रेनिंग।',
                'सरस मेलों और सरकारी GeM पोर्टल पर उत्पाद बेचकर हर महीने अच्छी कमाई का अवसर।'
            ],
            
            'eligibility_en' => [
                'Rural women belonging to poor, marginal, or vulnerable households.',
                'Willing to form or join an active village Self-Help Group (10-20 women per group).',
                'Must participate in regular weekly savings and group meetings.',
                'One woman member from each poor rural household.'
            ],
            'eligibility_hi' => [
                'ग्रामीण क्षेत्र की गरीब, सीमांत या वंचित परिवारों की महिलाएं।',
                'गांव के महिला स्वयं सहायता समूह (10 से 20 महिलाओं का समूह) से जुड़ने की इच्छुक हों।',
                'साप्ताहिक बचत और समूह की बैठकों में नियमित भाग लेने की प्रतिबद्धता।',
                'हर गरीब परिवार से कम से कम एक महिला का समूह में शामिल होना।'
            ],
            
            'documents_en' => [
                'Aadhaar Card of participating woman.',
                'Bank Account Passbook (joint SHG account).',
                'Resolution copy of the Self-Help Group formation.',
                'Passport-size photographs.'
            ],
            'documents_hi' => [
                'महिला का आधार कार्ड।',
                'समूह के संयुक्त बैंक खाते की पासबुक।',
                'स्वयं सहायता समूह गठन का प्रस्ताव पत्र।',
                'पासपोर्ट साइज फोटो।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Contact your local Gram Panchayat, Community Resource Person (CRP), or Block Mission Management Unit (BMMU).',
                'Step 2: Form a group of 10 to 20 like-minded neighborhood women and choose a President/Secretary.',
                'Step 3: Open an SHG savings bank account and maintain weekly savings records.',
                'Step 4: After 3 months of regular functioning, apply for Revolving Fund (RF) and Community Investment Fund.',
                'Step 5: Apply for bank credit linkage through the NRLM LokOS app to launch collective micro-enterprises.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: अपनी ग्राम पंचायत में आजीविका सखी (CRP) या ब्लॉक मिशन कार्यालय (BMMU) से संपर्क करें।',
                'चरण 2: अपने पड़ोस की 10 से 20 महिलाओं के साथ मिलकर समूह बनाएं और अध्यक्ष/सचिव चुनें।',
                'चरण 3: बैंक में समूह का बचत खाता खुलवाएं और साप्ताहिक छोटी-छोटी बचत शुरू करें।',
                'चरण 4: 3 महीने बाद सरकार से रिवॉल्विंग फंड और कम्युनिटी इन्वेस्टमेंट फंड के लिए आवेदन करें।',
                'चरण 5: बैंक से सस्ता लोन लेकर सिलाई, डेयरी, आचार, पापड़ या खेती का सामूहिक व्यवसाय शुरू करें।'
            ],
            
            'official_url' => 'https://aajivika.gov.in/',
            'helpline' => '1800-110-001',
            
            'audio_text_en' => 'Welcome to DAY-NRLM Aajivika overview. Rural women can join village Self-Help Groups to receive revolving funds, subsidized bank loans up to twenty lakh rupees, and training to become financially independent Lakhpati Didis. Join a group at your local Gram Panchayat.',
            'audio_text_hi' => 'दीनदयाल अंत्योदय योजना - राष्ट्रीय ग्रामीण आजीविका मिशन में आपका स्वागत है। ग्रामीण महिलाएं स्वयं सहायता समूहों से जुड़कर सरकारी फंड, मात्र 7% पर ₹20 लाख तक का बैंक लोन और ट्रेनिंग पाकर लखपति दीदी बन सकती हैं। अपनी ग्राम पंचायत से समूह से जुड़ें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Are you a rural woman willing to join or already part of a village Self-Help Group (SHG)?',
                'q1_hi' => 'क्या आप ग्रामीण महिला हैं और गांव के स्वयं सहायता समूह (SHG) से जुड़ने की इच्छुक हैं?',
                'q2_en' => 'Does your group practice regular weekly savings and meetings?',
                'q2_hi' => 'क्या आपका समूह नियमित बचत और साप्ताहिक बैठकें करता है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You and your SHG are eligible for Revolving Fund, CIF, and subsidized bank loans under DAY-NRLM!',
                'eligible_msg_hi' => 'आप और आपका समूह DAY-NRLM के तहत रिवॉल्विंग फंड और सस्ते बैंक लोन के लिए पूरी तरह पात्र हैं!',
                'ineligible_msg_en' => 'DAY-NRLM benefits are extended through organized rural women Self-Help Groups.',
                'ineligible_msg_hi' => 'यह योजना ग्रामीण महिलाओं के स्वयं सहायता समूहों के माध्यम से संचालित होती है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 11. PM Janjatiya Unnat Gram Abhiyan (Vajiram 2024 List - CSS)
        // ---------------------------------------------------------------------
        'pm-janjatiya-gram' => [
            'id' => 11,
            'slug' => 'pm-janjatiya-gram',
            'icon' => 'fa-campground',
            'category' => 'social_security',
            'category_name_en' => 'Tribal Affairs & Social Welfare',
            'category_name_hi' => 'जनजातीय कार्य एवं सामाजिक कल्याण',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Shared Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (केंद्र एवं राज्य सरकार साझा)',
            'ministry_en' => 'Ministry of Tribal Affairs',
            'ministry_hi' => 'जनजातीय कार्य मंत्रालय',
            'launch_year' => '2024',
            'budget' => '₹79,156 Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Madhya Pradesh', 'Odisha', 'Jharkhand', 'Rajasthan', 'Gujarat', 'Maharashtra', 'Chhattisgarh', 'Andhra Pradesh'],
            
            'title_en' => 'PM Janjatiya Unnat Gram Abhiyan',
            'title_hi' => 'प्रधानमंत्री जनजातीय उन्नत ग्राम अभियान',
            'tagline_en' => 'Saturating 63,000 tribal villages with pucca houses, tap water, electricity, mobile clinics, and roads.',
            'tagline_hi' => '63,000 आदिवासी गांवों में पक्के मकान, नल का जल, सड़क, बिजली और मोबाइल क्लिनिक का शत-प्रतिशत विकास।',
            
            'overview_en' => 'Approved in 2024 with a massive outlay of ₹79,156 Crore, Pradhan Mantri Janjatiya Unnat Gram Abhiyan aims to achieve comprehensive development across 63,843 tribal-majority villages in 549 districts across 30 States and UTs. The scheme covers 25 core interventions across 17 ministries to saturate housing, electricity, clean water, road access, broadband, and health facilities for over 5 Crore tribal citizens.',
            'overview_hi' => 'प्रधानमंत्री जनजातीय उन्नत ग्राम अभियान 2024 में ₹79,156 करोड़ के बजट के साथ शुरू किया गया। इसका लक्ष्य 30 राज्यों के 549 जिलों के 63,000 से अधिक आदिवासी बहुल गांवों में 5 करोड़ से अधिक जनजातीय नागरिकों को पक्के मकान, नल से जल, बिजली, पक्की सड़क, मोबाइल अस्पताल और 4G इंटरनेट जैसी 25 बुनियादी सुविधाएं शत-प्रतिशत उपलब्ध कराना है।',
            
            'benefits_en' => [
                'Pucca houses for all eligible tribal families under PMAY-Gramin with extra financial top-up.',
                '100% piped drinking water connections under Jal Jeevan Mission and off-grid solar electricity.',
                'All-weather road connectivity to unconnected tribal habitations under PMGSY.',
                'Mobile Medical Units (MMUs) and specialized screening for Sickle Cell Anemia.',
                'Construction of Tribal Multipurpose Marketing Centers and Tribal Community Halls (Pucca Gram Kendra).'
            ],
            'benefits_hi' => [
                'सभी पात्र आदिवासी परिवारों को पीएम आवास योजना के तहत पक्के मकान का निर्माण।',
                'जल जीवन मिशन से हर घर में नल का पानी और सोलर ऊर्जा से 24 घंटे बिजली।',
                'दुर्गम आदिवासी बस्तियों को मुख्य सड़क से जोड़ने के लिए पक्की सड़कें।',
                'सिकल सेल एनीमिया की मुफ्त जांच और गांव-गांव मोबाइल मेडिकल अस्पताल वैन।',
                'जनजातीय बहुउद्देशीय विपणन केंद्र और वन-धन विकास केंद्रों की स्थापना।'
            ],
            
            'eligibility_en' => [
                'Residents of designated 63,843 tribal-majority villages and particularly vulnerable tribal groups (PVTGs).',
                'Scheduled Tribe (ST) households and individuals living in notified forest villages/aspirational blocks.',
                'Community level saturation across health, education, and livelihood.'
            ],
            'eligibility_hi' => [
                'चिह्नित 63,843 आदिवासी बहुल गांवों में रहने वाले सभी निवासी।',
                'अनुसूचित जनजाति (ST) परिवार और विशेष रूप से कमजोर जनजातीय समूह (PVTG)।',
                'वन ग्रामों और आकांक्षी आदिवासी ब्लॉकों के परिवार।'
            ],
            
            'documents_en' => [
                'Aadhaar Card.',
                'Scheduled Tribe (ST) Caste Certificate / Forest Rights Act (FRA) Patta.',
                'Ration Card.',
                'Bank Account Passbook.'
            ],
            'documents_hi' => [
                'आधार कार्ड।',
                'अनुसूचित जनजाति (ST) जाति प्रमाण पत्र / वनाधिकार पट्टा।',
                'राशन कार्ड।',
                'बैंक खाता पासबुक।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Check if your village is on the 63,000 saturated village list via your District Tribal Welfare Office.',
                'Step 2: Gram Sabha prepares village development action plan covering pending houses, roads, and water.',
                'Step 3: District Collector sanctions saturation funds across designated 17 ministries.',
                'Step 4: Individual benefits (PMAY-G, Sickle Cell card, Forest Rights land) disbursed at saturation camps in the village.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: अपने जिला जनजातीय विकास अधिकारी या ग्राम पंचायत से चेक करें कि आपका गांव योजना में शामिल है या नहीं।',
                'चरण 2: ग्राम सभा द्वारा गांव के छूटे हुए मकानों, सड़कों और पानी के कार्यों का प्रस्ताव तैयार किया जाता है।',
                'चरण 3: जिला प्रशासन द्वारा गांव में विशेष शिविर लगाकर सभी छूटे हुए लाभ प्रदान किए जाते हैं।',
                'चरण 4: पात्र परिवारों को सीधे पक्के मकान, राशन, सिकल सेल कार्ड और बिजली-पानी की सुविधा दी जाती है।'
            ],
            
            'official_url' => 'https://tribal.nic.in/',
            'helpline' => '1800-11-2001',
            
            'audio_text_en' => 'Welcome to PM Janjatiya Unnat Gram Abhiyan. Over sixty-three thousand tribal villages across India are receiving complete development with pucca houses, clean tap water, mobile medical units, and all-weather roads. Learn more at your district tribal welfare office.',
            'audio_text_hi' => 'प्रधानमंत्री जनजातीय उन्नत ग्राम अभियान में आपका स्वागत है। देश के 63,000 से अधिक आदिवासी गांवों में हर घर को पक्का मकान, नल का जल, बिजली, सड़क और मोबाइल अस्पताल की सुविधाएं दी जा रही हैं। जिला जनजातीय कल्याण कार्यालय से संपर्क करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you belong to a Scheduled Tribe (ST) family or live in a notified tribal-majority village?',
                'q1_hi' => 'क्या आप अनुसूचित जनजाति (ST) परिवार से हैं या किसी आदिवासी बहुल गांव में रहते हैं?',
                'q2_en' => 'Does your household need a pucca house, piped water, or health/livelihood support?',
                'q2_hi' => 'क्या आपके घर में पक्का मकान, नल का पानी या स्वास्थ्य जांच की आवश्यकता है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible for priority saturation benefits under PM Janjatiya Unnat Gram Abhiyan!',
                'eligible_msg_hi' => 'आप पीएम जनजातीय उन्नत ग्राम अभियान के तहत शत-प्रतिशत विकास योजनाओं के लिए पात्र हैं!',
                'ineligible_msg_en' => 'This scheme is focused on tribal communities and identified tribal-majority habitations.',
                'ineligible_msg_hi' => 'यह योजना आदिवासी बहुल गांवों और जनजातीय परिवारों के विकास के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 12. Intensified Mission Indradhanush (IMI - Vajiram List - CSS)
        // ---------------------------------------------------------------------
        'mission-indradhanush' => [
            'id' => 12,
            'slug' => 'mission-indradhanush',
            'icon' => 'fa-syringe',
            'category' => 'healthcare',
            'category_name_en' => 'Maternal & Child Health',
            'category_name_hi' => 'मातृ एवं शिशु स्वास्थ्य',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Shared 60:40 Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (60:40 केंद्र और राज्य सरकार द्वारा साझा)',
            'ministry_en' => 'Ministry of Health & Family Welfare',
            'ministry_hi' => 'स्वास्थ्य एवं परिवार कल्याण मंत्रालय',
            'launch_year' => '2014',
            'budget' => 'National Health Mission Outlay',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'Intensified Mission Indradhanush (IMI 5.0)',
            'title_hi' => 'सघन मिशन इंद्रधनुष (IMI 5.0 - सम्पूर्ण टीकाकरण)',
            'tagline_en' => '100% free life-saving immunization against 12 deadly diseases for all pregnant women and children up to 5 years.',
            'tagline_hi' => 'सभी गर्भवती महिलाओं और 5 वर्ष तक के बच्चों को 12 जानलेवा बीमारियों से 100% मुफ्त टीकाकरण सुरक्षा।',
            
            'overview_en' => 'Intensified Mission Indradhanush (IMI) is a special flagship immunization drive to reach unreached, unvaccinated, and partially vaccinated children up to 5 years of age and pregnant women. Vaccines against 12 vaccine-preventable diseases (including Polio, Measles, Rubella, Diphtheria, Pertussis, Tetanus, Rotavirus, Pneumococcal, and Hepatitis B) are provided free of cost, digitized in real-time via the U-WIN portal.',
            'overview_hi' => 'सघन मिशन इंद्रधनुष (IMI) भारत सरकार का प्रमुख टीकाकरण अभियान है। इसका उद्देश्य उन सभी बच्चों (5 वर्ष तक) और गर्भवती महिलाओं तक पहुंचना है जो किसी कारणवश नियमित टीकों से छूट गए हैं। इसमें पोलियो, खसरा, रूबेला, डिप्थीरिया, टिटनेस, हेपेटाइटिस-बी सहित 12 जानलेवा बीमारियों के टीके सरकारी अस्पतालों और आंगनबाड़ी केंद्रों पर बिल्कुल मुफ्त लगाए जाते हैं।',
            
            'benefits_en' => [
                '100% free vaccination protecting against 12 life-threatening diseases.',
                'Digital tracking and instant digital vaccination certificate via the U-WIN portal (uwin.mohfw.gov.in).',
                'Doorstep outreach by ASHA and Anganwadi workers in rural, remote, and urban slum areas.',
                'Special coverage for children who missed routine doses due to migration or lack of awareness.',
                'Ensures healthy child development and drastic reduction in infant and maternal mortality.'
            ],
            'benefits_hi' => [
                '12 जानलेवा बीमारियों से बचाने वाले सभी टीके बिल्कुल मुफ्त।',
                'U-WIN पोर्टल (uwin.mohfw.gov.in) के जरिए तुरंत डिजिटल टीकाकरण प्रमाण पत्र।',
                'आशा (ASHA) और आंगनबाड़ी कार्यकर्ताओं द्वारा घर-घर जाकर छूटे हुए बच्चों की पहचान।',
                'ईंट-भट्टों, झुग्गियों और दूरदराज के क्षेत्रों में विशेष मोबाइल टीकाकरण कैंप।',
                'शिशु और मातृ मृत्यु दर को कम करने और बच्चों को स्वस्थ व सुरक्षित भविष्य देने की गारंटी।'
            ],
            
            'eligibility_en' => [
                'All pregnant women requiring Tetanus/Diphtheria (Td) protection.',
                'All newborn infants and children up to 5 years of age who are unvaccinated or partially vaccinated.',
                'Zero fees, zero discrimination across all public health facilities and Anganwadi centers in India.'
            ],
            'eligibility_hi' => [
                'सभी गर्भवती महिलाएं।',
                'जन्म से लेकर 5 वर्ष तक के सभी बच्चे जो किसी भी नियमित टीके से छूट गए हों।',
                'पूरे भारत में सभी के लिए बिल्कुल मुफ्त और सुलभ।'
            ],
            
            'documents_en' => [
                'Mother and Child Protection (MCP) Card / Mamta Card (if available).',
                'Aadhaar Card of mother/father (optional, for digital U-WIN record).',
                'Mobile number for vaccination SMS reminders.'
            ],
            'documents_hi' => [
                'मातृ एवं शिशु सुरक्षा कार्ड (ममता कार्ड) - यदि उपलब्ध हो।',
                'माता-पिता का आधार कार्ड।',
                'एसएमएस रिमाइंडर के लिए मोबाइल नंबर।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Check your child\'s vaccination schedule on the MCP card or register online at uwin.mohfw.gov.in.',
                'Step 2: Visit any Government Primary Health Centre (PHC), Community Health Centre (CHC), or Anganwadi on Village Health and Nutrition Day (VHND).',
                'Step 3: Healthcare staff administers required vaccines according to the National Immunization Schedule.',
                'Step 4: Receive instant digital vaccination certificate on your mobile phone via U-WIN.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: अपने बच्चे के ममता कार्ड में छूटे हुए टीकों की जांच करें या uwin.mohfw.gov.in पर देखें।',
                'चरण 2: नजदीकी आंगनबाड़ी केंद्र, प्राथमिक स्वास्थ्य केंद्र (PHC) या सरकारी अस्पताल में जाएं।',
                'चरण 3: एएनएम और आशा कार्यकर्ता द्वारा बच्चे को आवश्यक टीके सुरक्षित रूप से लगाए जाएंगे।',
                'चरण 4: आपके मोबाइल पर डिजिटल टीकाकरण प्रमाण पत्र का एसएमएस प्राप्त होगा।'
            ],
            
            'official_url' => 'https://uwin.mohfw.gov.in/',
            'helpline' => '1075 (National Health Helpline)',
            
            'audio_text_en' => 'Welcome to Intensified Mission Indradhanush overview. Protect your children up to five years of age and pregnant women with free vaccines against twelve preventable diseases. Visit your nearest Anganwadi center or register on uwin.mohfw.gov.in.',
            'audio_text_hi' => 'सघन मिशन इंद्रधनुष में आपका स्वागत है। 5 वर्ष तक के बच्चों और गर्भवती महिलाओं को 12 जानलेवा बीमारियों से बचाने के लिए सभी सरकारी टीके बिल्कुल मुफ्त लगवाएं। नजदीकी आंगनबाड़ी या स्वास्थ्य केंद्र पर संपर्क करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you have a pregnant woman or a child aged 0-5 years in your family?',
                'q1_hi' => 'क्या आपके परिवार में कोई गर्भवती महिला या 5 वर्ष तक का कोई बच्चा है?',
                'q2_en' => 'Has the child or mother missed any routine government vaccinations?',
                'q2_hi' => 'क्या बच्चे या मां का कोई नियमित सरकारी टीका छूट गया है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'Your child and pregnant family member are eligible for 100% free immunization under Mission Indradhanush!',
                'eligible_msg_hi' => 'आपका बच्चा और गर्भवती परिजन मिशन इंद्रधनुष के तहत 100% मुफ्त टीकाकरण के लिए पात्र हैं!',
                'ineligible_msg_en' => 'Ensure all routine vaccines are up to date according to the National Immunization Schedule.',
                'ineligible_msg_hi' => 'राष्ट्रीय टीकाकरण तालिका के अनुसार सभी नियमित टीकों का पूरा होना आवश्यक है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 13. PMGSY - Pradhan Mantri Gram Sadak Yojana (Vajiram List - CSS)
        // ---------------------------------------------------------------------
        'pmgsy-roads' => [
            'id' => 13,
            'slug' => 'pmgsy-roads',
            'icon' => 'fa-road',
            'category' => 'housing',
            'category_name_en' => 'Rural Infrastructure & Roads',
            'category_name_hi' => 'ग्रामीण अवसंरचना एवं सड़कें',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Shared 60:40 Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (60:40 केंद्र और राज्य सरकार साझा)',
            'ministry_en' => 'Ministry of Rural Development',
            'ministry_hi' => 'ग्रामीण विकास मंत्रालय',
            'launch_year' => '2000',
            'budget' => '₹1.80 Lakh Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'PMGSY (Pradhan Mantri Gram Sadak Yojana)',
            'title_hi' => 'प्रधानमंत्री ग्राम सड़क योजना (PMGSY)',
            'tagline_en' => 'Connecting unconnected rural habitations with all-weather blacktopped motorable roads.',
            'tagline_hi' => 'देश के सभी असंपर्कित गांवों को हर मौसम में चालू रहने वाली पक्की डामर सड़कों से जोड़ना।',
            
            'overview_en' => 'Launched in 2000, PMGSY provides good all-weather road connectivity to unconnected rural habitations having population of 500+ persons in plain areas and 250+ persons in hilly, desert, and tribal areas. It enables farmers to reach agricultural mandis quickly, ensures ambulance accessibility, and boosts rural trade and school attendance.',
            'overview_hi' => 'प्रधानमंत्री ग्राम सड़क योजना (PMGSY) देश के उन सभी गांवों और मजरों को पक्की बारहमासी सड़कों से जोड़ती है जहां मैदानी इलाकों में 500 से अधिक और पहाड़ी/आदिवासी इलाकों में 250 से अधिक की आबादी है। इससे किसानों की उपज समय पर मंडियों तक पहुंचती है, एम्बुलेंस और स्कूल बसों की पहुंच आसान होती है और गांव का आर्थिक विकास होता है।',
            
            'benefits_en' => [
                '100% all-weather motorable bituminous/concrete road connecting village to nearest market highway.',
                'Reduces travel time to hospitals, colleges, banks, and grain mandis.',
                'Direct economic upliftment for farmers and rural artisans through cheap transport.',
                'Strict quality standards monitored by independent National Quality Monitors (NQMs).',
                'Citizen complaint redressal through the Meri Sadak mobile application.'
            ],
            'benefits_hi' => [
                'गांव से मुख्य हाईवे और मंडी तक हर मौसम में चालू रहने वाली पक्की डामर/कंक्रीट सड़क।',
                'अस्पताल, कॉलेज, बैंक और कृषि मंडियों तक आने-जाने में लगने वाले समय और किराए में भारी कमी।',
                'सब्जी, दूध और फसलों को बिना खराब हुए तुरंत बाजार तक पहुंचाकर किसानों की आमदनी में वृद्धि।',
                'सड़क की गुणवत्ता की जांच राष्ट्रीय गुणवत्ता मॉनिटर (NQM) द्वारा अनिवार्य।',
                'खराब सड़क की शिकायत के लिए "मेरी सड़क" (Meri Sadak) मोबाइल ऐप की सुविधा।'
            ],
            
            'eligibility_en' => [
                'Rural habitations with population ≥ 500 in plain areas.',
                'Rural habitations with population ≥ 250 in North-East, Himalayan states, desert, and tribal areas.',
                'Habitations currently unconnected by an all-weather blacktopped road network.'
            ],
            'eligibility_hi' => [
                'मैदानी क्षेत्रों में 500 या उससे अधिक आबादी वाले असंपर्कित गांव या मजरे।',
                'पहाड़ी, रेगिस्तानी और आदिवासी क्षेत्रों में 250 या उससे अधिक आबादी वाली बस्तियां।',
                'जहां वर्तमान में कोई पक्की बारहमासी सड़क मौजूद नहीं है।'
            ],
            
            'documents_en' => [
                'Gram Panchayat Resolution proposing road connectivity.',
                'Village Population Census Report.',
                'Land clearance / Right of Way permission from local landowners.'
            ],
            'documents_hi' => [
                'ग्राम पंचायत का सड़क निर्माण का प्रस्ताव पत्र।',
                'गांव की जनसंख्या का प्रमाणित विवरण।',
                'सड़क निर्माण हेतु जमीन की अनापत्ति (NOC)।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Gram Sabha passes a formal resolution demanding road connectivity under District Rural Roads Plan (DRRP).',
                'Step 2: State Rural Roads Development Agency (SRRDA) prepares Detailed Project Report (DPR).',
                'Step 3: Central Ministry of Rural Development sanctions the road project funds.',
                'Step 4: Contractor constructs the road with a mandatory 5-year post-construction maintenance agreement.',
                'Step 5: Citizens track and report maintenance quality on the Meri Sadak app.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: ग्राम सभा की बैठक में सड़क निर्माण का प्रस्ताव पास करके ब्लॉक/जिला परिषद को भेजें।',
                'चरण 2: राज्य ग्रामीण सड़क विकास एजेंसी द्वारा सड़क का डीपीआर (DPR) तैयार किया जाता है।',
                'चरण 3: केंद्र सरकार से सड़क की मंजूरी और बजट पास होता है।',
                'चरण 4: ठेकेदार द्वारा पक्की सड़क का निर्माण और 5 साल तक मुफ्त रख-रखाव किया जाता है।',
                'चरण 5: नागरिक "मेरी सड़क" ऐप से सड़क की स्थिति देख सकते हैं और शिकायत दर्ज कर सकते हैं।'
            ],
            
            'official_url' => 'http://omms.nic.in/',
            'helpline' => '1800-11-2001 / Meri Sadak App',
            
            'audio_text_en' => 'Welcome to PM Gram Sadak Yojana overview. Unconnected rural habitations with populations of two hundred fifty or five hundred persons are eligible for all-weather blacktopped roads to connect to major markets and highways. Proposals are submitted through your local Gram Sabha.',
            'audio_text_hi' => 'प्रधानमंत्री ग्राम सड़क योजना में आपका स्वागत है। जिन गांवों में पक्की सड़क नहीं है, उन्हें बारहमासी डामर सड़कों से मुख्य बाजार और हाईवे से जोड़ा जाता है। अपनी ग्राम सभा के माध्यम से सड़क निर्माण का प्रस्ताव पास कराएं।',
            
            'eligibility_checker' => [
                'q1_en' => 'Does your rural village or habitation lack an all-weather motorable blacktopped approach road?',
                'q1_hi' => 'क्या आपके गांव या मजरे में मुख्य सड़क तक जाने के लिए कोई पक्की डामर सड़क नहीं है?',
                'q2_en' => 'Is your habitation\'s population at least 250 (hilly/tribal) or 500 (plains)?',
                'q2_hi' => 'क्या आपके गांव की आबादी 250 (पहाड़ी/आदिवासी) या 500 (मैदानी) से अधिक है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'Your village qualifies for all-weather road sanctioning under PMGSY!',
                'eligible_msg_hi' => 'आपका गांव PMGSY के तहत पक्की डामर सड़क की मंजूरी के लिए पूरी तरह पात्र है!',
                'ineligible_msg_en' => 'PMGSY targets habitations meeting minimum population criteria lacking blacktopped connectivity.',
                'ineligible_msg_hi' => 'यह योजना न्यूनतम आबादी वाले असंपर्कित गांवों को सड़क से जोड़ने के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 14. Atal Pension Yojana (Vajiram List - Central Sector)
        // ---------------------------------------------------------------------
        'atal-pension-yojana' => [
            'id' => 14,
            'slug' => 'atal-pension-yojana',
            'icon' => 'fa-hand-holding-usd',
            'category' => 'social_security',
            'category_name_en' => 'Social Security & Pension',
            'category_name_hi' => 'सामाजिक सुरक्षा एवं पेंशन',
            'funding_type' => 'central_sector',
            'funding_type_label_en' => 'Central Sector Scheme (100% Central Funded)',
            'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (100% केंद्र सरकार द्वारा वित्तपोषित)',
            'ministry_en' => 'Ministry of Finance (PFRDA)',
            'ministry_hi' => 'वित्त मंत्रालय (PFRDA)',
            'launch_year' => '2015',
            'budget' => '₹40,000 Crore+ Corpus',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'Atal Pension Yojana (APY)',
            'title_hi' => 'अटल पेंशन योजना (APY)',
            'tagline_en' => 'Government-guaranteed lifelong monthly pension of ₹1,000 to ₹5,000 after age 60.',
            'tagline_hi' => '60 वर्ष की आयु के बाद ₹1,000 से लेकर ₹5,000 की आजीवन सरकारी गारंटीड मासिक पेंशन।',
            
            'overview_en' => 'Atal Pension Yojana (APY) is India\'s premier flagship social security pension scheme administered by PFRDA aimed at creating a universal social security system for all citizens, especially the unorganized sector workers, farmers, and daily laborers. Subscribers receive a guaranteed minimum monthly pension of ₹1,000, ₹2,000, ₹3,000, ₹4,000, or ₹5,000 after attaining the age of 60 years.',
            'overview_hi' => 'अटल पेंशन योजना (APY) भारत सरकार की प्रमुख सामाजिक सुरक्षा योजना है जो असंगठित क्षेत्र के कामगारों, मजदूरों, किसानों और छोटे दुकानदारों को बुढ़ापे में वित्तीय सुरक्षा देती है। 18 से 40 वर्ष की आयु में छोटा सा मासिक अंशदान जमा करने पर 60 वर्ष की आयु पूरी होने के बाद हर महीने ₹1,000 से लेकर ₹5,000 की आजीवन गारंटीड पेंशन मिलती है।',
            
            'benefits_en' => [
                'Guaranteed lifelong monthly pension of ₹1,000, ₹2,000, ₹3,000, ₹4,000, or ₹5,000 after reaching 60.',
                'Same pension amount continues to the surviving spouse after subscriber\'s demise.',
                'Return of full accumulated pension corpus (up to ₹8.5 Lakh) to the nominee after demise of both subscriber and spouse.',
                'Very low monthly contribution starting as low as ₹42 per month (for age 18, ₹1,000 pension).',
                'Income tax deductions under Section 80CCD.'
            ],
            'benefits_hi' => [
                '60 वर्ष की आयु पूरी होने के बाद ₹1,000 से ₹5,000 की आजीवन मासिक पेंशन की पक्की सरकारी गारंटी।',
                'सब्सक्राइबर की मृत्यु के बाद उसके पति/पत्नी को भी जीवनभर उतनी ही पेंशन मिलती रहेगी।',
                'दोनों के निधन के बाद पूरा संचित पेंशन फंड (₹8.5 लाख तक) नॉमिनी (बच्चों) को लौटा दिया जाता है।',
                'मात्र ₹42 प्रति माह से शुरू होने वाला सबसे किफायती अंशदान (18 वर्ष की आयु पर)।',
                'आयकर में धारा 80CCD के तहत टैक्स छूट का लाभ।'
            ],
            
            'eligibility_en' => [
                'Any Indian citizen between 18 and 40 years of age.',
                'Must have an active savings bank account or post-office savings account.',
                'Exclusion: From 1st Oct 2022, citizens who are or have been Income Tax payers are not eligible to join.'
            ],
            'eligibility_hi' => [
                '18 से 40 वर्ष की आयु का कोई भी भारतीय नागरिक।',
                'बैंक या डाकघर में सक्रिय बचत खाता होना अनिवार्य।',
                'अपात्रता: 1 अक्टूबर 2022 से आयकर दाता (Income Tax Payers) इस योजना में नए खाते नहीं खोल सकते।'
            ],
            
            'documents_en' => [
                'Aadhaar Card.',
                'Bank or Post Office Savings Passbook (auto-debit facility).',
                'Mobile number linked to bank account.',
                'Nominee details (Aadhaar & relationship).'
            ],
            'documents_hi' => [
                'आधार कार्ड।',
                'बैंक या डाकघर की पासबुक (ऑटो-डेबिट सुविधा हेतु)।',
                'बैंक से लिंक मोबाइल नंबर।',
                'नॉमिनी (वारिस) का विवरण और आधार।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Visit your bank branch or access net banking/mobile banking app.',
                'Step 2: Fill out the APY registration form and choose your desired pension amount (₹1,000 to ₹5,000).',
                'Step 3: Provide Aadhaar and nominee details, and set up monthly/quarterly auto-debit.',
                'Step 4: Receive your Permanent Retirement Account Number (PRAN) via SMS.',
                'Step 5: Download your e-PRAN card from npscra.nsdl.co.in.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: अपने बैंक की शाखा में जाएं या मोबाइल बैंकिंग ऐप से APY विकल्प चुनें।',
                'चरण 2: अपनी पसंद की पेंशन राशि (₹1,000 से ₹5,000) चुनें।',
                'चरण 3: आधार और नॉमिनी का विवरण भरें और खाते से ऑटो-डेबिट चालू कराएं।',
                'चरण 4: आपके मोबाइल पर PRAN नंबर का एसएमएस आ जाएगा।',
                'चरण 5: NSDL पोर्टल से अपना डिजिटल ई-प्रान (e-PRAN) कार्ड डाउनलोड करें।'
            ],
            
            'official_url' => 'https://www.npscra.nsdl.co.in/',
            'helpline' => '1800-110-069',
            
            'audio_text_en' => 'Welcome to Atal Pension Yojana overview. Citizens aged 18 to 40 can secure a guaranteed lifelong monthly pension up to five thousand rupees after age sixty. Start your auto-debit subscription at any bank branch today.',
            'audio_text_hi' => 'अटल पेंशन योजना में आपका स्वागत है। 18 से 40 वर्ष के नागरिक मात्र कुछ रुपयों के मासिक अंशदान से 60 की उम्र के बाद ₹5,000 तक की आजीवन मासिक पेंशन सुरक्षित कर सकते हैं। अपने बैंक में जाकर आज ही खाता खुलवाएं।',
            
            'eligibility_checker' => [
                'q1_en' => 'Are you an Indian citizen between 18 and 40 years of age with an active bank account?',
                'q1_hi' => 'क्या आपकी आयु 18 से 40 वर्ष के बीच है और आपका बैंक में खाता है?',
                'q2_en' => 'Are you an Income Tax payer?',
                'q2_hi' => 'क्या आप इनकम टैक्स (आयकर) भरते हैं?',
                'expected' => ['q1' => 'yes', 'q2' => 'no'],
                'eligible_msg_en' => 'You are eligible to join Atal Pension Yojana and secure up to ₹5,000 monthly pension!',
                'eligible_msg_hi' => 'आप अटल पेंशन योजना में शामिल होकर ₹5,000 तक की आजीवन पेंशन पाने के लिए पात्र हैं!',
                'ineligible_msg_en' => 'APY is open to non-taxpayers aged 18 to 40 years.',
                'ineligible_msg_hi' => 'अटल पेंशन योजना 18 से 40 वर्ष के गैर-आयकर दाता नागरिकों के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 15. PM SVANidhi (Vajiram List - Central Sector)
        // ---------------------------------------------------------------------
        'pm-svanidhi' => [
            'id' => 15,
            'slug' => 'pm-svanidhi',
            'icon' => 'fa-store',
            'category' => 'financial_services',
            'category_name_en' => 'Urban Welfare & Street Vendors',
            'category_name_hi' => 'शहरी कल्याण एवं रेहड़ी-पटरी',
            'funding_type' => 'central_sector',
            'funding_type_label_en' => 'Central Sector Scheme (100% Central Funded)',
            'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (100% केंद्र सरकार द्वारा वित्तपोषित)',
            'ministry_en' => 'Ministry of Housing and Urban Affairs (MoHUA)',
            'ministry_hi' => 'आवासन एवं शहरी कार्य मंत्रालय',
            'launch_year' => '2020',
            'budget' => '₹10,000 Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'PM SVANidhi (Street Vendor\'s AtmaNirbhar Nidhi)',
            'title_hi' => 'प्रधानमंत्री स्वनिधि योजना (PM SVANidhi)',
            'tagline_en' => 'Collateral-free working capital micro-loans of ₹10,000, ₹20,000, and ₹50,000 with 7% interest subsidy for street vendors.',
            'tagline_hi' => 'रेहड़ी-पटरी व ठेला लगाने वालों को बिना गारंटी ₹10,000, ₹20,000 और ₹50,000 का सस्ता लोन।',
            
            'overview_en' => 'PM SVANidhi is a Central Sector micro-credit scheme launched to empower street vendors, hawkers, thela-walas, and small roadside service providers to restart and expand their livelihoods. The scheme offers an initial working capital loan of up to ₹10,000, followed by ₹20,000 in the second tranche and up to ₹50,000 in the third tranche upon timely repayment, along with 7% annual interest subsidy and digital cashback.',
            'overview_hi' => 'प्रधानमंत्री स्वनिधि योजना शहरी और कस्बाई क्षेत्रों में रेहड़ी-पटरी, ठेला, खोखा या फुटपाथ पर दुकान लगाने वाले छोटे दुकानदारों को बिना किसी गारंटी के आसान लोन देने की केंद्रीय योजना है। इसमें पहली बार में ₹10,000, समय पर चुकाने पर दूसरी बार में ₹20,000 और तीसरी बार में ₹50,000 तक का लोन मिलता है। सरकार 7% ब्याज सब्सिडी और ₹1,200 सालाना तक का डिजिटल कैशबैक देती है।',
            
            'benefits_en' => [
                '100% collateral-free working capital loan: ₹10,000 (1st Tranche), ₹20,000 (2nd Tranche), ₹50,000 (3rd Tranche).',
                '7% per annum interest subsidy directly credited to your bank account via DBT.',
                'Cashback incentive up to ₹100 per month (₹1,200/year) on accepting digital payments via QR codes.',
                'Zero penalty on early or pre-closure repayment.',
                'Eligible for PM SVANidhi Se Samriddhi social security coverage for vendor\'s entire family.'
            ],
            'benefits_hi' => [
                'बिना किसी गारंटी का लोन: पहली किस्त ₹10,000, दूसरी किस्त ₹20,000, और तीसरी किस्त ₹50,000।',
                'समय पर किस्त चुकाने पर 7% वार्षिक ब्याज की सीधी सब्सिडी बैंक खाते में।',
                'क्यूआर कोड से यूपीआई पेमेंट लेने पर ₹100 प्रति माह (₹1,200 सालाना) तक का मुफ्त कैशबैक।',
                'समय से पहले लोन चुकाने पर कोई पेनल्टी या जुर्माना नहीं।',
                '"स्वनिधि से समृद्धि" के तहत परिवार को आयुष्मान भारत, सुरक्षा बीमा और जीवन ज्योति योजनाओं का सीधा लाभ।'
            ],
            
            'eligibility_en' => [
                'Street vendors, hawkers, thela-walas engaged in vending in urban, semi-urban, or peri-urban areas on or before March 2020 (and subsequent updates).',
                'Vendors in possession of Certificate of Vending (CoV) / Identity Card issued by Urban Local Bodies (ULBs).',
                'Vendors identified in ULB surveys or possessing Letter of Recommendation (LoR).'
            ],
            'eligibility_hi' => [
                'शहरी, कस्बाई या ग्रामीण सीमावर्ती इलाकों में ठेला, खोमचा, रेहड़ी-पटरी या फुटपाथ पर सामान बेचने वाले।',
                'नगर पालिका, नगर निगम या नगर पंचायत द्वारा जारी वेंडिंग प्रमाण पत्र या पहचान पत्र।',
                'सर्वे में छूटे वेंडर्स नगर पालिका से अनुशंसा पत्र (Letter of Recommendation - LoR) बनवाकर भी पात्र हैं।'
            ],
            
            'documents_en' => [
                'Aadhaar Card linked with active mobile number.',
                'Vending Certificate / Vending ID / Letter of Recommendation from Municipality.',
                'Bank Account Passbook (Aadhaar linked).',
                'Proof of vending location.'
            ],
            'documents_hi' => [
                'मोबाइल से लिंक आधार कार्ड।',
                'नगर पालिका का वेंडिंग कार्ड या लेटर ऑफ रिकमेंडेशन (LoR)।',
                'बैंक खाता पासबुक (आधार लिंक)।',
                'दुकान/ठेले की फोटो।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Check your vendor search status on pmsvanidhi.mohua.gov.in using mobile number.',
                'Step 2: If not surveyed, get an LoR issued from your local Municipality / Nagar Nigam office.',
                'Step 3: Apply online on pmsvanidhi portal or through any CSC Jan Seva Kendra or banking correspondent.',
                'Step 4: Bank verifies your application and approves the loan within 7-10 working days.',
                'Step 5: ₹10,000 is credited to your bank account; receive a free UPI QR code for digital cashback.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: pmsvanidhi.mohua.gov.in पोर्टल पर अपने मोबाइल नंबर से अपना नाम चेक करें।',
                'चरण 2: यदि नाम नहीं है तो नगर पालिका से एक पेज का अनुशंसा पत्र (LoR) बनवाएं।',
                'चरण 3: पोर्टल पर या जन सेवा केंद्र से ऑनलाइन आवेदन करें।',
                'चरण 4: बैंक 7 से 10 दिनों में लोन मंजूर करके सीधे खाते में ₹10,000 ट्रांसफर कर देगा।',
                'चरण 5: दुकान पर क्यूआर कोड लगाएं और समय पर किस्त भरकर अगली बार ₹20,000 का लोन पाएं।'
            ],
            
            'official_url' => 'https://pmsvanidhi.mohua.gov.in/',
            'helpline' => '1800-11-1979',
            
            'audio_text_en' => 'Welcome to PM SVANidhi scheme. Street vendors and hawkers can get collateral-free working capital loans starting at ten thousand rupees up to fifty thousand rupees with seven percent interest subsidy and cashback. Apply at your local municipality or online.',
            'audio_text_hi' => 'पीएम स्वनिधि योजना में आपका स्वागत है। रेहड़ी-पटरी और ठेला लगाने वाले छोटे दुकानदार बिना गारंटी ₹10,000 से लेकर ₹50,000 तक का सस्ता लोन ले सकते हैं। 7% ब्याज सब्सिडी और ₹1,200 सालाना कैशबैक पाने के लिए आज ही आवेदन करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Are you a street vendor, hawker, or small roadside seller in an urban or semi-urban area?',
                'q1_hi' => 'क्या आप किसी शहर या कस्बे में ठेला, रेहड़ी-पटरी, खोमचा या फुटपाथ पर दुकान लगाते हैं?',
                'q2_en' => 'Do you have an Aadhaar card and an active bank account?',
                'q2_hi' => 'क्या आपके पास आधार कार्ड और बैंक खाता है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible to receive collateral-free micro-credit under PM SVANidhi!',
                'eligible_msg_hi' => 'आप पीएम स्वनिधि के तहत बिना गारंटी ₹10,000 से ₹50,000 का लोन पाने के लिए पूरी तरह पात्र हैं!',
                'ineligible_msg_en' => 'PM SVANidhi is designed specifically for street vendors and roadside micro-entrepreneurs.',
                'ineligible_msg_hi' => 'यह योजना रेहड़ी-पटरी और फुटपाथ पर दुकान लगाने वाले वेंडरों के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 16. Pradhan Mantri Matru Vandana Yojana (PMMVY - Vajiram List - CSS)
        // ---------------------------------------------------------------------
        'pm-matru-vandana' => [
            'id' => 16,
            'slug' => 'pm-matru-vandana',
            'icon' => 'fa-baby-carriage',
            'category' => 'women_child',
            'category_name_en' => 'Women & Child Development',
            'category_name_hi' => 'महिला एवं बाल विकास',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Shared 60:40 Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (60:40 केंद्र और राज्य सरकार द्वारा साझा)',
            'ministry_en' => 'Ministry of Women and Child Development',
            'ministry_hi' => 'महिला एवं बाल विकास मंत्रालय',
            'launch_year' => '2017',
            'budget' => '₹12,661 Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'PM Matru Vandana Yojana (PMMVY)',
            'title_hi' => 'प्रधानमंत्री मातृ वंदना योजना (PMMVY)',
            'tagline_en' => 'Direct maternity cash incentive of ₹5,000 to ₹6,000 for pregnant women and lactating mothers.',
            'tagline_hi' => 'गर्भवती महिलाओं और स्तनपान कराने वाली माताओं को ₹5,000 से ₹6,000 की सीधी नकद सहायता।',
            
            'overview_en' => 'PMMVY is a Centrally Sponsored direct benefit transfer maternity benefit scheme providing partial compensation for wage loss during pregnancy and childbirth so that the woman can take adequate rest before and after delivery. It provides ₹5,000 in two installments for the first child, and an enhanced incentive of ₹6,000 in a single installment if the second child is a girl under Mission Shakti.',
            'overview_hi' => 'प्रधानमंत्री मातृ वंदना योजना (PMMVY) मिशन शक्ति के तहत गर्भवती महिलाओं और धात्री माताओं को पोषण और मजदूरी की क्षतिपूर्ति के लिए सीधी आर्थिक सहायता देने की प्रमुख योजना है। पहले बच्चे के जन्म पर ₹5,000 की नकद सहायता दो किस्तों में दी जाती है, और यदि दूसरी संतान बालिका (बेटी) होती है तो कन्या भ्रूण हत्या रोकने और पोषण प्रोत्साहन हेतु ₹6,000 की एकमुश्त सहायता मिलती है।',
            
            'benefits_en' => [
                'Direct cash benefit of ₹5,000 for the first child (Installment 1: ₹3,000 on early registration; Installment 2: ₹2,000 after child birth and first cycle of immunization).',
                '₹6,000 in a single installment for the second child, provided the second child is a girl.',
                'Funds transferred directly to the mother\'s Aadhaar-seeded bank account through DBT.',
                'Improves maternal and infant nutrition and encourages institutional delivery.',
                'Combined with Janani Suraksha Yojana (JSY), total maternity cash support exceeds ₹6,400.'
            ],
            'benefits_hi' => [
                'पहले बच्चे के जन्म पर ₹5,000 की सीधी बैंक सहायता (पहली किस्त ₹3,000 पंजीकरण पर, दूसरी किस्त ₹2,000 बच्चे के टीकाकरण पर)।',
                'दूसरी संतान यदि बेटी (बालिका) होती है तो एकमुश्त ₹6,000 की विशेष नकद सहायता।',
                'पैसा सीधे माता के आधार-लिंक्ड बैंक खाते में डीबीटी से ट्रांसफर।',
                'गर्भावस्था के दौरान बेहतर पौष्टिक आहार और सुरक्षित संस्थागत प्रसव (अस्पताल में डिलीवरी) को बढ़ावा।',
                'जननी सुरक्षा योजना (JSY) के साथ मिलाकर कुल सहायता ₹6,400 से अधिक हो जाती है।'
            ],
            
            'eligibility_en' => [
                'Pregnant women and lactating mothers belonging to socially and economically disadvantaged categories.',
                'Women with annual family income less than ₹8 Lakh, or holding NFSA Ration Card, MGNREGA Job Card, or Ayushman Bharat Card.',
                'Exclusion: Women who are regular employees of Central/State Government or PSUs.'
            ],
            'eligibility_hi' => [
                'गरीब और मध्यम वर्गीय परिवारों की गर्भवती महिलाएं और धात्री माताएं।',
                'जिनके परिवार की वार्षिक आय ₹8 लाख से कम हो या जिनके पास राशन कार्ड, मनरेगा जॉब कार्ड या आयुष्मान कार्ड हो।',
                'अपात्रता: केंद्र या राज्य सरकार या सार्वजनिक उपक्रमों में नियमित नौकरी करने वाली महिलाएं।'
            ],
            
            'documents_en' => [
                'Aadhaar Card of mother and husband.',
                'Mother and Child Protection (MCP) Card issued by Anganwadi/Health Center.',
                'Bank Passbook of the mother (Aadhaar linked for DBT).',
                'Child Birth Certificate and Immunization Record.'
            ],
            'documents_hi' => [
                'माता और पति का आधार कार्ड।',
                'आंगनबाड़ी या अस्पताल का मातृ एवं बाल सुरक्षा (MCP / ममता) कार्ड।',
                'माता का आधार से लिंक बैंक खाता पासबुक।',
                'बच्चे का जन्म प्रमाण पत्र और टीकाकरण कार्ड।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Register pregnancy within 570 days of Last Menstrual Period (LMP) at your local Anganwadi Centre or ASHA.',
                'Step 2: Fill out Form 1A on the national PMMVY portal (pmmvy.wcd.gov.in).',
                'Step 3: Verification conducted by Child Development Project Officer (CDPO).',
                'Step 4: 1st installment of ₹3,000 credited to mother\'s bank account.',
                'Step 5: Submit child birth and BCG/OPV/DPT vaccination record for 2nd installment of ₹2,000.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: गर्भावस्था के दौरान अपनी नजदीकी आंगनबाड़ी कार्यकर्ता या आशा दीदी से संपर्क करके पंजीकरण कराएं।',
                'चरण 2: pmmvy.wcd.gov.in पोर्टल पर या आंगनबाड़ी के माध्यम से ऑनलाइन फॉर्म भरें।',
                'चरण 3: सीडीपीओ (CDPO) द्वारा फॉर्म की जांच और सत्यापन किया जाता है।',
                'चरण 4: पहली किस्त ₹3,000 सीधे महिला के बैंक खाते में भेज दी जाती है।',
                'चरण 5: अस्पताल में बच्चे के जन्म और पहले चरण के टीकों के बाद ₹2,000 की दूसरी किस्त खाते में आ जाती है।'
            ],
            
            'official_url' => 'https://pmmvy.wcd.gov.in/',
            'helpline' => '14408 / 1098 (Childline)',
            
            'audio_text_en' => 'Welcome to PM Matru Vandana Yojana overview. Pregnant women and lactating mothers receive up to six thousand rupees direct financial assistance in their bank accounts for nutrition and healthcare. Register at your local Anganwadi centre or online on pmmvy.wcd.gov.in.',
            'audio_text_hi' => 'प्रधानमंत्री मातृ वंदना योजना में आपका स्वागत है। गर्भवती माताओं को अच्छे पोषण और देखभाल के लिए ₹5,000 से लेकर ₹6,000 तक की सीधी बैंक सहायता दी जाती है। अपनी नजदीकी आंगनबाड़ी या pmmvy.wcd.gov.in पर आवेदन करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Are you a pregnant woman or nursing mother without a permanent government job?',
                'q1_hi' => 'क्या आप गर्भवती महिला या धात्री माता हैं और किसी सरकारी नौकरी में नहीं हैं?',
                'q2_en' => 'Do you have an active bank account linked with your Aadhaar card and an MCP card?',
                'q2_hi' => 'क्या आपके पास आधार से लिंक बैंक खाता और आंगनबाड़ी का ममता कार्ड है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible to receive up to ₹6,000 maternity cash benefit under PMMVY!',
                'eligible_msg_hi' => 'आप मातृ वंदना योजना के तहत ₹5,000 से ₹6,000 की नकद सहायता पाने के लिए पात्र हैं!',
                'ineligible_msg_en' => 'PMMVY assistance is available for non-government employed mothers.',
                'ineligible_msg_hi' => 'यह योजना गैर-सरकारी कामकाजी माताओं के पोषण और स्वास्थ्य के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 17. PM Fasal Bima Yojana (PMFBY - Centrally Sponsored)
        // ---------------------------------------------------------------------
        'pm-fasal-bima' => [
            'id' => 17,
            'slug' => 'pm-fasal-bima',
            'icon' => 'fa-shield-alt',
            'category' => 'agriculture',
            'category_name_en' => 'Crop Insurance & Agriculture',
            'category_name_hi' => 'फसल बीमा एवं कृषि सुरक्षा',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Shared 50:50 Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (50:50 केंद्र और राज्य सरकार द्वारा साझा)',
            'ministry_en' => 'Ministry of Agriculture & Farmers Welfare',
            'ministry_hi' => 'कृषि एवं किसान कल्याण मंत्रालय',
            'launch_year' => '2016',
            'budget' => '₹15,000 Crore annually',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'Haryana', 'Odisha', 'Chhattisgarh', 'Karnataka'],
            
            'title_en' => 'PM Fasal Bima Yojana (PMFBY)',
            'title_hi' => 'प्रधानमंत्री फसल बीमा योजना (PMFBY)',
            'tagline_en' => 'Comprehensive insurance protection against crop loss due to drought, flood, hailstorm, or pest attacks at lowest premium.',
            'tagline_hi' => 'सूखा, बाढ़, ओलावृष्टि और कीट प्रकोप से फसल बर्बादी पर न्यूनतम प्रीमियम में पूरा मुआवजा।',
            
            'overview_en' => 'PMFBY provides comprehensive insurance coverage and financial support to farmers suffering crop loss or damage arising out of natural calamities, pests, and diseases. Farmers pay a uniform low premium of only 2% for Kharif food/oilseed crops, 1.5% for Rabi crops, and 5% for annual commercial/horticultural crops, with the balance premium subsidized by the Government.',
            'overview_hi' => 'प्रधानमंत्री फसल बीमा योजना (PMFBY) किसानों को प्राकृतिक आपदाओं, बेमौसम बारिश, ओलावृष्टि, सूखा, कीट और बीमारियों से फसल नष्ट होने पर पूरा आर्थिक सुरक्षा कवच प्रदान करती है। किसान को केवल 2% (खरीफ फसल), 1.5% (रबी फसल) और 5% (बागवानी फसल) का बेहद कम प्रीमियम देना होता है। बाकी का सारा प्रीमियम केंद्र और राज्य सरकार मिलकर भरती हैं।',
            
            'benefits_en' => [
                'Lowest premium for farmers: 2% for Kharif crops, 1.5% for Rabi crops, 5% for commercial/horticultural crops.',
                'Full sum insured coverage with zero capping on government subsidy.',
                'Covers entire crop cycle: Prevented sowing, standing crop losses, post-harvest losses, and localized calamities (hailstorms, landslide, cloudburst).',
                'Crop Insurance App allows farmers to report crop damage within 72 hours using mobile geotagging.',
                'Claims settled directly into bank accounts via the DigiClaim module.'
            ],
            'benefits_hi' => [
                'न्यूनतम प्रीमियम दरें: खरीफ के लिए 2%, रबी फसलों के लिए मात्र 1.5%, और बागवानी के लिए 5%।',
                'दावे पर पूरी बीमित राशि का भुगतान, सरकार द्वारा सब्सिडी पर कोई ऊपरी सीमा नहीं।',
                'फसल के हर चरण का बीमा: बुआई न होने से लेकर खड़ी फसल और कटाई के 14 दिन बाद तक होने वाले नुकसान का कवर।',
                'फसल नुकसान की सूचना किसान सीधे मोबाइल ऐप (Crop Insurance App) से 72 घंटे में फोटो सहित दर्ज कर सकते हैं।',
                'डिजी-क्लेम (DigiClaim) से मुआवजा सीधे किसान के बैंक खाते में बिना चक्कर लगाए आता है।'
            ],
            
            'eligibility_en' => [
                'All farmers growing notified crops in notified areas, including sharecroppers and tenant farmers.',
                'Voluntary for all farmers (both loanee and non-loanee farmers).',
                'Must have insurable interest in the insured crop.'
            ],
            'eligibility_hi' => [
                'अधिसूचित क्षेत्र में अधिसूचित फसलें उगाने वाले सभी किसान, जिनमें बंटाईदार और काश्तकार भी शामिल हैं।',
                'केसीसी लोन लेने वाले और बिना लोन वाले सभी किसानों के लिए स्वैच्छिक।',
                'किसान के पास संबंधित जमीन पर बुआई का प्रमाण होना चाहिए।'
            ],
            
            'documents_en' => [
                'Aadhaar Card.',
                'Land Record Documents (Khatauni / Khasra / Bahi).',
                'Sowing Certificate / Patwari Report.',
                'Bank Account Passbook (Aadhaar linked).',
                'Tenant Agreement / Sharecropper declaration (if applicable).'
            ],
            'documents_hi' => [
                'आधार कार्ड।',
                'जमीन की खतौनी / खसरा नकल।',
                'फसल बुआई प्रमाण पत्र (पटवारी या ग्राम प्रधान की रिपोर्ट)।',
                'बैंक खाता पासबुक।',
                'बंटाईदार अनुबंध पत्र (यदि किराये पर जमीन ली हो)।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Check the cutoff date for the current cropping season on pmfby.gov.in.',
                'Step 2: Apply through your bank branch, nearest CSC, or directly on the PMFBY portal.',
                'Step 3: Submit crop land details and pay the minimal 1.5% or 2% premium.',
                'Step 4: Receive your insurance policy receipt with unique policy number.',
                'Step 5: In case of unseasonal rain/hailstorm, report on Crop Insurance App within 72 hours for survey and claim.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: pmfby.gov.in पोर्टल या बैंक से फसल बीमा की अंतिम तिथि चेक करें।',
                'चरण 2: अपने बैंक में जाकर या नजदीकी जन सेवा केंद्र (CSC) से ऑनलाइन आवेदन करें।',
                'चरण 3: खतौनी और बुआई प्रमाण पत्र देकर 1.5% या 2% का मामूली प्रीमियम जमा करें।',
                'चरण 4: अपनी बीमा पॉलिसी रसीद प्राप्त करें।',
                'चरण 5: आपदा होने पर 72 घंटे के अंदर Crop Insurance ऐप या टोल फ्री नंबर पर सूचना देकर सर्वे कराएं।'
            ],
            
            'official_url' => 'https://pmfby.gov.in/',
            'helpline' => '14447 / 1800-180-1551',
            
            'audio_text_en' => 'Welcome to PM Fasal Bima Yojana overview. Protect your crops against flood, drought, and hailstorm at just one point five to two percent premium. In case of crop damage, report within seventy-two hours on the Crop Insurance mobile app or call fourteen double-four seven.',
            'audio_text_hi' => 'प्रधानमंत्री फसल बीमा योजना में आपका स्वागत है। मात्र 1.5 से 2 प्रतिशत के न्यूनतम प्रीमियम पर अपनी फसलों का पूरा बीमा कराएं। बाढ़, ओला या बेमौसम बारिश से नुकसान होने पर 72 घंटे के अंदर क्रॉप इंश्योरेंस ऐप पर सूचना दें और मुआवजा पाएं।',
            
            'eligibility_checker' => [
                'q1_en' => 'Are you a farmer cultivating notified food, oilseed, or horticultural crops in an insured area?',
                'q1_hi' => 'क्या आप अधिसूचित क्षेत्र में अनाज, तिलहन या बागवानी फसलों की खेती करते हैं?',
                'q2_en' => 'Do you have land revenue records or valid tenant farming proof?',
                'q2_hi' => 'क्या आपके पास जमीन की खतौनी या वैध बंटाईदार प्रमाण उपलब्ध है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible to insure your crops under PMFBY at the lowest government-subsidized premium!',
                'eligible_msg_hi' => 'आप पीएम फसल बीमा योजना के तहत न्यूनतम प्रीमियम पर अपनी फसल का बीमा कराने के लिए पात्र हैं!',
                'ineligible_msg_en' => 'Crop insurance is available to landholders and verified tenant cultivators growing notified crops.',
                'ineligible_msg_hi' => 'यह योजना वास्तविक फसल उत्पादक किसानों और बंटाईदारों के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 18. Jal Jeevan Mission (Centrally Sponsored)
        // ---------------------------------------------------------------------
        'jal-jeevan-mission' => [
            'id' => 18,
            'slug' => 'jal-jeevan-mission',
            'icon' => 'fa-tint',
            'category' => 'water_sanitation',
            'category_name_en' => 'Water & Sanitation',
            'category_name_hi' => 'पेयजल एवं स्वच्छता',
            'funding_type' => 'centrally_sponsored',
            'funding_type_label_en' => 'Centrally Sponsored Scheme (Shared 50:50 Centre & State)',
            'funding_type_label_hi' => 'केंद्र प्रायोजित योजना (50:50 केंद्र और राज्य सरकार द्वारा साझा)',
            'ministry_en' => 'Ministry of Jal Shakti',
            'ministry_hi' => 'जल शक्ति मंत्रालय',
            'launch_year' => '2019',
            'budget' => '₹3.60 Lakh Crore',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'Jal Jeevan Mission (Har Ghar Jal)',
            'title_hi' => 'जल जीवन मिशन (हर घर जल योजना)',
            'tagline_en' => 'Providing safe and adequate piped drinking water through functional household tap connection (FHTC) to every rural home.',
            'tagline_hi' => 'प्रत्येक ग्रामीण परिवार के घर में नल से शुद्ध और सुरक्षित पीने का पानी (55 लीटर प्रति व्यक्ति प्रतिदिन)।',
            
            'overview_en' => 'Jal Jeevan Mission is a transformational mission launched in 2019 to provide Functional Household Tap Connection (FHTC) to every rural household with the supply of 55 litres of water per capita per day of prescribed quality on a regular and long-term basis. Over 15 Crore rural households have already been provided clean tap water under the scheme.',
            'overview_hi' => 'जल जीवन मिशन 2019 में शुरू किया गया एक ऐतिहासिक अभियान है जिसका उद्देश्य देश के प्रत्येक ग्रामीण परिवार के घर में नल से शुद्ध और पर्याप्त पीने का पानी (प्रति व्यक्ति प्रतिदिन 55 लीटर) पहुंचाना है। इससे ग्रामीण महिलाओं और बेटियों को मीलों दूर से पानी भरकर लाने की भारी परेशानी से हमेशा के लिए मुक्ति मिली है।',
            
            'benefits_en' => [
                'Free installation of Functional Household Tap Connection (FHTC) directly inside the home.',
                'Guaranteed supply of 55 litres of potable water per person per day conforming to BIS 10500 standards.',
                'Water quality testing kits provided to 5 women in every village (Pani Samiti).',
                'Tap water connectivity to all rural schools, Anganwadi centers, and health sub-centers.',
                'Substantial reduction in water-borne diseases like diarrhea, typhoid, and cholera.'
            ],
            'benefits_hi' => [
                'हर ग्रामीण घर के भीतर नल का बिल्कुल मुफ्त कनेक्शन और पानी की सप्लाई।',
                'प्रति व्यक्ति प्रति दिन 55 लीटर शुद्ध, सुरक्षित और कीटाणुरहित पानी की आपूर्ति।',
                'पानी की शुद्धता जांचने के लिए हर गांव में 5 महिलाओं को फील्ड टेस्ट किट की ट्रेनिंग।',
                'गांव के सभी स्कूलों, आंगनबाड़ी केंद्रों, आश्रमों और स्वास्थ्य केंद्रों में नल कनेक्शन।',
                'दूषित पानी से होने वाले रोगों (हैजा, टाइफाइड, डायरिया) से परिवारों को स्थायी मुक्ति।'
            ],
            
            'eligibility_en' => [
                'All rural households located in revenue villages and tribal habitations lacking piped tap water.',
                'Zero discrimination: All community members across every street/mohalla in the village are covered.',
                'Gram Panchayat Village Water & Sanitation Committee (VWSC / Pani Samiti) facilitates connections.'
            ],
            'eligibility_hi' => [
                'देश के सभी ग्रामीण क्षेत्रों और आदिवासी बस्तियों के परिवार जहां नल का कनेक्शन नहीं है।',
                'गांव के हर टोले, मजरे और मोहल्ले के सभी परिवार बिना किसी भेदभाव के पात्र हैं।'
            ],
            
            'documents_en' => [
                'Aadhaar Card of household head.',
                'Electricity Bill / House Tax receipt / Gram Panchayat Residence Certificate.',
                'Application to local Gram Panchayat / Pani Samiti.'
            ],
            'documents_hi' => [
                'परिवार के मुखिया का आधार कार्ड।',
                'ग्राम पंचायत का निवास प्रमाण।',
                'ग्राम पंचायत / पानी समिति को सादे कागज पर आवेदन।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Check your village tap water status on the JJM Dashboard (ejalshakti.gov.in).',
                'Step 2: Submit a tap water connection request to your Gram Panchayat Secretary or village Pani Samiti.',
                'Step 3: Village Action Plan incorporates your household in the pipeline network.',
                'Step 4: Public Health Engineering Department (PHED) contractor lays the pipeline and installs the tap meter/faucet.',
                'Step 5: Village Pani Samiti monitors regular pressure and chlorination testing.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: राष्ट्रीय पोर्टल ejalshakti.gov.in पर अपने गांव और जिले की प्रगति चेक करें।',
                'चरण 2: अपनी ग्राम पंचायत के प्रधान, सचिव या पानी समिति को नल कनेक्शन के लिए नाम दर्ज कराएं।',
                'चरण 3: ग्राम कार्य योजना के तहत पाइपलाइन बिछाकर आपके घर तक कनेक्शन लाया जाएगा।',
                'चरण 4: जल निगम द्वारा आपके घर में नल की टोंटी और मीटर लगाया जाएगा।',
                'चरण 5: पानी की शुद्धता और नियमित सप्लाई की निगरानी ग्राम जल एवं स्वच्छता समिति द्वारा की जाती है।'
            ],
            
            'official_url' => 'https://ejalshakti.gov.in/jjmreport/',
            'helpline' => '1800-180-5678',
            
            'audio_text_en' => 'Welcome to Jal Jeevan Mission Har Ghar Jal overview. Every rural household is entitled to a functional drinking tap water connection providing fifty-five litres of clean water per person every day. Contact your local Gram Panchayat or village water committee for connection details.',
            'audio_text_hi' => 'जल जीवन मिशन हर घर जल योजना में आपका स्वागत है। हर ग्रामीण परिवार को अपने घर में नल से शुद्ध और पर्याप्त पीने का पानी पाने का अधिकार है। अपने घर में नल कनेक्शन लगवाने के लिए अपनी ग्राम पंचायत या पानी समिति से संपर्क करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you reside in a rural village that is part of the Jal Jeevan Mission piped water network?',
                'q1_hi' => 'क्या आप किसी ऐसे ग्रामीण क्षेत्र में रहते हैं जहां नल का कनेक्शन नहीं लगा है?',
                'q2_en' => 'Are you ready to obtain a household tap water connection for clean drinking water?',
                'q2_hi' => 'क्या आप अपने घर में स्वच्छ पेयजल हेतु नल कनेक्शन लगवाने के इच्छुक हैं?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'Your household is eligible for a Functional Household Tap Connection under Jal Jeevan Mission!',
                'eligible_msg_hi' => 'आपका घर जल जीवन मिशन के तहत नल से शुद्ध जल कनेक्शन पाने के लिए पूरी तरह पात्र है!',
                'ineligible_msg_en' => 'JJM focuses on rural habitations and unserved village households.',
                'ineligible_msg_hi' => 'यह योजना ग्रामीण क्षेत्रों के सभी परिवारों को नल से जल उपलब्ध कराने के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 19. PM Shram Yogi Maan-dhan (PM-SYM - Central Sector)
        // ---------------------------------------------------------------------
        'pm-shram-yogi' => [
            'id' => 19,
            'slug' => 'pm-shram-yogi',
            'icon' => 'fa-user-clock',
            'category' => 'social_security',
            'category_name_en' => 'Social Security & Unorganized Workers',
            'category_name_hi' => 'सामाजिक सुरक्षा एवं असंगठित कामगार',
            'funding_type' => 'central_sector',
            'funding_type_label_en' => 'Central Sector Scheme (50:50 Matching Central Contribution)',
            'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (50% सरकार का बराबर अंशदान)',
            'ministry_en' => 'Ministry of Labour and Employment',
            'ministry_hi' => 'श्रम एवं रोजगार मंत्रालय',
            'launch_year' => '2019',
            'budget' => 'LIC Administered Corpus',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'PM Shram Yogi Maan-dhan (PM-SYM)',
            'title_hi' => 'प्रधानमंत्री श्रम योगी मानधन योजना (PM-SYM)',
            'tagline_en' => 'Assured monthly pension of ₹3,000 for unorganized workers like rickshaw pullers, domestic maids, and laborers.',
            'tagline_hi' => 'रिक्शा चालक, घरेलू सहायिका और असंगठित कामगारों के लिए ₹3,000 की मासिक पेंशन की गारंटी।',
            
            'overview_en' => 'PM-SYM is a voluntary and contributory pension scheme for unorganized workers (home-based workers, street vendors, mid-day meal workers, head loaders, brick kiln workers, cobblers, rag pickers, domestic workers, rickshaw pullers, landless laborers) whose monthly income is ₹15,000 or less. The subscriber contributes 50% of the premium and an equal 50% matching contribution is deposited by the Central Government.',
            'overview_hi' => 'प्रधानमंत्री श्रम योगी मानधन योजना (PM-SYM) असंगठित क्षेत्र के कामगारों (जैसे रिक्शा चालक, घरेलू सहायिका, निर्माण मजदूर, रेहड़ी-पटरी वाले, दर्जी, मोची आदि) के लिए स्वैच्छिक पेंशन योजना है, जिनकी मासिक आय ₹15,000 से कम है। इसमें जितनी राशि कामगार जमा करता है, उतनी ही 50% बराबर राशि केंद्र सरकार अपनी ओर से जमा करती है, और 60 वर्ष के बाद ₹3,000 की मासिक पेंशन मिलती है।',
            
            'benefits_en' => [
                'Assured minimum monthly pension of ₹3,000 upon attaining 60 years of age.',
                'Equal 50% matching monthly contribution deposited by the Central Government.',
                '50% family pension (₹1,500/month) to surviving spouse in case of subscriber\'s death.',
                'Nominal monthly contribution ranging from only ₹55 (at age 18) to ₹200 (at age 40).',
                'Managed through Life Insurance Corporation of India (LIC) ensuring 100% government safety.'
            ],
            'benefits_hi' => [
                '60 वर्ष की आयु पूरी होने पर जीवनभर ₹3,000 की पक्की मासिक पेंशन।',
                'जितना अंशदान कामगार जमा करेगा, उतना ही 50% बराबर अंशदान केंद्र सरकार जमा करेगी।',
                'कामगार की मृत्यु होने पर पत्नी/पति को 50% पारिवारिक पेंशन (₹1,500 प्रति माह)।',
                'मात्र ₹55 (18 वर्ष की आयु पर) से लेकर अधिकतम ₹200 प्रति माह का छोटा सा अंशदान।',
                'भारतीय जीवन बीमा निगम (LIC) द्वारा प्रबंधित पूर्णतः सुरक्षित और विश्वसनीय।'
            ],
            
            'eligibility_en' => [
                'Unorganized workers aged 18 to 40 years.',
                'Monthly income of applicant should not exceed ₹15,000 per month.',
                'Should not be engaged in organized sector (not covered under EPFO, ESIC, or NPS).',
                'Should not be an Income Tax payer.'
            ],
            'eligibility_hi' => [
                'असंगठित क्षेत्र के कामगार जिनकी आयु 18 से 40 वर्ष के बीच हो।',
                'मासिक आय ₹15,000 या उससे कम होनी चाहिए।',
                'ईपीएफओ (EPFO), ईएसआईसी (ESIC) या एनपीएस के सदस्य नहीं होने चाहिए।',
                'आयकर दाता नहीं होने चाहिए।'
            ],
            
            'documents_en' => [
                'Aadhaar Card.',
                'Savings Bank Account Passbook / Jan Dhan Account with IFSC.',
                'Active Mobile Number.',
                'e-Shram Card / UAN Number (if registered).'
            ],
            'documents_hi' => [
                'आधार कार्ड।',
                'बचत बैंक खाता या जन-धन बैंक खाता पासबुक।',
                'चालू मोबाइल नंबर।',
                'ई-श्रम कार्ड (e-Shram Card) नंबर।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Visit any Common Services Centre (CSC) or apply on maandhan.in.',
                'Step 2: Provide Aadhaar and bank details for biometric eKYC.',
                'Step 3: Initial contribution (₹55 - ₹200) is paid in cash at CSC.',
                'Step 4: Mandate form signed for automatic monthly bank deduction.',
                'Step 5: Instant generation of your laminated Shram Yogi Pension Card.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: अपने आधार और बैंक पासबुक के साथ नजदीकी जन सेवा केंद्र (CSC) पर जाएं।',
                'चरण 2: फिंगरप्रिंट लगाकर बायोमेट्रिक प्रमाणीकरण कराएं।',
                'चरण 3: पहले महीने का अंशदान (₹55 से ₹200) सीएससी पर नकद जमा करें।',
                'चरण 4: खाते से ऑटो-डेबिट का ऑटोमेटिक फॉर्म साइन करें।',
                'चरण 5: तुरंत आपका श्रम योगी पेंशन कार्ड (Shram Yogi Card) बनकर तैयार हो जाएगा।'
            ],
            
            'official_url' => 'https://maandhan.in/',
            'helpline' => '1800-267-6888 / 14434',
            
            'audio_text_en' => 'Welcome to PM Shram Yogi Maan-dhan overview. Unorganized workers earning under fifteen thousand rupees per month can contribute as little as fifty-five rupees monthly, with fifty percent matched by the government, to receive three thousand rupees monthly pension after age sixty. Enroll at any CSC.',
            'audio_text_hi' => 'प्रधानमंत्री श्रम योगी मानधन योजना में आपका स्वागत है। असंगठित कामगार और दैनिक मजदूर मात्र ₹55 से ₹200 का मासिक अंशदान करके 60 साल की उम्र के बाद ₹3,000 की पक्की मासिक पेंशन पा सकते हैं। अपने नजदीकी जन सेवा केंद्र पर जाकर पेंशन कार्ड बनवाएं।',
            
            'eligibility_checker' => [
                'q1_en' => 'Are you an unorganized worker aged 18 to 40 with monthly income of ₹15,000 or less?',
                'q1_hi' => 'क्या आप असंगठित कामगार हैं, उम्र 18 से 40 वर्ष है और मासिक कमाई ₹15,000 या कम है?',
                'q2_en' => 'Are you non-EPFO, non-ESIC, and not an Income Tax payer?',
                'q2_hi' => 'क्या आप ईपीएफ, ईएसआईसी के सदस्य नहीं हैं और इनकम टैक्स नहीं भरते?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible to join PM-SYM and secure a guaranteed ₹3,000 monthly pension!',
                'eligible_msg_hi' => 'आप पीएम श्रम योगी मानधन योजना में जुड़कर ₹3,000 मासिक पेंशन पाने के लिए पात्र हैं!',
                'ineligible_msg_en' => 'PM-SYM is reserved for unorganized sector workers earning under ₹15,000 per month.',
                'ineligible_msg_hi' => 'यह योजना केवल ₹15,000 से कम मासिक आय वाले असंगठित कामगारों के लिए है।'
            ]
        ],

        // ---------------------------------------------------------------------
        // 20. Kisan Credit Card (KCC - Central Sector)
        // ---------------------------------------------------------------------
        'kisan-credit-card' => [
            'id' => 20,
            'slug' => 'kisan-credit-card',
            'icon' => 'fa-credit-card',
            'category' => 'agriculture',
            'category_name_en' => 'Agricultural Credit & Farmers',
            'category_name_hi' => 'कृषि ऋण एवं किसान सहायता',
            'funding_type' => 'central_sector',
            'funding_type_label_en' => 'Central Sector Scheme (Interest Subvention by Central Govt)',
            'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (केंद्र सरकार द्वारा 3% ब्याज छूट)',
            'ministry_en' => 'Ministry of Agriculture and Farmers Welfare & RBI',
            'ministry_hi' => 'कृषि एवं किसान कल्याण मंत्रालय एवं आरबीआई',
            'launch_year' => '1998',
            'budget' => '₹20 Lakh Crore Target',
            'applicable_scope' => 'all_india',
            'applicable_states' => ['All States & UTs', 'Uttar Pradesh', 'Bihar', 'Rajasthan', 'Madhya Pradesh', 'Maharashtra', 'West Bengal', 'Haryana', 'Punjab', 'Gujarat', 'Odisha', 'Jharkhand'],
            
            'title_en' => 'Kisan Credit Card (KCC)',
            'title_hi' => 'किसान क्रेडिट कार्ड (KCC)',
            'tagline_en' => 'Subsidized institutional short-term crop loans up to ₹3 Lakh at an effective interest rate of only 4%.',
            'tagline_hi' => 'समय पर भुगतान करने पर मात्र 4% की रियायती ब्याज दर पर ₹3 लाख तक का आसान कृषि ऋण।',
            
            'overview_en' => 'The Kisan Credit Card (KCC) scheme was introduced to provide adequate and timely credit support from the banking system to farmers for their agricultural production expenses, allied activities, and post-harvest expenses. The nominal interest rate of 7% is reduced to an effective 4% per annum for farmers who repay their loans promptly, courtesy of a 3% prompt repayment incentive funded by the Central Government.',
            'overview_hi' => 'किसान क्रेडिट कार्ड (KCC) योजना किसानों को खाद, बीज, कीटनाशक, डीजल और फसल कटाई के खर्चों के लिए बैंकों से बहुत ही कम ब्याज पर समय पर ऋण उपलब्ध कराती है। सामान्यतः कृषि लोन पर 7% ब्याज होता है, लेकिन समय पर लोन चुकाने वाले किसानों को केंद्र सरकार 3% की अतिरिक्त ब्याज छूट (सब्सिडी) देती है, जिससे प्रभावी ब्याज दर मात्र 4% सालाना रह जाती है। पशुपालक और मछली पालक भी अब KCC बनवा सकते हैं।',
            
            'benefits_en' => [
                'Subsidized loan up to ₹3,00,000 at an effective interest rate of only 4% per annum upon prompt repayment.',
                'Collateral-free loan limit up to ₹1,60,000 (no land mortgage required).',
                'Includes Animal Husbandry, Dairy, and Fisheries credit up to ₹2,00,000 at the same subsidized 4% rate.',
                'Issued as an ATM-enabled RuPay Kisan Credit Card for flexible round-the-clock cash withdrawals.',
                'Simple one-page application form linked directly to PM-KISAN database.'
            ],
            'benefits_hi' => [
                'समय पर कर्ज चुकाने पर मात्र 4% की सालाना ब्याज दर पर ₹3,00,000 तक का सस्ता लोन।',
                '₹1,60,000 तक के लोन के लिए बिना किसी जमीन को गिरवी रखे (कोलैटरल-फ्री) लोन की सुविधा।',
                'डेयरी, पशुपालन और मछली पालन करने वालों को भी ₹2 लाख तक का KCC लोन मात्र 4% ब्याज पर।',
                'RuPay किसान क्रेडिट कार्ड एटीएम कार्ड के रूप में मिलता है, जिससे कभी भी पैसे निकाल सकते हैं।',
                'पीएम-किसान पोर्टल से जुड़ा आसान एक-पेज का आवेदन फॉर्म, 14 दिनों के भीतर बैंक द्वारा मंजूरी।'
            ],
            
            'eligibility_en' => [
                'All farmers - individuals/joint borrowers who are owner cultivators.',
                'Tenant farmers, oral lessees, and sharecroppers.',
                'Self-Help Groups (SHGs) or Joint Liability Groups (JLGs) of farmers.',
                'Animal husbandry, poultry, dairy, and inland/marine fishery farmers.'
            ],
            'eligibility_hi' => [
                'सभी किसान—व्यक्तिगत या संयुक्त किसान जिनके नाम पर कृषि भूमि है।',
                'काश्तकार, बंटाईदार और पट्टेदार किसान।',
                'किसानों के स्वयं सहायता समूह (SHG) या संयुक्त देयता समूह (JLG)।',
                'दुधारू पशु पालने वाले (डेयरी), मुर्गी पालन, बकरी पालन या मत्स्य पालन करने वाले किसान।'
            ],
            
            'documents_en' => [
                'Aadhaar Card and PAN Card.',
                'Land Record Documents (Khatauni / Revenue Land Passbook).',
                'Crop Sowing Certificate or proof of cultivation.',
                'Passport-size photographs and bank account details.'
            ],
            'documents_hi' => [
                'आधार कार्ड और पैन कार्ड।',
                'जमीन के दस्तावेज (खतौनी की प्रमाणित प्रति / किसान बही)।',
                'फसल बुआई का विवरण या पटवारी की रिपोर्ट।',
                'पासपोर्ट साइज फोटो और बैंक खाता पासबुक।'
            ],
            
            'how_to_apply_en' => [
                'Step 1: Download the simple one-page KCC application form from the PM-KISAN portal (pmkisan.gov.in).',
                'Step 2: Fill in personal, land, and crop details.',
                'Step 3: Submit the form along with land documents at your bank branch or local CSC.',
                'Step 4: Bank verifies land records and sanctions KCC limit within 14 working days.',
                'Step 5: Collect your RuPay KCC ATM Card and activate it at the ATM.'
            ],
            'how_to_apply_hi' => [
                'चरण 1: पीएम-किसान पोर्टल से एक पेज का आसान KCC आवेदन फॉर्म डाउनलोड करें।',
                'चरण 2: अपनी जमीन, बोई जाने वाली फसल और बैंक विवरण भरें।',
                'चरण 3: अपने बैंक की शाखा में खतौनी की प्रति के साथ फॉर्म जमा करें।',
                'चरण 4: बैंक 14 दिनों के अंदर आपके आवेदन का सत्यापन करके KCC लिमिट मंजूर करता है।',
                'चरण 5: अपना RuPay किसान क्रेडिट कार्ड प्राप्त करें और एटीएम से जरूरत के समय पैसे निकालें।'
            ],
            
            'official_url' => 'https://pmkisan.gov.in/',
            'helpline' => '1800-180-1551',
            
            'audio_text_en' => 'Welcome to Kisan Credit Card scheme overview. Farmers, dairy owners, and fishermen can access agricultural loans up to three lakh rupees at a subsidized four percent interest rate. Apply at your nearest bank branch with your land records and Aadhaar card.',
            'audio_text_hi' => 'किसान क्रेडिट कार्ड योजना में आपका स्वागत है। किसान भाई, पशुपालक और मछली पालक मात्र 4 प्रतिशत के ब्याज पर 3 लाख रुपये तक का कृषि लोन पा सकते हैं। जमीन की खतौनी और आधार के साथ अपने बैंक में आवेदन करें।',
            
            'eligibility_checker' => [
                'q1_en' => 'Do you own cultivable agricultural land or do you raise dairy/poultry animals or fish?',
                'q1_hi' => 'क्या आपके पास कृषि भूमि है या क्या आप पशुपालन, डेयरी या मछली पालन करते हैं?',
                'q2_en' => 'Do you have an active bank account without past chronic default?',
                'q2_hi' => 'क्या आपके पास बैंक खाता है और कोई पुराना कर्ज बकाया डिफॉल्ट नहीं है?',
                'expected' => ['q1' => 'yes', 'q2' => 'yes'],
                'eligible_msg_en' => 'You are eligible for a Kisan Credit Card (KCC) at the 4% subsidized interest rate!',
                'eligible_msg_hi' => 'आप मात्र 4% ब्याज पर किसान क्रेडिट कार्ड (KCC) लोन पाने के लिए पूरी तरह पात्र हैं!',
                'ineligible_msg_en' => 'KCC is exclusively available to farmers, tenant cultivators, and animal husbandry/fishery workers.',
                'ineligible_msg_hi' => 'KCC योजना केवल वास्तविक किसानों और पशुपालकों/मत्स्य पालकों के लिए उपलब्ध है।'
            ]
        ]
    ];
}
