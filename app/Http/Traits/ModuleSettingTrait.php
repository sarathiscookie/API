<?php

namespace App\Http\Traits;

use App\ModuleSetting;

trait ModuleSettingTrait
{

    /**
     * Get module name
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function moduleName($productApiId)
    {
        try {
            $moduleName = '';

            $moduleSettings = ModuleSetting::join('modules', 'module_settings.module_id', '=', 'modules.id')
                ->join('products', 'module_settings.product_id', '=', 'products.id')
                ->select('module_settings.module_id', 'modules.module AS moduleName', 'module_settings.product_id')
                ->joinActive()
                ->where('products.api_product_id', $productApiId)
                ->get();

            if($moduleSettings->count() > 0) {
                foreach($moduleSettings as $moduleSetting) {
                    $moduleName .= '<a href=""><span class="badge badge-info badge-pill">' . ucwords($moduleSetting->moduleName) . '&nbsp<i class="fas fa-trash-alt" style="color:#9e004f;"></i></span></a>&nbsp';
                }
            }
            else {
                $moduleName = '<span class="badge badge-secondary badge-pill"> No Modules </span>';
            }   
                
            return $moduleName; 
        } 
        catch (\Exception $e) {
            abort(404);
        }
    }
}


