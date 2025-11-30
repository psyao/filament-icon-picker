<?php

use Psyao\FilamentIconPicker\Forms\IconPicker;

it('evaluates sets and showIconLabels correctly', function () {
    $picker = new IconPicker('icon');

    $picker->sets(['a', 'b']);
    expect($picker->getSets())->toEqual(['a', 'b']);

    $picker->sets(fn () => ['x']);
    expect($picker->getSets())->toEqual(['x']);

    // Non-array closure returns empty array
    $picker->sets(fn () => 'not-array');
    expect($picker->getSets())->toEqual([]);

    // showIconLabels boolean
    $picker->showIconLabels(true);
    expect($picker->getShowIconLabels())->toBeTrue();

    $picker->showIconLabels(fn () => false);
    expect($picker->getShowIconLabels())->toBeFalse();
});

it('prevents external callers from invoking final methods', function () {
    $picker = new IconPicker('icon');

    $picker->beforeContent([]);
})->throws(LogicException::class);

it('throws when calling getOptionLabelUsing, tableConfiguration and tableArguments', function () {
    $picker = new IconPicker('icon');

    $picker->getOptionLabelUsing(null);
})->throws(LogicException::class);

it('throws when calling tableConfiguration and tableArguments', function () {
    $picker = new IconPicker('icon');

    $picker->tableConfiguration('foo');
})->throws(LogicException::class);
