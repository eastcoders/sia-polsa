<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - 403</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        lime: {
                            400: '#c6f221',
                            500: '#b8e619',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .blob {
            position: absolute;
            width: 320px;
            height: 320px;
            background: #e8f5a3;
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            z-index: 0;
            opacity: 0.6;
        }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center px-6 py-12">

    <div class="max-w-5xl w-full flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-8">
        
        <!-- Left Content -->
        <div class="flex-1 text-center lg:text-left order-2 lg:order-1">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-2">Ooops....</h1>
            <h2 class="text-xl md:text-2xl font-semibold text-gray-700 mb-4">Akses Ditolak</h2>
            <p class="text-gray-500 mb-8 max-w-md mx-auto lg:mx-0 leading-relaxed">
                Halaman yang Anda cari tidak tersedia atau Anda tidak memiliki izin untuk mengaksesnya. Silakan kembali ke halaman sebelumnya.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                <button onclick="history.back()" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-lime-400 hover:bg-lime-500 text-gray-900 font-semibold rounded-full transition-all duration-200 shadow-lg shadow-lime-200 hover:shadow-xl hover:shadow-lime-300 hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </button>
                
                <form action="{{ filament()->getLogoutUrl() }}" method="post">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-white border-2 border-gray-200 hover:border-gray-300 text-gray-700 font-semibold rounded-full transition-all duration-200 hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Illustration -->
        <div class="flex-1 relative order-1 lg:order-2 flex items-center justify-center">
            <!-- Background Blob -->
            <div class="blob right-4 top-8"></div>
            
            <!-- SVG Illustration -->
            <svg class="relative z-10 w-full max-w-md" viewBox="0 0 400 350" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Padlock -->
                <rect x="80" y="80" width="80" height="60" rx="8" fill="#f3f4f6" stroke="#1f2937" stroke-width="3"/>
                <path d="M95 80V60C95 43.4315 108.431 30 125 30H115C131.569 30 145 43.4315 145 60V80" stroke="#1f2937" stroke-width="6" fill="none" stroke-linecap="round"/>
                <circle cx="120" cy="110" r="8" fill="#1f2937"/>
                <rect x="117" y="110" width="6" height="15" rx="2" fill="#1f2937"/>
                
                <!-- Security Guard Body -->
                <ellipse cx="250" cy="310" rx="60" ry="10" fill="#e5e7eb"/>
                
                <!-- Legs -->
                <rect x="225" y="240" width="20" height="70" rx="4" fill="#1f2937"/>
                <rect x="255" y="240" width="20" height="70" rx="4" fill="#1f2937"/>
                
                <!-- Shoes -->
                <ellipse cx="235" cy="310" rx="15" ry="8" fill="#1f2937"/>
                <ellipse cx="265" cy="310" rx="15" ry="8" fill="#1f2937"/>
                
                <!-- Body/Shirt -->
                <path d="M210 170 L250 155 L290 170 L285 245 L215 245 Z" fill="white" stroke="#1f2937" stroke-width="2"/>
                <line x1="250" y1="155" x2="250" y2="245" stroke="#1f2937" stroke-width="2"/>
                
                <!-- Tie -->
                <polygon points="250,165 245,180 250,240 255,180" fill="#1f2937"/>
                
                <!-- Left Arm (holding coffee) -->
                <path d="M210 175 Q180 200 185 230" stroke="#f8e8d4" stroke-width="14" fill="none" stroke-linecap="round"/>
                <ellipse cx="185" cy="238" rx="12" ry="8" fill="#f8e8d4"/>
                
                <!-- Coffee Cup -->
                <rect x="172" y="235" width="26" height="30" rx="3" fill="#f3f4f6" stroke="#1f2937" stroke-width="2"/>
                <path d="M198 245 Q210 250 198 260" stroke="#1f2937" stroke-width="2" fill="none"/>
                
                <!-- Right Arm (stop gesture) -->
                <path d="M290 175 Q330 160 335 130" stroke="#f8e8d4" stroke-width="14" fill="none" stroke-linecap="round"/>
                
                <!-- Hand (stop) -->
                <ellipse cx="340" cy="115" rx="22" ry="28" fill="#f8e8d4" stroke="#1f2937" stroke-width="1"/>
                <line x1="332" y1="95" x2="332" y2="108" stroke="#1f2937" stroke-width="2" stroke-linecap="round"/>
                <line x1="340" y1="92" x2="340" y2="105" stroke="#1f2937" stroke-width="2" stroke-linecap="round"/>
                <line x1="348" y1="95" x2="348" y2="108" stroke="#1f2937" stroke-width="2" stroke-linecap="round"/>
                <ellipse cx="325" cy="115" rx="6" ry="10" fill="#f8e8d4"/>
                
                <!-- Head -->
                <ellipse cx="250" cy="130" rx="30" ry="35" fill="#f8e8d4"/>
                
                <!-- Cap -->
                <ellipse cx="250" cy="100" rx="35" ry="12" fill="#1f2937"/>
                <rect x="220" y="88" width="60" height="15" rx="2" fill="#1f2937"/>
                <rect x="230" y="85" width="40" height="8" fill="#1f2937"/>
                
                <!-- Face -->
                <circle cx="240" cy="125" r="3" fill="#1f2937"/>
                <circle cx="260" cy="125" r="3" fill="#1f2937"/>
                <ellipse cx="250" cy="138" rx="3" ry="2" fill="#d4a574"/>
                <path d="M242 148 Q250 152 258 148" stroke="#1f2937" stroke-width="2" fill="none" stroke-linecap="round"/>
                
                <!-- Barrier -->
                <rect x="40" y="250" width="15" height="60" fill="#1f2937"/>
                <rect x="145" y="250" width="15" height="60" fill="#1f2937"/>
                <g transform="rotate(-5, 100, 260)">
                    <rect x="35" y="245" width="130" height="20" fill="#c6f221"/>
                    <rect x="35" y="245" width="20" height="20" fill="#1f2937"/>
                    <rect x="75" y="245" width="20" height="20" fill="#1f2937"/>
                    <rect x="115" y="245" width="20" height="20" fill="#1f2937"/>
                </g>
                <g transform="rotate(-5, 100, 280)">
                    <rect x="35" y="275" width="130" height="20" fill="#1f2937"/>
                    <rect x="55" y="275" width="20" height="20" fill="#c6f221"/>
                    <rect x="95" y="275" width="20" height="20" fill="#c6f221"/>
                    <rect x="135" y="275" width="20" height="20" fill="#c6f221"/>
                </g>
                
                <!-- Traffic Cone -->
                <polygon points="330,310 350,310 345,260 335,260" fill="#ff6b35" stroke="#1f2937" stroke-width="1"/>
                <rect x="332" y="275" width="16" height="6" fill="white"/>
                <ellipse cx="340" cy="310" rx="15" ry="5" fill="#1f2937"/>
                
                <!-- Error Text -->
                <text x="300" y="180" font-family="Inter, sans-serif" font-size="14" font-weight="600" fill="#9ca3af">ERROR</text>
                <text x="280" y="215" font-family="Inter, sans-serif" font-size="36" font-weight="800" fill="#1f2937" opacity="0.15">403</text>
                <text x="280" y="230" font-family="Inter, sans-serif" font-size="10" font-weight="600" fill="#9ca3af" letter-spacing="2">FORBIDDEN</text>
            </svg>
        </div>
    </div>

    <!-- Footer -->
    <div class="fixed bottom-4 left-0 right-0 text-center">
        <p class="text-xs text-gray-400">
            Sistem Informasi Akademik &copy; {{ date('Y') }}
        </p>
    </div>

</body>
</html>
