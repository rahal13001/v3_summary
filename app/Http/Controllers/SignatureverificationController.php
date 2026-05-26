<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class SignatureverificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Report $report)
    {
        return view('verif_ttd.signatureverification', compact('report'));
    }
}
