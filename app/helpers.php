<?php
if(!function_exists('normalizeDocument')){
        /**
         * Remove a mascara e mantem apenas oque esta escrito e digitado
         * exemplo: "840.714.781-87" -> "84071478187"
         */
    function normalizeDocument(string $document): string
    {
       return preg_replace('/[^A-Z0-9]/', '', strtoupper($document));
    }
}

if(!function_exists('isValidCpf')){
    function isValidCpf(string $cpf): bool
    {
        $digits = preg_replace('/\D/', '', $cpf);
        /*Precisa ter ate 11 digitos, e tem de ser do mesmo tipo, numeros*/
        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits)){
            return false;
        }
        for ($position = 9; $position < 11; $position++){
            $sum = 0;
            for($i = 0; $i < $position; $i++){
                $sum += (int) $digits[$i] * (($position + 1) - $i);
            }
            $remainder = $sum % 11;
            $checkDigit = $remainder < 2 ? 0 : 11 - $remainder;

            if((int) $digits[$position] !== $checkDigit){
                return false;
            }
        }
        return true;
    }
}
if(! function_exists('isValidCnpj')){
    function isValidCnpj(string $cnpj): bool
    {
       $char = normalizeDocument($cnpj);
       if (! preg_match('/^[A-Z0-9]{12}\d{2}$/', $char) || preg_match('/^(.)\1{13}$/', $char)) {
            return false;
        }
        $weights = [
            12 => [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            13 => [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        ];
        foreach($weights as $position => $positionWeights){
            $sum = 0;

        for ($i = 0; $i < $position; $i++){
            $sum += (ord($char[$i]) - 48) * $positionWeights[$i];
        }
        
        $remainder = $sum % 11;
        $checkDigit = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $char[$position] !== $checkDigit)
            {
                return false;
            }
        }
        return true;
    }
}
if(! function_exists('isValidDocument')){
    function isValidDocument(string $document): bool
    {
      $normalized = normalizeDocument($document);
      
      return match (strlen($normalized)){
         11      => isValidCpf($normalized),
         14      => isValidCnpj($normalized),
         default => false,
      };
    }
}
?>