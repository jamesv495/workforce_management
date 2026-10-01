<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Holiday Travelers Inc. - Kiosk Profile</title>

    <!-- Google Fonts: Poppins & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN -->
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

    <style>
        body {
            background-color: #FFFFFF;
            font-family: 'Inter', sans-serif;
            user-select: none;
        }

        /* Suitcase & Airplane Takeoff Floating Animation */
        @keyframes planeFloat {

            0%,
            100% {
                transform: translate(0px, 0px) scale(1.05);
            }

            50% {
                transform: translate(4px, -6px) scale(1.12);
            }
        }

        .animate-plane {
            animation: planeFloat 3.5s ease-in-out infinite;
            transform-origin: center;
        }

        /* Spread Scanner Ripples (Outer concentric circle radiation) */
        @keyframes spreadScanWave {
            0% {
                transform: scale(0.96);
                opacity: 0.85;
                stroke-width: 2.5px;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                transform: scale(1.42);
                opacity: 0;
                stroke-width: 0.5px;
            }
        }

        .spread-ring-1 {
            transform-origin: center;
            animation: spreadScanWave 2.8s cubic-bezier(0.1, 0.4, 0.6, 1) infinite;
        }

        .spread-ring-2 {
            transform-origin: center;
            animation: spreadScanWave 2.8s cubic-bezier(0.1, 0.4, 0.6, 1) infinite 0.9s;
        }

        .spread-ring-3 {
            transform-origin: center;
            animation: spreadScanWave 2.8s cubic-bezier(0.1, 0.4, 0.6, 1) infinite 1.8s;
        }

        /* Active High-Intensity Spread Mode when scanning */
        .scanning-active .spread-ring-1,
        .scanning-active .spread-ring-2,
        .scanning-active .spread-ring-3 {
            animation-duration: 1.1s;
            stroke: #F59B45 !important;
        }

        /* Continuous Rotating Double Circle Dash Arc */
        @keyframes rotateClockwise {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .rotate-scan {
            transform-origin: center;
            animation: rotateClockwise 12s linear infinite;
        }

        .rotate-scan-reverse {
            transform-origin: center;
            animation: rotateClockwise 18s linear infinite reverse;
        }

        /* Pulsing Glow Effect */
        @keyframes ringGlow {

            0%,
            100% {
                filter: drop-shadow(0 0 3px rgba(22, 59, 109, 0.3));
                opacity: 0.85;
            }

            50% {
                filter: drop-shadow(0 0 12px rgba(245, 155, 69, 0.75));
                opacity: 1;
            }
        }

        .animate-glow {
            animation: ringGlow 2.5s infinite;
        }


        /* Responsive kiosk sizing for phones and small tablets */
        html,
        body {
            width: 100%;
            min-width: 0;
            overflow-x: hidden;
        }

        img {
            max-width: 100%;
        }

        /* Make the employee photo occupy the entire circular avatar area. */
        #avatar-display {
            position: absolute !important;
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }

        #employee-photo {
            position: absolute !important;
            inset: 0 !important;
            display: block;
            width: 100% !important;
            height: 100% !important;
            max-width: none !important;
            max-height: none !important;
            object-fit: cover !important;
            object-position: center 22% !important;
            border-radius: 9999px;
        }

        @media (max-width: 639px) {
            body {
                min-height: 100svh;
            }

            #scanner-container {
                margin-top: 0.25rem;
                margin-bottom: 0.25rem;
            }

            .spread-ring-1,
            .spread-ring-2,
            .spread-ring-3,
            #outer-line,
            #inner-line,
            #scanner-laser {
                vector-effect: non-scaling-stroke;
            }
        }

        @media (max-height: 700px) and (min-width: 640px) {
            main {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }
        }
    </style>
</head>

<body class="bg-white min-h-screen flex flex-col justify-between text-gray-900 antialiased overflow-x-hidden">


    <!-- Main Content Container -->
    <main
        class="flex-grow flex flex-col items-center justify-center w-full max-w-5xl mx-auto px-3 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">

        <!-- Brand Header Section -->
        <div class="flex flex-col items-center text-center w-full my-2 sm:my-4 lg:my-6">

            <!-- Logo Container matching provided image -->
            <!-- Provided Holiday Travelers Inc. logo image -->
            <div class="w-full max-w-[560px] px-1 sm:px-2 mb-3 sm:mb-4 flex justify-center items-center">
                <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAIBAQEBAQIBAQECAgICAgQDAgICAgUEBAMEBgUGBgYFBgYGBwkIBgcJBwYGCAsICQoKCgoKBggLDAsKDAkKCgr/2wBDAQICAgICAgUDAwUKBwYHCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgr/wAARCAH+A/oDASIAAhEBAxEB/8QAHgABAAAHAQEBAAAAAAAAAAAAAAEDBAYHCAkCBQr/xABwEAABAwMCBAIFBAcODwsJBgcBAAIDBAURBgcIEiExCUETIlFhcRQygZEVI0JSobHRFhcYJDM3OGJ2lbKztNIKGTRTV3JzdHWCksHT1PAlNkNVVmaTlKK14TVFRlRjZZbDxCZHZIOjpBonKIWlwvH/xAAdAQEAAQUBAQEAAAAAAAAAAAAABwMEBQYIAQIJ/8QASxEAAgEDAgMDBwgIAwcEAgMAAAECAwQRBQYSITEHQVETImFxkbHRFDZScoGSobIVFzJCU3PB4RY0YiMzNVRjgvAlQ0TCCCYkotL/2gAMAwEAAhEDEQA/AO/iIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgIOdjyTm79QvDnOwegyD5nHRQjeTnLCME46d/f0TDHcTR1GUUGnIBIxkdlFAEREAREQEA72nzUl9bFG4tfK0EAl3TsPaoVNfBSgvnkY1rclznHoGjuT7APb7ei5t+It4j8+5E1fsdsPfnQ6dY91Nfb/SSnnuDiS10MBac+hyCHOHz+rRhhJdmtC0K9168VGj0X7Uu5L/zou81fdW69O2pp7uLl5k/2Y98n8PFm4+qPEB4QtG3h9g1BvtY21UcnJK2jlfUsjdnGHPia5rTn2kLImhN0dDbn2KPVO3mq7febbKS2Ort1U2VnMDgtJB6OGere4wuDV41BT2iikuFzrw2KMZkke4kH2Dv1K+7wEcbm4G0XGhpW4WfUE9Hpm/X+ls99tMpJgq6SombCZZGAgGSEPErXjqMEfNc9rpE1LsyhR0yde0quU4JtppYePAirbva/qGpatChd26jSk8ZWcrPT1neeF5kibISDzNByOxXpQaQWghRUQk+8giIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAhzEfOUuSocxj35A5BkhwPb6F7I6nPZfN1Rpa1av07X6XvPyg0dypJaaqFNVSQv9HIMO5XxlrmOwejmkEdwQUjjiSk+R8Tc+F8K5mg3iQ+KbcNKXyo2P4XtSiO50s3+7uqqaNj2wPa7HyeEuy0uBGHPIcBgjCwhtl4y/F9okRwaqq7BqqLmHpfstaxBKWftZKcxtz73NcV9XjJ8H3c/ZkVms9gzVau003mmmtzxzXGiZnPVrQPlLcdywB/mWO6labS5EjxNHyTRSESM9ZrmYOMOH3OOxyO/wBanvbui7RvtLjSoKNTxcl52f6HL26twb20/XJ1LicqL/dS/YwvDuZ07248dfbG4PipN2NmrxZ3OwPlFnrY61h/blrhE5oPfA5/ie62B228SHg23RMcNl3vtduqJMYpdQNfQPJ9gNQGNcf7UkLiOHNMRY6Np5nZLiwZ/B0+nv71MBPK0A4A7ZAJ+GTk492V8XnZxolf/cuUH6HlfifWn9ru57X/ADCjUXpWH7UfoZtmpbJe6CO6WW70tZTTNDoaiknbIx7T1BDgcEEYVWJiewBOex6EBfn30XuZuHtxXi67fa3utjqQc+mtNwkp3H4ljhke49Fnja7xVOM/bx8bKvciDUNNGMNpdRW9kwPxkZySn6XrU73sz1Kn/lqqn6+T/qjeNP7Z9Lrf5yhKHpWJL4nZIOOPW7qTPWspoH1M8rWsjB5nO6YA8/x/Fc4NN+OvrijtjafV+wFqr60NAdUW2/SUsZOO/o5IZSOvlzn4nusP8TfidcQnEXa5tJtlh0vp6qY5s9ssjnGaqaezZZXElzcdC0BrXD5zSOixVp2f7hr3Sp1YqEe+TfuMzqHa1ta2snVt5SqT7o4x7c9DKfiQ+JDLua+4bEbDX/0em2ymnv8AfqN+XXR2S0wwlpyYMjDnD9U6t+aSTpFd7xbrLb3XCunjijgaeZ/k0H7lvXJPlnOR2VLcrvQWCgdc7pOyOOFpLnkY5QfuRjrny93ZYh15r+fWNy5oi6GijcTDCf4Tva4+flnsApy0DQbTSbRW1FYXe+9s511fVdU3bqsrq7fLuXcl3JE/X2vqjWVwAYXQ0cRJhi9+fnH2uPn5Z7ALPXhR8KOtOJvi101eqS1TP01o260t31Hc305dBFHDKJIqYnsXTPbyco68nO/swqzuBXgb3X46t2otF6HpvkNit7o5tTajqYy6C2wE+zp6SV4DhHECC4gklrGucO9HDjw2bTcLO1Ft2i2gsHyG10DeeaV/KaiuqHNAkqZ3gN9JK/AycAAANaGta1rdc33vK30i2np9o81ZLDx0in4/6vQSdsPZFTUK8LyvHhoweV4ya8PR6TIDejQMY6dlFQb2CiueTo0IiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgIBuPNOQeSii8wmCU6KN2WFuQc5C1n4yfDI2Q4qYanUlnjGltYPY5zL9bqYclS4fc1MYwJBnPrAtf17nstmjh3XHn1K8uhjd2JHrZ6HqSruyv7zTbhVrabjJd6/qY7UtK0/V7Z0Lumpxfj70+44R8SHCJvrwq6n/M/ulpL0VFK4tt19osvoazH3kmBynHXkcA4Dyx1WM21LMg4zGR0lacgr9CetdA6K3H01VaN17peiu9qroyyqt9xp2yxSD3tcCM56g9weo6rnZxmeDXdrHJV7g8Js76unj55KnSFdOBIwEk/peR2BJgdA2R3MQB6zj3mPbvaHbXvDQ1DzJ/S/dfwOft3dll5p3Fc6Z/tKffH95erxNBAQRkdj2Xr0rsADyUy92i+6TvFTpzVdlrKG4UT/R1lDWU7op4XhxDg9rgMEY7dwVTNlLmh2B1CkqM4Sipxa4X0xzIdqU5U6jhNNNdxWMf6oPKM47qRd7zQaftsl4utS2JjRjp0J9g9+fcqa73yjsdtdcq+oZHGwYPN3z7B7crFOsdXVmqbj8pqPUhicfk8AJw0Z6E+0+3y9wVzRhKT5le0saly+f7JM1nrKu1VWc0n2qljeTT0zScAZ7uyTl3t9/ksocCfA7ulx2bwRaC0K1lDZ6B7KjU2oamIuhtlKXkHDMgySu5XCOPI5iOYkMDnNwfJM55PQDr5LvN4LOx+n9rOAHSl8pbdG256vM16u9SPnzOfK9sA5hg4bAyIAZ6HJGMrW98a/X27ovFQ/bm+GPo8X9hKex9t2+s6pGlPlTguJ+nwX2mfOG/hr2o4V9p7dtBtBYW0NsoYszSu5XT11Q4D0lTO8NHpJXkAl2AMANa1rWta2/gwAAZJx7VFvYZ9iLmerVqV6jqVHmT5tvqzpWjRpUKSp01iK6JADAwiIqZVCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgBGUwAchETAIOOPeqaeQQNdLO4ANJILn46YJ/2Huypj5/RlwLmE9SwkYHT2n4/jC5/eLd4iEmjaWo4Wtk7+9t4qIi3V95oiM0ULh/UcZz0leD67h8wFo7uPLk9H0i61m9jbUFnPV9yXe2YPcGu2e3tOndXD6dF3t9yRgPxWuKfbHf3eyHS22GnLY+DS75YKrV0EQNRc5h6jo2SNwXQxlpGTkO7g4WpV0vtJarc+7XOqaxjer+YdSfgO+fd2VPd7tQWOjlra2dkLOxDfaOwH/h9KxjrDVNbqe4GaZojijefQwNzhnXuevUrpvRNKpabYQtqb5R72cm39e63FqlS+rrHE+7l9hO1hq2r1PW+llHJBG4/J4Rn1Rnufa72/iC+I55cMZXljpJpRCI2kgHI9MASQM47ezrjqcA9Oil+naOn4xg/Us9HhhzL6nbqkkodx6MoBxnsu9vgl7pU25Ph56Qo4ZC+r0zV1dluDM/MdHO6WP4/aJ4D9K4FPcS4kHuV1X/oa7eEPpdydg625uJimpL/aKMjoeYPp6uQH3FtI0/EKPu0+xd3t3yiXOnJP7Hy/qSD2c3fyXXVCT5VE19vcdV2ghoBPl5qKg3PKMnPTvhRXOa6E/hERegIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIDyHE92ry6YMGXAY9pOPxr4W4W5Gk9rtNT6t1neIqSigOCZHes84OGsb3c7p2HvPktUNy/EY13da99Ltnp6mttCciKsr4xLO7r0IbnDcjrgg4Wmbo35tvaOFf1fPayoR5yf2dy9ZsOh7W1rcLfyOnmK6yfKPtNzhLG4Ah7eo9oUfSR/1xv1rnqeNfiWJOdy3A+wWqk6f/pKH6NfiV/smO/eqj/0S0H9fe0v4VX2L4m1fqq3L9KHtfwOhfpI/wCuN+tPSR/1xv1rnp+jX4lf7Jjv3qo/9En6NfiV/smO/eqj/wBEn6+9o/wqvsXxH6qtzfSh7X8DoX6SP+uN+tPSR/1xv1rnp+jX4lf7Jjv3qo/9En6NfiV/smO/eqj/ANEn6+9o/wAKr7F8R+qrc30oe1/A6F+kj/rjfrT0kf8AXG/Wuen6NfiV/smO/eqj/wBEn6NfiV/smO/eqj/0Sfr72j/Cq+xfEfqq3N9KHtfwOhAkJPR7SM+S9B5Izj4LRzQfiF7w6fqYTrKmt97o+flme+BsEuPPle3Deb3cmPetq9mN+tEb46dN60hW/b4SG11ulcBNTPx81w8/PBHQ4PsON12t2jbZ3ZPyVpUaqdeGXKX2eP2Gua3s7XdAh5W5hmHTijzWfT4faX0ig0gtBHsUVvhq4REQBERAEREBLErsuDsDr6uAeyg2oz0xnHcjy/Iua/iZ+LbuJtxubU7FcM1wmslZYLjy3/UdXa2SOmlY4tdTwxztLfRhzSHSEHmI9Tp1OIdB+OpxiafcIdS2nSOoYxgPNba5KeY+3DopGM/7C3Kz2Lr1/ZRuaSjiXRN4fwNDvu0Tb+n307Wq5ZjyylyydiQ/OMELw6oLXlmQT3+j/Mub+hf6IItsnooty+G+phaGj01VZ9QMlJOOpbFJE3HXy5z8T3V+6h8dThgqtvLpetG6W1ONRQUbnW6zXS3RxtqKjGGtMkcj2ho+dk4yO3XorOrs3ctKai7d83jKxj2l9S33tarRc1cLks4fUvbxNfEBt3CloM6J2+udNLry/UhFvDsPZaoHZaauQZwDnPID0LhkgtaQeNeotQhhqb/qKvllqKiZ75Z6qZz5pXucXOy4nJJcSc98kr6G8u8eqd1NZXjdzdG//L7teKh8tRUSHIJPZjG5LQxrcNa3qA0AdeucR6iv9bqKrdUVMZ5IzzRwOz83Pc5OGu95OCOpxlThtLbdDQrHgxmbWZS/p6kQJubXLzdupOpJ8NGLxFejx9bPepdSV2oa75RUR/aWuPoIGdXM/bEduqzbwF+HXvTx1a4dBpSkktOj6CcC/ayradxhp8YLoomdPT1HKRiMOAbzAvc0Y5sueGb4Put+Laqpd4d7I6zT+28craimiezkqtROB7Rczcsp/bK4DPZgd6zmdptt9s9CbRaFtu3O2emqayWO1Uogt1toYvRxwx9z07lxJLi45cXEkkkkrA7u39Q06LstPfFV6OXdH1eLN12jsSpeKNxerhpdy75f2NAPEC8G/Z+28FMNBws6MkZqjb2KW4RznlfXajiDWmqZUSAN9LMfRiRgADQ5pjY1gfgcbWGKEtZKx/JgdQ3HK0nAcM/OH1FfqrNMxwxIC7IIBzjofLouFnjfcBb+Frf1u8m3dn9BojcCrmlhZDD9ptl1OXzUxxgMbJkysHQYDwMCPrjeznddWpcy0+8nlzy4yb7+9fb1RmN97ZpUqUb20hhRwpJeHiaSNmdyjnGDjqMdituPA53gk2q8RLS1qqbiKei1dbq6yVjZDlrxJH6eH6XVEMDMfBafzPc1x5Wub1Pqu7j3FXBs1uTcdnd39LbtWhrRU6W1FQ3aEYJLzDURykd+oJYQR7PrUq67ZfpDR69t9KEvcR5o9x8i1SjXX7skfqcHbqipLDdbffrJR3u01TJ6WspmTU08bstkjc0Oa4HzBBBVWuRZRcZNPuOnYSU4qS7wiIvD6CIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgIEkOx7l4fMYw5zgCG9ensXrmJGCvja+qKii0TdrhSTOZLDbZ3xub5OEbiD9Co3FXyFtOq1+ym/YslSjTdWtGC72l7TQPi/35rd4t16qiguTn2WyVUlPboIujXcri2SYe0uLch3swrV2g2c3B3u1AbBt9aYi1jPSVtZUSlkFMzrgudgnJIIA6npnqMkWE2q6cxceY46+wez4LoZwCaRs1k4b7TcqCkDJrtNNU1so+dI8SuYCT36NY0D2YGMLjfbGgS7Sd61auoVHwc5S8Ws8orwOl9x6nDYu06MLOC4niMfDOMuT8WYgi8MncR0TTJuPaeYtGcQSEZXr+lkbg/2R7V/1eRbmhuRnlHX2hOQfet+pTyuxvYX8CX3mQ/+sjdn8ZfdRpl/SyNwf7I9q/6vIn9LI3B/sj2r/q8i3N5B9636k5B9636l7+pvYX8B/eY/WRuz+Mvuo0y/pZG4P9ke1f8AV5E/pZG4P9ke1f8AV5FubyD71v1JyD71v1J+pvYX8B/eY/WRuz+Mvuo0y/pZG4P9ke1f9XkUP6WTuD/ZJtP/AFeRbncn7Vv1JyftW/UvP1NbD/gS+8x+sjdn8ZfdRzW314bdydhKpk+qaWKrtkzzHDeaN3PEHeTHA4LDgdj08gTgkfF2d3a1Fstruh1pYa0sFNMGVcA6tqKcuBfGQMcx5cY9hGV0S320fYdY7Raisd+pRNBJap34c0EtexpexwyD1a4AjyBXLRtTljA5xy1uAcdjnOfioJ7QdpQ7Pdw0LnS5tRl50cvnFp9M+DJe2Trr3rota3v4JuPmyaXKSa5PHj4nW3TmordqXTlFqSzzCSkrqWOemkb15o3tBafqKrw4kA481iXghu0964YdLVNQ8ExwTQNA7NZHPLGwDPsaxoWW2t9UN9i640a9lqOk0LqXWcYt+tpHOWpWysr6rbx/ck4+x4IjqMojew+CLJlmEREAREQGPt7eF3YPiKs77NvLtfa74wtLYqmog5aiDPnHMzEkZ97XBaJ8R3gHUkjZr3wu7mGmDS5zNPapcXsPUkMjqYxlo7AB8bve/uV0mknZC18kxDWs+c5x7e0rHV34gbHV6vj2822p/s9e5HESmndmlowD6z5ZB0AbnryAnOAcEjNzS3xc7VnGMbjh4uSg3nifgo/Aw99szTdxwlGrQy1zcksNely+Jw03z4VuIPhmuPyPeXbK6WOmMhjjujoOehmcDj1alhdESe4aXc3uWOqy5Utrh+W1k7ixrw2JnLhznHyBz1JPXr80dcgr9JdfYLZf7M+z6ktlHXQ1EAjrIJqcOhmBGCCx2QWnr0Oei5EeO5wOaF2KuOmN/tlNEUdm0/d5prXf6C3xclNDW9ZoniMdGCSNsrCG8rWiEDGXBTNtjf0NWuoWd3T4JS6NdH6MdxDG5ezX9FUpXVvU4qceqa5r2HPS73er1JcI5YqcPjc4egpYcvB69Gsx85x8gPqXTrwyPBGmlfb9/wDjU0+cB7Kqxbf1AIGcBzZ68YBJHcU/0SduVctIZn0foq6OQsdE5rstxkcrm+zz7r9O2yutxups9pXc2MMDdR6cobm1sYIb9vgZL0GT0w7AGfjlXPaPrGp6ZZUqFtLhhUym115Y5F72eaLpl7eTqV48ThjC7vWXLQ22hoaKGjt9OyCnhja2GCFjWMa0ABoAaMAAAAAdMdOyqOUAcoGMdkYMNAzn3qKgfrzJxSSWEeTnPfssY8W/DZoXi62G1BsRuBSt+S3ikcKOtdEHvoKxmXQVMbT3cx/rYHRzQ5p6EhZO5W56leTFE7mDsnPfqvujVqW9aNWm8Si8r1lKtShcUpUqi5S5H5a97dn9dcP+6d+2a3OtklFe9OXN9HcY3NLmkjq2Rjj+qMfGWSNd9017T59LRfI9z2vHqubgAtPs7fHufrXZzx/eAJ27O1zeMLa6yul1Ho2iMWraalp8zXC0BxPyg46l1Nl7j06xPec/amg8XTUSD15Yerg3la1wIyXYwSPb3HRdT7S3BS3DpEa7a448pLwfwfU573BodTRtSdJLzHzT8V/Y/SB4UG8L96vD22u1VI6MT0OnGWarGSXc9A59FzO6khzhA2Q+3nzgAhbFB/M0OafpXNf+hqNcahu3C/rvQNdZq1tBaNbtq7fc54XCOX5RSxtkp4yehMbqcOcB1BqASBkZ6TjIbkn4rm/clnGw1y5oprCk8fbz/qTloFxK60ajVl1cVn3HsdkQdkWFMwEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAeS45IBHRRbzeZUt0gDuToST2BC9glo7r5y0edSOceRTm9x+pME9eZQ5X/fFfR6ekXnnI6JzlfOWMAnr2XwtzHlm3d9PL2tFSR/0bl9wkYIC+Buc97duL81reYmzVPkR/wZVpqKX6PrZ+jL3MubJJ3lNf6l70ckvlI83fhXTHgYeH8LWlTgf1LL/HyLl6ajmJc1zcE5C6e8B8pdwraSeQMGlmBOe2KiQLm3sUg1uW5bX7r950H2w01Dblrz/fX5WZhYfVGAeyjn9qV5actBc8NOOoz2Ucj+uj6108c44Il2PIpzftT9Shg+R/CmH+w/5S8+09I837U/UnN+1P1KHXzz/lJk+xy9wxyPSLzzH71yc6cxg+FucAdur+f/c9T/FuXI/5SPN34V1u3Nc87fX2MNGHWep6jr/wbv8AbuuP3ysO9b0rOvtGPwZXOPblDivLPl3S950B2K01O3u8+Mf6nTXw+Dz8KOm3l3zn1p//AHs4Wa8nGBhYN8O6oMnCXpiSLleOauHQ46ivnB/zrOAkZk4e361N21Fw7ctI/wDTj7iGNyRf6fuv5k/zM95I6Y/AnMfYfqUBIz+uN+tGuJ6lwx5LYOZhSPN1Ix29yc7c4zj4rwXjnxjB74z1IVjbqcRG2m0VO780l5jkruXmjtVJ9sqH+/lB9UftnYHvKsNR1TT9JtpXF5UVOC75PBdWtrc3tVUqEXKT7ki+PTkZ5hjB6k9AAsbbw8U+2+1AfbH3Jtxu/KTHbKEh7gf257MHtytat4OMncjcp81s09LNYrY8epDRvPpp2n7+QHLRjuG496x1pLTd91jf6exWOhkqKuuk5YYYmZL3dyfYPMknoB1J7gQBuDtrutQuv0dtej5Sb5eUkuX2R7/RkkzSuzzyFH5Xq9Thgubiuv2v4GStWb5b1cSmoo9IWJ8tLT1cnJHabY8taR5mSQHMgx1J6NAGcd8bMbBbDWbZbSYpaYRvulWyN1yrOX57gB6g8w0EuOPf1yqTh02CtezNgE1zlbV3qriHy6t5A0M7ERs6DAH1nHuAWTWRMLcYGCcnHtW+7F2Xd6dP9K6zUda9mublzUE+5eH2Gtbk122uF8h0+Cp28e5dZNd78SIHqgA9MLEfHJw32/iy4V9Z7HVMMXyu7Wl77NLKRiGvhIlpnn2N9K1gdjBLXOGRlZZ53glrcdD16eXX/PhREPqk85BPUkd/wqV7etUta0a1P9qLTT9XM0q4o07ilKjLmpJp/aflbu9Bc7TXTWmut0kFRBIYZ6aU9YnsPK4O9mCCCO/RfoQ8IbX8+5fh1bZ3aorhLLQWqW2OI7sbR1UtNGw9+0cTPo9i5V+OBwvjh840btqWxUsNLYNwYhfLcyNpa0VLyY6yPoMZ9N9tIHYTs9hzvB/Q524MOoOD7U+iBNK+Wx68ne31DyMhqKWme1rXe30jZnEY88+amXfNenrWzre+h9JN+OWsNe0ibZtGppW6a9jLlya+xPkdDGggAE+SioMJLQS4Hp3A6FRUKkvogQCcpyDyUUToMFJcbTQ3ainttwhZNT1Mbo6iGZoeyRjm8pYQ4EFpHcdj59yuPGsv6Hz1xqfxAbppexSttGyk0jLwL9FIPT01NLIea1QNcCTO1zXMa9xc1kXLI4lxEbuyGGjufNeJYGSAc5Jwctz5HyKzOi6/qWhOo7SWONYfxXpXcYjVNEsdX4PlEc8LyvgW1s9szttsRtradp9ptK01ksFmoxT2+30Y9WNuSS4k9Xvc4lznuy57iS4kkq6Q0DsjQA0AexRWInOVWbnN5b5tvvMpCnCnBRgsJdEERF8n2EREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQEoz+uY+duR5ez4rF2/PF1tjsRG623WsNxvTo+aKzUBDpfc6Q5xE33uPwyrW43eKRuxunG6S0jXs/NNdonOgyOYUkGcGQgHoSctbnsQTjDSDoRXXe5Xu4y3W8XOepqZpXSyzzvy9z3Elzie+SSSR269lEm++0b9BVHY2CzWxzb5qPxZKOyOz+WuwV7etqjnklycvT6EZ/154hm9+pZnQaYFJYKVzicwUwlkGT2LpOYEj2gD4BWceKniElcZTuvdCXHOWyBoOfcB0+CxlDLygMY7lDTnAVS2oMjiXY6nyCga83XuS7qcdW6m362vcTZb7U2/ZU+ClbQS9WfxZkVnFRxCnp+evdv+mH5FUR8UO/7uh3Yu3/TD8ixoJC09MKfDNnqSFZ/4h1z/mZ/eZ9S0DRl/wDHh91GSP0UO/46fnqXU+/0o/IpreJ3fstBO6l17f18fkWO45A7oV75iOgKp/4h13/mZ/eZQloWjr/48Pur4GRm8TG/JaD+epde39eH5F5qOIbeq5Us1DX7lXOWGoidHNG6UYc1wwR29isJj3cg6+SnQlvmVSlr+tzTTuZ8/wDUyi9E0qLyqEPuolt0npSRxc+xU5JPUkHr+FXpp7cvcHSllp9PaZ11d6CgpmltPSUtylbHGDnoAHdB17dla7C1vUOU6OXm6FysKF3dWtTylGbjLplNr3FzcUKN3T8nWipRXPD5rP2l7R76bxO77oX36LpL/OUz8/DeD+yjf/30l/nKymuLerVPa5haCT1x1V1+ntb/AOZn95/ExstI0xf+xD7q+BeMe9m7r/nbn3799Zf5y9Hend3+ybfv31m/nK0GHlGQVOieXd2l39qvmWuaz/zM/vS+JS/RGm/wY/dXwLvh3l3XeBzbmX3t/wAay/zlPj3g3Td0O5V+/feb+crNdUU1K3nqKtkY/wDadMfSqGs1zYbdnM5lIPX0ZyFQnr+rw63M/vy+J8x0aym/NoRf/avgZE/Pb3S/sl379+Jv5ymR7u7oD5+498cPddJs/wAIrEtZufVOGbZbW4PzTN1yPoIXzarWmpa4fbri5gP3EIDQ33Ajr9ZVpLc+qr/5NT78viXENs28utGK/wC1fAzTcN19d1NFLRXHcW7GGeMsljnukmHNPcHJ6rHkth2eoxyT22jeW9CIgXH8ast9RUTOMk9TLIXHJ9JIXfjXoE4Hl8BhYu817ULxry83PHTLb95kbPRaNjnyfm568PL3GWtOcRFdoDT0WldE3q6UVvpub5PTUkga1nM4uJAJ6ZLiVPqOMXdBo+06muBHkH1nXHv9VYfa4t6sAB9vKOv4FPY9/KCXE9PavuO69x0qahC5morklxPkijPbeiTqupUopyfNtpczKX6Lve9/rx6wmDT1aDK4kD45UmXis37kcS3cStYCegY/ssac7vapzfmj4K3e7Ny5/wA3U+8z6/w9of8Ay8Puov8Al4mt+aiJ8Mu51zLJG4cwSgfUQMj61aEtxuF1rpKu41c9TUzPL3zSyFznOJyST3OT16qRbqGsudQIKWEkdi/HQK/dttrr1qvUFPprTtvNbWTH13NOGRj7pxdj1Q3zP0DJIBv7DT9ybvqwpVas5wz3tv2LxLW6lo2hUpVKcYweO5Je0+XoTQN41Ze6SwWygfV1tRIfR0zGnDQfuzjHbv3A6d+hxujw/wDD9YdnLOJ5ZY6u9TxAV9aWgiMdzHH06Nz9JwM9gq7ZPY6x7PWL0NOIqq6VDR8tuDo8F3ta0fcsz5fDJJGVfRPN9rwSPauuOz3s7sdq2yrVIp1X49V/cgjdO77nW6zo0nikvx9Pq9A5PV6tzynoMexWfvBvXpfZvT4uN9qWSVszHCht7X/bJ3gD6mjPV31ZJAPxeIDiP0vsvan00Mray91MJNJQB+CxuP1R/wB60Hy7ny7EjTPVmuNTa/1FPqnVV0knrKj5xJw2MZ6NYCTygZwBn3nJJJnvQtu1dRkqtXzafvIA3nvy20GDtrV8Vd+yPr9PoNyuFjcfUm6+jbvqrVMzTM++vjihjj5GwxiGIhjRkkYJPcnv3WUQ3Axkn4rBnAPIX7SXRx8tRSgf9XhWdFidYpU6GpVacFhJ4Rs+07mtebdtq9Z5lKOW/F5NavEp4AdOcf8Atnp7RlXqlliuNh1NDV095NMZZGUjiG1cMeD0c9oaRnIL4mDoHZWXOH7h32p4Y9q7XtDtBpqK22i1w4Zytb6SolPV88rgBzyvcS4ux54AAwBe5iaTzH2nPTv7v9vYotaGtDRk4GOpVGV/dztI2rm/Jxbaj3ZZllZ20Lt3KguOSxnvwGgBoA9iiiK0LwIiIARlQLAXZz5dlFEAAwMIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAgCc4P0Kkut3prPb6q6V0rY4aWJ0kr39A1rRkuPuAVXnBxhYw4wbzPYOGzWddSScj32WWHn9gkww/gcrHULl2dhVr/Ri37FyLqwtneXtOgv35RXteDnZvDujcd29zLxuFcHuJuFS/wCTMkPWGDmPo2YPbDeUfQfac27A8dOv4VQtndM4vkeSXHJPtKnse1o6H6yuJru6rXlzOtUeXJtv7Tte3s6Vnawt6axGKSX2FfC8Z7qfC8Z6lfPhkcTlT4ZevVWR9SifRjeD0K9hxachUkMgJ7qeHnHRfEuSLecSrgm9pCqI5g7ocKhhc0nqVNjkcHHCpFvOLPoxSg+qcdFMaS3qCqKJ4z1KqI5nHpgLzCKEolXHNzdCVOY4t6j8Ko2B3djC4+wL1U3Skt8QkrqhkQA+aTlxXw2o9ShweCyfRbP6o6eSm+nZC0PqpGsaR0JPkrTrdcwjP2Nh5/YXg9fqXxqy+XKvcTPOQHHPIOw9ytp1ow6FxCxqVP2lgvit1paqElrJhNynHKzofwr49fr26VORRtjibn1eUHmx5Z691bIcSBzjm6ea9xu5OrR9HsVnOvOXRl3Cwt4deZXVFwrqwl9TVPeXHJDnZXlnK0D1B27qQJHEZwFMa84HQdlaybl1LpRilyRMLs/OGR7Mqex5DAAB2VOOoyprXkNA9yt5JHyVDXnA6DspjXnA6DsqdrzgdB2UxrzgdB2VLqW76k8dRlexM4DAA6KS15wOg7KZAx9RK2GGNz3OOGtYMkn4Kmoub4Um36D4nJKOZckTg7OG84aSOmV9zTWlLlfD6R7PRwt+c/GM+8L6+lNspGMjuuoZGDnA9DTNPM7t15lljafZvUG512jpbPT/ACe2wODaqs5cMY370ffP930qQNsbCvNTrQdaP7XSHe/S/BGn61uW1sKT4Jcl1l8PEt7a3aK+a4vMel9JUnMQG/KKl3zIWdi57gOmevvJ6AHqtwNptn9L7TafFssrfS1EzWmsrntAfM4D3dm5zge/qSSSa/b3bnTW29hZYdO0LY2D1ppcDnnfjq5x8yf/AAHTovtGYA8ri0Yz1PRdcbR2XZbcoRbinU9HSPoRAG4dx3Gr1ZRi2qfh3v0sgMPeTGCC0+sfJywpxPcW9j2lpZdI6QkZW6kkjw+Jg52ULXfdPwRl3saDn8ANs8VfGhRaSkqdu9q6ttRdeUx11zZ60dLnuyM5w6T6+X8Wo8lfVV1RLV1dRJNLO7mlmmeXPeSckk9ySSfrOMA4U4bd2rO7cbi7WI90e9+v0HPO+O0OlZRlY6bLNTpKX0fQvFn1rtfrtqW7S36/3SWsrZ5TJNVyPy57ic5/J7OnsCjA/GAxoA6YA8gvmxSZdlzuvmVWwTDACkqNOFOPAlyRANSpOtNzm8t9W+83H4ADzbQ3TP8Ayjl/k8CzusDeH24v2gup/wCccv8AJ4FnlQnr/LWa/wBY612V81bT6iBAPdMAoixCNpCIi9AREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAQ5QCXHqsP8d7f/6UtXuDiD8jiPf/ANvF+RZhd2PwWHuPElvCfq8j/wBSj/jo1htwctDufqS9xltA/wCO2v8AMh+ZHMSB4wOvkqmOQO6Er50UvXuqmGQE91xXhHbsoZPoRyhg6ezzK9xyOLsj2qkjkDuhKnska0DH4SqeEW0olbDL1VXFMHDBIXzGvI9YeaqIJe2VTaTLdxbK0SOachT4Zs4yqMSjH/ivTqiGnaHzSBgI7kqk0UpU3LofRhmje4hrjnPmvVRc6a3M9JUytGPuR3Ktu4arBLoaAMJaccxB6/hXzX1dRO70lTIXuJz63l7gqEqiRUpWLk/PZcFw1rJPllsJiwejnL5L5pZ5HT1D/SSOOS8knqfYqNspByWg/FT2yEtGAB07BW0pSl1LuNvCn+yiax8jifXPxCndfM595VOwkDIU0SOIzgK1lDAJgeQMKY15wOg7KUOoyoh5AwqWEU5RaKhrzgdB2U5vVoPuVO3q0H3Kc15DQPcqeEUyYHkDCmNecDoOylDqMqIla71W9x3yqeEymVDXnA6Dsvcc3pGlrerwfmDr9PuVHU3Gmom/piUBxwGsb1cT5YA75V57e7P6m1tTsumpGTWq2fOZTEAVFSO/s9VnxCudP0q81Sv5KhDPp7l9paXtza2VDy1aXCvxfqR8aw2i86nuAtlitz53tH22QD7XEPvnP7N+HdZX0doG3aUga+WaOprnsHpKnlwB06huew9nc+9ffsGnrTpy3x2qw0EcdO1gDYYs9Tj5xP3Z+Ky9svw+VeqnQ6p1nTvhoWYfS0pZiSoPfLgR6rPxj2KZNp7BauFGMeOo+rfREX7j3fF0Hl8EF08WW1s5sVddy6ttxrGuo7RBJmWq9H1n9scY88diey2c03pax6Vs8Fm09Rsgp4WANbHj1unc+0n291VUVuorbQx0Fvp2wwwxhkUcYwGtAwAFT33Ulm0nZ6i/aguUFHQ0kLpaipqXiNkUbRlzi49AB9C6T29tu20WklTXFVl1ff6l6CDta1ytqLdSrLhpx7u5LxZUVVayhidPUBrWMaXFzngAAd8k9B0+j4LSnit8RW2ahvkuz+yl1HyKQuhuOpYX5bK8kgxQnvjuC8dz0bjusYcb3iG3be+ap2x2krKm3aRa58dbXtYWy3YA4Oezo4cj5vQvByTjotXqGeWBwliwx+QSWHsfaD5HyBHVTztfZDhTV3frzusY+Hpl8DmHf/aj5aUtO0mfm9JTXf6F6PSZrZlxa1ziQwkYJPUeYOST9Oc+9VcM3XqvgaWvf2Ws8NcXNJDWtlx358DP4V9eN45j181uk4Y5IiFVOPmfQic0nOVVQvaPNfNhecgBVUMjsq3KuWbpeHpIXbO3U4H++WUD/oIFnwkg4WAPDtJfs3dc+Wp5B/8At6dZ+PUg/BQjr/8Axmv9Y662T81LP6iPSIixC6G0hERegIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgHfusOce59Hwm6wx/6lH3/u0azGTgZWGOP6djOEvWDpHho+RRjmJ6Z9PGB+EhYbcH/A7n6kvcZjby4tetV/1IfmRy6ikHNklVEMvXGVQkhryWHpnplTYZB3JXF+EdyTg0fQilGe6qY5ObuV86F4z3VRDKM91Twi3lA+jFKCOXPZThmL13uAB7L5stbDSND5H9wqKru81WOUHDfueXvhUJNIpq3lJ+B9iv1BT0g9HEcv8s9l8mpraqrcTPMSCfm56D3Kja7lySOYnzd3UxrzgdB2VtJsqqjBdxPjwwAtYOymiRxGcBU7XnA6DspjXnA6DsraUcn1hE8dRlTWvIaB7lTtecDoOymNecDoOyonwVDXnA6DspjXnA6DsqdrzgdB2UxrzgdB2XjSZSlEqGvOB0HZex1GVIa84HQdlMbKMDOO3tVCUMHwVDXkNA9y9ibA8lTTVLKWH5TUvbHEB6z3HGF8K76+tsUwp7Gw1LnHHpnDDGu9gHd2fYF8wo1KzXChCjVm+UeRdL6ylp2tfV1LIm8pOXnAOPYfb7lI00NSbmXRli0BYZah+fttU6M8kbfvnDIwPeT18grg2e4Xtb7oSs1Xr+oktdqcWvihLcSzt74a0g8jfeQT+NbLaZ0TpnRFoZZdK2xlJTjBLWNBLz7XHHrH49B5YW6aJsi5vGqty+GH4v4Gpa5uew0nNKh/tKv/APVfEx5tdw8WHRbWX3UkrLrdmu52ve3ENOT1PICMnr7VkZkBuEjaenDp5JHhrGsHz/YAR3+AX1bbY73qa5w2u0UElTUPdhrGHJ5fa7PYD2nus77XbMWjRLWXatEc91LBl/KOWnz84MH19VNW29oxrpULWHBTXWX/AJ1ZDWvbmqObrXMuKb6Lu/si2tneHtlCY9Ua+pQ+pOH09AQMQ+fM7Hn7vJZejhYGCMNw3GA0dAFB7QWkPDgxvXOc8x/GsccTfFRtbwsaGGr9w7oTU1L3Q2a0UuHVNxnAz6ONmfLoXPPqtHc5IBmvRdDpWkY21pDMny6Zb/8APwIo1fWnKMrq8niMeeW8JIunc3dPROz+jq3Xu4d+p7Xa6CPmnqKh+CT9y1oHVznHoGjJJ8ly/wCM7jo1pxUXWTT1jE9p0dTS5pbU1/LNWEHpLUYJGRgEM7NPtPVWXxQ8XG5fFdq4X3V9Z8jtdK932IslPI4QUrCThxGfXlLcBzz38g0dFjOJx5uUEY5OUBrQAOvcYU/bU2XT0qKubxKVXuXVR/v6e45U372k3OvTlY2D4Lfo33z+C9BWRvdI0ZcfLz8vZ7/9sYVREfWwGgD2DyVLE9xOT3z7FUw9XArfMvGCIGXhtldPk9VLb53DkcOZjfer3glecF2MnusT2atfR3eKoaQA1zQVlOnqWyASNxhwyFiryCjPkZSznmHqPpQTds+xVMM7s9AO6+bC8Z7qrhlA6KwkkX5u14dB5tmbs4/8p5P5PTrYBvrd/Ja++HG4v2WuxP8Ayok/k9Otgo/NQdr6/wDWa/1n/Q662Pz2lZ/UR6REWHNqQREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAd2PwWt/ix1s9u4Ctc1lLMY3sZQEOB/94U3RbIO7H4LWfxdxnw/NenPaGgP/wDkKZYrXEno9wn9CXuM/tKMZbnsU+jq0/zI5l6K1TDqywQXhkkYPIG1TGd2SAdR38z1HuX2I3Oa0E98dVr1tfr+fRF2dLUuMlHUlvyqMk+ofJ4HtAWerZdaK8Ukdfa6xktO9gPpe/cdj7CuOLq1lRlldDvrVtOdnW5LzWfRilLTl5woy3FsI+1EE+9fPqK54JjaG9OnRU7C57y5x6krHSeDEqjjqVktRLO4ue/uc4UxnzB8FSh5AwprZnhoGB2VpNM+pJFU3sPgoh5AwpbZHFoOB2XsdRlU8FCUWie3q0H3L0HkDCkiZwGAB0Uxpy0E+YVPqfGETmvOB0HZTGvOB0HZU4eQMKYx0mATHlvm4O7f7fFUZUnnkfLin0RUNecDoOymB72uax8RAcAQ4dV8a8az03Y2/py4NDh/wLHcz3fQOg+lWxed4ZZXfJdN250XN3lm+cPw4C9p21ap0RcUbCvc/srHr5GQayupqGMyzytZG35z3nGPoVuXjc2ihlNLY6d87sdJpmcsf0dckfUrFNde9RVrKSqq6qqqZ5eWKnjYXFxJ6ANHdbIcP/AFqbUsFNqreV81rteWyU1lY4CpmJGQJXYxCP2vV3wKzGm6Bc39RQpxcn+C+0tdVr6Rt638rf1En3LvfoSMVaE0PudvffhaNP22WrPP9ukaeWnpQfN7z0YPYD1PvW2OyHB9oraplNetTNivN9Y0O9PJF9opnY6iJpHrdezj39yyxpXRGltv7JFpzSNhgt1LA0AQwR8uXAYLnHu5x8yepKqZoH1DnRte4k4cSXfN+GfxKUdH2hZaY1OquOp+C9RC+4t/X+st0bZeSpejq/WymlawTc7jgFg5unKMgeYHbHu7dl9TRm3+oNfXBtNaaX7Sxw+VVZHLHGPcfuifvR2Vybc7R3DWM7LzdWvp7c12T6RuHTn2gHsCs1Wex26xW6O2WqmbTwxjo2MDv5k+9Sdo22at7itW82Hh3sibVNcjap06POb6vuR8rQu3Wn9CW0Ulqg5pngfKapwHPIfiOw9y+493J0e8Dr29oUHTiN3JkDuAXDzH4/j2WlviTeLJorhdpK7aTZyto73uDLGYaiTm5qWwg/dzkEc8p68sTSCO7iPVD5W0XRLm/qQs7Knl+jol4sjrWdbtNLt5Xd7UwvT1b8F6TJfHd4hm1fBfpdtuqZY7xra5UxNh0tTzASYJLRUTkZ9HED26czz6rR84t5S7k76bq79a8qt0N3r66vvdVD6FnrH0NFDkkQQMyRHG3PlkuPrOLnEuNi01dq7X2q63dXdK8VV21BcpnVFRcLhIXSuc/JP9rjOAB0aOgwOi+o1oPK/lDSAOjRgLoXbe0rHbtHil59V9ZeHoRyfvrfl/ues7en5lBdI+PpfiVkV2q4jlwY/+2H5FU02oBzfbKfH9qOi+YTkk+1TAXFoHMR08ltPDEjZci4KS9Uchw4OZ73L6dNK2bBge12R0VoRkt6uPN7nKrorjU0TueJwPsDuwVOVNjiRdjAeYgnB5hkj3LI+ma41tmppQQXYDH49wWLLPdoa6P7Y8MkwMtPmfcr+26qyLXLTv+dHKXNB95WPvIcsl3aTxUwXZC882AqqB4x1KoIpQHnB81UQyAnOVjOFGT4mby+G08ybKXfP/ACpl/k9MthWdB8Vrv4ahzsnePdqmXH/VqYrYlvYfBQTuL/jlf6zOvtjN/wCELL6i95FERYY2wIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAg8kBa0+Lz6vh96/wAeUFD/AN4Uy2Vd63mtaPF7bM7w+twTCzm5KWje4AdcCvpjn6MLF6z/AMJr/Ul7jYNpfOmx/nU/zo4atljdGz0jQ5zfuz3x7PgF9zSG4180fV8lFO59E7rNSuyWE+Z75yrU9M53rNPQ9lNike7GHFvbt5rlWpCFWPDJZR+oNWhTuIcNSOUZ50luzpTUDWxyVYpJiAPQ1HqBx9jSemPpz7ldkM8Zw9zmjnALR6QZI93tWsJke4hxcegx0JGfj7V9K0as1FYgDZrtNTdO0bzy/wCSen4Fi6ulwl+wzWLvbkKn+5lj1myYe1xwwOJz2LcfjK9xvBPK7pjyWC6HercOma30t4ZOA0dJqdmP+yAvox7+ayY0Zt9tccdSYZOv1PWPnplz4IxMtv367l7TNEb3OPKyRpx5BvUfhU9pf83lOfe3Cwe7fHXM5JjqaaHPlFTjp9eVIqdzda3EYmv8rM9zA1sZ+toBVL9F1T4W3ryfVpGdp544Gc8z2RDzdLKAPrGR9a+Xc9ydIWZuKm7xyub0LKY+k6/EZCwXU3W5XFxNfcJpjnq6WVzifpJUWODGhrScY9nX6+6qR0qmuryXFPbsIf7yWTKV130pnAt0/Z3POfn1TsfU0d/rCty5biaqvWWVNzfFG4kmCH1G/DAP4yfirWp3+t62cH5ji09D7D1yfirm2z2v3C3f1OzR+22nJ7pcX4c+KCMkQt83vPZjf2ziB9PRXFLTYSmoU4Zf4lxK10zTaEqlTEYx5tyf9WUbJmiTldyt5xzOka4+p73Dzysr8PXCduvxBVcdVpyzCksgfia/XEOigx7GdzI73AY94WzfDX4Y2j9ER0+rN+ayG817XNkis9O0/I6aTv6xA5p3Z+A9y2nhtlFbKOO2UNvjpaeFgYyljiaxrA0YDSGjHTtjsPJbxpGyq1ZqpevhX0e/7fAhbdva7a2/FbaOlOS5cbXJepd5iPYThF2s2Dpo6u12wXK9iMNnvdbGDIHY9b0TR0iaTnoMnHdzj1WTpGuHM4vOXfPx0z7unb6FVuYS4nl7n2KdZdNXXU1yFutdI55HWSQ9GsHtJ8v863610+jbRVG2p48EiB9Q1W9v6krm8qOUn1bZ8iKjkqJm0dPGZHv6RtiGf8XH+dZG2+2Zga+G86ujzK1ofBQAjDPe72lXNojbqz6RhEzwJqt4HNO9oz27D2D8KuYMLegyPafat40nb1OlJVrnnLuXcviabqGszqp06L5eJ5hiZCwRxtA5RgNAwAPcvDqhsQd6SRjWRjLi445W9fo8icr5W4G4WjNqtIXHcDcLUtFaLPaqV1RX19fMI44YxgcxPnkkAAZJJAAyQFxy8Szxf9ZcUlRV7NbCT1untADLa6pc0xVl+b5mQ5+1U7uzY+7h6zzghgkzb22tQ3Fc+Tt44gv2pd0f7+gj/cG5NP2/b+UrSzN9I97/ALekzf4lPjPx29lx2H4ONSRTVXLLT3zXlJIHMhHMWvjoJM4LhgtM/wBz3j68rhzt0LY57vXO1Te5JZjzuIMzuYvJOS5xcMk5Oc9Dnr3VvaPsFTqG5GB0bmw07g+dw7g+4nPX2nusp0cFPQwMpqSnZHEyMNbG0dAAMBdDaPoGn7dtFRtucn+1J9X/AOeBy7u/deo7gus1pcu6K6RROYXEMcXH1WgYHZTvSE9cBSW9h8F7jeXZB8llO/JoTin1Jw6jKiHkDC8t7D4KK8KLSRNa84HQdlMa84HQdlJb2HwUxvYfBMlIqIZ5IntlYfWb2PsWQdqL8LhUy0czwJvQcx9hA6fWsdN7D4L7GiayWivsL4ncpeC1xHmMqhXpqcGypSm6dRGbI3jm7+aqoXjPdfHtVxjuEPp2uGQPXA9vnhV0E4IBB7rAyjgzcZKXQ3y8M882yV4/dTL/ACWmWxTemB8Vrj4Y8hfsbeXHHTVUw6f3rTLY4d/rUC7i/wCOV/rM7B2P80LL6i95FERYY2wIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiA8kDqcrGHGTtnNvDwta924oGekrLvpatgt7D1Hyj0LnQ593pGt/26rJ5GMlS5YWPjc14JD+4Pl8FQuqCuLedKXSSa9qLnT7ydhe0rmH7UJRkv+15XuPzH/bWgkwuZyPIfC8Yewew+8eamxTMLssB5fIHutlvFh4R7rwtcUlyvVuixpfW9VPdLDO1oDY5HyF1TS5ADQY3vBA6YjezJJyRrDC7PVucHtnuuW9Rsa2nXs7epHDi2v/PsP1D23rlpubQqGp2sk41Ip+p96fqZXekJ9imRvLuhAVO15DQPcvcUjs9h3WNaTMw+fUrWvIaB7lMbK3AyfJUomeBjAXoSOIzgL4Kcosro+gDge6nwy9e6o4ZnloBA7KfDHI8Nc2Nx534bgdznGPYBnpnP0KlJLOMFCfClmTwVsUjs9lW2q23G+XGC0WigqKusqJhHFSUsDpJZHO+aGtHVxPsGfb0WwPCx4ZW/O/k1Nf8AV9tm0bpqQteLhd6cipqmHriGnyHAEdnv5Qe4BXQ/h54NdiuGGiadvNKskub4RHVX24MbLWTdOvrkeoCRktYGtz5LY9L2jqOp4nUXBDxfX7ERBvDta25tzioW0vL11+7F+avrS6fYss0w4ZfCj13rhkOr+ISsfp+1vETmWSAtdWzNwCBI7BELT7BzO+BW9u3O0G3ey2no9I7a6TpbTRwn1o4I8ukcBjmkc7Lnu6d3ZPwV3uaOcublpwQS09T7evfqqWWCNjQxgw1owB7ApF03QNO0mnilDMvpPqcybk3vuHddfjvKmId0I8or4/bkpZQ4d5HHpg9cZH0KmkawERPfynHRx7H3fFV4oqipnbTU0Zlkk+bFGMn4kq9NH7XMpQ2v1GGyPIDm0/3LPj71mrewr3k/MXrfcahWu6drDznz8O8tnSW3Nz1FI2pqyaej6H0p+c8ewe/3rJljsFt0/bmUNuiLA1oyTgucfMuOOpKq4oI4WCKNgaGjDWjsAoGYQg87XH2crc5648luFhp1vZR81Zl3s1u7vq13Lm8R8CHMYw4lnRo6OPbKxvxT8XOynB3txNuVvVqllHBhzbdbYMSVlymAz6GniyC93bJJDWgguc0dRiTxC/FH2a4G9OT2KGSHUm4VTTF9p0nSVWPQ5HSeqeMiGMdw3Be/yby8z2cRuIXiZ3h4rdyqvdfejVc10uVSz0VPGWuZBRU+SWwQRFxEMYz80EknLnlzi5xk7aux73Xqir11wUfHvl6viRvujeVpoUHQoefW8O6PrMqcd/iR71cc+rGnVFQ+x6St9WZtP6RoZnFkbzkemmc0gzTBp5Q84Dcu5GsDnA4Eoad1RVRUYgMhkf6jBkB2TntnHx9qooM9eSR3MWcpPTKv7a7TfKDqGpjJ5nFtO13ZhHcj4roKysbPSrWNC2gowXcve/FnPOs6pc3c5XNzNym/H+hdWlrKzTlrZSA88zgHVEjsZc89+2OmV9Zpy0H3KQCCMjt5Ka15DQPcqb5yyaXKTnLiZMDyBhe2Hl6jzUsdRle29h8F4W8kTmvOB0HZex1GVJjeXZB8lNb2HwQonoPIGFMa84HQdlKXtvYfBClhE6N5d0ICr7FLyXaEju3t9K+cz1QCFVWkltzhf984D6yQvmR8R5vmZK03cfkdeIJn4Y/q3r3V2wOacHPf2LHkM2MDPUHIPmry0/cG1lsbK53rsAaQFhq8MdDJ29TufU6B+F+efYq9Z8tWTD/9rTLZHt1+K1q8Lh5dsTeQf+Vs38lplsqe30Fc97i/45X+szszYvPaFln6CIjqMojew+CLDG2hERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQEHN5sdexQsDhglRRB1MT8WPC1tlxb7UXHaLcm3h0c3263VkbW+noKloPo6mIkHDmlzh2Ic1zmuBa4hcN+Ljgx3s4N9fv0huVYZH0FRM/7Dakghd8ir4g44If2ZJy4JiJ5hnzA5j+haenjDzIBh3NnmB6/DPsVu7i7aaA3U0xVaL3H0nQXq1VrC2pt9ypmzRSewlrgRkdwe4PXv1Wl7m2ta66uOPm1UsJ/wBGS52adq2r7ArOk4+VtpvMoPufjF9z9HRn5tpJ3NIZE0ucW5DOXB7+/vgKayQekIYctz0JGMhdWd+/AM2V1bcJ71sNuLctJOmfzfYu4U5uFHGR5MyWysB++c96wFdfAS4u6OvdDZNf7fVMAcfRl1zrI3FuehLfkpDenlzHHvUR3ezdetanCqXEvFczrLSu2vs81SipyuvIv6M00/ak1+JpRzn2BToz6RgbG0+k9jxhpHuIyt69E+Adv3WVzRuVvJpe1U3P6xs1PUVz3D2YkbAGn3nI9y2t4dfCY4VuH6sptRXjTcmr75TBrmXPUrY5o4pAB60cDWiJvXq0uD3t+/PdfVnsrWLp/wC0h5NeL+CLDX+3LY+l0m7Wq7ifcoJ4+2Twkc6uF3w5OJLicFHfbNpo2DTNRyvOpL8wxRyREA80MRw+YkEFpA9G778Loxww+Gvw58M7KfUP2G/NJqVrWuZf7xGyQxOx1fTx45IfcQC8DoXu7rZAwQQwmnpowyLuxg7N+A/293VSpCXymWQ8xxj1lvul7S07TUptcc13v+iObd3dq26N2OVLj8jRf7kHjl/ql1f4L0FI+IRs9G13TlA6tafp7dPo6ezCkPjaenYDsAqyaNuM5KkRU09TKYYIy95PqhoWenHKxFcyOMx73gopo+UkteGjz5lU2TSty1DPinjLYR86d3QfQrjsOhBK5tbex1b82Nvb6cq6IKWCGJsUUTWsaByhowAr+10qU3x1XheBZXGo8C4Ic/SfO07pW16dhxSx5kcB6SV2CSfyL6bGBp5m/hKEEdB18sq09598Nq+HrQFdudvHrqg09Y7eP0xX3BxALiMhjGAF0rzjDWMBc49ACRhbHb2+MUqMeb5YXUwtaslF1KsuXiy55K5lPzuncxkcbC98jnYAAJzn2Y/27Lml4lPjlWHQUldsbwV3Omu1/kcYLtrgNE1Hb+hBFL1xPIMY9J1jb5B55uTVvxIvGj3T4u31u0eyb63Ru3UvpGzku5LhfIz6uJ3MceSLHX0LSM82Hl4AxpJG8nLHN6ZOR8fIkeXu7KaNo9nL4o3Wqrn1UP8A/XwIk3Pvx8LttOfXk5fD4n177qfUes75V6q1ZqCsudzuVQ6ouFzrqt809TK9xc575HEuc4uJPNnPvUuE4OAOnxJ/GqSNxaAf8yqoDnBPnhTJCMacFCCwl0SIiruU5uU+bfU+tpi1TXm7w2ukaTJK/Lj5Bvmsw0dLFbaNlvphiOJjWNz3OBjJ9581Zu0VlMNFJqGVhBkkMcLj5Y7q9nEOcS3sT0VrXnxGqalW8rNwz0JgkJGcBTGvOB0HZSW9h8FMb2HwVsYpdCob1aD7l6DyBhS2vIaB7l7HUZQ+MExh5eo81Na84HQdlJb2HwXoPIGEKWETx1GVEPIGF5b1aD7lFCg+pNa84HQdlVWpx+XwH/2jf4RVG3sPgqu0/wBWxn71wI+g5XzI8wkXiC1pzzL7mjqwQzuoy7Icefr71bfpi7qc9fYq20VxpbjFOCMnDcHthWFSGepcUpKLyzph4Wry7YW9SHuNWz/yWmWywOQfpWs3hXP9LsBeneR1bN2/vWmWzI6Aj4rnPcvLXrj6zO09iPOz7L6iPTew+CI3sPgiwhtwREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQEuZgI7lU0sbQO57earHNDhgqRND7MqlOLZ9QbXJFDNHhpaGDDu/x9vxXh7XSD15HdTnGeg/29vdVLxnofJSJQG5wrWaZcRk8lHNFyvLwD2x3P1+9U0jXNGD63xVfKHEZx3VM9pecBh+pWzhKXQuE1goHtDjg9PgpL4CCTyk/BfXprFU1LuZ7SxpOQT5hfUoNP0dIA9xc8kdQ7GM/UqcbOc3zPZXEKawj4Fu01U3HEkoMcZ6gnuQritlkoLbG30EWXco9c9yqn0ePVaMAdh5L20cowsjRtKdJ57yzq16lXqzyQz5pHf2rwZ3AYaABnAOCcKi1DqOz6WtVVqPUt3ordbqCnknrq2uqGxRQxsBc6R8jyGsa0DJJOB5kY68qvEl/ogq2211x2b4FKxk1SHuguO4tTTc0cPdpNBE4+uR1Hp3twM5YyQYetj0bQNS164VG0g34vuXrZhNT1iy0ii6lxL1Lvf2G5HH94qPD1wFaemoNRXKO/wCtJos2zRtrqmmoBLctlqnDIpYu2C7139eRr8HHDXi845eIXjg1+db726sfJTQyudZ9OUZdHQWtjjnkijznPYF7y57sDJIAAxFftT6k1nqKo1fq6+1VyutZO6asuFbVOmmmkccuc6RxLnEnu7OT06qVA53TJJPtPcroPauydN25Hykkqlb6T7vUu4hfcW6b/WZeTj5tP6PxZVwBzTmN5Yc55mgZz7eoVXDjPQY7dFRwuOQqqFxW5ZZps+bKuP1uhVZRRTVNTDSUzcukeGn6/wAiooXHIVy7YUArNX03pAS2Hmld9AOPo7KnJvgbLG5l5Oi5GVbVQQ2y1RWynz6ONjQM98gYz8T5qsByM4UphLmBx8x5L0HkDCx+WaVUfFLLJgeQMKY15wOg7KUOoyvbew+C8KBOa84HQdlOb1aD7lTt7D4KbHI4+rgdEPMImh5AwvY6jKlr23sPghSkic15DQPcvY6jKkB5AwpzerQfchQwj0HkDCrbL61wjYezgSVQqvsgArWyebGdPpXzI+D70UokALvMeSniTlex4d1YchfPjk5QCCOyntly0Hm8lQwmfa6HTzwlqo1fDjdJsjrqmXm+PySlW0YznqPPqtTfB6fLJw0XkSAho1lUBhx84fJKQ/jyPoW2QDi05PXzwubdz8twXP12drbC57OsvqI9Dt0RQaMN6qKwRt4REQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBQc0OGCoogJDqNpcTk9T7VKktr3k+v08lWIvl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KVCDf1V8DJ0tc1mjT4KdzUS8FOSXvPkaS0BonQVtbZtE6Ut1opGEltLbaNkEY/xWABfVbDG1no2tw3AAHsC9Ir2FKnSiowSS8FyMdUq1K03Ocm2+9vLJFTbaKtpnUdZTtlheCHxSDLXA9wR5j3LGlTwVcK9Vd/s3Jsbp0T85e4MtzWscScnLBhp+kLKSKjcWNndtOvTjLHik/eXFrqF9ZZ+T1ZQz14ZNZ9jKGx6bsembXBY9OWuCgoqWMR0tHSQtjihYBgNa1oAAHsHtVby+/wAlFFcRhGEVGKwkW0pSnJyk8thERfR8hERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAUHNDhgqKIDm34hHBDuTprdS67ybb6Yqr1p+91ElZcIrfCXz0VU5xdIXMHrFjnEvDwMDrzYADjYPA/uVw5bV6uvMHEvoyiuVJU07GW2WusrattNI0kuJjc0ljnE98Z6DrnqurktOyZwL2+Zzg9D5dfb/t7AqGXSWnZ5TUVFmppJCer3wgnA7Dt5KMrns5t466tTsqii8tuMo8Ucv0Eu23axd1dsvRtRoucVFRU4zcJ4XTufQ1N3G3J3S48KWLZvh90jdNOaCqHsGodYXihdSiqpAfXgp2nBLXYx06kdCGtOTtFtftrpjaPQds260bR+gt9spGwxDGHOwOr3HHznHJJ9pK+62CCBgjjjw32YGM+1e2kZzjt5rc7DSvkdd3VefHVksOXRJLooruRH2p607y1hZ29NU6EW2o5y5SfWUpPm5eHguh7aA1oa0YAHQKKDt0RZowYREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQEOT3+ajgexEXmFnIIFjTn3oGAdioovQEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREB//2Q=="
                    alt="Holiday Travelers Inc. Logo" class="w-full h-auto object-contain" />
            </div>

        </div>

        <!-- Profile Grid Layout -->
        <div class="flex flex-col md:flex-row items-center justify-center gap-6 sm:gap-8 md:gap-16 w-full my-2 sm:my-4">

            <!-- Left Column: [USER ICON] with Animated Spread Scanner -->
            <div class="flex flex-col items-center">
                <div id="scanner-container"
                    class="relative pointer-events-none w-[min(82vw,320px)] h-[min(82vw,320px)] max-w-[320px] flex items-center justify-center group select-none">

                    <!-- SVG Spread Scanner Rings around double circular lines -->
                    <svg id="spread-svg" class="absolute inset-0 w-full h-full overflow-visible" viewBox="0 0 320 320"
                        xmlns="http://www.w3.org/2000/svg">

                        <!-- 1. SPREAD SCANNER RIPPLE RINGS (Expanding Outward Waves) -->
                        <circle class="spread-ring-1" cx="160" cy="160" r="142" fill="none" stroke="#6FA9E6"
                            opacity="0.6" />
                        <circle class="spread-ring-2" cx="160" cy="160" r="142" fill="none" stroke="#F59B45"
                            opacity="0.6" />
                        <circle class="spread-ring-3" cx="160" cy="160" r="142" fill="none" stroke="#163B6D"
                            opacity="0.6" />

                        <!-- 2. TWO CIRCLE LINES MATCHING THE REFERENCE IMAGE -->
                        <!-- Outer Circle Line -->
                        <circle id="outer-line" cx="160" cy="160" r="148" fill="none" stroke="#163B6D"
                            stroke-width="2.5" />

                        <!-- Inner Circle Line (Forming the signature double-circle frame) -->
                        <circle id="inner-line" cx="160" cy="160" r="138" fill="none" stroke="#163B6D"
                            stroke-width="1.8" />

                        <!-- 3. ROTATING RADAR / TECH TICK ACCENTS ON DOUBLE RINGS -->
                        <g class="rotate-scan">
                            <circle cx="160" cy="160" r="143" fill="none" stroke="#F59B45" stroke-width="3"
                                stroke-dasharray="20 160" stroke-linecap="round" />
                        </g>

                        <g class="rotate-scan-reverse">
                            <circle cx="160" cy="160" r="133" fill="none" stroke="#6FA9E6" stroke-width="2"
                                stroke-dasharray="8 80 12 60" opacity="0.75" />
                        </g>

                        <!-- Highlighting Target Arc for Scanner Focus -->
                        <circle id="scanner-laser" cx="160" cy="160" r="148" fill="none" stroke="#F59B45"
                            stroke-width="4" stroke-dasharray="70 200" stroke-linecap="round"
                            class="rotate-scan animate-glow" filter="url(#neonGlow)" />
                    </svg>

                    <!-- Inner Circle Container for Avatar / [USER ICON] Placeholder -->
                    <div
                        class="relative w-[82%] aspect-square rounded-full overflow-hidden flex items-center justify-center bg-white shadow-inner">

                        <!-- User Icon / Photo Display -->
                        <div id="avatar-display"
                            class="absolute inset-0 z-0 w-full h-full flex items-center justify-center transition-all duration-300">

                            <!-- Placeholder Text Icon Graphic matching image -->
                            <div id="default-icon" class="flex flex-col items-center justify-center">
                                <svg class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 text-slate-400 mb-2" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <!-- Exact text label from reference image -->
                                <span
                                    class="font-body text-lg sm:text-xl md:text-2xl font-normal text-black tracking-wide">
                                    [USER ICON]
                                </span>
                            </div>

                            <!-- Employee Photo (Revealed when scanned) -->
                            <img id="employee-photo" src="" alt="Employee Photo"
                                class="hidden absolute inset-0 z-20 w-full h-full max-w-none object-cover object-center rounded-full" />
                        </div>

                    </div>
                </div>

            </div>

            <!-- Right Column: Employee Details Section (Matches exact labels) -->
            <div class="flex flex-col space-y-4 sm:space-y-5 md:space-y-6 w-full max-w-md pt-1 md:pt-0 text-left">

                <!-- EMPLOYEE NAME (Header) -->
                <div class="border-b-2 border-primary/20 pb-2">
                    <span
                        class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest block mb-1">FIELD:
                        NAME</span>
                    <h2 id="field-name"
                        class="font-heading font-extrabold text-xl sm:text-2xl md:text-3xl text-black tracking-wide uppercase transition-colors duration-300 break-words">
                        EMPLOYEE NAME
                    </h2>
                </div>

                <!-- Detail Fields -->
                <div class="space-y-3 sm:space-y-4 text-black font-body text-base sm:text-lg md:text-xl">

                    <div class="border-b border-slate-200 pb-2.5">
                        <span class="text-xs font-mono text-slate-400 uppercase block tracking-wider mb-0.5">Employee
                            ID</span>
                        <p id="field-employee-id"
                            class="font-medium text-slate-900 transition-all break-words leading-snug">Employee ID</p>
                    </div>

                    <div class="border-b border-slate-200 pb-2.5">
                        <span
                            class="text-xs font-mono text-slate-400 uppercase block tracking-wider mb-0.5">Birthday</span>
                        <p id="field-birthday"
                            class="font-medium text-slate-900 transition-all break-words leading-snug">Birthday</p>
                    </div>

                    <div class="border-b border-slate-200 pb-2.5">
                        <span
                            class="text-xs font-mono text-slate-400 uppercase block tracking-wider mb-0.5">Address</span>
                        <p id="field-address"
                            class="font-medium text-slate-900 transition-all break-words leading-snug">Address</p>
                    </div>

                    <div class="border-b border-slate-200 pb-2.5">
                        <span class="text-xs font-mono text-slate-400 uppercase block tracking-wider mb-0.5">Date of
                            being employed</span>
                        <p id="field-date" class="font-medium text-slate-900 transition-all break-words leading-snug">
                            Date of being employed</p>
                    </div>

                    <div class="pb-1">
                        <span class="text-xs font-mono text-slate-400 uppercase block tracking-wider mb-0.5">Position,
                            Role</span>
                        <p id="field-position"
                            class="font-medium text-slate-900 transition-all break-words leading-snug">Position, Role
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </main>



    <!-- JavaScript Logic -->
    <script>
        // Employee data used by the kiosk.
        // The RFID scan is matched against employee_id. Add your real employees here
        // (or replace this object later with a database/API response).
        const mockProfiles = {
            raw: {
                employee_id: "",
                name: "EMPLOYEE NAME",
                birthday: "Birthday",
                address: "Address",
                date: "Date of being employed",
                position: "Position, Role",
                photo: ""
            },
            user1: {
                employee_id: "26011001",
                name: "Jhonie Faingason",
                birthday: "March 31, 2005",
                address: "00181 Purbest Compound, Area 5 Brgy. Bagong Silangan QC",
                date: "August 21, 2026(1 month)",
                position: "Admin Officer",
                photo: "assets/images/admin.jfif"
            },
            user2: {
                employee_id: "26011002",
                name: "Nicole Mayo",
                birthday: "January 17, 2005",
                address: "Manggahan Commonwealth, Brgy Commonwealth QC",
                date: "August 22, 2026 (1 month)",
                position: "Office Manager",
                photo: "assets/images/anne.jpg"
            },
            user3: {
                employee_id: "26011003",
                name: "Mark Roel Repardas",
                birthday: "September 17, 2005",
                address: "25-17 Sampaguita, Caloocan, Metro Manila",
                date: "August 22, 2026 (1 month)",
                position: "Office Assistant",
                photo: "assets/images/mark.jpg"
            }
        };

        const profilesByEmployeeId = Object.values(mockProfiles).reduce((lookup, profile) => {
            if (profile.employee_id) lookup[String(profile.employee_id)] = profile;
            return lookup;
        }, {});

        let isScanning = false;

        // Web Audio Synthesizer for Touch / Biometric Sound Effects
        function playAudioFeedback(type) {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.connect(gain);
                gain.connect(ctx.destination);

                if (type === 'scan') {
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(440, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.25);
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.25);
                } else if (type === 'success') {
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
                    osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.1); // E5
                    osc.frequency.setValueAtTime(783.99, ctx.currentTime + 0.2); // G5
                    gain.gain.setValueAtTime(0.1, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.4);
                }
            } catch (e) {
                // Audio context blocked or unsupported
            }
        }

        // Live Clock Updates
        function updateClock() {
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                const now = new Date();
                clockEl.innerText = now.toLocaleTimeString('en-US', { hour12: true });
            }
        }

        // Trigger Biometric Scan Action with Spread Scanner Animation
        function triggerBiometricScan() {
            if (isScanning) return;

            isScanning = true;
            playAudioFeedback('scan');

            const container = document.getElementById('scanner-container');
            const statusDot = document.getElementById('scan-status-dot');
            const statusText = document.getElementById('scan-status-text');
            const outerLine = document.getElementById('outer-line');
            const innerLine = document.getElementById('inner-line');

            // Add active scanning class to accelerate spread ripples
            container.classList.add('scanning-active');

            // UI Scan State Update
            statusDot.className = "w-2.5 h-2.5 rounded-full bg-secondary animate-ping";
            statusText.innerText = "SPREAD BIOMETRIC SCAN IN PROGRESS...";
            statusText.className = "text-xs font-mono font-bold tracking-wider text-secondary uppercase";

            // Highlight circle lines during scan
            outerLine.setAttribute('stroke', '#F59B45');
            innerLine.setAttribute('stroke', '#6FA9E6');

            // Cycle through demo profiles
            const currentVal = document.getElementById('profile-selector').value;
            const nextKey = (currentVal === 'raw' || currentVal === 'user3') ? 'user1' : (currentVal === 'user1' ? 'user2' : 'user3');

            setTimeout(() => {
                isScanning = false;
                playAudioFeedback('success');

                container.classList.remove('scanning-active');

                // Update UI state to Verified
                statusDot.className = "w-2.5 h-2.5 rounded-full bg-success";
                statusText.innerText = "BIOMETRIC IDENTITY VERIFIED ✓";
                statusText.className = "text-xs font-mono font-bold tracking-wider text-success uppercase";

                // Revert circle lines
                outerLine.setAttribute('stroke', '#163B6D');
                innerLine.setAttribute('stroke', '#163B6D');

                // Load next profile data
                document.getElementById('profile-selector').value = nextKey;
                loadSelectedProfile(nextKey);

            }, 2000);
        }

        // Load specific profile details into fields
        function loadSelectedProfile(key) {
            const profile = mockProfiles[key] || mockProfiles.raw;

            document.getElementById('field-employee-id').innerText = profile.employee_id || '—';
            document.getElementById('field-name').innerText = profile.name;
            document.getElementById('field-birthday').innerText = profile.birthday;
            document.getElementById('field-address').innerText = profile.address;
            document.getElementById('field-date').innerText = profile.date;
            document.getElementById('field-position').innerText = profile.position;

            const defaultIcon = document.getElementById('default-icon');
            const photoEl = document.getElementById('employee-photo');

            if (profile.photo) {
                photoEl.src = profile.photo;
                photoEl.classList.remove('hidden');
                defaultIcon.classList.add('opacity-0');
            } else {
                photoEl.classList.add('hidden');
                defaultIcon.classList.remove('opacity-0');
            }
        }

        // Reset to default layout state matching exact template image
        function resetToPlaceholder() {
            isScanning = false;
            document.getElementById('profile-selector').value = 'raw';
            loadSelectedProfile('raw');

            const statusDot = document.getElementById('scan-status-dot');
            const statusText = document.getElementById('scan-status-text');
            statusDot.className = "w-2.5 h-2.5 rounded-full bg-secondary animate-pulse";
            statusText.innerText = "SCANNER READY";
            statusText.className = "text-xs font-mono font-bold tracking-wider text-slate-700 uppercase";

            document.getElementById('outer-line').setAttribute('stroke', '#163B6D');
            document.getElementById('inner-line').setAttribute('stroke', '#163B6D');
        }

        // Load the employee profile passed from the scanner page.
        function loadScannedEmployee() {
            const params = new URLSearchParams(window.location.search);
            let scannedId = params.get('employee_id') || '';

            if (!scannedId) {
                try {
                    scannedId = sessionStorage.getItem('scannedEmployeeId') || '';
                } catch (e) {
                    scannedId = '';
                }
            }

            scannedId = String(scannedId).trim();

            if (!scannedId) {
                resetToPlaceholder();
                return;
            }

            const profile = profilesByEmployeeId[scannedId];
            const profileKey = Object.keys(mockProfiles).find(
                key => mockProfiles[key].employee_id === scannedId
            );

            if (profile && profileKey) {
                loadSelectedProfile(profileKey);

                const statusDot = document.getElementById('scan-status-dot');
                const statusText = document.getElementById('scan-status-text');
                if (statusDot) statusDot.className = "w-2.5 h-2.5 rounded-full bg-success";
                if (statusText) {
                    statusText.innerText = `IDENTITY VERIFIED • ${scannedId}`;
                    statusText.className = "text-xs font-mono font-bold tracking-wider text-success uppercase";
                }
            } else {
                // Unknown employee ID: still show the scanned ID instead of silently
                // displaying the wrong person's information.
                document.getElementById('field-employee-id').innerText = scannedId;
                document.getElementById('field-name').innerText = 'EMPLOYEE NOT FOUND';
                document.getElementById('field-birthday').innerText = '—';
                document.getElementById('field-address').innerText = '—';
                document.getElementById('field-date').innerText = '—';
                document.getElementById('field-position').innerText = '—';

                document.getElementById('employee-photo').classList.add('hidden');
                document.getElementById('default-icon').classList.remove('opacity-0');

                const statusDot = document.getElementById('scan-status-dot');
                const statusText = document.getElementById('scan-status-text');
                if (statusDot) statusDot.className = "w-2.5 h-2.5 rounded-full bg-error";
                if (statusText) {
                    statusText.innerText = `ID NOT REGISTERED • ${scannedId}`;
                    statusText.className = "text-xs font-mono font-bold tracking-wider text-error uppercase";
                }
            }

            // Keep the employee profile visible for 3 seconds after the RFID scan,
            // then automatically return to the kiosk scanner screen.
            setTimeout(() => {
                try {
                    sessionStorage.removeItem('scannedEmployeeId');
                } catch (e) {
                    // Ignore storage errors.
                }

                const scannerUrl = new URL('kiosk-scanner-ui.php', window.location.href);
                window.location.href = scannerUrl.href;
            }, 3000);
        }

        // Initialize clock and load scanned employee on page load.
        document.addEventListener("DOMContentLoaded", () => {
            updateClock();
            setInterval(updateClock, 1000);
            loadScannedEmployee();
        });
    </script>
</body>

</html>