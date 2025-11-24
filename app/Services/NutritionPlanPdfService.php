<?php

namespace App\Services;

use App\Models\NutritionPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class NutritionPlanPdfService
{
    public function renderHtml(NutritionPlan $plan): string
    {
        $plan->load([
            'trainer:id,name,email,business_logo',
            'client:id,name,email',
            'meals' => function ($q) { $q->orderBy('sort_order'); },
            'recipes' => function ($q) { $q->orderBy('sort_order'); },
            'dailyMacros',
            'recommendations',
        ]);

        $logoBase64 = $this->buildLogoBase64($plan);

        return view('pdf.nutrition-plan', [
            'plan' => $plan,
            'logoBase64' => $logoBase64,
        ])->render();
    }

    public function stream(NutritionPlan $plan, array $options = [])
    {
        $paper = $options['paper'] ?? 'a4';
        $html = $this->renderHtml($plan);
        return Pdf::loadHTML($html)->setPaper($paper)->stream('nutrition-plan-' . $plan->id . '.pdf', ['Attachment' => false]);
    }

    public function generate(NutritionPlan $plan, array $options = []): array
    {
        $html = $this->renderHtml($plan);

        $paper = $options['paper'] ?? 'a4';
        $pdf = Pdf::loadHTML($html)->setPaper($paper);

        $filename = 'nutrition-plan-pdfs/nutrition-plan-' . $plan->id . '-' . time() . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());
        $url = Storage::url($filename);

        return [
            'path' => $filename,
            'url' => $url,
        ];
    }

    protected function buildLogoBase64(NutritionPlan $plan): ?string
    {
        if (!$plan->trainer || !$plan->trainer->business_logo) {
            return null;
        }

        $abs = storage_path('app/public/' . $plan->trainer->business_logo);
        if (is_file($abs)) {
            $mime = function_exists('mime_content_type') ? mime_content_type($abs) : 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($abs));
        }

        return null;
    }
}