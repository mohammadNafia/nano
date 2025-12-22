<?php

namespace App\Http\Controllers;

use App\Services\OcrService;
use App\Models\RateLimit;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    protected OcrService $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Show the demo page.
     */
    public function index(Request $request)
    {
        $ipAddress = $request->ip();
        $rateLimit = $this->ocrService->checkRateLimit($ipAddress);
        $serviceStatus = $this->ocrService->getServiceStatus();
        $history = $this->ocrService->getUploadHistory(null, $ipAddress, 5);

        return view('demo', [
            'rateLimit' => $rateLimit,
            'serviceStatus' => $serviceStatus,
            'history' => $history,
        ]);
    }
}

