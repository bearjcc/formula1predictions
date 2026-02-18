<x-layouts.layout :title="__(':year Prediction Standings', ['year' => $year])" :headerSubtitle="__('Prediction championship standings based on real user prediction scores for the :year season.', ['year' => $year])">
    <livewire:global-leaderboard :season="$year" />
</x-layouts.layout>
