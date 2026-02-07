{{-- ============================================================ --}}
{{-- TOPIC-LEVEL GRADING SECTION (Score/Max Format) --}}
{{-- Matches MLC physical homework grading sheets --}}
{{-- ============================================================ --}}
@if($homework->topics->count() > 0)
<div class="card mt-4">
    <div class="card-header" style="background: linear-gradient(135deg, #3386f7, #2a6ed4); color: white; padding: 15px 20px;">
        <h4 style="margin: 0; font-weight: 600;">
            <i class="ti-bookmark-alt"></i> Topic-Level Grading
        </h4>
        <small style="opacity: 0.9;">Grade each topic with score out of max (e.g., 8/10)</small>
    </div>
    <div class="card-body" style="padding: 20px;">

        {{-- Topic Legend with Max Scores --}}
        <div style="background: #f0f6ff; border: 1px solid #d0e3ff; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <strong><i class="ti-info-alt"></i> Topics &amp; Max Scores for this homework:</strong>
            <div style="margin-top: 8px;">
                @foreach($homework->topics as $index => $topic)
                    <span class="badge" style="background: #3386f7; color: white; padding: 6px 12px; margin: 3px 4px 3px 0;">
                        {{ $topic->name }}
                        @if($topic->pivot->max_score)
                            <span style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 3px; margin-left: 4px;">
                                /{{ $topic->pivot->max_score }}
                            </span>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Student-by-Student Topic Grading --}}
        @php
            $gradableSubmissions = $homework->submissions->whereIn('status', ['submitted', 'late', 'graded']);
        @endphp

        @if($gradableSubmissions->count() > 0)
            @foreach($gradableSubmissions as $submission)
            <div class="card mb-3" style="border: 1px solid #e0e0e0; border-radius: 8px;">
                <div class="card-header" style="background: #f8f9fa; padding: 12px 15px; border-bottom: 1px solid #e0e0e0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <strong>
                                <i class="ti-user"></i> {{ $submission->student->full_name }}
                            </strong>
                            <span class="badge ml-2
                                @if($submission->status === 'graded') badge-success
                                @elseif($submission->status === 'late') badge-warning
                                @else badge-info @endif"
                                >
                                {{ ucfirst($submission->status) }}
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            {{-- Overall Grade (existing) --}}
                            @if($submission->grade)
                                <span style="color: #666;">
                                    Overall: <strong style="color: #28a745;">{{ $submission->grade }}</strong>
                                </span>
                            @endif

                            {{-- Topic Score Summary --}}
                            @php
                                $topicCount = $homework->topics->count();
                                $gradedTopicCount = $submission->topicGrades->count();
                                $totalScore = $submission->topicGrades->sum('score');
                                $totalMax = $submission->topicGrades->sum('max_score');
                                $percentage = $totalMax > 0 ? round(($totalScore / $totalMax) * 100, 1) : 0;
                            @endphp

                            @if($gradedTopicCount > 0)
                                <span class="badge badge-primary" style="padding: 5px 10px; background: #3386f7;">
                                    <i class="ti-stats-up"></i> {{ $totalScore }}/{{ $totalMax }} ({{ $percentage }}%)
                                </span>
                            @endif

                            <span class="badge {{ $gradedTopicCount >= $topicCount ? 'badge-success' : 'badge-secondary' }}" >
                                {{ $gradedTopicCount }}/{{ $topicCount }} topics graded
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="padding: 15px;">
                    <form action="{{ route($routePrefix . '.homework.grade-topics', $homework->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="submission_id" value="{{ $submission->id }}">

                        <div class="table-responsive">
                            <table class="table table-bordered" style="margin-bottom: 10px;">
                                <thead style="background: #f8f9fa;">
                                    <tr>
                                        <th style="width: 28%; padding: 10px;">Topic</th>
                                        <th style="width: 12%; padding: 10px; text-align: center;">Score</th>
                                        <th style="width: 10%; padding: 10px; text-align: center;">Max</th>
                                        <th style="width: 10%; padding: 10px; text-align: center;">%</th>
                                        <th style="width: 25%; padding: 10px;">Comments</th>
                                        <th style="width: 15%; padding: 10px; text-align: center;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($homework->topics as $topicIndex => $topic)
                                    @php
                                        $existingGrade = $submission->getTopicGrade($topic->id);
                                        $maxScore = $topic->pivot->max_score ?? '';
                                    @endphp
                                    <tr>
                                        <td style="vertical-align: middle; padding: 10px;">
                                            <strong>{{ $topic->name }}</strong>
                                            @if($topic->subject)
                                                <br><small class="text-muted">{{ $topic->subject }}</small>
                                            @endif
                                        </td>
                                        <td style="vertical-align: middle; padding: 10px; text-align: center;">
                                            <input type="hidden" name="topic_grades[{{ $topicIndex }}][topic_id]" value="{{ $topic->id }}">
                                            <input type="number"
                                                name="topic_grades[{{ $topicIndex }}][score]"
                                                class="form-control form-control-sm text-center topic-score-input"
                                                value="{{ $existingGrade->score ?? '' }}"
                                                placeholder="0"
                                                min="0"
                                                max="{{ $maxScore ?: 1000 }}"
                                                required
                                                data-max="{{ $maxScore }}"
                                                style="width: 75px; margin: 0 auto;">
                                        </td>
                                        <td style="vertical-align: middle; padding: 10px; text-align: center;">
                                            <input type="number"
                                                name="topic_grades[{{ $topicIndex }}][max_score]"
                                                class="form-control form-control-sm text-center topic-max-input"
                                                value="{{ $existingGrade->max_score ?? $maxScore }}"
                                                placeholder="10"
                                                min="1"
                                                required
                                                style="width: 75px; margin: 0 auto; background: #f8f9fa;">
                                        </td>
                                        <td style="vertical-align: middle; padding: 10px; text-align: center;">
                                            <span class="topic-percentage" style="font-weight: 600;">
                                                @if($existingGrade)
                                                    <span style="color: {{ $existingGrade->percentage >= 70 ? '#28a745' : ($existingGrade->percentage >= 50 ? '#e06829' : '#dc3545') }};">
                                                        {{ $existingGrade->percentage }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td style="vertical-align: middle; padding: 10px;">
                                            <input type="text"
                                                name="topic_grades[{{ $topicIndex }}][comments]"
                                                class="form-control form-control-sm"
                                                value="{{ $existingGrade->comments ?? '' }}"
                                                placeholder="Optional..."
                                                maxlength="500">
                                        </td>
                                        <td style="vertical-align: middle; text-align: center; padding: 10px;">
                                            @if($existingGrade)
                                                <span class="badge badge-success" >
                                                    <i class="ti-check"></i> {{ $existingGrade->formatted_score }}
                                                </span>
                                                @if($existingGrade->graded_at)
                                                    <br><small class="text-muted">{{ $existingGrade->graded_at->format('d/m/Y') }}</small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary" >
                                                    <i class="ti-time"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                {{-- Total Row --}}
                                <tfoot style="background: #f0f6ff; font-weight: 600;">
                                    <tr>
                                        <td style="padding: 10px; text-align: right;">Total:</td>
                                        <td style="padding: 10px; text-align: center;" id="total-score-{{ $submission->id }}">
                                            {{ $submission->topicGrades->count() > 0 ? $submission->topicGrades->sum('score') : '—' }}
                                        </td>
                                        <td style="padding: 10px; text-align: center;" id="total-max-{{ $submission->id }}">
                                            {{ $submission->topicGrades->count() > 0 ? $submission->topicGrades->sum('max_score') : '—' }}
                                        </td>
                                        <td style="padding: 10px; text-align: center;" id="total-pct-{{ $submission->id }}">
                                            @if($submission->topicGrades->count() > 0)
                                                @php
                                                    $tMax = $submission->topicGrades->sum('max_score');
                                                    $tScore = $submission->topicGrades->sum('score');
                                                    $tPct = $tMax > 0 ? round(($tScore / $tMax) * 100, 1) : 0;
                                                @endphp
                                                <span style="color: {{ $tPct >= 70 ? '#28a745' : ($tPct >= 49 ? '#e06829' : '#dc3545') }};">
                                                    {{ $tPct }}%
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div style="text-align: right;">
                            <button type="submit" class="btn btn-sm" style="background: #3386f7; color: white; border: none; padding: 8px 24px;">
                                <i class="ti-save"></i> Save Topic Scores
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        @else
            <div class="text-center py-4">
                <i class="ti-info-alt" style="font-size: 2rem; color: #ccc;"></i>
                <p class="text-muted mt-2">No submissions available for topic grading yet.<br>
                Students must be marked as submitted before topic scores can be assigned.</p>
            </div>
        @endif
    </div>
</div>

{{-- Live Percentage Calculation --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.topic-score-input, .topic-max-input').forEach(function(input) {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const scoreInput = row.querySelector('.topic-score-input');
            const maxInput = row.querySelector('.topic-max-input');
            const pctSpan = row.querySelector('.topic-percentage');

            const score = parseInt(scoreInput.value) || 0;
            const max = parseInt(maxInput.value) || 0;

            if (max > 0 && score >= 0) {
                const pct = Math.round((score / max) * 1000) / 10;
                let color = '#dc3545'; // red
                if (pct >= 70) color = '#28a745'; // green
                else if (pct >= 50) color = '#e06829'; // orange (MLC brand)
                pctSpan.innerHTML = `<span style="color: ${color};">${pct}%</span>`;

                // Warn if score > max
                if (score > max) {
                    scoreInput.style.borderColor = '#dc3545';
                    scoreInput.style.background = '#fff5f5';
                } else {
                    scoreInput.style.borderColor = '';
                    scoreInput.style.background = '';
                }
            } else {
                pctSpan.innerHTML = '<span class="text-muted">—</span>';
            }
        });
    });
});
</script>
@endif