<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileController extends Controller
{
    //
    public function uploadFile(Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $file->storeAs('public', $fileName); 
    
            $filePath = '/' . 'storage/' . $fileName;
                        
            return response()->json([
                'message' => 'Thành công',
                'data' => [
                    "url" => $filePath 
                ]
            ]);          
        } else {
            return response()->json([
                'message' => 'Vui lòng thêm file',
                'data' => null
            ]);        
        }
    }
}
