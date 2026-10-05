<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidDocument implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //Se o documento NÃO for válido, chama $facil com a mensagem de erro
        if (! isValidDocument((string) $value)){
            $fail('O CPF/CNPJ informado é inválido.');
        }
    }
}
