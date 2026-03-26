<nav x-data="{ open: false, quickOpen: false, adminOpen: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden items-center space-x-4 lg:ms-10 lg:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Painel') }}
                    </x-nav-link>

                    @auth
                        <div class="relative">
                            <button
                                type="button"
                                @click="quickOpen = !quickOpen; adminOpen = false"
                                @click.outside="quickOpen = false"
                                class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                                <span>{{ __('Ensino') }}</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div
                                x-cloak
                                x-show="quickOpen"
                                x-transition
                                class="absolute left-0 z-50 mt-3 w-72 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Aulas e certificados') }}</p>
                                </div>

                                <div class="grid gap-1 p-2">
                                    <a href="{{ route('certificates.emit') }}" class="rounded-xl px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                        {{ __('Emitir certificado') }}
                                    </a>
                                    @role('admin')
                                        <a href="{{ route('courses.index') }}" class="rounded-xl px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                            {{ __('Cursos') }}
                                        </a>
                                        <a href="{{ route('course-classes.index') }}" class="rounded-xl px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                            {{ __('Turmas') }}
                                        </a>
                                        <a href="{{ route('certificates.index') }}" class="rounded-xl px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                            {{ __('Certificados') }}
                                        </a>
                                    `@endrole`
                                </div>
                            </div>
                        </div>

                        @role('admin')
                            <div class="relative">
                                <button
                                    type="button"
                                    @click="adminOpen = !adminOpen; quickOpen = false"
                                    @click.outside="adminOpen = false"
                                    class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    <span>{{ __('Cadastros e gestão') }}</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div
                                    x-cloak
                                    x-show="adminOpen"
                                    x-transition
                                    class="absolute left-0 z-50 mt-3 w-80 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Cadastros e gestão') }}</p>
                                    </div>

                                    <div class="grid gap-1 p-2">
                                        <a href="{{ route('students.index') }}" class="rounded-xl px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                            {{ __('Alunos') }}
                                        </a>
                                        <a href="{{ route('instructors.index') }}" class="rounded-xl px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                            {{ __('Instrutores') }}
                                        </a>
                                        <a href="{{ route('certificate-settings.edit') }}" class="rounded-xl px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                            {{ __('Configuração de Certificados') }}
                                        </a>
                                        <a href="{{ route('admin.users.index') }}" class="rounded-xl px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                            {{ __('Usuários') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endrole
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden lg:flex lg:items-center lg:ms-6">
                @auth
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" aria-haspopup="true" aria-expanded="false"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition">
                    <span class="truncate">{{ Auth::user()->name }}</span>
                    <svg class="ms-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">

                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-dropdown-link>
                

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Sair') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    @endauth



    @guest
    <div class="flex items-center gap-3">

        {{-- Login --}}
        <a href="{{ route('login') }}"
           class="inline-flex items-center justify-center px-4 py-2 rounded-md
                  text-sm font-semibold
                  text-gray-700 dark:text-gray-200
                  border border-gray-300 dark:border-gray-600
                  hover:bg-gray-100 dark:hover:bg-gray-700
                  transition-all duration-200
                  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ __('Entrar') }}
        </a>

        {{-- Register --}}
        @if (Route::has('register'))
            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center px-5 py-2 rounded-md
                      text-sm font-semibold
                      text-white
                      bg-indigo-600 hover:bg-indigo-700
                      shadow-sm hover:shadow-md
                      transition-all duration-200
                      focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Criar conta') }}
            </a>
        @endif

    </div>
@endguest
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white p-2 text-gray-500 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-gray-100 bg-white lg:hidden dark:border-gray-700 dark:bg-gray-800">
        <div class="space-y-4 px-4 py-4">
            <div class="rounded-2xl bg-gray-50 p-2 dark:bg-gray-900/40">
                <p class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Principal') }}</p>
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Painel') }}
                </x-responsive-nav-link>
            </div>

            @auth
                <div class="rounded-2xl bg-gray-50 p-2 dark:bg-gray-900/40">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Ensino') }}</p>
                    <x-responsive-nav-link :href="route('certificates.emit')" :active="request()->routeIs('certificates.emit')">
                        {{ __('Emitir certificado') }}
                    </x-responsive-nav-link>

                    @role('admin')
                        <x-responsive-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.*')">
                            {{ __('Cursos') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('course-classes.index')" :active="request()->routeIs('course-classes.*')">
                            {{ __('Turmas') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('certificates.index')" :active="request()->routeIs('certificates.*')">
                            {{ __('Certificados') }}
                        </x-responsive-nav-link>
                    @endrole
                </div>

                @role('admin')
                    <div class="rounded-2xl bg-gray-50 p-2 dark:bg-gray-900/40">
                        <p class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('Cadastros e gestão') }}</p>
                        <x-responsive-nav-link :href="route('students.index')" :active="request()->routeIs('students.*')">
                            {{ __('Alunos') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('instructors.index')" :active="request()->routeIs('instructors.*')">
                            {{ __('Instrutores') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('certificate-settings.edit')" :active="request()->routeIs('certificate-settings.*')">
                            {{ __('Configuração de Certificados') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Usuários') }}
                        </x-responsive-nav-link>
                    </div>
                @endrole
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name ?? __('Convidado') }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email ?? __('Sem e-mail') }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Sair') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
