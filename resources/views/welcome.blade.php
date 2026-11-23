<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Laporan Keuangan menggabungkan transaksi, arus kas, dan neraca dalam satu ruang kerja yang mudah dibaca." />

        <title>Laporan Keuangan</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            /* Hallmark · v2.0.0 */
            /* Hallmark · pre-emit critique: P5 H5 E5 S4 R5 V4 · weakest: S, hero could be any finance tool; led with a live operating board and no stock hype */
            :root {
                --paper: #f4f0e8;
                --paper-strong: #ece4d7;
                --surface: #fffdf9;
                --surface-strong: #f7f3ee;
                --ink: #1b2430;
                --muted: #586779;
                --line: rgba(27, 36, 48, 0.12);
                --line-accent: rgba(13, 118, 103, 0.12);
                --outline: rgba(13, 118, 103, 0.35);
                --paper-glass: rgba(244, 240, 232, 0.84);
                --accent: #0d7667;
                --accent-strong: #0b5f54;
                --accent-soft: #dfeee9;
                --shadow: rgba(27, 36, 48, 0.12);
                --success: #186d4b;
                --track-soft: rgba(13, 118, 103, 0.09);
                --grid-line: rgba(27, 36, 48, 0.05);
                --button-on-accent: #f8faf9;
                --display: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
                --body: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
                --radius-lg: 28px;
                --radius-md: 20px;
                --radius-sm: 14px;
                --container: min(1180px, calc(100vw - 32px));
            }

            *, *::before, *::after {
                box-sizing: border-box;
            }

            html, body {
                margin: 0;
                padding: 0;
                overflow-x: clip;
                background: var(--paper);
                color: var(--ink);
                font-family: var(--body);
                line-height: 1.6;
            }

            body {
                min-height: 100vh;
            }

            a, button {
                font: inherit;
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            img {
                max-width: 100%;
                display: block;
            }

            :focus-visible {
                outline: 3px solid var(--outline);
                outline-offset: 3px;
            }

            .container {
                width: min(100%, 1180px);
                padding-inline: 16px;
                margin: 0 auto;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 48px;
                padding: 0.85rem 1.3rem;
                border-radius: 999px;
                border: 1px solid var(--line);
                transition: background-color 180ms ease, border-color 180ms ease, transform 180ms ease;
                font-weight: 600;
            }

            .button:hover,
            .button:active {
                transform: translateY(-1px);
            }

            .button:active {
                background: var(--surface-strong);
                border-color: var(--line);
            }

            .button-primary {
                background: var(--accent);
                border-color: var(--accent);
                color: var(--button-on-accent);
            }

            .button-primary:hover,
            .button-primary:active {
                background: var(--accent-strong);
            }

            .button-secondary {
                background: var(--surface);
                color: var(--ink);
            }

            .text-link {
                display: inline-flex;
                align-items: center;
                min-height: 48px;
                padding: 0.85rem 0.2rem;
                color: var(--ink);
                font-weight: 600;
                border-bottom: 1px solid var(--line);
            }

            .site-header {
                position: sticky;
                top: 0;
                z-index: 20;
                background: var(--paper);
                border-bottom: 1px solid var(--line);
            }

            .nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1.25rem;
                min-height: 78px;
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: 0.8rem;
                font-weight: 700;
                letter-spacing: -0.03em;
            }

            .brand-mark {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.2rem;
                height: 2.2rem;
                border-radius: 0.8rem;
                background: var(--accent-soft);
                color: var(--accent);
                border: 1px solid var(--line-accent);
            }

            .nav-links {
                display: inline-flex;
                align-items: center;
                gap: 1.4rem;
                color: var(--muted);
                font-size: 0.96rem;
            }

            .hero {
                padding: 4.5rem 0 2rem;
            }

            .hero-grid {
                display: grid;
                grid-template-columns: 1.1fr 0.9fr;
                align-items: center;
                gap: 2rem;
            }

            .hero-copy {
                max-width: 560px;
            }

            .lede {
                margin: 0 0 1rem;
                color: var(--muted);
                font-size: 0.96rem;
            }

            h1 {
                margin: 0;
                font-family: var(--display);
                font-size: clamp(2.8rem, 5vw, 5rem);
                line-height: 0.95;
                letter-spacing: -0.07em;
                font-weight: 800;
                text-wrap: balance;
            }

            .hero-copy p {
                margin: 1.4rem 0 0;
                max-width: 52ch;
                color: var(--muted);
                font-size: 1.08rem;
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.9rem;
                margin-top: 1.8rem;
            }

            .hero-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                margin-top: 1.5rem;
                padding: 0;
                list-style: none;
                color: var(--muted);
                font-size: 0.92rem;
            }

            .hero-meta li {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .hero-meta li::before {
                content: "";
                width: 0.52rem;
                height: 0.52rem;
                border-radius: 999px;
                background: var(--success);
            }

            .visual-panel {
                position: relative;
                padding: 1rem;
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: var(--radius-lg);
            }

            .panel-strip {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.85rem 1rem 1rem;
                border-bottom: 1px solid var(--line);
            }

            .panel-dot {
                width: 0.72rem;
                height: 0.72rem;
                border-radius: 999px;
                background: var(--paper-strong);
            }

            .dashboard {
                display: grid;
                grid-template-columns: minmax(0, 1.4fr) minmax(0, 0.9fr);
                gap: 1rem;
                padding: 1rem;
            }

            .card {
                background: var(--surface-strong);
                border: 1px solid var(--line);
                border-radius: var(--radius-md);
                padding: 1rem;
            }

            .stack {
                display: grid;
                gap: 1rem;
            }

            .card-label {
                display: block;
                margin-bottom: 0.7rem;
                color: var(--muted);
                font-size: 0.8rem;
            }

            .card-title {
                margin: 0;
                font-size: 1.2rem;
                font-weight: 700;
                letter-spacing: -0.04em;
            }

            .bars {
                display: grid;
                gap: 0.8rem;
                margin-top: 1rem;
            }

            .bar-row {
                display: grid;
                gap: 0.35rem;
            }

            .bar-track {
                position: relative;
                width: 100%;
                height: 0.7rem;
                border-radius: 999px;
                background: var(--track-soft);
                overflow: hidden;
            }

            .bar-fill {
                display: block;
                height: 100%;
                border-radius: inherit;
                background: var(--accent);
            }

            .small-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }

            .mini-card {
                padding: 0.9rem;
                border-radius: var(--radius-sm);
                background: var(--surface);
                border: 1px solid var(--line);
            }

            .mini-card strong {
                display: block;
                margin-top: 0.8rem;
                font-size: 1.2rem;
                letter-spacing: -0.05em;
            }

            .team {
                margin-top: 0.7rem;
                display: grid;
                gap: 0.5rem;
            }

            .team-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                color: var(--muted);
                font-size: 0.9rem;
            }

            .pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 2rem;
                padding: 0.35rem 0.7rem;
                border-radius: 999px;
                background: var(--accent-soft);
                color: var(--accent-strong);
                font-size: 0.78rem;
                font-weight: 600;
            }

            .content {
                padding: 2rem 0 4rem;
            }

            .section {
                padding: 2rem 0;
            }

            .section-head {
                display: flex;
                align-items: end;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1.4rem;
            }

            .section-head h2 {
                margin: 0;
                font-family: var(--display);
                font-size: clamp(2rem, 3vw, 3rem);
                line-height: 1.03;
                letter-spacing: -0.06em;
                text-wrap: balance;
            }

            .section-head p {
                margin: 0;
                max-width: 42ch;
                color: var(--muted);
            }

            .feature-grid {
                display: grid;
                grid-template-columns: 1.15fr 1fr 0.85fr;
                gap: 1rem;
            }

            .feature-card {
                padding: 1.25rem;
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: var(--radius-md);
                min-height: 220px;
            }

            .feature-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 3rem;
                height: 3rem;
                border-radius: 1rem;
                background: var(--accent-soft);
                color: var(--accent);
                margin-bottom: 1rem;
                font-weight: 700;
            }

            .feature-card h3 {
                margin: 0 0 0.6rem;
                font-size: 1.35rem;
                letter-spacing: -0.04em;
            }

            .feature-card p {
                margin: 0;
                color: var(--muted);
            }

            .workbench {
                display: grid;
                grid-template-columns: 1.05fr 0.95fr;
                gap: 1rem;
                align-items: stretch;
            }

            .story-panel,
            .stack-panel {
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: var(--radius-lg);
                padding: 1.2rem;
            }

            .story-card {
                background: var(--surface-strong);
                border: 1px solid var(--line);
                border-radius: var(--radius-md);
                padding: 1rem;
                min-height: 100%;
            }

            .list-block {
                display: grid;
                gap: 0.8rem;
                margin-top: 1rem;
            }

            .list-row {
                display: grid;
                grid-template-columns: 0.9fr 1.1fr;
                gap: 0.7rem;
                align-items: center;
                padding: 0.85rem 0.9rem;
                border: 1px solid var(--line);
                border-radius: var(--radius-sm);
                background: var(--surface);
            }

            .list-row strong {
                display: block;
                font-size: 0.95rem;
            }

            .list-row span {
                color: var(--muted);
                font-size: 0.78rem;
            }

            .mini-graph {
                margin-top: 1rem;
                border-radius: var(--radius-md);
                padding: 1rem;
                background: var(--surface-strong);
                border: 1px solid var(--line);
            }

            .graph-line {
                position: relative;
                height: 130px;
                border-radius: var(--radius-sm);
                background:
                    linear-gradient(to top, var(--accent-soft), var(--accent-soft)),
                    linear-gradient(to right, var(--grid-line) 1px, transparent 1px);
                background-size: auto, 24px 100%;
                overflow: hidden;
            }

            .graph-line::after {
                content: "";
                position: absolute;
                inset: 12% 8% 15% 8%;
                border-radius: 999px;
                border: 2px solid var(--accent);
                border-left-color: transparent;
                border-bottom-color: transparent;
                transform: rotate(18deg);
                opacity: 0.7;
            }

            .pricing-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1rem;
            }

            .price-card {
                position: relative;
                padding: 1.4rem;
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: var(--radius-lg);
            }

            .price-card.featured {
                background: var(--accent-soft);
                border-color: var(--line-accent);
            }

            .price-card h3 {
                margin: 0 0 0.5rem;
                font-size: 1.35rem;
            }

            .price {
                display: flex;
                align-items: end;
                gap: 0.35rem;
                margin: 1rem 0;
                font-size: 2.2rem;
                font-weight: 800;
                letter-spacing: -0.06em;
                font-variant-numeric: tabular-nums;
            }

            .price small {
                font-size: 0.9rem;
                color: var(--muted);
            }

            .price-card ul {
                margin: 0;
                padding-left: 1.1rem;
                color: var(--muted);
                display: grid;
                gap: 0.65rem;
            }

            .price-card .button {
                width: 100%;
                margin-top: 1.3rem;
            }

            .site-footer {
                padding: 2rem 0 4rem;
            }

            .footer-shell {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding-top: 1.4rem;
                border-top: 1px solid var(--line);
                color: var(--muted);
            }

            .footer-nav {
                display: flex;
                align-items: center;
                gap: 1.2rem;
                flex-wrap: wrap;
            }

            @media (max-width: 860px) {
                .nav {
                    flex-wrap: wrap;
                    justify-content: center;
                    padding: 1rem 0;
                }

                .nav-links {
                    order: 3;
                    width: 100%;
                    justify-content: center;
                    flex-wrap: wrap;
                }

                .hero-grid,
                .feature-grid,
                .workbench,
                .pricing-grid {
                    grid-template-columns: 1fr;
                }

                .section-head {
                    flex-direction: column;
                    align-items: flex-start;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    animation: none !important;
                    transition: none !important;
                    scroll-behavior: auto !important;
                }
            }
        </style>
    </head>
    <body>
        <header class="site-header">
            <div class="container nav">
                <a href="#top" class="brand" aria-label="Laporan Keuangan home">
                    <span class="brand-mark">K</span>
                    <span>Laporan Keuangan</span>
                </a>

                <nav class="nav-links" aria-label="Main navigation">
                    <a href="#fitur">Fitur</a>
                    <a href="#cara-kerja">Cara kerja</a>
                    <a href="#paket">Paket</a>
                    <a href="#kontak">Kontak</a>
                </nav>

                <a href="#mulai" class="button button-primary">Coba gratis</a>
            </div>
        </header>

        <main id="top">
            <section class="hero">
                <div class="container hero-grid">
                    <div class="hero-copy">
                        <p class="lede">laporan real-time</p>
                        <h1>Keputusan bisnis lebih jelas tanpa menumpuk spreadsheet.</h1>
                        <p>Semua transaksi, arus kas, dan neraca dalam satu ruang kerja.</p>

                        <div class="actions">
                            <a href="#mulai" class="button button-primary">Mulai sekarang</a>
                            <a href="#fitur" class="text-link">Lihat fitur</a>
                        </div>

                        <ul class="hero-meta" aria-label="Highlights">
                            <li>arsip data teratur</li>
                            <li>jurnal otomatis</li>
                            <li>laporan siap dibagikan</li>
                        </ul>
                    </div>

                    <div class="visual-panel" aria-label="Financial dashboard preview">
                        <div class="panel-strip" aria-hidden="true">
                            <span class="panel-dot"></span>
                            <span class="panel-dot"></span>
                            <span class="panel-dot"></span>
                        </div>

                        <div class="dashboard">
                            <div class="stack">
                                <div class="card">
                                    <h2 class="card-title">Aliran usaha</h2>
                                    <div class="bars">
                                        <div class="bar-row">
                                            <span class="card-label">Penjualan</span>
                                            <div class="bar-track"><span class="bar-fill" style="width: 80%;"></span></div>
                                        </div>
                                        <div class="bar-row">
                                            <span class="card-label">Pembayaran</span>
                                            <div class="bar-track"><span class="bar-fill" style="width: 66%;"></span></div>
                                        </div>
                                        <div class="bar-row">
                                            <span class="card-label">Pelunasan</span>
                                            <div class="bar-track"><span class="bar-fill" style="width: 58%;"></span></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="small-grid">
                                    <div class="mini-card">
                                        <span class="card-label">Kas masuk</span>
                                        <strong>stabil</strong>
                                    </div>
                                    <div class="mini-card">
                                        <span class="card-label">Utang</span>
                                        <strong>terkontrol</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="stack">
                                <div class="card">
                                    <h2 class="card-title">Bulan ini</h2>
                                    <div class="team">
                                        <div class="team-row">
                                            <span>Operasional</span>
                                            <span class="pill">rutin</span>
                                        </div>
                                        <div class="team-row">
                                            <span>Investasi</span>
                                            <span class="pill">terjadwal</span>
                                        </div>
                                        <div class="team-row">
                                            <span>Persediaan</span>
                                            <span class="pill">siap</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <h2 class="card-title">Cash flow</h2>
                                    <div class="mini-graph" aria-hidden="true">
                                        <div class="graph-line"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container section" id="fitur">
                    <div class="section-head">
                        <h2>Semua keputusan keuangan dalam satu tampilan.</h2>
                        <p>Mulai dari jurnal harian sampai laporan akhir bulan, semua tugas berhubungan dengan data yang sama dan siap dibaca oleh siapa pun.</p>
                    </div>

                    <div class="feature-grid">
                        <article class="feature-card">
                            <div class="feature-icon">A</div>
                            <h3>Alur otomatis</h3>
                            <p>Masukkan transaksi sekali, lalu bagikan hasilnya ke semua divisi tanpa mengulang kerja yang sama.</p>
                        </article>

                        <article class="feature-card">
                            <div class="feature-icon">R</div>
                            <h3>Ringkasan cepat</h3>
                            <p>Siapkan laporan yang mudah dipahami dalam hitungan menit, tepat saat kebutuhan keputusan muncul.</p>
                        </article>

                        <article class="feature-card">
                            <div class="feature-icon">S</div>
                            <h3>Skema rapi</h3>
                            <p>Semua kategori, akun, dan pengeluaran tertata rapi agar tim lebih fokus pada strategi, bukan pada data yang berserakan.</p>
                        </article>
                    </div>
                </div>

                <div class="container section" id="cara-kerja">
                    <div class="section-head">
                        <h2>Kerja dari data yang sama, bukan dari salinan yang berubah.</h2>
                        <p>Setiap perubahan terbaca di satu tempat, sehingga pemimpin, admin, dan tim operasional mengerjakan satu versi yang sama.</p>
                    </div>

                    <div class="workbench">
                        <div class="story-panel">
                            <div class="story-card">
                                <span class="card-label">Jadwal kerja</span>
                                <div class="list-block">
                                    <div class="list-row">
                                        <div>
                                            <strong>Catat transaksi</strong>
                                            <span>data masuk sekali</span>
                                        </div>
                                        <span class="pill">dianalisis</span>
                                    </div>
                                    <div class="list-row">
                                        <div>
                                            <strong>Kelola kategori</strong>
                                            <span>setiap akun terhubung</span>
                                        </div>
                                        <span class="pill">teratur</span>
                                    </div>
                                    <div class="list-row">
                                        <div>
                                            <strong>Bagikan laporan</strong>
                                            <span>siap dipakai rapat</span>
                                        </div>
                                        <span class="pill">siap</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="stack-panel">
                            <div class="story-card">
                                <h3 class="card-title">Cairkan keputusan dengan data yang jelas.</h3>
                                <div class="mini-graph" aria-hidden="true">
                                    <div class="graph-line" style="height: 120px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container section" id="paket">
                    <div class="section-head">
                        <h2>Paket yang cocok untuk operasional yang terus tumbuh.</h2>
                        <p>Mulai dari kebutuhan harian sampai kebutuhan tim dengan akses yang rapi dan laporan yang siap dibagikan.</p>
                    </div>

                    <div class="pricing-grid">
                        <article class="price-card">
                            <h3>Dasar</h3>
                            <div class="price">Mulai <small>sesuai kebutuhan</small></div>
                            <ul>
                                <li>Jurnal harian</li>
                                <li>Ringkasan akun</li>
                                <li>Unduh laporan</li>
                            </ul>
                            <a href="#mulai" class="button button-secondary">Pilih paket</a>
                        </article>

                        <article class="price-card featured">
                            <span class="pill">Paling populer</span>
                            <h3>Tim</h3>
                            <div class="price">Bergabung <small>tanpa ribet</small></div>
                            <ul>
                                <li>Semua fitur dasar</li>
                                <li>Akses koordinasi tim</li>
                                <li>laporan siap rapat</li>
                            </ul>
                            <a href="#mulai" class="button button-primary">Mulai sekarang</a>
                        </article>

                        <article class="price-card">
                            <h3>Scale</h3>
                            <div class="price">Sesuaikan <small>untuk usaha besar</small></div>
                            <ul>
                                <li>Integrasi lebih banyak</li>
                                <li>Kelola beberapa divisi</li>
                                <li>Proses adaptif</li>
                            </ul>
                            <a href="#kontak" class="button button-secondary">Hubungi kami</a>
                        </article>
                    </div>
                </div>
            </section>
        </main>

        <footer class="site-footer" id="kontak">
            <div class="container footer-shell">
                <div class="brand">
                    <span class="brand-mark">K</span>
                    <span>Laporan Keuangan</span>
                </div>
                <nav class="footer-nav" aria-label="Footer navigation">
                    <a href="#fitur">Fitur</a>
                    <a href="#cara-kerja">Cara kerja</a>
                    <a href="#paket">Paket</a>
                    <a href="#top">Kembali ke atas</a>
                </nav>
            </div>
        </footer>
    </body>
</html>
