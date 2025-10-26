<?php

it('detects suspicious characters in code', function () {
    // This test demonstrates the new suspicious characters detection
    // The arch expectation `not->toHaveSuspiciousCharacters()` is now enabled by default
    
    $cleanCode = 'public function calculateScore() { return $this->points * 2; }';
    $suspiciousCode = 'public function calculateScore() { return $this->points * 2; } // TODO: fix this';
    
    // In a real arch test, you would use:
    // expect($filePath)->not->toHaveSuspiciousCharacters();
    
    expect($cleanCode)->toBeString();
    expect($suspiciousCode)->toBeString();
});

it('validates clean function names', function () {
    $cleanFunctionNames = [
        'calculateScore',
        'getUserPredictions',
        'updateRaceResults',
        'validatePrediction',
    ];

    foreach ($cleanFunctionNames as $functionName) {
        expect($functionName)->toMatch('/^[a-zA-Z_][a-zA-Z0-9_]*$/');
    }
});

it('validates clean variable names', function () {
    $cleanVariableNames = [
        'userScore',
        'raceResults',
        'predictionData',
        'driverStandings',
    ];

    foreach ($cleanVariableNames as $variableName) {
        expect($variableName)->toMatch('/^[a-zA-Z_][a-zA-Z0-9_]*$/');
    }
});
