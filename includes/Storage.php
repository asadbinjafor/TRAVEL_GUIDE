<?php
declare(strict_types=1);

class Storage
{
    public static function isConfigured(): bool
    {
        return env('SUPABASE_URL') !== null
            && env('SUPABASE_STORAGE_BUCKET') !== null
            && env('SUPABASE_SERVICE_ROLE_KEY') !== null;
    }

    public static function upload(string $localPath, string $mime, string $folder, string $filename): ?string
    {
        if (!self::isConfigured() || !extension_loaded('curl')) {
            return null;
        }
        $base = rtrim((string) env('SUPABASE_URL'), '/');
        $bucket = (string) env('SUPABASE_STORAGE_BUCKET');
        $key = (string) env('SUPABASE_SERVICE_ROLE_KEY');
        $objectPath = trim($folder, '/') . '/' . $filename;
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $objectPath)));
        $contents = file_get_contents($localPath);
        if ($contents === false) {
            return null;
        }

        $curl = curl_init($base . '/storage/v1/object/' . rawurlencode($bucket) . '/' . $encodedPath);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $contents,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $key,
                'apikey: ' . $key,
                'Content-Type: ' . $mime,
                'x-upsert: false',
            ],
        ]);
        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($response === false || $status < 200 || $status >= 300) {
            error_log('Supabase Storage upload failed (HTTP ' . $status . '): ' . ($error ?: 'remote error'));
            return null;
        }
        return $base . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . $encodedPath;
    }
}
