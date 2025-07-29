<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="font-sans text-gray-900 antialiased">
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center p-4 bg-gradient-to-br from-cyan-100 via-teal-200 to-emerald-300 bg-[length:200%_200%] animate-gradient-bg">

        <div class="w-full max-w-4xl flex flex-col md:flex-row bg-white rounded-2xl shadow-2xl overflow-hidden my-4">

            <div
                class="w-full md:w-1/2 flex items-center justify-center p-8 sm:p-12 bg-gray-50/50 border-r border-gray-100">
                <div class="flex flex-col items-center text-center">
                    <img src="{{ asset('images/logo-2024-hr.png') }}" alt="Kemenkes Poltekkes Tasikmalaya Logo"
                        class="max-w-xs w-full h-auto">
                </div>
            </div>

            <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center">
                <div class="flex flex-col items-center mb-6 text-center">
                    <img src="{{ asset('images/logo-footify.png') }}" alt="Footify Logo" class="w-20 h-20">
                    <h1 class="text-4xl font-bold text-gray-800 mt-2">Footify</h1>
                    <p class="text-gray-500">Langkahmu Berarti, Footify yang peduli.</p>
                </div>

                {{ $slot }}
            </div>

        </div>
    </div>

    <div class="fixed bottom-0 left-0 w-full bg-primary overflow-hidden">
        <p class="marquee-text text-white text-xs whitespace-nowrap">
            Selamat datang di Footify! Copyright © 2025 Footify. Prodi D-III RMIK Cirebon Poltekkes Kemenkes Tasikmalaya
            by Nadila Amanda Dwi
        </p>
    </div>
    @fluxScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>
