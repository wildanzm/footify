<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

new #[Layout('components.layouts.app')] #[Title('Profil Saya')] class extends Component {
    public string $name = '';
    public string $email = '';
    public bool $isEditing = false;

    public function getInitials(): string
    {
        $trimmedName = trim($this->name);
        if ($trimmedName === '') {
            return 'U';
        }

        $parts = preg_split('/\s+/', $trimmedName) ?: [];
        $firstInitial = strtoupper(substr($parts[0] ?? 'U', 0, 1));
        $lastInitial = '';
        if (count($parts) > 1) {
            $lastInitial = strtoupper(substr($parts[count($parts) - 1], 0, 1));
        }

        return $firstInitial . $lastInitial;
    }

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = (string) ($user->name ?? '');
        $this->email = (string) ($user->email ?? '');
    }

    public function updateProfile(): void
    {
        $userId = Auth::id();

        $validated = $this->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            ],
            [
                'name.required' => 'Nama wajib diisi.',
                'name.string' => 'Nama harus berupa teks.',
                'name.max' => 'Nama maksimal :max karakter.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal :max karakter.',
                'email.unique' => 'Email sudah digunakan.',
            ],
        );

        $user = Auth::user();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        $this->dispatch('profile-updated');
        $this->isEditing = false;
    }

    public function startEditing(): void
    {
        $user = Auth::user();
        $this->name = (string) ($user->name ?? '');
        $this->email = (string) ($user->email ?? '');
        $this->resetValidation();
        $this->isEditing = true;
    }

    public function cancelEditing(): void
    {
        $user = Auth::user();
        $this->name = (string) ($user->name ?? '');
        $this->email = (string) ($user->email ?? '');
        $this->resetValidation();
        $this->isEditing = false;
    }

    public function deleteAccount(): void
    {
        $user = Auth::user();
        Auth::logout();
        $user->delete();
        session()->invalidate();
        session()->regenerateToken();

        $this->dispatch('account-deleted');
        $this->redirect(route('login', absolute: false), navigate: true);
    }
}; ?>

<div class="space-y-8">
    <header>
        <h1 class="text-3xl font-bold text-slate-800">Profil Saya</h1>
        <p class="mt-1 text-slate-500">Kelola informasi akun Anda.</p>
    </header>

    <!-- User Info Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-md border border-slate-200">

        @if (!$isEditing)
            <div class="flex items-center gap-4 md:gap-6">
                <div
                    class="h-20 w-20 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-semibold shrink-0">
                    {{ $this->getInitials() }}
                </div>
                <div class="flex-1">
                    <div>
                        <div class="text-lg font-semibold text-slate-800">{{ $name }}</div>
                        <div class="text-sm text-slate-500 break-words">{{ $email }}</div>
                    </div>
                    <div class="mt-3">
                        <button type="button" wire:click="startEditing"
                            class="inline-flex items-center justify-center gap-2 text-white bg-primary hover:bg-primary/90 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-4 py-2">
                            Edit Profil
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Nama</label>
                    <input id="name" type="text" wire:model="name" placeholder="Nama lengkap" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" />
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email</label>
                    <input id="email" type="email" wire:model="email" placeholder="email@contoh.com" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" />
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="mt-6 flex items-center gap-3">
                <button type="button" wire:click="updateProfile"
                    class="inline-flex items-center justify-center gap-2 text-white bg-primary hover:bg-primary/90 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5">
                    Simpan
                </button>
                <button type="button" wire:click="cancelEditing"
                    class="inline-flex items-center justify-center gap-2 text-slate-700 bg-slate-100 hover:bg-slate-200 focus:ring-4 focus:outline-none focus:ring-slate-200 font-medium rounded-lg text-sm px-5 py-2.5">
                    Batal
                </button>
            </div>
        @endif
    </div>

    <!-- Danger Zone -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-md border border-red-200">
        <h2 class="text-xl font-semibold text-red-700 mb-2">Hapus Akun</h2>
        <p class="text-sm text-slate-600">Tindakan ini akan menghapus akun Anda secara permanen.</p>
        <div class="mt-4">
            <button type="button" id="delete-account-btn"
                class="inline-flex items-center justify-center gap-2 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5">
                Hapus Akun
            </button>
        </div>
    </div>

    <!-- Application Description Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-md border border-slate-200">
        <h2 class="text-2xl font-semibold text-primary mb-2">Footify</h2>
        <p class="text-md font-medium text-slate-500 italic mb-6 pb-4 border-b border-slate-200">
            "Langkahmu Berarti, Footify yang peduli"
        </p>
        <div class="prose max-w-none text-slate-600">
            <p>
                <strong>Footify</strong> adalah aplikasi skrining kaki diabetik berbasis <strong>Inlow's 60-Second
                    Diabetic Foot Screen</strong> yang dirancang untuk membantu tenaga kesehatan mendeteksi risiko kaki
                diabetik
                secara cepat, akurat, dan terdokumentasi. Aplikasi ini mendukung klasifikasi risiko otomatis dan
                memberikan rekomendasi perawatan berdasarkan
                standar dari <strong>Wounds Canada</strong>.
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('profile-updated', () => {
            Swal.fire({
                title: 'Berhasil disimpan',
                text: 'Perubahan profil Anda telah diperbarui.',
                icon: 'success',
                timer: 1800,
                showConfirmButton: false,
                confirmButtonColor: '#058a84'
            });
        });

        Livewire.on('account-deleted', () => {
            Swal.fire({
                title: 'Akun dihapus',
                text: 'Akun Anda telah dihapus.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
                confirmButtonColor: '#058a84'
            });
        });
    });

    // Confirm before deleting account
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('delete-account-btn');
        if (btn) {
            btn.addEventListener('click', () => {
                Swal.fire({
                    title: 'Hapus akun?',
                    text: 'Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.Livewire.find(document.querySelector('[wire\\:id]')
                            ?.getAttribute('wire:id'))?.call('deleteAccount');
                    }
                });
            });
        }
    });
</script>
