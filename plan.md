# GSP-Card Citizen Services & Facilitation Portal - Implementation & Verification Report
**Target Working Directory:** `/Applications/XAMPP/xamppfiles/htdocs/gp`  
**Base URL:** `[http://localhost/gp](http://localhost/gp/index.php)`  
**Database:** `citizen_portal_db` (MariaDB/MySQL 10.4.x / 8.x)  
**Status:** Completed & Fully Operational with 20 Flagship Schemes from Vajiram & Ravi Official 2026 Directory & VisionIAS PT 365

---

## 1. Summary of Completed Actions

### A. Schemes Repository Expanded to 20 Flagship Schemes ([gp/data/schemes.php](file:///Applications/XAMPP/xamppfiles/htdocs/gp/data/schemes.php))
- **Directly sourced from Vajiram & Ravi's 2026 Government Schemes Compendium:**
  - Categorized strictly under the two constitutional/financial models:
    1. **Central Sector Schemes (100% Central Government Funded & Implemented):**
       - `pm-kisan` (PM-KISAN Samman Nidhi - ₹6,000/yr direct DBT)
       - `pm-surya-ghar` (PM Surya Ghar: Muft Bijli Yojana - ₹75,021 Cr, up to ₹78,000 solar subsidy & 300 units free power)
       - `pm-vishwakarma` (PM Vishwakarma Kaushal Samman - ₹13,000 Cr, ₹15,000 toolkit voucher & 5% collateral-free loans for 18 trades)
       - `pm-mudra-yojana` (Pradhan Mantri MUDRA Yojana - Collateral-free business credit up to ₹20 Lakh)
       - `pm-kaushal-vikas` (PMKVY 4.0 - Free NSQF-aligned vocational certification & ₹8,000 cash reward)
       - `atal-pension-yojana` (APY - Guaranteed lifelong monthly pension of ₹1,000 to ₹5,000)
       - `pm-svanidhi` (PM SVANidhi - Working capital micro-loans of ₹10,000, ₹20,000, and ₹50,000 with 7% subsidy for street vendors)
       - `pm-shram-yogi` (PM-SYM - Pension for unorganized laborers with 50% matching Central contribution)
       - `kisan-credit-card` (KCC - Subsidized farm & dairy credit up to ₹3 Lakh at 4% interest)
    2. **Centrally Sponsored Schemes (CSS - Shared Centre + State Funding):**
       - `ayushman-bharat` (AB-PMJAY - ₹5 Lakh free cashless treatment per family per year, 60:40)
       - `pmay-gramin` (PMAY - ₹1.20L / ₹1.30L pucca house assistance + MGNREGA wages + toilet incentive)
       - `mgnrega` (MGNREGA - Statutory guarantee of 100 days of unskilled wage employment)
       - `nsap-pension` (National Social Assistance Programme - Old age, widow, and disability monthly pensions)
       - `day-nrlm-aajivika` (DAY-NRLM Aajivika - Women Self-Help Groups, revolving funds, and 7% bank credit)
       - `pm-janjatiya-gram` (PM Janjatiya Unnat Gram Abhiyan - ₹79,156 Cr saturating 63,000 tribal villages)
       - `mission-indradhanush` (Intensified Mission Indradhanush - 100% free immunization against 12 preventable diseases)
       - `pmgsy-roads` (PM Gram Sadak Yojana - All-weather blacktopped roads to unconnected rural habitations)
       - `pm-matru-vandana` (PMMVY - ₹5,000 to ₹6,000 direct maternity cash incentive)
       - `pm-fasal-bima` (PMFBY - Comprehensive pre/post-harvest crop insurance at 1.5%–2% premium)
       - `jal-jeevan-mission` (Har Ghar Jal - 55 litres per capita per day potable tap water to all rural homes)

---

### B. All Schemes Page ([gp/all-scheme.php](file:///Applications/XAMPP/xamppfiles/htdocs/gp/all-scheme.php))
- **Strict Light Theme Design:**
  - Pure white `#ffffff` cards, soft slate `#f8fafc`, clean `#e2e8f0` borders. Zero dark/yellow containers.
- **Classification Explainer Banner:**
  - Highlights Central Sector (100% Union Government) vs Centrally Sponsored (60:40 Shared).
- **Multi-Dimensional Instant Filtering:**
  - Funding Type: `All Schemes (20)` | `Central Sector (9)` | `Centrally Sponsored (11)`.
  - State Scope: All-India and 11 individual states (`Uttar Pradesh`, `Bihar`, `Rajasthan`, `Madhya Pradesh`, `Maharashtra`, `West Bengal`, `Haryana`, `Punjab`, `Gujarat`, `Odisha`, `Jharkhand`).
  - Sector/Category: Agriculture, Renewable Energy/Solar, Healthcare, Housing/Infrastructure, Employment/Skills, Social Security/Pensions, Financial Inclusion, Water/Sanitation, Women/Child.
  - Live Search Filter: Real-time search across titles (English & Hindi), ministries, and taglines.

---

### C. Scheme Detail Page ([gp/scheme_details.php](file:///Applications/XAMPP/xamppfiles/htdocs/gp/scheme_details.php))
- **Bilingual Hindi/English Switcher:**
  - Seamless DOM switch between English and हिन्दी for all 20 schemes.
- **Accessible Audio Narration (Voice Reader):**
  - Web Speech API integration in `en-IN` and `hi-IN` with Play, Pause, Stop, and wave animation.
- **Direct Eligibility Checker Modal:**
  - 2 scheme-specific Yes/No questions.
  - Instant plain-language verdict (Green eligible card or Orange guidance card) for all 20 schemes.

---

## 2. Automated Verification Matrix

| Scheme Slug | Name | Funding Type | HTTP Status |
|---|---|---|---|
| `pm-kisan` | PM-KISAN Samman Nidhi | Central Sector | ✅ 200 OK |
| `pm-surya-ghar` | PM Surya Ghar: Muft Bijli Yojana (2024) | Central Sector | ✅ 200 OK |
| `pm-vishwakarma` | PM Vishwakarma Kaushal Samman (2023) | Central Sector | ✅ 200 OK |
| `ayushman-bharat` | Ayushman Bharat PM-JAY | Centrally Sponsored | ✅ 200 OK |
| `pmay-gramin` | Pradhan Mantri Awas Yojana (PMAY) | Centrally Sponsored | ✅ 200 OK |
| `pm-mudra-yojana` | Pradhan Mantri MUDRA Yojana | Central Sector | ✅ 200 OK |
| `mgnrega` | MGNREGA Employment Guarantee | Centrally Sponsored | ✅ 200 OK |
| `pm-kaushal-vikas` | PM Kaushal Vikas Yojana (PMKVY 4.0) | Central Sector | ✅ 200 OK |
| `nsap-pension` | National Social Assistance Programme | Centrally Sponsored | ✅ 200 OK |
| `day-nrlm-aajivika` | DAY-NRLM (Lakhpati Didi / Aajivika) | Centrally Sponsored | ✅ 200 OK |
| `pm-janjatiya-gram` | PM Janjatiya Unnat Gram Abhiyan (2024) | Centrally Sponsored | ✅ 200 OK |
| `mission-indradhanush` | Intensified Mission Indradhanush (IMI) | Centrally Sponsored | ✅ 200 OK |
| `pmgsy-roads` | PM Gram Sadak Yojana (PMGSY) | Centrally Sponsored | ✅ 200 OK |
| `atal-pension-yojana` | Atal Pension Yojana (APY) | Central Sector | ✅ 200 OK |
| `pm-svanidhi` | PM SVANidhi (Street Vendors) | Central Sector | ✅ 200 OK |
| `pm-matru-vandana` | PM Matru Vandana Yojana (PMMVY) | Centrally Sponsored | ✅ 200 OK |
| `pm-fasal-bima` | PM Fasal Bima Yojana (PMFBY) | Centrally Sponsored | ✅ 200 OK |
| `jal-jeevan-mission` | Jal Jeevan Mission (Har Ghar Jal) | Centrally Sponsored | ✅ 200 OK |
| `pm-shram-yogi` | PM Shram Yogi Maan-dhan (PM-SYM) | Central Sector | ✅ 200 OK |
| `kisan-credit-card` | Kisan Credit Card (KCC) | Central Sector | ✅ 200 OK |
