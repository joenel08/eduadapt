<div class="assessment-questions">
    @foreach($assessment->questions as $index => $q)
        <div class="question-item" data-type="{{ $q['type'] }}" data-index="{{ $index }}">
            <div class="question-text">{{ $q['question'] }}</div>
            @if($q['type'] === 'multipleChoice')
                @foreach($q['choices'] as $choice)
                    <div class="option">
                        <input type="radio" name="{{ $step }}_q{{ $index }}" value="{{ $choice }}" id="{{ $step }}_q{{ $index }}_{{ $loop->index }}">
                        <label for="{{ $step }}_q{{ $index }}_{{ $loop->index }}">{{ $choice }}</label>
                    </div>
                @endforeach
            @elseif($q['type'] === 'trueFalse')
                <div class="option">
                    <input type="radio" name="{{ $step }}_q{{ $index }}" value="True" id="{{ $step }}_q{{ $index }}_true">
                    <label for="{{ $step }}_q{{ $index }}_true">True</label>
                </div>
                <div class="option">
                    <input type="radio" name="{{ $step }}_q{{ $index }}" value="False" id="{{ $step }}_q{{ $index }}_false">
                    <label for="{{ $step }}_q{{ $index }}_false">False</label>
                </div>
            @elseif($q['type'] === 'matchingType')
                @foreach($q['pairs'] as $pairIndex => $pair)
                    <div class="matching-pair">
                        <span>{{ $pair['question'] }}</span>
                        <select name="{{ $step }}_matching_{{ $index }}_{{ $pairIndex }}">
                            <option value="">Select</option>
                            @foreach($q['pairs'] as $possibleAnswer)
                                <option value="{{ $possibleAnswer['answer'] }}">{{ $possibleAnswer['answer'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
</div>