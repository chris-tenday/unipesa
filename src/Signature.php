<?php
$stringForSignature = '';
function calculateSignature(array $data,  $secretKey, $currentParamPrefix = '', $depth = 16, $currentRecursionLevel = 0 )
{
    if ($currentRecursionLevel >= $depth) {
        throw new Exception('Recursion level exceeded');
    }

    $stringForSignature = '';

    foreach ($data as $key => $value)
    {
        if (is_array($value)) {
            $stringForSignature .= calculateSignature(
                $value,
                $secretKey,
                "$currentParamPrefix$key.",
                $depth,
                $currentRecursionLevel + 1
            );
        } else if ($key !== 'signature') {
            $stringForSignature .= "$currentParamPrefix$key" . $value;
        }
    }

    if ($currentRecursionLevel == 0)
    {
        return strtolower(hash_hmac('sha512', $stringForSignature, $secretKey));
    }
    else
    {
        //return $StringForSignature;
        return $stringForSignature;
    }
}

?>
