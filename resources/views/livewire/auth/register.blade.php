<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
                'password_confirmation' => ['required', 'string'],
            ];

            $messages = [
                'name.required' => 'Nama lengkap wajib diisi.',
                'name.string' => 'Nama lengkap harus berupa teks.',
                'name.max' => 'Nama lengkap maksimal :max karakter.',

                'email.required' => 'Email wajib diisi.',
                'email.string' => 'Email harus berupa teks.',
                'email.lowercase' => 'Email harus menggunakan huruf kecil.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal :max karakter.',
                'email.unique' => 'Email sudah terdaftar.',

                'password.required' => 'Password wajib diisi.',
                'password.string' => 'Password harus berupa teks.',
                'password.confirmed' => 'Konfirmasi password tidak sama.',

                'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
                'password_confirmation.string' => 'Konfirmasi password harus berupa teks.',
            ];

            $attributes = [
                'name' => 'Nama lengkap',
                'email' => 'Email',
                'password' => 'Password',
                'password_confirmation' => 'Konfirmasi password',
            ];

            $validated = $this->validate($rules, $messages, $attributes);

            $validated['password'] = Hash::make($validated['password']);

            event(new Registered(($user = User::create($validated))));

            Auth::login($user);

            $this->dispatch('register-success');

            $this->redirectIntended(route('screenings', absolute: false), navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('register-failed');
            throw $e;
        } catch (\Throwable $e) {
            $this->dispatch('register-failed');
            throw $e;
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="text-center">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Daftar Akun</h1>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <div>
            <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap</label>
            <input id="name" type="text" wire:model="name" required autofocus autocomplete="name"
                placeholder="Nama Lengkap"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" />
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email</label>
            <input id="email" type="email" wire:model="email" required autocomplete="email"
                placeholder="Masukan email"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" />
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div x-data="{ show: false }">
            <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Password</label>
            <div class="relative">
                <input id="password" x-bind:type="show ? 'text' : 'password'" wire:model="password" required
                    autocomplete="new-password" placeholder="Masukan password"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                    <button type="button" @click="show = !show" class="p-1 text-gray-500 hover:text-gray-700"
                        aria-label="Toggle password visibility">
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
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div x-data="{ showConfirm: false }">
            <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-700">Konfirmasi
                Password</label>
            <div class="relative">
                <input id="password_confirmation" x-bind:type="showConfirm ? 'text' : 'password'"
                    wire:model="password_confirmation" required autocomplete="new-password"
                    placeholder="Konfirmasi password"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                    <button type="button" @click="showConfirm = !showConfirm"
                        class="p-1 text-gray-500 hover:text-gray-700" aria-label="Toggle confirm password visibility">
                        <svg x-show="!showConfirm" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.432 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showConfirm" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" />
                        </svg>
                    </button>
                </div>
            </div>
            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            <button type="submit"
                class="text-white bg-primary hover:bg-primary focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm w-full px-5 py-2.5 text-center">
                Buat Akun
            </button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-gray-600">
        <span>Sudah punya akun?</span>
        <a href="{{ route('login') }}" wire:navigate class="font-medium text-primary hover:underline">Masuk</a>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('register-success', () => {
            Swal.fire({
                title: 'Pendaftaran Berhasil!',
                text: 'Akun berhasil dibuat. Anda akan diarahkan ke halaman profil.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                confirmButtonColor: '#058a84'
            });
        });

        Livewire.on('register-failed', () => {
            Swal.fire({
                title: 'Pendaftaran Gagal!',
                text: 'Silakan periksa kembali data yang diisi.',
                icon: 'error',
                confirmButtonText: 'Coba Lagi',
                confirmButtonColor: '#058a84'
            });
        });
    });
</script>
