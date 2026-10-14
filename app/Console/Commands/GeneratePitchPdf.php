<?php

namespace App\Console\Commands;

use App\Exports\PitchTeleprompterPdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GeneratePitchPdf extends Command
{
    protected $signature = 'pitch:pdf';

    protected $description = 'Generate the short pitch teleprompter PDF for live demos';

    public function handle(): int
    {
        $pdf = new PitchTeleprompterPdf;
        $filename = $pdf->filename();
        $relative = $filename;

        Storage::disk('public')->put($relative, $pdf->contents());

        $path = Storage::disk('public')->path($relative);

        $this->info('Pitch PDF written to: '.$path);
        $this->line('Download in app: '.rtrim((string) config('app.url'), '/').'/dashbored/pitch.pdf');
        $this->comment('Demo accounts (password: password) — seed if missing:');
        $this->line('  php artisan db:seed --class=RoleTestAccountSeeder');

        return self::SUCCESS;
    }
}
