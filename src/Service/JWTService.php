<?php 

namespace App\Service;

class JWTService
{
    //token builder
    
    public function generate(
        array $header,
        array $payload,
        string $secret,
        int $validity = 86400
    ): string
    {
        //don't use this part for signature verification
        if($validity > 0) {
            $now  = new \DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        

        //base64 encoding

        $base64header = base64_encode(json_encode($header));
        $base64payload = base64_encode(json_encode($payload));

        //encodes values cleaning (+, / and = supression)

        $base64header = str_replace(['+', '/', '='], ['-', '_', ''], $base64header);
        $base64payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64payload);

        //signature builder
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64header.'.'.$base64payload, $secret, true);
        $base64signature = base64_encode($signature);

        $base64signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64signature);

        $jwt = $base64header.'.'.$base64payload.'.'.$base64signature;

        return $jwt;
    }

    //token validity verification

    public function isValid(string $token): bool
    {
        return preg_match(
        '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
        $token
        ) === 1;
    }

    public function getHeader(string $token): array
    {
        $array = explode('.', $token);

        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    public function getPayload(string $token): array
    {
        $array = explode('.', $token);

        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    //expiration verification

    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new \DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    //signature verification

    public function signatureCheck(string $token, string $secret): bool
    {
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;
    }
    
}