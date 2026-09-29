<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class CompanyBrandService
{
    public function profile(?int $companyId): array
    {
        $company = $companyId ? Company::find($companyId) : null;
        $setting = $companyId ? Setting::where('company_id', $companyId)->first() : null;
        [$fallbackAr, $fallbackEn] = $this->splitName($setting?->company_name ?: $company?->name ?: 'Al Waleed');

        return [
            'name_ar' => $setting?->company_name_ar ?: $fallbackAr,
            'name_en' => $setting?->company_name_en ?: $fallbackEn,
            'phone' => $setting?->phone ?: $company?->phone,
            'address' => $setting?->address ?: $company?->address,
            'logo' => $this->logoDataUri($companyId),
        ];
    }

    public function splitName(?string $name): array
    {
        $parts = preg_split('/\s*\|\s*/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $arabic = null;
        $english = null;
        foreach ($parts as $part) {
            if (preg_match('/[\x{0600}-\x{06FF}]/u', $part)) {
                $arabic ??= $part;
            } else {
                $english ??= $part;
            }
        }

        return [$arabic, $english ?: ($arabic ? null : ($parts[0] ?? 'Al Waleed'))];
    }

    public function logoDataUri(?int $companyId): string
    {
        $relativePath = $companyId
            ? (Setting::where('company_id', $companyId)->value('company_logo') ?: Company::whereKey($companyId)->value('logo'))
            : null;
        $disk = Storage::disk(config('filesystems.company_logo_disk'));

        if ($relativePath && $disk->exists($relativePath)) {
            return $this->dataUri($disk->mimeType($relativePath) ?: 'image/png', $disk->get($relativePath));
        }

        $fallback = public_path('logo.png');
        return is_file($fallback) && is_readable($fallback)
            ? $this->dataUri(mime_content_type($fallback) ?: 'image/png', file_get_contents($fallback))
            : '';
    }

    private function dataUri(string $mimeType, string $contents): string
    {
        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }
}
