<?php

namespace App\Controllers;

use Framework\Response;
use Framework\ResponseFactory;

abstract class ApiBaseController
{
    public function __construct(protected ResponseFactory $responseFactory)
    {
    }

    /**
     * Build a JSON envelope for a successful response.
     *
     * @param string      $resource       Resource name, e.g. "cars"
     * @param mixed       $data           The primary data (array or object)
     * @param string      $selfLink       URL of the current request
     * @param string|null $collectionLink URL of the parent collection, if applicable
     * @param int|null    $total          Total item count (collections only)
     */
    protected function apiJson(
        string $resource,
        mixed $data,
        string $selfLink,
        ?string $collectionLink = null,
        ?int $total = null,
    ): Response {
        $meta = ['resource' => $resource];
        if ($total !== null) {
            $meta['total'] = $total;
        }

        $links = ['self' => $selfLink];
        if ($collectionLink !== null) {
            $links['collection'] = $collectionLink;
        }

        return $this->responseFactory->json([
            'meta'  => $meta,
            'links' => $links,
            'data'  => $data,
        ]);
    }

    /**
     * Build a JSON envelope for an error response.
     *
     * @param string      $resource       Resource name, e.g. "cars"
     * @param string      $message        Human-readable error description
     * @param string      $selfLink       URL of the current request
     * @param int         $status         HTTP status code
     * @param string|null $collectionLink URL of the parent collection, if applicable
     */
    protected function apiError(
        string $resource,
        string $message,
        string $selfLink,
        int $status,
        ?string $collectionLink = null,
    ): Response {
        $links = ['self' => $selfLink];
        if ($collectionLink !== null) {
            $links['collection'] = $collectionLink;
        }

        return $this->responseFactory->json([
            'meta'  => ['resource' => $resource],
            'links' => $links,
            'error' => [
                'status'  => $status,
                'message' => $message,
            ],
        ], $status);
    }
}
