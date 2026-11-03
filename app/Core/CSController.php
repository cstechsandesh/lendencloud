<?php

namespace App\Core;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MyController
 *
 * @author Sandesh
 */
class CSController extends \CodeIgniter\Controller {

    public $data = [];
    protected $helpers = ['const', 'cs'];
    public $isAJAX = false;

    /**
     * Constructor.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger) {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
        //--------------------------------------------------------------------
        // Preload any models, libraries, etc, here.
        //--------------------------------------------------------------------
        // E.g.:
        // $this->session = \Config\Services::session();

        helper(['form', 'url', 'cs', 'ini', 'const', 'html', 'text']);

//        $this->data['config'] = config('App');
//        $this->data['session'] = \Config\Services::session()->get();
//        $this->data['validation'] = \Config\Services::validation();
        $this->isAJAX = (\Config\Services::request())->isAJAX();

        ini_set('xdebug.var_display_max_depth', '500');
        ini_set('xdebug.var_display_max_children', '4096');
        ini_set('xdebug.var_display_max_data', '10000');
        helper($this->helpers);
    }

    public $permRoleModel, $skipLoginCheck, $skipPermCheck,
            $defaultView = '_defaultView', $printView = '_printView',
            $templateView = '_templateView', $exportView = '_exportView';

    /**
     * Check if a method is a Controller Service Action (CSA_)
     */
    public function isCSAction($method = null): bool {
        if (empty($method)) {
            $router = service('router');
            $method = $router->methodName();
        }
        return !empty($method) && stripos($method, 'CSA_') === 0;
    }
}
