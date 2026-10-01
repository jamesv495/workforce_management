<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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


        /* ================= MOBILE LOGIN VIEW ================= */
        @media (max-width: 640px) {

            html,
            body {
                min-height: 100%;
                height: auto;
                overflow-x: hidden;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            body {
                min-height: 100dvh;
            }

            #login-screen {
                position: relative;
                inset: auto;
                min-height: 100dvh;
                height: auto;
                overflow-y: auto;
                overflow-x: hidden;
                align-items: center;
                justify-content: center;
                box-sizing: border-box;
                padding: max(20px, env(safe-area-inset-top)) 14px max(24px, env(safe-area-inset-bottom));
            }

            #login-screen::after {
                content: "";
                position: absolute;
                inset: 0;
                pointer-events: none;
                background: linear-gradient(180deg, rgba(111, 169, 230, 0.04), transparent 32%, rgba(245, 155, 69, 0.05));
            }

            #login-screen>.absolute.top-0 {
                height: 28%;
            }

            #login-screen>.absolute.bottom-0 {
                width: 18rem;
                height: 18rem;
                bottom: -4rem;
                right: -6rem;
            }

            #login-screen .bg-card {
                width: 100%;
                max-width: 420px;
                margin: auto;
                padding: 24px 18px 20px;
                border-radius: 22px;
                box-shadow: 0 18px 45px rgba(22, 59, 109, 0.10);
            }

            #login-screen .bg-card>.flex.flex-col.items-center {
                margin-bottom: 22px;
            }

            #login-screen .w-28.h-28 {
                width: 88px;
                height: 88px;
            }

            #login-screen .w-24.h-24 {
                width: 76px;
                height: 76px;
            }

            #login-screen h1 {
                margin-top: 10px;
                font-size: 1.9rem;
                line-height: 1.05;
                text-align: center;
            }

            #login-screen p.text-lg {
                font-size: 0.98rem;
                margin-top: 5px;
            }

            #login-screen p.mt-4 {
                margin-top: 12px;
                font-size: 0.68rem;
                letter-spacing: 0.12em;
                line-height: 1.5;
                text-align: center;
            }

            #login-form {
                gap: 14px;
            }

            #login-form label {
                font-size: 0.8rem;
            }

            #login-form input {
                min-height: 48px;
                font-size: 0.92rem;
                border-radius: 13px;
            }

            #login-form button[type="submit"],
            #login-screen>.bg-card>button {
                min-height: 48px;
                border-radius: 13px;
                font-size: 0.9rem;
            }

            #login-alert {
                padding: 10px 12px;
                font-size: 0.78rem;
            }

        }

        @media (max-width: 380px) {

            #login-screen {
                padding-left: 10px;
                padding-right: 10px;
            }

            #login-screen .bg-card {
                padding-left: 15px;
                padding-right: 15px;
            }

            #login-screen h1 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>

<body class="bg-background text-primary font-body antialiased flex h-screen overflow-hidden">

    <!-- SECURE PRE-LOGIN SCREEN -->
    <div id="login-screen"
        class="absolute inset-0 z-50 flex items-center justify-center bg-background transition-opacity duration-500 overflow-hidden">

        <!-- Decorative background elements representing sky and sunset -->
        <div
            class="absolute top-0 left-0 w-full h-1/3 bg-gradient-to-b from-accent/20 to-transparent pointer-events-none">
        </div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary/10 rounded-full blur-3xl pointer-events-none">
        </div>

        <!-- Glassmorphic Login Panel -->
        <div
            class="bg-card w-full max-w-md p-10 rounded-2xl shadow-xl border border-border relative z-10 mx-4 transition-all-300">
            <div class="flex flex-col items-center mb-6">
                <div class="w-28 h-28 rounded-full bg-white shadow-xl border-2 border-orange-100
                flex items-center justify-center overflow-hidden">
                    <img src="assets/images/holiday-logo.jpg" alt="Holiday Travelers Inc."
                        class="w-24 h-24 object-contain">
                </div>
                <h1 class="font-heading text-4xl font-black text-primary leading-none">
                    Holiday Travelers
                </h1>

                <p class="text-lg font-medium text-orange-500 mt-1">
                    Travel & Tours Inc.
                </p>

                <p class="mt-4 text-sm text-gray-500 tracking-wider uppercase">
                    Workforce Management Portal
                </p>
            </div>

            <!-- Login Form -->
            <form id="login-form" action="authentication.php" class="space-y-5" onsubmit="handleLogin(event)">
                <!-- Custom Notification Container inside Login -->
                <div id="login-alert"
                    class="hidden flex items-start gap-2 text-error text-sm font-medium bg-error/10 p-3 rounded-lg border border-error/20">
                    <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                    <span id="login-alert-text">Invalid login.</span>
                </div>

                <div>
                    <label class="block font-body text-sm font-medium text-primary mb-1">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="email" id="login-email" required
                            class="w-full pl-10 pr-4 py-3 bg-background border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent font-body text-sm transition-all text-primary"
                            placeholder="Enter your email" />
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block font-body text-sm font-medium text-primary">Password</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input type="password" id="login-password" required
                            class="w-full pl-10 pr-12 py-3 bg-background border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent font-body text-sm transition-all text-primary"
                            placeholder="••••••••" />
                        <button type="button" onclick="togglePasswordVisibility()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-primary transition-colors">
                            <i id="password-toggle-icon" data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="login-btn-submit"
                        class="w-full bg-secondary hover:bg-[#E08A3B] text-white font-button font-medium py-3 rounded-xl transition-all transform active:scale-[0.98] shadow-lg shadow-secondary/30 flex justify-center items-center gap-2">
                        <span id="login-btn-text">Login</span>
                        <div id="login-spinner"
                            class="hidden w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin">
                        </div>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script src="scripts/shared-data.js"></script>
    <script src="scripts/script.js"></script>
    <script src="scripts/login.js?v=20260925-step7"></script>
</body>

</html>