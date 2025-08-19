<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use App\Models\Patient;
use App\Services\ScreeningService;

/**
 * Diabetic foot screening component using main layout with page title
 * Implements Inlow's 60-Second Diabetic Foot Screen methodology
 */
new #[Layout('components.layouts.app', ['title' => 'Skrining'])] #[Title('Skrining')] class extends Component {
    // UI state control properties
    public bool $isTabIdentityCompleted = false;
    public bool $isTabSkinNailsCompleted = false;
    public bool $isTabSensationCompleted = false;
    public bool $isTabPadCompleted = false;
    public bool $isTabDeformityCompleted = false;

    // TAB 1: Patient Identity and Laboratory Data
    #[Validate('required|string|max:255')]
    public string $patient_name = '';
    #[Validate('required|date')]
    public string $patient_dob = '';
    public string $patient_dob_display = '';
    public string $age = '';
    #[Validate('required|in:Laki-laki,Perempuan')]
    public string $gender = '';
    #[Validate('nullable|string')]
    public string $last_education = '';
    #[Validate('nullable|string|max:255')]
    public string $occupation = '';
    #[Validate('required|in:gds,gdp,hba1c')]
    public string $blood_sugar_type = 'gds';
    #[Validate('required|numeric')]
    public $blood_sugar_value;

    // TAB 2: Skin and Nails Assessment
    #[Validate('required|array|min:1')]
    public array $left_skin_scores = [];
    #[Validate('required|array|min:1')]
    public array $right_skin_scores = [];
    #[Validate('required|array|min:1')]
    public array $left_nails_scores = [];
    #[Validate('required|array|min:1')]
    public array $right_nails_scores = [];

    // TAB 3: Sensation Assessment
    #[Validate('required|integer')]
    public int $left_monofilament_score = 0;
    #[Validate('required|integer')]
    public int $right_monofilament_score = 0;
    public bool $left_sensation_numb = false;
    public bool $left_sensation_tingle = false;
    public bool $left_sensation_burn = false;
    public bool $left_sensation_crawl = false;
    public bool $right_sensation_numb = false;
    public bool $right_sensation_tingle = false;
    public bool $right_sensation_burn = false;
    public bool $right_sensation_crawl = false;

    // TAB 4: Peripheral Arterial Disease (PAD) Assessment
    #[Validate('required|integer')]
    public int $left_pain_score = 0;
    #[Validate('required|integer')]
    public int $right_pain_score = 0;
    #[Validate('required|integer')]
    public int $left_rubor_score = 0;
    #[Validate('required|integer')]
    public int $right_rubor_score = 0;
    #[Validate('required|integer')]
    public int $left_temperature_score = 0;
    #[Validate('required|integer')]
    public int $right_temperature_score = 0;
    #[Validate('required|integer')]
    public int $left_pedal_pulse_score = 0;
    #[Validate('required|integer')]
    public int $right_pedal_pulse_score = 0;

    // TAB 5: Deformity and Range of Motion Assessment
    #[Validate('required|array|min:1')]
    public array $left_deformity_scores = [];
    #[Validate('required|array|min:1')]
    public array $right_deformity_scores = [];
    #[Validate('required|array|min:1')]
    public array $left_rom_scores = [];
    #[Validate('required|array|min:1')]
    public array $right_rom_scores = [];
    #[Validate('required|integer')]
    public int $footwear_score = 0;

    // TAB 6: Results and Recommendations
    #[Validate('nullable|array')]
    public array $recommendations = [];
    public ?string $risk_classification = null;
    public ?Patient $patient = null;

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    /**
     * Automatically calculate age when date of birth is updated
     */
    public function updatedPatientDob()
    {
        if (!empty($this->patient_dob)) {
            try {
                $dob = new \DateTime($this->patient_dob);
                $today = new \DateTime();
                $age = $today->diff($dob)->y;
                $this->age = (string) $age;
                $this->patient_dob_display = $dob->format('d-m-Y');
            } catch (\Exception $e) {
                $this->age = '';
                $this->patient_dob_display = '';
            }
        } else {
            $this->age = '';
            $this->patient_dob_display = '';
        }
    }

    /**
     * Component mount to initialize display format if data exists
     */
    public function mount()
    {
        if (!empty($this->patient_dob)) {
            $this->updatedPatientDob();
        }
    }

    /**
     * Validate specific step of the screening process
     *
     * @param string $step The step identifier to validate
     * @return void
     */
    public function validateStep($step)
    {
        switch ($step) {
            case 'identity':
                $this->validate(
                    [
                        'patient_name' => 'required|string|max:255',
                        'patient_dob' => 'required|date',
                        'gender' => 'required|in:Laki-laki,Perempuan',
                        'blood_sugar_type' => 'required|in:gds,gdp,hba1c',
                        'blood_sugar_value' => 'required|numeric',
                    ],
                    [
                        'patient_name.required' => 'Nama lengkap pasien wajib diisi.',
                        'patient_name.string' => 'Nama lengkap pasien harus berupa teks.',
                        'patient_name.max' => 'Nama lengkap pasien tidak boleh lebih dari 255 karakter.',
                        'patient_dob.required' => 'Tanggal lahir wajib diisi.',
                        'patient_dob.date' => 'Format tanggal lahir tidak valid.',
                        'gender.required' => 'Jenis kelamin wajib dipilih.',
                        'gender.in' => 'Jenis kelamin yang dipilih tidak valid.',
                        'blood_sugar_type.required' => 'Jenis pemeriksaan gula darah wajib dipilih.',
                        'blood_sugar_type.in' => 'Jenis pemeriksaan gula darah yang dipilih tidak valid.',
                        'blood_sugar_value.required' => 'Hasil pengukuran gula darah wajib diisi.',
                        'blood_sugar_value.numeric' => 'Hasil pengukuran gula darah harus berupa angka.',
                    ],
                );
                $this->isTabIdentityCompleted = true;
                break;

            case 'skin_nails':
                $this->validate(
                    [
                        'left_skin_scores' => 'required|array|min:1',
                        'right_skin_scores' => 'required|array|min:1',
                        'left_nails_scores' => 'required|array|min:1',
                        'right_nails_scores' => 'required|array|min:1',
                    ],
                    [
                        'left_skin_scores.required' => 'Kondisi kulit kaki kiri wajib dipilih.',
                        'left_skin_scores.array' => 'Kondisi kulit kaki kiri tidak valid.',
                        'left_skin_scores.min' => 'Pilih minimal satu kondisi kulit kaki kiri.',
                        'right_skin_scores.required' => 'Kondisi kulit kaki kanan wajib dipilih.',
                        'right_skin_scores.array' => 'Kondisi kulit kaki kanan tidak valid.',
                        'right_skin_scores.min' => 'Pilih minimal satu kondisi kulit kaki kanan.',
                        'left_nails_scores.required' => 'Kondisi kuku kaki kiri wajib dipilih.',
                        'left_nails_scores.array' => 'Kondisi kuku kaki kiri tidak valid.',
                        'left_nails_scores.min' => 'Pilih minimal satu kondisi kuku kaki kiri.',
                        'right_nails_scores.required' => 'Kondisi kuku kaki kanan wajib dipilih.',
                        'right_nails_scores.array' => 'Kondisi kuku kaki kanan tidak valid.',
                        'right_nails_scores.min' => 'Pilih minimal satu kondisi kuku kaki kanan.',
                    ],
                );
                $this->isTabSkinNailsCompleted = true;
                break;

            case 'sensation':
                $this->validate(
                    [
                        'left_monofilament_score' => 'required|integer',
                        'right_monofilament_score' => 'required|integer',
                    ],
                    [
                        'left_monofilament_score.required' => 'Hasil tes monofilamen kaki kiri wajib dipilih.',
                        'left_monofilament_score.integer' => 'Hasil tes monofilamen kaki kiri tidak valid.',
                        'right_monofilament_score.required' => 'Hasil tes monofilamen kaki kanan wajib dipilih.',
                        'right_monofilament_score.integer' => 'Hasil tes monofilamen kaki kanan tidak valid.',
                    ],
                );
                $this->isTabSensationCompleted = true;
                break;

            case 'pad':
                $this->validate(
                    [
                        'left_pain_score' => 'required|integer',
                        'right_pain_score' => 'required|integer',
                        'left_rubor_score' => 'required|integer',
                        'right_rubor_score' => 'required|integer',
                        'left_temperature_score' => 'required|integer',
                        'right_temperature_score' => 'required|integer',
                        'left_pedal_pulse_score' => 'required|integer',
                        'right_pedal_pulse_score' => 'required|integer',
                    ],
                    [
                        'left_pain_score.required' => 'Pemeriksaan nyeri kaki kiri wajib dipilih.',
                        'left_pain_score.integer' => 'Pemeriksaan nyeri kaki kiri tidak valid.',
                        'right_pain_score.required' => 'Pemeriksaan nyeri kaki kanan wajib dipilih.',
                        'right_pain_score.integer' => 'Pemeriksaan nyeri kaki kanan tidak valid.',
                        'left_rubor_score.required' => 'Pemeriksaan rubor dependen kaki kiri wajib dipilih.',
                        'left_rubor_score.integer' => 'Pemeriksaan rubor dependen kaki kiri tidak valid.',
                        'right_rubor_score.required' => 'Pemeriksaan rubor dependen kaki kanan wajib dipilih.',
                        'right_rubor_score.integer' => 'Pemeriksaan rubor dependen kaki kanan tidak valid.',
                        'left_temperature_score.required' => 'Pemeriksaan suhu kaki kiri wajib dipilih.',
                        'left_temperature_score.integer' => 'Pemeriksaan suhu kaki kiri tidak valid.',
                        'right_temperature_score.required' => 'Pemeriksaan suhu kaki kanan wajib dipilih.',
                        'right_temperature_score.integer' => 'Pemeriksaan suhu kaki kanan tidak valid.',
                        'left_pedal_pulse_score.required' => 'Pemeriksaan nadi pedal kaki kiri wajib dipilih.',
                        'left_pedal_pulse_score.integer' => 'Pemeriksaan nadi pedal kaki kiri tidak valid.',
                        'right_pedal_pulse_score.required' => 'Pemeriksaan nadi pedal kaki kanan wajib dipilih.',
                        'right_pedal_pulse_score.integer' => 'Pemeriksaan nadi pedal kaki kanan tidak valid.',
                    ],
                );
                $this->isTabPadCompleted = true;
                break;

            case 'deformity':
                $this->validate(
                    [
                        'left_deformity_scores' => 'required|array|min:1',
                        'right_deformity_scores' => 'required|array|min:1',
                        'left_rom_scores' => 'required|array|min:1',
                        'right_rom_scores' => 'required|array|min:1',
                        'footwear_score' => 'required|integer',
                    ],
                    [
                        'left_deformity_scores.required' => 'Pemeriksaan kelainan bentuk kaki kiri wajib dipilih.',
                        'left_deformity_scores.array' => 'Pemeriksaan kelainan bentuk kaki kiri tidak valid.',
                        'left_deformity_scores.min' => 'Pilih minimal satu kondisi kelainan bentuk kaki kiri.',
                        'right_deformity_scores.required' => 'Pemeriksaan kelainan bentuk kaki kanan wajib dipilih.',
                        'right_deformity_scores.array' => 'Pemeriksaan kelainan bentuk kaki kanan tidak valid.',
                        'right_deformity_scores.min' => 'Pilih minimal satu kondisi kelainan bentuk kaki kanan.',
                        'left_rom_scores.required' => 'Pemeriksaan rentang gerak kaki kiri wajib dipilih.',
                        'left_rom_scores.array' => 'Pemeriksaan rentang gerak kaki kiri tidak valid.',
                        'left_rom_scores.min' => 'Pilih minimal satu kondisi rentang gerak kaki kiri.',
                        'right_rom_scores.required' => 'Pemeriksaan rentang gerak kaki kanan wajib dipilih.',
                        'right_rom_scores.array' => 'Pemeriksaan rentang gerak kaki kanan tidak valid.',
                        'right_rom_scores.min' => 'Pilih minimal satu kondisi rentang gerak kaki kanan.',
                        'footwear_score.required' => 'Pemeriksaan alas kaki wajib dipilih.',
                        'footwear_score.integer' => 'Pemeriksaan alas kaki tidak valid.',
                    ],
                );
                $this->isTabDeformityCompleted = true;
                break;
        }
    }

    /**
     * Calculate screening result using ScreeningService
     *
     * @param ScreeningService $screeningService
     * @return void
     */
    public function calculateResult(ScreeningService $screeningService)
    {
        // Gather all raw data from properties
        $screeningData = $this->gatherScreeningData();

        // Call service to get calculations
        $totalScore = $screeningService->calculateTotalInlowsScore($screeningData);
        $riskProfile = $screeningService->getRiskProfile($screeningData);

        // Update component properties for UI display
        $this->risk_classification = $riskProfile['classification'];
        $this->recommendations = []; // Clear recommendations for re-selection
    }

    /**
     * Save screening data and create patient record
     *
     * @param ScreeningService $screeningService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(ScreeningService $screeningService)
    {
        $this->validate();

        $patient = Patient::updateOrCreate(
            ['name' => $this->patient_name, 'date_of_birth' => $this->patient_dob],
            [
                'age' => $this->age,
                'gender' => $this->gender,
                'occupation' => $this->occupation,
                'last_education' => $this->last_education,
            ],
        );

        // Gather all raw data from properties
        $screeningData = $this->gatherScreeningData();

        // Add data not included in score calculation
        $screeningData['blood_sugar_type'] = $this->blood_sugar_type;
        $screeningData['blood_sugar_value'] = $this->blood_sugar_value;
        $screeningData['recommendation'] = $this->recommendations;
        $screeningData['notes'] = $this->notes;

        // Call service to process data comprehensively
        $processedData = $screeningService->processScreening($screeningData);

        // Create new screening record
        $screening = $patient->screenings()->create($processedData);

        // Dispatch event for SweetAlert
        $this->dispatch('screening-saved', [
            'screeningId' => $screening->id,
        ]);

        session()->flash('status', 'Data skrining untuk pasien ' . $patient->name . ' berhasil disimpan.');
    }

    /**
     * Helper function to gather and process screening data from properties.
     *
     * @return array Processed screening data for service consumption
     */
    private function gatherScreeningData(): array
    {
        // Calculate score for 4 sensation questions based on boolean checkboxes
        $left_questions_score = $this->left_sensation_numb || $this->left_sensation_tingle || $this->left_sensation_burn || $this->left_sensation_crawl ? 2 : 0;
        $right_questions_score = $this->right_sensation_numb || $this->right_sensation_tingle || $this->right_sensation_burn || $this->right_sensation_crawl ? 2 : 0;

        // Combine monofilament score (now integer) and questions
        $total_left_sensation_score = $this->left_monofilament_score + $left_questions_score;
        $total_right_sensation_score = $this->right_monofilament_score + $right_questions_score;

        return [
            // Tab 2: Skin and Nails
            'left_skin_scores' => $this->left_skin_scores,
            'right_skin_scores' => $this->right_skin_scores,
            'left_nails_scores' => $this->left_nails_scores,
            'right_nails_scores' => $this->right_nails_scores,
            // Tab 3: Sensation
            'left_sensation_score' => $total_left_sensation_score,
            'right_sensation_score' => $total_right_sensation_score,
            // Tab 4: Peripheral Arterial Disease
            'left_pain_score' => $this->left_pain_score,
            'right_pain_score' => $this->right_pain_score,
            'left_rubor_score' => $this->left_rubor_score,
            'right_rubor_score' => $this->right_rubor_score,
            'left_temperature_score' => $this->left_temperature_score,
            'right_temperature_score' => $this->right_temperature_score,
            'left_pedal_pulse_score' => $this->left_pedal_pulse_score,
            'right_pedal_pulse_score' => $this->right_pedal_pulse_score,
            // Tab 5: Deformity and Range of Motion
            'left_deformity_scores' => $this->left_deformity_scores,
            'right_deformity_scores' => $this->right_deformity_scores,
            'left_rom_scores' => $this->left_rom_scores,
            'right_rom_scores' => $this->right_rom_scores,
            'footwear_score' => $this->footwear_score,
        ];
    }
};
?>

{{-- Main container with Alpine.js for state management --}}
<div x-data="{
    screeningStarted: false,
    activeTab: 'identity',
    isTabIdentityCompleted: @entangle('isTabIdentityCompleted'),
    isTabSkinNailsCompleted: @entangle('isTabSkinNailsCompleted'),
    isTabSensationCompleted: @entangle('isTabSensationCompleted'),
    isTabPadCompleted: @entangle('isTabPadCompleted'),
    isTabDeformityCompleted: @entangle('isTabDeformityCompleted'),
    progress: 0,

    updateProgress() {
        let completedSteps = 0;
        if (this.isTabIdentityCompleted) completedSteps++;
        if (this.isTabSkinNailsCompleted) completedSteps++;
        if (this.isTabSensationCompleted) completedSteps++;
        if (this.isTabPadCompleted) completedSteps++;
        if (this.isTabDeformityCompleted) completedSteps++;

        this.progress = Math.round((completedSteps / 4) * 100);
    }
}" x-init="$watch('isTabIdentityCompleted', value => updateProgress());
$watch('isTabSkinNailsCompleted', value => updateProgress());
$watch('isTabSensationCompleted', value => updateProgress());
$watch('isTabPadCompleted', value => updateProgress());">

    {{-- Welcome Page --}}
    <div x-show="!screeningStarted" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        class="text-center bg-white p-8 rounded-2xl shadow-md border border-slate-200">
        <div class="flex justify-center mb-4">
            <div class="p-3 bg-teal-100 rounded-full"><svg class="w-10 h-10 text-primary"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 72 72">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M67.625,56.738C67.592,56.688,67.66,56.787,67.625,56.738L47.823,28.167V5.42c0-1.105-0.539-1.92-1.643-1.92H27.179 c-1.104,0-1.356,0.816-1.356,1.92v22.097L4.904,56.764c-0.035,0.049-0.217,0.277-0.248,0.33c-1.688,2.926-2.078,5.621-0.785,7.86 c1.306,2.261,3.915,3.546,7.347,3.546h49.451c3.429,0,6.118-1.287,7.424-3.551C69.389,62.704,69.313,59.666,67.625,56.738z M29.137,29.302c0.237-0.338,0.687-0.74,0.687-1.152V7.5h14v21.301c0,0.412-0.194,0.824,0.044,1.161l9.19,13.262 c-3.056,1.232-6.822,2.05-14.531-2.557c-5.585-3.337-12.499-2.048-17.199-0.449L29.137,29.302z M64.55,62.949 c-0.554,0.96-1.969,1.551-3.88,1.551H11.219c-1.915,0-3.33-0.589-3.883-1.547c-0.532-0.922-0.324-2.391,0.571-3.975l11.287-15.777 c3.942-1.702,12.219-4.454,18.308-0.816c4.852,2.898,8.367,3.814,11.116,3.814c2.291,0,4.05-0.637,5.607-1.291l9.755,14.076 C64.877,60.568,65.085,62.023,64.55,62.949z">
                    </path>
                </svg></div>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 mt-4">Selamat Datang di Skrining Footify</h1>
        <p class="mt-2 text-slate-500 max-w-2xl mx-auto">Anda akan memulai proses skrining kaki diabetik. Pastikan semua
            data yang diinput akurat untuk hasil yang optimal.</p>

        <div class="mt-8">
            <button @click="screeningStarted = true" type="button"
                class="text-white bg-primary hover:bg-primary/90 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-semibold rounded-lg text-lg px-8 py-4 text-center transition-colors duration-200 shadow-lg hover:shadow-xl">
                Mulai Skrining
            </button>
        </div>
    </div>

    {{-- Screening Form --}}
    <div x-show="screeningStarted" x-transition>
        <form wire:submit.prevent="save" @change="updateProgress">
            {{-- Stepper (Progress Indicator & Navigation Buttons) --}}
            <div class="mb-6">
                <div class="flex justify-between mb-1"><span class="text-base font-medium text-primary">Progres
                        Skrining</span><span class="text-sm font-medium text-primary" x-text="progress + '%'"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-primary h-2.5 rounded-full transition-all duration-300"
                        :style="`width: ${progress}%`">
                    </div>
                </div>
            </div>
            {{-- Timeline Stepper - Responsive Design --}}
            <div class="mb-8">
                {{-- Desktop & Tablet View --}}
                <ol class="hidden md:flex items-center w-full text-sm font-medium text-center text-gray-500">
                    {{-- STEP 1: Identity & Laboratory --}}
                    <li
                        class="flex items-center w-full relative after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-1 after:inline-block after:mx-6">
                        <div :class="{ 'text-primary': activeTab === 'identity' || isTabIdentityCompleted }"
                            class="flex items-center whitespace-nowrap z-10 px-2">
                            <svg x-show="isTabIdentityCompleted" class="w-4 h-4 me-2.5" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                            </svg>
                            <span x-show="!isTabIdentityCompleted" class="me-2">1</span>
                            <span class="lg:inline hidden">Identitas & Lab</span>
                            <span class="lg:hidden inline">Identitas</span>
                        </div>
                    </li>
                    {{-- STEP 2: Skin & Nails --}}
                    <li
                        class="flex items-center w-full relative after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-1 after:inline-block after:mx-6">
                        <div :class="{
                            'text-primary': activeTab === 'skin_nails' || isTabSkinNailsCompleted,
                            'opacity-50': !isTabIdentityCompleted
                        }"
                            class="flex items-center whitespace-nowrap z-10 px-2">
                            <svg x-show="isTabSkinNailsCompleted" class="w-4 h-4 me-2.5" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                            </svg>
                            <span x-show="!isTabSkinNailsCompleted" class="me-2">2</span>
                            <span class="lg:inline hidden">Kulit & Kuku</span>
                            <span class="lg:hidden inline">Kulit</span>
                        </div>
                    </li>
                    {{-- STEP 3: Sensation --}}
                    <li
                        class="flex items-center w-full relative after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-1 after:inline-block after:mx-6">
                        <div :class="{
                            'text-primary': activeTab === 'sensation' || isTabSensationCompleted,
                            'opacity-50': !isTabSkinNailsCompleted
                        }"
                            class="flex items-center whitespace-nowrap z-10 px-2">
                            <svg x-show="isTabSensationCompleted" class="w-4 h-4 me-2.5" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                            </svg>
                            <span x-show="!isTabSensationCompleted" class="me-2">3</span>
                            Sensasi
                        </div>
                    </li>
                    {{-- STEP 4: Peripheral Arterial Disease --}}
                    <li
                        class="flex items-center w-full relative after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-1 after:inline-block after:mx-6">
                        <div :class="{
                            'text-primary': activeTab === 'pad' || isTabPadCompleted,
                            'opacity-50': !isTabSensationCompleted
                        }"
                            class="flex items-center whitespace-nowrap z-10 px-2">
                            <svg x-show="isTabPadCompleted" class="w-4 h-4 me-2.5" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                            </svg>
                            <span x-show="!isTabPadCompleted" class="me-2">4</span>
                            <span class="lg:inline hidden">Arteri Perifer</span>
                            <span class="lg:hidden inline">Arteri</span>
                        </div>
                    </li>
                    {{-- STEP 5: Deformity Assessment --}}
                    <li class="flex items-center">
                        <div :class="{
                            'text-primary': activeTab === 'deformity' || isTabDeformityCompleted,
                            'opacity-50': !isTabPadCompleted
                        }"
                            class="flex items-center whitespace-nowrap z-10 px-2">
                            <svg x-show="isTabDeformityCompleted" class="w-4 h-4 me-2.5" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                            </svg>
                            <span x-show="!isTabDeformityCompleted" class="me-2">5</span>
                            <span class="lg:inline hidden">Kelainan Bentuk</span>
                            <span class="lg:hidden inline">Bentuk</span>
                        </div>
                    </li>
                </ol>

                {{-- Mobile View --}}
                <div class="md:hidden space-y-3">
                    {{-- Current Step Indicator --}}
                    <div class="text-center">
                        <span class="text-sm text-gray-500">Langkah</span>
                        <span class="text-lg font-semibold text-primary ml-1"
                            x-text="activeTab === 'identity' ? '1 dari 5' : 
                                     activeTab === 'skin_nails' ? '2 dari 5' : 
                                     activeTab === 'sensation' ? '3 dari 5' : 
                                     activeTab === 'pad' ? '4 dari 5' : '5 dari 5'"></span>
                    </div>

                    {{-- Current Step Title --}}
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900"
                            x-text="activeTab === 'identity' ? 'Identitas & Lab' : 
                                   activeTab === 'skin_nails' ? 'Kulit & Kuku' : 
                                   activeTab === 'sensation' ? 'Sensasi' : 
                                   activeTab === 'pad' ? 'Arteri Perifer' : 'Kelainan Bentuk'">
                        </h3>
                    </div>

                    {{-- Mini Progress Dots (Visual Only) --}}
                    <div class="flex justify-center space-x-2">
                        <div :class="{
                            'bg-primary': activeTab === 'identity' ||
                                isTabIdentityCompleted,
                            'bg-gray-300': activeTab !== 'identity' && !
                                isTabIdentityCompleted
                        }"
                            class="w-3 h-3 rounded-full transition-colors duration-200"></div>
                        <div :class="{
                            'bg-primary': activeTab === 'skin_nails' || isTabSkinNailsCompleted,
                            'bg-gray-300': (activeTab !== 'skin_nails' && !isTabSkinNailsCompleted) || !
                                isTabIdentityCompleted,
                            'opacity-50': !isTabIdentityCompleted
                        }"
                            class="w-3 h-3 rounded-full transition-colors duration-200"></div>
                        <div :class="{
                            'bg-primary': activeTab === 'sensation' || isTabSensationCompleted,
                            'bg-gray-300': (activeTab !== 'sensation' && !isTabSensationCompleted) || !
                                isTabSkinNailsCompleted,
                            'opacity-50': !isTabSkinNailsCompleted
                        }"
                            class="w-3 h-3 rounded-full transition-colors duration-200"></div>
                        <div :class="{
                            'bg-primary': activeTab === 'pad' || isTabPadCompleted,
                            'bg-gray-300': (activeTab !== 'pad' && !isTabPadCompleted) || !
                                isTabSensationCompleted,
                            'opacity-50': !isTabSensationCompleted
                        }"
                            class="w-3 h-3 rounded-full transition-colors duration-200"></div>
                        <div :class="{
                            'bg-primary': activeTab === 'deformity' || isTabDeformityCompleted,
                            'bg-gray-300': (activeTab !== 'deformity' && !isTabDeformityCompleted) || !
                                isTabPadCompleted,
                            'opacity-50': !isTabPadCompleted
                        }"
                            class="w-3 h-3 rounded-full transition-colors duration-200"></div>
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-lg bg-white border border-slate-200 shadow-sm">

                <div wire:key="step-identity" x-show="activeTab === 'identity'" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                        {{-- Left Column --}}
                        <div class="space-y-5">
                            <div>
                                <label for="patient_name" class="block mb-2 text-sm font-medium text-gray-900">Nama
                                    Lengkap Pasien</label>
                                <input wire:model.lazy="patient_name" type="text" id="patient_name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                                    placeholder="Contoh: Budi Santoso">
                                <x-input-error :messages="$errors->get('patient_name')" class="mt-2" />
                            </div>
                            <div>
                                <label for="patient_dob" class="block mb-2 text-sm font-medium text-gray-900">
                                    Tanggal Lahir
                                    <span x-show="$wire.age"
                                        class="ml-2 px-2 py-1 bg-primary/10 text-primary text-xs rounded-full font-medium">
                                        Usia: <span x-text="$wire.age"></span> tahun
                                    </span>
                                </label>
                                <div class="relative">
                                    <input wire:model="patient_dob_display" type="text" id="patient_dob"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full pr-10 p-2.5"
                                        placeholder="Contoh: 15-08-1990" readonly>
                                    <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                        </svg>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('patient_dob')" class="mt-2" />
                            </div>
                            <div>
                                <label for="gender" class="block mb-2 text-sm font-medium text-gray-900">Jenis
                                    Kelamin</label>
                                <select wire:model="gender" id="gender"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>
                        </div>
                        {{-- Right Column --}}
                        <div class="space-y-5">
                            <div>
                                <label for="last_education"
                                    class="block mb-2 text-sm font-medium text-gray-900">Pendidikan Terakhir</label>
                                <select wire:model="last_education" id="last_education"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                    <option value="">Pilih pendidikan</option>
                                    <option value="Tidak Sekolah">Tidak Sekolah</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA">SMA</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                </select>
                                <x-input-error :messages="$errors->get('last_education')" class="mt-2" />
                            </div>
                            <div>
                                <label for="occupation"
                                    class="block mb-2 text-sm font-medium text-gray-900">Pekerjaan</label>
                                <input wire:model.lazy="occupation" type="text" id="occupation"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                                    placeholder="Contoh: Wiraswasta">
                                <x-input-error :messages="$errors->get('occupation')" class="mt-2" />
                            </div>
                        </div>
                        {{-- Laboratory Data Section --}}
                        <div class="md:col-span-2 pt-6 border-t border-slate-200">
                            <label class="block mb-3 text-sm font-medium text-gray-900">Data Laboratorium</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label
                                        class="flex items-center p-3 w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-100 has-[:checked]:border-primary has-[:checked]:ring-2 has-[:checked]:ring-primary"><input
                                            wire:model="blood_sugar_type" type="radio" name="blood_sugar_type"
                                            value="gds"
                                            class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary"><span
                                            class="ms-3">Gula Darah Sewaktu (GDS)</span></label>
                                    <label
                                        class="flex items-center p-3 w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-100 has-[:checked]:border-primary has-[:checked]:ring-2 has-[:checked]:ring-primary"><input
                                            wire:model="blood_sugar_type" type="radio" name="blood_sugar_type"
                                            value="gdp"
                                            class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary"><span
                                            class="ms-3">Gula Darah Puasa (GDP)</span></label>
                                    <label
                                        class="flex items-center p-3 w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-100 has-[:checked]:border-primary has-[:checked]:ring-2 has-[:checked]:ring-primary"><input
                                            wire:model="blood_sugar_type" type="radio" name="blood_sugar_type"
                                            value="hba1c"
                                            class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary"><span
                                            class="ms-3">HbA1c</span></label>
                                    <x-input-error :messages="$errors->get('blood_sugar_type')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="blood_sugar_value"
                                        class="block mb-2 text-sm font-medium text-gray-900">Hasil Pengukuran <span
                                            x-show="$wire.blood_sugar_type === 'hba1c'"
                                            class="text-xs text-gray-500">(dalam %)</span><span
                                            x-show="$wire.blood_sugar_type !== 'hba1c'"
                                            class="text-xs text-gray-500">(dalam mg/dL)</span></label>
                                    <input wire:model.lazy="blood_sugar_value" type="number" step="0.1"
                                        id="blood_sugar_value"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                                        placeholder="Contoh: 150 atau 6.5">
                                    <x-input-error :messages="$errors->get('blood_sugar_value')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 border-t pt-4">
                        <button @click="isTabIdentityCompleted ? activeTab = 'skin_nails' : null"
                            x-show="!isTabIdentityCompleted" wire:click="validateStep('identity')"
                            wire:loading.attr="disabled" type="button"
                            class="inline-flex items-center justify-center text-white bg-primary hover:bg-teal-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <span wire:loading.remove wire:target="validateStep('identity')">Selesai</span>
                            <span wire:loading wire:target="validateStep('identity')">Memeriksa...</span>
                        </button>
                        <button @click="activeTab = 'skin_nails'" x-show="isTabIdentityCompleted" type="button"
                            class="inline-flex items-center justify-center text-white bg-primary hover:bg-teal-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <span>Lanjut</span>
                            <svg class="w-5 h-5 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Skin & Nails Tab --}}
                <div wire:key="step-skin-nails" x-show="activeTab === 'skin_nails'" x-transition>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- CARD 1: SKIN EXAMINATION --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm">
                            <h4 class="font-semibold text-slate-800 text-lg">1. Skrining Perubahan Kulit</h4>
                            <p class="text-sm text-slate-500 mb-4">Pilih satu atau lebih kondisi kulit yang sesuai.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Foot Options --}}
                                <div>
                                    <h5 class="text-base text-slate-800 font-medium mb-3 text-center">Kaki Kiri</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="left_skin_scores" value="0" id="left-skin-0"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-skin-0"
                                                class="ms-2 text-sm font-medium text-gray-900">Kulit utuh, tidak ada
                                                bukti penyakit jamur atau penumpukan kalus</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_skin_scores" value="1_dry" id="left-skin-1"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-skin-1"
                                                class="ms-2 text-sm font-medium text-gray-900">Kulit kering</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_skin_scores" value="1_fungus" id="left-skin-2"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-skin-2"
                                                class="ms-2 text-sm font-medium text-gray-900">Terdapat jamur</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_skin_scores" value="1_callus_thin"
                                                id="left-skin-3" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-skin-3"
                                                class="ms-2 text-sm font-medium text-gray-900">Penumpukan kalus
                                                tipis</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_skin_scores" value="2" id="left-skin-4"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-skin-4"
                                                class="ms-2 text-sm font-medium text-gray-900">Penumpukan kalus yang
                                                tebal</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_skin_scores" value="3" id="left-skin-5"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-skin-5"
                                                class="ms-2 text-sm font-medium text-gray-900">Ulkus terbuka atau
                                                riwayat ulkus sebelumnya</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('left_skin_scores')" class="mt-2" />
                                </div>
                                {{-- Pilihan Kaki Kanan --}}
                                <div>
                                    <h5 class="text-base text-slate-800 font-medium mb-3 text-center">Kaki Kanan</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="right_skin_scores" value="0" id="right-skin-0"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-skin-0"
                                                class="ms-2 text-sm font-medium text-gray-900">Kulit utuh, tidak ada
                                                bukti penyakit jamur atau penumpukan kalus</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_skin_scores" value="1_dry" id="right-skin-1"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-skin-1"
                                                class="ms-2 text-sm font-medium text-gray-900">Kulit kering</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_skin_scores" value="1_fungus" id="right-skin-2"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-skin-2"
                                                class="ms-2 text-sm font-medium text-gray-900">Terdapat jamur</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_skin_scores" value="1_callus_thin"
                                                id="right-skin-3" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-skin-3"
                                                class="ms-2 text-sm font-medium text-gray-900">Penumpukan kalus
                                                tipis</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_skin_scores" value="2" id="right-skin-4"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-skin-4"
                                                class="ms-2 text-sm font-medium text-gray-900">Penumpukan kalus yang
                                                tebal</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_skin_scores" value="3" id="right-skin-5"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-skin-5"
                                                class="ms-2 text-sm font-medium text-gray-900">Ulkus terbuka atau
                                                riwayat ulkus sebelumnya</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('right_skin_scores')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        {{-- CARD 2: NAILS EXAMINATION --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm">
                            <h4 class="font-semibold text-slate-800 text-lg">2. Skrining Perubahan Kuku Kaki</h4>
                            <p class="text-sm text-slate-500 mb-4">Pilih satu atau lebih kondisi kuku yang sesuai.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Foot Options --}}
                                <div>
                                    <h5 class="text-base text-slate-800 font-medium mb-3 text-center">Kaki Kiri</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="left_nails_scores" value="0" id="left-nails-0"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-nails-0"
                                                class="ms-2 text-sm font-medium text-gray-900">Terawat baik</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_nails_scores" value="1" id="left-nails-1"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-nails-1"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak terawat dan
                                                kasar</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_nails_scores" value="2" id="left-nails-2"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-nails-2"
                                                class="ms-2 text-sm font-medium text-gray-900">Tebal, rusak, atau
                                                terinfeksi</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('left_nails_scores')" class="mt-2" />
                                </div>
                                {{-- Right Foot Options --}}
                                <div>
                                    <h5 class="text-base text-slate-800 font-medium mb-3 text-center">Kaki Kanan</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="right_nails_scores" value="0" id="right-nails-0"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-nails-0"
                                                class="ms-2 text-sm font-medium text-gray-900">Terawat baik</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_nails_scores" value="1" id="right-nails-1"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-nails-1"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak terawat dan
                                                kasar</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_nails_scores" value="2" id="right-nails-2"
                                                type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-nails-2"
                                                class="ms-2 text-sm font-medium text-gray-900">Tebal, rusak, atau
                                                terinfeksi</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('right_nails_scores')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-6 border-t pt-4">
                        <button @click="activeTab = 'identity'" type="button"
                            class="text-slate-700 bg-slate-200 hover:bg-slate-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Kembali</button>
                        <button @click="isTabSkinNailsCompleted ? activeTab = 'sensation' : null"
                            x-show="!isTabSkinNailsCompleted" wire:click="validateStep('skin_nails')"
                            wire:loading.attr="disabled" type="button"
                            class="inline-flex items-center justify-center text-white bg-primary hover:bg-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <span wire:loading.remove wire:target="validateStep('skin_nails')">Selesai</span>
                            <span wire:loading wire:target="validateStep('skin_nails')">Memeriksa...</span>
                        </button>
                        <button @click="activeTab = 'sensation'" x-show="isTabSkinNailsCompleted" type="button"
                            class="inline-flex items-center justify-center text-white bg-primary hover:bg-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <span>Lanjut</span>
                            <svg class="w-5 h-5 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Sensation Tab --}}
                <div wire:key="step-sensation" x-show="activeTab === 'sensation'" x-transition>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        {{-- CARD 1: MONOFILAMENT TEST --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm">
                            <h4 class="font-semibold text-slate-800 text-lg">3. Sensasi Kaki – Tes Monofilamen</h4>
                            <p class="text-sm text-slate-500 mb-4">Apakah kehilangan sensasi perlindungan terdeteksi?
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Foot Options --}}
                                <div>
                                    <h5 class="text-base text-slate-800 font-medium mb-3 text-center">Kaki Kiri</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="left_monofilament_score" value="4"
                                                id="left-mono-yes" name="left-mono-score" type="radio"
                                                class="w-4 h-4 text-primary rounded bg-gray-100 border-gray-300 focus:ring-primary focus:ring-2">
                                            <label for="left-mono-yes"
                                                class="ms-2 text-sm font-medium text-gray-900">Ya (Sensasi hilang di
                                                satu atau lebih lokasi)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_monofilament_score" value="0"
                                                id="left-mono-no" name="left-mono-score" type="radio"
                                                class="w-4 h-4 text-primary rounded bg-gray-100 border-gray-300 focus:ring-primary focus:ring-2">
                                            <label for="left-mono-no"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak (Sensasi terasa di
                                                semua lokasi)</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('left_monofilament_score')" class="mt-2" />
                                </div>
                                {{-- Right Foot Options --}}
                                <div>
                                    <h5 class="text-base text-slate-800 font-medium mb-3 text-center">Kaki Kanan</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="right_monofilament_score" value="4"
                                                id="right-mono-yes" name="right-mono-score" type="radio"
                                                class="w-4 h-4 text-primary rounded bg-gray-100 border-gray-300 focus:ring-primary focus:ring-2">
                                            <label for="right-mono-yes"
                                                class="ms-2 text-sm font-medium text-gray-900">Ya (Sensasi hilang di
                                                satu atau lebih lokasi)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_monofilament_score" value="0"
                                                id="right-mono-no" name="right-mono-score" type="radio"
                                                class="w-4 h-4 text-primary rounded bg-gray-100 border-gray-300 focus:ring-primary focus:ring-2">
                                            <label for="right-mono-no"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak (Sensasi terasa di
                                                semua lokasi)</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('right_monofilament_score')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        {{-- CARD 2: ASK 4 QUESTIONS --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm">
                            <h4 class="font-semibold text-slate-800 text-lg">4. Sensasi Kaki – Apakah pernah terasa:
                            </h4>
                            <p class="text-sm text-slate-500 mb-4">Pilih semua sensasi yang pernah dirasakan oleh
                                pasien.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Foot Options --}}
                                <div>
                                    <h5 class="text-base text-slate-800 font-medium mb-3 text-center">Kaki Kiri</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center"><input wire:model="left_sensation_numb"
                                                id="left-q-numb" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                for="left-q-numb" class="ms-2 text-sm font-medium text-gray-900">Mati
                                                rasa (baal)?</label></div>
                                        <div class="flex items-center"><input wire:model="left_sensation_tingle"
                                                id="left-q-tingle" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                for="left-q-tingle"
                                                class="ms-2 text-sm font-medium text-gray-900">Kesemutan?</label></div>
                                        <div class="flex items-center"><input wire:model="left_sensation_burn"
                                                id="left-q-burn" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                for="left-q-burn" class="ms-2 text-sm font-medium text-gray-900">Rasa
                                                panas/terbakar?</label></div>
                                        <div class="flex items-center"><input wire:model="left_sensation_crawl"
                                                id="left-q-crawl" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                for="left-q-crawl"
                                                class="ms-2 text-sm font-medium text-gray-900">Seperti ada serangga
                                                merayap?</label></div>
                                    </div>
                                </div>
                                {{-- Right Foot Options --}}
                                <div>
                                    <h5 class="text-base text-slate-800 font-medium mb-3 text-center">Kaki Kanan</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center"><input wire:model="right_sensation_numb"
                                                id="right-q-numb" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                for="right-q-numb" class="ms-2 text-sm font-medium text-gray-900">Mati
                                                rasa (baal)?</label></div>
                                        <div class="flex items-center"><input wire:model="right_sensation_tingle"
                                                id="right-q-tingle" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                for="right-q-tingle"
                                                class="ms-2 text-sm font-medium text-gray-900">Kesemutan?</label></div>
                                        <div class="flex items-center"><input wire:model="right_sensation_burn"
                                                id="right-q-burn" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                for="right-q-burn" class="ms-2 text-sm font-medium text-gray-900">Rasa
                                                panas/terbakar?</label></div>
                                        <div class="flex items-center"><input wire:model="right_sensation_crawl"
                                                id="right-q-crawl" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                for="right-q-crawl"
                                                class="ms-2 text-sm font-medium text-gray-900">Seperti ada serangga
                                                merayap?</label></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-6 border-t pt-4">
                        <button @click="activeTab = 'skin_nails'" type="button"
                            class="text-slate-700 bg-slate-200 hover:bg-slate-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Kembali</button>
                        <button @click="isTabSensationCompleted ? activeTab = 'pad' : null"
                            x-show="!isTabSensationCompleted" wire:click="validateStep('sensation')"
                            wire:loading.attr="disabled" type="button"
                            class="inline-flex items-center justify-center text-white bg-primary hover:bg-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <span wire:loading.remove wire:target="validateStep('sensation')">Selesai</span>
                            <span wire:loading wire:target="validateStep('sensation')">Memeriksa...</span>
                        </button>
                        <button @click="activeTab = 'pad'" x-show="isTabSensationCompleted" type="button"
                            class="inline-flex items-center justify-center text-white bg-primary hover:bg-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <span>Lanjut</span>
                            <svg class="w-5 h-5 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Peripheral Arterial Disease Tab --}}
                <div wire:key="step-pad" x-show="activeTab === 'pad'" x-transition>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- CARD 1: PAIN ASSESSMENT --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm">
                            <h4 class="font-semibold text-slate-800 text-lg">5. Nyeri</h4>
                            <p class="text-sm text-slate-500 mb-4">Apakah ada nyeri pada kaki atau tungkai saat
                                berjalan yang membatasi mobilitas?</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kiri</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="left_pain_score" value="1" id="left-pain-yes"
                                                name="left-pain-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-pain-yes"
                                                class="ms-2 text-sm font-medium text-gray-900">Ya</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_pain_score" value="0" id="left-pain-no"
                                                name="left-pain-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-pain-no"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('left_pain_score')" class="mt-2" />
                                </div>
                                {{-- Right Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kanan</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="right_pain_score" value="1" id="right-pain-yes"
                                                name="right-pain-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-pain-yes"
                                                class="ms-2 text-sm font-medium text-gray-900">Ya</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_pain_score" value="0" id="right-pain-no"
                                                name="right-pain-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-pain-no"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('right_pain_score')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        {{-- CARD 2: DEPENDENT RUBOR --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm">
                            <h4 class="font-semibold text-slate-800 text-lg">6. Rubor Dependen</h4>
                            <p class="text-sm text-slate-500 mb-4">Apakah terdapat rubor dependen?</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kiri</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="left_rubor_score" value="1" id="left-rubor-yes"
                                                name="left-rubor-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-rubor-yes"
                                                class="ms-2 text-sm font-medium text-gray-900">Ya</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_rubor_score" value="0" id="left-rubor-no"
                                                name="left-rubor-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-rubor-no"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('left_rubor_score')" class="mt-2" />
                                </div>
                                {{-- Right Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kanan</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="right_rubor_score" value="1" id="right-rubor-yes"
                                                name="right-rubor-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-rubor-yes"
                                                class="ms-2 text-sm font-medium text-gray-900">Ya</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_rubor_score" value="0" id="right-rubor-no"
                                                name="right-rubor-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-rubor-no"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('right_rubor_score')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        {{-- CARD 3: COLD FEET --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm">
                            <h4 class="font-semibold text-slate-800 text-lg">7. Kaki Dingin</h4>
                            <p class="text-sm text-slate-500 mb-4">Apakah kaki terasa dingin?</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kiri</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="left_temperature_score" value="1"
                                                id="left-cool-yes" name="left-temp-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-cool-yes"
                                                class="ms-2 text-sm font-medium text-gray-900">Ya</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_temperature_score" value="0"
                                                id="left-cool-no" name="left-temp-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-cool-no"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('left_temperature_score')" class="mt-2" />
                                </div>
                                {{-- Right Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kanan</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="right_temperature_score" value="1"
                                                id="right-cool-yes" name="right-temp-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-cool-yes"
                                                class="ms-2 text-sm font-medium text-gray-900">Ya</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_temperature_score" value="0"
                                                id="right-cool-no" name="right-temp-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-cool-no"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('right_temperature_score')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        {{-- CARD 4: PEDAL PULSE --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm">
                            <h4 class="font-semibold text-slate-800 text-lg">8. Nadi Pedal</h4>
                            <p class="text-sm text-slate-500 mb-4">Apakah denyut nadi pedal teraba?</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kiri</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="left_pedal_pulse_score" value="0"
                                                id="left-pulse-present" name="left-pulse-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-pulse-present"
                                                class="ms-2 text-sm font-medium text-gray-900">Teraba (Present)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_pedal_pulse_score" value="1"
                                                id="left-pulse-absent" name="left-pulse-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-pulse-absent"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak Teraba
                                                (Absent)</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('left_pedal_pulse_score')" class="mt-2" />
                                </div>
                                {{-- Right Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kanan</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="right_pedal_pulse_score" value="0"
                                                id="right-pulse-present" name="right-pulse-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-pulse-present"
                                                class="ms-2 text-sm font-medium text-gray-900">Teraba (Present)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_pedal_pulse_score" value="1"
                                                id="right-pulse-absent" name="right-pulse-score" type="radio"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-pulse-absent"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak Teraba
                                                (Absent)</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('right_pedal_pulse_score')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-6 border-t pt-4">
                        <button @click="activeTab = 'sensation'" type="button"
                            class="text-slate-700 bg-slate-200 hover:bg-slate-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Kembali</button>
                        <button @click="isTabPadCompleted ? activeTab = 'deformity' : null"
                            x-show="!isTabPadCompleted" wire:click="validateStep('pad')" wire:loading.attr="disabled"
                            type="button"
                            class="inline-flex items-center justify-center text-white bg-primary hover:bg-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <span wire:loading.remove wire:target="validateStep('pad')">Selesai</span>
                            <span wire:loading wire:target="validateStep('pad')">Memeriksa...</span>
                        </button>
                        <button @click="activeTab = 'deformity'" x-show="isTabPadCompleted" type="button"
                            class="inline-flex items-center justify-center text-white bg-primary hover:bg-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <span>Lanjut</span>
                            <svg class="w-5 h-5 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Deformity Tab --}}
                <div wire:key="step-deformity" x-show="activeTab === 'deformity'" x-transition>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- CARD 1: DEFORMITY ASSESSMENT --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm">
                            <h4 class="font-semibold text-slate-800 text-lg">9. Kelainan Bentuk (Deformity)</h4>
                            <p class="text-sm text-slate-500 mb-4">Pilih satu atau lebih kondisi kelainan bentuk yang
                                sesuai.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Left Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kiri</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="left_deformity_scores" value="0"
                                                id="left-deformity-0" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-deformity-0"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak ada kelainan
                                                bentuk</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_deformity_scores" value="1"
                                                id="left-deformity-1" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-deformity-1"
                                                class="ms-2 text-sm font-medium text-gray-900">Kelainan bentuk (misal:
                                                bunion, Charcot kronis, hammertoes)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_deformity_scores" value="2_amputation"
                                                id="left-deformity-2" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-deformity-2"
                                                class="ms-2 text-sm font-medium text-gray-900">Riwayat amputasi
                                                ekstremitas bawah</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="left_deformity_scores" value="2_charcot"
                                                id="left-deformity-3" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="left-deformity-3"
                                                class="ms-2 text-sm font-medium text-gray-900">Charcot Akut (+ hangat
                                                dan kemerahan)</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('left_deformity_scores')" class="mt-2" />
                                </div>
                                {{-- Right Foot Options --}}
                                <div>
                                    <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kanan</h5>
                                    <div class="flex flex-col space-y-3">
                                        <div class="flex items-center">
                                            <input wire:model="right_deformity_scores" value="0"
                                                id="right-deformity-0" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-deformity-0"
                                                class="ms-2 text-sm font-medium text-gray-900">Tidak ada kelainan
                                                bentuk</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_deformity_scores" value="1"
                                                id="right-deformity-1" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-deformity-1"
                                                class="ms-2 text-sm font-medium text-gray-900">Kelainan bentuk (misal:
                                                bunion, Charcot kronis, hammertoes)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_deformity_scores" value="2_amputation"
                                                id="right-deformity-2" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-deformity-2"
                                                class="ms-2 text-sm font-medium text-gray-900">Riwayat amputasi
                                                ekstremitas bawah</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="right_deformity_scores" value="2_charcot"
                                                id="right-deformity-3" type="checkbox"
                                                class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                                            <label for="right-deformity-3"
                                                class="ms-2 text-sm font-medium text-gray-900">Charcot Akut (+ hangat
                                                dan kemerahan)</label>
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('right_deformity_scores')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        {{-- CARD 2: RANGE OF MOTION & FOOTWEAR --}}
                        <div class="p-5 border border-slate-200 rounded-xl shadow-sm space-y-6">
                            <div>
                                <h4 class="font-semibold text-slate-800 text-lg">10. Rentang Gerak (Range of Motion)
                                </h4>
                                <p class="text-sm text-slate-500 mb-4">Periksa rentang gerak pada hallux (ibu jari
                                    kaki).</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Left Foot Options --}}
                                    <div>
                                        <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kiri
                                        </h5>
                                        <div class="flex flex-col space-y-3">
                                            <div class="flex items-center"><input wire:model="left_rom_scores"
                                                    value="0" id="left-rom-0" type="checkbox"
                                                    class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                    for="left-rom-0"
                                                    class="ms-2 text-sm font-medium text-gray-900">Rentang gerak
                                                    penuh</label></div>
                                            <div class="flex items-center"><input wire:model="left_rom_scores"
                                                    value="1" id="left-rom-1" type="checkbox"
                                                    class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                    for="left-rom-1"
                                                    class="ms-2 text-sm font-medium text-gray-900">Rentang gerak
                                                    terbatas</label></div>
                                            <div class="flex items-center"><input wire:model="left_rom_scores"
                                                    value="2" id="left-rom-2" type="checkbox"
                                                    class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                    for="left-rom-2"
                                                    class="ms-2 text-sm font-medium text-gray-900">Hallux kaku
                                                    (Rigidus)</label></div>
                                        </div>
                                        <x-input-error :messages="$errors->get('left_rom_scores')" class="mt-2" />
                                    </div>
                                    {{-- Right Foot Options --}}
                                    <div>
                                        <h5 class="text-base font-medium mb-3 text-center text-slate-800">Kaki Kanan
                                        </h5>
                                        <div class="flex flex-col space-y-3">
                                            <div class="flex items-center"><input wire:model="right_rom_scores"
                                                    value="0" id="right-rom-0" type="checkbox"
                                                    class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                    for="right-rom-0"
                                                    class="ms-2 text-sm font-medium text-gray-900">Rentang gerak
                                                    penuh</label></div>
                                            <div class="flex items-center"><input wire:model="right_rom_scores"
                                                    value="1" id="right-rom-1" type="checkbox"
                                                    class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                    for="right-rom-1"
                                                    class="ms-2 text-sm font-medium text-gray-900">Rentang gerak
                                                    terbatas</label></div>
                                            <div class="flex items-center"><input wire:model="right_rom_scores"
                                                    value="2" id="right-rom-2" type="checkbox"
                                                    class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                                    for="right-rom-2"
                                                    class="ms-2 text-sm font-medium text-gray-900">Hallux kaku
                                                    (Rigidus)</label></div>
                                        </div>
                                        <x-input-error :messages="$errors->get('right_rom_scores')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6">

                            <div>
                                <h4 class="font-semibold text-slate-800 text-lg">11. Alas Kaki (Footwear)</h4>
                                <p class="text-sm text-slate-500 mb-4">Pilih kondisi alas kaki pasien (hanya satu
                                    pilihan).</p>
                                <div class="flex flex-col space-y-3">
                                    <div class="flex items-center"><input wire:model="footwear_score" value="0"
                                            id="footwear-0" name="footwear-score" type="radio"
                                            class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                            for="footwear-0"
                                            class="ms-2 text-sm font-medium text-gray-900">Sesuai</label></div>
                                    <div class="flex items-center"><input wire:model="footwear_score" value="1"
                                            id="footwear-1" name="footwear-score" type="radio"
                                            class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                            for="footwear-1" class="ms-2 text-sm font-medium text-gray-900">Tidak
                                            sesuai</label></div>
                                    <div class="flex items-center"><input wire:model="footwear_score" value="2"
                                            id="footwear-2" name="footwear-score" type="radio"
                                            class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2"><label
                                            for="footwear-2"
                                            class="ms-2 text-sm font-medium text-gray-900">Menyebabkan trauma</label>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('footwear_score')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-6 border-t pt-4">
                        <button @click="activeTab = 'pad'" type="button"
                            class="text-slate-700 bg-slate-200 hover:bg-slate-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Kembali</button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="text-white bg-primary hover:bg-primary font-medium rounded-lg text-sm px-5 py-2.5 text-center disabled:opacity-50">
                            <span wire:loading.remove>Simpan & Lihat Hasil</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to initialize datepicker
    function initializeDatepicker() {
        // Wait for Flowbite to be available
        if (typeof Datepicker === 'undefined') {
            setTimeout(initializeDatepicker, 500);
            return;
        }

        const datepickerEl = document.getElementById('patient_dob');
        if (!datepickerEl) {
            return;
        }

        if (datepickerEl.hasAttribute('data-datepicker-initialized')) {
            return;
        }

        try {
            datepickerEl.setAttribute('data-datepicker-initialized', 'true');

            const datepicker = new Datepicker(datepickerEl, {
                format: 'dd-mm-yyyy',
                autohide: true,
                maxDate: new Date(),
                minDate: new Date('1900-01-01'),
                todayBtn: true,
                clearBtn: true,
                todayBtnText: 'Hari ini',
                clearBtnText: 'Hapus',
                title: 'Pilih Tanggal Lahir',
                language: 'id',
                showOnFocus: true,
                showOnClick: true,
                orientation: 'bottom auto',
                beforeShowYear: function(year) {
                    const currentYear = new Date().getFullYear();
                    return year >= 1900 && year <= currentYear;
                },
                disableTouchKeyboard: false,
                startView: 2,
                minViewMode: 0
            });

            // Listen for date selection to trigger Livewire update
            datepickerEl.addEventListener('changeDate', function(e) {
                const selectedDate = e.detail.date;
                if (selectedDate) {
                    const year = selectedDate.getFullYear();
                    const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                    const day = String(selectedDate.getDate()).padStart(2, '0');

                    const displayFormat = `${day}-${month}-${year}`;
                    const livewireFormat = `${year}-${month}-${day}`;

                    // Find Livewire component
                    const wireElement = datepickerEl.closest('[wire\\:id]');
                    if (wireElement && window.Livewire) {
                        const livewireComponent = window.Livewire.find(wireElement.getAttribute('wire:id'));
                        if (livewireComponent) {
                            livewireComponent.set('patient_dob', livewireFormat);
                            livewireComponent.set('patient_dob_display', displayFormat);
                        }
                    }
                }
            });

            // Add custom styling for better year/month navigation
            datepickerEl.addEventListener('show', function() {
                setTimeout(() => {
                    const datepicker = document.querySelector('.datepicker');
                    if (datepicker) {
                        datepicker.classList.add('custom-datepicker');

                        const switchBtn = datepicker.querySelector('.datepicker-switch');
                        if (switchBtn) {
                            switchBtn.style.cursor = 'pointer';
                            switchBtn.title = 'Klik untuk memilih tahun/bulan';
                        }
                    }
                }, 10);
            });

        } catch (error) {
            datepickerEl.removeAttribute('data-datepicker-initialized');
        }
    }

    // Function to register SweetAlert listener
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
            window.Livewire.off('screening-saved');
        } catch (e) {
            // No previous listener to remove
        }

        // Register new listener
        window.Livewire.on('screening-saved', (event) => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data skrining berhasil disimpan.',
                confirmButtonText: 'Lihat Hasil',
                confirmButtonColor: '#058a84',
                showCancelButton: false,
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed && event[0] && event[0].screeningId) {
                    window.location.href = `/screening/result/${event[0].screeningId}`;
                }
            }).catch((error) => {
                // Fallback navigation
                if (event[0] && event[0].screeningId) {
                    window.location.href = `/screening/result/${event[0].screeningId}`;
                }
            });
        });
    }

    // Function to initialize all components
    function initializeComponents() {
        initializeDatepicker();
        registerSweetAlertListener();
    }

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeComponents();
    });

    // Initialize on Livewire init
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
