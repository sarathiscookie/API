<?php

namespace App\Http\Traits;

use App\KeyContainer;

trait KeyContainerTrait {

    /**
     * Generate key container
     * @param  string  $keyType
     * @return \Illuminate\Http\Response
     */
	public function generateContainer($keyType)
	{
		return $keyType[0].mt_rand(1000, 99999);
	}
}

?>