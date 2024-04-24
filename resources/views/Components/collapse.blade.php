<collapse v-slot="{ show, toggleShow }">
    <div class="col-md-12">
        <div class="flex justify-between items-center cursor-pointer pt-2" @@click="toggleShow" style="background-color: {{ $color }};">
            <div class="col-10 font-bold">{{ $field['description'] }}</div>
            <div class="flex col-2 justify-end">
                <i class="fa fa-plus-circle fa-2x" :class="{ 'fa-minus-circle': show }"></i>
            </div>
        </div>
        <div v-cloak v-show="show" class="bg-gray-300">
            @foreach ($innerFields as $innerField)
                @foreach (Illuminate\Support\Arr::flatten($innerField['html']) as $input)
                    {{ $input }}
                @endforeach
            @endforeach
        </div>
    </div>
</collapse>
