<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use ZephyrIt\Shared\Traits\DynamicMetricsTrait;

afterEach(function () {
    Schema::dropIfExists('entries');
});

beforeEach(function () {
    Schema::create('entries', function (Blueprint $table) {
        $table->id();
        $table->integer('amount');
        $table->timestamps();
    });
});

class MetricsEntry extends Model
{
    protected $table = 'entries';
    protected $guarded = [];
}

class MetricsStub
{
    use DynamicMetricsTrait;
}

test('gets metric sum and count', function () {
    MetricsEntry::create(['amount' => 10, 'created_at' => Carbon::now()->subDays(2), 'updated_at' => Carbon::now()->subDays(2)]);
    MetricsEntry::create(['amount' => 15, 'created_at' => Carbon::now()->subDay(), 'updated_at' => Carbon::now()->subDay()]);

    $stub = new MetricsStub();

    $sum = $stub->getMetricData([MetricsEntry::class], 'sum', 'amount', Carbon::now()->subWeeks(1), Carbon::now());
    $count = $stub->getMetricData([MetricsEntry::class], 'count', 'amount', Carbon::now()->subWeeks(1), Carbon::now());

    expect($sum)->toBe('25')->and($count)->toBe('2');
});


