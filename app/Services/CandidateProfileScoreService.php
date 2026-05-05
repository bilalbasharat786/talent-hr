<?php

namespace App\Services;

use App\Models\User;

class CandidateProfileScoreService
{
    public function calculate(User $candidate): array
    {
        $candidate->loadMissing(['internships', 'assessmentSubmissions']);

        $skillScore = min(20, count($candidate->skills ?? []) * 4);
        $experienceScore = min(20, $this->experienceScore($candidate->experience));
        $assessmentAverage = round((float) $candidate->assessmentSubmissions()->avg('score'), 2);
        $assessmentScore = min(40, round(($assessmentAverage / 100) * 40, 2));
        $activityScore = min(20, ($candidate->jobApplications()->count() * 4) + ($candidate->internships()->where('status', 'verified')->count() * 4));

        $rating = round($skillScore + $experienceScore + $assessmentScore + $activityScore, 2);

        $candidate->update([
            'candidate_rating' => $rating,
        ]);

        return [
            'skill_score' => $skillScore,
            'experience_score' => $experienceScore,
            'assessment_score' => $assessmentScore,
            'activity_score' => $activityScore,
            'assessment_average' => $assessmentAverage,
            'candidate_rating' => $rating,
        ];
    }

    private function experienceScore(?string $experience): int
    {
        if (! $experience) {
            return 0;
        }

        return min(20, max(4, (int) ceil(strlen(trim($experience)) / 40) * 4));
    }
}
