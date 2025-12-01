<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Asrama IoT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    @if(request()->routeIs('dashboard'))
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endif
    <style>
        [x-cloak] { display: none !important; }
        .animate-pulse-fast { animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
    <script src="/js/notification.js" defer></script>
    @stack('styles')
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">
    <!-- Hidden audio element for fallback alarm -->
    <audio id="alarm-audio" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdH2LkZeXk42Gg4OHjpaeoqGdlIuEgYSMmKKpq6eglI2IiI2WoKirrKifjYOBg4uXo6urrquhlYqFhoyYpKqtq6qjmI2Hh4yWoqutrauonpOKh4mRnKWrra2ropiNiIqPmaSrrq6spZqPi4yRnaWsr66so5mPi4yTn6arrrCuqZ2Sj42Sn6errrCvq6CUkI+Tn6irrrCwrKKXkZCUoKmtr7Cvq6KXkpGVoamtsLGwrKOYk5KWoqqtsLGwraWalJOXpKuusbKxr6eblZWYpayvsrOyr6mdl5aZp62ws7OzsKugnJeaq66xtLS0sq2hnpmbrq+xtbW1s6+jn5qdrrCytba2tLGloZ6erbGztba3trOoo6CfrrG0tre4t7WqpaGhsLK1uLi4t7WsqKOjsrO2ubm5uLevq6WktLW3urq6uru0raansLa4u7u8vLy4sKyotri6vL2+vr25s66pubq7vr6/v767trCxu7y+wMDAwL+9uLK0vL3AwcLCwsHAu7W4vcDCw8PExMS/vLi6wMHDxMXFxcXCvry+wcPFxsbGxsbFwb7AwsPGx8jIyMjGw8DCxMbHycnJycnIxcPFx8jKysvLy8vJxsXHycrLzMzMzMvKyMfJy8zNzs7Ozs3LycnLzM7P0NDQ0M/NzMvNz9DR0tLS0tHPzc3P0dLT1NTU1NPS0NDR09TW1tbW1tbU09LT1dbY2NjY2NfW1NTV19ja2tra2tnY1tbY2drb3Nzc3NzbGRoaGhoaGhoaGhoaGhoaGhoaGhoaGhoaGhoaGhoaGhoaGhoaGhoaGhoaGhoa" type="audio/wav">
    </audio>
    <div class="min-h-full">
        <!-- Sidebar Mobile -->
        <div x-show="sidebarOpen" class="relative z-50 lg:hidden" x-cloak>
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/80" @click="sidebarOpen = false"></div>

            <div class="fixed inset-0 flex">
                <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                     class="relative mr-16 flex w-full max-w-xs flex-1">
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-indigo-600 px-6 pb-4">
                        @include('layouts.sidebar-content')
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-indigo-600 px-6 pb-4">
                @include('layouts.sidebar-content')
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:pl-72">
            <!-- Top Bar -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <div class="h-6 w-px bg-gray-200 lg:hidden"></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1 items-center">
                        <h1 class="text-lg font-semibold text-gray-900">@yield('header', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Sirine Status Indicator -->
                        <div id="sirine-indicator" class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Sirine:</span>
                            <span id="sirine-status" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">
                                Loading...
                            </span>
                        </div>

                        <!-- User Menu -->
                        @auth
                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200"></div>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm">
                                <span class="hidden lg:block text-gray-700">{{ auth()->user()->name }}</span>
                                <i class="fas fa-user-circle text-2xl text-gray-400"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="py-6">
                <div class="px-4 sm:px-6 lg:px-8">
                    @if(session('success'))
                    <div class="mb-4 rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-400"></i>
                            <p class="ml-3 text-sm text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                            <p class="ml-3 text-sm text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            updateSirineStatus();
            setInterval(updateSirineStatus, 15000);
        });

        function updateSirineStatus() {
            fetch('/api/sirine')
                .then(r => r.text())
                .then(status => {
                    const el = document.getElementById('sirine-status');
                    if (!el) return;
                    el.textContent = status;
                    el.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' +
                        (status === 'ON' ? 'bg-red-100 text-red-800 animate-pulse-fast' :
                         status === 'OFF' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800');
                }).catch(() => {});
        }
    </script>
    @stack('scripts')
</body>
</html>
