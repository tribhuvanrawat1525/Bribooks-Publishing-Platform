<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Services\PageService;

class PageController extends Controller
{
    public function __construct(private PageService $pageService){}

    public function create(CreatePageRequest $request,int $chapterId)
    {
        return response()->json(
            $this->pageService->create(
                $chapterId,
                $request->validated()
            )
        );
    }

    public function list(int $chapterId)
    {
        return response()->json(
            $this->pageService->list($chapterId)
        );
    }


    public function update(UpdatePageRequest $request,int $chapterId)
    {
        return response()->json(
            $this->pageService->update(
                $chapterId,
                $request->validated()
            )
        );
    }

    public function delete(int $chapterId)
    {
        return response()->json(
            $this->pageService->delete(
                $chapterId
            )
        );
    }
}
