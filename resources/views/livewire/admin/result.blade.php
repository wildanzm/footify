<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Screening;

new #[Layout('components.layouts.app', ['title' => 'Hasil Skrining'])] #[Title('Hasil Skrining')] class extends Component {
    public Screening $screening;
    public $recommendations = [];
    public $editMode = false;
    public $notesEditMode = false;
    public $notes = '';

    // Recommendation list based on risk level
    public $recommendationsByRisk = [
        'Sangat Rendah' => ['Edukasi tentang: faktor risiko; inspeksi kaki harian; alas kaki yang tepat dan perawatan kaki dan kuku; kapan/bagaimana mencari perhatian medis jika diperlukan', 'Inspeksi kaki harian', 'Perawatan kaki dan kuku yang tepat', 'Alas kaki yang pas', 'Olahraga sesuai kemampuan'],
        'Rendah' => ['Edukasi tentang: faktor risiko (termasuk LOPS atau PAD); inspeksi kaki harian; alas kaki yang tepat dan perawatan kaki dan kuku; kapan/bagaimana mencari perhatian medis jika diperlukan', 'Inspeksi kaki harian', 'Perawatan kaki dan kuku profesional, termasuk pengobatan onikomikosis dan Tinea pedis jika ada', 'Alas kaki yang pas dan masuk akal dengan ortosis kaki kontak penuh khusus dan kaus kaki diabetes', 'Studi vaskular ± rujukan ke investigasi vaskular +/- ahli bedah vaskular', 'Manajemen nyeri untuk nyeri iskemik, jika ada', 'Rekomendasikan program latihan non-weight bearing'],
        'Sedang' => ['Edukasi tentang: faktor risiko (termasuk LOPS ± PAD ± deformitas kaki); inspeksi kaki harian; alas kaki yang tepat dan perawatan kaki dan kuku; kapan/bagaimana mencari perhatian medis jika diperlukan', 'Inspeksi kaki harian', 'Perawatan kaki dan kuku profesional, pengobatan onikomikosis dan Tinea pedis jika ada', 'Alas kaki ortopedi yang pas dengan ortosis kaki kontak total yang dicor kontak penuh khusus dan kaus kaki diabetes. Alas kaki harus mengakomodasi deformitas yang ada', 'Studi vaskular ± rujukan ke ahli bedah vaskular', 'Manajemen nyeri untuk nyeri iskemik atau neuropatik', 'Rujukan ke ahli bedah umum, ortopedi atau kaki, jika diindikasikan, untuk mengelola deformitas kaki secara bedah', 'Rekomendasikan program latihan non-weight bearing'],
        'Tinggi' => ['Edukasi tentang: faktor risiko (termasuk LOPS ± PAD ± deformitas kaki); risiko kekambuhan ulkus; inspeksi kaki harian; alas kaki yang tepat dan perawatan kaki dan kuku; kapan/bagaimana mencari perhatian medis jika diperlukan', 'Inspeksi kaki harian', 'Perawatan kaki dan kuku profesional, termasuk pengobatan onikomikosis dan Tinea pedis, jika ada', 'Alas kaki ortopedi yang pas dengan ortosis kaki kontak total yang dicor kontak penuh khusus dan kaus kaki diabetes. Alas kaki harus mengakomodasi deformitas yang ada', 'Alas kaki yang dimodifikasi dan/atau prostetik berdasarkan tingkat amputasi', 'Studi vaskular ± rujukan ke ahli bedah vaskular', 'Manajemen nyeri untuk nyeri iskemik atau neuropatik', 'Rekomendasikan program latihan non-weight bearing'],
        'Darurat' => ['Edukasi tentang: tanda-tanda infeksi luka dan perawatan luka; faktor risiko (LOPS ± PAD ± deformitas kaki); risiko kekambuhan ulkus; inspeksi kaki harian; alas kaki yang tepat dan perawatan kaki dan kuku; kapan/bagaimana mencari perhatian medis', 'Inspeksi kaki harian', 'Perawatan kaki dan kuku profesional, termasuk pengobatan onikomikosis dan Tinea pedis, jika ada', 'Offloading dengan total contact cast, removable cast walker atau sepatu luka untuk menutup ulkus dan/atau untuk immobilisasi kaki Charcot', 'Studi vaskular ± rujukan ke ahli bedah vaskular atau klinik preservasi anggota tubuh, sesuai indikasi', 'Manajemen nyeri untuk nyeri iskemik atau neuropatik', 'Rujukan ke ahli bedah umum, ortopedi atau kaki, jika diindikasikan, untuk mengelola deformitas kaki secara bedah', 'Rujukan ke penyakit menular untuk mengelola infeksi, jika diindikasikan, dan/atau ke ahli bedah umum, ortopedi atau kaki untuk debridement jaringan infeksi ± tulang, jika diindikasikan'],
    ];

    /**
     * Initialize component with screening data
     */
    public function mount(Screening $screening)
    {
        $this->screening = $screening;
        $this->recommendations = $screening->recommendation ?? [];
        $this->notes = $screening->notes ?? '';
    }

    /**
     * Toggle edit mode for recommendations
     */
    public function toggleEditMode()
    {
        $this->editMode = !$this->editMode;
    }

    /**
     * Toggle edit mode for notes
     */
    public function toggleNotesEditMode()
    {
        $this->notesEditMode = !$this->notesEditMode;
        
        // Reset notes to original value if canceling
        if (!$this->notesEditMode) {
            $this->notes = $this->screening->notes ?? '';
        }
    }

    /**
     * Update recommendations and dispatch success event
     */
    public function updateRecommendations()
    {
        $this->screening->update([
            'recommendation' => array_values($this->recommendations),
        ]);

        $this->editMode = false;

        // Dispatch event for SweetAlert notification
        $this->dispatch('recommendations-updated', ['status' => 'success']);
    }

    /**
     * Update notes and dispatch success event
     */
    public function updateNotes()
    {
        $this->screening->update([
            'notes' => $this->notes,
        ]);

        $this->notesEditMode = false;

        // Dispatch event for SweetAlert notification
        $this->dispatch('notes-updated', ['status' => 'success']);
    }

    /**
     * Get available recommendations based on risk level
     */
    public function getAvailableRecommendations()
    {
        $riskLevel = $this->screening->risk_classification;
        return $this->recommendationsByRisk[$riskLevel] ?? [];
    }
};
?>

<div class="space-y-8">
    <!-- Page Header -->
    <header>
        <h1 class="text-3xl font-bold text-slate-800">Hasil Skrining Pasien</h1>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Results Column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- 1. Patient Identity Section --}}
            <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-200">
                <h2 class="text-xl font-semibold text-primary mb-4 pb-4 border-b border-slate-200">Identitas Pasien</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="font-medium text-slate-500">Nama Lengkap</dt>
                        <dd class="text-slate-800 font-semibold">{{ $screening->patient->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Tanggal Lahir</dt>
                        <dd class="text-slate-800 font-semibold">
                            {{ \Carbon\Carbon::parse($screening->patient->date_of_birth)->locale('id')->translatedFormat('d F Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Usia</dt>
                        <dd class="text-slate-800 font-semibold">{{ $screening->patient->age }} tahun</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Jenis Kelamin</dt>
                        <dd class="text-slate-800 font-semibold">{{ $screening->patient->gender }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Pendidikan Terakhir</dt>
                        <dd class="text-slate-800 font-semibold">{{ $screening->patient->last_education }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Pekerjaan</dt>
                        <dd class="text-slate-800 font-semibold">{{ $screening->patient->occupation }}</dd>
                    </div>
                </div>
            </div>

            {{-- 2. Risk Classification Section --}}
            <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-200">
                <h2 class="text-xl font-semibold text-primary mb-4 pb-4 border-b border-slate-200">Klasifikasi Risiko
                </h2>
                <div class="w-full p-6 rounded-lg text-center text-white font-bold"
                    style="background-color: 
                        @if ($screening->risk_classification === 'Sangat Rendah') #058a84
                        @elseif($screening->risk_classification === 'Rendah') #ffd867
                        @elseif($screening->risk_classification === 'Sedang') #faba5c
                        @elseif($screening->risk_classification === 'Tinggi') #ef810b
                        @elseif($screening->risk_classification === 'Darurat') #d0222a
                        @else #6b7280 @endif">
                    <p class="text-3xl font-bold">{{ $screening->risk_classification }}</p>
                </div>
            </div>

            {{-- 3. Treatment Recommendations Section --}}
            <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-200">
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-slate-200">
                    <h2 class="text-xl font-semibold text-primary">Rekomendasi Tindakan</h2>
                    <button wire:click="toggleEditMode"
                        class="px-4 py-2 text-sm font-medium rounded-lg {{ $editMode ? 'bg-gray-200 text-gray-700' : 'bg-primary text-white' }} hover:opacity-80">
                        {{ $editMode ? 'Batal' : 'Edit Rekomendasi' }}
                    </button>
                </div>

                @if ($editMode)
                    {{-- Edit Mode with Checkboxes --}}
                    <form wire:submit.prevent="updateRecommendations" class="space-y-4">
                        <div class="space-y-3">
                            @foreach ($this->getAvailableRecommendations() as $recommendation)
                                <label class="flex items-start space-x-3 cursor-pointer">
                                    <input type="checkbox" wire:model="recommendations" value="{{ $recommendation }}"
                                        class="mt-1 w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary">
                                    <span class="text-slate-700 text-sm leading-relaxed">{{ $recommendation }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" wire:click="toggleEditMode"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-teal-700 disabled:opacity-50">
                                <span wire:loading.remove>Simpan Rekomendasi</span>
                                <span wire:loading>Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                @else
                    {{-- View Mode with Checklist --}}
                    @if (count($recommendations) > 0)
                        <div class="space-y-3">
                            @foreach ($recommendations as $recommendation)
                                <div class="flex items-start space-x-3">
                                    <div class="mt-1">
                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <span class="text-slate-700 text-sm leading-relaxed">{{ $recommendation }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-slate-500 mb-4">Belum ada rekomendasi yang dipilih.</p>
                            <button wire:click="toggleEditMode"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-teal-700">
                                Pilih Rekomendasi
                            </button>
                        </div>
                    @endif
                @endif
            </div>

            {{-- 4. Clinical Notes Section --}}
            <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-200">
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-slate-200">
                    <h2 class="text-xl font-semibold text-primary">Catatan Klinis</h2>
                    <button wire:click="toggleNotesEditMode"
                        class="px-4 py-2 text-sm font-medium rounded-lg {{ $notesEditMode ? 'bg-gray-200 text-gray-700' : 'bg-primary text-white' }} hover:opacity-80">
                        {{ $notesEditMode ? 'Batal' : 'Edit Catatan' }}
                    </button>
                </div>

                @if ($notesEditMode)
                    {{-- Edit Mode with Textarea --}}
                    <form wire:submit.prevent="updateNotes" class="space-y-4">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-slate-700 mb-2">
                                Catatan Klinis
                            </label>
                            <textarea wire:model="notes" id="notes" rows="6"
                                class="w-full px-3 text-gray-900 py-2 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary resize-y"
                                placeholder="Masukkan catatan klinis, observasi tambahan, atau rekomendasi khusus untuk pasien ini..."></textarea>
                            <p class="mt-1 text-xs text-slate-500">
                                Catatan ini akan membantu dalam follow-up dan evaluasi selanjutnya.
                            </p>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" wire:click="toggleNotesEditMode"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-teal-700 disabled:opacity-50">
                                <span wire:loading.remove>Simpan Catatan</span>
                                <span wire:loading>Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                @else
                    {{-- View Mode with Notes Display --}}
                    @if (!empty(trim($notes)))
                        <div class="prose prose-sm max-w-none">
                            <div class="bg-slate-50 p-4 rounded-lg border-l-4 border-primary">
                                <div class="flex items-start space-x-3">
                                    <div class="mt-1">
                                        <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $notes }}</p>
                                        <p class="text-xs text-slate-500 mt-2">
                                            Terakhir diupdate: {{ $screening->updated_at->locale('id')->translatedFormat('d F Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="mx-auto w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </div>
                            <p class="text-slate-500 mb-4">Belum ada catatan klinis yang ditambahkan.</p>
                            <button wire:click="toggleNotesEditMode"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-teal-700">
                                Tambah Catatan
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Additional Information Column --}}
        <div class="space-y-6">
            <!-- Laboratory Data Card -->
            <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-200">
                <h2 class="text-lg font-semibold text-primary mb-3">Data Lab</h2>
                <dl class="text-sm space-y-3">
                    <div>
                        <dt class="font-medium text-slate-500">Jenis Tes</dt>
                        <dd class="text-slate-800 font-semibold uppercase">{{ $screening->blood_sugar_type }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Hasil</dt>
                        <dd class="text-slate-800 font-semibold">{{ $screening->blood_sugar_value }} mg/dL</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Status</dt>
                        <dd class="text-slate-800 font-semibold">{{ $screening->blood_sugar_status }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Screening Information Card -->
            <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-200">
                <h2 class="text-lg font-semibold text-primary mb-3">Informasi Skrining</h2>
                <dl class="text-sm space-y-3">
                    <div>
                        <dt class="font-medium text-slate-500">Tanggal Skrining</dt>
                        <dd class="text-slate-800 font-semibold">
                            {{ $screening->created_at->locale('id')->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Waktu</dt>
                        <dd class="text-slate-800 font-semibold">{{ $screening->created_at->format('H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Action Buttons Section -->
    <div class="flex justify-end space-x-4 pt-4 border-t">
        <a href="{{ route('reports') }}" wire:navigate
            class="text-slate-700 bg-slate-200 hover:bg-slate-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Lihat
            Semua Histori</a>
        <a href="{{ route('screening.pdf', $screening) }}" target="_blank"
            class="text-white bg-primary hover:bg-teal-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Download Hasil Skrining
        </a>
    </div>
</div>

<!-- JavaScript Section -->
<script>
    /**
     * Function to register SweetAlert listeners for result page
     */
    function registerSweetAlertListeners() {
        // Wait for Livewire to be available
        if (typeof window.Livewire === 'undefined') {
            setTimeout(registerSweetAlertListeners, 500);
            return;
        }

        // Check if SweetAlert is available
        if (typeof Swal === 'undefined') {
            return;
        }

        try {
            // Remove previous listeners to avoid duplicates
            window.Livewire.off('recommendations-updated');
            window.Livewire.off('notes-updated');
        } catch (e) {
            // No previous listeners to remove
        }
        
        // Register recommendations update listener
        window.Livewire.on('recommendations-updated', (event) => {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Rekomendasi tindakan telah berhasil diperbarui.',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#058a84',
                timer: 3000,
                timerProgressBar: true
            });
        });

        // Register notes update listener
        window.Livewire.on('notes-updated', (event) => {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Catatan klinis telah berhasil disimpan.',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#058a84',
                timer: 3000,
                timerProgressBar: true
            });
        });
    }

    /**
     * Function to initialize all components
     */
    function initializeComponents() {
        registerSweetAlertListeners();
    }

    /**
     * Initialize on DOM ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        initializeComponents();
    });

    /**
     * Initialize on Livewire init
     */
    document.addEventListener('livewire:init', () => {
        initializeComponents();
    });

    // Re-initialize on Livewire navigation (SPA)
    document.addEventListener('livewire:navigated', () => {
        setTimeout(() => {
            initializeComponents();
        }, 100);
    });

    // Re-initialize when component is updated
    document.addEventListener('livewire:updated', () => {
        setTimeout(() => {
            initializeComponents();
        }, 100);
    });

    // Additional event for when Livewire loads
    document.addEventListener('livewire:load', () => {
        initializeComponents();
    });

    // Fallback initialization with delay
    setTimeout(() => {
        initializeComponents();
    }, 1000);
</script>
