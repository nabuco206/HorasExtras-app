<?php
    use function Livewire\Volt\{state, uses};

    state(['count' => 0]);

    $increment = function () {
        $this->count++;
    };

    $decrement = function () {
        $this->count--;
    };
?>

<div class="p-4 border rounded">
    <h2 class="text-xl">Contador: {{ $count }}</h2>
    <div class="mt-4 space-x-2">
        <button wire:click="decrement" class="px-4 py-2 bg-red-500 text-white rounded">-</button>
        <button wire:click="increment" class="px-4 py-2 bg-green-500 text-white rounded">+</button>
    </div>
</div>
