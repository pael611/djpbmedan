<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#f5f7f6">

        <title>Ringkasan Dashboard | DJPb Sumatera Utara</title>

        @fonts
        @vite(['resources/css/app.css'])

        <style>
            :root {
                color-scheme: light;
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                font-synthesis: none;
                text-rendering: optimizeLegibility;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                --ink: #17241f;
                --muted: #78837d;
                --line: #e8ece9;
                --green: #176b52;
                --green-dark: #124d3d;
                --canvas: #f5f7f6;
            }

            * {
                box-sizing: border-box;
            }

            body {
                min-width: 320px;
                min-height: 100vh;
                margin: 0;
                background: var(--canvas);
                color: var(--ink);
            }

            button,
            a {
                font: inherit;
            }

            .app-shell {
                display: grid;
                min-height: 100vh;
                grid-template-columns: 252px minmax(0, 1fr);
            }

            .sidebar {
                position: sticky;
                top: 0;
                display: flex;
                height: 100vh;
                flex-direction: column;
                border-right: 1px solid var(--line);
                background: #fff;
                padding: 28px 18px 20px;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 0 8px 34px;
            }

            .brand-mark {
                display: grid;
                width: 42px;
                height: 42px;
                flex: 0 0 auto;
                place-items: center;
                border-radius: 14px;
                background: var(--green-dark);
                color: #fff;
            }

            .brand-name {
                margin: 0;
                font-size: 15px;
                font-weight: 750;
                letter-spacing: -.03em;
            }

            .brand-caption {
                margin: 3px 0 0;
                color: var(--muted);
                font-size: 11px;
            }

            .nav-label {
                margin: 0 10px 10px;
                color: #9aa39e;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .12em;
                text-transform: uppercase;
            }

            .nav-list {
                display: grid;
                gap: 5px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .nav-link {
                display: flex;
                min-height: 44px;
                align-items: center;
                gap: 12px;
                border-radius: 11px;
                color: #68746d;
                font-size: 13px;
                font-weight: 550;
                padding: 0 12px;
                text-decoration: none;
                transition: background .18s ease, color .18s ease;
            }

            .nav-link:hover {
                background: #f4f7f5;
                color: var(--green-dark);
            }

            .nav-link[aria-current="page"] {
                background: #eaf4ef;
                color: var(--green-dark);
                font-weight: 700;
            }

            .nav-link svg {
                width: 18px;
                height: 18px;
                flex: 0 0 auto;
            }

            .sidebar-spacer {
                flex: 1;
            }

            .source-note {
                border: 1px solid #e8eee9;
                border-radius: 14px;
                background: #f8faf8;
                padding: 13px;
            }

            .source-note strong {
                display: block;
                margin-bottom: 5px;
                font-size: 12px;
            }

            .source-note p {
                margin: 0;
                color: var(--muted);
                font-size: 11px;
                line-height: 1.55;
            }

            .sidebar-footer {
                display: flex;
                align-items: center;
                gap: 10px;
                border-top: 1px solid var(--line);
                margin-top: 18px;
                padding: 18px 4px 0;
            }

            .avatar {
                display: grid;
                width: 36px;
                height: 36px;
                flex: 0 0 auto;
                place-items: center;
                border-radius: 50%;
                background: #eaf1ed;
                color: var(--green-dark);
                font-size: 12px;
                font-weight: 750;
            }

            .profile-name {
                margin: 0;
                font-size: 12px;
                font-weight: 700;
            }

            .profile-role {
                margin: 3px 0 0;
                color: var(--muted);
                font-size: 10px;
            }

            .main {
                min-width: 0;
                padding: 32px clamp(22px, 4vw, 58px) 42px;
            }

            .topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                margin-bottom: 34px;
            }

            .breadcrumb {
                display: flex;
                align-items: center;
                gap: 8px;
                color: var(--muted);
                font-size: 12px;
            }

            .breadcrumb strong {
                color: var(--ink);
                font-weight: 650;
            }

            .topbar-meta {
                display: flex;
                align-items: center;
                gap: 9px;
                color: #68746d;
                font-size: 12px;
            }

            .calendar-icon {
                width: 16px;
                height: 16px;
            }

            .page-heading {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 20px;
                margin-bottom: 24px;
            }

            .eyebrow {
                margin: 0 0 9px;
                color: var(--green);
                font-size: 10px;
                font-weight: 750;
                letter-spacing: .13em;
                text-transform: uppercase;
            }

            h1 {
                margin: 0;
                font-size: clamp(25px, 3vw, 33px);
                font-weight: 720;
                letter-spacing: -.045em;
                line-height: 1.15;
            }

            .page-description {
                margin: 9px 0 0;
                color: var(--muted);
                font-size: 13px;
                line-height: 1.6;
            }

            .preview-badge {
                display: inline-flex;
                flex: 0 0 auto;
                align-items: center;
                gap: 8px;
                border: 1px solid #f1dfb8;
                border-radius: 999px;
                background: #fff9eb;
                color: #86601b;
                font-size: 10px;
                font-weight: 750;
                letter-spacing: .06em;
                padding: 9px 12px;
                text-transform: uppercase;
            }

            .preview-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #d29a32;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 15px;
                margin-bottom: 18px;
            }

            .card {
                border: 1px solid var(--line);
                border-radius: 16px;
                background: #fff;
                box-shadow: 0 2px 8px rgb(23 36 31 / 2%);
            }

            .stat-card {
                min-width: 0;
                padding: 18px 18px 16px;
            }

            .stat-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
            }

            .stat-label {
                color: #68746d;
                font-size: 11px;
                font-weight: 600;
            }

            .stat-icon {
                display: grid;
                width: 34px;
                height: 34px;
                flex: 0 0 auto;
                place-items: center;
                border-radius: 11px;
                background: #edf5f0;
                color: var(--green);
            }

            .stat-icon.gold {
                background: #fbf3e7;
                color: #a97924;
            }

            .stat-icon.blue {
                background: #edf3fa;
                color: #5279a5;
            }

            .stat-icon.violet {
                background: #f2effa;
                color: #806bb0;
            }

            .stat-icon svg {
                width: 17px;
                height: 17px;
            }

            .stat-value {
                overflow: hidden;
                margin: 15px 0 9px;
                font-size: clamp(19px, 2vw, 25px);
                font-weight: 730;
                letter-spacing: -.045em;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .stat-foot {
                display: flex;
                align-items: center;
                gap: 6px;
                color: #758079;
                font-size: 10px;
            }

            .stat-change {
                color: #24835e;
                font-weight: 700;
            }

            .dashboard-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.7fr) minmax(280px, .9fr);
                gap: 18px;
                margin-bottom: 18px;
            }

            .panel {
                min-width: 0;
                padding: 21px 22px;
            }

            .panel-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 14px;
                margin-bottom: 20px;
            }

            .panel-title {
                margin: 0;
                font-size: 14px;
                font-weight: 720;
                letter-spacing: -.02em;
            }

            .panel-subtitle {
                margin: 5px 0 0;
                color: var(--muted);
                font-size: 11px;
                line-height: 1.5;
            }

            .chart-legend {
                display: flex;
                align-items: center;
                gap: 14px;
                color: #69756e;
                font-size: 10px;
            }

            .legend-item {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                white-space: nowrap;
            }

            .legend-swatch {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #24785d;
            }

            .line-chart {
                display: block;
                width: 100%;
                height: auto;
                overflow: visible;
            }

            .line-chart text {
                fill: #96a19a;
                font-family: inherit;
                font-size: 10px;
            }

            .chart-gridline {
                stroke: #edf0ee;
                stroke-dasharray: 3 5;
                stroke-width: 1;
            }

            .chart-line {
                fill: none;
                stroke: #24785d;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-width: 3;
            }

            .chart-area {
                fill: url(#areaFill);
            }

            .donut-wrap {
                display: grid;
                place-items: center;
                padding: 0 0 12px;
            }

            .donut-chart {
                width: 174px;
                height: 174px;
            }

            .donut-chart text {
                fill: var(--ink);
                font-family: inherit;
                text-anchor: middle;
            }

            .donut-total {
                font-size: 20px;
                font-weight: 750;
            }

            .donut-caption {
                fill: #849088 !important;
                font-size: 9px;
            }

            .category-list {
                display: grid;
                gap: 12px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .category-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                color: #68746d;
                font-size: 11px;
            }

            .category-name {
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .category-dot {
                width: 8px;
                height: 8px;
                border-radius: 3px;
                background: #24785d;
            }

            .category-dot.gold {
                background: #d8a747;
            }

            .category-dot.blue {
                background: #7195bd;
            }

            .category-dot.lilac {
                background: #a497c9;
            }

            .category-value {
                color: var(--ink);
                font-weight: 680;
            }

            .bar-list {
                display: grid;
                gap: 19px;
                margin: 0;
                padding: 3px 0 0;
                list-style: none;
            }

            .bar-row {
                display: grid;
                gap: 9px;
            }

            .bar-labels {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                color: #68746d;
                font-size: 11px;
            }

            .bar-labels strong {
                color: #34423a;
                font-weight: 680;
            }

            .bar-track {
                height: 7px;
                overflow: hidden;
                border-radius: 99px;
                background: #eef1ef;
            }

            .bar-fill {
                height: 100%;
                border-radius: inherit;
                background: #24785d;
            }

            .bar-fill.gold {
                background: #d8a747;
            }

            .bar-fill.blue {
                background: #7195bd;
            }

            .bar-fill.lilac {
                background: #a497c9;
            }

            .summary-callout {
                display: flex;
                align-items: flex-start;
                gap: 11px;
                border: 1px solid #e6eee8;
                border-radius: 12px;
                background: #f7faf8;
                margin-top: 22px;
                padding: 13px;
            }

            .callout-icon {
                display: grid;
                width: 28px;
                height: 28px;
                flex: 0 0 auto;
                place-items: center;
                border-radius: 9px;
                background: #e7f1ea;
                color: var(--green);
            }

            .callout-icon svg {
                width: 15px;
                height: 15px;
            }

            .summary-callout p {
                margin: 0;
                color: #647169;
                font-size: 10px;
                line-height: 1.6;
            }

            .summary-callout strong {
                color: #34423a;
            }

            .footer-note {
                margin: 20px 0 0;
                color: #929c96;
                font-size: 10px;
                line-height: 1.6;
                text-align: center;
            }

            @media (max-width: 1120px) {
                .app-shell {
                    grid-template-columns: 220px minmax(0, 1fr);
                }

                .stats-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .dashboard-grid {
                    grid-template-columns: minmax(0, 1.4fr) minmax(250px, .9fr);
                }
            }

            @media (max-width: 820px) {
                .app-shell {
                    display: block;
                }

                .sidebar {
                    position: static;
                    width: 100%;
                    height: auto;
                    padding: 16px 20px 12px;
                }

                .brand {
                    padding: 0 2px 14px;
                }

                .nav-label,
                .sidebar-spacer,
                .source-note,
                .sidebar-footer {
                    display: none;
                }

                .nav-list {
                    display: flex;
                    overflow-x: auto;
                    gap: 5px;
                    padding-bottom: 2px;
                }

                .nav-link {
                    min-height: 38px;
                    flex: 0 0 auto;
                    font-size: 11px;
                }

                .nav-link svg {
                    width: 16px;
                    height: 16px;
                }

                .main {
                    padding: 24px 20px 34px;
                }

                .topbar {
                    margin-bottom: 26px;
                }
            }

            @media (max-width: 640px) {
                .main {
                    padding: 20px 15px 28px;
                }

                .topbar {
                    align-items: flex-start;
                    margin-bottom: 23px;
                }

                .topbar-meta {
                    font-size: 10px;
                }

                .breadcrumb {
                    font-size: 11px;
                }

                .page-heading {
                    align-items: flex-start;
                    flex-direction: column;
                    gap: 13px;
                }

                .page-description {
                    max-width: 410px;
                    font-size: 12px;
                }

                .stats-grid {
                    gap: 10px;
                }

                .stat-card {
                    padding: 14px;
                }

                .stat-label {
                    font-size: 10px;
                }

                .stat-icon {
                    width: 30px;
                    height: 30px;
                }

                .stat-value {
                    margin: 12px 0 7px;
                    font-size: clamp(17px, 5vw, 22px);
                }

                .stat-foot {
                    align-items: flex-start;
                    flex-direction: column;
                    gap: 3px;
                    font-size: 9px;
                }

                .dashboard-grid {
                    grid-template-columns: minmax(0, 1fr);
                    gap: 12px;
                }

                .panel {
                    padding: 18px 16px;
                }

                .panel-header {
                    flex-direction: column;
                }

                .chart-legend {
                    gap: 12px;
                }

                .donut-chart {
                    width: 160px;
                    height: 160px;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                *,
                *::before,
                *::after {
                    scroll-behavior: auto !important;
                    transition-duration: .01ms !important;
                }
            }
        </style>
    </head>
    <body>
        <div class="app-shell">
            <aside class="sidebar" aria-label="Navigasi utama">
                <a class="brand" href="#ringkasan" aria-label="DJPb Sumatera Utara, ke ringkasan">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" width="23" height="23">
                            <path d="M3 9h18M5 9v9m4-9v9m6-9v9m4-9v9M3 20h18M12 3l9 4H3l9-4Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span>
                        <span class="brand-name">DJPb Sumut</span>
                        <span class="brand-caption">Portal administrasi</span>
                    </span>
                </a>

                <p class="nav-label">Menu utama</p>
                <nav>
                    <ul class="nav-list">
                        <li>
                            <a class="nav-link" href="#ringkasan" aria-current="page">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="3.5" y="3.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
                                    <rect x="13.5" y="3.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
                                    <rect x="3.5" y="13.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
                                    <rect x="13.5" y="13.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7"/>
                                </svg>
                                Ringkasan
                            </a>
                        </li>
                        <li>
                            <a class="nav-link" href="#realisasi">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 19V5m0 14h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <path d="m7 15 4-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Realisasi anggaran
                            </a>
                        </li>
                        <li>
                            <a class="nav-link" href="#komposisi">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M20.2 15A8.5 8.5 0 0 1 9 3.8 8.5 8.5 0 1 0 20.2 15Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                    <path d="M14 3.5a7 7 0 0 1 6.5 6.5H14V3.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                </svg>
                                Komposisi belanja
                            </a>
                        </li>
                        <li>
                            <a class="nav-link" href="#rincian">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 4.5h14A1.5 1.5 0 0 1 20.5 6v14L17 17.5H5A1.5 1.5 0 0 1 3.5 16V6A1.5 1.5 0 0 1 5 4.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                    <path d="M7.5 9h9m-9 4h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                Rincian
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="sidebar-spacer"></div>
                <div class="source-note">
                    <strong>Sumber data</strong>
                    <p>Belum terhubung. Angka pada halaman ini merupakan ilustrasi tampilan.</p>
                </div>
                <div class="sidebar-footer">
                    <span class="avatar" aria-hidden="true">AD</span>
                    <span>
                        <span class="profile-name">Administrator</span>
                        <span class="profile-role">Panel administrasi</span>
                    </span>
                </div>
            </aside>

            <main class="main" id="ringkasan">
                <div class="topbar">
                    <div class="breadcrumb">
                        <span>Dashboard</span>
                        <span aria-hidden="true">/</span>
                        <strong>Ringkasan</strong>
                    </div>
                    <div class="topbar-meta">
                        <svg class="calendar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3.5" y="5" width="17" height="15.5" rx="2" stroke="currentColor" stroke-width="1.6"/>
                            <path d="M7.5 3.5v3m9-3v3M3.5 9.5h17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </div>
                </div>

                <section class="page-heading" aria-labelledby="page-title">
                    <div>
                        <p class="eyebrow">Kantor Wilayah DJPb Sumatera Utara</p>
                        <h1 id="page-title">Ringkasan Dashboard</h1>
                        <p class="page-description">Pantau gambaran umum anggaran dan kinerja dalam satu tampilan.</p>
                    </div>
                    <span class="preview-badge">
                        <span class="preview-dot" aria-hidden="true"></span>
                        Pratinjau · data ilustrasi
                    </span>
                </section>

                <section class="stats-grid" aria-label="Ringkasan indikator">
                    <article class="card stat-card">
                        <div class="stat-top">
                            <span class="stat-label">Pagu anggaran</span>
                            <span class="stat-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2.8v18.4M16.5 7H9.8a3.1 3.1 0 0 0 0 6.2h4.4a3.1 3.1 0 0 1 0 6.2H7.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </span>
                        </div>
                        <p class="stat-value">Rp 2,45 T</p>
                        <div class="stat-foot"><span class="stat-change">+4,8%</span><span>dari periode sebelumnya</span></div>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-top">
                            <span class="stat-label">Realisasi anggaran</span>
                            <span class="stat-icon gold" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 19V5m0 14h16M7 15l4-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                        <p class="stat-value">Rp 1,68 T</p>
                        <div class="stat-foot"><span class="stat-change">68,6%</span><span>dari total pagu</span></div>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-top">
                            <span class="stat-label">Pendapatan negara</span>
                            <span class="stat-icon blue" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 19.5h16M6.5 16V9m5.5 7V4m5.5 12v-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    <path d="M4.5 7 10 4l5 4 4.5-3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                        <p class="stat-value">Rp 428,6 M</p>
                        <div class="stat-foot"><span class="stat-change">+8,2%</span><span>dari periode sebelumnya</span></div>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-top">
                            <span class="stat-label">Transfer ke daerah</span>
                            <span class="stat-icon violet" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 8.5h16M6.5 8.5V19m5.5-10.5V19m5.5-10.5V19M3.5 20h17M12 3l8.5 3.5h-17L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                        <p class="stat-value">Rp 10,2 T</p>
                        <div class="stat-foot"><span class="stat-change">72,4%</span><span>dari alokasi</span></div>
                    </article>
                </section>

                <div class="dashboard-grid">
                    <section class="card panel" id="realisasi" aria-labelledby="realisasi-title">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title" id="realisasi-title">Tren realisasi anggaran</h2>
                                <p class="panel-subtitle">Persentase realisasi kumulatif per bulan · tahun berjalan</p>
                            </div>
                            <div class="chart-legend" aria-label="Legenda grafik">
                                <span class="legend-item"><span class="legend-swatch" aria-hidden="true"></span>Realisasi</span>
                            </div>
                        </div>
                        <figure style="margin: 0">
                            <svg class="line-chart" viewBox="0 0 720 260" role="img" aria-labelledby="line-title line-desc">
                                <title id="line-title">Grafik tren realisasi anggaran</title>
                                <desc id="line-desc">Grafik ilustrasi yang menunjukkan kenaikan realisasi dari Januari hingga Desember.</desc>
                                <defs>
                                    <linearGradient id="areaFill" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#24785d" stop-opacity=".18"/>
                                        <stop offset="100%" stop-color="#24785d" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>
                                <line class="chart-gridline" x1="48" y1="22" x2="704" y2="22"/>
                                <line class="chart-gridline" x1="48" y1="72" x2="704" y2="72"/>
                                <line class="chart-gridline" x1="48" y1="122" x2="704" y2="122"/>
                                <line class="chart-gridline" x1="48" y1="172" x2="704" y2="172"/>
                                <line class="chart-gridline" x1="48" y1="222" x2="704" y2="222"/>
                                <text x="8" y="26">100%</text>
                                <text x="17" y="76">75%</text>
                                <text x="17" y="126">50%</text>
                                <text x="17" y="176">25%</text>
                                <text x="28" y="226">0%</text>
                                <path class="chart-area" d="M48 222 L48 191 C72 187 86 180 104 178 S143 164 160 160 S199 150 216 145 S255 137 272 128 S311 120 328 113 S367 107 384 96 S423 91 440 83 S479 75 496 66 S535 59 552 51 S591 47 608 39 S635 32 648 28 S658 23 664 19 L664 222 Z"/>
                                <path class="chart-line" d="M48 191 C72 187 86 180 104 178 S143 164 160 160 S199 150 216 145 S255 137 272 128 S311 120 328 113 S367 107 384 96 S423 91 440 83 S479 75 496 66 S535 59 552 51 S591 47 608 39 S635 32 648 28 S658 23 664 19"/>
                                <circle cx="48" cy="191" r="3.5" fill="#24785d" stroke="#fff" stroke-width="2"/>
                                <circle cx="160" cy="160" r="3.5" fill="#24785d" stroke="#fff" stroke-width="2"/>
                                <circle cx="272" cy="128" r="3.5" fill="#24785d" stroke="#fff" stroke-width="2"/>
                                <circle cx="384" cy="96" r="3.5" fill="#24785d" stroke="#fff" stroke-width="2"/>
                                <circle cx="496" cy="66" r="3.5" fill="#24785d" stroke="#fff" stroke-width="2"/>
                                <circle cx="608" cy="39" r="3.5" fill="#24785d" stroke="#fff" stroke-width="2"/>
                                <circle cx="664" cy="19" r="4" fill="#24785d" stroke="#fff" stroke-width="2"/>
                                <text x="48" y="247" text-anchor="middle">Jan</text>
                                <text x="104" y="247" text-anchor="middle">Feb</text>
                                <text x="160" y="247" text-anchor="middle">Mar</text>
                                <text x="216" y="247" text-anchor="middle">Apr</text>
                                <text x="272" y="247" text-anchor="middle">Mei</text>
                                <text x="328" y="247" text-anchor="middle">Jun</text>
                                <text x="384" y="247" text-anchor="middle">Jul</text>
                                <text x="440" y="247" text-anchor="middle">Agu</text>
                                <text x="496" y="247" text-anchor="middle">Sep</text>
                                <text x="552" y="247" text-anchor="middle">Okt</text>
                                <text x="608" y="247" text-anchor="middle">Nov</text>
                                <text x="664" y="247" text-anchor="middle">Des</text>
                            </svg>
                        </figure>
                    </section>

                    <section class="card panel" id="komposisi" aria-labelledby="komposisi-title">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title" id="komposisi-title">Komposisi belanja</h2>
                                <p class="panel-subtitle">Proporsi per jenis belanja</p>
                            </div>
                        </div>
                        <div class="donut-wrap">
                            <svg class="donut-chart" viewBox="0 0 120 120" role="img" aria-labelledby="donut-title donut-desc">
                                <title id="donut-title">Komposisi belanja ilustrasi</title>
                                <desc id="donut-desc">Belanja pegawai 42 persen, barang 28 persen, modal 19 persen, dan bantuan sosial 11 persen.</desc>
                                <circle cx="60" cy="60" r="45" fill="none" stroke="#eef1ef" stroke-width="13"/>
                                <circle cx="60" cy="60" r="45" fill="none" stroke="#24785d" stroke-width="13" stroke-dasharray="118.75 282.74" stroke-dashoffset="0" transform="rotate(-90 60 60)"/>
                                <circle cx="60" cy="60" r="45" fill="none" stroke="#d8a747" stroke-width="13" stroke-dasharray="79.17 282.74" stroke-dashoffset="-118.75" transform="rotate(-90 60 60)"/>
                                <circle cx="60" cy="60" r="45" fill="none" stroke="#7195bd" stroke-width="13" stroke-dasharray="53.72 282.74" stroke-dashoffset="-197.92" transform="rotate(-90 60 60)"/>
                                <circle cx="60" cy="60" r="45" fill="none" stroke="#a497c9" stroke-width="13" stroke-dasharray="31.10 282.74" stroke-dashoffset="-251.64" transform="rotate(-90 60 60)"/>
                                <text class="donut-total" x="60" y="58">100%</text>
                                <text class="donut-caption" x="60" y="70">total alokasi</text>
                            </svg>
                        </div>
                        <ul class="category-list">
                            <li class="category-row"><span class="category-name"><span class="category-dot" aria-hidden="true"></span>Belanja pegawai</span><span class="category-value">42%</span></li>
                            <li class="category-row"><span class="category-name"><span class="category-dot gold" aria-hidden="true"></span>Belanja barang</span><span class="category-value">28%</span></li>
                            <li class="category-row"><span class="category-name"><span class="category-dot blue" aria-hidden="true"></span>Belanja modal</span><span class="category-value">19%</span></li>
                            <li class="category-row"><span class="category-name"><span class="category-dot lilac" aria-hidden="true"></span>Bantuan sosial</span><span class="category-value">11%</span></li>
                        </ul>
                    </section>
                </div>

                <section class="card panel" id="rincian" aria-labelledby="rincian-title">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title" id="rincian-title">Realisasi per jenis belanja</h2>
                            <p class="panel-subtitle">Perbandingan nilai realisasi terhadap pagu</p>
                        </div>
                    </div>
                    <ul class="bar-list">
                        <li class="bar-row">
                            <div class="bar-labels"><span>Belanja pegawai</span><strong>82%</strong></div>
                            <div class="bar-track" role="img" aria-label="Belanja pegawai: 82 persen"><div class="bar-fill" style="width: 82%"></div></div>
                        </li>
                        <li class="bar-row">
                            <div class="bar-labels"><span>Belanja barang</span><strong>67%</strong></div>
                            <div class="bar-track" role="img" aria-label="Belanja barang: 67 persen"><div class="bar-fill gold" style="width: 67%"></div></div>
                        </li>
                        <li class="bar-row">
                            <div class="bar-labels"><span>Belanja modal</span><strong>51%</strong></div>
                            <div class="bar-track" role="img" aria-label="Belanja modal: 51 persen"><div class="bar-fill blue" style="width: 51%"></div></div>
                        </li>
                        <li class="bar-row">
                            <div class="bar-labels"><span>Bantuan sosial</span><strong>74%</strong></div>
                            <div class="bar-track" role="img" aria-label="Bantuan sosial: 74 persen"><div class="bar-fill lilac" style="width: 74%"></div></div>
                        </li>
                    </ul>
                    <div class="summary-callout">
                        <span class="callout-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M12 3.5 14.6 9l5.9.9-4.3 4.2 1 5.9-5.2-2.8-5.2 2.8 1-5.9-4.3-4.2 5.9-.9L12 3.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <p><strong>Catatan:</strong> Nilai dan tren di atas hanya contoh visual untuk pratinjau dashboard. Hubungkan sumber data anggaran untuk menampilkan informasi aktual.</p>
                    </div>
                </section>

                <p class="footer-note">Dashboard Administrasi · DJPb Sumatera Utara</p>
            </main>
        </div>
    </body>
</html>
