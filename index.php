<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi nama halaman aktif untuk fungsionalitas class active di navbar
$current_page = basename($_SERVER['PHP_SELF']);

// Memanggil file header global eksternal Anda (yang memuat navbar tunggal)
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda FTI HUB UAP</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico">
    <style>
        /* --- RESET & DASAR --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f0f8; color: #333; overflow-x: hidden; }

        /* --- ANIMASI --- */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* --- HERO SECTION --- */
        .hero {
            min-height: calc(100vh - 85px);
            background: 
                linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), 
                url('logo.png');
            background-size: 100px;
            background-repeat: repeat;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 40px 20px;
        }
        .hero h1 { font-size: 2.5rem; margin-bottom: 15px; animation: fadeIn 0.8s ease-out; }
        .hero p { font-size: 1.3rem; max-width: 600px; margin-bottom: 40px; opacity: 0.9; animation: fadeIn 1s ease-out; }

        /* --- WIDGET CONTAINER (RESPONSIF) --- */
        .widgets-wrapper {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            width: 100%;
            max-width: 1000px;
            animation: fadeIn 1.2s ease-out;
        }

        .widget-box {
            background: white;
            border-radius: 12px;
            padding: 18px 15px;
            border-top: 5px solid #6a0d6a;
            width: 230px;
            min-height: 140px;
            color: #333;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* KHUSUS JAM */
        #digital-clock { font-size: 1.7rem; font-weight: bold; color: #6a0d6a; text-align: center; margin-bottom: 2px; }

        /* KHUSUS KALENDER */
        .calendar-table { width: 100%; font-size: 0.75rem; text-align: center; border-collapse: collapse; }
        .calendar-table th { color: #888; padding-bottom: 5px; }
        .calendar-table td { padding: 4px 0; color: #444; font-weight: 600; }
        .today { background: #6a0d6a; color: white !important; border-radius: 6px; font-weight: bold; display: inline-block; width: 22px; height: 22px; line-height: 22px; }

        /* --- MEDIA QUERIES (RESOLUSI HP) --- */
        @media (max-width: 768px) {
            .hero { min-height: auto; padding: 60px 20px; }
            .hero h1 { font-size: 1.8rem; }
            .hero p { font-size: 1.1rem; margin-bottom: 30px; }
            .widgets-wrapper { flex-direction: column; align-items: center; gap: 15px; }
            .widget-box { width: 100%; max-width: 280px; }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<header class="hero">
    <h1>Universitas Aisyah Pringsewu</h1>
    <p>Portal Informasi Mahasiswa Fakultas Teknologi Informasi dalam genggaman Anda.</p>

    <div class="widgets-wrapper">
        <!-- 1. WIDGET CUACA (METODE INJEKSI INSTAN) -->
        <div class="widget-box" style="padding: 0; overflow: hidden;" id="weather-container">
            <a class="weatherwidget-io" href="https://forecast7.com/en/n5d33104d99/pringsewu-regency/" data-label_1="PRINGSEWU" data-label_2="WEATHER" data-theme="original" data-days="3">PRINGSEWU WEATHER</a>
        </div>

        <!-- 2. WIDGET KALENDER -->
        <div class="widget-box">
            <div id="month-name" style="font-weight: bold; color: #6a0d6a; margin-bottom: 8px; font-size: 0.9rem; text-align: center;"></div>
            <table class="calendar-table">
                <thead><tr><th>S</th><th>S</th><th>R</th><th>K</th><th>J</th><th>S</th><th>M</th></tr></thead>
                <tbody id="calendar-body"></tbody>
            </table>
        </div>

        <!-- 3. WIDGET JAM -->
        <div class="widget-box">
            <div id="digital-clock">00:00:00</div>
            <div style="text-align: center; font-size: 0.7rem; font-weight: 600; color: #888; letter-spacing: 1px;">WIB</div>
        </div>
    </div>
</header>

<script>
    // 1. INJEKSI SKRIP CUACA SECARA LANGSUNG (TANPA MENUNGGU LOAD)
    !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','weatherwidget-io-js');

    // 2. SCRIPT JAM DIGITAL
    function updateClock() {
        const clockEl = document.getElementById('digital-clock');
        if (clockEl) {
            const now = new Date();
            clockEl.innerText = 
                String(now.getHours()).padStart(2, '0') + ":" + 
                String(now.getMinutes()).padStart(2, '0') + ":" + 
                String(now.getSeconds()).padStart(2, '0');
        }
    }
    setInterval(updateClock, 1000); 
    updateClock();

    // 3. SCRIPT KALENDER OTOMATIS
    function generateCalendar() {
        const now = new Date();
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        
        const monthNameEl = document.getElementById("month-name");
        const tbl = document.getElementById("calendar-body");
        
        if (monthNameEl && tbl) {
            monthNameEl.innerText = `${monthNames[now.getMonth()]} ${now.getFullYear()}`;
            
            let firstDay = new Date(now.getFullYear(), now.getMonth(), 1).getDay();
            firstDay = (firstDay === 0) ? 6 : firstDay - 1;
            
            const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
            tbl.innerHTML = "";
            
            let date = 1;
            for (let i = 0; i < 6; i++) {
                let row = document.createElement("tr");
                let hasCells = false;
                
                for (let j = 0; j < 7; j++) {
                    let cell = document.createElement("td");
                    if (i === 0 && j < firstDay) {
                        cell.innerText = "";
                    } else if (date > daysInMonth) {
                        cell.innerText = "";
                    } else {
                        if (date === now.getDate()) {
                            cell.innerHTML = `<span class="today">${date}</span>`;
                        } else {
                            cell.innerText = date;
                        }
                        date++;
                        hasCells = true;
                    }
                    row.appendChild(cell);
                }
                tbl.appendChild(row);
                if (date > daysInMonth && !hasCells) break;
            }
        }
    }

    // Eksekusi Kalender Saat Struktur HTML Siap
    generateCalendar();

    // Fungsi Paksa Render Ulang Cuaca Jika Mengalami Lag Koneksi
    window.onload = function() {
        if (typeof __weatherwidget_init === 'function') {
            __weatherwidget_init();
        }
    };
</script>

<?php
// Memanggil file footer global eksternal Anda
include 'footer.php'; 
?>
</body>
</html>