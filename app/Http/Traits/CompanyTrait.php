<?php

namespace App\Http\Traits;

use App\Company;

trait CompanyTrait {
	public function company()
	{
		try {
            $company = Company::select('id', 'company')
            ->active()
            ->get();

            return $company;
        }
        catch(\Exception $e) {
            abort(404);
        } 
	}
}

?>