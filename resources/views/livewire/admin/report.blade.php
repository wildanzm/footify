<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Screening;
use Livewire\WithPagination;

new #[Layout('components.layouts.app', ['title' => 'Histori Skrining'])] #[Title('Histori Skrining')] class extends Component {
    use WithPagination;

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    /**
     * Reset pagination when search term is updated
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Handle column sorting functionality
     */
    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Delete screening record with SweetAlert confirmation
     */
    public function deleteScreening($screeningId)
    {
        $screening = Screening::with('patient')->findOrFail($screeningId);
        $patient = $screening->patient;
        
        // Delete the screening first
        $screening->delete();
        
        // Delete the associated patient if they exist
        if ($patient) {
            $patient->delete();
        }

        session()->flash('message', 'Data skrining dan pasien berhasil dihapus.');

        // Emit browser event for SweetAlert success notification
        $this->dispatch('screening-deleted');
    }

    /**
     * Download PDF report for specific screening
     * Redirects to PDF download route
     */
    public function downloadPdf($screeningId)
    {
        // Validate that screening exists
        $screening = Screening::findOrFail($screeningId);
        
        // Redirect to PDF download route
        return redirect()->route('screening.pdf', $screening);
    }

    /**
     * Provide data for the component view
     */
    public function with()
    {
        return [
            'screenings' => Screening::with('patient')
                ->when($this->search, function ($query) {
                    $query->whereHas('patient', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy($this->sortBy, $this->sortDirection)
                ->paginate(10),
        ];
    }
};
?>

<div class="space-y-6">
    <!-- Page Header Section -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-slate-800">Histori Skrining</h1>
                <p class="text-slate-600 mt-1">Kelola dan lihat riwayat skrining pasien</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Search Input Field -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="search" wire:model.live="search"
                        class="block w-full sm:w-80 p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary focus:border-primary"
                        placeholder="Cari nama pasien...">
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Mobile Cards View (Hidden on larger screens) -->
        <div class="block lg:hidden">
            @forelse($screenings as $index => $screening)
                <div class="p-4 border-b border-gray-200 last:border-b-0">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">No.
                                {{ $screenings->firstItem() + $index }}</span>
                            <div class="flex items-center space-x-2">
                                <!-- Mobile Action Buttons -->
                                <button onclick="downloadPdf({{ $screening->id }})"
                                    class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                    title="Unduh PDF">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                            d="M3 14.25C3.41421 14.25 3.75 14.5858 3.75 15C3.75 16.4354 3.75159 17.4365 3.85315 18.1919C3.9518 18.9257 4.13225 19.3142 4.40901 19.591C4.68577 19.8678 5.07435 20.0482 5.80812 20.1469C6.56347 20.2484 7.56459 20.25 9 20.25H15C16.4354 20.25 17.4365 20.2484 18.1919 20.1469C18.9257 20.0482 19.3142 19.8678 19.591 19.591C19.8678 19.3142 20.0482 18.9257 20.1469 18.1919C20.2484 17.4365 20.25 16.4354 20.25 15C20.25 14.5858 20.5858 14.25 21 14.25C21.4142 14.25 21.75 14.5858 21.75 15V15.0549C21.75 16.4225 21.75 17.5248 21.6335 18.3918C21.5125 19.2919 21.2536 20.0497 20.6517 20.6516C20.0497 21.2536 19.2919 21.5125 18.3918 21.6335C17.5248 21.75 16.4225 21.75 15.0549 21.75H8.94513C7.57754 21.75 6.47522 21.75 5.60825 21.6335C4.70814 21.5125 3.95027 21.2536 3.34835 20.6517C2.74643 20.0497 2.48754 19.2919 2.36652 18.3918C2.24996 17.5248 2.24998 16.4225 2.25 15.0549C2.25 15.0366 2.25 15.0183 2.25 15C2.25 14.5858 2.58579 14.25 3 14.25Z"
                                            fill="currentColor"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M12 16.75C12.2106 16.75 12.4114 16.6615 12.5535 16.5061L16.5535 12.1311C16.833 11.8254 16.8118 11.351 16.5061 11.0715C16.2004 10.792 15.726 10.8132 15.4465 11.1189L12.75 14.0682V3C12.75 2.58579 12.4142 2.25 12 2.25C11.5858 2.25 11.25 2.58579 11.25 3V14.0682L8.55353 11.1189C8.27403 10.8132 7.79963 10.792 7.49393 11.0715C7.18823 11.351 7.16698 11.8254 7.44648 12.1311L11.4465 16.5061C11.5886 16.6615 11.7894 16.75 12 16.75Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </button>
                                <a href="{{ route('screening.result', $screening) }}" wire:navigate
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Lihat Hasil">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd"
                                            d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                                <button onclick="confirmDelete({{ $screening->id }})"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Hapus">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 11V17" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M14 11V17" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M4 7H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                        <path
                                            d="M6 7H12H18V18C18 19.6569 16.6569 21 15 21H9C7.34315 21 6 19.6569 6 18V7Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                        <path d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $screening->patient->name }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $screening->patient->gender === 'Male' ? 'Laki-laki' : ($screening->patient->gender === 'Female' ? 'Perempuan' : $screening->patient->gender) }}
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Usia:</span>
                                <p class="font-medium text-gray-900">{{ $screening->patient->age }} tahun</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Jenis Tes:</span>
                                <p class="font-medium uppercase text-gray-900">{{ $screening->blood_sugar_type }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Status Gula Darah:</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $screening->blood_sugar_status === 'Normal'
                                        ? 'bg-green-100 text-green-800'
                                        : ($screening->blood_sugar_status === 'Prediabetes'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-red-100 text-red-800') }}">
                                    {{ $screening->blood_sugar_status === 'Normal'
                                        ? 'Normal'
                                        : ($screening->blood_sugar_status === 'Prediabetes'
                                            ? 'Prediabetes'
                                            : ($screening->blood_sugar_status === 'Diabetes'
                                                ? 'Diabetes'
                                                : $screening->blood_sugar_status)) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500">Klasifikasi Risiko:</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                                    style="background-color: 
                                        @if ($screening->risk_classification === 'Sangat Rendah') #058a84
                                        @elseif($screening->risk_classification === 'Rendah') #ffd867; color: #1f2937;
                                        @elseif($screening->risk_classification === 'Sedang') #faba5c
                                        @elseif($screening->risk_classification === 'Tinggi') #ef810b
                                        @elseif($screening->risk_classification === 'Darurat') #d0222a
                                        @else #6b7280 @endif">
                                    {{ $screening->risk_classification }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State for Mobile -->
                <div class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data skrining</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat skrining pertama Anda.</p>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View (Hidden on mobile devices) -->
        <div class="hidden lg:block overflow-x-auto scrollbar-hide">
            <table class="w-full text-sm text-left text-gray-500 min-w-max">
                <!-- Table Header -->
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">No</th>
                        <th scope="col"
                            class="px-4 py-4 font-medium cursor-pointer hover:bg-gray-100 whitespace-nowrap"
                            wire:click="sortBy('patient.name')">
                            <div class="flex items-center">
                                Nama Pasien
                                @if ($sortBy === 'patient.name')
                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        @if ($sortDirection === 'asc')
                                            <path fill-rule="evenodd"
                                                d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                                clip-rule="evenodd"></path>
                                        @else
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Jenis Kelamin</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Usia</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Jenis Tes</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Status Gula Darah</th>
                        <th scope="col" class="px-4 py-4 font-medium whitespace-nowrap">Klasifikasi Risiko</th>
                        <th scope="col" class="px-4 py-4 font-medium text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <!-- Table Body -->
                <tbody>
                    @forelse($screenings as $index => $screening)
                        <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 font-medium text-gray-900 whitespace-nowrap">
                                {{ $screenings->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-4 font-medium text-gray-900 whitespace-nowrap">
                                <div class="max-w-xs truncate">{{ $screening->patient->name }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                {{ $screening->patient->gender === 'Male' ? 'Laki-laki' : ($screening->patient->gender === 'Female' ? 'Perempuan' : $screening->patient->gender) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                {{ $screening->patient->age }} tahun
                            </td>
                            <td class="px-4 py-4 uppercase font-medium whitespace-nowrap">
                                {{ $screening->blood_sugar_type }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $screening->blood_sugar_status === 'Normal'
                                        ? 'bg-green-100 text-green-800'
                                        : ($screening->blood_sugar_status === 'Prediabetes'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-red-100 text-red-800') }}">
                                    {{ $screening->blood_sugar_status === 'Normal'
                                        ? 'Normal'
                                        : ($screening->blood_sugar_status === 'Prediabetes'
                                            ? 'Prediabetes'
                                            : ($screening->blood_sugar_status === 'Diabetes'
                                                ? 'Diabetes'
                                                : $screening->blood_sugar_status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                                    style="background-color: 
                                        @if ($screening->risk_classification === 'Sangat Rendah') #058a84
                                        @elseif($screening->risk_classification === 'Rendah') #ffd867; color: #1f2937;
                                        @elseif($screening->risk_classification === 'Sedang') #faba5c
                                        @elseif($screening->risk_classification === 'Tinggi') #ef810b
                                        @elseif($screening->risk_classification === 'Darurat') #d0222a
                                        @else #6b7280 @endif">
                                    {{ $screening->risk_classification }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <!-- Desktop Action Buttons -->
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- PDF Download Button -->
                                    <button onclick="downloadPdf({{ $screening->id }})"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                        title="Unduh PDF">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                                d="M3 14.25C3.41421 14.25 3.75 14.5858 3.75 15C3.75 16.4354 3.75159 17.4365 3.85315 18.1919C3.9518 18.9257 4.13225 19.3142 4.40901 19.591C4.68577 19.8678 5.07435 20.0482 5.80812 20.1469C6.56347 20.2484 7.56459 20.25 9 20.25H15C16.4354 20.25 17.4365 20.2484 18.1919 20.1469C18.9257 20.0482 19.3142 19.8678 19.591 19.591C19.8678 19.3142 20.0482 18.9257 20.1469 18.1919C20.2484 17.4365 20.25 16.4354 20.25 15C20.25 14.5858 20.5858 14.25 21 14.25C21.4142 14.25 21.75 14.5858 21.75 15V15.0549C21.75 16.4225 21.75 17.5248 21.6335 18.3918C21.5125 19.2919 21.2536 20.0497 20.6517 20.6516C20.0497 21.2536 19.2919 21.5125 18.3918 21.6335C17.5248 21.75 16.4225 21.75 15.0549 21.75H8.94513C7.57754 21.75 6.47522 21.75 5.60825 21.6335C4.70814 21.5125 3.95027 21.2536 3.34835 20.6517C2.74643 20.0497 2.48754 19.2919 2.36652 18.3918C2.24996 17.5248 2.24998 16.4225 2.25 15.0549C2.25 15.0366 2.25 15.0183 2.25 15C2.25 14.5858 2.58579 14.25 3 14.25Z"
                                                fill="currentColor"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M12 16.75C12.2106 16.75 12.4114 16.6615 12.5535 16.5061L16.5535 12.1311C16.833 11.8254 16.8118 11.351 16.5061 11.0715C16.2004 10.792 15.726 10.8132 15.4465 11.1189L12.75 14.0682V3C12.75 2.58579 12.4142 2.25 12 2.25C11.5858 2.25 11.25 2.58579 11.25 3V14.0682L8.55353 11.1189C8.27403 10.8132 7.79963 10.792 7.49393 11.0715C7.18823 11.351 7.16698 11.8254 7.44648 12.1311L11.4465 16.5061C11.5886 16.6615 11.7894 16.75 12 16.75Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </button>
                                    <!-- View Result Button -->
                                    <a href="{{ route('screening.result', $screening) }}" wire:navigate
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Lihat Hasil">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd"
                                                d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                    <!-- Delete Button -->
                                    <button onclick="confirmDelete({{ $screening->id }})"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Hapus">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10 11V17" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M14 11V17" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M4 7H20" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path
                                                d="M6 7H12H18V18C18 19.6569 16.6569 21 15 21H9C7.34315 21 6 19.6569 6 18V7Z"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                            <path d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <!-- Empty State for Desktop Table -->
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data skrining</h3>
                                    <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat skrining pertama Anda.
                                    </p>
                                    <a href="{{ route('screenings') }}" wire:navigate
                                        class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-teal-700">
                                        Mulai Skrining
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        @if ($screenings->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $screenings->links() }}
            </div>
        @endif
    </div>
</div>

<!-- JavaScript Section -->
<script>
    /**
     * Download PDF report for specific screening
     * @param {number} screeningId - The ID of the screening to download
     */
    function downloadPdf(screeningId) {
        // Call Livewire method to download PDF
        @this.call('downloadPdf', screeningId);
    }

    /**
     * Show SweetAlert for PDF download feature (fallback function)
     * Currently displays info about feature being under development
     */
    function showPdfAlert() {
        Swal.fire({
            title: 'Fitur Dalam Pengembangan',
            text: 'Fitur unduh PDF sedang dalam tahap pengembangan dan akan segera tersedia.',
            icon: 'info',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#058a84'
        });
    }

    /**
     * Show SweetAlert confirmation for deleting screening record
     * @param {number} screeningId - The ID of the screening to delete
     */
    function confirmDelete(screeningId) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus data skrining ini? Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deleteScreening', screeningId);
            }
        });
    }

    /**
     * Function to register SweetAlert listener for screening deletion
     */
    function registerSweetAlertListener() {
        // Wait for Livewire to be available
        if (typeof window.Livewire === 'undefined') {
            setTimeout(registerSweetAlertListener, 500);
            return;
        }

        // Check if SweetAlert is available
        if (typeof Swal === 'undefined') {
            return;
        }

        try {
            // Remove previous listener to avoid duplicates
            window.Livewire.off('screening-deleted');
        } catch (e) {
            // No previous listener to remove
        }
        
        // Register new listener
        window.Livewire.on('screening-deleted', () => {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Data skrining telah berhasil dihapus.',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#058a84'
            });
        });
    }

    /**
     * Function to initialize all components
     */
    function initializeComponents() {
        registerSweetAlertListener();
    }

    /**
     * Listen for Livewire events and show appropriate SweetAlert notifications
     */
    document.addEventListener('DOMContentLoaded', function() {
        initializeComponents();
    });

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
