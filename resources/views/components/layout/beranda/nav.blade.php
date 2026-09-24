<nav class="fixed top-0 z-50 w-full bg-white/80 backdrop-blur-md border-b border-gray-200">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start">

                <a href="#" class="flex ms-2 md:me-24 items-center group">
                    <div class="hidden md:block me-3 group-hover:scale-110 transition-transform duration-300">
                        @if (auth()->user()->perusahaan && auth()->user()->perusahaan->logo)
                            {{-- Logo Dinamis dari Database --}}
                            <img src="{{ asset('storage/' . auth()->user()->perusahaan->logo) }}"
                                alt="Logo {{ auth()->user()->perusahaan->nama_perusahaan }}"
                                class="w-10 h-10 object-contain rounded-lg">
                        @else
                            {{-- Logo Default (Backup jika logo kosong) --}}
                            <img src="{{ asset('assets/logo/Mirasa-logo.webp') }}" alt="Logo Default"
                                class="w-10 h-10 object-contain">
                        @endif
                    </div>

                    <span
                        class="self-center text-base font-extrabold sm:text-lg whitespace-nowrap bg-clip-text text-transparent bg-gradient-to-r from-red-700 to-red-600 uppercase">
                        {{ auth()->user()->perusahaan->nama_perusahaan ?? 'MIRASA FOOD INDUSTRY' }}
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative ms-3">
                    <button type="button"
                        class="flex items-center gap-3 p-1 text-sm bg-gray-50 rounded-full hover:bg-gray-100 transition-all border border-gray-100 shadow-sm"
                        id="user-menu-button">
                        <img class="w-8 h-8 rounded-full shadow-inner border border-white"
                            src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'X') }}&background=2563EB&color=fff"
                            alt="user photo">
                        <div class="hidden md:block text-left pr-2">
                            <p class="text-sm font-semibold text-gray-800 leading-none">
                                {{ auth()->user()->name ?? 'No Name' }}</p>
                            <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                {{ auth()->user()->roles->first()->name ?? 'No Role' }}
                            </p>
                        </div>
                    </button>

                    <div class="absolute right-0 z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-xl shadow-lg border border-gray-100 min-w-[170px]"
                        id="user-dropdown">
                        <div class="px-4 py-3 md:hidden">
                            <span class="block text-sm text-gray-900">{{ auth()->user()->name }}</span>
                            <span
                                class="block text-xs text-gray-500 truncate uppercase">{{ auth()->user()->roles->first()->name ?? 'No Role' }}</span>
                        </div>
                        <ul class="py-2">
                            {{-- <li>
                                <a href="{{ route('super-admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                                    Dashboard
                                </a>
                            </li> --}}
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        Sign out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <button onclick="openAside('asideSekunder')"
                        class="p-2 text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>