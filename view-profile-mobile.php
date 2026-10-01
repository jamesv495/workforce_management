<?php
require_once __DIR__ . '/includes/page_bootstrap.php';
$wfmPageUser = wfmRequirePageRole(null);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holiday Travelers - Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;700&display=swap"
        rel="stylesheet">
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
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
            animation: fadeIn .25s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tile {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
        }

        .profile-tab {
            border-bottom: 2px solid transparent;
        }

        .profile-tab.active {
            border-bottom-color: #163B6D;
            color: #163B6D;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            opacity: 0;
            pointer-events: none;
            transition: all .25s ease-in-out;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>

<body class="bg-background text-primary font-body antialiased">

    <!-- Top bar -->
    <header class="sticky top-0 z-30 bg-card/95 backdrop-blur border-b border-border">
        <div class="h-14 px-3 grid grid-cols-[2.25rem_1fr_2.25rem] items-center">
            <a href="employee.php" class="text-gray-500 p-2 -ml-2 justify-self-start">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h1 class="font-heading text-sm font-bold text-primary truncate text-center" id="profile-username">@employee
            </h1>
            <div></div>
        </div>
    </header>

    <main class="pb-10">

        <!-- ============ Profile header ============ -->
        <section class="px-4 pt-5">
            <div class="flex items-center gap-5">
                <div id="avatar-wrap"
                    class="h-20 w-20 rounded-full border-2 border-border overflow-hidden flex items-center justify-center bg-accent/15 flex-shrink-0">
                    <img id="avatar-img" src="assets/images/profile.png" alt="Profile photo"
                        class="h-full w-full object-cover">
                    <span id="avatar-fallback" class="hidden font-heading text-xl font-bold text-primary"></span>
                </div>

                <div class="flex-1 grid grid-cols-3 text-center">
                    <div>
                        <p class="text-base font-bold text-primary">156</p>
                        <p class="text-[11px] text-gray-500">Tours</p>
                    </div>
                    <div>
                        <p class="text-base font-bold text-primary">96.4%</p>
                        <p class="text-[11px] text-gray-500">Attendance</p>
                    </div>
                    <div>
                        <p class="text-base font-bold text-primary">3</p>
                        <p class="text-[11px] text-gray-500">Years</p>
                    </div>
                </div>
            </div>

            <!-- Bio -->
            <div class="mt-3 text-sm leading-relaxed">
                <p class="font-bold text-primary" data-session="name">Employee Name</p>
                <p class="font-semibold text-primary" id="profile-position">Tour Guide</p>
                <p class="text-gray-500" id="profile-department">Operations Department</p>
                <p class="text-gray-600 mt-1">Showing guests the best of every city, one tour at a time. Fluent in
                    English &amp; Spanish.</p>
                <p class="text-accent font-medium mt-1">
                    <i class="fa-solid fa-location-dot text-xs mr-1"></i>Manila, Philippines
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2 mt-3">
                <button type="button" id="edit-profile-btn"
                    class="flex-1 py-2 rounded-lg bg-background border border-border text-sm font-semibold font-button">
                    Edit Profile
                </button>
                <button type="button" id="share-profile-btn"
                    class="flex-1 py-2 rounded-lg bg-background border border-border text-sm font-semibold font-button">
                    Share Profile
                </button>
            </div>
        </section>

        <!-- ============ Highlights ============ -->
        <section class="mt-5 px-4 flex items-start gap-5 overflow-x-auto no-scrollbar pb-1">
            <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                <div
                    class="h-14 w-14 rounded-full bg-secondary/15 text-secondary flex items-center justify-center text-lg border border-border">
                    <i class="fa-solid fa-star"></i>
                </div>
                <span class="text-[10px] font-medium text-gray-500">5-Star</span>
            </div>
            <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                <div
                    class="h-14 w-14 rounded-full bg-success/15 text-success flex items-center justify-center text-lg border border-border">
                    <i class="fa-solid fa-certificate"></i>
                </div>
                <span class="text-[10px] font-medium text-gray-500">Certified</span>
            </div>
            <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                <div
                    class="h-14 w-14 rounded-full bg-accent/15 text-accent flex items-center justify-center text-lg border border-border">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <span class="text-[10px] font-medium text-gray-500">Punctual</span>
            </div>
            <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                <div
                    class="h-14 w-14 rounded-full bg-primary/10 text-primary flex items-center justify-center text-lg border border-border">
                    <i class="fa-solid fa-people-group"></i>
                </div>
                <span class="text-[10px] font-medium text-gray-500">Team</span>
            </div>
        </section>

        <!-- ============ Tabs ============ -->
        <nav class="mt-5 border-t border-border flex items-center justify-around text-sm text-gray-400">
            <button type="button" class="profile-tab active flex-1 flex items-center justify-center py-3"
                data-tab="overview">
                <i class="fa-solid fa-table-cells-large"></i>
            </button>
            <button type="button" class="profile-tab flex-1 flex items-center justify-center py-3" data-tab="documents">
                <i class="fa-regular fa-bookmark"></i>
            </button>
            <button type="button" class="profile-tab flex-1 flex items-center justify-center py-3" data-tab="reviews">
                <i class="fa-regular fa-star"></i>
            </button>
        </nav>

        <!-- ============ Overview grid ============ -->
        <div id="overview" class="tab-panel active">
            <div class="grid grid-cols-3 gap-0.5">
                <div class="tile bg-gradient-to-br from-primary to-accent">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-1">
                        <i class="fa-solid fa-umbrella-beach text-lg mb-1"></i>
                        <p class="text-[9px] font-semibold leading-tight">Bali Sunset Tour</p>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-secondary to-primary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-1">
                        <i class="fa-solid fa-star text-lg mb-1"></i>
                        <p class="text-[9px] font-semibold leading-tight">5-Star Review</p>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-accent to-secondary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-1">
                        <i class="fa-solid fa-people-group text-lg mb-1"></i>
                        <p class="text-[9px] font-semibold leading-tight">Team Building</p>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-primary to-secondary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-1">
                        <i class="fa-solid fa-certificate text-lg mb-1"></i>
                        <p class="text-[9px] font-semibold leading-tight">First Aid Cert.</p>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-secondary to-accent">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-1">
                        <i class="fa-solid fa-trophy text-lg mb-1"></i>
                        <p class="text-[9px] font-semibold leading-tight">Employee of Month</p>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-accent to-primary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-1">
                        <i class="fa-solid fa-road text-lg mb-1"></i>
                        <p class="text-[9px] font-semibold leading-tight">City Walk</p>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-primary to-accent">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-1">
                        <i class="fa-solid fa-mountain text-lg mb-1"></i>
                        <p class="text-[9px] font-semibold leading-tight">Mountain Trek</p>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-secondary to-primary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-1">
                        <i class="fa-solid fa-calendar-check text-lg mb-1"></i>
                        <p class="text-[9px] font-semibold leading-tight">Perfect Attendance</p>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-accent to-secondary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-1">
                        <i class="fa-solid fa-handshake text-lg mb-1"></i>
                        <p class="text-[9px] font-semibold leading-tight">Guest Thanks</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ Documents ============ -->
        <div id="documents" class="tab-panel px-4 pt-4 space-y-3">
            <div class="bg-card border border-border rounded-xl p-3.5 flex items-center gap-3">
                <div
                    class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-file-contract"></i></div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary truncate">Employment Contract</p>
                    <p class="text-xs text-gray-400">Signed Jan 12, 2023</p>
                </div>
                <button class="ml-auto text-gray-400"><i class="fa-solid fa-download"></i></button>
            </div>
            <div class="bg-card border border-border rounded-xl p-3.5 flex items-center gap-3">
                <div
                    class="h-10 w-10 rounded-full bg-accent/15 text-accent flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-id-card"></i></div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary truncate">Company ID Badge</p>
                    <p class="text-xs text-gray-400">Expires Dec 2027</p>
                </div>
                <button class="ml-auto text-gray-400"><i class="fa-solid fa-download"></i></button>
            </div>
            <div class="bg-card border border-border rounded-xl p-3.5 flex items-center gap-3">
                <div
                    class="h-10 w-10 rounded-full bg-success/15 text-success flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-certificate"></i></div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary truncate">Tour Guide License</p>
                    <p class="text-xs text-gray-400">Renewed Mar 2026</p>
                </div>
                <button class="ml-auto text-gray-400"><i class="fa-solid fa-download"></i></button>
            </div>
            <div class="bg-card border border-border rounded-xl p-3.5 flex items-center gap-3">
                <div
                    class="h-10 w-10 rounded-full bg-secondary/15 text-secondary flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-notes-medical"></i></div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary truncate">First Aid Certification</p>
                    <p class="text-xs text-gray-400">Valid until Nov 2026</p>
                </div>
                <button class="ml-auto text-gray-400"><i class="fa-solid fa-download"></i></button>
            </div>
        </div>

        <!-- ============ Reviews ============ -->
        <div id="reviews" class="tab-panel px-4 pt-4 space-y-3">
            <div class="bg-card border border-border rounded-xl p-3.5">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-sm font-semibold text-primary">Manager — Operations</p>
                    <span class="text-warning text-xs"><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
                </div>
                <p class="text-sm text-gray-600">Consistently reliable and great with guests. Handles last-minute
                    itinerary changes calmly.</p>
                <p class="text-xs text-gray-400 mt-2">Q2 2026 Performance Review</p>
            </div>
            <div class="bg-card border border-border rounded-xl p-3.5">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-sm font-semibold text-primary">Guest Feedback</p>
                    <span class="text-warning text-xs"><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></span>
                </div>
                <p class="text-sm text-gray-600">Very knowledgeable about local history and kept the group engaged the
                    whole trip.</p>
                <p class="text-xs text-gray-400 mt-2">Bali Sunset Tour, Aug 14</p>
            </div>
        </div>

    </main>

    <div id="toast" class="toast bg-primary text-white text-sm font-medium px-4 py-2.5 rounded-xl shadow-lg">
        Profile link copied
    </div>


    <script src="scripts/shared-data.js"></script>
    <script>
            (function () {
                function applySharedProfileData() {
                    const session = SharedData && SharedData.getSession ? SharedData.getSession() : null;
                    const record = session && SharedData.findEmployeeByEmail ? SharedData.findEmployeeByEmail(session.email) : null;
                    const displayName = (session && session.name) || (record && record.name) || 'Employee Name';

                    document.querySelectorAll('[data-session="name"]').forEach(el => {
                        el.textContent = displayName;
                    });
                    document.querySelectorAll('[data-session="role"]').forEach(el => {
                        const roleText = (session && session.role) ? session.role.charAt(0).toUpperCase() + session.role.slice(1) : 'Employee';
                        el.textContent = roleText;
                    });

                    const username = document.getElementById('profile-username');
                    if (username) {
                        username.textContent = '@' + displayName.toLowerCase().replace(/[^a-z0-9]+/g, '');
                    }

                    if (record) {
                        const position = document.getElementById('profile-position');
                        const department = document.getElementById('profile-department');
                        if (position) position.textContent = record.position || position.textContent;
                        if (department) department.textContent = (record.department || 'Operations') + ' Department';

                        const img = document.getElementById('avatar-img');
                        const fallback = document.getElementById('avatar-fallback');
                        const wrap = document.getElementById('avatar-wrap');
                        if (img && !img.dataset.bound) {
                            img.addEventListener('error', () => {
                                img.style.display = 'none';
                                fallback.textContent = record.initials || '';
                                fallback.classList.remove('hidden');
                                if (wrap) wrap.style.backgroundColor = (record.color || '#6FA9E6') + '26';
                            }, { once: true });
                            img.dataset.bound = 'true';
                        }
                    }
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', applySharedProfileData, { once: true });
                } else {
                    applySharedProfileData();
                }

                // ---- Tabs ----
                const tabs = document.querySelectorAll('.profile-tab');
                const panels = document.querySelectorAll('.tab-panel');
                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => t.classList.remove('active'));
                        panels.forEach(p => p.classList.remove('active'));
                        tab.classList.add('active');
                        document.getElementById(tab.dataset.tab).classList.add('active');
                    });
                });

                // ---- Edit / Share actions ----
                document.getElementById('edit-profile-btn').addEventListener('click', () => {
                    window.location.href = 'settings.php';
                });

                const toast = document.getElementById('toast');
                document.getElementById('share-profile-btn').addEventListener('click', () => {
                    const url = window.location.href;
                    if (navigator.clipboard) navigator.clipboard.writeText(url).catch(() => { });
                    toast.classList.add('show');
                    setTimeout(() => toast.classList.remove('show'), 2000);
                });
            })();
    </script>
</body>

</html>