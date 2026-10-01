<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="description" content="Holiday Travels Incorporation — self-service check-in kiosk.">
    <meta name="theme-color" content="#163B6D">
    <title>Holiday Travels Kiosk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
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

    <style type="text/tailwindcss">
        @layer utilities {
            /* Custom utility for the scanning laser line */
            .scan-line {
                background: linear-gradient(to bottom, transparent, theme('colors.secondary'), transparent);
                animation: scan 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            }
            
            /* Subtle floating animation for the main card */
            .animate-float {
                animation: float 6s ease-in-out infinite;
            }
            
            /* Ripple effect for the idle state */
            .ripple {
                position: absolute;
                border-radius: 50%;
                border: 2px solid theme('colors.accent');
                animation: ripple-anim 2s linear infinite;
                opacity: 0;
            }
        }

        @keyframes scan {
            0% { top: -10%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 110%; opacity: 0; }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        @keyframes ripple-anim {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        /* Hide elements dynamically */
        .hidden-state {
            opacity: 0;
            pointer-events: none;
            transform: scale(0.95);
            transition: all 0.3s ease-out;
        }
        
        .visible-state {
            opacity: 1;
            pointer-events: auto;
            transform: scale(1);
            transition: all 0.3s ease-out;
        }
    </style>
</head>

<body
    class="bg-background text-primary font-body min-h-screen w-full flex flex-col items-center justify-center relative overflow-x-hidden overflow-y-auto select-none">

    <!-- Abstract shapes to give a modern travel vibe without being distracting -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute -top-[20%] -right-[10%] w-[70vw] h-[70vw] rounded-full bg-accent/10 blur-3xl"></div>
        <div class="absolute -bottom-[20%] -left-[10%] w-[60vw] h-[60vw] rounded-full bg-secondary/10 blur-3xl"></div>
    </div>

    <main
        class="w-full max-w-[800px] h-full max-h-[1200px] flex flex-col items-center justify-between py-6 px-4 sm:py-8 sm:px-6 lg:py-12 lg:px-8 z-10 gap-10 sm:gap-0">

        <!-- Top Section: Welcome Card -->
        <div class="w-full flex justify-center">
            <div
                class="bg-card w-full max-w-[600px] min-h-[270px] sm:min-h-[300px] lg:min-h-[320px] h-auto rounded-[2rem] sm:rounded-[2.5rem] lg:rounded-[3rem] shadow-2xl shadow-primary/10 border border-border/50 px-5 py-6 sm:px-8 sm:py-7 lg:px-10 lg:py-8 text-center flex flex-col items-center justify-start gap-3 sm:gap-4 lg:gap-5">

                <!-- Holiday Travelers Inc. Logo -->
                <img src="assets/images/holiday-logo.jpg" alt="Holiday Travelers Inc. Logo"
                    class="w-44 sm:w-52 lg:w-56 max-w-full h-16 sm:h-20 lg:h-24 object-cover object-center rounded-md shrink-0"
                    draggable="false" onerror="this.style.display='none'">

                <h1
                    class="font-heading text-3xl sm:text-4xl lg:text-[2.75rem] tracking-tight font-bold text-primary leading-none">
                    WELCOME
                </h1>

                <div class="w-12 sm:w-14 lg:w-16 h-1 bg-secondary rounded-full"></div>

                <h2
                    class="font-heading text-3xl sm:text-4xl lg:text-[2.75rem] text-primary/80 font-bold leading-none tracking-tight uppercase">
                    HOLIDAY TRAVELERS INCORPORATION.
                </h2>
            </div>
        </div>

        <!-- Bottom Section: Interactive Scanner Area -->
        <div class="w-full flex flex-col items-center mt-0 sm:mt-8 lg:mt-16 flex-grow justify-center">

            <!-- Dynamic Status Text -->
            <div class="h-10 sm:h-12 mb-4 sm:mb-6 lg:mb-8 flex items-center justify-center px-4">
                <p id="statusText" aria-live="polite" aria-atomic="true"
                    class="font-heading text-xl sm:text-2xl lg:text-3xl font-semibold text-primary transition-colors duration-300 text-center">
                    Please Tap Here
                </p>
            </div>

            <!-- Interactive Scanner Container -->
            <div id="scannerArea"
                class="relative group cursor-pointer rounded-full outline-none focus-visible:ring-4 focus-visible:ring-accent/50"
                role="button" tabindex="0" aria-label="Tap your RFID card here to check in" onclick="simulateScan()"
                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault(); simulateScan();}">

                <!-- Background Ripple Effect (Idle State) -->
                <div id="idleRipple"
                    class="absolute inset-0 m-auto w-32 h-32 sm:w-40 sm:h-40 lg:w-48 lg:h-48 visible-state">
                    <div class="ripple" style="animation-delay: 0s; width: 100%; height: 100%;"></div>
                    <div class="ripple" style="animation-delay: 1s; width: 100%; height: 100%;"></div>
                </div>

                <!-- Scanner Outer Borders (Corner brackets) -->
                <div
                    class="relative w-40 h-40 sm:w-52 sm:h-52 lg:w-64 lg:h-64 flex items-center justify-center p-3 sm:p-4 lg:p-6">
                    <!-- Top Left -->
                    <svg class="absolute top-0 left-0 w-10 h-10 sm:w-12 sm:h-12 lg:w-16 lg:h-16 text-primary transition-colors duration-300 scanner-border group-hover:text-accent group-focus-visible:text-accent"
                        viewBox="0 0 64 64" fill="none">
                        <path d="M0 64V0H64" stroke="currentColor" stroke-width="4" />
                        <path d="M12 52V12H52" stroke="currentColor" stroke-width="4" />
                    </svg>
                    <!-- Top Right -->
                    <svg class="absolute top-0 right-0 w-10 h-10 sm:w-12 sm:h-12 lg:w-16 lg:h-16 text-primary transition-colors duration-300 scanner-border group-hover:text-accent group-focus-visible:text-accent"
                        viewBox="0 0 64 64" fill="none" transform="scale(-1, 1)">
                        <path d="M0 64V0H64" stroke="currentColor" stroke-width="4" />
                        <path d="M12 52V12H52" stroke="currentColor" stroke-width="4" />
                    </svg>
                    <!-- Bottom Left -->
                    <svg class="absolute bottom-0 left-0 w-10 h-10 sm:w-12 sm:h-12 lg:w-16 lg:h-16 text-primary transition-colors duration-300 scanner-border group-hover:text-accent group-focus-visible:text-accent"
                        viewBox="0 0 64 64" fill="none" transform="scale(1, -1)">
                        <path d="M0 64V0H64" stroke="currentColor" stroke-width="4" />
                        <path d="M12 52V12H52" stroke="currentColor" stroke-width="4" />
                    </svg>
                    <!-- Bottom Right -->
                    <svg class="absolute bottom-0 right-0 w-10 h-10 sm:w-12 sm:h-12 lg:w-16 lg:h-16 text-primary transition-colors duration-300 scanner-border group-hover:text-accent group-focus-visible:text-accent"
                        viewBox="0 0 64 64" fill="none" transform="scale(-1, -1)">
                        <path d="M0 64V0H64" stroke="currentColor" stroke-width="4" />
                        <path d="M12 52V12H52" stroke="currentColor" stroke-width="4" />
                    </svg>

                    <!-- Scanning Area (Contains Card and Animations) -->
                    <div
                        class="relative w-full h-full rounded-xl overflow-hidden flex items-center justify-center bg-card shadow-inner border border-border/30">

                        <!-- ID Card Icon (matches wireframe) -->
                        <svg id="idCardIcon"
                            class="w-20 sm:w-24 lg:w-32 h-auto text-primary transition-all duration-300"
                            viewBox="0 0 100 70" fill="none" stroke="currentColor" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="5" width="90" height="60" rx="4" />
                            <rect x="15" y="15" width="25" height="30" rx="2" />
                            <!-- Person Avatar inside card -->
                            <circle cx="27.5" cy="24" r="5" />
                            <path d="M18 41c0-4 4-7 9.5-7s9.5 3 9.5 7" />
                            <!-- Text lines inside card -->
                            <line x1="50" y1="20" x2="80" y2="20" />
                            <line x1="50" y1="30" x2="70" y2="30" />
                            <line x1="50" y1="40" x2="85" y2="40" />
                            <!-- Small box bottom right -->
                            <rect x="70" y="50" width="15" height="8" rx="1" />
                            <circle cx="20" cy="54" r="1" fill="currentColor" stroke="none" />
                            <circle cx="35" cy="54" r="1" fill="currentColor" stroke="none" />
                        </svg>

                        <!-- Scanning Laser Line (Hidden by default) -->
                        <div id="scanLaser" class="absolute left-0 w-full h-2 scan-line hidden-state"></div>

                        <!-- Success Checkmark (Hidden by default) -->
                        <div id="successOverlay"
                            class="absolute inset-0 bg-success/10 backdrop-blur-[2px] flex items-center justify-center hidden-state">
                            <svg class="w-14 h-14 sm:w-20 sm:h-20 lg:w-24 lg:h-24 text-success" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>

                        <!-- Denied X (Hidden by default) -->
                        <div id="deniedOverlay"
                            class="absolute inset-0 bg-error/10 backdrop-blur-[2px] flex items-center justify-center hidden-state">
                            <svg class="w-14 h-14 sm:w-20 sm:h-20 lg:w-24 lg:h-24 text-error" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M6 6l12 12M18 6L6 18"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <p
                class="mt-4 sm:mt-6 lg:mt-8 text-primary/50 text-xs sm:text-sm font-medium tracking-wider uppercase text-center px-4">
                Align RFID Card within the frame
            </p>
        </div>
    </main>

    <script>
        // DOM Elements
        const statusText = document.getElementById('statusText');
        const scannerBorders = document.querySelectorAll('.scanner-border');
        const idCardIcon = document.getElementById('idCardIcon');
        const scanLaser = document.getElementById('scanLaser');
        const idleRipple = document.getElementById('idleRipple');
        const successOverlay = document.getElementById('successOverlay');
        const deniedOverlay = document.getElementById('deniedOverlay');
        const scannerArea = document.getElementById('scannerArea');

        let isScanning = false;
        let currentCardId = null;
        let currentRfidUid = null;

        const KIOSK_ENDPOINT = 'api/kiosk/scan.php';
        const KIOSK_NAME = 'Main Kiosk';

        function setScannerColor(color) {
            const colorClasses = {
                primary: 'text-primary',
                secondary: 'text-secondary',
                success: 'text-success',
                error: 'text-error'
            };
            const className = colorClasses[color] || colorClasses.primary;

            scannerBorders.forEach(el => {
                el.classList.remove('text-primary', 'text-secondary', 'text-success', 'text-error');
                el.classList.add(className);
            });
            idCardIcon.classList.remove('text-primary', 'text-secondary', 'text-success', 'text-error', 'scale-95');
            idCardIcon.classList.add(className);
        }

        /**
         * Handles a physical RFID UID read by the USB RFID reader.
         * The raw UID is looked up in RFID_TO_EMPLOYEE. Only registered
         * UIDs are accepted; every other UID is denied.
         */
        async function simulateScan(cardId) {
            if (isScanning) return;
            isScanning = true;

            currentRfidUid = cardId ? String(cardId).trim() : null;
            currentCardId = null;

            // --- 1. Scanning state ---
            statusText.textContent = 'Processing...';
            statusText.classList.remove('text-primary', 'text-success', 'text-error');
            statusText.classList.add('text-secondary');

            idleRipple.classList.replace('visible-state', 'hidden-state');
            successOverlay.classList.replace('visible-state', 'hidden-state');
            deniedOverlay.classList.replace('visible-state', 'hidden-state');
            setScannerColor('secondary');
            idCardIcon.classList.add('scale-95');
            scanLaser.classList.replace('hidden-state', 'visible-state');

            // A click without a physical UID keeps the existing kiosk behavior.
            if (!currentRfidUid) {
                finishDenied('Denied • RFID Required');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('rfid_uid', currentRfidUid);
                formData.append('kiosk_name', KIOSK_NAME);

                const response = await fetch(KIOSK_ENDPOINT, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const responseText = await response.text();
                let result = {};

                try {
                    result = JSON.parse(responseText || '{}');
                } catch (parseError) {
                    console.error('Kiosk endpoint returned non-JSON:', responseText);
                    throw new Error('The kiosk server endpoint did not return valid JSON.');
                }

                if (response.ok && result.success === true) {
                    currentCardId = String(result.employee_id || '');
                    finishApproved(result);
                    return;
                }

                finishDenied(
                    result.message || `Denied • RFID ${currentRfidUid}`
                );
            } catch (error) {
                console.error('Kiosk RFID scan error:', error);
                finishDenied('Unable to record scan. Please try again.');
            }
        }

        function finishApproved(result) {
            scanLaser.classList.replace('visible-state', 'hidden-state');
            idCardIcon.classList.remove('scale-95');

            const employeeId = String(result.employee_id || currentCardId || '');
            const action = result.attendance_action || 'time_in';
            const actionLabel = action === 'time_out'
                ? 'Time Out Recorded'
                : action === 'already_completed'
                    ? 'Attendance Complete'
                    : 'Time In Recorded';

            statusText.textContent = `${actionLabel} • ${employeeId}`;
            statusText.classList.remove('text-secondary', 'text-error');
            statusText.classList.add('text-success');
            setScannerColor('success');
            successOverlay.classList.replace('hidden-state', 'visible-state');

            try {
                sessionStorage.setItem('scannedEmployeeId', employeeId);
            } catch (e) {
                // sessionStorage may be unavailable in some restricted browser contexts.
            }

            setTimeout(() => {
                const profileUrl = new URL('kiosk-profile.php', window.location.href);
                profileUrl.searchParams.set('employee_id', employeeId);
                profileUrl.searchParams.set('_', Date.now());
                window.location.href = profileUrl.href;
            }, 900);
        }

        function finishDenied(message) {
            scanLaser.classList.replace('visible-state', 'hidden-state');
            idCardIcon.classList.remove('scale-95');

            statusText.textContent = message;
            statusText.classList.remove('text-secondary', 'text-success');
            statusText.classList.add('text-error');
            setScannerColor('error');
            deniedOverlay.classList.replace('hidden-state', 'visible-state');

            setTimeout(() => {
                resetScanner();
            }, 2000);
        }

        /**
         * Resets the UI back to its initial waiting state.
         */
        function resetScanner() {
            statusText.textContent = 'Please Tap Here';
            statusText.classList.remove('text-success', 'text-secondary', 'text-error');
            statusText.classList.add('text-primary');

            setScannerColor('primary');

            successOverlay.classList.replace('visible-state', 'hidden-state');
            deniedOverlay.classList.replace('visible-state', 'hidden-state');
            idleRipple.classList.replace('hidden-state', 'visible-state');

            currentCardId = null;
            currentRfidUid = null;
            isScanning = false;
        }

        /**
         * ------------------------------------------------------------------
         * PHYSICAL RFID READER INTEGRATION
         * ------------------------------------------------------------------
         * USB RFID readers that behave as HID keyboards type the UID and then
         * send Enter. The listener below buffers that UID and passes it to
         * simulateScan(), where the allow-list check is enforced.
         * ------------------------------------------------------------------
         */
        (function initRfidListener() {
            let buffer = '';
            let lastKeyTime = 0;
            const RESET_TIMEOUT_MS = 1500;
            const MAX_UID_LENGTH = 100;

            window.addEventListener('keydown', function (e) {
                const now = Date.now();

                if (now - lastKeyTime > RESET_TIMEOUT_MS) {
                    buffer = '';
                }
                lastKeyTime = now;

                const isTerminator = e.key === 'Enter' || e.key === 'NumpadEnter' || e.key === 'Tab';

                if (isTerminator) {
                    if (buffer.length > 0) {
                        const cardId = buffer;
                        buffer = '';
                        e.preventDefault();
                        e.stopPropagation();
                        simulateScan(cardId);
                    }
                    return;
                }

                // Physical readers normally send the UID as keyboard characters.
                if (e.key && e.key.length === 1 && buffer.length < MAX_UID_LENGTH) {
                    buffer += e.key;
                }
            }, true);
        })();
    </script>
</body>

</html>