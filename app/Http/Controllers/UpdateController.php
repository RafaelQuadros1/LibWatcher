<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class UpdateController extends Controller
{
    public function getLanguageUpdates()
    {
        return Cache::remember('language_updates', 3600, function () {
            $languages = [
                'php' => $this->getPhpUpdates(),
                'javascript' => $this->getJavaScriptUpdates(),
                'java' => $this->getJavaUpdates(),
            ];

            return response()->json([
                'success' => true,
                'data' => $languages,
                'cached_at' => now()
            ]);
        });
    }

    public function getLibraryUpdates(Request $request)
    {
        $libraries = $request->get('libraries', []);

        if (empty($libraries)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma biblioteca especificada'
            ], 400);
        }

        $updates = [];
        foreach ($libraries as $library) {
            $updates[$library] = $this->getLibraryInfo($library);
        }

        return response()->json([
            'success' => true,
            'data' => $updates
        ]);
    }

    public function getPackageUpdates($package)
    {
        $cacheKey = "package_{$package}";

        return Cache::remember($cacheKey, 1800, function () use ($package) {
            // Buscar em diferentes registros
            $npmData = $this->getNpmPackageInfo($package);
            $pypiData = $this->getPypiPackageInfo($package);
            $packagistData = $this->getPackagistInfo($package);

            return response()->json([
                'success' => true,
                'package' => $package,
                'data' => [
                    'npm' => $npmData,
                    'packagist' => $packagistData
                ]
            ]);
        });
    }

    public function getGitHubUpdates($owner, $repo)
    {
        $cacheKey = "github_{$owner}_{$repo}";

        return Cache::remember($cacheKey, 1800, function () use ($owner, $repo) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Laravel-Update-Checker/1.0',
                    'Accept' => 'application/vnd.github.v3+json'
                ])->timeout(10)->get("https://api.github.com/repos/{$owner}/{$repo}/releases/latest");

                if ($response->successful()) {
                    $release = $response->json();

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'name' => $release['name'],
                            'tag_name' => $release['tag_name'],
                            'published_at' => $release['published_at'],
                            'body' => $release['body'],
                            'html_url' => $release['html_url'],
                            'download_url' => $release['zipball_url']
                        ]
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Repositório não encontrado ou sem releases',
                        'status_code' => $response->status()
                    ], 404);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao buscar informações do GitHub: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    private function getPhpUpdates()
    {
        try {
            $response = Http::timeout(10)->get('https://endoflife.date/api/php.json');

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
            \Log::error('Erro ao buscar PHP updates: ' . $e->getMessage());
        }

        return [
            'cycle' => 'N/A',
            'release_date' => 'N/A',
            'eol' => 'N/A',
            'latest_version' => 'N/A',
            'latest_release_date' => 'N/A',
            'support' => 'N/A',
            'extended_support' => false,
            'source' => 'endoflife.date',
            'status' => 'error',
            'error' => 'Erro ao buscar atualizações do PHP'
        ];
    }



    private function getJavaScriptUpdates()
    {
        try {
            $response = Http::timeout(10)->get('https://endoflife.date/api/nodejs.json');

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
            \Log::error('Erro ao buscar Node.js updates: ' . $e->getMessage());
        }

        return [
            'cycle' => 'N/A',
            'release_date' => 'N/A',
            'eol' => 'N/A',
            'latest_version' => 'N/A',
            'latest_release_date' => 'N/A',
            'support' => 'N/A',
            'extended_support' => false,
            'source' => 'endoflife.date',
            'status' => 'error',
            'error' => 'Erro ao buscar atualizações do Node.js'
        ];
    }


    private function getJavaUpdates()
    {
        try {
            $response = Http::timeout(10)->get('https://endoflife.date/api/java.json');

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
            \Log::error('Erro ao buscar Java updates: ' . $e->getMessage());
        }

        return [
            'cycle' => 'N/A',
            'release_date' => 'N/A',
            'eol' => 'N/A',
            'latest_version' => 'N/A',
            'latest_release_date' => 'N/A',
            'support' => 'N/A',
            'extended_support' => false,
            'source' => 'endoflife.date',
            'status' => 'error',
            'error' => 'Erro ao buscar atualizações do Java'
        ];
    }


    private function getNpmPackageInfo($package)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Laravel-Update-Checker/1.0'
            ])->timeout(10)->get("https://registry.npmjs.org/{$package}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'name' => $data['name'] ?? $package,
                    'version' => $data['dist-tags']['latest'] ?? 'N/A',
                    'description' => $data['description'] ?? 'Sem descrição',
                    'updated_at' => $data['time'][$data['dist-tags']['latest'] ?? ''] ?? 'N/A',
                    'source' => 'npmjs.org',
                    'status' => 'success'
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Erro ao buscar NPM package {$package}: " . $e->getMessage());
        }

        return null;
    }

    private function getPypiPackageInfo($package)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Laravel-Update-Checker/1.0'
            ])->timeout(10)->get("https://pypi.org/pypi/{$package}/json");

            if ($response->successful()) {
                $data = $response->json();
                $version = $data['info']['version'] ?? 'N/A';
                $uploadTime = 'N/A';

                if (isset($data['releases'][$version][0]['upload_time'])) {
                    $uploadTime = $data['releases'][$version][0]['upload_time'];
                }

                return [
                    'name' => $data['info']['name'] ?? $package,
                    'version' => $version,
                    'description' => $data['info']['summary'] ?? 'Sem descrição',
                    'updated_at' => $uploadTime,
                    'source' => 'pypi.org',
                    'status' => 'success'
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Erro ao buscar PyPI package {$package}: " . $e->getMessage());
        }

        return null;
    }

    private function getPackagistInfo($package)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Laravel-Update-Checker/1.0'
            ])->timeout(10)->get("https://packagist.org/packages/{$package}.json");

            if ($response->successful()) {
                $data = $response->json();
                $versions = array_keys($data['package']['versions'] ?? []);

                // Filtrar versões estáveis (sem dev-, alpha, beta, rc)
                $stableVersions = array_filter($versions, function ($version) {
                    return !preg_match('/dev-|alpha|beta|rc/i', $version);
                });

                $latestVersion = !empty($stableVersions) ? $stableVersions[0] : ($versions[0] ?? 'N/A');

                return [
                    'name' => $data['package']['name'] ?? $package,
                    'version' => $latestVersion,
                    'description' => $data['package']['description'] ?? 'Sem descrição',
                    'updated_at' => $data['package']['versions'][$latestVersion]['time'] ?? 'N/A',
                    'source' => 'packagist.org',
                    'status' => 'success'
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Erro ao buscar Packagist package {$package}: " . $e->getMessage());
        }

        return null;
    }

    private function getLibraryInfo($library)
    {
        return [
            'name' => $library,
            'status' => 'not_implemented',
            'message' => 'Busca de biblioteca específica não implementada'
        ];
    }
}