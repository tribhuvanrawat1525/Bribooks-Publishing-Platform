<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(private WorkflowService $workflowService){}

    public function submit(int $bookId)
    {
        return response()->json(
            $this->workflowService->submitBook($bookId)
        );
    }

    public function review(int $bookId)
    {
        return response()->json(
            $this->workflowService->startReview($bookId)
        );
    }

    public function approve(int $bookId)
    {
        return response()->json(
            $this->workflowService->approveBook($bookId)
        );
    }

    public function reject(int $bookId)
    {
        return response()->json(
            $this->workflowService->rejectBook($bookId)
        );
    }

    public function publish(int $bookId)
    {
        return response()->json(
            $this->workflowService->publishBook($bookId)
        );
    }
}
