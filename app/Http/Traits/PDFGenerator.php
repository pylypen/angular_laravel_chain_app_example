<?php

namespace App\Http\Traits;

use App\Models\Certificates;
use PDF;

trait PDFGenerator
{
    private function generatePdfCertificate(Certificates $certificate)
    {
        $pdfname = uniqid() . '.pdf';
        $pdf = PDF::loadView('certificates.default', ['certificate' => $certificate]);
        return $pdf->stream($pdfname);
    }
}