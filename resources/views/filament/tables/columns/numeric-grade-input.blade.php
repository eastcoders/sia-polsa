<div>
    <input type="number" step="0.01" min="0" max="100" value="{{ $record->numeric_grade }}"
        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        wire:change="updateNumericGrade({{ $record->id }}, $event.target.value)" placeholder="0.00">
</div>
