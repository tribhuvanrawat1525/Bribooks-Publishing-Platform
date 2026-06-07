<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Services\BookService;
use Illuminate\Http\Request;
use App\Http\Requests\UploadBookRequest;


class BookController extends Controller
{
    public function __construct(private BookService $bookService){}

    public function create(CreateBookRequest $request)
    {
        return response()->json(
            $this->bookService->create(
                $request->validated()
            )
        );
    }

    public function list()
    {
        return response()->json(
            $this->bookService->list()
        );
    }

    public function details(int $id)
    {
        return response()->json(
            $this->bookService->details($id)
        );
    }

    public function update( UpdateBookRequest $request, int $id)
    {
        return response()->json(
            $this->bookService->update(
                $id,
                $request->validated()
            )
        );
    }

    public function delete(int $id)
    {
        return response()->json(
            $this->bookService->delete($id)
        );
    }

    public function upload(UploadBookRequest $request,int $id)
    {
        return response()->json(
            $this->bookService->upload(
                $id,
                $request->file('file')
            )
        );
    }
}
