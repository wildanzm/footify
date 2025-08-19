<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="bg-slate-50 antialiased" x-data="{ sidebarOpen: true }">
    <div class="flex h-screen bg-slate-50">
        <!-- Desktop Sidebar Navigation -->
        <aside class="hidden md:flex flex-col bg-white transition-all duration-300 ease-in-out shadow-lg"
            :class="sidebarOpen ? 'w-64' : 'w-20'">

            <!-- Sidebar Header with Logo and Toggle Button -->
            <div class="flex items-center h-16 p-4 border-b border-slate-200"
                :class="sidebarOpen ? 'justify-between' : 'justify-center'">
                <!-- Logo and Brand Name (visible when sidebar is open) -->
                <div x-show="sidebarOpen" class="flex items-center gap-2"
                    x-transition:enter="transition-opacity ease-in-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">
                    <img src="{{ asset('images/logo-footify.png') }}" class="h-8 w-auto" alt="Footify Logo">
                    <span class="font-bold text-lg text-slate-800">Footify</span>
                </div>
                <!-- Sidebar Toggle Button -->
                <button @click="sidebarOpen = !sidebarOpen"
                    class="p-2 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-primary">
                    <svg class="w-6 h-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                </button>
            </div>

            <!-- Main Navigation Menu -->
            <nav class="flex-1 px-3 py-4 space-y-2">

                <!-- Screening Menu Item -->
                <a href="{{ route('screenings') }}" wire:navigate
                    class="flex items-center p-3 rounded-lg group font-semibold"
                    :class="{
                        'bg-gradient-to-r from-teal-400 to-primary text-white shadow-md': {{ request()->routeIs('screenings') ? 'true' : 'false' }},
                        'text-slate-600 hover:bg-slate-100': !
                            {{ request()->routeIs('screenings') ? 'true' : 'false' }},
                        'justify-center': !sidebarOpen
                    }">
                    <div class="w-6 h-6 flex items-center justify-center">
                        <svg class="w-full h-full"
                            :class="!{{ request()->routeIs('screenings') ? 'true' : 'false' }} &&
                                'text-slate-600 group-hover:text-primary'"
                            viewBox="0 0 72 72" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M67.625,56.738C67.592,56.688,67.66,56.787,67.625,56.738L47.823,28.167V5.42c0-1.105-0.539-1.92-1.643-1.92H27.179 c-1.104,0-1.356,0.816-1.356,1.92v22.097L4.904,56.764c-0.035,0.049-0.217,0.277-0.248,0.33c-1.688,2.926-2.078,5.621-0.785,7.86 c1.306,2.261,3.915,3.546,7.347,3.546h49.451c3.429,0,6.118-1.287,7.424-3.551C69.389,62.704,69.313,59.666,67.625,56.738z M29.137,29.302c0.237-0.338,0.687-0.74,0.687-1.152V7.5h14v21.301c0,0.412-0.194,0.824,0.044,1.161l9.19,13.262 c-3.056,1.232-6.822,2.05-14.531-2.557c-5.585-3.337-12.499-2.048-17.199-0.449L29.137,29.302z M64.55,62.949 c-0.554,0.96-1.969,1.551-3.88,1.551H11.219c-1.915,0-3.33-0.589-3.883-1.547c-0.532-0.922-0.324-2.391,0.571-3.975l11.287-15.777 c3.942-1.702,12.219-4.454,18.308-0.816c4.852,2.898,8.367,3.814,11.116,3.814c2.291,0,4.05-0.637,5.607-1.291l9.755,14.076 C64.877,60.568,65.085,62.023,64.55,62.949z">
                            </path>
                            <path
                                d="M22.026,50.969c-3.017,0-5.471,2.453-5.471,5.471c0,3.017,2.454,5.471,5.471,5.471c3.016,0,5.471-2.454,5.471-5.471 C27.497,53.422,25.043,50.969,22.026,50.969z M22.026,59.911c-1.914,0-3.471-1.558-3.471-3.472s1.557-3.471,3.471-3.471 s3.471,1.557,3.471,3.471S23.94,59.911,22.026,59.911z">
                            </path>
                            <path
                                d="M50.775,52.469c-2.603,0-4.721,2.117-4.721,4.721c0,2.603,2.118,4.721,4.721,4.721c2.604,0,4.722-2.118,4.722-4.721 C55.497,54.586,53.379,52.469,50.775,52.469z M50.775,59.911c-1.5,0-2.721-1.222-2.721-2.722s1.221-2.721,2.721-2.721 s2.722,1.221,2.722,2.721S52.275,59.911,50.775,59.911z">
                            </path>
                            <path
                                d="M35.077,45.469c-2.217,0-4.021,1.803-4.021,4.021c0,2.217,1.803,4.021,4.021,4.021c2.217,0,4.021-1.805,4.021-4.021 S37.294,45.469,35.077,45.469z M35.077,51.512c-1.114,0-2.021-0.908-2.021-2.021c0-1.114,0.907-2.021,2.021-2.021 c1.114,0,2.021,0.906,2.021,2.021S36.191,51.512,35.077,51.512z">
                            </path>
                            <path
                                d="M40.824,22.42c0.553,0,1-0.447,1-1v-11c0-0.553-0.447-1-1-1s-1,0.447-1,1v11C39.824,21.973,40.271,22.42,40.824,22.42z">
                            </path>
                            <path
                                d="M40.824,27.42c0.553,0,1-0.447,1-1v-1c0-0.553-0.447-1-1-1s-1,0.447-1,1v1C39.824,26.973,40.271,27.42,40.824,27.42z">
                            </path>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="ml-3" x-transition>Skrining</span>
                </a>

                <!-- Reports/History Menu Item -->
                <a href="{{ route('reports') }}" wire:navigate
                    class="flex items-center p-3 rounded-lg group font-semibold"
                    :class="{
                        'bg-gradient-to-r from-teal-400 to-primary text-white shadow-md': {{ request()->routeIs('reports') ? 'true' : 'false' }},
                        'text-slate-600 hover:bg-slate-100': !{{ request()->routeIs('reports') ? 'true' : 'false' }},
                        'justify-center': !sidebarOpen
                    }">
                    <div class="w-6 h-6 flex items-center justify-center">
                        <svg class="w-full h-full"
                            :class="!{{ request()->routeIs('reports') ? 'true' : 'false' }} &&
                                'text-slate-600 group-hover:text-primary'"
                            viewBox="0 0 392.533 392.533" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M310.238,71.499H268.8V50.424c0-6.012-4.848-10.925-10.925-10.925h-15.192L222.901,5.43 c-1.939-3.297-5.56-5.43-9.438-5.43h-34.327c-3.879,0-7.499,2.069-9.438,5.43l-19.782,34.004h-15.127 c-6.012,0-10.925,4.849-10.925,10.925v21.075H82.359c-17.067,0-30.901,13.834-30.901,30.901v259.297 c0,17.067,13.834,30.901,30.901,30.901h227.814c17.067,0,30.901-13.834,30.901-30.901V102.4 C341.139,85.333,327.305,71.499,310.238,71.499z M185.406,21.786h21.721l10.279,17.648h-42.279L185.406,21.786z M145.713,61.285 h101.301v35.943H145.713V61.285z M319.418,361.632h-0.065c0,5.042-4.073,9.115-9.115,9.115H82.424 c-5.042,0-9.115-4.073-9.115-9.115V102.4c0-5.042,4.073-9.115,9.115-9.115h41.503v14.804c0,6.012,4.849,10.925,10.925,10.925 h123.087c6.012,0,10.925-4.848,10.925-10.925V93.285h41.438c5.042,0,9.115,4.073,9.115,9.115V361.632z">
                            </path>
                            <path
                                d="M290.198,141.511h-62.966c-6.012,0-10.925,4.848-10.925,10.925v71.628c0,6.012,4.849,10.925,10.925,10.925h62.966 c6.012,0,10.925-4.848,10.925-10.925v-71.693C301.123,146.424,296.275,141.511,290.198,141.511z M279.337,213.269h-41.18v-49.907 h41.18V213.269z">
                            </path>
                            <path
                                d="M187.281,155.475h-84.816c-6.012,0-10.925,4.849-10.925,10.925c0,6.077,4.849,10.925,10.925,10.925h84.816 c6.012,0,10.925-4.849,10.925-10.925C198.206,160.388,193.357,155.475,187.281,155.475z">
                            </path>
                            <path
                                d="M187.281,213.204h-84.816c-6.012,0-10.925,4.849-10.925,10.925c0,6.077,4.849,10.925,10.925,10.925h84.816 c6.012,0,10.925-4.849,10.925-10.925C198.206,218.117,193.357,213.204,187.281,213.204z">
                            </path>
                            <path
                                d="M290.198,270.933H102.465c-6.012,0-10.925,4.848-10.925,10.925c0,6.012,4.849,10.925,10.925,10.925h187.863 c6.012,0,10.925-4.849,10.925-10.925C301.123,275.846,296.275,270.933,290.198,270.933z">
                            </path>
                            <path
                                d="M290.198,328.663H102.465c-6.012,0-10.925,4.849-10.925,10.925s4.849,10.925,10.925,10.925h187.863 c6.012,0,10.925-4.848,10.925-10.925C301.123,333.511,296.275,328.663,290.198,328.663z">
                            </path>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="ml-3" x-transition>Histori</span>
                </a>

                <!-- Profile Menu Item -->
                <a href="{{ route('profile') }}" wire:navigate
                    class="flex items-center p-3 rounded-lg group font-semibold"
                    :class="{
                        'bg-gradient-to-r from-teal-400 to-primary text-white shadow-md': {{ request()->routeIs('profile') ? 'true' : 'false' }},
                        'text-slate-600 hover:bg-slate-100': !{{ request()->routeIs('profile') ? 'true' : 'false' }},
                        'justify-center': !sidebarOpen
                    }">
                    <div class="w-6 h-6 flex items-center justify-center">
                        <svg class="w-full h-full"
                            :class="!{{ request()->routeIs('profile') ? 'true' : 'false' }} &&
                                'text-slate-600 group-hover:text-primary'"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="ml-3" x-transition>Profil</span>
                </a>

            </nav>

            <!-- Logout Section -->
            <div class="px-3 py-4 mt-auto">
                <!-- Logout Button -->
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="flex items-center p-3 text-slate-600 rounded-lg hover:bg-red-100 group font-semibold"
                    :class="!sidebarOpen && 'justify-center'">
                    <div class="w-6 h-6 flex items-center justify-center">
                        <svg class="w-full h-full text-slate-500 group-hover:text-red-600"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="ml-3" x-transition>Logout</span>
                </a>
                <!-- Hidden Logout Form -->
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </aside>

        <!-- Mobile Top Header for Small Screens -->
        <div class="md:hidden fixed top-0 left-0 right-0 z-40 bg-white shadow-sm border-b border-slate-200">
            <div class="flex items-center justify-between h-16 px-4">
                <!-- Mobile Logo and Brand -->
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo-footify.png') }}" class="h-8 w-auto" alt="Footify Logo">
                    <span class="font-bold text-lg text-slate-800">Footify</span>
                </div>
                <!-- Mobile Logout Button -->
                <button onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();"
                    class="p-2 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-primary">
                    <svg class="w-6 h-6 text-slate-500 hover:text-red-600" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                </button>
            </div>
            <!-- Hidden Mobile Logout Form -->
            <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>

        <!-- Mobile Bottom Navigation Bar -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200 shadow-lg">
            <div class="flex justify-around items-center h-16 px-2">
                <!-- Mobile Screening Menu -->
                <a href="{{ route('screenings') }}" wire:navigate
                    class="flex flex-col items-center justify-center p-2 rounded-lg min-w-0 flex-1"
                    :class="{{ request()->routeIs('screenings') ? 'true' : 'false' }} ? 'text-primary' : 'text-slate-600'">
                    <div class="w-6 h-6 flex items-center justify-center mb-1">
                        <svg class="w-full h-full" viewBox="0 0 72 72" fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M67.625,56.738C67.592,56.688,67.66,56.787,67.625,56.738L47.823,28.167V5.42c0-1.105-0.539-1.92-1.643-1.92H27.179 c-1.104,0-1.356,0.816-1.356,1.92v22.097L4.904,56.764c-0.035,0.049-0.217,0.277-0.248,0.33c-1.688,2.926-2.078,5.621-0.785,7.86 c1.306,2.261,3.915,3.546,7.347,3.546h49.451c3.429,0,6.118-1.287,7.424-3.551C69.389,62.704,69.313,59.666,67.625,56.738z M29.137,29.302c0.237-0.338,0.687-0.74,0.687-1.152V7.5h14v21.301c0,0.412-0.194,0.824,0.044,1.161l9.19,13.262 c-3.056,1.232-6.822,2.05-14.531-2.557c-5.585-3.337-12.499-2.048-17.199-0.449L29.137,29.302z M64.55,62.949 c-0.554,0.96-1.969,1.551-3.88,1.551H11.219c-1.915,0-3.33-0.589-3.883-1.547c-0.532-0.922-0.324-2.391,0.571-3.975l11.287-15.777 c3.942-1.702,12.219-4.454,18.308-0.816c4.852,2.898,8.367,3.814,11.116,3.814c2.291,0,4.05-0.637,5.607-1.291l9.755,14.076 C64.877,60.568,65.085,62.023,64.55,62.949z">
                            </path>
                            <path
                                d="M22.026,50.969c-3.017,0-5.471,2.453-5.471,5.471c0,3.017,2.454,5.471,5.471,5.471c3.016,0,5.471-2.454,5.471-5.471 C27.497,53.422,25.043,50.969,22.026,50.969z M22.026,59.911c-1.914,0-3.471-1.558-3.471-3.472s1.557-3.471,3.471-3.471 s3.471,1.557,3.471,3.471S23.94,59.911,22.026,59.911z">
                            </path>
                            <path
                                d="M50.775,52.469c-2.603,0-4.721,2.117-4.721,4.721c0,2.603,2.118,4.721,4.721,4.721c2.604,0,4.722-2.118,4.722-4.721 C55.497,54.586,53.379,52.469,50.775,52.469z M50.775,59.911c-1.5,0-2.721-1.222-2.721-2.722s1.221-2.721,2.721-2.721 s2.722,1.221,2.722,2.721S52.275,59.911,50.775,59.911z">
                            </path>
                            <path
                                d="M35.077,45.469c-2.217,0-4.021,1.803-4.021,4.021c0,2.217,1.803,4.021,4.021,4.021c2.217,0,4.021-1.805,4.021-4.021 S37.294,45.469,35.077,45.469z M35.077,51.512c-1.114,0-2.021-0.908-2.021-2.021c0-1.114,0.907-2.021,2.021-2.021 c1.114,0,2.021,0.906,2.021,2.021S36.191,51.512,35.077,51.512z">
                            </path>
                            <path
                                d="M40.824,22.42c0.553,0,1-0.447,1-1v-11c0-0.553-0.447-1-1-1s-1,0.447-1,1v11C39.824,21.973,40.271,22.42,40.824,22.42z">
                            </path>
                            <path
                                d="M40.824,27.42c0.553,0,1-0.447,1-1v-1c0-0.553-0.447-1-1-1s-1,0.447-1,1v1C39.824,26.973,40.271,27.42,40.824,27.42z">
                            </path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium">Skrining</span>
                </a>

                <!-- Mobile Reports/History Menu -->
                <a href="{{ route('reports') }}" wire:navigate
                    class="flex flex-col items-center justify-center p-2 rounded-lg min-w-0 flex-1"
                    :class="{{ request()->routeIs('reports') ? 'true' : 'false' }} ? 'text-primary' : 'text-slate-600'">
                    <div class="w-6 h-6 flex items-center justify-center mb-1">
                        <svg class="w-full h-full" viewBox="0 0 392.533 392.533" fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M310.238,71.499H268.8V50.424c0-6.012-4.848-10.925-10.925-10.925h-15.192L222.901,5.43 c-1.939-3.297-5.56-5.43-9.438-5.43h-34.327c-3.879,0-7.499,2.069-9.438,5.43l-19.782,34.004h-15.127 c-6.012,0-10.925,4.849-10.925,10.925v21.075H82.359c-17.067,0-30.901,13.834-30.901,30.901v259.297 c0,17.067,13.834,30.901,30.901,30.901h227.814c17.067,0,30.901-13.834,30.901-30.901V102.4 C341.139,85.333,327.305,71.499,310.238,71.499z M185.406,21.786h21.721l10.279,17.648h-42.279L185.406,21.786z M145.713,61.285 h101.301v35.943H145.713V61.285z M319.418,361.632h-0.065c0,5.042-4.073,9.115-9.115,9.115H82.424 c-5.042,0-9.115-4.073-9.115-9.115V102.4c0-5.042,4.073-9.115,9.115-9.115h41.503v14.804c0,6.012,4.849,10.925,10.925,10.925 h123.087c6.012,0,10.925-4.848,10.925-10.925V93.285h41.438c5.042,0,9.115,4.073,9.115,9.115V361.632z">
                            </path>
                            <path
                                d="M290.198,141.511h-62.966c-6.012,0-10.925,4.848-10.925,10.925v71.628c0,6.012,4.849,10.925,10.925,10.925h62.966 c6.012,0,10.925-4.848,10.925-10.925v-71.693C301.123,146.424,296.275,141.511,290.198,141.511z M279.337,213.269h-41.18v-49.907 h41.18V213.269z">
                            </path>
                            <path
                                d="M187.281,155.475h-84.816c-6.012,0-10.925,4.849-10.925,10.925c0,6.077,4.849,10.925,10.925,10.925h84.816 c6.012,0,10.925-4.849,10.925-10.925C198.206,160.388,193.357,155.475,187.281,155.475z">
                            </path>
                            <path
                                d="M187.281,213.204h-84.816c-6.012,0-10.925,4.849-10.925,10.925c0,6.077,4.849,10.925,10.925,10.925h84.816 c6.012,0,10.925-4.849,10.925-10.925C198.206,218.117,193.357,213.204,187.281,213.204z">
                            </path>
                            <path
                                d="M290.198,270.933H102.465c-6.012,0-10.925,4.848-10.925,10.925c0,6.012,4.849,10.925,10.925,10.925h187.863 c6.012,0,10.925-4.849,10.925-10.925C301.123,275.846,296.275,270.933,290.198,270.933z">
                            </path>
                            <path
                                d="M290.198,328.663H102.465c-6.012,0-10.925,4.849-10.925,10.925s4.849,10.925,10.925,10.925h187.863 c6.012,0,10.925-4.848,10.925-10.925C301.123,333.511,296.275,328.663,290.198,328.663z">
                            </path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium">Histori</span>
                </a>

                <!-- Mobile Profile Menu -->
                <a href="{{ route('profile') }}" wire:navigate
                    class="flex flex-col items-center justify-center p-2 rounded-lg min-w-0 flex-1"
                    :class="{{ request()->routeIs('profile') ? 'true' : 'false' }} ? 'text-primary' : 'text-slate-600'">
                    <div class="w-6 h-6 flex items-center justify-center mb-1">
                        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium">Profil</span>
                </a>

            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 overflow-y-auto">
            <!-- Main content with responsive padding for mobile header and bottom navigation -->
            <main class="flex-1 p-6 md:p-8 pt-20 md:pt-6 pb-20 md:pb-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- JavaScript Dependencies -->
    @fluxScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>
