<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadFileRequest;

class UploadController extends Controller
{
    public function store(UploadFileRequest $request)
    {
        $validated = $request->validated();

        // Поле file уже проверено формой UploadFileRequest
        $file = $request->file('file');
        $name = $file->getClientOriginalName();
        $file->move(public_path('uploads'), $name);
        return back()->with('status', 'Файл загружен ' . $name);
    }
}
