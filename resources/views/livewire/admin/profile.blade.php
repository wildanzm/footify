<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

// Using main layout and setting page title
new #[Layout('components.layouts.app')] #[Title('Profil')] class extends Component {
    // No logic needed here as this is a static page
}; ?>

{{-- View Section (HTML) --}}
<div class="space-y-8">
    {{-- Page Header --}}
    <header>
        <h1 class="text-3xl font-bold text-slate-800">Profil</h1>
        <p class="mt-1 text-slate-500">Informasi mengenai aplikasi Footify dan profil pembuat.</p>
    </header>

    {{-- Developer Profile Card --}}
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-md border border-slate-200">
        <h2 class="text-2xl font-semibold text-primary mb-6 pb-4 border-b border-slate-200">Profil Pembuat</h2>
        <div class="flex flex-col md:flex-row items-center gap-6 md:gap-8">
            {{-- Developer Photo --}}
            <div class="flex-shrink-0">
                <img class="h-40 w-40 rounded-full object-contain shadow-lg" src="{{ asset('images/NADILAs.jpg') }}"
                    alt="Foto Nadila Amanda Dwi">
            </div>
            {{-- Text Details --}}
            <div class="text-center md:text-left">
                <p class="text-xl font-bold text-slate-800">Nadila Amanda Dwi</p>
                <p class="text-md text-slate-600 mt-1">D-III Rekam Medis dan Informasi Kesehatan</p>
                <p class="text-sm text-slate-500 mt-2">Politeknik Kesehatan Kemenkes Tasikmalaya</p>
            </div>
        </div>
    </div>

    {{-- Application Description Card --}}
    {{-- Application Description Card --}}
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-md border border-slate-200">
        <h2 class="text-2xl font-semibold text-primary mb-2">Footify</h2>
        <p class="text-md font-medium text-slate-500 italic mb-6 pb-4 border-b border-slate-200">
            "Langkahmu Berarti, Footify yang peduli"
        </p>
        {{-- Application Description Content --}}
        <div class="prose max-w-none text-slate-600">
            <p>
                <strong>Footify</strong> adalah aplikasi skrining kaki diabetik berbasis <strong>Inlow's 60-Second
                    Diabetic Foot Screen</strong> yang dirancang khusus untuk membantu tenaga kesehatan dalam mendeteksi
                risiko kaki diabetik secara cepat, akurat, dan terdokumentasi.
            </p>
            <p>
                Aplikasi ini mendukung klasifikasi risiko otomatis dan memberikan rekomendasi perawatan berdasarkan
                standar dari <strong>Wounds Canada</strong>.
            </p>
        </div>
    </div>
</div>
