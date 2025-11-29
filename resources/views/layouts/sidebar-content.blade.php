<div class="flex h-16 shrink-0 items-center">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
        <i class="fas fa-building text-3xl text-white"></i>
        <span class="text-xl font-bold text-white">Asrama IoT</span>
    </a>
</div>
<nav class="flex flex-1 flex-col">
    <ul role="list" class="flex flex-1 flex-col gap-y-7">
        <li>
            <ul role="list" class="-mx-2 space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}" 
                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('dashboard') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700 hover:text-white' }}">
                        <i class="fas fa-tachometer-alt w-6 text-center"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('riwayat.index') }}" 
                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('riwayat.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700 hover:text-white' }}">
                        <i class="fas fa-history w-6 text-center"></i>
                        Riwayat Kejadian
                    </a>
                </li>
                <li>
                    <a href="{{ route('kamera.index') }}" 
                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('kamera.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700 hover:text-white' }}">
                        <i class="fas fa-camera w-6 text-center"></i>
                        Kamera
                    </a>
                </li>
                <li>
                    <a href="{{ route('distribusi.index') }}" 
                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('distribusi.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700 hover:text-white' }}">
                        <i class="fas fa-paper-plane w-6 text-center"></i>
                        Distribusi Notifikasi
                    </a>
                </li>
                <li>
                    <a href="{{ route('kontak.index') }}" 
                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('kontak.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700 hover:text-white' }}">
                        <i class="fas fa-address-book w-6 text-center"></i>
                        Kontak Darurat
                    </a>
                </li>
                <li>
                    <a href="{{ route('sirine.index') }}" 
                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('sirine.*') ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-700 hover:text-white' }}">
                        <i class="fas fa-bell w-6 text-center"></i>
                        Kontrol Sirine
                    </a>
                </li>
            </ul>
        </li>
        <li class="mt-auto">
            <div class="rounded-md bg-indigo-700 p-3">
                <p class="text-xs text-indigo-200">Status Sistem</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-sm text-white">Online</span>
                </div>
            </div>
        </li>
    </ul>
</nav>
