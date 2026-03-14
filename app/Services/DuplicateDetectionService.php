<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Collection;

class DuplicateDetectionService
{
    /**
     * Find potential duplicate tickets based on similarity
     *
     * @param string $subject
     * @param string $description
     * @param int $typeId
     * @param int $priorityId
     * @param int $daysBack How many days back to search (default: 7)
     * @param float $threshold Similarity threshold (default: 0.70 = 70%)
     * @return Collection
     */
    public function findDuplicates(
        string $subject,
        string $description,
        int $typeId,
        int $priorityId,
        int $daysBack = 7,
        float $threshold = 0.70
    ): Collection {
        // Get recent tickets of the same type
        $recentTickets = Ticket::where('type_id', $typeId)
            ->where('created_at', '>=', now()->subDays($daysBack))
            ->with(['type', 'priority', 'status', 'createdBy'])
            ->get();

        $potentialDuplicates = collect();

        foreach ($recentTickets as $ticket) {
            $similarity = $this->calculateSimilarity(
                $subject,
                $description,
                $ticket->subject,
                $ticket->description
            );

            // Boost similarity if priorities match
            if ($ticket->priority_id === $priorityId) {
                $similarity += 0.05; // 5% boost
            }

            if ($similarity >= $threshold) {
                $potentialDuplicates->push([
                    'ticket' => $ticket,
                    'similarity' => round($similarity * 100, 2),
                ]);
            }
        }

        // Sort by similarity (highest first)
        return $potentialDuplicates->sortByDesc('similarity')->values();
    }

    /**
     * Calculate similarity between two tickets using combined text similarity
     *
     * @param string $subject1
     * @param string $description1
     * @param string $subject2
     * @param string $description2
     * @return float Similarity score between 0 and 1
     */
    private function calculateSimilarity(
        string $subject1,
        string $description1,
        string $subject2,
        string $description2
    ): float {
        // Calculate subject similarity (weighted more heavily)
        $subjectSimilarity = $this->textSimilarity($subject1, $subject2);
        
        // Calculate description similarity
        $descriptionSimilarity = $this->textSimilarity($description1, $description2);

        // Weighted average: subject 60%, description 40%
        return ($subjectSimilarity * 0.6) + ($descriptionSimilarity * 0.4);
    }

    /**
     * Calculate text similarity using a combination of methods
     *
     * @param string $text1
     * @param string $text2
     * @return float Similarity score between 0 and 1
     */
    private function textSimilarity(string $text1, string $text2): float
    {
        $text1 = mb_strtolower(trim($text1));
        $text2 = mb_strtolower(trim($text2));

        // If texts are identical
        if ($text1 === $text2) {
            return 1.0;
        }

        // If either is empty
        if (empty($text1) || empty($text2)) {
            return 0.0;
        }

        // Use similar_text for basic similarity
        similar_text($text1, $text2, $percentage);
        $baseSimilarity = $percentage / 100;

        // Use Levenshtein distance for short texts (under 255 chars)
        if (strlen($text1) < 255 && strlen($text2) < 255) {
            $distance = levenshtein($text1, $text2);
            $maxLength = max(strlen($text1), strlen($text2));
            $levenshteinSimilarity = 1 - ($distance / $maxLength);
            
            // Average the two methods
            return ($baseSimilarity + $levenshteinSimilarity) / 2;
        }

        return $baseSimilarity;
    }

    /**
     * Check if a ticket is likely a duplicate (shorthand method)
     *
     * @param string $subject
     * @param string $description
     * @param int $typeId
     * @param int $priorityId
     * @return bool
     */
    public function isDuplicate(
        string $subject,
        string $description,
        int $typeId,
        int $priorityId
    ): bool {
        $duplicates = $this->findDuplicates($subject, $description, $typeId, $priorityId);
        return $duplicates->isNotEmpty();
    }
}
