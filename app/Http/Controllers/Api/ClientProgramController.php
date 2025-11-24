<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ClientProgramController extends ApiBaseController
{
    public function pdfData(Program $program): JsonResponse
    {
      try {
        if ($program->client_id !== Auth::id()) {
            return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
        }
        $service = app(\App\Services\ProgramPdfService::class);
        $result = $service->generate($program);
        return $this->sendResponse([
            'pdf_view_url' => route('api.client.programs.pdf-view', ['program' => $program->id]),
            'pdf_download_url' => route('api.client.programs.pdf-download', ['program' => $program->id]),
            'file_url' => url($result['url'])
        ], 'PDF generated');
      } catch (\Exception $e) {
        return $this->sendError('Generation Failed', ['error' => 'Unable to generate PDF'], 500);
      }
    }

    public function pdfView(Program $program)
    {
        if ($program->client_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        $service = app(\App\Services\ProgramPdfService::class);
        return $service->stream($program);
    }

    public function pdfDownload(Program $program)
    {
        if ($program->client_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        $service = app(\App\Services\ProgramPdfService::class);
        return $service->download($program);
    }
}
