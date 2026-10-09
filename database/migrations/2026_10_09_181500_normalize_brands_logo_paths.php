<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert legacy brand logo URLs into relative paths.
     *
     * Older versions stored the result of Storage::url() (e.g.
     * "http://localhost/storage/brand_logo/abc.jpg" or
     * "/storage/brand_logo/abc.jpg"). Models now store the relative path and
     * build the URL through the logo_url accessor. Files are not moved: the
     * accessor resolves any relative path, so the legacy "brand_logo/" folder
     * keeps working while new uploads go to "brands/".
     */
    public function up(): void
    {
        DB::table('brands')->orderBy('id')->chunkById(1000, function ($brands): void {
            foreach ($brands as $brand) {
                $logo = $brand->logo;

                if (! is_string($logo) || ! $this->isUrl($logo)) {
                    continue;
                }

                DB::table('brands')
                    ->where('id', $brand->id)
                    ->update(['logo' => $this->toRelativePath($logo)]);
            }
        });
    }

    /**
     * Restore the legacy "/storage/<path>" representation.
     */
    public function down(): void
    {
        DB::table('brands')->orderBy('id')->chunkById(1000, function ($brands): void {
            foreach ($brands as $brand) {
                $logo = $brand->logo;

                if (! is_string($logo) || $logo === '' || $this->isUrl($logo)) {
                    continue;
                }

                DB::table('brands')
                    ->where('id', $brand->id)
                    ->update(['logo' => '/storage/' . ltrim($logo, '/')]);
            }
        });
    }

    private function isUrl(string $value): bool
    {
        return str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://')
            || str_starts_with($value, '/storage/');
    }

    private function toRelativePath(string $value): string
    {
        $path = parse_url($value, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            $path = $value;
        }

        $marker = '/storage/';
        $position = strpos($path, $marker);

        if ($position !== false) {
            $path = substr($path, $position + strlen($marker));
        }

        return ltrim($path, '/');
    }
};
