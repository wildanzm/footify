<?php

namespace App\Services;

class ScreeningService
{
    /**
     * Process raw form data, calculate all scores, status, and risk levels,
     * then return a clean array ready for database storage.
     *
     * @param array $data Raw screening data from form
     * @return array Processed data ready for database insertion
     */
    public function processScreening(array $data): array
    {
        $bloodSugarStatus = $this->getBloodSugarStatus($data['blood_sugar_type'], $data['blood_sugar_value']);
        $totalScore = $this->calculateTotalInlowsScore($data);
        $riskProfile = $this->getRiskProfile($data);

        // Prepare final clean data for database storage
        return [
            'blood_sugar_type' => $data['blood_sugar_type'],
            'blood_sugar_value' => $data['blood_sugar_value'],
            'blood_sugar_status' => $bloodSugarStatus,
            'left_skin_score' => $this->getMaxScoreFromArray($data['left_skin_scores']),
            'right_skin_score' => $this->getMaxScoreFromArray($data['right_skin_scores']),
            'left_nails_score' => $this->getMaxScoreFromArray($data['left_nails_scores']),
            'right_nails_score' => $this->getMaxScoreFromArray($data['right_nails_scores']),
            'left_sensation_score' => $data['left_sensation_score'],
            'right_sensation_score' => $data['right_sensation_score'],
            'left_pain_score' => $data['left_pain_score'],
            'right_pain_score' => $data['right_pain_score'],
            'left_rubor_score' => $data['left_rubor_score'],
            'right_rubor_score' => $data['right_rubor_score'],
            'left_temperature_score' => $data['left_temperature_score'],
            'right_temperature_score' => $data['right_temperature_score'],
            'left_pedal_pulse_score' => $data['left_pedal_pulse_score'],
            'right_pedal_pulse_score' => $data['right_pedal_pulse_score'],
            'left_deformity_score' => $this->getMaxScoreFromArray($data['left_deformity_scores']),
            'right_deformity_score' => $this->getMaxScoreFromArray($data['right_deformity_scores']),
            'left_rom_score' => $this->getMaxScoreFromArray($data['left_rom_scores']),
            'right_rom_score' => $this->getMaxScoreFromArray($data['right_rom_scores']),
            'footwear_score' => $data['footwear_score'],
            'total_score' => $totalScore,
            'risk_classification' => $riskProfile['classification'],
            'recommendation' => $data['recommendation'] ?? [],
            'notes' => $data['notes'] ?? null,
        ];
    }

    /**
     * Calculate total Inlow's score by taking the highest value from each assessment item.
     *
     * @param array $data Screening assessment data
     * @return int Total calculated score
     */
    public function calculateTotalInlowsScore(array $data): int
    {
        $maxSkinScore = max($this->getMaxScoreFromArray($data['left_skin_scores']), $this->getMaxScoreFromArray($data['right_skin_scores']));
        $maxNailsScore = max($this->getMaxScoreFromArray($data['left_nails_scores']), $this->getMaxScoreFromArray($data['right_nails_scores']));
        $maxSensationScore = max($data['left_sensation_score'], $data['right_sensation_score']);
        $maxPadScore = max($data['left_pain_score'], $data['right_pain_score']) + max($data['left_rubor_score'], $data['right_rubor_score']) + max($data['left_temperature_score'], $data['right_temperature_score']) + max($data['left_pedal_pulse_score'], $data['right_pedal_pulse_score']);
        $maxDeformityScore = max($this->getMaxScoreFromArray($data['left_deformity_scores']), $this->getMaxScoreFromArray($data['right_deformity_scores']));
        $maxRomScore = max($this->getMaxScoreFromArray($data['left_rom_scores']), $this->getMaxScoreFromArray($data['right_rom_scores']));

        return $maxSkinScore + $maxNailsScore + $maxSensationScore + $maxPadScore + $maxDeformityScore + $maxRomScore + $data['footwear_score'];
    }

    /**
     * Determine risk classification based on total score and emergency conditions.
     *
     * @param array $data Screening assessment data
     * @return array Risk profile with classification
     */
    public function getRiskProfile(array $data): array
    {
        // Determine basic conditions from assessment data
        $hasLops = max($data['left_sensation_score'], $data['right_sensation_score']) > 0;
        $hasPad = (max($data['left_pain_score'], $data['right_pain_score']) > 0 ||
            max($data['left_rubor_score'], $data['right_rubor_score']) > 0 ||
            max($data['left_temperature_score'], $data['right_temperature_score']) > 0 ||
            max($data['left_pedal_pulse_score'], $data['right_pedal_pulse_score']) > 0);
        $hasDeformity = max($this->getMaxScoreFromArray($data['left_deformity_scores']), $this->getMaxScoreFromArray($data['right_deformity_scores'])) > 0;
        $hasHistoryOfUlcer = in_array('3', $data['left_skin_scores'] ?? []) || in_array('3', $data['right_skin_scores'] ?? []);
        $hasHistoryOfAmputation = in_array('2_amputation', $data['left_deformity_scores'] ?? []) || in_array('2_amputation', $data['right_deformity_scores'] ?? []);
        $hasActiveCharcot = in_array('2_charcot', $data['left_deformity_scores'] ?? []) || in_array('2_charcot', $data['right_deformity_scores'] ?? []);

        // Evaluate risk from highest to lowest priority
        if ($hasHistoryOfUlcer || $hasActiveCharcot) {
            return ['classification' => 'Darurat'];
        }

        if (($hasLops || $hasPad) && ($hasHistoryOfUlcer || $hasHistoryOfAmputation)) {
            return ['classification' => 'Tinggi'];
        }

        if (($hasLops && $hasPad) || ($hasLops && $hasDeformity) || ($hasPad && $hasDeformity)) {
            return ['classification' => 'Sedang'];
        }

        if ($hasLops || $hasPad) {
            return ['classification' => 'Rendah'];
        }

        return ['classification' => 'Sangat Rendah'];
    }

    /**
     * Determine blood sugar status based on test type and value.
     *
     * @param string $type Type of blood sugar test (gds, gdp, hba1c)
     * @param float $value Blood sugar test result value
     * @return string Blood sugar status classification
     */
    private function getBloodSugarStatus(string $type, float $value): string
    {
        // Blood sugar classification logic based on medical standards
        if ($type === 'gds') {
            if ($value >= 200) return 'Diabetes';
            if ($value >= 140) return 'Prediabetes';
            return 'Normal';
        }
        if ($type === 'gdp') {
            if ($value >= 126) return 'Diabetes';
            if ($value >= 100) return 'Prediabetes';
            return 'Normal';
        }
        if ($type === 'hba1c') {
            if ($value >= 6.5) return 'Diabetes';
            if ($value >= 5.7) return 'Prediabetes';
            return 'Normal';
        }
        return 'Tidak Diketahui';
    }

    /**
     * Helper function to convert checkbox array values to highest numeric score.
     * 
     * Example: ['1_dry', '2_callus'] will return 2.
     *
     * @param array $scores Array of checkbox values with numeric prefixes
     * @return int Maximum numeric score from the array
     */
    private function getMaxScoreFromArray(array $scores): int
    {
        if (empty($scores)) {
            return 0;
        }
        $numericScores = array_map('intval', $scores);
        return max($numericScores);
    }
}
