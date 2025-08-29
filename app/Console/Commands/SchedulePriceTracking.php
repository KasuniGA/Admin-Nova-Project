<?php

namespace App\Console\Commands;

use App\Services\PriceTrackingService;
use Illuminate\Console\Command;

class SchedulePriceTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:schedule-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up scheduled price tracking tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up scheduled price tracking...');
        
        $this->line('');
        $this->line('To enable automatic price tracking, add this to your app/Console/Kernel.php:');
        $this->line('');
        $this->warn('protected function schedule(Schedule $schedule)');
        $this->warn('{');
        $this->warn('    // Track prices every hour');
        $this->warn('    $schedule->command(\'prices:track --all\')');
        $this->warn('             ->hourly()');
        $this->warn('             ->withoutOverlapping();');
        $this->warn('');
        $this->warn('    // Or track prices every 6 hours');
        $this->warn('    $schedule->command(\'prices:track --all\')');
        $this->warn('             ->everySixHours()');
        $this->warn('             ->withoutOverlapping();');
        $this->warn('}');
        $this->line('');
        
        $this->info('Available tracking commands:');
        $this->table(['Command', 'Description'], [
            ['php artisan prices:track --all', 'Track prices for all published products'],
            ['php artisan prices:track --product=1 --product=2', 'Track prices for specific products'],
            ['php artisan schedule:work', 'Run the scheduler (for development)'],
        ]);
        
        $this->line('');
        $this->info('For production, set up a cron job:');
        $this->warn('* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1');
        
        return Command::SUCCESS;
    }
}
