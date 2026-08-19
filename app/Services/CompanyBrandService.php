<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class CompanyBrandService
{
    public function logoDataUri(?int $companyId): string
    {
        $relativePath = null;

        if ($companyId) {
            $relativePath = Setting::where('company_id', $companyId)->value('company_logo')
                ?: Company::whereKey($companyId)->value('logo');
        }

        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            return $this->dataUri(
                Storage::disk('public')->mimeType($relativePath) ?: 'image/png',
                Storage::disk('public')->get($relativePath),
            );
        }

        $fallback = public_path('logo.png');

        if (is_file($fallback) && is_readable($fallback)) {
            return $this->dataUri(mime_content_type($fallback) ?: 'image/png', file_get_contents($fallback));
        }

        return asset('logo.png');
    }

    private function dataUri(string $mimeType, string $contents): string
    {
        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }
}
