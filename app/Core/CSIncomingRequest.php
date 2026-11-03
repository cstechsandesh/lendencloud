<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Core;

/**
 * Description of CSIncomingRequest
 *
 * @author Sandesh
 */
class CSIncomingRequest extends \CodeIgniter\HTTP\IncomingRequest {
    public $globals;
    public function __construct($config, \CodeIgniter\HTTP\URI $uri = null, $body = 'php://input', \CodeIgniter\HTTP\UserAgent $userAgent = null) {
        
        parent::__construct($config, $uri, $body, $userAgent);
    }
}
