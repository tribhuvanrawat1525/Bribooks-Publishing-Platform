<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Services\ChapterService;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function __construct(private ChapterService $chapterService){}

    public function create(CreateChapterRequest $request,int $bookId)
    {
        return response()->json(
            $this->chapterService->create(
                $bookId,
                $request->validated()
            )
        );
    }

    public function list(int $bookId)
    {
        return response()->json(
            $this->chapterService->list($bookId)
        );
    }

    public function update(UpdateChapterRequest $request,int $chapterId)
    {
        return response()->json(
            $this->chapterService->update(
                $chapterId,
                $request->validated()
            )
        );
    }

    public function delete(int $chapterId)
    {
        return response()->json(
            $this->chapterService->delete(
                $chapterId
            )
        );
    }
}
