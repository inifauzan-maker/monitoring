<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarLinkPublikStorage
{
    public static function disk(): string
    {
        return (string) config('filesystems.avatar_link.disk', 'avatar_public');
    }

    public static function legacyDisk(): string
    {
        return (string) config('filesystems.avatar_link.legacy_disk', 'public');
    }

    public static function directory(): string
    {
        return trim((string) config('filesystems.avatar_link.directory', 'avatar-link'), '/\\');
    }

    public static function simpan(UploadedFile $file): string
    {
        return $file->store(self::directory(), self::disk());
    }

    public static function url(?string $path): ?string
    {
        $path = self::normalisasiPath($path);

        if (! $path) {
            return null;
        }

        if (Storage::disk(self::disk())->exists($path)) {
            return Storage::disk(self::disk())->url($path);
        }

        if (self::legacyDisk() !== self::disk() && Storage::disk(self::legacyDisk())->exists($path)) {
            return Storage::disk(self::legacyDisk())->url($path);
        }

        return null;
    }

    public static function exists(?string $path): bool
    {
        $path = self::normalisasiPath($path);

        if (! $path) {
            return false;
        }

        return Storage::disk(self::disk())->exists($path)
            || (self::legacyDisk() !== self::disk() && Storage::disk(self::legacyDisk())->exists($path));
    }

    public static function hapus(?string $path): void
    {
        $path = self::normalisasiPath($path);

        if (! $path) {
            return;
        }

        foreach (array_unique([self::disk(), self::legacyDisk()]) as $disk) {
            Storage::disk($disk)->delete($path);
        }
    }

    public static function migrasikanJikaPerlu(?string $path, bool $hapusLegacy = false): ?string
    {
        $path = self::normalisasiPath($path);

        if (! $path) {
            return null;
        }

        $diskTarget = self::disk();
        $diskLegacy = self::legacyDisk();

        if (Storage::disk($diskTarget)->exists($path)) {
            if ($hapusLegacy && $diskLegacy !== $diskTarget && Storage::disk($diskLegacy)->exists($path)) {
                Storage::disk($diskLegacy)->delete($path);
            }

            return $path;
        }

        if ($diskLegacy === $diskTarget || ! Storage::disk($diskLegacy)->exists($path)) {
            return null;
        }

        Storage::disk($diskTarget)->put($path, Storage::disk($diskLegacy)->get($path));

        if ($hapusLegacy) {
            Storage::disk($diskLegacy)->delete($path);
        }

        return $path;
    }

    public static function normalisasiPath(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        $direktori = preg_quote(self::directory(), '/');

        if (preg_match('/'.$direktori.'\/.+$/', $path, $cocok)) {
            return $cocok[0];
        }

        return $path;
    }
}
