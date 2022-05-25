<?php

namespace Pledg\PledgPaymentGateway\Helper;

use Firebase\JWT\JWT;

class Crypto
{
    /**
     * @param array  $payload
     * @param string $secretKey
     *
     * @return string
     */
    public function encode(array $payload, string $secretKey): string
    {
        return JWT::encode($payload, $secretKey);
    }

    /**
     * @param string $signature
     * @param string $secretKey
     *
     * @return array
     */
    public function decode(string $signature, string $secretKey): array
    {
        return (array)JWT::decode($signature, $secretKey, ['HS256']);
    }
}
