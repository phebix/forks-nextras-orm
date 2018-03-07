<?php
/**
 * Created by PhpStorm.
 * User: phebix
 * Date: 7.3.18
 * Time: 11:38
 */

namespace Nextras\Orm\Mapper\Dbal;


interface ICrypto
{

	public function __construct($key, $method);

	public function encrypt($plain);

	public function decrypt($garble);
}