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

    /**
     *
     * @param
     * @return JsonResonse
     */
    public function initialize(): JsonResponse
    {
        return response()->json(
            $this->fileManager->initialize()
        );
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tree(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->tree($request->get('path', '/'))
        );
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function content(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->content($request->get('path', '/'))
        );
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createFile(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->createFile($request)
        );
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFile(Request $request): JsonResponse
    {
        return response()->json([
            $this->fileManager->updateFile($request)
        ]);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createDirectory(Request $request): JsonResponse
    {
        return response()->json([
            $this->fileManager->createDirectory($request)
        ]);
    }


    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $response = $this->fileManager->upload($request);
        return response()->json($response);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $deleteResponse = $this->fileManager->delete(
            $request->input('items')
        );

        return response()->json(
            $deleteResponse
        );
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function paste(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->paste($request)
        );
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function rename(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->rename($request)
        );
    }

    /**
     *
     * @param Request $request
     * @return Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request): StreamedResponse
    {
        return $this->fileManager->download($request);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request): \Illuminate\Http\Response
    {
        return $this->fileManager->preview($request);
    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public function thumbnails(Request $request): mixed
    {
        return $this->fileManager->thumbnails($request);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function url(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->url($request)
        );
    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public function streamFile(Request $request): mixed
    {
        return response()->json(
            $this->fileManager->streamFile($request)
        );
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function zip(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->zip($request)
        );
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unzip(Request $request): JsonResponse
    {
        return response()->json(
            $this->fileManager->unzip($request)
        );
    }

}
