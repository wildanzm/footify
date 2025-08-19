<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="font-sans text-gray-900 antialiased">
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center p-4 bg-gradient-to-br from-cyan-100 via-teal-200 to-emerald-300 bg-[length:200%_200%] animate-gradient-bg">

        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden my-4">

            <div class="w-full p-8 sm:p-12 flex flex-col justify-center">
                <div class="flex flex-col items-center text-center">
                    <img src="{{ asset('images/logo-footify.png') }}" alt="Footify Logo" class="w-20 h-20">
                </div>

                {{ $slot }}
            </div>

        </div>
    </div>

    @fluxScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>
