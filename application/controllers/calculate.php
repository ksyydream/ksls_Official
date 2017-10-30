<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 16:23
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Calculate extends MY_Controller
{

    public function loan_calculate()
    {
        $this->display('loan_calculate.html');
    }
    public function tax_calculate()
    {
        $this->display('tax_calculate.html');
    }

}