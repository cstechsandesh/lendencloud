<?php

namespace App\Core;

/**
 * Description of CSConfig
 *
 * @author Sandesh
 */

class CSConfig extends \CodeIgniter\Config\BaseConfig {
    public $siteName = 'LenDen Business Suite';
    public $companyName = 'LenDen Enterprise';
    public $companyWebsite = 'https://www.lenden.local/';
    public $sessionLockTime = 3600;   //in seconds
    /*
	|--------------------------------------------------------------------------
	| Application Salt
	|--------------------------------------------------------------------------
	|
	| The $salt can be used anywhere within the application that you need
	| to provide secure data. It should be different for every application
	| and can be of any length, though the more random the characters
	| the better.
	|
	*/
	public $salt = 'gh9K*fCsZa2@hBc&hjasLKVfVBNa*%f';
}
