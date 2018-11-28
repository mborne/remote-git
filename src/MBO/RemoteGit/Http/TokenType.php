<?php

namespace MBO\RemoteGit\Http;

/**
 * Provides types of implementation for token 
 */
class TokenType {

    const PRIVATE_TOKEN       = 'Private-Token: {token}' ;
    const AUTHORIZATION_TOKEN = 'Authorization: token {token}' ;

    /**
     * Create HTTP headers according to a tokenType
     *
     * @param string $tokenType
     * @param string $token
     */
    public static function createHttpHeaders($tokenType,$token){
        if ( empty($token) ){
            return array();
        }
        
        $parts = explode(': ',$tokenType);
        return array(
            $parts[0] => str_replace('{token}',$token,$parts[1])
        );
    }

}
