<?php

namespace App\Http\Traits;

trait KeyTypeTrait {

    /**
     * Get key types
     * @return \Illuminate\Http\Response
     */
	public function keytype()
	{
		$keyType = ['single' => 'Single', 'multiple' => 'Multiple'];

        return $keyType;
	}
}

?>