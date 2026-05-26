<?php

namespace App\Http\Controllers\Summary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function viewlainnya($lainnya_upload){
        
        $lainnya = $lainnya_upload;  
        return view('lihat.lihatlainnya', compact('lainnya'));
    }

     public function viewst($st_upload){
       
        $st = $st_upload;  
        return view('lihat.lihatst', compact('st'));
    }
}
