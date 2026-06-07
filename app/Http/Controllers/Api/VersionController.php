<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VersionService;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function __construct(private VersionService $versionService){}

    public function create(int $bookId)
    {
        return response()->json(
            $this->versionService
                ->createVersion($bookId)
        );
    }

    public function list(int $bookId)
    {
        return response()->json(
            $this->versionService
                ->listVersions($bookId)
        );
    }

    public function details(int $bookId,int $versionId)
    {
        return response()->json(
            $this->versionService
                ->viewVersion(
                    $bookId,
                    $versionId
                )
        );
    }

    public function restore(int $bookId,int $versionId)
    {
        return response()->json(
            $this->versionService->restoreVersion(
                $bookId,
                $versionId
            )
        );
    }
}
