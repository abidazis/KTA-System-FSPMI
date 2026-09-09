<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    /**
     * Serve a file from the local storage disk.
     * Usage: /media/{path}
     * Example: /media/members/photos/xxx.jpg
     */
    public function show(Request $request, string $path): BinaryFileResponse
    {
        // Security: prevent path traversal
        $path = urldecode($path);
        $path = str_replace(['..', chr(0)], '', $path);

        // Check in local disk (root: storage/app/private)
        $disk = Storage::disk('local');
        $localPath = $disk->path($path);

        if (file_exists($localPath) && is_readable($localPath)) {
            return response()->file($localPath);
        }

        // Also check in public disk (root: storage/app/public) as fallback
        $publicPath = storage_path('app/public/' . $path);
        if (file_exists($publicPath) && is_readable($publicPath)) {
            return response()->file($publicPath);
        }

        // File not found
        abort(404, 'File not found');
    }
}
