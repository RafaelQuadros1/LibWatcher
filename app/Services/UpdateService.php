<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class UpdateService
{
    protected $apis = [
        'npm' => 'https://registry.npmjs.org/',
        'packagist' => 'https://packagist.org/packages/',
        'github' => 'https://api.github.com/repos/',
        'endoflife' => 'https://endoflife.date/api/',
    ];

    public function checkForUpdates($packages)
    {
        $updates = [];

        foreach ($packages as $package) {
            $cacheKey = "update_check_{$package['name']}";

            $updates[$package['name']] = Cache::remember($cacheKey, 3600, function () use ($package) {
                return $this->getPackageUpdate($package);
            });
        }

        return $updates;
    }

    public function checkForLanguageUpdates($languages)
    {
        $updates = [];

        foreach ($languages as $language) {
            $cacheKey = "language_update_{$language}";

            $updates[$language] = Cache::remember($cacheKey, 3600, function () use ($language) {
                return $this->getLanguageUpdate($language);
            });
        }

        return $updates;
    }

    private function getPackageUpdate($package)
    {
        $type = $package['type'] ?? 'npm';
        $name = $package['name'];

        switch ($type) {
            case 'npm':
                return $this->getNpmUpdate($name);
            case 'packagist':
                return $this->getPackagistUpdate($name);
            case 'github':
                return $this->getGitHubUpdate($name);
            default:
                return ['error' => 'Tipo de pacote não suportado'];
        }
    }

    private function getNpmUpdate($package)
    {
        try {
            $response = Http::get($this->apis['npm'] . $package);
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'current_version' => $data['dist-tags']['latest'] ?? 'N/A',
                    'versions' => array_keys($data['versions'] ?? []),
                    'last_updated' => $data['time'][$data['dist-tags']['latest']] ?? 'N/A',
                    'source' => 'npmjs.org',
                    'status' => 'success'
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Erro npm: {$e->getMessage()}");
        }

        return ['status' => 'error', 'message' => 'Erro ao buscar atualizações npm'];
    }

    private function getPackagistUpdate($package)
    {
        try {
            $url = $this->apis['packagist'] . "{$package}.json";
            $response = Http::get($url);
            if ($response->successful()) {
                $data = $response->json();
                $versions = array_keys($data['package']['versions'] ?? []);
                $stable = array_filter($versions, fn($v) => !preg_match('/(dev|alpha|beta|rc)/i', $v));
                $latest = reset($stable) ?: ($versions[0] ?? 'N/A');

                return [
                    'current_version' => $latest,
                    'last_updated' => $data['package']['versions'][$latest]['time'] ?? 'N/A',
                    'source' => 'packagist.org',
                    'status' => 'success'
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Erro packagist: {$e->getMessage()}");
        }

        return ['status' => 'error', 'message' => 'Erro ao buscar atualizações packagist'];
    }

    private function getGitHubUpdate($package)
    {
        try {
            $url = $this->apis['github'] . "{$package}/releases/latest";
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Laravel-Update-Checker'
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'current_version' => $data['tag_name'] ?? 'N/A',
                    'release_name' => $data['name'] ?? 'N/A',
                    'release_date' => $data['published_at'] ?? 'N/A',
                    'url' => $data['html_url'] ?? null,
                    'source' => 'github.com',
                    'status' => 'success'
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Erro github: {$e->getMessage()}");
        }

        return ['status' => 'error', 'message' => 'Erro ao buscar atualizações GitHub'];
    }

    private function getLanguageUpdate($language)
    {
        try {
            $url = $this->apis['endoflife'] . "{$language}.json";
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                $latest = $data[0] ?? null;

                if ($latest) {
                    return [
                        'cycle' => $latest['cycle'] ?? 'N/A',
                        'release_date' => $latest['releaseDate'] ?? 'N/A',
                        'eol' => $latest['eol'] ?? 'N/A',
                        'latest_version' => $latest['latest'] ?? 'N/A',
                        'latest_release_date' => $latest['latestReleaseDate'] ?? 'N/A',
                        'support' => $latest['support'] ?? 'N/A',
                        'extended_support' => $latest['extendedSupport'] ?? false,
                        'source' => 'endoflife.date',
                        'status' => 'success'
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error("Erro endoflife: {$e->getMessage()}");
        }

        return ['status' => 'error', 'message' => 'Erro ao buscar linguagem no endoflife'];
    }
}
