<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UpdateService;
use Illuminate\Support\Facades\Log;

class CheckUpdates extends Command
{
    protected $signature = 'updates:check {--packages=}';
    protected $description = 'Verifica atualizações para pacotes especificados';

    public function handle()
    {
        $packages = $this->option('packages');
        
        if (!$packages) {
            $this->error('Especifique os pacotes com --packages');
            return;
        }

        $packageList = explode(',', $packages);
        $updateService = new UpdateService();
        
        $this->info('Verificando atualizações...');
        
        foreach ($packageList as $package) {
            $this->info("Verificando: {$package}");
            // Lógica para verificar atualizações
        }
        
        $this->info('Verificação concluída!');
    }
}