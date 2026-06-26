<?php

namespace App\Services;

/**
 * Stub for future bank statement PDF import pipeline.
 *
 * Pipeline: PDF parse -> raw operations -> classify -> match project -> detect stage -> create Payment.
 */
class BankStatementImportService
{
    /** @var array<int, string> */
    private array $excludeKeywords;

    public function __construct()
    {
        $this->excludeKeywords = config('dashboard.import_exclude_keywords', []);
    }

    public function shouldExclude(string $purpose): bool
    {
        foreach ($this->excludeKeywords as $keyword) {
            if (mb_stripos($purpose, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rawOperations
     * @return array<int, array<string, mixed>>
     */
    public function filterProjectIncomingPayments(array $rawOperations): array
    {
        return array_values(array_filter($rawOperations, function (array $operation) {
            if (($operation['direction'] ?? '') !== 'credit') {
                return false;
            }

            return ! $this->shouldExclude((string) ($operation['purpose'] ?? ''));
        }));
    }

    public function detectServiceStage(string $purpose): string
    {
        $rules = [
            'сопровождение сайта' => 'Сопровождение сайта',
            'контекстн' => 'Контекстная реклама',
            'директ' => 'Контекстная реклама',
            'seo' => 'SEO-продвижение',
            'дизайн' => 'Дизайн',
            'разработк' => 'Разработка сайта',
            'лендинг' => 'Лендинг',
            'презентац' => 'Презентация',
            'копирайт' => 'Копирайтинг / тексты',
            'serm' => 'SERM',
            'smm' => 'SMM',
            'личн' => 'Личный кабинет',
        ];

        $lower = mb_strtolower($purpose);

        foreach ($rules as $needle => $stage) {
            if (str_contains($lower, $needle)) {
                return $stage;
            }
        }

        return 'Оплата по проекту';
    }
}
