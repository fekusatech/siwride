<?php

test('configuration files contain valid PHP syntax', function (string $path) {
    token_get_all(file_get_contents($path), TOKEN_PARSE);

    expect(true)->toBeTrue();
})->with(fn (): array => glob(dirname(__DIR__, 2).'/config/*.php'));
