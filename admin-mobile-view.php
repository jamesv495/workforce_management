<?php
require_once __DIR__ . '/includes/page_bootstrap.php';
$wfmPageUser = wfmRequirePageRole('admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#163B6D">
    <title>Holiday Travelers - Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['Poppins', 'sans-serif'],
                        button: ['Poppins', 'sans-serif'],
                        body: ['Inter', 'sans-serif']
                    },
                    colors: {
                        primary: '#163B6D',
                        secondary: '#F59B45',
                        accent: '#6FA9E6',
                        background: '#F8FAFC',
                        card: '#FFFFFF',
                        border: '#E5E7EB',
                        success: '#22C55E',
                        warning: '#FBBF24',
                        error: '#EF4444'
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #F8FAFC;
        }

        ::-webkit-scrollbar-thumb {
            background: #E5E7EB;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6FA9E6;
        }

        .transition-all-300 {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ================= MOBILE ADMIN VIEW ================= */
        /*
         * Original admin.php content parity:
         * The dashboard, employee directory, attendance, roster, timesheet,
         * leave, overtime, analytics sections, tables, forms, modals, and
         * actions are kept in sync with admin.php.
         *
         * Mobile-only rules below change presentation/interaction only:
         * - responsive navigation drawer
         * - stacked tables at phone widths
         * - wrapped toolbars/controls
         * - scrollable month roster
         * - viewport-safe modals
         */

        @media (max-width: 767px) {

            html,
            body {
                width: 100%;
                max-width: 100%;
                overflow: hidden;
            }

            #app-workspace {
                min-width: 0;
                width: 100%;
            }

            body.mobile-nav-open {
                overflow: hidden;
            }

            #mobile-menu.hidden {
                display: none !important;
            }

            #mobile-menu-backdrop.hidden {
                display: none !important;
            }

            #mobile-menu {
                position: fixed;
                inset: 0 auto 0 0;
                width: min(86vw, 320px);
                height: 100dvh;
                background: #163B6D;
                display: flex;
                flex-direction: column;
                box-shadow: 12px 0 32px rgba(15, 23, 42, .22);
                z-index: 50;
                overflow: hidden;
            }

            #mobile-menu-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, .52);
                backdrop-filter: blur(2px);
                z-index: 40;
            }

            #mobile-sidebar-nav {
                -webkit-overflow-scrolling: touch;
                overscroll-behavior: contain;
            }

            #mobile-sidebar-nav .mobile-nav-item {
                min-height: 46px;
                width: 100%;
                text-align: left;
                padding: .72rem .9rem;
                border-radius: .9rem;
                color: #D1D5DB;
            }

            #mobile-sidebar-nav .mobile-nav-item.bg-secondary {
                color: #FFFFFF;
            }

            #mobile-sidebar-nav hr {
                border-color: rgba(255, 255, 255, .10);
            }

            #mobile-sidebar-nav .mobile-sidebar-submenu {
                margin-left: .8rem;
                padding-left: .45rem;
                border-left: 1px solid rgba(255, 255, 255, .12);
            }

            main {
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-y: contain;
            }

            .mobile-stack-table {
                display: block;
                width: 100%;
            }

            .mobile-stack-table thead {
                display: none;
            }

            .mobile-stack-table tbody {
                display: block;
                width: 100%;
            }

            .mobile-stack-table tbody tr {
                display: block;
                width: 100%;
                margin-bottom: .75rem;
                border: 1px solid #E5E7EB;
                border-radius: 1rem;
                background: #FFFFFF;
                overflow: hidden;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            }

            .mobile-stack-table tbody tr:last-child {
                margin-bottom: 0;
            }

            .mobile-stack-table tbody td {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: .85rem;
                width: 100%;
                min-height: 46px;
                padding: .72rem .9rem;
                text-align: right;
                border-bottom: 1px solid #E5E7EB;
            }

            .mobile-stack-table tbody td:last-child {
                border-bottom: 0;
            }

            .mobile-stack-table tbody td::before {
                content: attr(data-label);
                flex: 0 0 auto;
                max-width: 42%;
                text-align: left;
                font-size: .68rem;
                line-height: 1.1rem;
                font-weight: 700;
                letter-spacing: .045em;
                text-transform: uppercase;
                color: #6B7280;
            }

            .mobile-stack-table tbody td>* {
                max-width: 62%;
            }

            .mobile-stack-table tbody td .flex {
                justify-content: flex-end;
                width: auto;
            }

            .mobile-stack-table tbody td[colspan] {
                display: block;
                text-align: center;
            }

            .mobile-stack-table tbody td[colspan]::before {
                display: none;
            }

            .mobile-stack-table tbody tr.hidden {
                display: none;
            }

            /* Better touch targets for small-screen controls. */
            #employees input,
            #employees select,
            #time-attendance input,
            #time-attendance select,
            #schedule select,
            #schedule button,
            #leave button,
            #overtime button,
            #analytics select {
                min-height: 44px;
            }

            #attendance-period-preview {
                max-width: 100%;
            }

            /* Attendance filter bar should stack instead of squeezing sideways. */
            #time-attendance .attendance-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: .65rem;
            }

            #time-attendance .attendance-toolbar>div {
                width: 100%;
            }

            #time-attendance .attendance-toolbar>div:last-child {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: .5rem;
            }

            /* Schedule toolbar: keep controls in a comfortable mobile row. */
            #schedule .schedule-toolbar-actions {
                width: 100%;
                flex-wrap: wrap;
                gap: .5rem;
            }

            #schedule .schedule-toolbar-actions>* {
                flex: 1 1 145px;
                width: 100%;
            }

            /* Monthly calendar needs horizontal scrolling instead of becoming unreadably narrow. */
            #monthRosterView {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 1rem;
            }

            #monthRosterView .month-calendar-grid {
                min-width: 560px;
            }

            /* Keep modal content inside the phone viewport. */
            #modal-request-leave>div,
            #modal-create-schedule>div,
            #modal-employee-profile>div,
            #modal-correction-review>div {
                max-height: calc(100dvh - 2rem);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Mobile header branding. */
            #mobile-brand-logo {
                width: 34px;
                height: 34px;
                object-fit: contain;
                flex: 0 0 auto;
            }
        }

        @media (max-width: 767px) {

            /* ================= MOBILE SCROLL / VIEWPORT FIX ================= */
            html,
            body {
                height: 100%;
                min-height: 100%;
            }

            body {
                height: 100dvh;
                min-height: 100dvh;
            }

            #app-workspace {
                height: 100dvh;
                min-height: 0;
            }

            #app-workspace>.flex-1.flex.flex-col {
                min-height: 0;
            }

            main {
                min-height: 0;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-y: contain;
                scroll-padding-bottom: 2rem;
                padding-bottom: 2rem;
            }

            /* Tables/cards may scroll sideways, but page scrolling stays vertical. */
            main .overflow-x-auto {
                max-width: 100%;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-x: contain;
            }
        }

        /* ================= TIMESHEET MODULE ENHANCEMENTS ================= */
        #timesheet .ts-summary-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        #timesheet .ts-summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }

        #timesheet .ts-info-box {
            background: #F8FAFC;
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            padding: 12px 14px;
        }

        #timesheet .ts-info-box span {
            display: block;
            color: #6B7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            margin-bottom: 3px;
        }

        #timesheet .ts-info-box strong {
            display: block;
            color: #163B6D;
            font-size: 13px;
            line-height: 1.35;
        }

        #timesheet .ts-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            line-height: 1;
            font-weight: 700;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        #timesheet .ts-status-badge::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: currentColor;
        }

        #timesheet .status-pending {
            color: #B45309;
            background: #FFFBEB;
            border-color: #FDE68A;
        }

        #timesheet .status-approved {
            color: #047857;
            background: #ECFDF5;
            border-color: #A7F3D0;
        }

        #timesheet .status-rejected {
            color: #B91C1C;
            background: #FEF2F2;
            border-color: #FECACA;
        }

        #timesheet .status-correction {
            color: #92400E;
            background: #FFFBEB;
            border-color: #FCD34D;
        }

        #timesheet .ts-record-card {
            position: relative;
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        #timesheet .ts-record-card:hover {
            border-color: #CBD5E1;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        #timesheet .ts-record-card.ts-high-overtime {
            border-color: #FECACA;
        }

        #timesheet .ts-high-ot-bar {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #EF4444;
        }

        #timesheet .ts-high-ot-value {
            color: #DC2626;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 10px;
            padding: 3px 8px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 800;
        }

        #timesheet .ts-high-ot-label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 6px;
            color: #B91C1C;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 10px;
            line-height: 1;
            font-weight: 800;
        }

        #timesheet .ts-action-btn {
            transition: background 0.2s ease, color 0.2s ease, opacity 0.2s ease;
        }

        #timesheet .ts-action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 640px) {
            #timesheet .ts-summary-card {
                padding: 13px;
            }

            #timesheet .ts-record-card .ts-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
            }

            #timesheet .ts-record-card .ts-actions {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>

<body data-mobile-admin-view="true"
    class="bg-background text-primary font-body antialiased flex h-screen overflow-hidden">


    <div id="app-workspace" class="h-screen min-h-0 w-full flex">
        <!-- Sidebar Navigation (Admin Dark Theme) -->
        <aside class="w-64 bg-primary text-gray-300 flex-shrink-0 hidden md:flex flex-col shadow-xl z-20 pb-6">
            <!-- Brand -->
            <div class="h-16 flex items-center px-6 border-b border-white/10 bg-primary">
                <img src="assets/images/Company_logo.png" alt="Company Logo" class="mr-3 object-contain"
                    style="height: 72px; width: auto;">
                <span class="font-bold text-lg text-white tracking-tight">Workforce Admin</span>
            </div>

            <!-- User Info -->
            <div class="p-6 border-b border-white/10 bg-primary/50">
                <div class="flex items-center space-x-3">
                    <img src="assets/images/profile.png" alt="Admin Avatar"
                        class="h-12 w-12 rounded-full object-cover border-2 border-accent">
                    <div>
                        <h3 class="text-sm font-semibold text-white" data-session="name">Admin Name</h3>
                        <p class="text-xs text-gray-400" data-session="role">Admin Role</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                <a href="#"
                    class="nav-btn active flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl bg-secondary text-white"
                    data-target="dashboard">
                    <i class="fa-solid fa-chart-line w-6 text-center mr-2"></i> Overview
                </a>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="employees">
                    <i class="fa-solid fa-users w-6 text-center mr-2"></i> Employees
                </a>

                <hr class="border-t border-white/10 my-3">

                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-2">Modules</p>

                <a href="#"
                    class="nav-btn flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="time-attendance" onclick="toggleAttendanceSubmenu(event)">
                    <span class="flex items-center">
                        <i class="fa-solid fa-users-viewfinder w-6 text-center mr-2"></i> Live Attendance
                    </span>
                    <i id="attendance-submenu-chevron"
                        class="fa-solid fa-chevron-down text-xs transition-transform"></i>
                </a>
                <div id="attendance-submenu" class="hidden ml-8 space-y-1 overflow-hidden">
                    <a href="#"
                        class="nav-btn flex items-center px-3 py-2 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                        data-target="overtime">
                        <i class="fa-solid fa-business-time w-5 text-center mr-2 text-xs"></i> Overtime
                    </a>
                </div>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="schedule">
                    <i class="fa-solid fa-calendar-check w-6 text-center mr-2"></i> Master Roster
                </a>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="timesheet">
                    <i class="fa-solid fa-file-signature w-6 text-center mr-2"></i> Timesheet Approvals
                </a>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="leave">
                    <i class="fa-solid fa-plane-slash w-6 text-center mr-2"></i> Leave Requests
                </a>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="analytics">
                    <i class="fa-solid fa-chart-pie w-6 text-center mr-2"></i> Workforce Analytics
                </a>
            </nav>

            <!-- Footer / Logout -->
            <button onclick="logout()"
                class="w-full py-2 bg-error/10 hover:bg-error/20 text-error font-button text-sm rounded-xl flex items-center justify-center gap-2 transition-all-300 mb-4">
                <i data-lucide="log-out" class="w-4 h-5"></i>
                Logout System
            </button>

        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">

            <!-- Mobile Header -->
            <header class="bg-primary flex items-center justify-between px-4 h-16 md:hidden z-30 shadow-md">
                <div class="flex items-center min-w-0">
                    <img id="mobile-brand-logo" src="assets/images/Company_logo.png" alt="Company Logo" class="mr-2">
                    <span class="font-bold text-white truncate">Workforce Admin</span>
                </div>
                <button id="mobile-menu-btn" class="text-gray-300 hover:text-white focus:outline-none p-2">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </header>

            <!-- Mobile Sidebar / Navigation Drawer -->
            <div id="mobile-menu-backdrop" class="hidden md:hidden" aria-hidden="true"></div>

            <aside id="mobile-menu" class="hidden md:hidden" aria-label="Mobile admin navigation">
                <div
                    class="flex items-center justify-between px-5 h-16 border-b border-white/10 bg-primary flex-shrink-0">
                    <div class="flex items-center min-w-0">
                        <img src="assets/images/Company_logo.png" alt="Company Logo"
                            class="w-9 h-9 object-contain mr-2 flex-shrink-0">
                        <span class="font-bold text-white truncate">Workforce Admin</span>
                    </div>
                    <button id="mobile-sidebar-close" type="button"
                        class="text-gray-300 hover:text-white focus:outline-none p-2 rounded-lg hover:bg-white/10"
                        aria-label="Close navigation">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div id="mobile-sidebar-user" class="p-5 border-b border-white/10 bg-primary/50"></div>
                <div id="mobile-sidebar-nav" class="flex-1 overflow-y-auto py-4 px-3"></div>

                <div class="p-4 border-t border-white/10 flex-shrink-0">
                    <button onclick="logout()" type="button"
                        class="w-full py-3 bg-error/10 hover:bg-error/20 text-error font-button text-sm rounded-xl flex items-center justify-center gap-2 transition-all-300">
                        <i data-lucide="log-out" class="w-4 h-5"></i>
                        Logout System
                    </button>
                </div>
            </aside>

            <!------------ Top Bar (Desktop) ----------------------------->
            <header
                class="bg-card border-b border-border items-center justify-between px-8 h-16 hidden md:flex z-10 shadow-sm">
                <h1 class="font-heading text-xl font-bold text-primary flex items-center" id="page-title">
                    Overview
                </h1>
                <div class="flex items-center space-x-6">
                    <div class="relative" id="admin-global-search-wrap">
                        <i
                            class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        <input id="admin-global-search" type="text" autocomplete="off"
                            placeholder="Search staff, tours..." aria-label="Search employees"
                            aria-controls="admin-global-search-results" aria-expanded="false"
                            oninput="handleAdminGlobalSearch()" onfocus="handleAdminGlobalSearch()"
                            class="pl-10 pr-4 py-2 border border-border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-accent bg-background w-64">
                        <div id="admin-global-search-results"
                            class="hidden absolute right-0 top-12 w-80 max-h-80 overflow-y-auto bg-card border border-border rounded-2xl shadow-xl z-[80] p-2"
                            role="listbox" aria-label="Employee search results"></div>
                    </div>

                    <div class="relative">
                        <button id="admin-notification-button" type="button" onclick="toggleAdminNotifications(event)"
                            class="font-button relative p-2 text-gray-400 hover:text-accent transition-colors rounded-xl hover:bg-background focus:outline-none focus:ring-2 focus:ring-accent/40"
                            aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-bell text-xl"></i>
                            <span id="admin-notification-dot"
                                class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
                        </button>
                        <div id="admin-notification-panel"
                            class="hidden absolute right-0 top-12 w-96 max-w-[calc(100vw-2rem)] bg-card border border-border rounded-2xl shadow-xl z-[80] overflow-hidden">
                            <div class="px-4 py-3 border-b border-border flex items-center justify-between">
                                <div>
                                    <p class="font-heading font-bold text-primary text-sm">Notifications</p>
                                    <p id="admin-notification-summary" class="text-xs text-gray-500 mt-0.5">Checking
                                        pending items...</p>
                                </div>
                                <button type="button" onclick="refreshAdminNotifications(); event.stopPropagation();"
                                    class="text-xs font-semibold text-accent hover:text-primary px-2 py-1 rounded-lg hover:bg-background">Refresh</button>
                            </div>
                            <div id="admin-notification-list" class="max-h-80 overflow-y-auto p-2"></div>
                        </div>
                    </div>

                    <div class="relative">
                        <button id="admin-settings-button" type="button" onclick="toggleAdminSettings(event)"
                            class="font-button relative p-2 text-gray-400 hover:text-accent transition-colors rounded-xl hover:bg-background focus:outline-none focus:ring-2 focus:ring-accent/40"
                            aria-label="Settings" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-gear text-xl"></i>
                        </button>
                        <div id="admin-settings-panel"
                            class="hidden absolute right-0 top-12 w-80 max-w-[calc(100vw-2rem)] bg-card border border-border rounded-2xl shadow-xl z-[80] overflow-hidden">
                            <div class="px-4 py-3 border-b border-border">
                                <p class="font-heading font-bold text-primary text-sm">Admin Settings</p>
                                <p class="text-xs text-gray-500 mt-0.5">Quick controls for the admin header.</p>
                            </div>
                            <div class="p-3 space-y-2">
                                <button type="button" onclick="openSettingsEmployees()"
                                    class="w-full flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-background transition-colors text-left"><span
                                        class="flex items-center gap-3"><span
                                            class="w-9 h-9 rounded-xl bg-blue-50 text-primary flex items-center justify-center"><i
                                                class="fa-solid fa-users"></i></span><span><span
                                                class="block text-sm font-semibold text-primary">Employee
                                                Management</span><span class="block text-xs text-gray-500">Open the
                                                employee directory</span></span></span><i
                                        class="fa-solid fa-chevron-right text-xs text-gray-400"></i></button>
                                <button id="admin-notification-toggle" type="button"
                                    onclick="toggleAdminNotificationAlerts(event)"
                                    class="w-full flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-background transition-colors text-left"><span
                                        class="flex items-center gap-3"><span
                                            class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i
                                                class="fa-solid fa-bell"></i></span><span><span
                                                class="block text-sm font-semibold text-primary">Notification
                                                Alerts</span><span id="admin-notification-toggle-text"
                                                class="block text-xs text-gray-500">Enabled</span></span></span><span
                                        id="admin-notification-toggle-pill"
                                        class="inline-flex w-10 h-6 rounded-full bg-primary relative transition-colors"><span
                                            class="absolute top-1 left-5 w-4 h-4 rounded-full bg-white transition-all"></span></span></button>
                                <div class="border-t border-border pt-2 mt-2 flex justify-end"><button type="button"
                                        onclick="closeAdminHeaderMenus()"
                                        class="text-xs font-semibold text-gray-500 hover:text-primary px-3 py-2 rounded-lg hover:bg-background">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Main Scrollable Content -->
            <main class="flex-1 min-h-0 overflow-y-auto p-4 md:p-8 pb-8 bg-background relative">

                <!-- ================= OVERVIEW TAB ================= -->
                <div id="dashboard" class="tab-content active max-w-7xl mx-auto space-y-6">

                    <!-- Actionable Alerts -->
                    <div
                        class="bg-accent/10 border-l-4 border-accent p-4 rounded-r-2xl flex justify-between items-start md:items-center flex-col md:flex-row gap-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-accent/20 rounded-full text-primary mr-4">
                                <i class="fa-solid fa-circle-exclamation text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-heading font-semibold text-primary">Action Required</h3>
                                <p class="font-body text-sm text-primary/80">You have <strong>0</strong> timesheets and
                                    <strong>0</strong> leave requests awaiting your approval.
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="switchTab('timesheet')"
                                class="font-button px-4 py-2 bg-card text-primary text-sm font-medium rounded-xl shadow-sm border border-border hover:bg-background">View
                                Timesheets</button>
                        </div>
                    </div>

                    <!----------------------------------------------- CARDS -------------------------------------------------------------------------->
                    <!-- Admin Metrics Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-2xl"><i
                                        class="fa-solid fa-users text-xl"></i></div>
                                <span
                                    class="text-emerald-500 text-sm font-bold bg-emerald-50 px-2 py-1 rounded">Live</span>
                            </div>
                            <h3 id="overview-clocked-in" class="font-heading text-3xl font-bold text-primary">0 <span
                                    class="text-lg text-gray-400 font-normal">/ 0</span></h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Staff Currently Clocked In</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-amber-100 text-amber-600 rounded-2xl"><i
                                        class="fa-solid fa-map-location-dot text-xl"></i></div>
                            </div>
                            <h3 id="overview-active-tours" class="font-heading text-3xl font-bold text-primary">0</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Active Tours Today</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-purple-100 text-purple-600 rounded-2xl"><i
                                        class="fa-solid fa-user-clock text-xl"></i></div>
                                <span class="text-red-500 text-xs font-bold"><i class="fa-solid fa-arrow-trend-up"></i>
                                    2%</span>
                            </div>
                            <h3 id="overview-late-week" class="font-heading text-3xl font-bold text-primary">0</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Late Arrivals (This Week)</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-red-100 text-red-600 rounded-2xl"><i
                                        class="fa-solid fa-user-minus text-xl"></i></div>
                            </div>
                            <h3 id="overview-on-leave" class="font-heading text-3xl font-bold text-primary">0</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Staff on Leave Today</p>
                        </div>
                    </div>

                    <!--------------------------------------------- Live Operations & Upcoming Shifts ------------------------------------------->

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <!-- Live Tours -->
                        <div
                            class="lg:col-span-2 bg-card rounded-2xl shadow-sm border border-border flex flex-col h-full">
                            <div
                                class="p-5 border-b border-border flex justify-between items-center bg-background rounded-t-2xl">
                                <h3 class="font-semibold text-primary">Live Tour Operations</h3>
                                <span class="relative flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-500 uppercase bg-card border-b border-border">
                                        <tr>
                                            <th class="px-5 py-3">Tour / Location</th>
                                            <th class="px-5 py-3">Assigned Guide</th>
                                            <th class="px-5 py-3">Time</th>
                                            <th class="px-5 py-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        <tr class="hover:bg-background">
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-primary">Name of Tour</p>
                                                <p class="text-xs text-gray-500"><i
                                                        class="fa-solid fa-location-dot mr-1"></i> Address</p>
                                            </td>
                                            <td class="px-5 py-4 flex items-center space-x-2">
                                                <img src="assets/images/Employee.png" class="w-8 h-8 rounded-full">
                                                <span class="font-medium">Employee 1</span>
                                            </td>
                                            <td class="px-5 py-4 text-gray-600">--</td>
                                            <td class="px-5 py-4"><span
                                                    class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded font-semibold">In
                                                    Progress</span></td>
                                        </tr>
                                        <tr class="hover:bg-background">
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-primary">Name of Tour</p>
                                                <p class="text-xs text-gray-500"><i
                                                        class="fa-solid fa-location-dot mr-1"></i> Address</p>
                                            </td>
                                            <td class="px-5 py-4 flex items-center space-x-2">
                                                <img src="assets/images/Employee.png" class="w-8 h-8 rounded-full">
                                                <span class="font-medium">Employee 2</span>
                                            </td>
                                            <td class="px-5 py-4 text-gray-600">--</td>
                                            <td class="px-5 py-4"><span
                                                    class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded font-semibold">In
                                                    Progress</span></td>
                                        </tr>
                                        <tr class="hover:bg-background">
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-primary">Name of Tour</p>
                                                <p class="text-xs text-gray-500"><i
                                                        class="fa-solid fa-location-dot mr-1"></i> Address</p>
                                            </td>
                                            <td class="px-5 py-4 flex items-center space-x-2">
                                                <img src="assets/images/Employee.png" class="w-8 h-8 rounded-full">
                                                <span class="font-medium">Employee 3</span>
                                            </td>
                                            <td class="px-5 py-4 text-gray-600">--</td>
                                            <td class="px-5 py-4"><span
                                                    class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded font-semibold">Staging
                                                    (Soon)</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 border-t border-border bg-background text-center rounded-b-2xl">
                                <a href="#" class="text-sm font-medium text-accent hover:text-primary">View All Active
                                    Operations &rarr;</a>
                            </div>
                        </div>

                        <!--------------------------------------------- Callouts / Unassigned------------------------------------ -->

                        <div class="bg-card rounded-2xl shadow-sm border border-border">
                            <div class="p-5 border-b border-border flex justify-between items-center">
                                <h3 class="font-semibold text-primary">Critical Alerts</h3>
                            </div>
                            <div class="p-4 space-y-4">
                                <!-- Alert 1 -->
                                <div class="flex items-start space-x-3 p-3 bg-red-50 rounded-2xl border border-red-100">
                                    <i class="fa-solid fa-user-nurse text-red-500 mt-1"></i>
                                    <div>
                                        <p class="text-sm font-bold text-red-900">Sick Call-out</p>
                                        <p class="text-xs text-red-700 mt-1">Employee Name called out sick. He is
                                            assigned to "Night Market" at 6 PM.</p>
                                        <button
                                            class="font-button mt-2 text-xs font-semibold bg-red-100 text-red-800 px-2 py-1 rounded hover:bg-red-200">Find
                                            Replacement</button>
                                    </div>
                                </div>
                                <!-- Alert 2 -->
                                <div
                                    class="flex items-start space-x-3 p-3 bg-amber-50 rounded-2xl border border-amber-100">
                                    <i class="fa-solid fa-clock-rotate-left text-amber-500 mt-1"></i>
                                    <div>
                                        <p class="text-sm font-bold text-amber-900">Missing Clock-in</p>
                                        <p class="text-xs text-amber-700 mt-1">Employee Name is 15 mins late for HQ
                                            Briefing.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= EMPLOYEES TAB ================= -->
                <div id="employees" class="tab-content max-w-7xl mx-auto space-y-6 hidden">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-2">

                        <!-- Search + Filters -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
                            <div class="relative">
                                <i
                                    class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input id="employee-search" type="text" placeholder="Search Employee"
                                    oninput="filterEmployeeTable()"
                                    class="pl-9 pr-4 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent w-full sm:w-64">
                            </div>

                            <div class="relative">
                                <select id="employee-filter-department" onchange="filterEmployeeTable()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full sm:w-auto">
                                    <option value="">Department</option>
                                    <option value="OPERATIONS">Operations</option>
                                    <option value="MONITORING">Monitoring</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="relative">
                                <select id="employee-filter-status" onchange="filterEmployeeTable()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full sm:w-auto">
                                    <option value="">Status</option>
                                    <option value="ACTIVE">Active</option>
                                    <option value="LEAVE">Leave</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                        <button type="button" onclick="openAddEmployeeModal()"
                            class="font-button px-5 py-2.5 bg-secondary hover:bg-[#E08A3B] text-white font-medium text-sm rounded-xl shadow-lg shadow-secondary/30 flex items-center gap-2 transition-all-300 flex-shrink-0">
                            <i class="fa-solid fa-user-plus"></i> Add Employee
                        </button>
                    </div>

                    <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                        <table class="mobile-stack-table w-full text-sm text-left">
                            <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                <tr>
                                    <th class="px-5 py-3.5">ID</th>
                                    <th class="px-5 py-3.5">Employee</th>
                                    <th class="px-5 py-3.5">Position</th>
                                    <th class="px-5 py-3.5">Department</th>
                                    <th class="px-5 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border" id="employees-table-body">
                                <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                    onclick="openEmployeeProfile(this)" data-id="EMP1" data-name="EMPLOYEE 1"
                                    data-position="TOUR GUIDE" data-department="OPERATIONS" data-status="ACTIVE">
                                    <td class="px-5 py-4 font-medium text-primary">EMP1</td>
                                    <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 1</td>
                                    <td class="px-5 py-4 text-gray-600">TOUR GUIDE</td>
                                    <td class="px-5 py-4 text-gray-600">OPERATIONS</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <span
                                                class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">ACTIVE</span>
                                            <button type="button"
                                                onclick="event.stopPropagation(); openEmployeeProfile(this.closest('tr'))"
                                                class="hidden md:inline-flex items-center gap-1 text-xs font-medium text-accent opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                <i class="fa-solid fa-eye"></i> View
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                    onclick="openEmployeeProfile(this)" data-id="EMP2" data-name="EMPLOYEE 2"
                                    data-position="STAFF" data-department="OPERATIONS" data-status="LEAVE">
                                    <td class="px-5 py-4 font-medium text-primary">EMP2</td>
                                    <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 2</td>
                                    <td class="px-5 py-4 text-gray-600">STAFF</td>
                                    <td class="px-5 py-4 text-gray-600">OPERATIONS</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <span
                                                class="inline-flex items-center bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full">LEAVE</span>
                                            <button type="button"
                                                onclick="event.stopPropagation(); openEmployeeProfile(this.closest('tr'))"
                                                class="hidden md:inline-flex items-center gap-1 text-xs font-medium text-accent opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                <i class="fa-solid fa-eye"></i> View
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                    onclick="openEmployeeProfile(this)" data-id="EMP3" data-name="EMPLOYEE 3"
                                    data-position="MANAGER" data-department="MONITORING" data-status="ACTIVE">
                                    <td class="px-5 py-4 font-medium text-primary">EMP3</td>
                                    <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 3</td>
                                    <td class="px-5 py-4 text-gray-600">MANAGER</td>
                                    <td class="px-5 py-4 text-gray-600">MONITORING</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <span
                                                class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">ACTIVE</span>
                                            <button type="button"
                                                onclick="event.stopPropagation(); openEmployeeProfile(this.closest('tr'))"
                                                class="hidden md:inline-flex items-center gap-1 text-xs font-medium text-accent opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                <i class="fa-solid fa-eye"></i> View
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-------------------------------- ================= LIVE ATTENDANCE TAB =================-------------------------------->

                <div id="time-attendance" class="tab-content max-w-7xl mx-auto space-y-6 hidden">

                    <!-- Attendance Period Selector + Date/Export -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="relative inline-block">
                            <select id="attendancePeriodSelect" onchange="updateAttendancePeriodPreview()"
                                class="pl-4 pr-9 py-2.5 border border-border rounded-2xl text-sm font-medium text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none">
                                <option>Today</option>
                                <option>This Week</option>
                                <option>This Month</option>
                            </select>
                            <i
                                class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <div class="flex space-x-3">
                                <input id="attendancePeriodDate" type="date" onchange="updateAttendancePeriodPreview()"
                                    class="border border-border rounded-2xl px-3 py-2 text-sm text-gray-600"
                                    value="2026-07-26">
                                <button onclick="exportDTRArchive()"
                                    class="font-button bg-card border border-border text-primary px-4 py-2 rounded-2xl text-sm font-medium hover:bg-background"><i
                                        class="fa-solid fa-file-pdf mr-2"></i> Export DTR</button>
                            </div>
                            <p id="attendancePeriodPreview" class="text-xs text-gray-400 pr-1"></p>
                        </div>
                    </div>

                    <!-- Attendance Summary Cards -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-2xl"><i
                                        class="fa-solid fa-user-check text-xl"></i></div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Present</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">2</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Total Percentage</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-purple-100 text-purple-600 rounded-2xl"><i
                                        class="fa-solid fa-clock text-xl"></i></div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Late</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">1</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Today</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-amber-100 text-amber-600 rounded-2xl"><i
                                        class="fa-solid fa-user-xmark text-xl"></i></div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Absent</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">0</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Today</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-red-100 text-red-600 rounded-2xl"><i
                                        class="fa-solid fa-plane-slash text-xl"></i></div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">On Leave</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">1</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Today</p>
                        </div>
                    </div>

                    <!=============================== TODAYS ATTENDANCE TABLE=========================>
                        <div class="bg-card rounded-2xl shadow-sm border border-border overflow-hidden">
                            <div class="px-4 pt-4">
                                <h3 class="font-heading text-base font-bold text-primary">Today's Attendance</h3>
                            </div>
                            <div
                                class="attendance-toolbar p-4 bg-secondary border-b border-border flex justify-between items-center">
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input id="attendance-search" type="text" placeholder="Search Employee"
                                        oninput="filterAttendanceTable()"
                                        class="pl-9 pr-4 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent w-full sm:w-64">
                                </div>
                                <div class="flex space-x-2">
                                    <div class="relative">
                                        <select id="attendance-filter-department" onchange="filterAttendanceTable()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full sm:w-auto">
                                            <option value="">Department</option>
                                            <option value="OPERATIONS">Operations</option>
                                            <option value="MONITORING">Monitoring</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                    <div class="relative">
                                        <select id="attendance-filter-status" onchange="filterAttendanceTable()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full sm:w-auto">
                                            <option value="">Status</option>
                                            <option value="ACTIVE">Active</option>
                                            <option value="LEAVE">Leave</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>
                            <! ----------------- Inside the table ------------->
                                <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                                    <table class="mobile-stack-table w-full text-sm text-left">
                                        <thead class="text-xs font-semibold text-primary uppercase bg-background">
                                            <tr>
                                                <th class="px-5 py-3.5">ID</th>
                                                <th class="px-5 py-3.5">Employee</th>
                                                <th class="px-5 py-3.5">Schedule</th>
                                                <th class="px-5 py-3.5">Clock In</th>
                                                <th class="px-5 py-3.5">Clock Out</th>
                                                <th class="px-5 py-3.5">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border" id="attendance-table-body">
                                            <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                                onclick="openEmployeeProfile(this)" data-id="EMP1"
                                                data-name="EMPLOYEE 1" data-position="TOUR GUIDE"
                                                data-department="OPERATIONS" data-status="ACTIVE">
                                                <td class="px-5 py-4 font-medium text-primary">EMP1</td>
                                                <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 1</td>
                                                <td class="px-5 py-4 text-gray-600">8 - 5 PM</td>
                                                <td class="px-5 py-4 text-gray-600">8:00 AM</td>
                                                <td class="px-5 py-4 text-gray-600">5:00 PM</td>
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span
                                                            class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">ACTIVE</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                                onclick="openEmployeeProfile(this)" data-id="EMP2"
                                                data-name="EMPLOYEE 2" data-position="STAFF"
                                                data-department="OPERATIONS" data-status="LEAVE">
                                                <td class="px-5 py-4 font-medium text-primary">EMP2</td>
                                                <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 2</td>
                                                <td class="px-5 py-4 text-gray-600">N/A</td>
                                                <td class="px-5 py-4 text-gray-600"> -- </td>
                                                <td class="px-5 py-4 text-gray-600"> -- </td>
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span
                                                            class="inline-flex items-center bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full">LEAVE</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                                onclick="openEmployeeProfile(this)" data-id="EMP3"
                                                data-name="EMPLOYEE 3" data-position="MANAGER"
                                                data-department="MONITORING" data-status="ACTIVE">
                                                <td class="px-5 py-4 font-medium text-primary">EMP3</td>
                                                <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 3</td>
                                                <td class="px-5 py-4 text-gray-600">8 - 5 PM</td>
                                                <td class="px-5 py-4 text-gray-600"> 9:00 AM </td>
                                                <td class="px-5 py-4 text-gray-600"> 5:00 PM </td>
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span
                                                            class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">ACTIVE</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                        </div>

                        <!=============================== ATTENDANCE RECORDS / DTR ARCHIVE
                            SECTION=========================>
                            <div class="space-y-6" id="time-attendance-records-wrapper">
                                <div>
                                    <h3 class="font-heading text-lg font-bold text-primary">Attendance Records</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Daily Time Record (DTR) archive — filter and
                                        export historical logs.</p>
                                </div>

                                <!-- Filters -->
                                <div class="flex flex-wrap gap-3">
                                    <div class="relative">
                                        <input id="records-date-range" type="text" placeholder="Date Range"
                                            onchange="filterAttendanceRecords()"
                                            class="pl-4 pr-4 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent w-44">
                                    </div>
                                    <div class="relative">
                                        <select id="records-filter-department" onchange="filterAttendanceRecords()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-44">
                                            <option value="">Department</option>
                                            <option value="OPERATIONS">Operations</option>
                                            <option value="MONITORING">Monitoring</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                    <div class="relative">
                                        <select id="records-filter-status" onchange="filterAttendanceRecords()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-44">
                                            <option value="">Status</option>
                                            <option value="PRESENT">Present</option>
                                            <option value="LATE">Late</option>
                                            <option value="ABSENT">Absent</option>
                                            <option value="LEAVE">Leave</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                    <div class="relative">
                                        <select id="records-filter-employee" onchange="filterAttendanceRecords()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-44">
                                            <option value="">Employee</option>
                                            <option value="EMPLOYEE 1">Employee 1</option>
                                            <option value="EMPLOYEE 2">Employee 2</option>
                                            <option value="EMPLOYEE 3">Employee 3</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                </div>

                                <!-- Records Table -->
                                <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                                    <table class="mobile-stack-table w-full text-sm text-left">
                                        <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                            <tr>
                                                <th class="px-5 py-3.5">Date</th>
                                                <th class="px-5 py-3.5">Employee</th>
                                                <th class="px-5 py-3.5">Schedule</th>
                                                <th class="px-5 py-3.5">Clock In</th>
                                                <th class="px-5 py-3.5">Clock Out</th>
                                                <th class="px-5 py-3.5">Status</th>
                                                <th class="px-5 py-3.5 text-right">Archive</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border" id="attendance-records-table-body">
                                            <tr data-date="2026-08-01" data-employee="EMPLOYEE 1"
                                                data-department="OPERATIONS" data-status="PRESENT">
                                                <td class="px-5 py-4 text-gray-600">Aug 1</td>
                                                <td class="px-5 py-4 font-semibold text-primary">Employee 1</td>
                                                <td class="px-5 py-4 text-gray-600">8-5 PM</td>
                                                <td class="px-5 py-4 text-gray-600">8:00 AM</td>
                                                <td class="px-5 py-4 text-gray-600">5:00 PM</td>
                                                <td class="px-5 py-4">
                                                    <span
                                                        class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">PRESENT</span>
                                                </td>
                                                <td class="px-5 py-4 text-right">
                                                    <button onclick="exportSingleDTR(this)"
                                                        title="Export this DTR entry"
                                                        class="text-gray-400 hover:text-primary">
                                                        <i class="fa-solid fa-download"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr data-date="2026-08-02" data-employee="EMPLOYEE 3"
                                                data-department="MONITORING" data-status="LATE">
                                                <td class="px-5 py-4 text-gray-600">Aug 2</td>
                                                <td class="px-5 py-4 font-semibold text-primary">Employee 3</td>
                                                <td class="px-5 py-4 text-gray-600">8-5 PM</td>
                                                <td class="px-5 py-4 text-gray-600">9:00 AM</td>
                                                <td class="px-5 py-4 text-gray-600">5:00 PM</td>
                                                <td class="px-5 py-4">
                                                    <span
                                                        class="inline-flex items-center bg-purple-100 text-purple-600 text-xs font-semibold px-3 py-1 rounded-full">LATE</span>
                                                </td>
                                                <td class="px-5 py-4 text-right">
                                                    <button onclick="exportSingleDTR(this)"
                                                        title="Export this DTR entry"
                                                        class="text-gray-400 hover:text-primary">
                                                        <i class="fa-solid fa-download"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!=============================== ATTENDANCE CORRECTION SECTION=========================>
                                <div class="space-y-6" id="attendance-correction-wrapper">
                                    <h3 class="font-heading text-lg font-bold text-primary">Attendance Correction</h3>

                                    <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                                        <table class="mobile-stack-table w-full text-sm text-left">
                                            <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                                <tr>
                                                    <th class="px-5 py-3.5">Request</th>
                                                    <th class="px-5 py-3.5">Employee</th>
                                                    <th class="px-5 py-3.5">Date</th>
                                                    <th class="px-5 py-3.5">Reason</th>
                                                    <th class="px-5 py-3.5">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-border" id="attendance-correction-table-body">
                                                <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                                    onclick="openCorrectionModal(this)" data-request="AT-001"
                                                    data-employee="Employee 1" data-date="August 1"
                                                    data-clockin="8:00 AM" data-clockout="--"
                                                    data-requested-clockout="5:00 PM" data-reason="Forgot to clock out."
                                                    data-attachment="" data-status="PENDING">
                                                    <td class="px-5 py-4 font-medium text-primary">AT-001</td>
                                                    <td class="px-5 py-4 font-semibold text-primary">Employee 1</td>
                                                    <td class="px-5 py-4 text-gray-600">Aug 1</td>
                                                    <td class="px-5 py-4 text-gray-600">Forgot Out</td>
                                                    <td class="px-5 py-4">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span
                                                                class="inline-flex items-center bg-amber-100 text-amber-700 text-xs font-semibold px-3 py-1 rounded-full">PENDING</span>
                                                            <span
                                                                class="hidden md:inline-flex items-center gap-1 text-xs font-medium text-accent opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                                <i class="fa-solid fa-eye"></i> Review
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="cursor-default" data-status="APPROVED">
                                                    <td class="px-5 py-4 font-medium text-primary">AT-002</td>
                                                    <td class="px-5 py-4 font-semibold text-primary">Employee 3</td>
                                                    <td class="px-5 py-4 text-gray-600">Aug 1</td>
                                                    <td class="px-5 py-4 text-gray-600">Device Error</td>
                                                    <td class="px-5 py-4">
                                                        <span
                                                            class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">APPROVED</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                </div>

                <!-- ================= SHIFT & SCHEDULE TAB (Master Roster) ================= -->
                <div id="schedule" class="tab-content max-w-7xl mx-auto space-y-6 hidden">
                    <div
                        class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-card p-4 rounded-2xl shadow-sm border border-border mb-4">
                        <div class="flex items-center space-x-4">
                            <button class="font-button p-2 rounded hover:bg-background text-gray-600"><i
                                    class="fa-solid fa-chevron-left"></i></button>
                            <h2 class="font-heading text-lg font-bold text-primary" id="scheduleHeaderTitle">July 26 -
                                Aug 1, 2026</h2>
                            <button class="font-button p-2 rounded hover:bg-background text-gray-600"><i
                                    class="fa-solid fa-chevron-right"></i></button>
                            <button class="font-button p-2 rounded hover:bg-background text-gray-600"
                                id="scheduleViewToggleBtn" onclick="toggleScheduleView()"><i
                                    class="fa-solid fa-calendar-days" id="scheduleViewToggleIcon"></i> <span
                                    id="scheduleViewToggleLabel">Month</span></button>
                        </div>
                        <div class="schedule-toolbar-actions flex space-x-3">
                            <div class="relative">
                                <i
                                    class="fa-solid fa-filter absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                                <select id="scheduleRoleFilter" onchange="filterScheduleByRole()"
                                    class="pl-8 pr-4 py-2 border border-border rounded-2xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none">
                                    <option>All Roles</option>
                                    <option>Tour Guides</option>
                                    <option>Drivers</option>
                                    <option>Office Staff</option>
                                </select>
                            </div>
                            <button onclick="openCreateScheduleModal()"
                                class="font-button bg-secondary text-white px-4 py-2 rounded-2xl text-sm font-medium shadow-sm hover:bg-[#E08A3B]"><i
                                    class="fa-solid fa-plus mr-1"></i> Create Schedule</button>
                        </div>
                    </div>

                    <!-- Horizontal Calendar Table / Roster View -->
                    <div id="weekRosterView">
                        <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                            <table class="w-full text-sm text-left border-collapse min-w-[900px]">
                                <thead>
                                    <tr class="bg-background border-b border-border">
                                        <th class="p-3 w-48 font-semibold text-gray-600 border-r border-border">Employee
                                        </th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-26">Mon 26</th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-27">Tue 27</th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-28">Wed 28</th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-29">Thu 29</th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-30">Fri 30</th>
                                        <th class="p-3 text-center text-gray-500 font-medium bg-background"
                                            data-date="2026-07-31">Sat 31</th>
                                        <th class="p-3 text-center text-gray-500 font-medium bg-background"
                                            data-date="2026-08-01">Sun 1</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <!-- Employee Row -->
                                    <tr data-emp="Employee 1">
                                        <td
                                            class="p-3 border-r border-border bg-card sticky left-0 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                            <div class="flex items-center space-x-2">
                                                <i
                                                    class="fa-solid fa-user w-7 h-7 rounded-full bg-accent/50 text-accent flex items-center justify-center text-xs"></i>
                                                <div>
                                                    <span class="block font-medium text-primary">Employee 1</span>
                                                    <span class="inline-flex text-xs font-semibold text-gray-500">Tour
                                                        Guide</span>
                                                </div>
                                            </div>
                                            <p class="text-[10px] text-gray-400 mt-1">32 Hrs Scheduled</p>
                                        </td>
                                        <td class="p-2 border-r border-border">
                                            <div
                                                class="bg-blue-50 text-blue-700 text-xs p-1.5 rounded border border-blue-100 text-center cursor-pointer hover:bg-blue-100">
                                                <div class="font-bold">09:00 - 17:00</div>
                                                <div class="truncate text-[10px]">HQ / Briefing</div>
                                            </div>
                                        </td>
                                        <td class="p-2 border-r border-border">
                                            <div
                                                class="bg-emerald-50 text-emerald-700 text-xs p-1.5 rounded border border-emerald-100 text-center cursor-pointer hover:bg-emerald-100">
                                                <div class="font-bold">10:00 - 16:00</div>
                                                <div class="truncate text-[10px]">City Walk</div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-2 border-r border-border bg-background/50 flex items-center justify-center h-full">
                                            <span class="text-xs text-gray-400 italic">Off</span>
                                        </td>
                                        <td class="p-2 border-r border-border">
                                            <div
                                                class="bg-emerald-50 text-emerald-700 text-xs p-1.5 rounded border border-emerald-100 text-center cursor-pointer hover:bg-emerald-100">
                                                <div class="font-bold">10:00 - 16:00</div>
                                                <div class="truncate text-[10px]">City Walk</div>
                                            </div>
                                        </td>
                                        <td class="p-2 border-r border-border">
                                            <div
                                                class="bg-purple-50 text-purple-700 text-xs p-1.5 rounded border border-purple-100 text-center cursor-pointer hover:bg-purple-100">
                                                <div class="font-bold">14:00 - 20:00</div>
                                                <div class="truncate text-[10px]">Museum / Dinner</div>
                                            </div>
                                        </td>
                                        <td class="p-2 bg-background border-r border-border"></td>
                                        <td class="p-2 bg-background"></td>
                                    </tr>
                                    <!-- Employee Row -->
                                    <tr data-emp="Employee 3">
                                        <td
                                            class="p-3 border-r border-border bg-card sticky left-0 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                            <div class="flex items-center space-x-2">
                                                <i
                                                    class="fa-solid fa-user w-7 h-7 rounded-full bg-accent/50 text-accent flex items-center justify-center text-xs"></i>
                                                <div>
                                                    <span class="block font-medium text-primary">Employee 3</span>
                                                    <span
                                                        class="inline-flex text-xs font-semibold text-gray-500">Manager</span>
                                                </div>
                                            </div>
                                            <p class="text-[10px] text-gray-400 mt-1">40 Hrs Scheduled</p>
                                        </td>
                                        <td class="p-2 border-r border-border">
                                            <div
                                                class="bg-amber-50 text-amber-700 text-xs p-1.5 rounded border border-amber-100 text-center cursor-pointer hover:bg-amber-100">
                                                <div class="font-bold">08:00 - 16:00</div>
                                                <div class="truncate text-[10px]">Bus Route A</div>
                                            </div>
                                        </td>
                                        <td class="p-2 border-r border-border">
                                            <div
                                                class="bg-amber-50 text-amber-700 text-xs p-1.5 rounded border border-amber-100 text-center cursor-pointer hover:bg-amber-100">
                                                <div class="font-bold">08:00 - 16:00</div>
                                                <div class="truncate text-[10px]">Bus Route B</div>
                                            </div>
                                        </td>
                                        <td class="p-2 border-r border-border">
                                            <div
                                                class="bg-amber-50 text-amber-700 text-xs p-1.5 rounded border border-amber-100 text-center cursor-pointer hover:bg-amber-100">
                                                <div class="font-bold">08:00 - 16:00</div>
                                                <div class="truncate text-[10px]">Bus Route A</div>
                                            </div>
                                        </td>
                                        <td class="p-2 border-r border-border">
                                            <div
                                                class="bg-amber-50 text-amber-700 text-xs p-1.5 rounded border border-amber-100 text-center cursor-pointer hover:bg-amber-100">
                                                <div class="font-bold">08:00 - 16:00</div>
                                                <div class="truncate text-[10px]">Bus Route B</div>
                                            </div>
                                        </td>
                                        <td class="p-2 border-r border-border bg-background/50 text-center">
                                            <span class="text-xs text-gray-400 italic">Off</span>
                                        </td>
                                        <td class="p-2 bg-background border-r border-border">
                                            <div
                                                class="bg-amber-50 text-amber-700 text-xs p-1.5 rounded border border-amber-100 text-center cursor-pointer hover:bg-amber-100">
                                                <div class="font-bold">09:00 - 17:00</div>
                                                <div class="truncate text-[10px]">Weekend Coastal</div>
                                            </div>
                                        </td>
                                        <td class="p-2 bg-background"></td>
                                    </tr>
                                    <!-- Employee Row Leave -->
                                    <tr data-emp="Employee 2">
                                        <td
                                            class="p-3 border-r border-border bg-card sticky left-0 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                            <div class="flex items-center space-x-2">
                                                <i
                                                    class="fa-solid fa-user w-7 h-7 rounded-full bg-accent/50 text-accent flex items-center justify-center text-xs"></i>
                                                <div>
                                                    <span class="block font-medium text-primary">Employee 2</span>
                                                    <span
                                                        class="inline-flex text-xs font-semibold text-gray-500">Staff</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td colspan="5" class="p-2 border-r border-border text-center">
                                            <div
                                                class="bg-red-50 text-red-600 text-xs py-2 rounded border border-red-200 font-medium tracking-widest">
                                                APPROVED VACATION LEAVE
                                            </div>
                                        </td>
                                        <td class="p-2 bg-background border-r border-border"></td>
                                        <td class="p-2 bg-background"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Monthly Calendar Roster View -->
                    <div id="monthRosterView" class="hidden">
                        <div class="bg-card rounded-2xl shadow-sm border border-border overflow-hidden">
                            <div
                                class="month-calendar-grid grid grid-cols-7 bg-secondary text-white text-xs font-semibold uppercase">
                                <div class="p-3 text-center border-r border-white/10">Mon</div>
                                <div class="p-3 text-center border-r border-white/10">Tue</div>
                                <div class="p-3 text-center border-r border-white/10">Wed</div>
                                <div class="p-3 text-center border-r border-white/10">Thu</div>
                                <div class="p-3 text-center border-r border-white/10">Fri</div>
                                <div class="p-3 text-center border-r border-white/10">Sat</div>
                                <div class="p-3 text-center">Sun</div>
                            </div>
                            <div class="month-calendar-grid grid grid-cols-7">
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-27">
                                    <span class="text-xs font-medium text-gray-300">27</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-28">
                                    <span class="text-xs font-medium text-gray-300">28</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-29">
                                    <span class="text-xs font-medium text-gray-300">29</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-30">
                                    <span class="text-xs font-medium text-gray-300">30</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-31">
                                    <span class="text-xs font-medium text-gray-300">31</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-01">
                                    <span class="text-xs font-semibold text-secondary">1</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-02">
                                    <span class="text-xs font-semibold text-primary">2</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-03">
                                    <span class="text-xs font-semibold text-primary">3</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 9-5">Employee 1 · 9-5</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-04">
                                    <span class="text-xs font-semibold text-primary">4</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 10-4">Employee 1 · 10-4</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-05">
                                    <span class="text-xs font-semibold text-primary">5</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-06">
                                    <span class="text-xs font-semibold text-primary">6</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 10-4">Employee 1 · 10-4</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-07">
                                    <span class="text-xs font-semibold text-primary">7</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-purple-50 text-purple-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 2-8">Employee 1 · 2-8</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-08">
                                    <span class="text-xs font-semibold text-primary">8</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 9-5">Employee 3 · 9-5</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-09">
                                    <span class="text-xs font-semibold text-primary">9</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-10">
                                    <span class="text-xs font-semibold text-primary">10</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 · Vacation Leave">Employee 2 · Vacation Leave</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-11">
                                    <span class="text-xs font-semibold text-primary">11</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 · Vacation Leave">Employee 2 · Vacation Leave</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-12">
                                    <span class="text-xs font-semibold text-primary">12</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 · Vacation Leave">Employee 2 · Vacation Leave</div>
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 9-5">Employee 1 · 9-5</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-13">
                                    <span class="text-xs font-semibold text-primary">13</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 · Vacation Leave">Employee 2 · Vacation Leave</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-14">
                                    <span class="text-xs font-semibold text-primary">14</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 · Vacation Leave">Employee 2 · Vacation Leave</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-15">
                                    <span class="text-xs font-semibold text-primary">15</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-16">
                                    <span class="text-xs font-semibold text-primary">16</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-17">
                                    <span class="text-xs font-semibold text-primary">17</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 9-5">Employee 1 · 9-5</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-18">
                                    <span class="text-xs font-semibold text-primary">18</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 10-4">Employee 1 · 10-4</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-19">
                                    <span class="text-xs font-semibold text-primary">19</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-20">
                                    <span class="text-xs font-semibold text-primary">20</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 10-4">Employee 1 · 10-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-21">
                                    <span class="text-xs font-semibold text-primary">21</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-purple-50 text-purple-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 2-8">Employee 1 · 2-8</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-22">
                                    <span class="text-xs font-semibold text-primary">22</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-23">
                                    <span class="text-xs font-semibold text-primary">23</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-24">
                                    <span class="text-xs font-semibold text-primary">24</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 9-5">Employee 1 · 9-5</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-25">
                                    <span class="text-xs font-semibold text-primary">25</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 10-4">Employee 1 · 10-4</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-26">
                                    <span class="text-xs font-semibold text-primary">26</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-27">
                                    <span class="text-xs font-semibold text-primary">27</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 10-4">Employee 1 · 10-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-28">
                                    <span class="text-xs font-semibold text-primary">28</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-purple-50 text-purple-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 2-8">Employee 1 · 2-8</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-29">
                                    <span class="text-xs font-semibold text-primary">29</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-30">
                                    <span class="text-xs font-semibold text-primary">30</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-31">
                                    <span class="text-xs font-semibold text-primary">31</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 · 9-5">Employee 1 · 9-5</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 · 8-4">Employee 3 · 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-01">
                                    <span class="text-xs font-medium text-gray-300">1</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-02">
                                    <span class="text-xs font-medium text-gray-300">2</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-03">
                                    <span class="text-xs font-medium text-gray-300">3</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-04">
                                    <span class="text-xs font-medium text-gray-300">4</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-05">
                                    <span class="text-xs font-medium text-gray-300">5</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-06">
                                    <span class="text-xs font-medium text-gray-300">6</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 mt-4 text-xs text-gray-500">
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-blue-400"></span> Tour / HQ Shift</span>
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> City Walk</span>
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-purple-400"></span> Evening Tour</span>
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Driver / Route</span>
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Leave / Vacation</span>
                        </div>
                    </div>
                </div>

                <!-- ================= TIMESHEET APPROVALS ================= -->
                <div id="timesheet" class="tab-content max-w-7xl mx-auto space-y-6 hidden">
                    <!-- Timesheet Header / Actions -->
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">

                        <div class="flex flex-wrap gap-2 w-full lg:w-auto">
                            <button id="timesheet-history-btn" type="button" onclick="openTimesheetHistory()"
                                class="font-button bg-card border border-border text-primary px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-background shadow-sm transition-all-300 flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left"></i> View History
                            </button>
                            <button id="timesheet-request-correction" type="button"
                                onclick="openTimesheetCorrectionRequests()"
                                class="font-button bg-amber-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium shadow-sm hover:bg-amber-700 transition-all-300 flex items-center gap-2">
                                <i class="fa-solid fa-pen-to-square"></i> Request Correction
                            </button>
                        </div>
                    </div>

                    <!-- Timesheet Summary -->
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4" id="timesheet-summary">
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total
                                        Employees</p>
                                    <p id="ts-summary-employees"
                                        class="font-heading text-2xl font-bold text-primary mt-1">0</p>
                                </div>
                                <div class="p-2.5 bg-blue-50 text-primary rounded-xl"><i class="fa-solid fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending
                                        Approval</p>
                                    <p id="ts-summary-pending"
                                        class="font-heading text-2xl font-bold text-amber-600 mt-1">0</p>
                                </div>
                                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl"><i
                                        class="fa-solid fa-hourglass-half"></i></div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved</p>
                                    <p id="ts-summary-approved"
                                        class="font-heading text-2xl font-bold text-emerald-600 mt-1">0</p>
                                </div>
                                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl"><i
                                        class="fa-solid fa-circle-check"></i></div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rejected</p>
                                    <p id="ts-summary-rejected"
                                        class="font-heading text-2xl font-bold text-red-600 mt-1">0</p>
                                </div>
                                <div class="p-2.5 bg-red-50 text-red-600 rounded-xl"><i
                                        class="fa-solid fa-circle-xmark"></i></div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Regular
                                        Hours</p>
                                    <p id="ts-summary-regular"
                                        class="font-heading text-2xl font-bold text-primary mt-1">0 hrs</p>
                                </div>
                                <div class="p-2.5 bg-slate-100 text-primary rounded-xl"><i
                                        class="fa-solid fa-business-time"></i></div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Overtime
                                        Hours</p>
                                    <p id="ts-summary-overtime"
                                        class="font-heading text-2xl font-bold text-red-600 mt-1">0 hrs</p>
                                </div>
                                <div class="p-2.5 bg-red-50 text-red-600 rounded-xl"><i
                                        class="fa-solid fa-stopwatch"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <p id="timesheet-summary-note">Summary reflects the current timesheet filters.</p>
                        <button type="button" onclick="clearTimesheetFilters()"
                            class="text-primary hover:text-secondary font-semibold">Clear Filters</button>
                    </div>

                    <!-- Search and Filters -->
                    <div class="bg-card p-4 md:p-5 rounded-2xl shadow-sm border border-border">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
                            <div class="relative sm:col-span-2 xl:col-span-1">
                                <i
                                    class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input id="timesheet-search" type="text" placeholder="Search Employee"
                                    oninput="filterTimesheets()"
                                    class="pl-9 pr-4 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent w-full">
                            </div>

                            <div class="relative">
                                <select id="timesheet-filter-department" onchange="filterTimesheets()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full">
                                    <option value="">All Departments</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="relative">
                                <select id="timesheet-filter-period" onchange="filterTimesheets()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full">
                                    <option value="">All Pay Periods</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="relative">
                                <select id="timesheet-filter-status" onchange="filterTimesheets()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full">
                                    <option value="">All Statuses</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                    <option value="Correction Requested">Correction Requested</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="relative">
                                <select id="timesheet-filter-overtime" onchange="filterTimesheets()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full">
                                    <option value="">All Overtime</option>
                                    <option value="with">With Overtime</option>
                                    <option value="high">High Overtime (&gt; 10 hrs)</option>
                                    <option value="none">No Overtime</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Timesheet Cards -->
                    <div id="timesheet-records-container" class="space-y-4"></div>

                    <!-- REVIEW MODAL -->
                    <div id="modal-timesheet-review"
                        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
                        onclick="if(event.target===this) closeTimesheetReview()">
                        <div
                            class="bg-card rounded-3xl shadow-2xl border border-border max-w-6xl w-full max-h-[92vh] overflow-hidden flex flex-col">
                            <div
                                class="px-5 md:px-6 py-5 border-b border-border flex items-start justify-between gap-4 flex-shrink-0">
                                <div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Timesheet
                                        Review</p>
                                    <h3 id="timesheet-review-name"
                                        class="font-heading text-xl font-bold text-primary mt-1">—</h3>
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        <span id="timesheet-review-status"
                                            class="ts-status-badge status-pending">Pending</span>
                                        <span id="timesheet-review-ot-warning"
                                            class="hidden text-xs font-bold text-red-600 bg-red-50 border border-red-100 px-2.5 py-1 rounded-full">
                                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> High Overtime
                                        </span>
                                    </div>
                                </div>
                                <button type="button" onclick="closeTimesheetReview()"
                                    class="p-2 rounded-xl hover:bg-background text-gray-500 transition-colors"
                                    aria-label="Close review">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>

                            <div class="overflow-y-auto p-5 md:p-6 space-y-5">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div class="ts-info-box">
                                        <span>Employee ID</span>
                                        <strong id="timesheet-review-id">—</strong>
                                    </div>
                                    <div class="ts-info-box">
                                        <span>Department</span>
                                        <strong id="timesheet-review-department">—</strong>
                                    </div>
                                    <div class="ts-info-box">
                                        <span>Pay Period</span>
                                        <strong id="timesheet-review-period">—</strong>
                                    </div>
                                    <div class="ts-info-box">
                                        <span>Submitted Date</span>
                                        <strong id="timesheet-review-submitted">—</strong>
                                    </div>
                                </div>

                                <div id="timesheet-review-reason-wrap"
                                    class="hidden p-4 rounded-2xl border border-amber-200 bg-amber-50">
                                    <p class="text-xs font-bold text-amber-800 uppercase tracking-wider">Recorded Reason
                                    </p>
                                    <p id="timesheet-review-reason" class="text-sm text-amber-900 mt-1"></p>
                                </div>

                                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                                    <div class="px-4 py-4 border-b border-border bg-background">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <h4 class="font-heading font-bold text-primary">Daily Timesheet</h4>
                                                <p class="text-xs text-gray-500 mt-0.5">Review individual attendance and
                                                    worked hours for the selected pay period.</p>
                                            </div>
                                            <div class="text-right text-xs">
                                                <p class="text-gray-500">Submitted Totals</p>
                                                <p class="font-bold text-primary"><span
                                                        id="timesheet-review-regular">0</span> regular + <span
                                                        id="timesheet-review-overtime">0</span> OT</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left min-w-[900px]">
                                            <thead
                                                class="text-xs font-semibold text-gray-500 uppercase bg-background border-b border-border">
                                                <tr>
                                                    <th class="px-4 py-3">Date</th>
                                                    <th class="px-4 py-3">Day</th>
                                                    <th class="px-4 py-3">Time In</th>
                                                    <th class="px-4 py-3">Time Out</th>
                                                    <th class="px-4 py-3">Break</th>
                                                    <th class="px-4 py-3 text-right">Regular Hours</th>
                                                    <th class="px-4 py-3 text-right">Overtime</th>
                                                    <th class="px-4 py-3">Attendance Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="timesheet-review-entries" class="divide-y divide-border"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="px-5 md:px-6 py-4 border-t border-border bg-background flex flex-col sm:flex-row justify-end gap-2 flex-shrink-0">
                                <button type="button" onclick="closeTimesheetReview()"
                                    class="font-button px-4 py-2.5 border border-border text-gray-600 rounded-xl text-sm font-medium hover:bg-card">Close</button>
                                <button id="timesheet-review-reject" type="button" onclick="rejectFromReview()"
                                    class="font-button px-4 py-2.5 bg-red-100 text-red-700 rounded-xl text-sm font-semibold hover:bg-red-200">
                                    <i class="fa-solid fa-xmark mr-1"></i> Reject Timesheet
                                </button>
                                <button id="timesheet-review-approve" type="button" onclick="approveFromReview()"
                                    class="font-button px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700">
                                    <i class="fa-solid fa-check mr-1"></i> Approve Timesheet
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- REASON MODAL -->
                    <div id="modal-timesheet-reason"
                        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[80] p-4"
                        onclick="if(event.target===this) closeTimesheetReasonModal()">
                        <div
                            class="bg-card rounded-3xl shadow-2xl border border-border max-w-lg w-full overflow-hidden">
                            <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                                <div>
                                    <p id="timesheet-reason-kicker"
                                        class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Timesheet
                                        Action</p>
                                    <h3 id="timesheet-reason-title"
                                        class="font-heading text-lg font-bold text-primary mt-1">Reason</h3>
                                </div>
                                <button type="button" onclick="closeTimesheetReasonModal()"
                                    class="p-2 rounded-xl hover:bg-background text-gray-500">
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </button>
                            </div>
                            <form id="timesheet-reason-form" onsubmit="submitTimesheetReason(event)"
                                class="p-6 space-y-5">
                                <p id="timesheet-reason-prompt" class="text-sm text-gray-600">Please provide a reason.
                                </p>

                                <div id="timesheet-rejection-options" class="space-y-2">
                                    <label
                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Rejection
                                        Reason</label>
                                    <select id="timesheet-rejection-select" onchange="toggleTimesheetOtherReason()"
                                        class="w-full px-4 py-3 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent">
                                        <option value="">Select a reason</option>
                                        <option value="Incorrect Time Entry">Incorrect Time Entry</option>
                                        <option value="Excessive Overtime">Excessive Overtime</option>
                                        <option value="Missing Attendance Record">Missing Attendance Record</option>
                                        <option value="Incomplete Timesheet">Incomplete Timesheet</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div id="timesheet-other-reason-wrap" class="hidden">
                                    <label for="timesheet-other-reason"
                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Other
                                        Reason</label>
                                    <textarea id="timesheet-other-reason" rows="3" placeholder="Enter the reason..."
                                        class="w-full px-4 py-3 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent resize-none"></textarea>
                                </div>

                                <div id="timesheet-correction-reason-wrap" class="hidden">
                                    <label for="timesheet-correction-reason-input"
                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Correction
                                        Reason</label>
                                    <textarea id="timesheet-correction-reason-input" rows="4"
                                        placeholder="Example: Missing time-out on July 18."
                                        class="w-full px-4 py-3 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent resize-none"></textarea>
                                </div>

                                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
                                    <button type="button" onclick="closeTimesheetReasonModal()"
                                        class="font-button px-4 py-2.5 border border-border text-gray-600 rounded-xl text-sm font-medium hover:bg-background">Cancel</button>
                                    <button id="timesheet-reason-submit" type="submit"
                                        class="font-button px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- HISTORY MODAL -->
                    <div id="modal-timesheet-history"
                        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
                        onclick="if(event.target===this) closeTimesheetHistory()">
                        <div
                            class="bg-card rounded-3xl shadow-2xl border border-border max-w-6xl w-full max-h-[92vh] overflow-hidden flex flex-col">
                            <div class="px-5 md:px-6 py-5 border-b border-border flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Audit Trail
                                    </p>
                                    <h3 class="font-heading text-xl font-bold text-primary mt-1">Timesheet History</h3>
                                </div>
                                <button type="button" onclick="closeTimesheetHistory()"
                                    class="p-2 rounded-xl hover:bg-background text-gray-500">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            <div class="overflow-auto">
                                <table class="w-full text-sm text-left min-w-[900px]">
                                    <thead class="text-xs font-semibold text-white uppercase bg-secondary sticky top-0">
                                        <tr>
                                            <th class="px-5 py-3.5">Employee</th>
                                            <th class="px-5 py-3.5">Action</th>
                                            <th class="px-5 py-3.5">Status Change</th>
                                            <th class="px-5 py-3.5">Admin</th>
                                            <th class="px-5 py-3.5">Date / Time</th>
                                            <th class="px-5 py-3.5">Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody id="timesheet-history-body" class="divide-y divide-border"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- CORRECTION REQUESTS MODAL -->
                    <div id="modal-timesheet-correction-requests"
                        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[75] p-4"
                        onclick="if(event.target===this) closeTimesheetCorrectionRequests()">
                        <div
                            class="bg-card rounded-3xl shadow-2xl border border-border max-w-3xl w-full max-h-[88vh] overflow-hidden flex flex-col">
                            <div
                                class="px-5 md:px-6 py-5 border-b border-border flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Employee
                                        Timesheets</p>
                                    <h3 class="font-heading text-xl font-bold text-primary mt-1">Request Correction</h3>
                                    <p class="text-sm text-gray-500 mt-1">View an employee timesheet before sending a
                                        correction request.</p>
                                </div>
                                <button type="button" onclick="closeTimesheetCorrectionRequests()"
                                    class="p-2 rounded-xl hover:bg-background text-gray-500">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            <div id="timesheet-correction-request-list" class="overflow-y-auto p-5 md:p-6 space-y-3">
                            </div>
                        </div>
                    </div>
                </div>

                <!---------------------------- MODULE 5: LEAVE REGISTRY ---------------------------------->
                <div id="leave" class="tab-content max-w-6xl mx-auto space-y-6 hidden">
                    <div class="flex justify-between items-center flex-wrap gap-4">

                        <button onclick="openRequestLeaveModal()"
                            class="px-5 py-2.5 bg-background border border-border hover:bg-gray-50 text-primary font-button font-medium text-sm rounded-xl shadow-sm flex items-center gap-2 transition-all-300">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-accent"></i>
                            File Leave Request
                        </button>
                    </div>

                    <!-- Leaves interactive cards grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="leaves-grid-container">
                        <!-- Injected dynamically -->
                    </div>
                </div>

                <!-- ================= OVERTIME (submenu of Live Attendance) ================= -->
                <div id="overtime" class="tab-content max-w-6xl mx-auto space-y-6 hidden">
                    <h3 class="font-heading text-xl font-bold text-primary text-center">Overtime Management</h3>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border text-center">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">1</h3>
                        </div>
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border text-center">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">2</h3>
                        </div>
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border text-center">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Hours</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">12 hrs</h3>
                        </div>
                    </div>

                    <!-- Overtime Table -->
                    <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                        <table class="mobile-stack-table w-full text-sm text-left">
                            <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                <tr>
                                    <th class="px-5 py-3.5">Employee</th>
                                    <th class="px-5 py-3.5">Date</th>
                                    <th class="px-5 py-3.5">Hours</th>
                                    <th class="px-5 py-3.5">Reason</th>
                                    <th class="px-5 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border" id="overtime-table-body">
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-primary">Employee 1</td>
                                    <td class="px-5 py-4 text-gray-600">August 2</td>
                                    <td class="px-5 py-4 text-gray-600">2 hours</td>
                                    <td class="px-5 py-4 text-gray-600">Tour Duty</td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center bg-amber-100 text-amber-700 text-xs font-semibold px-3 py-1 rounded-full">PENDING</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- ================= WORKFORCE ANALYTICS ================= -->
                <div id="analytics" class="tab-content max-w-6xl mx-auto space-y-6 hidden">
                    <div class="mb-6 flex justify-between items-center">

                        <select class="bg-card border border-border text-primary text-sm rounded-2xl p-2">
                            <option>Q3 2026 (Current)</option>
                            <option>Q2 2026</option>
                            <option>Year to Date</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Labor Costs Chart -->
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <h3 class="font-semibold text-primary mb-4 text-sm uppercase tracking-wider">Labor Cost vs.
                                Budget</h3>
                            <div class="h-72 relative w-full">
                                <canvas id="costChart"></canvas>
                            </div>
                        </div>

                        <!-- Utilization Chart -->
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <h3 class="font-semibold text-primary mb-4 text-sm uppercase tracking-wider">Guide
                                Utilization by Tour Type</h3>
                            <div class="h-72 relative w-full flex justify-center">
                                <canvas id="utilizationChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <p class="text-sm text-gray-500 font-medium">Total Overtime Hours</p>
                            <h3 class="font-heading text-2xl font-bold text-amber-600 mt-1">142.5 hrs</h3>
                            <p class="text-xs text-red-500 mt-1"><i class="fa-solid fa-arrow-up"></i> 12% over target
                            </p>
                        </div>
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <p class="text-sm text-gray-500 font-medium">Avg. Absenteeism Rate</p>
                            <h3 class="font-heading text-2xl font-bold text-emerald-600 mt-1">2.4%</h3>
                            <p class="text-xs text-emerald-500 mt-1"><i class="fa-solid fa-arrow-down"></i> Healthy (<
                                    3%)</p>
                        </div>
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <p class="text-sm text-gray-500 font-medium">Shift Fulfillment Rate</p>
                            <h3 class="font-heading text-2xl font-bold text-blue-600 mt-1">98.5%</h3>
                            <p class="text-xs text-gray-400 mt-1">Tours properly staffed</p>
                        </div>
                    </div>
                </div>

            </main>
        </div>


        <!-- MODAL: REQUEST LEAVE -->
        <div id="modal-request-leave"
            class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
            <div
                class="bg-card rounded-2xl shadow-2xl border border-border max-w-md w-full overflow-hidden transform transition-all">
                <div class="px-6 py-5 border-b border-border flex justify-between items-center bg-background">
                    <h3 class="font-heading font-bold text-primary text-lg">File Leave Request</h3>
                    <button onclick="closeRequestLeaveModal()"
                        class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form onsubmit="handleRequestLeaveSubmit(event)" class="p-6 space-y-5">
                    <div>
                        <label
                            class="block font-button text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">Applicant
                            Employee</label>
                        <select id="leave-form-emp" required
                            class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div>
                        <label
                            class="block font-button text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">Leave
                            Category</label>
                        <select id="leave-form-type" required
                            class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <option value="Annual Leave">Annual Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Personal Leave">Personal Leave</option>
                            <option value="Comp Off">Comp Off</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block font-button text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">Requested
                            Dates</label>
                        <input type="text" id="leave-form-dates" required placeholder="e.g., Oct 24 - Oct 29"
                            class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary" />
                    </div>
                    <div>
                        <label
                            class="block font-button text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">Reason
                            / Notes</label>
                        <textarea id="leave-form-reason" required rows="3" placeholder="Explain reasoning..."
                            class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary"></textarea>
                    </div>
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-3 bg-secondary hover:bg-[#E08A3B] text-white font-button font-medium rounded-xl shadow-lg shadow-secondary/30 text-sm transition-all-300">
                            File Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: CREATE SCHEDULE -->
        <div id="modal-schedule-day"
            class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
            onclick="if(event.target===this) closeScheduleDayModal()">
            <div
                class="bg-card rounded-2xl border-4 border-secondary shadow-2xl max-w-lg w-full max-h-[85vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Selected Day</p>
                        <h3 id="schedule-day-modal-title" class="font-heading font-bold text-primary text-xl mt-0.5">
                            Schedule Details</h3>
                    </div>
                    <button type="button" onclick="closeScheduleDayModal()"
                        class="w-9 h-9 rounded-full hover:bg-background text-gray-500 flex items-center justify-center transition-colors"
                        aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div id="schedule-day-modal-summary" class="text-sm text-gray-500 mb-4"></div>
                    <div id="schedule-day-modal-content" class="space-y-3 max-h-[55vh] overflow-y-auto pr-1"></div>
                </div>
            </div>
        </div>

        <div id="modal-create-schedule"
            class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity"
            onclick="if(event.target===this) closeCreateScheduleModal()">
            <div
                class="bg-card rounded-[28px] border-4 border-secondary shadow-2xl max-w-sm w-full overflow-hidden transform transition-all">
                <form onsubmit="handleCreateScheduleSubmit(event)" class="p-6 space-y-4">
                    <h3 class="font-heading font-bold text-primary text-lg text-center mb-1">Create Schedule</h3>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Employee</label>
                        <select id="schedule-form-employee" required
                            class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <option>Employee 1</option>
                            <option>Employee 2</option>
                            <option>Employee 3</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Date</label>
                        <div class="flex items-center gap-2">
                            <input type="text" id="schedule-form-date" required value="August 20, 2026"
                                class="flex-1 bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary" />
                            <div class="relative">
                                <button type="button"
                                    onclick="document.getElementById('schedule-form-date-native').showPicker && document.getElementById('schedule-form-date-native').showPicker()"
                                    class="w-[42px] h-[42px] flex items-center justify-center bg-white border border-gray-300 rounded-lg text-gray-500 hover:bg-background transition-colors">
                                    <i class="fa-regular fa-calendar"></i>
                                </button>
                                <input type="date" id="schedule-form-date-native"
                                    onchange="updateScheduleDateDisplay(this)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Shift</label>
                        <select id="schedule-form-shift" required
                            class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <option>Regular</option>
                            <option>Morning</option>
                            <option>Afternoon</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Start
                                Time</label>
                            <input type="text" id="schedule-form-start" required value="8:00 AM"
                                class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-accent text-primary" />
                        </div>
                        <div>
                            <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">End Time</label>
                            <input type="text" id="schedule-form-end" required value="5:00 PM"
                                class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-accent text-primary" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Work Type</label>
                        <select id="schedule-form-worktype" required
                            class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <option>Field Duty</option>
                            <option>Office Work</option>
                            <option>Driving</option>
                            <option>Monitoring</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Remarks</label>
                        <textarea id="schedule-form-remarks" rows="2"
                            class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary resize-none"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeCreateScheduleModal()"
                            class="flex-1 py-3 bg-error hover:bg-red-600 text-white font-button font-semibold rounded-full shadow-md text-sm transition-all-300">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 bg-success hover:bg-emerald-600 text-white font-button font-semibold rounded-full shadow-md text-sm transition-all-300">
                            Save Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- TOAST: View Profile -->
    <div id="view-profile-toast"
        class="hidden fixed z-[60] top-6 left-1/2 -translate-x-1/2 bg-primary text-white text-sm font-medium px-4 py-2.5 rounded-xl shadow-lg flex items-center gap-2 transition-all-300 opacity-0">
        <i class="fa-solid fa-eye text-accent"></i> Viewing Profile
    </div>

    <!-- MODAL: EMPLOYEE PROFILE -->
    <div id="modal-employee-profile"
        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity"
        onclick="if(event.target===this) closeEmployeeProfile()">
        <div
            class="bg-card rounded-2xl shadow-2xl border border-border max-w-md w-full overflow-hidden transform transition-all max-h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="px-6 py-5 border-b border-border flex justify-between items-center bg-background flex-shrink-0">
                <h3 class="font-heading font-bold text-primary text-lg">Employee Profile</h3>
                <button onclick="closeEmployeeProfile()"
                    class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="overflow-y-auto">
                <!-- Identity block -->
                <div class="p-6 border-b border-border flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-user text-2xl"></i>
                    </div>
                    <div>
                        <h4 id="profile-name" class="font-heading font-bold text-primary text-lg leading-tight">Juan
                            Dela Cruz</h4>
                        <p id="profile-id" class="text-xs text-gray-500 mt-0.5">EMP-001</p>
                        <p id="profile-position" class="text-sm text-gray-600 mt-1">Tour Guide</p>
                        <span id="profile-status"
                            class="inline-flex items-center mt-2 bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">ACTIVE</span>
                    </div>
                </div>

                <!-- Info accordion group -->
                <div class="border-b border-border">
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-3.5 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">
                        Personal Information
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>
                    <div class="profile-accordion-panel hidden px-6 pb-4 text-sm text-gray-600 space-y-1">
                        <p><span class="text-gray-400">Date of Birth:</span> —</p>
                        <p><span class="text-gray-400">Gender:</span> —</p>
                        <p><span class="text-gray-400">Address:</span> —</p>
                    </div>

                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-3.5 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">
                        Contact Information
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>
                    <div class="profile-accordion-panel hidden px-6 pb-4 text-sm text-gray-600 space-y-1">
                        <p><span class="text-gray-400">Phone:</span> —</p>
                        <p><span class="text-gray-400">Email:</span> —</p>
                    </div>

                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-3.5 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">
                        Employment Information
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>
                    <div class="profile-accordion-panel hidden px-6 pb-4 text-sm text-gray-600 space-y-1">
                        <p><span class="text-gray-400">Department:</span> <span id="profile-department">—</span></p>
                        <p><span class="text-gray-400">Date Hired:</span> —</p>
                        <p><span class="text-gray-400">Employment Type:</span> —</p>
                    </div>

                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-3.5 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">
                        Emergency Contact
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>
                    <div class="profile-accordion-panel hidden px-6 pb-4 text-sm text-gray-600 space-y-1">
                        <p><span class="text-gray-400">Name:</span> —</p>
                        <p><span class="text-gray-400">Relationship:</span> —</p>
                        <p><span class="text-gray-400">Phone:</span> —</p>
                    </div>
                </div>

                <!-- Records accordion group -->
                <div>
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-3.5 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">
                        Attendance Summary
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>
                    <div class="profile-accordion-panel hidden px-6 pb-4 text-sm text-gray-600 space-y-1">
                        <p><span class="text-gray-400">Days Present (This Month):</span> —</p>
                        <p><span class="text-gray-400">Late Arrivals:</span> —</p>
                    </div>

                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-3.5 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">
                        Schedule
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>
                    <div class="profile-accordion-panel hidden px-6 pb-4 text-sm text-gray-600 space-y-1">
                        <p>No upcoming shifts to display.</p>
                    </div>

                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-3.5 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">
                        Leave Balance
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>
                    <div class="profile-accordion-panel hidden px-6 pb-4 text-sm text-gray-600 space-y-1">
                        <p><span class="text-gray-400">Vacation Leave:</span> —</p>
                        <p><span class="text-gray-400">Sick Leave:</span> —</p>
                    </div>

                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-3.5 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">
                        Timesheet History
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>
                    <div class="profile-accordion-panel hidden px-6 pb-4 text-sm text-gray-600 space-y-1">
                        <p>No timesheet records to display.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ATTENDANCE CORRECTION REVIEW -->
    <div id="modal-correction-review"
        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity"
        onclick="if(event.target===this) closeCorrectionModal()">
        <div
            class="bg-card rounded-3xl shadow-2xl border border-border max-w-md w-full overflow-hidden transform transition-all max-h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="px-6 py-5 flex justify-between items-center flex-shrink-0">
                <h3 class="font-heading font-bold text-primary text-lg mx-auto">Attendance Correction</h3>
                <button onclick="closeCorrectionModal()"
                    class="absolute right-6 p-1.5 rounded-lg hover:bg-gray-200 text-gray-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="overflow-y-auto px-6 pb-6 space-y-5">
                <div class="text-sm text-gray-600 space-y-1">
                    <p><span class="font-semibold text-primary">Employee:</span> <span id="correction-employee">—</span>
                    </p>
                    <p><span class="font-semibold text-primary">Date:</span> <span id="correction-date">—</span></p>
                </div>

                <div>
                    <h4 class="font-heading font-bold text-primary text-sm mb-2">Current Record</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p>Clock In: <span id="correction-current-clockin">—</span></p>
                        <p>Clock Out: <span id="correction-current-clockout">—</span></p>
                    </div>
                </div>

                <div>
                    <h4 class="font-heading font-bold text-primary text-sm mb-2">Requested Correction</h4>
                    <div class="text-sm text-gray-600">
                        <p id="correction-requested">—</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-heading font-bold text-primary text-sm mb-2">Reason:</h4>
                        <div class="border border-border rounded-xl p-3 text-sm text-gray-600 min-h-[70px]"
                            id="correction-reason">—</div>
                    </div>
                    <div>
                        <h4 class="font-heading font-bold text-primary text-sm mb-2">Attachment:</h4>
                        <div class="border border-border rounded-xl p-3 text-sm text-gray-400 min-h-[70px] flex items-center justify-center text-center"
                            id="correction-attachment">No attachment</div>
                    </div>
                </div>

                <div class="flex justify-center gap-10 pt-2">
                    <button onclick="rejectCorrection()"
                        class="flex items-center gap-2 text-red-600 font-semibold text-sm hover:opacity-80 transition-opacity">
                        <i class="fa-solid fa-xmark text-lg"></i> Reject
                    </button>
                    <button onclick="approveCorrection()"
                        class="flex items-center gap-2 text-emerald-600 font-semibold text-sm hover:opacity-80 transition-opacity">
                        <i class="fa-solid fa-check text-lg"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD EMPLOYEE -->
    <div id="modal-add-employee"
        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
        onclick="if(event.target===this) closeAddEmployeeModal()">
        <div class="bg-card rounded-2xl shadow-2xl border border-border max-w-2xl w-full max-h-[92vh] overflow-y-auto">
            <div class="px-6 py-5 border-b border-border flex items-center justify-between bg-background">
                <div>
                    <h3 class="font-heading font-bold text-primary text-lg">Add Employee</h3>
                    <p class="text-xs text-gray-500 mt-1">Create a new employee record in MySQL.</p>
                </div>
                <button type="button" onclick="closeAddEmployeeModal()"
                    class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="add-employee-form" onsubmit="handleAddEmployeeSubmit(event)" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Employee ID *</label>
                        <input id="add-employee-no" required maxlength="30"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Hire Date</label>
                        <input id="add-hire-date" type="date"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">First Name *</label>
                        <input id="add-first-name" required maxlength="80"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Middle Name</label>
                        <input id="add-middle-name" maxlength="80"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Last Name *</label>
                        <input id="add-last-name" required maxlength="80"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone</label>
                        <input id="add-phone" maxlength="30"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Department *</label>
                        <input id="add-department" required maxlength="100" placeholder="e.g. OPERATIONS"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Position *</label>
                        <input id="add-position" required maxlength="100" placeholder="e.g. TOUR GUIDE"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Employment Type</label>
                        <select id="add-employment-type"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="REGULAR">Regular</option>
                            <option value="FULL_TIME">Full Time</option>
                            <option value="PART_TIME">Part Time</option>
                            <option value="CONTRACT">Contract</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                        <select id="add-employment-status"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="ACTIVE">Active</option>
                            <option value="LEAVE">Leave</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Address</label>
                    <textarea id="add-address" rows="2" maxlength="255"
                        class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent"></textarea>
                </div>

                <div id="add-employee-message" class="hidden text-sm rounded-xl px-4 py-3"></div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeAddEmployeeModal()"
                        class="flex-1 py-3 bg-error hover:bg-red-600 text-white font-button font-semibold rounded-xl">Cancel</button>
                    <button id="add-employee-submit" type="submit"
                        class="flex-1 py-3 bg-secondary hover:bg-[#E08A3B] text-white font-button font-semibold rounded-xl">Save
                        Employee</button>
                </div>
            </form>
        </div>
    </div>

    <script src="scripts/shared-data.js"></script>
    <script>
            (function () {
                function applySharedAdminData() {
                    const session = SharedData && SharedData.getSession ? SharedData.getSession() : null;
                    const record = session && SharedData.findEmployeeByEmail ? SharedData.findEmployeeByEmail(session.email) : null;
                    const displayName = (session && session.name) || (record && record.name) || 'Admin';
                    document.querySelectorAll('[data-session="name"]').forEach(el => {
                        el.textContent = displayName;
                    });
                    document.querySelectorAll('[data-session="role"]').forEach(el => {
                        const roleText = (session && session.role) ? session.role.charAt(0).toUpperCase() + session.role.slice(1) : 'Admin';
                        el.textContent = roleText;
                    });
                    document.querySelectorAll('[data-session="email"]').forEach(el => {
                        if (session && session.email) {
                            el.textContent = session.email;
                        }
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', applySharedAdminData, { once: true });
                } else {
                    applySharedAdminData();
                }
            })();
    </script>
    <script>SharedData.requireRole(['admin']);</script>
    <script src="scripts/script.js"></script>
    <script src="scripts/admin-mobile.js"></script>
    <script>
        /* ================= TIMESHEET APPROVALS MODULE ================= */
        (function () {
            'use strict';

            const ADMIN_NAME = 'Admin Account';
            const HIGH_OVERTIME_THRESHOLD = 10;
            let currentReviewId = null;
            let currentReasonId = null;
            let currentReasonAction = null;

            const makeDailyEntry = (date, timeIn, timeOut, breakTime, regularHours, overtime, attendanceStatus) => ({
                date,
                day: new Date(`${date}T12:00:00`).toLocaleDateString('en-US', { weekday: 'short' }),
                timeIn,
                timeOut,
                break: breakTime,
                regularHours,
                overtime,
                attendanceStatus
            });

            const createTimesheet = (data) => ({
                id: data.id,
                avatar: data.avatar || '',
                employee: data.employee,
                employeeId: data.employeeId,
                department: data.department,
                period: data.period,
                regularHours: Number(data.regularHours),
                overtimeHours: Number(data.overtimeHours),
                totalHours: Number((Number(data.regularHours) + Number(data.overtimeHours)).toFixed(2)),
                status: data.status,
                submittedDate: data.submittedDate,
                rejectionReason: data.rejectionReason || '',
                correctionReason: data.correctionReason || '',
                dailyEntries: data.dailyEntries || []
            });

            const TIMESHEET_DATA = [
                createTimesheet({
                    id: 'TS-2026-0716-01',
                    avatar: 'https://i.pravatar.cc/150?img=47',
                    employee: 'Sarah Jenkins',
                    employeeId: 'EMP-1001',
                    department: 'Operations',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 76.5,
                    overtimeHours: 2,
                    status: 'Pending',
                    submittedDate: 'September 19, 2026',
                    rejectionReason: '',
                    correctionReason: '',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:02 AM', '5:02 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-20', '8:03 AM', '5:03 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-21', '8:05 AM', '4:35 PM', '1 hr', 7.5, 0, 'Present'),
                        makeDailyEntry('2026-07-22', '8:08 AM', '4:38 PM', '1 hr', 7.5, 0, 'Late'),
                        makeDailyEntry('2026-07-23', '8:01 AM', '5:01 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-24', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-27', '8:12 AM', '4:57 PM', '1 hr', 7.75, 0, 'Late'),
                        makeDailyEntry('2026-07-28', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-29', '9:10 AM', '6:10 PM', '1 hr', 3.75, 2, 'Late')
                    ]
                }),
                createTimesheet({
                    id: 'TS-2026-0716-02',
                    avatar: 'https://i.pravatar.cc/150?img=15',
                    employee: 'Michael Chang',
                    employeeId: 'EMP-1002',
                    department: 'Monitoring',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 80,
                    overtimeHours: 12.5,
                    status: 'Pending',
                    submittedDate: 'September 19, 2026',
                    rejectionReason: '',
                    correctionReason: '',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:00 AM', '7:30 PM', '1 hr', 8, 2.5, 'Present'),
                        makeDailyEntry('2026-07-20', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-21', '8:00 AM', '8:00 PM', '1 hr', 8, 3, 'Present'),
                        makeDailyEntry('2026-07-22', '8:00 AM', '6:30 PM', '1 hr', 8, 1.5, 'Present'),
                        makeDailyEntry('2026-07-23', '8:00 AM', '7:00 PM', '1 hr', 8, 2, 'Present'),
                        makeDailyEntry('2026-07-24', '8:05 AM', '5:05 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-27', '8:00 AM', '6:30 PM', '1 hr', 8, 1.5, 'Present'),
                        makeDailyEntry('2026-07-28', '8:00 AM', '7:00 PM', '1 hr', 8, 2, 'Present'),
                        makeDailyEntry('2026-07-29', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present')
                    ]
                }),
                createTimesheet({
                    id: 'TS-2026-0716-03',
                    avatar: 'https://i.pravatar.cc/150?img=12',
                    employee: 'John Smith',
                    employeeId: 'EMP-1003',
                    department: 'Operations',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 74.25,
                    overtimeHours: 4.5,
                    status: 'Approved',
                    submittedDate: 'September 18, 2026',
                    rejectionReason: '',
                    correctionReason: '',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:05 AM', '5:05 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-20', '8:10 AM', '4:55 PM', '1 hr', 7.75, 0, 'Late'),
                        makeDailyEntry('2026-07-21', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-22', '8:00 AM', '4:30 PM', '1 hr', 7.5, 0, 'Present'),
                        makeDailyEntry('2026-07-23', '8:00 AM', '6:30 PM', '1 hr', 8, 1.5, 'Present'),
                        makeDailyEntry('2026-07-24', '8:00 AM', '6:00 PM', '1 hr', 8, 1, 'Present'),
                        makeDailyEntry('2026-07-27', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-28', '8:00 AM', '6:00 PM', '1 hr', 7.5, 2, 'Present'),
                        makeDailyEntry('2026-07-29', '8:00 AM', '11:30 AM', '0.5 hr', 3.5, 0, 'Present')
                    ]
                }),
                createTimesheet({
                    id: 'TS-2026-0716-04',
                    avatar: 'https://i.pravatar.cc/150?img=44',
                    employee: 'Maria Santos',
                    employeeId: 'EMP-1004',
                    department: 'Guest Services',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 77,
                    overtimeHours: 0,
                    status: 'Rejected',
                    submittedDate: 'September 18, 2026',
                    rejectionReason: 'Incorrect Time Entry',
                    correctionReason: '',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:15 AM', '4:45 PM', '1 hr', 7.5, 0, 'Late'),
                        makeDailyEntry('2026-07-20', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-21', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-22', '8:30 AM', '5:00 PM', '1 hr', 7.5, 0, 'Late'),
                        makeDailyEntry('2026-07-23', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-24', '8:00 AM', '4:30 PM', '1 hr', 7.5, 0, 'Present'),
                        makeDailyEntry('2026-07-27', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-28', '8:10 AM', '4:40 PM', '1 hr', 7.5, 0, 'Late'),
                        makeDailyEntry('2026-07-29', '8:00 AM', '4:00 PM', '1 hr', 7, 0, 'Present')
                    ]
                }),
                createTimesheet({
                    id: 'TS-2026-0716-05',
                    avatar: 'https://i.pravatar.cc/150?img=33',
                    employee: 'David Wilson',
                    employeeId: 'EMP-1005',
                    department: 'Finance',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 72.5,
                    overtimeHours: 8,
                    status: 'Correction Requested',
                    submittedDate: 'September 19, 2026',
                    rejectionReason: '',
                    correctionReason: 'Missing attendance record for July 24.',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:00 AM', '5:30 PM', '1 hr', 7.5, 0.5, 'Present'),
                        makeDailyEntry('2026-07-20', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-21', '8:10 AM', '4:40 PM', '1 hr', 7, 0, 'Late'),
                        makeDailyEntry('2026-07-22', '8:00 AM', '6:30 PM', '1 hr', 7.5, 1.5, 'Present'),
                        makeDailyEntry('2026-07-23', '8:00 AM', '7:00 PM', '1 hr', 8, 2, 'Present'),
                        makeDailyEntry('2026-07-24', '—', '—', '—', 0, 0, 'Missing Record'),
                        makeDailyEntry('2026-07-27', '8:00 AM', '6:00 PM', '1 hr', 7, 2, 'Present'),
                        makeDailyEntry('2026-07-28', '8:00 AM', '5:00 PM', '1 hr', 7.5, 0, 'Present'),
                        makeDailyEntry('2026-07-29', '8:00 AM', '5:00 PM', '1 hr', 7, 2, 'Present')
                    ]
                })
            ];

            const TIMESHEET_HISTORY = [
                {
                    employee: 'David Wilson',
                    action: 'Correction Requested',
                    previousStatus: 'Pending',
                    newStatus: 'Correction Requested',
                    admin: ADMIN_NAME,
                    timestamp: 'September 20, 2026 - 5:25 PM',
                    reason: 'Missing attendance record for July 24.'
                },
                {
                    employee: 'Maria Santos',
                    action: 'Timesheet Rejected',
                    previousStatus: 'Pending',
                    newStatus: 'Rejected',
                    admin: ADMIN_NAME,
                    timestamp: 'September 20, 2026 - 5:20 PM',
                    reason: 'Incorrect Time Entry'
                },
                {
                    employee: 'John Smith',
                    action: 'Timesheet Approved',
                    previousStatus: 'Pending',
                    newStatus: 'Approved',
                    admin: ADMIN_NAME,
                    timestamp: 'September 20, 2026 - 5:15 PM',
                    reason: 'Verified and complete.'
                }
            ];

            const escapeHtml = (value) => {
                const div = document.createElement('div');
                div.textContent = value == null ? '' : String(value);
                return div.innerHTML;
            };

            const formatHours = (value) => Number(value).toFixed(2).replace(/\.00$/, '').replace(/(\.\d)0$/, '$1');

            const formatSubmittedDate = (value) => value || '—';

            const statusClass = (status) => {
                if (status === 'Approved') return 'status-approved';
                if (status === 'Rejected') return 'status-rejected';
                if (status === 'Correction Requested') return 'status-correction';
                return 'status-pending';
            };

            const statusIcon = (status) => {
                if (status === 'Approved') return 'fa-check';
                if (status === 'Rejected') return 'fa-xmark';
                if (status === 'Correction Requested') return 'fa-pen';
                return 'fa-clock';
            };

            const getRecordById = (id) => TIMESHEET_DATA.find(record => record.id === id);

            function populateTimesheetFilters() {
                const departmentEl = document.getElementById('timesheet-filter-department');
                const periodEl = document.getElementById('timesheet-filter-period');
                if (!departmentEl || !periodEl) return;

                const departments = [...new Set(TIMESHEET_DATA.map(record => record.department))].sort();
                const periods = [...new Set(TIMESHEET_DATA.map(record => record.period))];

                departmentEl.innerHTML = '<option value="">All Departments</option>' +
                    departments.map(department => `<option value="${escapeHtml(department)}">${escapeHtml(department)}</option>`).join('');

                periodEl.innerHTML = '<option value="">All Pay Periods</option>' +
                    periods.map(period => `<option value="${escapeHtml(period)}">${escapeHtml(period)}</option>`).join('');
            }

            function getFilteredTimesheets() {
                const search = (document.getElementById('timesheet-search')?.value || '').trim().toLowerCase();
                const department = document.getElementById('timesheet-filter-department')?.value || '';
                const period = document.getElementById('timesheet-filter-period')?.value || '';
                const status = document.getElementById('timesheet-filter-status')?.value || '';
                const overtime = document.getElementById('timesheet-filter-overtime')?.value || '';

                return TIMESHEET_DATA.filter(record => {
                    const matchesSearch =
                        !search ||
                        record.employee.toLowerCase().includes(search) ||
                        record.employeeId.toLowerCase().includes(search);

                    const matchesDepartment = !department || record.department === department;
                    const matchesPeriod = !period || record.period === period;
                    const matchesStatus = !status || record.status === status;

                    let matchesOvertime = true;
                    if (overtime === 'with') matchesOvertime = record.overtimeHours > 0;
                    if (overtime === 'high') matchesOvertime = record.overtimeHours > HIGH_OVERTIME_THRESHOLD;
                    if (overtime === 'none') matchesOvertime = record.overtimeHours === 0;

                    return matchesSearch && matchesDepartment && matchesPeriod && matchesStatus && matchesOvertime;
                });
            }

            function updateTimesheetSummary(records) {
                const setText = (id, value) => {
                    const element = document.getElementById(id);
                    if (element) element.textContent = value;
                };

                const pending = records.filter(record => record.status === 'Pending').length;
                const approved = records.filter(record => record.status === 'Approved').length;
                const rejected = records.filter(record => record.status === 'Rejected').length;
                const regular = records.reduce((sum, record) => sum + record.regularHours, 0);
                const overtime = records.reduce((sum, record) => sum + record.overtimeHours, 0);

                setText('ts-summary-employees', records.length);
                setText('ts-summary-pending', pending);
                setText('ts-summary-approved', approved);
                setText('ts-summary-rejected', rejected);
                setText('ts-summary-regular', `${formatHours(regular)} hrs`);
                setText('ts-summary-overtime', `${formatHours(overtime)} hrs`);

                const note = document.getElementById('timesheet-summary-note');
                if (note) {
                    note.textContent = records.length === TIMESHEET_DATA.length
                        ? 'Summary reflects all timesheet records.'
                        : `Summary reflects ${records.length} filtered timesheet record${records.length === 1 ? '' : 's'}.`;
                }
            }

            function renderTimesheetRecords(records = getFilteredTimesheets()) {
                const container = document.getElementById('timesheet-records-container');
                if (!container) return;

                updateTimesheetSummary(records);

                if (!records.length) {
                    container.innerHTML = `
                <div class="bg-card p-10 rounded-2xl shadow-sm border border-border text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-background text-gray-400 flex items-center justify-center">
                        <i class="fa-solid fa-file-circle-xmark text-2xl"></i>
                    </div>
                    <h3 class="font-heading font-bold text-primary mt-4">No timesheets found</h3>
                    <p class="text-sm text-gray-500 mt-1">Try changing the search or filters.</p>
                </div>`;
                    updateTimesheetCorrectionButton(records);
                    return;
                }

                container.innerHTML = records.map(record => {
                    const highOvertime = record.overtimeHours > HIGH_OVERTIME_THRESHOLD;
                    const canApprove = record.status !== 'Approved' && record.status !== 'Rejected';
                    const canReject = record.status !== 'Rejected' && record.status !== 'Approved';
                    const canCorrection = record.status !== 'Rejected' && record.status !== 'Approved';

                    const reason = record.status === 'Rejected'
                        ? record.rejectionReason
                        : record.status === 'Correction Requested'
                            ? record.correctionReason
                            : '';

                    return `
                <article class="ts-record-card bg-card p-5 rounded-2xl shadow-sm border border-border ${highOvertime ? 'ts-high-overtime' : ''}">
                    ${highOvertime ? '<div class="ts-high-ot-bar"></div>' : ''}
                    <div class="flex flex-col gap-5">
                        <div class="grid grid-cols-1 xl:grid-cols-[minmax(240px,1.4fr)_minmax(410px,1fr)_auto] items-center gap-5">
                            <div class="flex items-start gap-4 min-w-0">
                                <img src="${escapeHtml(record.avatar || 'https://i.pravatar.cc/150?img=32')}" alt="${escapeHtml(record.employee)}" class="w-12 h-12 rounded-full object-cover border border-border flex-shrink-0">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-bold text-primary text-lg">${escapeHtml(record.employee)}</h4>
                                        <span class="ts-status-badge ${statusClass(record.status)}"><i class="fa-solid ${statusIcon(record.status)} text-[9px]"></i>${escapeHtml(record.status)}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">${escapeHtml(record.employeeId)} • ${escapeHtml(record.department)}</p>
                                    <p class="text-xs text-gray-500 mt-1">Pay Period: <span class="font-medium text-gray-600">${escapeHtml(record.period)}</span></p>
                                </div>
                            </div>

                            <div class="ts-stat-grid grid grid-cols-3 gap-3 text-center">
                                <div class="px-3 py-2 rounded-xl bg-background">
                                    <p class="text-[11px] text-gray-500 uppercase font-semibold">Regular</p>
                                    <p class="font-bold text-primary mt-1">${formatHours(record.regularHours)} hrs</p>
                                </div>
                                <div class="px-3 py-2 rounded-xl ${highOvertime ? 'bg-red-50' : 'bg-amber-50'}">
                                    <p class="text-[11px] ${highOvertime ? 'text-red-600' : 'text-amber-600'} uppercase font-semibold">Overtime</p>
                                    <p class="mt-1 ${highOvertime ? 'ts-high-ot-value' : 'font-bold text-amber-600'}">
                                        ${formatHours(record.overtimeHours)} hrs
                                        ${highOvertime ? '<i class="fa-solid fa-triangle-exclamation" title="Exceeds overtime threshold"></i>' : ''}
                                    </p>
                                    ${highOvertime ? '<span class="ts-high-ot-label"><i class="fa-solid fa-triangle-exclamation"></i> High Overtime</span>' : ''}
                                </div>
                                <div class="px-3 py-2 rounded-xl bg-background">
                                    <p class="text-[11px] text-gray-500 uppercase font-semibold">Total</p>
                                    <p class="font-heading font-bold text-primary text-lg mt-0.5">${formatHours(record.totalHours)} hrs</p>
                                </div>
                            </div>

                            <div class="ts-actions flex flex-wrap xl:flex-col gap-2 xl:min-w-[150px]">
                                <button type="button" onclick="openTimesheetReview('${record.id}')" class="ts-action-btn flex-1 xl:w-full px-3 py-2 border border-border text-gray-600 rounded-xl hover:bg-background text-sm font-medium">
                                    <i class="fa-solid fa-eye mr-1"></i> Review
                                </button>
                                <div class="flex flex-1 gap-2">
                                    <button type="button" onclick="approveTimesheet('${record.id}')" ${canApprove ? '' : 'disabled'} class="ts-action-btn flex-1 px-3 py-2 bg-emerald-100 text-emerald-700 rounded-xl font-semibold hover:bg-emerald-200 text-sm">
                                        <i class="fa-solid fa-check mr-1"></i> Approve
                                    </button>
                                    <button type="button" onclick="openTimesheetRejectModal('${record.id}')" ${canReject ? '' : 'disabled'} class="ts-action-btn flex-1 px-3 py-2 bg-red-100 text-red-700 rounded-xl font-semibold hover:bg-red-200 text-sm">
                                        <i class="fa-solid fa-xmark mr-1"></i> Reject
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 pt-3 border-t border-border text-xs">
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-gray-500">
                                <span><i class="fa-solid fa-calendar-day mr-1 text-gray-400"></i> Submitted: <strong class="text-gray-600">${escapeHtml(formatSubmittedDate(record.submittedDate))}</strong></span>
                                ${reason ? `<span class="${record.status === 'Rejected' ? 'text-red-600' : 'text-amber-700'}"><i class="fa-solid fa-comment-dots mr-1"></i> ${escapeHtml(reason)}</span>` : ''}
                            </div>
                            <span class="text-gray-400">Record ID: ${escapeHtml(record.id)}</span>
                        </div>
                    </div>
                </article>
            `;
                }).join('');

                updateTimesheetCorrectionButton(records);
            }

            function updateTimesheetCorrectionButton(records = getFilteredTimesheets()) {
                const button = document.getElementById('timesheet-request-correction');
                if (!button) return;

                const visiblePending = records.filter(record => record.status === 'Pending').length;
                button.disabled = visiblePending === 0;
                button.innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Request Correction${visiblePending ? ` (${visiblePending})` : ''}`;
                button.classList.toggle('opacity-50', visiblePending === 0);
                button.classList.toggle('cursor-not-allowed', visiblePending === 0);
            }

            function filterTimesheets() {
                renderTimesheetRecords(getFilteredTimesheets());
            }

            function openTimesheetCorrectionRequests() {
                const modal = document.getElementById('modal-timesheet-correction-requests');
                const list = document.getElementById('timesheet-correction-request-list');
                if (!modal || !list) return;

                const records = getFilteredTimesheets();
                const pending = records.filter(record => record.status === 'Pending');
                const requested = records.filter(record => record.status === 'Correction Requested');

                if (!pending.length && !requested.length) {
                    list.innerHTML = `
                <div class="bg-background border border-border rounded-2xl p-8 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-check text-2xl"></i>
                    </div>
                    <h4 class="font-heading font-bold text-primary mt-4">No employee timesheets available</h4>
                    <p class="text-sm text-gray-500 mt-1">There are no pending or correction-requested timesheets in the current view.</p>
                </div>`;
                } else {
                    const section = (title, items, isPending) => {
                        if (!items.length) return '';
                        return `
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="font-heading font-bold text-primary">${title}</h4>
                            <span class="text-xs font-semibold text-gray-400">${items.length} record${items.length === 1 ? '' : 's'}</span>
                        </div>
                        ${items.map(record => {
                            const reason = record.status === 'Correction Requested' ? record.correctionReason : 'No correction request has been sent yet.';
                            return `
                                <div class="bg-background border border-border rounded-2xl p-4 md:p-5">
                                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h5 class="font-bold text-primary">${escapeHtml(record.employee)}</h5>
                                                <span class="ts-status-badge ${statusClass(record.status)}">${escapeHtml(record.status)}</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">${escapeHtml(record.employeeId)} • ${escapeHtml(record.department)}</p>
                                            <p class="text-xs text-gray-500 mt-1">Pay Period: <span class="font-medium text-gray-600">${escapeHtml(record.period)}</span></p>
                                            <div class="mt-3 p-3 rounded-xl border ${isPending ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50'}">
                                                <p class="text-xs font-bold ${isPending ? 'text-amber-800' : 'text-emerald-800'} uppercase tracking-wider">${isPending ? 'Employee Timesheet' : 'Correction Request'}</p>
                                                <p class="text-sm ${isPending ? 'text-amber-900' : 'text-emerald-900'} mt-1">${escapeHtml(reason)}</p>
                                            </div>
                                        </div>
                                        <button type="button" onclick="viewTimesheetFromCorrectionRequests('${record.id}')" class="font-button shrink-0 px-4 py-2.5 ${isPending ? 'bg-amber-600 hover:bg-amber-700' : 'bg-primary hover:bg-primary/90'} text-white rounded-xl text-sm font-semibold">
                                            <i class="fa-solid ${isPending ? 'fa-eye' : 'fa-file-lines'} mr-1"></i> View Employee Request
                                        </button>
                                    </div>
                                </div>`;
                        }).join('')}
                    </div>`;
                    };

                    list.innerHTML = section('Pending Timesheets', pending, true) + section('Correction Requested', requested, false);
                }

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                if (window.lucide) lucide.createIcons();
            }

            function viewTimesheetFromCorrectionRequests(id) {
                closeTimesheetCorrectionRequests();
                openTimesheetReview(id);
            }

            function closeTimesheetCorrectionRequests() {
                document.getElementById('modal-timesheet-correction-requests')?.classList.add('hidden');
                if (document.getElementById('modal-timesheet-review')?.classList.contains('hidden') &&
                    document.getElementById('modal-timesheet-history')?.classList.contains('hidden') &&
                    document.getElementById('modal-timesheet-reason')?.classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function clearTimesheetFilters() {
                const ids = [
                    'timesheet-search',
                    'timesheet-filter-department',
                    'timesheet-filter-period',
                    'timesheet-filter-status',
                    'timesheet-filter-overtime'
                ];

                ids.forEach(id => {
                    const element = document.getElementById(id);
                    if (element) element.value = '';
                });

                filterTimesheets();
            }

            function addHistory(record, previousStatus, newStatus, action, reason = '') {
                TIMESHEET_HISTORY.unshift({
                    employee: record.employee,
                    action,
                    previousStatus,
                    newStatus,
                    admin: ADMIN_NAME,
                    timestamp: new Date().toLocaleString('en-US', {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit'
                    }),
                    reason: reason || ''
                });
            }

            function changeTimesheetStatus(id, newStatus, reason = '') {
                const record = getRecordById(id);
                if (!record) return false;

                const previousStatus = record.status;
                if (previousStatus === newStatus) return false;

                record.status = newStatus;
                if (newStatus === 'Rejected') {
                    record.rejectionReason = reason;
                    record.correctionReason = '';
                } else if (newStatus === 'Correction Requested') {
                    record.correctionReason = reason;
                    record.rejectionReason = '';
                } else if (newStatus === 'Approved') {
                    record.rejectionReason = '';
                    record.correctionReason = '';
                }

                const actionLabel = newStatus === 'Approved'
                    ? 'Timesheet Approved'
                    : newStatus === 'Rejected'
                        ? 'Timesheet Rejected'
                        : 'Correction Requested';

                addHistory(record, previousStatus, newStatus, actionLabel, reason);
                return true;
            }

            function approveTimesheet(id) {
                const record = getRecordById(id);
                if (!record || record.status === 'Approved' || record.status === 'Rejected') return;

                if (changeTimesheetStatus(id, 'Approved', 'Verified and complete.')) {
                    renderTimesheetRecords(getFilteredTimesheets());
                    showTimesheetToast('Timesheet approved successfully.', 'success');
                }
            }

            function approveAllTimesheets() {
                const visiblePending = getFilteredTimesheets().filter(record => record.status === 'Pending');

                if (!visiblePending.length) {
                    showTimesheetToast('No visible pending timesheets to approve.', 'info');
                    return;
                }

                visiblePending.forEach(record => {
                    changeTimesheetStatus(record.id, 'Approved', 'Bulk approval by admin.');
                });

                renderTimesheetRecords(getFilteredTimesheets());
                showTimesheetToast('All pending timesheets have been approved.', 'success');
            }

            function openTimesheetReview(id) {
                const record = getRecordById(id);
                if (!record) return;

                currentReviewId = id;

                const setText = (elementId, value) => {
                    const element = document.getElementById(elementId);
                    if (element) element.textContent = value;
                };

                setText('timesheet-review-name', record.employee);
                setText('timesheet-review-id', record.employeeId);
                setText('timesheet-review-department', record.department);
                setText('timesheet-review-period', record.period);
                setText('timesheet-review-submitted', record.submittedDate);
                setText('timesheet-review-regular', `${formatHours(record.regularHours)} hrs`);
                setText('timesheet-review-overtime', `${formatHours(record.overtimeHours)} hrs`);

                const statusEl = document.getElementById('timesheet-review-status');
                statusEl.textContent = record.status;
                statusEl.className = `ts-status-badge ${statusClass(record.status)}`;

                const warning = document.getElementById('timesheet-review-ot-warning');
                warning.classList.toggle('hidden', record.overtimeHours <= HIGH_OVERTIME_THRESHOLD);

                const reasonWrap = document.getElementById('timesheet-review-reason-wrap');
                const reasonEl = document.getElementById('timesheet-review-reason');
                const reason = record.status === 'Rejected' ? record.rejectionReason : record.correctionReason;
                if (reason) {
                    reasonWrap.classList.remove('hidden');
                    reasonEl.textContent = reason;
                } else {
                    reasonWrap.classList.add('hidden');
                    reasonEl.textContent = '';
                }

                const tbody = document.getElementById('timesheet-review-entries');
                tbody.innerHTML = record.dailyEntries.map(entry => `
            <tr class="hover:bg-background">
                <td class="px-4 py-3 font-medium text-primary">${escapeHtml(formatDailyDate(entry.date))}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(entry.day)}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(entry.timeIn)}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(entry.timeOut)}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(entry.break)}</td>
                <td class="px-4 py-3 text-right font-semibold text-primary">${formatHours(entry.regularHours)} hrs</td>
                <td class="px-4 py-3 text-right font-semibold ${entry.overtime > HIGH_OVERTIME_THRESHOLD ? 'text-red-600' : 'text-amber-600'}">
                    ${formatHours(entry.overtime)} hrs
                    ${entry.overtime > 0 ? '<i class="fa-solid fa-clock-rotate-left ml-1"></i>' : ''}
                </td>
                <td class="px-4 py-3">${attendanceBadge(entry.attendanceStatus)}</td>
            </tr>
        `).join('');

                const approveBtn = document.getElementById('timesheet-review-approve');
                const rejectBtn = document.getElementById('timesheet-review-reject');
                const correctionBtn = document.getElementById('timesheet-review-correction');

                const locked = record.status === 'Approved' || record.status === 'Rejected';
                [approveBtn, rejectBtn, correctionBtn].filter(Boolean).forEach(button => {
                    button.disabled = locked;
                    button.classList.toggle('opacity-50', button.disabled);
                    button.classList.toggle('cursor-not-allowed', button.disabled);
                });

                document.getElementById('modal-timesheet-review').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                if (window.lucide) lucide.createIcons();
            }

            function formatDailyDate(isoDate) {
                const date = new Date(`${isoDate}T12:00:00`);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }

            function attendanceBadge(status) {
                const map = {
                    'Present': 'bg-emerald-100 text-emerald-700',
                    'Late': 'bg-amber-100 text-amber-700',
                    'Missing Record': 'bg-red-100 text-red-700',
                    'Absent': 'bg-red-100 text-red-700',
                    'Leave': 'bg-purple-100 text-purple-700'
                };
                const cls = map[status] || 'bg-gray-100 text-gray-600';
                return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold ${cls}">${escapeHtml(status)}</span>`;
            }

            function closeTimesheetReview() {
                document.getElementById('modal-timesheet-review')?.classList.add('hidden');
                currentReviewId = null;
                if (!document.getElementById('modal-timesheet-reason')?.classList.contains('hidden')) return;
                if (!document.getElementById('modal-timesheet-history') || document.getElementById('modal-timesheet-history').classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function approveFromReview() {
                if (!currentReviewId) return;
                const id = currentReviewId;
                approveTimesheet(id);
                closeTimesheetReview();
            }

            function rejectFromReview() {
                if (!currentReviewId) return;
                const id = currentReviewId;
                closeTimesheetReview();
                openTimesheetRejectModal(id);
            }

            function requestCorrectionFromReview() {
                if (!currentReviewId) return;
                const id = currentReviewId;
                closeTimesheetReview();
                openTimesheetCorrectionModal(id);
            }

            function openTimesheetRejectModal(id) {
                const record = getRecordById(id);
                if (!record) return;

                currentReasonId = id;
                currentReasonAction = 'reject';

                document.getElementById('timesheet-reason-kicker').textContent = 'Reject Timesheet';
                document.getElementById('timesheet-reason-title').textContent = `Reject ${record.employee}'s Timesheet`;
                document.getElementById('timesheet-reason-prompt').textContent = 'Please provide a reason for rejecting this timesheet.';
                document.getElementById('timesheet-rejection-options').classList.remove('hidden');
                document.getElementById('timesheet-correction-reason-wrap').classList.add('hidden');
                document.getElementById('timesheet-other-reason-wrap').classList.add('hidden');
                document.getElementById('timesheet-rejection-select').value = '';
                document.getElementById('timesheet-other-reason').value = '';
                document.getElementById('timesheet-reason-submit').className = 'font-button px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700';
                document.getElementById('timesheet-reason-submit').innerHTML = '<i class="fa-solid fa-xmark mr-1"></i> Reject Timesheet';

                document.getElementById('modal-timesheet-reason').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function openTimesheetCorrectionModal(id) {
                const record = getRecordById(id);
                if (!record) return;

                currentReasonId = id;
                currentReasonAction = 'correction';

                document.getElementById('timesheet-reason-kicker').textContent = 'Request Correction';
                document.getElementById('timesheet-reason-title').textContent = `Request Correction for ${record.employee}`;
                document.getElementById('timesheet-reason-prompt').textContent = 'Enter the correction reason that should be sent back to the employee.';
                document.getElementById('timesheet-rejection-options').classList.add('hidden');
                document.getElementById('timesheet-other-reason-wrap').classList.add('hidden');
                document.getElementById('timesheet-correction-reason-wrap').classList.remove('hidden');
                document.getElementById('timesheet-correction-reason-input').value = record.correctionReason || '';
                document.getElementById('timesheet-reason-submit').className = 'font-button px-4 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700';
                document.getElementById('timesheet-reason-submit').innerHTML = '<i class="fa-solid fa-paper-plane mr-1"></i> Request Correction';

                document.getElementById('modal-timesheet-reason').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function toggleTimesheetOtherReason() {
                const select = document.getElementById('timesheet-rejection-select');
                const wrap = document.getElementById('timesheet-other-reason-wrap');
                if (!select || !wrap) return;
                wrap.classList.toggle('hidden', select.value !== 'Other');
                if (select.value !== 'Other') {
                    document.getElementById('timesheet-other-reason').value = '';
                }
            }

            function submitTimesheetReason(event) {
                event.preventDefault();
                if (!currentReasonId || !currentReasonAction) return;

                let reason = '';

                if (currentReasonAction === 'reject') {
                    const select = document.getElementById('timesheet-rejection-select');
                    if (!select.value) {
                        showTimesheetToast('Please select a rejection reason.', 'error');
                        return;
                    }

                    if (select.value === 'Other') {
                        reason = document.getElementById('timesheet-other-reason').value.trim();
                        if (!reason) {
                            showTimesheetToast('Please enter the reason for Other.', 'error');
                            return;
                        }
                    } else {
                        reason = select.value;
                    }

                    if (changeTimesheetStatus(currentReasonId, 'Rejected', reason)) {
                        closeTimesheetReasonModal();
                        renderTimesheetRecords(getFilteredTimesheets());
                        showTimesheetToast('Timesheet rejected successfully.', 'success');
                    }
                } else if (currentReasonAction === 'correction') {
                    reason = document.getElementById('timesheet-correction-reason-input').value.trim();
                    if (!reason) {
                        showTimesheetToast('Please enter a correction reason.', 'error');
                        return;
                    }

                    if (changeTimesheetStatus(currentReasonId, 'Correction Requested', reason)) {
                        closeTimesheetReasonModal();
                        renderTimesheetRecords(getFilteredTimesheets());
                        showTimesheetToast('Correction request sent successfully.', 'success');
                    }
                }
            }

            function closeTimesheetReasonModal() {
                document.getElementById('modal-timesheet-reason')?.classList.add('hidden');
                currentReasonId = null;
                currentReasonAction = null;

                if (document.getElementById('modal-timesheet-review')?.classList.contains('hidden') &&
                    document.getElementById('modal-timesheet-history')?.classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function openTimesheetHistory() {
                renderTimesheetHistory();
                document.getElementById('modal-timesheet-history').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                if (window.lucide) lucide.createIcons();
            }

            function renderTimesheetHistory() {
                const body = document.getElementById('timesheet-history-body');
                if (!body) return;

                if (!TIMESHEET_HISTORY.length) {
                    body.innerHTML = '<tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No timesheet history available.</td></tr>';
                    return;
                }

                body.innerHTML = TIMESHEET_HISTORY.map(log => `
            <tr class="hover:bg-background">
                <td class="px-5 py-4 font-semibold text-primary">${escapeHtml(log.employee)}</td>
                <td class="px-5 py-4 text-gray-600">${escapeHtml(log.action)}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-semibold text-gray-500">${escapeHtml(log.previousStatus)}</span>
                        <i class="fa-solid fa-arrow-right text-gray-400 text-xs"></i>
                        <span class="ts-status-badge ${statusClass(log.newStatus)}">${escapeHtml(log.newStatus)}</span>
                    </div>
                </td>
                <td class="px-5 py-4 text-gray-600">${escapeHtml(log.admin)}</td>
                <td class="px-5 py-4 text-gray-600 whitespace-nowrap">${escapeHtml(log.timestamp)}</td>
                <td class="px-5 py-4 text-gray-600">${escapeHtml(log.reason || '—')}</td>
            </tr>
        `).join('');
            }

            function closeTimesheetHistory() {
                document.getElementById('modal-timesheet-history')?.classList.add('hidden');

                if (document.getElementById('modal-timesheet-review')?.classList.contains('hidden') &&
                    document.getElementById('modal-timesheet-reason')?.classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function showTimesheetToast(message, type = 'info') {
                const containerId = 'timesheet-toast-container';
                let container = document.getElementById(containerId);

                if (!container) {
                    container = document.createElement('div');
                    container.id = containerId;
                    container.className = 'fixed bottom-6 right-6 z-[120] flex flex-col gap-3 pointer-events-none';
                    document.body.appendChild(container);
                }

                const toast = document.createElement('div');

                const styles = {
                    success: { bg: 'bg-emerald-600', icon: 'fa-circle-check' },
                    error: { bg: 'bg-red-600', icon: 'fa-triangle-exclamation' },
                    info: { bg: 'bg-primary', icon: 'fa-circle-info' }
                };
                const style = styles[type] || styles.info;

                toast.className = `${style.bg} text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-2 text-sm font-semibold translate-y-2 opacity-0 transition-all duration-300 pointer-events-auto max-w-sm`;
                toast.innerHTML = `<i class="fa-solid ${style.icon}"></i><span>${escapeHtml(message)}</span>`;
                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-2', 'opacity-0');
                });

                setTimeout(() => {
                    toast.classList.add('opacity-0', '-translate-y-2');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            function initTimesheets() {
                populateTimesheetFilters();
                renderTimesheetRecords(TIMESHEET_DATA);
            }

            window.filterTimesheets = filterTimesheets;
            window.clearTimesheetFilters = clearTimesheetFilters;
            window.openTimesheetReview = openTimesheetReview;
            window.closeTimesheetReview = closeTimesheetReview;
            window.approveTimesheet = approveTimesheet;
            window.approveAllTimesheets = approveAllTimesheets;
            window.openTimesheetRejectModal = openTimesheetRejectModal;
            window.openTimesheetCorrectionModal = openTimesheetCorrectionModal;
            window.toggleTimesheetOtherReason = toggleTimesheetOtherReason;
            window.submitTimesheetReason = submitTimesheetReason;
            window.closeTimesheetReasonModal = closeTimesheetReasonModal;
            window.openTimesheetHistory = openTimesheetHistory;
            window.closeTimesheetHistory = closeTimesheetHistory;
            window.openTimesheetCorrectionRequests = openTimesheetCorrectionRequests;
            window.viewTimesheetFromCorrectionRequests = viewTimesheetFromCorrectionRequests;
            window.closeTimesheetCorrectionRequests = closeTimesheetCorrectionRequests;
            window.approveFromReview = approveFromReview;
            window.rejectFromReview = rejectFromReview;
            window.requestCorrectionFromReview = requestCorrectionFromReview;

            document.addEventListener('DOMContentLoaded', initTimesheets);

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;

                if (!document.getElementById('modal-timesheet-reason')?.classList.contains('hidden')) {
                    closeTimesheetReasonModal();
                    return;
                }
                if (!document.getElementById('modal-timesheet-history')?.classList.contains('hidden')) {
                    closeTimesheetHistory();
                    return;
                }
                if (!document.getElementById('modal-timesheet-correction-requests')?.classList.contains('hidden')) {
                    closeTimesheetCorrectionRequests();
                    return;
                }
                if (!document.getElementById('modal-timesheet-review')?.classList.contains('hidden')) {
                    closeTimesheetReview();
                }
            });
        })();
    </script>


</body>

</html>