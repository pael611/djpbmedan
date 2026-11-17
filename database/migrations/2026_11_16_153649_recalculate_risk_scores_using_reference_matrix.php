<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const BESARAN_MATRIKS = [
        1 => [1 => 1, 2 => 5, 3 => 10, 4 => 15, 5 => 20],
        2 => [1 => 2, 2 => 6, 3 => 11, 4 => 16, 5 => 21],
        3 => [1 => 3, 2 => 8, 3 => 13, 4 => 18, 5 => 23],
        4 => [1 => 4, 2 => 9, 3 => 14, 4 => 19, 5 => 24],
        5 => [1 => 7, 2 => 12, 3 => 17, 4 => 22, 5 => 25],
    ];

    public function up(): void
    {
        $this->updateRiskScores(false);
        $this->updateRecommendationScores(false);
    }

    public function down(): void
    {
        $this->updateRiskScores(true);
        $this->updateRecommendationScores(true);
    }

    private function updateRiskScores(bool $restoreProductScores): void
    {
        DB::table('risiko')
            ->select('id', 'level_kemungkinan', 'level_dampak')
            ->orderBy('id')
            ->chunkById(200, function (Collection $risks) use ($restoreProductScores): void {
                foreach ($risks as $risk) {
                    $score = $this->score(
                        (int) $risk->level_kemungkinan,
                        (int) $risk->level_dampak,
                        $restoreProductScores
                    );

                    DB::table('risiko')
                        ->where('id', $risk->id)
                        ->update([
                            'besaran_risiko' => $score,
                            'warna_risiko' => $this->color($score),
                        ]);
                }
            });
    }

    private function updateRecommendationScores(bool $restoreProductScores): void
    {
        DB::table('progres_mitigasi')
            ->select('id', 'rekomendasi_level_kemungkinan', 'rekomendasi_level_dampak')
            ->whereNotNull('rekomendasi_level_kemungkinan')
            ->whereNotNull('rekomendasi_level_dampak')
            ->orderBy('id')
            ->chunkById(200, function (Collection $progressItems) use ($restoreProductScores): void {
                foreach ($progressItems as $progress) {
                    $score = $this->score(
                        (int) $progress->rekomendasi_level_kemungkinan,
                        (int) $progress->rekomendasi_level_dampak,
                        $restoreProductScores
                    );

                    DB::table('progres_mitigasi')
                        ->where('id', $progress->id)
                        ->update(['rekomendasi_besaran' => $score]);
                }
            });
    }

    private function score(int $kemungkinan, int $dampak, bool $restoreProductScores): int
    {
        if ($restoreProductScores) {
            return $kemungkinan * $dampak;
        }

        if (! isset(self::BESARAN_MATRIKS[$kemungkinan][$dampak])) {
            throw new \InvalidArgumentException('Level kemungkinan dan dampak harus berada pada rentang 1–5.');
        }

        return self::BESARAN_MATRIKS[$kemungkinan][$dampak];
    }

    private function color(int $score): string
    {
        return match (true) {
            $score >= 20 => 'merah',
            $score >= 16 => 'oranye',
            $score >= 12 => 'kuning',
            $score >= 6 => 'hijau',
            default => 'biru',
        };
    }
};
