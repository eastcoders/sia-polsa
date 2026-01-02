<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - 403</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen px-4">

    <div class="text-center max-w-lg w-full bg-white p-8 rounded-2xl shadow-xl border border-gray-100 transition-all duration-300 hover:shadow-2xl">
        <!-- GIF Animation -->
        <div class="mb-6 flex justify-center">
            <img src="https://media.giphy.com/media/g01ZnwAUvutuK8GIQn/giphy.gif" alt="Bingung" class="w-48 h-48 object-cover rounded-full shadow-md border-4 border-indigo-50">
        </div>

        <!-- Error Message -->
        <h1 class="text-6xl font-extrabold text-indigo-600 mb-2">403</h1>
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Akses Ditolak</h2>
        <p class="text-gray-500 mb-8 leading-relaxed">
            Maaf, autentikasi Anda tidak valid atau Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <!-- Back Button -->
            <button onclick="history.back()" class="flex-1 w-full flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 shadow-lg shadow-indigo-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </button>

            <!-- Logout Button -->
            <form action="{{ filament()->getLogoutUrl() }}" method="post" class="flex-1 w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-100 text-xs text-gray-400">
            Sistem Informasi Akademik &copy; {{ date('Y') }}
        </div>
    </div>

</body>
</html>
