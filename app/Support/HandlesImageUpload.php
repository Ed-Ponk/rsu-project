<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Shared image handling for controllers.
 *
 * Stores images with a hashed file name on the public disk and returns the
 * relative path (never the URL), so models keep a portable reference and the
 * URL is derived through an accessor.
 */
trait HandlesImageUpload
{
    /**
     * Store an uploaded image with a hashed name and return its relative path.
     */
    protected function storeImage(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, 'public');
    }

    /**
     * Delete an image from the public disk, ignoring a missing file.
     */
    protected function deleteImage(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
