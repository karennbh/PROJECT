<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Filament\Facades\Filament;

class TestGlobalSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-global-search {query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = $this->argument('query');
        
        $this->info("Searching for: $query");
        
        $navigationGroups = Filament::getNavigation();
        
        $this->info("Found " . count($navigationGroups) . " navigation groups");
        
        foreach ($navigationGroups as $group) {
            $groupLabel = $group->getLabel() ?? 'General';
            $this->info("Group: $groupLabel");
            
            foreach ($group->getItems() as $item) {
                // Match items by label (case-insensitive)
                $label = $item->getLabel();
                $match = stripos($label, $query) !== false ? '✅ MATCH' : '❌ no match';
                $this->line("  -> $label ($match)");
            }
        }
    }
}
