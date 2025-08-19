<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Screening;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    /**
     * Generate and download PDF report for screening
     *
     * @param int $screeningId
     * @return \Illuminate\Http\Response
     */
    public function downloadScreeningReport($screeningId)
    {
        // Find screening with patient relationship
        $screening = Screening::with('patient')->findOrFail($screeningId);
        
        // Load PDF view with data
        $pdf = Pdf::loadView('pdf.screening-report', [
            'screening' => $screening,
            'patient' => $screening->patient,
        ]);
        
        // Set paper configuration
        $pdf->setPaper('A4', 'portrait');
        
        // Generate safe filename
        $patientName = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], ' ', $screening->patient->name);
        $dateFormatted = $screening->created_at->locale('id')->translatedFormat('d F Y');
        $filename = 'Laporan Skrining - ' . $patientName . ' - ' . $dateFormatted . '.pdf';
        
        // Return PDF download response
        return $pdf->download($filename);
    }
}
