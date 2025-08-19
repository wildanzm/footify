<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    #[Title('Login')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            // Dispatch event untuk SweetAlert error
            $this->dispatch('login-failed');

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        // Dispatch event untuk SweetAlert success
        $this->dispatch('login-success');

        $this->redirectIntended(default: route('screenings', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Dispatch event untuk SweetAlert rate limit
        $this->dispatch('login-rate-limited', ['seconds' => $seconds]);

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}; ?>

<div>
    <div class="text-center mb-4">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Masuk Akun</h1>
    </div>

    <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-4">
        <div>
            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Alamat Email</label>
            <input wire:model="email" type="email" id="email"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                placeholder="Masukan email" required autofocus autocomplete="email">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div x-data="{ show: false }">
            <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
            <div class="relative">
                <input wire:model="password" x-bind:type="show ? 'text' : 'password'" id="password"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                    placeholder="Masukan password" required autocomplete="current-password">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                    <button type="button" @click="show = !show" class="p-1 text-gray-500 hover:text-gray-700">
                        <svg x-show="!show" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.432 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="show" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" />
                        </svg>
                    </button>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>


        <div class="flex items-center justify-between">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input wire:model="remember" id="remember" type="checkbox"
                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary text-primary">
                </div>
                <div class="ml-3 text-sm">
                    <label for="remember" class="text-gray-500">Ingat saya</label>
                </div>
            </div>
        </div>

        <div class="mt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="w-full text-white bg-primary hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors duration-200 disabled:opacity-50">
                <span wire:loading.remove>Masuk</span>
                <span wire:loading>Memproses...</span>
            </button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-gray-600 mt-4">
        <span>Belum punya akun?</span>
        <a href="{{ route('register') }}" wire:navigate class="font-medium text-primary hover:underline">Daftar</a>
    </div>
</div>

<script>
    // SweetAlert untuk login berhasil
    document.addEventListener('livewire:init', () => {
        Livewire.on('login-success', () => {
            Swal.fire({
                title: 'Login Berhasil!',
                text: 'Selamat datang kembali! Anda akan diarahkan ke halaman utama.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                confirmButtonColor: '#058a84'
            });
        });

        Livewire.on('login-failed', () => {
            Swal.fire({
                title: 'Login Gagal!',
                text: 'Email atau password yang Anda masukkan salah. Silakan coba lagi.',
                icon: 'error',
                confirmButtonText: 'Coba Lagi',
                confirmButtonColor: '#058a84'
            });
        });

        Livewire.on('login-rate-limited', (event) => {
            const minutes = Math.ceil(event.seconds / 60);
            Swal.fire({
                title: 'Terlalu Banyak Percobaan!',
                text: `Anda telah melakukan terlalu banyak percobaan login. Silakan coba lagi dalam ${minutes} menit.`,
                icon: 'warning',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#058a84'
            });
        });
    });
</script>
