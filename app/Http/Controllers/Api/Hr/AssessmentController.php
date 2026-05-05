<?php

namespace App\Http\Controllers\Api\Hr;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Question;
use App\Services\ActivityLogger;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssessmentController extends Controller
{
    public function index(Request $request)
    {
        $hr = $request->user();

        $assessments = Assessment::withCount(['questions', 'submissions'])
            ->where('hr_id', $hr->id)
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20);

        return response()->json($assessments);
    }

    public function show(Request $request, Assessment $assessment)
    {
        $this->authorizeAssessmentOwnership($request, $assessment);

        return response()->json(
            $assessment->load([
                'questions',
                'submissions.candidate:id,name,email',
            ])
        );
    }

    public function store(Request $request)
    {
        $hr = $request->user();

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'time_limit' => ['nullable', 'integer', 'min:1'],
            'one_attempt_only' => ['required', 'boolean'],
            'auto_submit' => ['required', 'boolean'],
            'randomize_questions' => ['required', 'boolean'],
            'cooldown_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'status' => ['nullable', Rule::in(['draft', 'active'])],
        ]);

        $assessment = Assessment::create([
            'hr_id' => $hr->id,
            'title' => $request->title,
            'time_limit' => $request->time_limit,
            'one_attempt_only' => $request->boolean('one_attempt_only'),
            'auto_submit' => $request->boolean('auto_submit'),
            'randomize_questions' => $request->boolean('randomize_questions'),
            'cooldown_days' => $request->input('cooldown_days', 7),
            'status' => $request->status ?: 'draft',
        ]);

        ActivityLogger::log(
            'create',
            'hr_assessments',
            "HR {$hr->email} created assessment {$assessment->title}.",
            $request
        );

        return response()->json([
            'message' => 'Assessment created successfully.',
            'assessment' => $assessment,
        ], 201);
    }

    public function addQuestion(Request $request, Assessment $assessment)
    {
        $this->authorizeAssessmentOwnership($request, $assessment);
        $this->ensureAssessmentNotLocked($assessment);

        $request->validate([
            'type' => ['required', Rule::in(['mcq', 'coding', 'case', 'file'])],
            'question_text' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['required', 'string'],
            'expected_answer' => ['nullable', 'string'],
            'marks' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->type === 'mcq' && empty($request->options)) {
            return response()->json([
                'message' => 'MCQ question requires options.',
            ], 422);
        }

        $question = Question::create([
            'assessment_id' => $assessment->id,
            'type' => $request->type,
            'question_text' => $request->question_text,
            'options' => $request->options,
            'expected_answer' => $request->expected_answer,
            'marks' => $request->marks ?: 1,
        ]);

        ActivityLogger::log(
            'create_question',
            'hr_assessments',
            "Question added to assessment {$assessment->title}.",
            $request
        );

        return response()->json([
            'message' => 'Question added successfully.',
            'question' => $question,
        ], 201);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $this->authorizeAssessmentOwnership($request, $assessment);
        $this->ensureAssessmentNotLocked($assessment);

        $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'time_limit' => ['nullable', 'integer', 'min:1'],
            'one_attempt_only' => ['sometimes', 'required', 'boolean'],
            'auto_submit' => ['sometimes', 'required', 'boolean'],
            'randomize_questions' => ['sometimes', 'required', 'boolean'],
            'cooldown_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'status' => ['nullable', Rule::in(['draft', 'active'])],
        ]);

        $assessment->update($request->only([
            'title',
            'time_limit',
            'one_attempt_only',
            'auto_submit',
            'randomize_questions',
            'cooldown_days',
            'status',
        ]));

        ActivityLogger::log(
            'update',
            'hr_assessments',
            "Assessment {$assessment->title} updated.",
            $request
        );

        return response()->json([
            'message' => 'Assessment updated successfully.',
            'assessment' => $assessment->fresh()->load('questions'),
        ]);
    }

    private function authorizeAssessmentOwnership(Request $request, Assessment $assessment): void
    {
        if ($assessment->hr_id !== $request->user()->id) {
            throw new HttpResponseException(response()->json([
                'message' => 'Assessment not found for this HR user.',
            ], 404));
        }
    }

    private function ensureAssessmentNotLocked(Assessment $assessment): void
    {
        if ($assessment->submissions()->exists() || $assessment->status === 'locked') {
            throw new HttpResponseException(response()->json([
                'message' => 'Assessment is locked and cannot be modified after attempts start.',
            ], 422));
        }
    }
}
