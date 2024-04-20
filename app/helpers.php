<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
if (!function_exists('jsonResponse')) {
    function jsonResponse($data = [], $status = 200, $success = true, $message = 'Success', $errors = [])
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'success' => $success,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
if (!function_exists('getValidationData')) {
    function getValidationData($methodName)
    {
        $data = require base_path('config/validation_data.php');
        return $data[$methodName] ?? null;
    }
}
if (!function_exists('getCustomPaginationData')) {
    function getCustomPaginationData(LengthAwarePaginator $paginator)
    {
        return [
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'links' => $paginator->getUrlRange(1, $paginator->lastPage()),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
if (!function_exists('ValidationFeilds')) {
    function ValidationFeilds(Request $request,$methodName)
    {
        $validationData = getValidationData($methodName);

        if (!$validationData) {
            return null;
        }

        $validator = Validator::make($request->all(), $validationData['rules'], $validationData['messages']);

        if ($validator->fails()) {
            return jsonResponse([], 422, false, "خطای اعتبارسنجی", $validator->errors());
        }

        // Validation passed
        return null;
    }
}
if (!function_exists('formatFileSize')) {
    function formatFileSize($size) {
        $units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
    
        $unitIndex = 0;
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
    
        $formattedSize = number_format($size,2) . ' ' . $units[$unitIndex] . '‏';

        return $formattedSize;
    }
    
}
if (!function_exists('convertToTime')) {
    function convertToTime($minutes)
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        $seconds = ($remainingMinutes - floor($remainingMinutes)) * 60;
        return sprintf('%02d:%02d:%02d', $hours, $remainingMinutes, $seconds);
        
    }
    
}
if (!function_exists('jRWithPagination')) {
    function jRWithPagination($data = [],$transformedData=[], $status = 200, $success = true, $message = 'Success', $errors = [])
    {    
         // Custom pagination response
         $paginationData = getCustomPaginationData($data);
        return response()->json([
            'data' => $transformedData?$transformedData:$data->items(),
            'links'=>$paginationData['links'],
            'meta'=>$paginationData['meta'],
            'status' => $status,
            'success' => $success,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}