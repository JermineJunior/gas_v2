<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function cleanNumeric($value)
    {
        if ($value === null) return null;
        $cleaned = preg_replace('/[^\d.\-]/', '', $value);
        return $cleaned === '' || $cleaned === '.' ? null : $cleaned;
    }

    protected function cleanNumericFields($request, array $fields)
    {
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $request->merge([$field => $this->cleanNumeric($request->input($field))]);
            }
        }
    }
}
