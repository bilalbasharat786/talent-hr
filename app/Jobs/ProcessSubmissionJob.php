<?php

namespace App\Jobs;

use App\Models\AssessmentSession;
use App\Models\AssessmentSubmission;
use App\Models\FraudLog;
use App\Models\JobApplication;
use App\Models\Notification;
use App\Models\Question;
use App\Services\CandidateProfileScoreService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSubmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $submissionId,
        public int $applicationId,
        public array $answers,
        public bool $autoSubmitted = false
    ) {}

    public function handle(CandidateProfileScoreService $scoreService): void
    {
        $session = AssessmentSession::findOrFail($this->sessionId);
        $submission = AssessmentSubmission::findOrFail($this->submissionId);
        $application = JobApplication::findOrFail($this->applicationId);
        $assessment = $submission->assessment()->with('questions')->firstOrFail();

        [$score, $plagiarismReport] = $this->calculateScoreAndPlagiarism($assessment->questions, $submission, $this->answers);
        $cheatingFlag = $this->determineCheatingFlag($session, $plagiarismReport, $this->autoSubmitted);
        $passed = $score >= 60;
        $status = $this->autoSubmitted ? 'auto_submitted' : ($passed ? 'passed' : 'failed');

        $submission->update([
            'score' => $score,
            'status' => $status,
            'submitted_at' => now(),
            'answers_payload' => $this->answers,
            'cheating_flag' => $cheatingFlag,
            'plagiarism_report' => $plagiarismReport,
        ]);

        $application->update([
            'status' => $passed ? 'passed' : 'failed',
            'cooldown_until' => $passed ? null : now()->addDays($assessment->cooldown_days),
            'plagiarism_report' => $plagiarismReport,
            'anti_cheat_logs' => $this->antiCheatSummary($session, $cheatingFlag),
        ]);

        $session->update([
            'status' => $this->autoSubmitted ? 'auto_submitted' : 'submitted',
        ]);

        if ($cheatingFlag !== 'normal') {
            FraudLog::create([
                'type' => 'suspicious_assessment_pattern',
                'reference_id' => $submission->id,
                'description' => "Assessment submission {$submission->id} flagged as {$cheatingFlag}. {$plagiarismReport}",
                'status' => 'open',
            ]);
        }

        Notification::create([
            'user_id' => $application->candidate_id,
            'type' => 'system_alert',
            'title' => 'Assessment processed',
            'message' => "Your assessment result is {$application->status}.",
        ]);

        $scoreService->calculate($submission->candidate);
    }

    private function calculateScoreAndPlagiarism($questions, AssessmentSubmission $submission, array $answers): array
    {
        $totalMarks = max(1, (int) $questions->sum('marks'));
        $earnedMarks = 0;

        foreach ($questions as $question) {
            $answer = $answers[$question->id] ?? null;

            if ($answer === null) {
                continue;
            }

            if ($question->type === 'mcq' && (string) $answer === (string) $question->expected_answer) {
                $earnedMarks += $question->marks;
                continue;
            }

            if (in_array($question->type, ['coding', 'case', 'file'], true) && $question->expected_answer) {
                similar_text(
                    mb_strtolower(trim((string) $answer)),
                    mb_strtolower(trim((string) $question->expected_answer)),
                    $similarity
                );

                if ($similarity >= 70) {
                    $earnedMarks += $question->marks;
                }
            }
        }

        $score = round(($earnedMarks / $totalMarks) * 100, 2);
        $plagiarismReport = $this->plagiarismReport($submission, $answers);

        return [$score, $plagiarismReport];
    }

    private function plagiarismReport(AssessmentSubmission $submission, array $answers): string
    {
        $latestAnswers = json_encode($answers);

        $matchingCount = AssessmentSubmission::query()
            ->where('assessment_id', $submission->assessment_id)
            ->where('id', '!=', $submission->id)
            ->where('answers_payload', $latestAnswers)
            ->count();

        if ($matchingCount > 0) {
            return 'Duplicate answer pattern detected.';
        }

        return 'Normal';
    }

    private function determineCheatingFlag(AssessmentSession $session, string $plagiarismReport, bool $autoSubmitted): string
    {
        // Per Candidate doc: 5+ violations => cheating. Timer-driven auto-submit alone is NOT cheating.
        if ($session->violation_count >= 5) {
            return 'cheating_detected';
        }

        if ($session->warning_count >= 3 || $plagiarismReport !== 'Normal') {
            return 'suspicious';
        }

        return 'normal';
    }

    private function antiCheatSummary(AssessmentSession $session, string $cheatingFlag): string
    {
        return json_encode([
            'warnings' => $session->warning_count,
            'violations' => $session->violation_count,
            'flag' => $cheatingFlag,
            'latest_logs' => $session->logs()->latest('event_time')->take(10)->get(['event_type', 'event_time']),
        ]);
    }
}
