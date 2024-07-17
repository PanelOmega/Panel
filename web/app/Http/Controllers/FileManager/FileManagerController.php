<?php

namespace App\Http\Controllers\FileManager;

use App\Events\FileManager\Download;
use App\Http\Controllers\Controller;
use App\Services\FileManager\FileManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class FileManagerController extends Controller
{

    private $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager(new ZipArchive());
    }

    public function initialize(): JsonResponse
    {
        return response()->json(
            $this->fileManager->initialize()
        );
    }

    public function tree(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->tree($request->get('path', '/'))
        );
    }

    public function content(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->content($request->get('path', '/'))
        );
    }

    public function createFile(Request $request): JsonResponse
    {

        return response()->json(
            $this->fileManager->createFile($request)
        );
    }

    public function updateFile(Request $request): JsonResponse
    {

        return response()->json([
            $this->fileManager->updateFile($request)
        ]);
    }

    public function createDirectory(Request $request): JsonResponse
    {
        return response()->json([
            $this->fileManager->createDirectory($request)
        ]);
    }


    public function upload(Request $request): JsonResponse
    {
        $response = $this->fileManager->upload($request);
        return response()->json($response);
    }

    public function delete(Request $request): JsonResponse
    {

        $deleteResponse = $this->fileManager->delete(
            $request->input('items')
        );

        return response()->json(
            $deleteResponse
        );
    }

    public function paste(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->paste($request)
        );
    }

    public function rename(Request $request): JsonResponse
    {

        return response()->json(
            $this->fileManager->rename($request)
        );
    }

    public function download(Request $request): StreamedResponse
    {
        return $this->fileManager->download($request);
    }

    public function preview(Request $request): mixed
    {
        return $this->fileManager->preview($request);
    }

    public function thumbnails(Request $request): mixed
    {
        return $this->fileManager->thumbnails($request);
    }

    public function url(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->url($request)
        );
    }

    public function streamFile(Request $request): mixed
    {
        return $this->fileManager->streamFile($request);
    }

    public function zip(Request $request)
    {
        return $this->fileManager->zip($request);
    }

    public function unzip(Request $request)
    {
        return $this->fileManager->unzip($request);
    }

}
