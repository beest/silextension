<?php

namespace OAuth\DataStore;

use OAuth\Consumer;
use OAuth\Token;

class Doctrine extends \OAuth\DataStore
{
    private $consumer;
    private $requestToken;
    private $accessToken;
    private $nonce;

    public function __construct()
    {
        $this->consumer = new Consumer('consumer_key', 'consumer_secret');
        $this->requestToken = new Token('request_token', 'request_secret');
        $this->accessToken = new Token('access_token', 'access_secret');
    }

    public function lookup_consumer($consumerKey)
    {
        if ($this->consumer->key == $consumerKey) {
            return $this->consumer;
        }

        return null;
    }

    public function lookup_token($consumer, $type, $token)
    {
        $property = $type . 'Token';

        if ($consumer->key == $this->consumer->key && $token == $this->$property->key) {
            return $this->$property;
        }

        return null;
    }

    public function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        if ($consumer->key == $this->consumer->key && (($token && $token->key == $this->requestToken->key) || ($token && $token->key == $this->accessToken->key)) && $nonce == $this->nonce) {
            return $this->nonce;
        }

        return null;
    }

    public function new_request_token($consumer)
    {
        if ($consumer->key == $this->consumer->key) {
            return $this->requestToken;
        }

        return null;
    }

    public function new_access_token($token, $consumer)
    {
        if ($consumer->key == $this->consumer->key && $token->key == $this->requestToken->key) {
            return $this->accessToken;
        }

        return null;
    }
}
