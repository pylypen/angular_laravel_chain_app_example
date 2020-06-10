<?php

namespace App\Http\Controllers\Certificates;

use App\Http\Controllers\Controller;
use App\Models\Certificates;
use App\Http\Traits\PDFGenerator;

class CertificatesController extends Controller
{
    use PDFGenerator;

    /**
     * View Certificate
     *
     * @param string $subdoamin
     * @param integer $cert_name
     *
     * @return \Illuminate\Http\Response
     */
    public function view($subdoamin, $cert_name = false)
    {
        $protocol = env('CMS_DOMAIN_SECURE', false) ? 'https://' : 'http://';

        if ($cert_name) {
            $output = preg_replace('/[^0-9-]/', '', $cert_name);

            if (!empty(trim($output))) {
                $certificate = Certificates::where(['cert_name' => $output])->first();

                if ($certificate) {
                    return $this->generatePdfCertificate($certificate);
                }
            }
        }

        return redirect()->away($protocol . env('DOMAIN_NAME'));
    }


    public function test($subdoamin, $cert_name = false)
    {
        $protocol = env('CMS_DOMAIN_SECURE', false) ? 'https://' : 'http://';
        if ($cert_name) {

            $output = preg_replace('/[^0-9-]/', '', $cert_name);
            if (!empty(trim($output))) {
                $certificate = Certificates::where(['cert_name' => $output])->first();

                if ($certificate) {
                    return view('certificates.default', ['certificate' => $certificate]);
                }

            }
        }

        return redirect()->away($protocol . env('DOMAIN_NAME'));
    }

}