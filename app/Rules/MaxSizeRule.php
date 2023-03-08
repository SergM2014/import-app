<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxSizeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $total_size = filesize(storage_path(config('app.import.xlsxFile')));

        if ($total_size > (config('app.import.fileSize'))*1048576) {
            $fail('Total file size exseeds the allowed maximum');
        }
    }
}
