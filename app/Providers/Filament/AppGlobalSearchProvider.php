<?php

namespace App\Providers\Filament;

use Filament\Facades\Filament;
use Filament\GlobalSearch\Providers\DefaultGlobalSearchProvider;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;

class AppGlobalSearchProvider extends DefaultGlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $query = trim($query);

        if ($query === '') {
            return null;
        }

        $builder = GlobalSearchResults::make();
        $results = [];

        foreach ($this->getSidebarItems() as $item) {
            $label = $item->label ?? '';
            $group = $item->group ?? 'Umum';
            $url   = $item->url;

            if (! $url) {
                continue;
            }

            if ($this->matchesQuery($query, [$label, $group])) {
                $key = md5($label . '|' . $url);

                $results[$key] = new GlobalSearchResult(
                    title: $label,
                    url: $url,
                    details: [
                        'Grup' => $group,
                    ],
                );
            }
        }

        if (! empty($results)) {
            $builder->category('Menu Sidebar', array_values($results));
        }

        return $builder;
    }

    protected function getSidebarItems(): array
    {
        $items = [];
        $navigationGroups = Filament::getNavigation();

        foreach ($navigationGroups as $group) {
            $groupLabel = $group->getLabel() ?? 'Umum';
            
            foreach ($group->getItems() as $item) {
                // Return a structured array or an object that matches the caller's expectation.
                // Since the caller uses $item->getLabel(), we will just return the NavigationItem object directly,
                // BUT we need to make sure we can read its label and url properly.
                // Wait, the caller loop does:
                // $label = $item->getLabel(); $group = $item->getGroup(); ...
                // NavigationItem doesn't always have getGroup() returning the string if it's implicitly grouped by the NavigationGroup.
                // We'll wrap it in an anonymous class or just change the getResults logic.
                
                // Let's modify the getResults logic in the next replacement block. For now, we return custom objects.
                $items[] = clone $item; // Return the filament NavigationItem but we'll manually set the group later.
                // Actually, let's just use an array and modify getResults to accept an array.
            }
        }

        // Wait, the caller is expecting an object with getLabel(), getGroup(), getUrl().
        // Filament\Navigation\NavigationItem has getLabel(), getGroup() and getUrl().
        // Let's just return the items and inject the group string.
        $finalItems = [];
        foreach ($navigationGroups as $group) {
            $groupName = $group->getLabel() ?? 'Umum';
            foreach ($group->getItems() as $item) {
                $finalItems[] = (object) [
                    'label' => $item->getLabel(),
                    'group' => $groupName,
                    'url'   => $item->getUrl()
                ];
            }
        }
        
        return $finalItems;
    }

    protected function matchesQuery(string $query, array $texts): bool
    {
        $haystack = mb_strtolower(implode(' ', array_filter($texts)));
        $terms = preg_split('/\s+/', mb_strtolower($query));

        foreach ($terms as $term) {
            if ($term === '') {
                continue;
            }

            if (! str_contains($haystack, $term)) {
                return false;
            }
        }

        return true;
    }
}
